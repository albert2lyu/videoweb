<?php
/*
 * 说明：1、 获取磁盘信息，磁盘ID列表，磁盘编号，磁盘类型，磁盘固件版本号、WWN、连接速率、SMART数据等
 * 		 2、定位磁盘
 * 		 3、获取最新错误信息
 * 		 4、配置UNIT
 * 
 * 控制器支持   ：3ware 9690SA系列控制卡
 * 底层API版本：9.5.2
 * 
 */
require_once("function.php");

$lang=load_lang();

$ok_str=array(
	"OK",
	"OK"
);
$verifying_str=array(
	"校验",
	"Verifying"
);
$initializing_str=array(
	"初始化",
	"Initializing"
);
$degraded_str=array(
	"降级",
	"Degraded"
);
$rebuilding_str=array(
	"重建",
	"Rebuilding"
);
$recovery_str=array(
	"修复",
	"Recovery"
);
$migrating_str=array(
	"转移",
	"Migrating"
);
$inoperable_str=array(
	"不可操作",
	"Inoperable"
);
$unknown_str=array(
	"未知",
	"Unknown"
);
$active_str=array(
	"进行中",
	"active"
);
$pause_str=array(
	"暂停",
	"pause"
);

define('GET_UNIT_ID_LIST'               , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_unit_id_list");
define('GET_UNIT_NUMBER'                , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_unit_number");
define('GET_UNIT_NAME'                  , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_unit_name");
define('GET_UNIT_SERIAL_NUMBER'         , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_unit_serial_number");
define('GET_UNIT_CAPACITY'              , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_unit_capacity");
define('GET_UNIT_CONFIGURATION'         , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_unit_configuration");
define('GET_UNIT_STRIPE_SIZE'           , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_unit_stripe_size");
define('GET_DRIVE_ID_LIST_OF_UNIT'      , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_drive_id_list_of_unit");
define('GET_UNIT_MODE'                  , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_unit_mode");
define('GET_UNIT_WRITE_CACHE_STATE'     , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_unit_write_cache_state");
define('GET_UNIT_AUTO_VERIFY_POLICY'    , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_unit_auto_verify_policy");
define('GET_UNIT_ECC_POLICY'            , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_unit_ecc_policy");
define('GET_UNIT_QUEUE_MODE'            , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_unit_queue_mode");
define('GET_UNIT_STORSAVE_MODE'         , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_unit_storsave_mode");
define('GET_UNIT_RAPID_RECOVERY_CONTROL', "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_unit_rapid_recovery_control");
define('IDENTIFY_UNIT'                  , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/identify_unit");
define('GET_UNIT_IDENTIFY_STATUS'       , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_unit_identify_status");

define('VERIFY_UNIT'                    , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/verify_unit");
define('DELETE_UNIT'                    , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/delete_unit");
define('ENABLE_UNIT_WRITE_CACHE'        , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/enable_unit_write_cache");
define('DISABLE_UNIT_WRITE_CACHE'       , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/disable_unit_write_cache");
define('ENABLE_UNIT_AUTO_VERIFY'        , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/enable_unit_auto_verify");
define('DISABLE_UNIT_AUTO_VERIFY'       , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/disable_unit_auto_verify");
define('ENABLE_UNIT_QUEUING'            , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/enable_unit_queuing");
define('DISABLE_UNIT_QUEUING'           , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/disable_unit_queuing");
define('ENABLE_UNIT_ECC'                , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/enable_unit_ecc");
define('DISABLE_UNIT_ECC'               , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/disable_unit_ecc");
define('SET_UNIT_STORSAVE_PROTECTION'   , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/set_unit_storsave_protection");
define('SET_UNIT_STORSAVE_BALANCE'      , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/set_unit_storsave_balance");
define('SET_UNIT_STORSAVE_PERFORMANCE'  , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/set_unit_storsave_performance");
define('SET_UNIT_RAPID_RECOVERY_CONTROL', "export LANG=C; /usr/bin/sudo /opt/vstor/bin/set_unit_rapid_recovery_control");
define('STOP_VERIFY_UNIT'               , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/stop_verify_unit");
define('SET_UNIT_NAME'                  , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/set_unit_name");
define('REBUILD_UNIT'                   , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/rebuild_unit");
define('CREATE_UNIT'                    , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/create_unit");

define('U_ID_ERR'   , "Uint ID Invalid");
define('U_NAME_ERR' , "Unit Name Invalid");
define('FILE_ERR'   , "File not existed");
// 存储模式
define('STORSAVE_PROTECTION', 0);
define('STORSAVE_BALANCE', 1);
define('STORSAVE_PERFORMANCE', 2);
// 快速RAID修复模式
define('RRC_REBUILD', 0);
define('RRC_ALL', 1);
define('RRC_DISABLE', 2);

Class Unit
{
// -----------------------------------------------成员变量部分

	//记录最新的错误信息
	private $m_szLastErrorInfo;
	
// -----------------------------------------------公有成员函数部分
	
	function __construct()
	{
		$this->m_szLastErrorInfo = "";
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
	 * 说明：获取Uint ID列表
	 * 参数：控制器ID
	 * 返回：Unit ID列表，失败返回FALSE
	 * array(
	 * 	"2 2 255 1 1255 255 255",
	 * 	...
	 * )
	 */
	function GetUnitIdList($controller_id)
	{
		$this->m_szLastErrorInfo = "";
		$Unit_Id_List = array();
		
		if( IsIdOk($controller_id) === FALSE )
		{
			$this->m_szLastErrorInfo = C_ID_ERR;
			return FALSE;
		}
		exec(GET_UNIT_ID_LIST . " " . $controller_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}
		
		foreach( $output as $line )
		{
			$Unit_Id_List[] = trim( $line );
		}
		return $Unit_Id_List;
	}
	
	/*
	 * 说明：根据unit number获取unit id
	 * 参数：controller id， unit number
	 * 返回：unit id，失败返回FALSE
	 */
	function GetUnitIdFormUnitNumber($controller_id ,$unit_number)
	{
		$Unit_Id_List = array();
		$Unit_Id = "";
		$Number = 0;
		
		if( IsIdOk($controller_id) === FALSE )
		{
			$this->m_szLastErrorInfo = C_ID_ERR;
			return FALSE;
		}
		$Unit_Id_List = $this->GetUnitIdList($controller_id);
		if($Unit_Id_List === FALSE)
		{
			return FALSE;
		}
		foreach( $Unit_Id_List as $entry )
		{
			$Number = $this->GetUnitNumber($entry);
			if($Number === FALSE)
			{
				continue;
			}
			if( $Number == $unit_number )
			{
				$Unit_Id = $entry;
				break;
			}			
		}
		return $Unit_Id;
	}
	
	/*
	 * 说明：获取UNIT基本信息
	 * 参数：Unit ID
	 * 返回：基本信息列表，失败返回FALSE
	 * array(
	 * 	"id"=>"",
	 * 	"number"=>"",
	 * 	"name"=>"",
	 * 	"type"=>"",
	 * 	"capacity"=>"",
	 * 	"status"=>""
	 * 	"drive_number"=>"",
	 * 	"drive_id_list"=>array(
	 * 				"",
	 * 				...
	 * 				)
	 * )
	 */
	function GetUnitBasicInfo($unit_id)
	{
		$UnitBasicInfo = array();
		
		if( IsIdOk($unit_id) === FALSE )
		{
			$this->m_szLastErrorInfo = U_ID_ERR;
			return FALSE;
		}
		
		//id
		$UnitBasicInfo['id'] = $unit_id;
		//number
		$number = $this->GetUnitNumber($unit_id);
		if($number === FALSE)
		{
			return FALSE;
		}
		$UnitBasicInfo['number'] = $number;
		//name
		$name = $this->GetUnitName($unit_id);
		if($name === FALSE)
		{
			return FALSE;
		}
		$UnitBasicInfo['name'] = $name;
		//type
		$type = $this->GetUnitConfiguration($unit_id);
		if($type === FALSE)
		{
			return FALSE;
		}
		$UnitBasicInfo['type'] = $type;
		//capacity
		$capacity = $this->GetUnitCapacity($unit_id);
		if($capacity === FALSE)
		{
			return FALSE;
		}
		$UnitBasicInfo['capacity'] = $capacity;
		//status
		$status = $this->GetUnitMode($unit_id);
		if($status === FALSE)
		{
			return FALSE;
		}
		$UnitBasicInfo['status'] = $status;
		//drive_number
		$drive_number = $this->GetDriveNumberOfUnit($unit_id);
		if($drive_number === FALSE)
		{
			return FALSE;
		}
		$UnitBasicInfo['drive_number'] = $drive_number;
		//drive_id_list
		$drive_id_list = $this->GetDriveIdListOfUnit($unit_id);
		if($drive_id_list === FALSE)
		{
			return FALSE;
		}
		$UnitBasicInfo['drive_id_list'] = $drive_id_list;
		
		return $UnitBasicInfo;
	}
	
	/*
	 * 说明：获取UNIT基本信息
	 * 参数：Unit ID
	 * 返回：基本信息列表，失败返回FALSE
	 * array(
	 * 	"id"=>"",
	 * 	"number"=>"",
	 * 	"name"=>"",
	 * 	"type"=>"",
	 * 	"capacity"=>"",
	 * 	"status"=>"",
	 * 	"serial"=>"",
	 * 	"stripe"=>"",
	 * 	"write_cache"=>"",
	 * 	"auto_verify"=>"",
	 * 	"ecc"=>"",
	 * 	"queue"=>"",
	 * 	"storsave"=>"",
	 * 	"rrr"=>""// rapid raid recovery
	 * 	"drive_number"=>"",
	 * 	"drive_id_list"=>array(
	 * 				"",
	 * 				...
	 * 				)
	 * )
	 */
	function GetUnitDetailInfo($unit_id)
	{
		$UnitDetailInfo = array();
		
		if( IsIdOk($unit_id) === FALSE )
		{
			$this->m_szLastErrorInfo = U_ID_ERR;
			return FALSE;
		}
		$UnitDetailInfo = $this->GetUnitBasicInfo($unit_id);
		if($UnitDetailInfo === FALSE)
		{
			return FALSE;
		}
		//serial
		$serial = $this->GetUnitSerialNumber($unit_id);
		if($serial === FALSE)
		{
			return FALSE;
		}
		$UnitDetailInfo['serial'] = $serial;
		//stripe
		$stripe = $this->GetUnitStripeSize($unit_id);
		if($stripe === FALSE)
		{
			return FALSE;
		}
		$UnitDetailInfo['stripe'] = $stripe;
		//write_cache
		$write_cache = $this->GetUnitWriteCacheState($unit_id);
		if($write_cache === FALSE)
		{
			return FALSE;
		}
		$UnitDetailInfo['write_cache'] = $write_cache;
		//auto_verify
		$auto_verify = $this->GetUnitAutoVerifyPolicy($unit_id);
		if($auto_verify === FALSE)
		{
			return FALSE;
		}
		$UnitDetailInfo['auto_verify'] = $auto_verify;
		//ecc
		$ecc = $this->GetUnitEccPolicy($unit_id);
		if($ecc === FALSE)
		{
			return FALSE;
		}
		$UnitDetailInfo['ecc'] = $ecc;
		//queue
		$queue = $this->GetUnitQueueMode($unit_id);
		if($queue === FALSE)
		{
			return FALSE;
		}
		$UnitDetailInfo['queue'] = $queue;
		//storsave
		$storsave = $this->GetUnitStorsaveMode($unit_id);
		if($storsave === FALSE)
		{
			return FALSE;
		}
		$UnitDetailInfo['storsave'] = $storsave;
		//rrr// rapid raid recovery
		$rrr = $this->GetUnitRapidRecoveryControl($unit_id);
		if($rrr === FALSE)
		{
			return FALSE;
		}
		$UnitDetailInfo['rrr'] = $rrr;
		//drive_number
		$drive_number = $this->GetDriveNumberOfUnit($unit_id);
		if($drive_number === FALSE)
		{
			return FALSE;
		}
		$UnitDetailInfo['drive_number'] = $drive_number;
		//drive_id_list
		$drive_id_list = $this->GetDriveIdListOfUnit($unit_id);
		if($drive_id_list === FALSE)
		{
			return FALSE;
		}
		$UnitDetailInfo['drive_id_list'] = $drive_id_list;
		
		return $UnitDetailInfo;
	}
	
	
	/*
	 * 说明：定位UNIT
	 * 参数：unit id
	 * 返回：成功返回TRUE，否则返回FALSE
	 */
	function IdentifyUnit($unit_id)
	{
		$this->m_szLastErrorInfo = "";
		
		if( IsIdOk($unit_id) === FALSE )
		{
			$this->m_szLastErrorInfo = U_ID_ERR;
			return FALSE;
		}
		exec(IDENTIFY_UNIT . " " . $unit_id . " >/dev/null &");
		return TRUE;
	}
	
	/*
	 * 说明：校验unit
	 * 参数：unit id
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	function VerifyUnit($unit_id)
	{
		$this->m_szLastErrorInfo = "";
		
		if( IsIdOk($unit_id) === FALSE )
		{
			$this->m_szLastErrorInfo = U_ID_ERR;
			return FALSE;
		}
		exec(VERIFY_UNIT . " " . $unit_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}
		
		return TRUE;
	}
	
	/*
	 * 说明：停止校验unit
	 * 参数：unit id
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	function StopVerifyUnit($unit_id)
	{
		$this->m_szLastErrorInfo = "";
		
		if( IsIdOk($unit_id) === FALSE )
		{
			$this->m_szLastErrorInfo = U_ID_ERR;
			return FALSE;
		}
		exec(STOP_VERIFY_UNIT . " " . $unit_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}
		
		return TRUE;
	}
	
	/*
	 * 说明：删除unit
	 * 参数：unit id
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	function DeleteUnit($unit_id)
	{
		$this->m_szLastErrorInfo = "";
		
		if( IsIdOk($unit_id) === FALSE )
		{
			$this->m_szLastErrorInfo = U_ID_ERR;
			return FALSE;
		}
		exec(DELETE_UNIT . " " . $unit_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}
		
		return TRUE;
	}

	/*
	 * 说明：设置unit的写缓存
	 * 参数：unit id
	 * 		 $mode:TRUE-开启（默认），FALSE-关闭, 其他-关闭
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	function SetUnitWriteCache($unit_id, $mode=TRUE)
	{
		$this->m_szLastErrorInfo = "";
		$retval = 0;
		$output = array();
		
		if( IsIdOk($unit_id) === FALSE )
		{
			$this->m_szLastErrorInfo = U_ID_ERR;
			return FALSE;
		}
		if($mode === TRUE)
		{
			exec(ENABLE_UNIT_WRITE_CACHE . " " . $unit_id, $output, $retval);
		}
		else
		{
			exec(DISABLE_UNIT_WRITE_CACHE . " " . $unit_id, $output, $retval);
		}
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}
		
		return TRUE;
	}
	
	/*
	 * 说明：设置unit的自动校验
	 * 参数：unit id
	 * 		 $mode:TRUE-开启（默认），FALSE-关闭, 其他-关闭
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	function SetUnitAutoVerify($unit_id, $mode=TRUE)
	{
		$this->m_szLastErrorInfo = "";
		$retval = 0;
		$output = array();
		
		if( IsIdOk($unit_id) === FALSE )
		{
			$this->m_szLastErrorInfo = U_ID_ERR;
			return FALSE;
		}
		if($mode === TRUE)
		{
			exec(ENABLE_UNIT_AUTO_VERIFY . " " . $unit_id, $output, $retval);
		}
		else
		{
			exec(DISABLE_UNIT_AUTO_VERIFY . " " . $unit_id, $output, $retval);
		}
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}
		
		return TRUE;
	}

	/*
	 * 说明：设置unit的队列
	 * 参数：unit id
	 * 		 $mode:TRUE-开启（默认），FALSE-关闭, 其他-关闭
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	function SetUnitQueuing($unit_id, $mode=TRUE)
	{
		$this->m_szLastErrorInfo = "";
		$retval = 0;
		$output = array();
		
		if( IsIdOk($unit_id) === FALSE )
		{
			$this->m_szLastErrorInfo = U_ID_ERR;
			return FALSE;
		}
		if($mode === TRUE)
		{
			exec(ENABLE_UNIT_QUEUING . " " . $unit_id, $output, $retval);
		}
		else
		{
			exec(DISABLE_UNIT_QUEUING . " " . $unit_id, $output, $retval);
		}
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}
		
		return TRUE;
	}

	/*
	 * 说明：设置unit的ecc
	 * 参数：unit id
	 * 		 $mode:TRUE-开启（默认），FALSE-关闭, 其他-关闭
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	function SetUnitECC($unit_id, $mode=TRUE)
	{
		$this->m_szLastErrorInfo = "";
		$retval = 0;
		$output = array();
		
		if( IsIdOk($unit_id) === FALSE )
		{
			$this->m_szLastErrorInfo = U_ID_ERR;
			return FALSE;
		}
		if($mode === TRUE)
		{
			exec(ENABLE_UNIT_ECC . " " . $unit_id, $output, $retval);
		}
		else
		{
			exec(DISABLE_UNIT_ECC . " " . $unit_id, $output, $retval);
		}
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}
		
		return TRUE;
	}
	

	/*
	 * 说明：设置unit的存储策略
	 * 参数：unit id
	 * 		 $mode: STORSAVE_PROTECTION  - 保护模式（默认）
	 * 			    STORSAVE_BALANCE     - 平衡模式
	 * 			    STORSAVE_PERFORMANCE - 性能模式
	 * 			          其他                                               - 保护模式 
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	function SetUnitStorsave($unit_id, $mode=STORSAVE_PROTECTION)
	{
		$this->m_szLastErrorInfo = "";
		$retval = 0;
		$output = array();
		
		if( IsIdOk($unit_id) === FALSE )
		{
			$this->m_szLastErrorInfo = U_ID_ERR;
			return FALSE;
		}
		if($mode === STORSAVE_PROTECTION)
		{
			exec(SET_UNIT_STORSAVE_PROTECTION . " " . $unit_id, $output, $retval);
		}
		else if($mode === STORSAVE_BALANCE)
		{
			exec(SET_UNIT_STORSAVE_BALANCE . " " . $unit_id, $output, $retval);
		}
		else if($mode  === STORSAVE_PERFORMANCE)
		{
			exec(SET_UNIT_STORSAVE_PERFORMANCE . " " . $unit_id, $output, $retval);
		}
		else
		{
			exec(SET_UNIT_STORSAVE_PROTECTION . " " . $unit_id, $output, $retval);
		}
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}
		
		return TRUE;
	}
	
	/*
	 * 说明：设置unit的快速RAID修复模式
	 * 参数：unit id
	 * 		 $mode: RRC_REBUILD  - 重建
	 * 			    RRC_ALL      - 所有（默认）
	 * 			    RRC_DISABLE  - 不开启
	 * 			          其他                          - 所有 
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	function SetUnitRapidRecoveryControl($unit_id, $mode=RRC_ALL)
	{
		$this->m_szLastErrorInfo = "";
		$retval = 0;
		$output = array();
		
		if( IsIdOk($unit_id) === FALSE )
		{
			$this->m_szLastErrorInfo = U_ID_ERR;
			return FALSE;
		}
		//0-重建Rebuild，1-所有All, 2-未打开Disable
		$rrc_mode = 1;
		if($mode === RRC_ALL)
		{
			$rrc_mode = 1;
		}
		else if($mode === RRC_REBUILD)
		{
			$rrc_mode = 0;
		}
		else if($mode  === RRC_DISABLE)
		{
			$rrc_mode = 2;
		}
		exec(SET_UNIT_RAPID_RECOVERY_CONTROL . " " . $unit_id . " " . $rrc_mode, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}
		
		return TRUE;
	}
	
	/*
	 * 说明：设置unit名称
	 * 参数：unit id
	 * 		 name：unit名称
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	function SetUnitName($unit_id, $name)
	{
		$this->m_szLastErrorInfo = "";
		
		if( IsIdOk($unit_id) === FALSE )
		{
			$this->m_szLastErrorInfo = U_ID_ERR;
			return FALSE;
		}
		if( IsRaidNameOk($name) === FALSE )
		{
			$this->m_szLastErrorInfo = U_NAME_ERR;
			return FALSE;
		}
		exec(SET_UNIT_NAME . " " . $unit_id . " " . $name, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}
		
		return TRUE;
	}
	/*
	 * 说明：重建unit
	 * 参数：
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	function RebuildUnit()
	{
		$this->m_szLastErrorInfo = "";
		
		return TRUE;
	}
	
	/*
	 * 说明：创建UNIT
	 * 参数：$drv_id_file：保存磁盘ID列表的文件路径
	 * 		 $unit_params_file：保存要创建的UNIT的相关参数
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	function CreateUnit($drv_id_file, $unit_params_file)
	{
		$this->m_szLastErrorInfo = "";
		
		if( ! file_exists($drv_id_file) )
		{
			$this->m_szLastErrorInfo = FILE_ERR;
			return FALSE;
		}
		if( ! file_exists($unit_params_file) )
		{
			$this->m_szLastErrorInfo = FILE_ERR;
			return FALSE;
		}
		
		exec(CREATE_UNIT . " " . $drv_id_file . " " . $unit_params_file, $output, $retval);
		if( $retval !== 0 )
		{
			unlink($drv_id_file);
			unlink($unit_params_file);
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}
		
		// 删除文件
		unlink($drv_id_file);
		unlink($unit_params_file);
		return TRUE;
	}
	
	
// -----------------------------------------------私有成员函数部分

	/*
	 * 说明：获取UNIT编号
	 * 参数：unit id
	 * 返回：unit编号，失败返回FALSE
	 */
	private function GetUnitNumber($unit_id)
	{
		$this->m_szLastErrorInfo = "";
		$Unit_Number = 0;

		if( IsIdOk($unit_id) === FALSE )
		{
			$this->m_szLastErrorInfo = U_ID_ERR;
			return FALSE;
		}
		exec(GET_UNIT_NUMBER . " " . $unit_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}

		$Unit_Number = trim( $output[0] );

		return $Unit_Number;
	}
	
	/*
	 * 说明：获取UNIT名称
	 * 参数：unit id
	 * 返回：unit名称，失败返回FALSE
	 */
	private function GetUnitName($unit_id)
	{
		$this->m_szLastErrorInfo = "";
		$Unit_Name = "";

		if( IsIdOk($unit_id) === FALSE )
		{
			$this->m_szLastErrorInfo = U_ID_ERR;
			return FALSE;
		}
		exec(GET_UNIT_NAME . " " . $unit_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}

		$Unit_Name = trim( $output[0] );

		return $Unit_Name;
	}
	
	/*
	 * 说明：获取UNIT序列号
	 * 参数：unit id
	 * 返回：unit序列号，失败返回FALSE
	 */
	private function GetUnitSerialNumber($unit_id)
	{
		$this->m_szLastErrorInfo = "";
		$Unit_Serial_Number = "";

		if( IsIdOk($unit_id) === FALSE )
		{
			$this->m_szLastErrorInfo = U_ID_ERR;
			return FALSE;
		}
		exec(GET_UNIT_SERIAL_NUMBER . " " . $unit_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}

		$Unit_Serial_Number = trim( $output[0] );

		return $Unit_Serial_Number;
	}
	
	/*
	 * 说明：获取UNIT容量
	 * 参数：unit id
	 * 返回：unit容量字符串，如1 TB，失败返回FALSE
	 */
	private function GetUnitCapacity($unit_id)
	{
		$this->m_szLastErrorInfo = "";
		$Unit_Capacity = 0;

		if( IsIdOk($unit_id) === FALSE )
		{
			$this->m_szLastErrorInfo = U_ID_ERR;
			return FALSE;
		}
		exec(GET_UNIT_CAPACITY . " " . $unit_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}

		$Unit_Capacity = format_bytesize( trim($output[0]) );
		return $Unit_Capacity;
	}
	
	/*
	 * 说明：获取UNIT配置（RAID级别）
	 * 参数：unit id
	 * 返回：unitRAID级别，失败返回FALSE
	 */
	private function GetUnitConfiguration($unit_id)
	{
		$this->m_szLastErrorInfo = "";
		$Unit_Configuration = "";

		if( IsIdOk($unit_id) === FALSE )
		{
			$this->m_szLastErrorInfo = U_ID_ERR;
			return FALSE;
		}
		exec(GET_UNIT_CONFIGURATION . " " . $unit_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}

		$Unit_Configuration = trim($output[0]);
		return $Unit_Configuration;
	}

	/*
	 * 说明：获取UNIT Stripe 大小
	 * 参数：unit id
	 * 返回：stripe大小（KB），失败返回FALSE
	 */
	private function GetUnitStripeSize($unit_id)
	{
		$this->m_szLastErrorInfo = "";
		$Unit_Stripe_Size = 0;

		if( IsIdOk($unit_id) === FALSE )
		{
			$this->m_szLastErrorInfo = U_ID_ERR;
			return FALSE;
		}
		exec(GET_UNIT_STRIPE_SIZE . " " . $unit_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}

		$Unit_Stripe_Size = trim($output[0]);
		if ( $Unit_Stripe_Size == 0 )
		{
			return "--";
		}
		return $Unit_Stripe_Size . " KB";
	}
	
	/*
	 * 说明：获取UNIT包含的磁盘ID列表
	 * 参数：unit id
	 * 返回：磁盘ID列表，失败返回FALSE
	 * array(
	 * 	"3 2 14 255 255 255 255 255",
	 * 	"3 2 15 255 255 255 255 255",
	 * 	...
	 * )
	 */
	private function GetDriveIdListOfUnit($unit_id)
	{
		$this->m_szLastErrorInfo = "";
		$Drive_Id_List = array();

		if( IsIdOk($unit_id) === FALSE )
		{
			$this->m_szLastErrorInfo = U_ID_ERR;
			return FALSE;
		}
		exec(GET_DRIVE_ID_LIST_OF_UNIT . " " . $unit_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}
		foreach( $output as $line )
		{
			$Drive_Id_List[] = trim($line);
		}
		return $Drive_Id_List;
	}
	/*
	 * 说明：获取UNIT包含的磁盘个数
	 * 参数：unit id
	 * 返回：磁盘个数，失败返回FALSE

	 */
	private function GetDriveNumberOfUnit($unit_id)
	{
		$Drive_Number_Of_Unit = 0;
		$Drive_Id_List = $this->GetDriveIdListOfUnit($unit_id);
		if($Drive_Id_List === FALSE)
		{
			return FALSE;
		}
		$Drive_Number_Of_Unit = count($Drive_Id_List);
		return $Drive_Number_Of_Unit;
	}
	/*
	 * 说明：获取UNIT状态
	 * 参数：unit id
	 * 返回：unit状态，失败返回FALSE
	 */
	private function GetUnitMode($unit_id)
	{
		global $lang;
		global $ok_str, $verifying_str, $initializing_str, $degraded_str, $rebuilding_str;
		global $recovery_str, $migrating_str, $inoperable_str, $unknown_str;
		global $active_str, $pause_str;
		$this->m_szLastErrorInfo = "";
		$Unit_Mode = "";
		$Mode_List = array();

		if( IsIdOk($unit_id) === FALSE )
		{
			$this->m_szLastErrorInfo = U_ID_ERR;
			return FALSE;
		}
		exec(GET_UNIT_MODE . " " . $unit_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}
		
		$Mode_List = explode(" ", trim($output[0]));
		if( count($Mode_List) !==3 )
		{
			return FALSE;
		}
		if($Mode_List[0] == 0)
		{
			$Unit_Mode = $ok_str[$lang];
		}
		else if($Mode_List[0] == 1)
		{
			$Unit_Mode = $verifying_str[$lang];
		}
		else if($Mode_List[0] == 2)
		{
			$Unit_Mode = $initializing_str[$lang];
		}
		else if($Mode_List[0] == 3)
		{
			$Unit_Mode = $degraded_str[$lang];
		}
		else if($Mode_List[0] == 4)
		{
			$Unit_Mode = $rebuilding_str[$lang];
		}
		else if($Mode_List[0] == 5)
		{
			$Unit_Mode = $recovery_str[$lang];
		}
		else if($Mode_List[0] == 6)
		{
			$Unit_Mode = $migrating_str[$lang];
		}
		else if($Mode_List[0] == 7)
		{
			$Unit_Mode = $inoperable_str[$lang];
		}
		else if($Mode_List[0] == 8)
		{
			$Unit_Mode = $unknown_str[$lang];
		}
		
		if($Mode_List[1] != 255)
		{
			$Unit_Mode = $Unit_Mode . " " . $Mode_List[1] . "%";
		}
		
		if($Mode_List[2] == 0)
		{
			$Unit_Mode = $Unit_Mode . "[{$active_str[$lang]}]";
		}
		else if($Mode_List[2] == 1)
		{
			$Unit_Mode = $Unit_Mode . "[{$pause_str[$lang]}]";
		}
		else
		{
			//
		}
		
		return $Unit_Mode;
	}
	
	/*
	 * 说明：获取UNIT的写缓存策略状态
	 * 参数：unit id
	 * 返回：策略状态0-打开，1-未打开，-1-不支持，失败返回FALSE
	 */
	private function GetUnitWriteCacheState($unit_id)
	{
		$this->m_szLastErrorInfo = "";
		$Unit_Write_Cache_State = 0;

		if( IsIdOk($unit_id) === FALSE )
		{
			$this->m_szLastErrorInfo = U_ID_ERR;
			return FALSE;
		}
		exec(GET_UNIT_WRITE_CACHE_STATE . " " . $unit_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}
		$Unit_Write_Cache_State = trim($output[0]);
		return $Unit_Write_Cache_State;
	}
	
	/*
	 * 说明：获取UNIT的自动校验策略状态
	 * 参数：unit id
	 * 返回：策略状态0-打开，1-未打开，-1-不支持，失败返回FALSE
	 */
	private function GetUnitAutoVerifyPolicy($unit_id)
	{
		$this->m_szLastErrorInfo = "";
		$Unit_Auto_Verify_Policy = 0;

		if( IsIdOk($unit_id) === FALSE )
		{
			$this->m_szLastErrorInfo = U_ID_ERR;
			return FALSE;
		}
		exec(GET_UNIT_AUTO_VERIFY_POLICY . " " . $unit_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}
		$Unit_Auto_Verify_Policy = trim($output[0]);
		return $Unit_Auto_Verify_Policy;
	}
	
	/*
	 * 说明：获取UNIT的ECC策略状态
	 * 参数：unit id
	 * 返回：策略状态0-打开，1-未打开，-1-不支持，失败返回FALSE
	 */
	private function GetUnitEccPolicy($unit_id)
	{
		$this->m_szLastErrorInfo = "";
		$Unit_Ecc_Policy = 0;

		if( IsIdOk($unit_id) === FALSE )
		{
			$this->m_szLastErrorInfo = U_ID_ERR;
			return FALSE;
		}
		exec(GET_UNIT_ECC_POLICY . " " . $unit_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}
		$Unit_Ecc_Policy = trim($output[0]);
		return $Unit_Ecc_Policy;
	}
	
	/*
	 * 说明：获取UNIT的队列策略状态
	 * 参数：unit id
	 * 返回：策略状态0-打开，1-未打开，-1-不支持，失败返回FALSE
	 */
	private function GetUnitQueueMode($unit_id)
	{
		$this->m_szLastErrorInfo = "";
		$Unit_Queue_Mode = 0;

		if( IsIdOk($unit_id) === FALSE )
		{
			$this->m_szLastErrorInfo = U_ID_ERR;
			return FALSE;
		}
		exec(GET_UNIT_QUEUE_MODE . " " . $unit_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}
		$Unit_Queue_Mode = trim($output[0]);
		return $Unit_Queue_Mode;
	}
	
	/*
	 * 说明：获取UNIT的存储策略状态
	 * 参数：unit id
	 * 返回：0-保护（Protection）,1-平衡(Balance)，2-性能(Performance)，-1-不支持，失败返回FALSE
	 */
	private function GetUnitStorsaveMode($unit_id)
	{
		$this->m_szLastErrorInfo = "";
		$Unit_Storsave_Mode = 0;

		if( IsIdOk($unit_id) === FALSE )
		{
			$this->m_szLastErrorInfo = U_ID_ERR;
			return FALSE;
		}
		exec(GET_UNIT_STORSAVE_MODE . " " . $unit_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}
		$Unit_Storsave_Mode = trim($output[0]);
		return $Unit_Storsave_Mode;
	}
	
	/*
	 * 说明：获取UNIT的快速RAID修复策略状态
	 * 参数：unit id
	 * 返回：0-重建（Rebuild），1-所有（All）,2-未打开，-1-不支持，失败返回FALSE
	 */
	private function GetUnitRapidRecoveryControl($unit_id)
	{
		$this->m_szLastErrorInfo = "";
		$Unit_Rapid_Recovery_Control = 0;

		if( IsIdOk($unit_id) === FALSE )
		{
			$this->m_szLastErrorInfo = U_ID_ERR;
			return FALSE;
		}
		exec(GET_UNIT_RAPID_RECOVERY_CONTROL . " " . $unit_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}
		$Unit_Rapid_Recovery_Control = trim($output[0]);
		return $Unit_Rapid_Recovery_Control;
	}
	
}
?>