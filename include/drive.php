<?php
/*
 * 说明：1、 获取磁盘信息，磁盘ID列表，磁盘编号，磁盘类型，磁盘固件版本号、WWN、连接速率、SMART数据等
 * 		 2、定位磁盘
 * 		 3、获取最新错误信息
 * 
 * 控制器支持   ：3ware 9690SA系列控制卡
 * 底层API版本：9.5.2
 * 
 */
require_once("function.php");
$lang=load_lang();

$drive_prefix_str=array(
	"磁盘",
	"Drive"
);
$slot_prefix_str=array(
	"插槽",
	"Slot"
);

define('GET_DRIVE_ID_LIST'         , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_drive_id_list");
define('GET_DRIVE_NUMBER'          , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_drive_number");
define('GET_DRIVE_MODEL'           , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_drive_model");
define('GET_DRIVE_CAPACITY'        , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_drive_capacity");
define('GET_DRIVE_INTERFACE'       , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_drive_interface");
define('GET_DRIVE_SLOT_NUMBER'     , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_drive_slot_number");
define('GET_UNIT_OF_DRIVE'         , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_unit_of_drive");
define('GET_DRIVE_STATUS'          , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_drive_status");
define('IDENTIFY_DRIVE'            , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/identify_drive");
define('GET_DRIVE_IDENTIFY_STATUS' , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_drive_identify_status");
define('GET_DRIVE_FIRMWARE_VERSION', "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_drive_firmware_version");
define('GET_DRIVE_SERIAL_NUMBER'   , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_drive_serial_number");
define('GET_DRIVE_WWN'			   , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_drive_wwn");
define('GET_DRIVE_TEMPERATURE'     , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_drive_temperature");
define('GET_DRIVE_POWER_ON_HOURS'  , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_drive_power_on_hours");
define('GET_DRIVE_SPINDLE_SPEED'   , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_drive_spindle_speed");
define('GET_DRIVE_QUEUE_CAPABILITY', "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_drive_queue_capability");
define('GET_DRIVE_QUEUE_MODE'      , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_drive_queue_mode");
define('GET_DRIVE_LINK_CAPABILITY' , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_drive_link_capability");
define('GET_DRIVE_LINK_STATUS'     , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_drive_link_status");
define('GET_DRIVE_SMART_DATA'      , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_drive_smart_data");
define('GET_UNIT_ID_OF_DRIVE'      , "export LANG=C; /usr/bin/sudo /opt/vstor/bin/get_unit_id_of_drive");

//define('C_ID_ERR', "Controller ID Invalid");
define('D_ID_ERR', "Drive ID Invalid");

Class Drive
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
	 * 说明：获取连接到控制器的磁盘ID列表
	 * 参数：控制器ID
	 * 返回：磁盘ID列表，失败返回FALSE
	 * array(
	 * 		"1 2 3 4 5 6 7 8",
	 * 		"2 3 4 5 6 7 8 9",
	 * 		...
	 * );
	 */
	function GetDriveIdList( $controller_id )
	{
		$this->m_szLastErrorInfo = "";
		$Drive_Id_List = array();
		
		if( IsIdOk($controller_id) === FALSE )
		{
			$this->m_szLastErrorInfo = C_ID_ERR;
			return FALSE;
		}
		exec(GET_DRIVE_ID_LIST . " " . $controller_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}
		
		foreach( $output as $line )
		{
			$Drive_Id_List[] = trim( $line );
		}
		return $Drive_Id_List;
	}
	
	/*
	 * 说明：获取磁盘的基本信息
	 * 参数：磁盘ID
	 * 返回：磁盘基本信息列表，失败返回FALSE
	 * array(
	 * 	"id"=>"",
	 * 	"name"=>"",
	 * 	"model"=>"",
	 * 	"capacity"=>"",
	 * 	"type"=>"",
	 * 	"slot"=>"",
	 * 	"unit"=>"",
	 * 	"temperature"=>"",
	 * 	"status"=>"",
	 * );
	 */
	function GetDriveBasicInfo( $drive_id )
	{
		global $drive_prefix_str, $slot_prefix_str, $lang;
		$Drive_Basic_Info = array();
		
		if( IsIdOk($drive_id) === FALSE )
		{
			$this->m_szLastErrorInfo = D_ID_ERR;
			return FALSE;
		}
		$Drive_Basic_Info['id'] = $drive_id;
		// name
		$name = $this->GetDriveNumber($drive_id);
		if( $name === FALSE )
		{
			return FALSE;
		}
		$Drive_Basic_Info['name'] = $drive_prefix_str[$lang] . $name;
		// model
		$model = $this->GetDriveModel($drive_id);
		if( $model === FALSE )
		{
			return FALSE;
		}
		$Drive_Basic_Info['model'] = $model;
		// capacity
		$capacity = $this->GetDriveCapacity($drive_id);
		if( $capacity === FALSE )
		{
			return FALSE;
		}
		$Drive_Basic_Info['capacity'] = $capacity;
		// type
		$type = $this->GetDriveInterface($drive_id);
		if( $type === FALSE )
		{
			return FALSE;
		}
		$Drive_Basic_Info['type'] = $type;
		// slot
		$slot = $this->GetDriveSlotNumber($drive_id);
		if( $slot === FALSE )
		{
			return FALSE;
		}
		$Drive_Basic_Info['slot'] = $slot_prefix_str[$lang] . $slot;
		// unit
		$unit = $this->GetUnitOfDrive($drive_id);
		if( $unit === FALSE )
		{
			return FALSE;
		}		
		$Drive_Basic_Info['unit'] = $unit;
		// temperature
		$temperature = $this->GetDriveTemperature($drive_id);
		if( $temperature === FALSE )
		{
			return FALSE;
		}
		$Drive_Basic_Info['temperature'] = $temperature;
		// status
		$status = $this->GetDriveStatus($drive_id);
		if( $status === FALSE )
		{
			return FALSE;
		}
		$Drive_Basic_Info['status'] = $status;

		return $Drive_Basic_Info;
	}
	
	/*
	 * 说明：获取磁盘的详细信息
	 * 参数：磁盘ID
	 * 返回：磁盘详细信息列表，失败返回FALSE
	 * array(
	 * 	"id"=>"",
	 * 	"name"=>"",
	 * 	"model"=>"",
	 * 	"capacity"=>"",
	 * 	"type"=>"",
	 * 	"slot"=>"",
	 * 	"unit"=>"",
	 * 	"status"=>"",
	 * 	"firmware"=>"",
	 * 	"serial"=>"",
	 * 	"wwn"=>"",
	 * 	"temperature"=>"",
	 * 	"hours"=>"",
	 * 	"spindle"=>"",
	 * 	"queue_c"=>"",
	 * 	"queue_m"=>"",
	 * 	"link_c"=>"",
	 * 	"link_s"=>""
	 * );
	 */
	function GetDriveDetailInfo( $drive_id )
	{
		$Drive_Detail_Info = array();
		$Drive_Detail_Info = $this->GetDriveBasicInfo($drive_id);
		if( $Drive_Detail_Info === FALSE )
		{
			return FALSE;
		}
		
		// firmware
		$firmware = $this->GetDriveFirmwareVersion($drive_id);
		if( $firmware === FALSE )
		{
			return FALSE;
		}
		$Drive_Detail_Info['firmware'] = $firmware;
		// serial
		$serial = $this->GetDriveSerialNumber($drive_id);
		if( $serial === FALSE )
		{
			return FALSE;
		}
		$Drive_Detail_Info['serial'] = $serial;
		// wwn
		$wwn = $this->GetDriveWWN($drive_id);
		if( $wwn === FALSE )
		{
			return FALSE;
		}
		$Drive_Detail_Info['wwn'] = $wwn;
		// temperature
		$temperature = $this->GetDriveTemperature($drive_id);
		if( $temperature === FALSE )
		{
			return FALSE;
		}
		$Drive_Detail_Info['temperature'] = $temperature;
		// hours
		$hours = $this->GetDrivePowerOnHours($drive_id);
		if( $hours === FALSE )
		{
			return FALSE;
		}
		$Drive_Detail_Info['hours'] = $hours;
		// spindle
		$spindle = $this->GetDriveSpindleSpeed($drive_id);
		if( $spindle === FALSE )
		{
			return FALSE;
		}
		$Drive_Detail_Info['spindle'] = $spindle;
		// queue_c
		$queue_c = $this->GetDriveQueueCapability($drive_id);
		if( $queue_c === FALSE )
		{
			return FALSE;
		}
		$Drive_Detail_Info['queue_c'] = $queue_c;
		// queue_m
		$queue_m = $this->GetDriveQueueMode($drive_id);
		if( $queue_m === FALSE )
		{
			return FALSE;
		}
		$Drive_Detail_Info['queue_m'] = $queue_m;
		// link_c
		$link_c = $this->GetDriveLinkCapability($drive_id);
		if( $link_c === FALSE )
		{
			return FALSE;
		}
		$Drive_Detail_Info['link_c'] = $link_c;
		// link_s
		$link_s = $this->GetDriveLinkStatus($drive_id);
		if( $link_s === FALSE )
		{
			return FALSE;
		}
		$Drive_Detail_Info['link_s'] = $link_s;
		
		return $Drive_Detail_Info;
	}
	
	/*
	 * 说明：定位磁盘
	 * 参数：磁盘ID
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	function IdentifyDrive( $drive_id )
	{
		$this->m_szLastErrorInfo = "";

		if( IsIdOk($drive_id) === FALSE )
		{
			$this->m_szLastErrorInfo = D_ID_ERR;
			return FALSE;
		}
		exec(IDENTIFY_DRIVE . " " . $drive_id  . " >/dev/null &");
		return TRUE;
	}
	
	/*
	 * 说明：获取磁盘SMART数据
	 * 参数：磁盘ID
	 * 返回：SMART数据数组，比如“ 1.5 Gbps”，失败返回FALSE
	 */
	function GetDriveSmartData( $drive_id )
	{
		$this->m_szLastErrorInfo = "";
		$Drive_Smart_Data = array();

		if( IsIdOk($drive_id) === FALSE )
		{
			$this->m_szLastErrorInfo = D_ID_ERR;
			return FALSE;
		}
		exec(GET_DRIVE_SMART_DATA . " " . $drive_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}

		foreach( $output as $line )
		{
			$Drive_Smart_Data[] = trim( $line );
		}

		return $Drive_Smart_Data;
	}
	
	
	/*
	 * 说明：获取磁盘所属Raid组
	 * 参数：磁盘ID
	 * 返回：raid组编号，失败返回FALSE
	 */
	function GetUnitOfDrive( $drive_id )
	{
		$this->m_szLastErrorInfo = "";
		$Unit_Of_Drive = 0;

		if( IsIdOk($drive_id) === FALSE )
		{
			$this->m_szLastErrorInfo = D_ID_ERR;
			return FALSE;
		}
		exec(GET_UNIT_OF_DRIVE . " " . $drive_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}

		$Unit_Of_Drive = trim( $output[0] );
		// 不属于任何组
		if($Unit_Of_Drive == 255)
		{
			$Unit_Of_Drive = "";
		}

		return $Unit_Of_Drive;
	}
	
// -----------------------------------------------------------------------私有成员函数
	/*
	 * 说明：获取磁盘编号
	 * 参数：磁盘ID
	 * 返回：磁盘编号，失败返回FALSE
	 */
	private function GetDriveNumber( $drive_id )
	{
		$this->m_szLastErrorInfo = "";
		$Drive_Number = 0;

		if( IsIdOk($drive_id) === FALSE )
		{
			$this->m_szLastErrorInfo = D_ID_ERR;
			return FALSE;
		}
		exec(GET_DRIVE_NUMBER . " " . $drive_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}

		$Drive_Number = trim( $output[0] );

		return $Drive_Number;
	}
	
	/*
	 * 说明：获取磁盘型号
	 * 参数：磁盘ID
	 * 返回：磁盘型号字符串，失败返回FALSE
	 */
	private function GetDriveModel( $drive_id )
	{
		$this->m_szLastErrorInfo = "";
		$Drive_Model = 0;

		if( IsIdOk($drive_id) === FALSE )
		{
			$this->m_szLastErrorInfo = D_ID_ERR;
			return FALSE;
		}
		exec(GET_DRIVE_MODEL . " " . $drive_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}

		$Drive_Model = trim( $output[0] );

		return $Drive_Model;
	}
	
	/*
	 * 说明：获取磁盘容量
	 * 参数：磁盘ID
	 * 返回：磁盘容量大小字符串，如200 GB，失败返回FALSE
	 */
	private function GetDriveCapacity( $drive_id )
	{
		$this->m_szLastErrorInfo = "";
		$Drive_Capacity = "";

		if( IsIdOk($drive_id) === FALSE )
		{
			$this->m_szLastErrorInfo = D_ID_ERR;
			return FALSE;
		}
		exec(GET_DRIVE_CAPACITY . " " . $drive_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}

		$Drive_Capacity = format_bytesize( trim($output[0]) );
		return $Drive_Capacity;
	}
	
	/*
	 * 说明：获取磁盘接口型号
	 * 参数：磁盘ID
	 * 返回：磁盘接口型号字符串，如SATA，失败返回FALSE
	 */
	private function GetDriveInterface( $drive_id )
	{
		$this->m_szLastErrorInfo = "";
		$Drive_Interface = "";

		if( IsIdOk($drive_id) === FALSE )
		{
			$this->m_szLastErrorInfo = D_ID_ERR;
			return FALSE;
		}
		exec(GET_DRIVE_INTERFACE . " " . $drive_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}

		$Drive_Interface = trim( $output[0] );

		return $Drive_Interface;
	}
	
	/*
	 * 说明：获取磁盘插槽号
	 * 参数：磁盘ID
	 * 返回：磁盘插槽号，失败返回FALSE
	 */
	private function GetDriveSlotNumber( $drive_id )
	{
		$this->m_szLastErrorInfo = "";
		$Drive_Slot_Number = 0;

		if( IsIdOk($drive_id) === FALSE )
		{
			$this->m_szLastErrorInfo = D_ID_ERR;
			return FALSE;
		}
		exec(GET_DRIVE_SLOT_NUMBER . " " . $drive_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}

		$Drive_Slot_Number = trim( $output[0] );

		return $Drive_Slot_Number;
	}
	
	/*
	 * 说明：获取磁盘状态
	 * 参数：磁盘ID
	 * 返回：磁盘状态字符串，失败返回FALSE
	 */
	private function GetDriveStatus( $drive_id )
	{
		$this->m_szLastErrorInfo = "";
		$Drive_Status = 0;

		if( IsIdOk($drive_id) === FALSE )
		{
			$this->m_szLastErrorInfo = D_ID_ERR;
			return FALSE;
		}
		exec(GET_DRIVE_STATUS . " " . $drive_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}

		$Drive_Status = trim( $output[0] );

		return $Drive_Status;
	}
	
	/*
	 * 说明：获取磁盘定位状态
	 * 参数：磁盘ID
	 * 返回：磁盘定位状态字符串On/Off，失败返回FALSE
	 */
	private function GetDriveIdentifyStatus( $drive_id )
	{
		$this->m_szLastErrorInfo = "";
		$Drive_Identify_Status = "";

		if( IsIdOk($drive_id) === FALSE )
		{
			$this->m_szLastErrorInfo = D_ID_ERR;
			return FALSE;
		}
		exec(GET_DRIVE_IDENTIFY_STATUS . " " . $drive_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}

		$Drive_Identify_Status = trim( $output[0] );

		return Drive_Identify_Status;
	}
	
	/*
	 * 说明：获取磁盘固件版本
	 * 参数：磁盘ID
	 * 返回：磁盘固件版本字符串，失败返回FALSE
	 */
	private function GetDriveFirmwareVersion( $drive_id )
	{
		$this->m_szLastErrorInfo = "";
		$Drive_Firmware_Version = "";

		if( IsIdOk($drive_id) === FALSE )
		{
			$this->m_szLastErrorInfo = D_ID_ERR;
			return FALSE;
		}
		exec(GET_DRIVE_FIRMWARE_VERSION . " " . $drive_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}

		$Drive_Firmware_Version = trim( $output[0] );

		return $Drive_Firmware_Version;
	}
	
	/*
	 * 说明：获取磁盘序列号
	 * 参数：磁盘ID
	 * 返回：磁盘序列号字符串，失败返回FALSE
	 */
	private function GetDriveSerialNumber( $drive_id )
	{
		$this->m_szLastErrorInfo = "";
		$Drive_Serial_Number = "";

		if( IsIdOk($drive_id) === FALSE )
		{
			$this->m_szLastErrorInfo = D_ID_ERR;
			return FALSE;
		}
		exec(GET_DRIVE_SERIAL_NUMBER . " " . $drive_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}

		$Drive_Serial_Number = trim( $output[0] );

		return $Drive_Serial_Number;
	}
	
	/*
	 * 说明：获取磁盘WWN
	 * 参数：磁盘ID
	 * 返回：磁盘WWN字符串，失败返回FALSE
	 */
	private function GetDriveWWN( $drive_id )
	{
		$this->m_szLastErrorInfo = "";
		$Drive_WWN = "";

		if( IsIdOk($drive_id) === FALSE )
		{
			$this->m_szLastErrorInfo = D_ID_ERR;
			return FALSE;
		}
		exec(GET_DRIVE_WWN . " " . $drive_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}

		$Drive_WWN = trim( $output[0] );

		return $Drive_WWN;
	}
	
	/*
	 * 说明：获取磁盘温度
	 * 参数：磁盘ID
	 * 返回：磁盘温度字符串，失败返回FALSE
	 */
	private function GetDriveTemperature( $drive_id )
	{
		$this->m_szLastErrorInfo = "";
		$Drive_Temperature = "";

		if( IsIdOk($drive_id) === FALSE )
		{
			$this->m_szLastErrorInfo = D_ID_ERR;
			return FALSE;
		}
		exec(GET_DRIVE_TEMPERATURE . " " . $drive_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}

		$Drive_Temperature = trim( $output[0] ) . " ℃";

		return $Drive_Temperature;
	}
	
	/*
	 * 说明：获取磁盘上电小时数
	 * 参数：磁盘ID
	 * 返回：磁盘上电小时数，失败返回FALSE
	 */
	private function GetDrivePowerOnHours( $drive_id )
	{
		$this->m_szLastErrorInfo = "";
		$Drive_Power_On_Hours = 0;

		if( IsIdOk($drive_id) === FALSE )
		{
			$this->m_szLastErrorInfo = D_ID_ERR;
			return FALSE;
		}
		exec(GET_DRIVE_POWER_ON_HOURS . " " . $drive_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}

		$Drive_Power_On_Hours = trim( $output[0] );

		return $Drive_Power_On_Hours;
	}
	
	/*
	 * 说明：获取磁盘转轴速度
	 * 参数：磁盘ID
	 * 返回：磁盘转轴速度字符串，失败返回FALSE
	 */
	private function GetDriveSpindleSpeed( $drive_id )
	{
		$this->m_szLastErrorInfo = "";
		$Drive_Spindle_Speed = 0;

		if( IsIdOk($drive_id) === FALSE )
		{
			$this->m_szLastErrorInfo = D_ID_ERR;
			return FALSE;
		}
		exec(GET_DRIVE_SPINDLE_SPEED . " " . $drive_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}

		$Drive_Spindle_Speed = trim( $output[0] ) . " RPM";

		return $Drive_Spindle_Speed;
	}
	
	/*
	 * 说明：获取磁盘是否支持队列
	 * 参数：磁盘ID
	 * 返回：Yes/No，失败返回FALSE
	 */
	private function GetDriveQueueCapability( $drive_id )
	{
		$this->m_szLastErrorInfo = "";
		$Drive_Queue_Capability = "";

		if( IsIdOk($drive_id) === FALSE )
		{
			$this->m_szLastErrorInfo = D_ID_ERR;
			return FALSE;
		}
		exec(GET_DRIVE_QUEUE_CAPABILITY . " " . $drive_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}

		$Drive_Queue_Capability = trim( $output[0] );

		return $Drive_Queue_Capability;
	}
	
	/*
	 * 说明：获取磁盘是队列是否打开
	 * 参数：磁盘ID
	 * 返回：Yes/No，失败返回FALSE
	 */
	private function GetDriveQueueMode( $drive_id )
	{
		$this->m_szLastErrorInfo = "";
		$Drive_Queue_Mode = "";

		if( IsIdOk($drive_id) === FALSE )
		{
			$this->m_szLastErrorInfo = D_ID_ERR;
			return FALSE;
		}
		exec(GET_DRIVE_QUEUE_MODE . " " . $drive_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}

		$Drive_Queue_Mode = trim( $output[0] );

		return $Drive_Queue_Mode;
	}
	
	/*
	 * 说明：获取磁盘连接速率支持
	 * 参数：磁盘ID
	 * 返回：支持的链接速率字符串，比如“ 1.5 Gbps”，失败返回FALSE
	 */
	private function GetDriveLinkCapability( $drive_id )
	{
		$this->m_szLastErrorInfo = "";
		$Drive_Link_Capability = "";

		if( IsIdOk($drive_id) === FALSE )
		{
			$this->m_szLastErrorInfo = D_ID_ERR;
			return FALSE;
		}
		exec(GET_DRIVE_LINK_CAPABILITY . " " . $drive_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}

		$Drive_Link_Capability = trim( $output[0] );

		return $Drive_Link_Capability;
	}
	
	/*
	 * 说明：获取磁盘连接速率
	 * 参数：磁盘ID
	 * 返回：连接速率字符串，比如“ 1.5 Gbps”，失败返回FALSE
	 */
	private function GetDriveLinkStatus( $drive_id )
	{
		$this->m_szLastErrorInfo = "";
		$Drive_Link_Status = "";

		if( IsIdOk($drive_id) === FALSE )
		{
			$this->m_szLastErrorInfo = D_ID_ERR;
			return FALSE;
		}
		exec(GET_DRIVE_LINK_STATUS . " " . $drive_id, $output, $retval);
		if( $retval !== 0 )
		{
			$this->m_szLastErrorInfo = trim($output[0]);
			return FALSE;
		}

		$Drive_Link_Status = trim( $output[0] );

		return $Drive_Link_Status;
	}

	
}

?>



