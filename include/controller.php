<?php
/*
 * ˵����1�� ��ȡ3ware�洢���ƿ�����Ϣ���������ID�б��������ţ��������ͺŵ�
 * 		 2��ɨ�������
 * 		 2����ȡ���´�����Ϣ
 * 
 * ������֧��   ��3ware 9690SAϵ�п��ƿ�
 * �ײ�API�汾��9.5.2
 * 		
 * Created by ����䣬2010-12-06
 */

require_once("function.php");

$lang=load_lang();

$controller_prefix_str=array(
	"������",
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

// -----------------------------------------------��Ա��������

	//��¼���µĴ�����Ϣ
	private $m_szLastErrorInfo;
	
// -----------------------------------------------���г�Ա��������

	function __construct()
	{
		$m_szLastErrorInfo = "";
		
	}
	
	/*
	 * ˵������ȡ���µĴ�����Ϣ
	 * ��������
	 * ���أ�������Ϣ��û�д�����Ϣ����FALSE
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
	 * ˵������ȡ�������б�
	 * ��������
	 * ���أ��������б�ʧ�ܷ���FALSE
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
	 * ˵������ȡ��̨�ؽ�����
	 * ������������ID
	 * ���أ��ɹ��������ʣ�ʧ�ܷ���FALSE
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
		// -2��ʾ�ͣ�-1��ʾ�еͣ�0��ʾ�еȣ�1��ʾ�иߣ�2��ʾ��
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
	 * ˵������ȡ��̨У������
	 * ������������ID
	 * ���أ��ɹ��������ʣ�ʧ�ܷ���FALSE
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
		// -2��ʾ�ͣ�-1��ʾ�еͣ�0��ʾ�еȣ�1��ʾ�иߣ�2��ʾ��
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
	 * ˵�������ú�̨�ؽ�����
	 * ������������ID
	 * 		 $rate������
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
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
	 * ˵�������ú�̨У������
	 * ������������ID
	 * 		 $rate������
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
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
	 * ˵��������ɨ�������������ɨ��������»�ȡ��������Ϣ��
	 * ������������ID
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
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
	
	

// ----------------------------------------------------˽�г�Ա��������

	/*
	 * ˵������ȡ������ID�б�
	 * ��������
	 * ���أ�ID�б����飬��ȡʧ�ܷ���FALSE
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
	 * ˵������ȡ�������ı��
	 * ������������ID
	 * ���أ���������ţ�ʧ�ܷ���FALSE
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
	 * ˵������ȡ�������ͺ�
	 * ������������ID
	 * ���أ��������ͺţ�ʧ�ܷ���FALSE
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
	 * ˵������ȡ���������Ӵ�����Ŀ
	 * ������������ID
	 * ���أ����Ӵ�����Ŀ��ʧ�ܷ���FALSE
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
	 * ˵������ȡ������������RAID����Ŀ
	 * ������������ID
	 * ���أ�RAID����Ŀ��ʧ�ܷ���FALSE
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


