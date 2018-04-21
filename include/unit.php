<?php
/*
 * ˵����1�� ��ȡ������Ϣ������ID�б����̱�ţ��������ͣ����̹̼��汾�š�WWN���������ʡ�SMART���ݵ�
 * 		 2����λ����
 * 		 3����ȡ���´�����Ϣ
 * 		 4������UNIT
 * 
 * ������֧��   ��3ware 9690SAϵ�п��ƿ�
 * �ײ�API�汾��9.5.2
 * 
 */
require_once("function.php");

$lang=load_lang();

$ok_str=array(
	"OK",
	"OK"
);
$verifying_str=array(
	"У��",
	"Verifying"
);
$initializing_str=array(
	"��ʼ��",
	"Initializing"
);
$degraded_str=array(
	"����",
	"Degraded"
);
$rebuilding_str=array(
	"�ؽ�",
	"Rebuilding"
);
$recovery_str=array(
	"�޸�",
	"Recovery"
);
$migrating_str=array(
	"ת��",
	"Migrating"
);
$inoperable_str=array(
	"���ɲ���",
	"Inoperable"
);
$unknown_str=array(
	"δ֪",
	"Unknown"
);
$active_str=array(
	"������",
	"active"
);
$pause_str=array(
	"��ͣ",
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
// �洢ģʽ
define('STORSAVE_PROTECTION', 0);
define('STORSAVE_BALANCE', 1);
define('STORSAVE_PERFORMANCE', 2);
// ����RAID�޸�ģʽ
define('RRC_REBUILD', 0);
define('RRC_ALL', 1);
define('RRC_DISABLE', 2);

Class Unit
{
// -----------------------------------------------��Ա��������

	//��¼���µĴ�����Ϣ
	private $m_szLastErrorInfo;
	
// -----------------------------------------------���г�Ա��������
	
	function __construct()
	{
		$this->m_szLastErrorInfo = "";
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
	 * ˵������ȡUint ID�б�
	 * ������������ID
	 * ���أ�Unit ID�б�ʧ�ܷ���FALSE
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
	 * ˵��������unit number��ȡunit id
	 * ������controller id�� unit number
	 * ���أ�unit id��ʧ�ܷ���FALSE
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
	 * ˵������ȡUNIT������Ϣ
	 * ������Unit ID
	 * ���أ�������Ϣ�б�ʧ�ܷ���FALSE
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
	 * ˵������ȡUNIT������Ϣ
	 * ������Unit ID
	 * ���أ�������Ϣ�б�ʧ�ܷ���FALSE
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
	 * ˵������λUNIT
	 * ������unit id
	 * ���أ��ɹ�����TRUE�����򷵻�FALSE
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
	 * ˵����У��unit
	 * ������unit id
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
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
	 * ˵����ֹͣУ��unit
	 * ������unit id
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
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
	 * ˵����ɾ��unit
	 * ������unit id
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
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
	 * ˵��������unit��д����
	 * ������unit id
	 * 		 $mode:TRUE-������Ĭ�ϣ���FALSE-�ر�, ����-�ر�
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
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
	 * ˵��������unit���Զ�У��
	 * ������unit id
	 * 		 $mode:TRUE-������Ĭ�ϣ���FALSE-�ر�, ����-�ر�
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
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
	 * ˵��������unit�Ķ���
	 * ������unit id
	 * 		 $mode:TRUE-������Ĭ�ϣ���FALSE-�ر�, ����-�ر�
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
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
	 * ˵��������unit��ecc
	 * ������unit id
	 * 		 $mode:TRUE-������Ĭ�ϣ���FALSE-�ر�, ����-�ر�
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
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
	 * ˵��������unit�Ĵ洢����
	 * ������unit id
	 * 		 $mode: STORSAVE_PROTECTION  - ����ģʽ��Ĭ�ϣ�
	 * 			    STORSAVE_BALANCE     - ƽ��ģʽ
	 * 			    STORSAVE_PERFORMANCE - ����ģʽ
	 * 			          ����                                               - ����ģʽ 
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
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
	 * ˵��������unit�Ŀ���RAID�޸�ģʽ
	 * ������unit id
	 * 		 $mode: RRC_REBUILD  - �ؽ�
	 * 			    RRC_ALL      - ���У�Ĭ�ϣ�
	 * 			    RRC_DISABLE  - ������
	 * 			          ����                          - ���� 
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
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
		//0-�ؽ�Rebuild��1-����All, 2-δ��Disable
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
	 * ˵��������unit����
	 * ������unit id
	 * 		 name��unit����
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
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
	 * ˵�����ؽ�unit
	 * ������
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
	 */
	function RebuildUnit()
	{
		$this->m_szLastErrorInfo = "";
		
		return TRUE;
	}
	
	/*
	 * ˵��������UNIT
	 * ������$drv_id_file���������ID�б���ļ�·��
	 * 		 $unit_params_file������Ҫ������UNIT����ز���
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
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
		
		// ɾ���ļ�
		unlink($drv_id_file);
		unlink($unit_params_file);
		return TRUE;
	}
	
	
// -----------------------------------------------˽�г�Ա��������

	/*
	 * ˵������ȡUNIT���
	 * ������unit id
	 * ���أ�unit��ţ�ʧ�ܷ���FALSE
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
	 * ˵������ȡUNIT����
	 * ������unit id
	 * ���أ�unit���ƣ�ʧ�ܷ���FALSE
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
	 * ˵������ȡUNIT���к�
	 * ������unit id
	 * ���أ�unit���кţ�ʧ�ܷ���FALSE
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
	 * ˵������ȡUNIT����
	 * ������unit id
	 * ���أ�unit�����ַ�������1 TB��ʧ�ܷ���FALSE
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
	 * ˵������ȡUNIT���ã�RAID����
	 * ������unit id
	 * ���أ�unitRAID����ʧ�ܷ���FALSE
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
	 * ˵������ȡUNIT Stripe ��С
	 * ������unit id
	 * ���أ�stripe��С��KB����ʧ�ܷ���FALSE
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
	 * ˵������ȡUNIT�����Ĵ���ID�б�
	 * ������unit id
	 * ���أ�����ID�б�ʧ�ܷ���FALSE
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
	 * ˵������ȡUNIT�����Ĵ��̸���
	 * ������unit id
	 * ���أ����̸�����ʧ�ܷ���FALSE

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
	 * ˵������ȡUNIT״̬
	 * ������unit id
	 * ���أ�unit״̬��ʧ�ܷ���FALSE
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
	 * ˵������ȡUNIT��д�������״̬
	 * ������unit id
	 * ���أ�����״̬0-�򿪣�1-δ�򿪣�-1-��֧�֣�ʧ�ܷ���FALSE
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
	 * ˵������ȡUNIT���Զ�У�����״̬
	 * ������unit id
	 * ���أ�����״̬0-�򿪣�1-δ�򿪣�-1-��֧�֣�ʧ�ܷ���FALSE
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
	 * ˵������ȡUNIT��ECC����״̬
	 * ������unit id
	 * ���أ�����״̬0-�򿪣�1-δ�򿪣�-1-��֧�֣�ʧ�ܷ���FALSE
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
	 * ˵������ȡUNIT�Ķ��в���״̬
	 * ������unit id
	 * ���أ�����״̬0-�򿪣�1-δ�򿪣�-1-��֧�֣�ʧ�ܷ���FALSE
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
	 * ˵������ȡUNIT�Ĵ洢����״̬
	 * ������unit id
	 * ���أ�0-������Protection��,1-ƽ��(Balance)��2-����(Performance)��-1-��֧�֣�ʧ�ܷ���FALSE
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
	 * ˵������ȡUNIT�Ŀ���RAID�޸�����״̬
	 * ������unit id
	 * ���أ�0-�ؽ���Rebuild����1-���У�All��,2-δ�򿪣�-1-��֧�֣�ʧ�ܷ���FALSE
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