<?php
/*
 * 说明：1、 获取3ware存储控制卡的信息，如控制器ID列表，控制器号，控制器型号等
 * 		 2、扫描控制器
 * 		 2、获取最新错误信息
 * 
 * 控制器支持   ：3ware 9690SA系列控制卡
 * 底层API版本：9.5.2
 * 		
 * Created by 王大典，2010-12-06
 */

require_once("function.php");

$lang=load_lang();

$controller_prefix_str=array(
	"控制器",
	"Controller"
);

define('GET_CONTROLLER_ID_LIST', "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_controller_id_list");
define('GET_CONTROLLER_NUMBER' , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_controller_number");
define('GET_CONTROLLER_MODEL'  , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_controller_model");
define('GET_NUMBER_OF_DRIVES'  , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_number_of_drives");
define('GET_NUMBER_OF_UNITS'   , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_number_of_units");
define('RESCAN_CONTROLLER'     , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/rescan_controller");
define('GET_BG_REBUILD_RATE'   , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_background_rebuild_rate");
define('GET_BG_VERIFY_RATE'    , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_background_verify_rate");
define('SET_BG_REBUILD_RATE'   , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/set_background_rebuild_rate");
define('SET_BG_VERIFY_RATE'    , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/set_background_verify_rate");

define('C_ID_ERR', "Controller ID Invalid");
define('BG_TASK_RATE_LOW'    , -2);
define('BG_TASK_RATE_MED_LOW', -1);
define('BG_TASK_RATE_MEDIUM' , 0);
define('BG_TASK_RATE_MED_HI' , 1);
define('BG_TASK_RATE_HIGH'   , 2);

class Controller
{

// -----------------------------------------------成员变量部分

	//记录最新的错误信息
	private $m_szLastErrorInfo;
	
// -----------------------------------------------公有成员函数部分

	function __construct()
	{
		$m_szLastErrorInfo = "";
		
	}
	
	/*
	 * 说明：获取最新的错误信息
	 * 参数：无
	 * 返回：错误信息，没有错误信息返回FALSE
	 */
	function GetLastErrorInfo()
	{
		if( $this->m_szLastErrorInfo == "" )
		{
			return FALSE;
		}
		$error_info = $this->m_szLastErrorInfo;
		$this->m_szLastErrorInfo = "";
		return $error_info;
	}
	
	/*
	 * 说明：获取控制器列表
	 * 参数：无
	 * 返回：控制器列表，失败返回FALSE
	 * array(
	 * 	array(
	 * 		"name"=>"controller 2 9660SA",
	 * 		"id"=>"1 2 3 4 5 6 7 8",
	 * 		"drives"=>10,
	 * 		"units"=>4
	 * 	),
	 * 	... 
	 * );
	 */
	function GetControllerList()
	{
		global $controller_prefix_str, $lang;
		$Controller_List = array();
		$Controller = array();
		$Controller_Number = 0;
		$Controller_Model = "";
		$Number_Of_Drives = 0;
		$Controller_Id_List = $this->GetControllerIdList();
		if( $Controller_Id_List === FALSE )
		{
			return FALSE;
		}
		
		foreach( $Controller_Id_List as $id )
		{
			$Controller_Number = $this->GetControllerNumber($id);
			if( $Controller_Number === FALSE )
			{
				return FALSE;
			}
			$Controller_Model = $this->GetControllerModel($id);
			if( $Controller_Model === FALSE )
			{
				return FALSE;
			}
			$Number_Of_Drives = $this->GetNumberOfDrives($id);
			if( $Number_Of_Drives === FALSE )
			{
				return FALSE;
			}
			$Number_Of_Units = $this->GetNumberOfUnits($id);
			if( $Number_Of_Units === FALSE )
			{
				return FALSE;
			}
			
			$Controller['id']    = $id;
			$Controller['name']  = $controller_prefix_str[$lang] . " " . $Controller_Number . " (" . $Controller_Model . ")";
			$Controller['drives']= $Number_Of_Drives;
			$Controller['units'] = $Number_Of_Units;
			
			$Controller_List[] = $Controller;
			$Controller = array();
		}
		
		return $Controller_List;
	}
	
	/*
	 * 说明：获取后台重建速率
	 * 参数：控制器ID
	 * 返回：成功返回速率，失败返回FALSE
	 */
	function GetBgRebuildRate($controller_id)
	{
		$this->m_szLastErrorInfo = "";
		$rate = 0;
		
		if( IsIdOk($controller_id) === FALSE )
		{
			$this->m_szLastErrorInfo = C_ID_ERR;
			return FALSE;
		}
		exec(GET_BG_REBUILD_RATE . " " . $controller_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}
		// -2表示低，-1表示中低，0表示中等，1表示中高，2表示高
		$rate = trim($output[0]);
		if($rate == -2)
		{
			return BG_TASK_RATE_LOW;
		}
		else if($rate == -1)
		{
			return BG_TASK_RATE_MED_LOW;
		}
		else if($rate == 0)
		{
			return BG_TASK_RATE_MEDIUM;
		}
		else if($rate == 1)
		{
			return BG_TASK_RATE_MED_HI;
		}
		else if($rate == 2)
		{
			return BG_TASK_RATE_HIGH;
		}
		
		return FALSE;
	}
	
	/*
	 * 说明：获取后台校验速率
	 * 参数：控制器ID
	 * 返回：成功返回速率，失败返回FALSE
	 */
	function GetBgVerifyRate($controller_id)
	{
		$this->m_szLastErrorInfo = "";
		$rate = 0;
		
		if( IsIdOk($controller_id) === FALSE )
		{
			$this->m_szLastErrorInfo = C_ID_ERR;
			return FALSE;
		}
		exec(GET_BG_VERIFY_RATE . " " . $controller_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}
		// -2表示低，-1表示中低，0表示中等，1表示中高，2表示高
		$rate = trim($output[0]);
		if($rate == -2)
		{
			return BG_TASK_RATE_LOW;
		}
		else if($rate == -1)
		{
			return BG_TASK_RATE_MED_LOW;
		}
		else if($rate == 0)
		{
			return BG_TASK_RATE_MEDIUM;
		}
		else if($rate == 1)
		{
			return BG_TASK_RATE_MED_HI;
		}
		else if($rate == 2)
		{
			return BG_TASK_RATE_HIGH;
		}
		
		return FALSE;
	}
	
	/*
	 * 说明：设置后台重建速率
	 * 参数：控制器ID
	 * 		 $rate：速率
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	function SetBgRebuildRate($controller_id, $rate=BG_TASK_RATE_MEDIUM)
	{
		$this->m_szLastErrorInfo = "";
		
		if( IsIdOk($controller_id) === FALSE )
		{
			$this->m_szLastErrorInfo = C_ID_ERR;
			return FALSE;
		}
		$rate_array = array( BG_TASK_RATE_LOW, BG_TASK_RATE_MED_LOW, BG_TASK_RATE_MEDIUM, 
							 BG_TASK_RATE_MED_HI, BG_TASK_RATE_HIGH);

		if( ! in_array($rate, $rate_array) )
		{
			exec(SET_BG_REBUILD_RATE . " " . $controller_id . " " . BG_TASK_RATE_MEDIUM, $output, $retval);
		}
		else
		{
			exec(SET_BG_REBUILD_RATE . " " . $controller_id . " " . $rate, $output, $retval);
		}
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}
		
		return TRUE;
	}
	
	/*
	 * 说明：设置后台校验速率
	 * 参数：控制器ID
	 * 		 $rate：速率
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	function SetBgVerifyRate($controller_id, $rate=BG_TASK_RATE_MEDIUM)
	{
		$this->m_szLastErrorInfo = "";
		
		if( IsIdOk($controller_id) === FALSE )
		{
			$this->m_szLastErrorInfo = C_ID_ERR;
			return FALSE;
		}
		$rate_array = array( BG_TASK_RATE_LOW, BG_TASK_RATE_MED_LOW, BG_TASK_RATE_MEDIUM, 
							 BG_TASK_RATE_MED_HI, BG_TASK_RATE_HIGH);

		if( ! in_array($rate, $rate_array) )
		{
			exec(SET_BG_VERIFY_RATE . " " . $controller_id . " " . BG_TASK_RATE_MEDIUM, $output, $retval);
		}
		else
		{
			exec(SET_BG_VERIFY_RATE . " " . $controller_id . " " . $rate, $output, $retval);
		}
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}
		
		return TRUE;
	}
	
	/*
	 * 说明：重新扫描控制器（重新扫描后需重新获取控制器信息）
	 * 参数：控制器ID
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	function RescanController($controller_id)
	{
		$this->m_szLastErrorInfo = "";
		
		if( IsIdOk($controller_id) === FALSE )
		{
			$this->m_szLastErrorInfo = C_ID_ERR;
			return FALSE;
		}
		exec(RESCAN_CONTROLLER . " " . $controller_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}
		return TRUE;
	}
	
	

// ----------------------------------------------------私有成员函数部分

	/*
	 * 说明：获取控制器ID列表
	 * 参数：无
	 * 返回：ID列表数组，获取失败返回FALSE
	 * array(
	 * 		"1 2 3 4 5 6 7 8",
	 * 		"2 3 4 5 6 7 8 9",
	 * 		...
	 * );
	 */
	private function GetControllerIdList()
	{
		$this->m_szLastErrorInfo = "";
		$ControllerIdList = array();
		
		exec(GET_CONTROLLER_ID_LIST, $output, $retval);
		if( $retval !== 0 )
		{
			if( isset($output[0]) )
				$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}
		foreach( $output as $line )
		{
			$ControllerIdList[] = trim( $line );
		}
		return $ControllerIdList;
	}
	
	/*
	 * 说明：获取控制器的编号
	 * 参数：控制器ID
	 * 返回：控制器编号，失败返回FALSE
	 */
	private function GetControllerNumber( $controller_id )
	{
		$this->m_szLastErrorInfo = "";
		$Controller_Number = 0;
		
		if( IsIdOk($controller_id) === FALSE )
		{
			$this->m_szLastErrorInfo = C_ID_ERR;
			return FALSE;
		}
		exec(GET_CONTROLLER_NUMBER . " " . $controller_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}
		$Controller_Number = trim($output[0]);
		return $Controller_Number;
	}
	
	/*
	 * 说明：获取控制器型号
	 * 参数：控制器ID
	 * 返回：控制器型号，失败返回FALSE
	 */
	private function GetControllerModel( $controller_id )
	{
		$this->m_szLastErrorInfo = "";
		$Controller_Model = "";
		
		if( IsIdOk($controller_id) === FALSE )
		{
			$this->m_szLastErrorInfo = C_ID_ERR;
			return FALSE;
		}
		exec(GET_CONTROLLER_MODEL . " " . $controller_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}
		$Controller_Model = trim($output[0]);
		return $Controller_Model;
	}

	/*
	 * 说明：获取控制器连接磁盘数目
	 * 参数：控制器ID
	 * 返回：连接磁盘数目，失败返回FALSE
	 */
	private function GetNumberOfDrives( $controller_id )
	{
		$this->m_szLastErrorInfo = "";
		$Number_Of_Drives = 0;
		
		if( IsIdOk($controller_id) === FALSE )
		{
			$this->m_szLastErrorInfo = C_ID_ERR;
			return FALSE;
		}
		exec(GET_NUMBER_OF_DRIVES . " " . $controller_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}
		$Number_Of_Drives = trim($output[0]);
		return $Number_Of_Drives;
	}
	
	/*
	 * 说明：获取控制器创建的RAID组数目
	 * 参数：控制器ID
	 * 返回：RAID组数目，失败返回FALSE
	 */
	private function GetNumberOfUnits( $controller_id )
	{
		$this->m_szLastErrorInfo = "";
		$Number_Of_Units = 0;
		
		if( IsIdOk($controller_id) === FALSE )
		{
			$this->m_szLastErrorInfo = C_ID_ERR;
			return FALSE;
		}
		exec(GET_NUMBER_OF_UNITS . " " . $controller_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}
		$Number_Of_Units = trim($output[0]);
		return $Number_Of_Units;
	}
}

?>


