<?php

require_once("function.php");
require_once("./include/iscsi_target.php");
require_once("./include/disk.php");

/*
 * 说明：
 * 1、GetDiskDevices函数：扫描非挂载、非物理卷的磁盘
 * 2、LVM处理相关类：物理卷类、逻辑卷组类、逻辑卷类
 * 
 * created by 王大典, 2009-11-09
 */


/*
 * lvm相关shell命令
 */
define('CMD_LVM', "export LANG=C; /usr/bin/sudo /sbin/lvm ");

define('CMD_PVS', CMD_LVM . " pvs "); 
define('CMD_VGS', CMD_LVM . " vgs ");
define('CMD_LVS', CMD_LVM . " lvs ");

define('CMD_PVCREATE', CMD_LVM . " pvcreate ");
define('CMD_VGCREATE', CMD_LVM . " vgcreate ");
define('CMD_LVCREATE', CMD_LVM . " lvcreate ");

define('CMD_PVREMOVE', CMD_LVM . " pvremove ");
define('CMD_VGREMOVE', CMD_LVM . " vgremove ");
define('CMD_LVREMOVE', CMD_LVM . " lvremove ");

$lang=load_lang();
$disk_prefix_str=array(
	"磁盘",
	"disk"
);
$volume_prefix_str=array(
	"卷",
	"volume"
);

/*
 * 说明：扫描磁盘，获取非挂载、非物理卷的磁盘
 * 
 * 返回：磁盘列表;如果没有找到磁盘，则返回FALSE.
 * 
 * created by 王大典, 2009-11-09
 */
function GetDiskDevices()
{
	global $disk_prefix_str;
	global $lang;
	$diskList = array();// 所有的磁盘
	$diskEntry = array(
/*
			"name"=>"磁盘0",
			"path"=>"/dev/sda",
			"size"=>"1024 GB"
*/
	); // 单个磁盘
	
	$diskListToPv = array(
/*		
  		array(//$diskEntry
			"name"=>"磁盘0",
			"path"=>"/dev/sda",
			"size"=>"1024 GB"
			),
		...
*/
	); // 可作为物理磁盘的磁盘，用作返回
	$fp = popen("ls /sys/block/ | grep '[s|h]d\([a-z]\{1,\}\)'", 'r');
	while( !feof($fp) )
	{
		$tmp = trim( fgets($fp) );// 去掉左右空格及最后的换行符(\n, \r)
		if( $tmp != "" )//最后多出一个空行，在此过滤。
		{
			$diskList[] = $tmp;
		}
	}
	
	$index = 0;
	foreach($diskList as $disk)
	{
		// 检查是否已挂载
		exec("mount | grep \"" . $disk . "\"", $output, $retval);
		if( $retval == 0 )// 已挂载
		{
			continue;
		}
		//检查是否已经作为物理卷
		// pvs --noheadings --separator " " | grep "sdb"
		$command = CMD_PVS . "--noheadings | grep  $disk";
		unset($output);
		exec($command, $output, $retval);
		if( (isset($output[0]) && trim($output[0])!="") )
		{
			continue;
		}
		// 磁盘名称
		$diskEntry["name"] = $disk_prefix_str[$lang] . $index;
		// 获取磁盘路径
		$diskEntry['path'] = "/dev/" . $disk;
		// 检查磁盘来源，如果是iscsi则剔除
		$disk_origintype = GetDiskOriginType($diskEntry['path']);
		if($disk_origintype == "iscsi")
		{
			continue;
		}
		// 获取磁盘大小
		//    8     0  312571224 sda
		$command = "export LANG=C; /bin/cat /proc/partitions |grep $disk";
		unset($output);
		exec($command, $output, $retval);
		if( $retval!=0 || !isset($output[0]) )
		{
			continue;//没有此磁盘的大小信息，可能是光驱。剔除
		}
		$output = preg_split("/\s+/", trim($output[0] ));
		$diskEntry['size'] = format_bytesize($output[2] * 1024);
		$index++;
		$diskListToPv[] = $diskEntry;
	}

	// 如果没有找到磁盘则返回FALSE
/*	if( count($diskListToPv) == 0 )
	{
		return FALSE;
	}
*/
	
	return $diskListToPv;
}

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////
/*
 *  说明：物理卷类
 * 		1、获取物理卷列表
 * 		2、创建、删除物理卷
 * 
 * 	created by 王大典, 2009-11-09
 */
class PhysicalVolume
{
	private $pv_list=array(
		/*
		 array(
		 	"name"=>"磁盘0"
		 	"disk"=>"sda",
		 	"device"=>"/dev/sda",
		 	"size"=>"1024 MB",
		 	"vg"="vg0"
		 ),
		 ...
		 */
	);
	
	// 构造函数
	function __construct()
	{
		$this->RefreshPvList();
	}
	
	/*
	 * 说明：新建物理磁盘（physical volume）
	 * 参数：$device: 磁盘路径，如/dev/sda
	 * 返回：成功-TRUE，失败-FALSE
	 */
	function CreatePv($device)
	{
		exec(CMD_PVCREATE . $device, $output, $retval);
		if( $retval != 0 )
		{
			return FALSE;
		}

		return TRUE;
	}
	
	/*
	 * 说明：刷新物理卷列表（在创建、删除后使用，否则会使GetPvList获取的列表有误）
	 * 参数：无
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	function RefreshPvList()
	{
		if ($this->ListPv() !== FALSE)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	/*
	 * 说明：获取物理卷列表（路径、大小、所属卷组）
	 * 参数：无
	 * 返回：成功返回物理卷列表（如果没有成员，则为空数组），失败返回FALSE
	 */
	function GetPvList()
	{
		return $this->pv_list;
	}
	
	/*
	 * 说明：删除物理卷
	 * 参数：无
	 * 返回：成功-TRUE，失败-FALSE
	 */
	function RemovePv($pv)
	{
		exec(CMD_PVREMOVE . $pv, $output, $retval);
		if( $retval != 0 )
		{
			return FALSE;
		}

		return TRUE;
	}
	
	/*
	 * 说明：是否是物理卷
	 * 参数：$disk，如/dev/sda
	 * 返回：是物理卷则返回TRUE，否则返回FALSE
	 */
	function IsPv($disk)
	{
		if($this->pv_list === FALSE)
		{
			return FALSE;
		}
		
		foreach($this->pv_list as $pv)
		{
			if($disk == $pv['device'])
			{
				return TRUE;
			}
		}
		
		return FALSE;
	}
	
	
	//////////////////////////////////
	// private
	private function ListPv()
	{
		$this->pv_list = array();
		exec(CMD_PVS . "--noheadings --separator \";\"", $output, $retval);
		if ( $retval != 0 )
		{
			return FALSE;
		}
		global $volume_prefix_str;
		global $lang;
		$index = 0;
		foreach($output as $line)
		{
			// /dev/sdc vg0 lvm2 a- 596.02G 496.02G
			$line = trim($line);
			if($line == "")
			{
				continue;
			}
			$pv_info = array();
			$pv = array();
			$pv_info = split(";", $line);
			$pv['name'] = $volume_prefix_str[$lang] . $index;
			$pv['device'] = $pv_info[0];
			$pv['disk'] = substr( strrchr($pv['device'], "/"), 1 );
			$pv['size'] = $pv_info[4];
			$pv['vg'] = $pv_info[1];
			$index++;

			$this->pv_list[] = $pv;
		}
		
		return TRUE;
	}
}


///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

/* 
 * 说明：逻辑卷类
 * 1、创建、删除逻辑卷
 * 2、获取逻辑卷列表（名称、大小、所属逻辑卷组名称）
 * 
 * created by 王大典, 2009-11-09
 */
class LogicVolume
{
	private $lv_list = array(
/*
		array(
			"name"=>"lv0",
			"path"=>"/dev/vg0/lv0",
			"origin"=>"volume",
			"mapper"=>"/dev/mapper/vg0-lv0",
			"mountdir"=>"/mnt/lv0",
			"size"=>"1024 MB",
			"free"=>"512 MB",
			"usage"=>"50%",
			"vg"=>"vg0"
		),
		...
 */
	);
	
	// 已作为iscsi-target的逻辑卷列表
	private $lv_list_of_it = array(
/*
  		array(
			"name"=>"lv0",
			"path"=>"/dev/vg0/lv0",
			"size"=>"1024 MB",
			"vg"=>"vg0",
	  		"it"=>"iqn.123"
  		),
  		...
 */
	);
	
	
	// 构造函数
	function __construct()
	{
		
	}
	
	/*
	 * 说明：创建逻辑卷
	 * 参数：$lv_name:逻辑卷名称
	 * 		 $lv_size:逻辑卷大小，单位为MB
	 * 		 $vg_name:逻辑卷组名称
	 * 返回：成功返回TRUE，否则返回FALSE
	 */
	function CreateLv($lv_name, $lv_size, $vg_name)
	{
		// 检查名称合法性
		if ( ! IsLvmNameOk($lv_name) )
		{
			return FALSE;
		}
		// 处理逻辑卷大小
		// 检查是否输入数字
		if(preg_match("/[^0-9\.]/", $lv_size)>0)
		{
			return FALSE;
		}
		$lv_size = $lv_size . "M";
		
		// 命令执行
		$command = CMD_LVCREATE . "-L " . $lv_size . " -n " . $lv_name . " " . $vg_name;
		exec($command, $output, $retval);
		if( $retval != 0 )
		{
			return FALSE;
		}

		return TRUE;
	}
	
	/*
	 * 说明：获取所有的逻辑卷列表
	 * 参数：无
	 * 返回：成功返回逻辑卷列表（如果没有成员，则返回空数组），否则返回FALSE
	 */
	function GetLvList()
	{
		if( ! $this->ListLv() )
		{
			return FALSE;
		}
		else
		{
			return $this->lv_list;
		}
	}
	
	/*
	 * 说明：删除逻辑卷
	 * 参数：$lv_name:逻辑卷名称
	 * 		 $vg_name:所属逻辑卷组名称
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	function RemoveLv($lv_name, $vg_name)
	{
		$command = CMD_LVREMOVE . "-f " . $vg_name . "/" . $lv_name; 
		exec($command, $output, $retval);
		if ( $retval != 0 )
		{
			return FALSE;
		}
		
		return TRUE;
	}
	
	/*
	 * 说明：根据路径获取逻辑卷的大小
	 * 参数：$path：逻辑卷路径，/dev/vg0/lv0
	 * 返回：成功返回大小字符串，失败返回FALSE
	 */
	function GetLvSizeByPath($path)
	{
		if( ! $this->lv_list )
		{
			if( $this->GetLvList() === FALSE)
			{
				return FALSE;
			}
		}
		
		foreach($this->lv_list as $lv)
		{
			if( $lv['path'] == $path)
			{
				return $lv['size'];
			}
		}
		
		return FALSE;
	}
	
	/*
	 * 说明：检查某一个逻辑卷是否已经作为iscsi-target的lun了
	 * 参数：$vg_name：卷组名称
	 * 		 $lv_name：逻辑卷名称
	 * 返回：是则返回TRUE，否则返回FALSE
	 */
	function IsAsItLun($vg_name, $lv_name)
	{
		if( ! $this->lv_list_of_it )
		{
			if( $this->ListLvOfIt() === FALSE )
			{
				return FALSE;
			}
		}
		
		foreach( $this->lv_list_of_it as $lv )
		{
			if( $lv['vg']==$vg_name && $lv['name']==$lv_name )
			{
				return TRUE;
			}
		}
		
		return FALSE;
	}

	/*
	 * 说明：获取LV对应的设备，/dev/vg0/vg0_0 -> /dev/mapper/vg0-vg0_0
	 * 参数：逻辑卷路径，/dev/vg0/vg0_1
	 * 返回：返回对应设备，失败返回FALSE
	 */
	function GetLvMapperDev($lv)
	{
		// 获取lv的对应设备，/dev/mapper/...
		$command = "export LANG=C; /usr/bin/sudo /bin/ls -l " . $lv;
		exec($command, $output, $retval);
		if( $retval !== 0 )
		{
			return FALSE;
		}
		
		if( ! isset($output[0]) )
		{
			return FALSE;
		}

		if( preg_match("|{$lv}\s+->\s+(.*)$|i", trim($output[0]), $match) )
		{
			return $match[1];
		}
		
		return FALSE;
	}
	

	/*
	 * 说明：获取LV的预读扇区个数（每个扇区512Byte）
	 * 参数：逻辑卷路径，/dev/vg0/vg0_1
	 * 返回：成功返回大小，失败返回FALSE
	 */
	function GetLvRa($lv)
	{
		return GetDiskRa($lv);
	}
	
	/*
	 * 说明：设置预读扇区个数
	 * 参数：$lv: 磁盘，如/dev/sda
	 *       $ra：预读的扇区个数（每个扇区512字节）
	 * 返回：成功返回TRUE，否则返回FALSE
	 */
	function SetLvRa($lv, $ra)
	{
		return 	SetDiskRa($lv, $ra);
	}
	
	//////////////////////////////////////
	// private
		/* 
	 * 说明：获取lv的挂载属性
	 * 参数：逻辑卷路径，/dev/vg0/vg0_1
	 * 返回：返回lv的挂载属性，如果lv没有挂载返回FALSE
				array(
					"path"=>"/dev/vg0/lv0",
					"mountdir"=>"/mnt/lv0",
					"size"=>"1024 MB",
					"free"=>"512 MB",
					"usage"=>"50%"
				)
	 */
	private function GetLvMountedAttrbute($lv)
	{
		$lv_mnt_attr = array(
						"path"=>$lv,
						"mountdir"=>"",
						"size"=>"",
						"free"=>"",
						"usage"=>""
						);
		$is_get = FALSE;
		$lv_mapper_dev = $this->GetLvMapperDev($lv);
		if( $lv_mapper_dev === FALSE )
		{
			return FALSE;
		}
		/*
			Filesystem           1K-blocks      Used Available Use% Mounted on
			/dev/sda1            150038000  38156100 111881900  26% /
			tmpfs                  1686704         0   1686704   0% /dev/shm
			/dev/mapper/ISCSI_VG0-ISCSI_VG0_LV0
			                     1073610752      5152 1073605600   1% /mnt/disk
			/dev/mapper/1-1_1    2147352576      5152 2147347424   1% /mnt/disk1
		*/
		
		$command = "export LANG=C; /usr/bin/sudo /bin/df -h ";
		exec($command, $output, $retval);
		if($retval !== 0)
		{
			return FALSE;
		}
		
		reset($output);
		while( TRUE )
		{
			$line = current($output);
			if( $line === FALSE )
			{
				break;
			}
			/* 
			 * 匹配：
			 * 	/dev/mapper/ISCSI_VG0-ISCSI_VG0_LV0
			 *                   1073610752      5152 1073605600   1% /mnt/disk
			 */
			if( preg_match("|^{$lv_mapper_dev}$|i", trim($line)) )
			{
				$line_next = next($output);
				if( $line_next === FALSE )
				{
					return FALSE;
				}
				if( preg_match("|^([0-9\.]+[TGMKB]+)\s+([0-9\.]+[TGMKB]+)\s+([0-9\.]+[TGMKB]+)\s+([0-9\.]+%)\s+(.*)$|i",
								 trim($line_next), $match) )
				{
					$lv_mnt_attr['size'] = $match[1];
					$lv_mnt_attr['free'] = $match[3];
					$lv_mnt_attr['usage'] = $match[4];
					$lv_mnt_attr['mountdir'] = $match[5];
					$is_get = TRUE;
					break;
				}
			}
			/* 
			 * 匹配：
			 * /dev/mapper/1-1_1    2147352576      5152 2147347424   1% /mnt/disk1
			 */
			else if( preg_match("|^{$lv_mapper_dev}([0-9\.]+[TGMKB]+)\s+([0-9\.]+[TGMKB]+)\s+([0-9\.]+[TGMKB]+)\s+([0-9\.]+%)\s+(.*)$|i",  
									trim($line), $match) )
			{
				$lv_mnt_attr['size'] = $match[1];
				$lv_mnt_attr['free'] = $match[3];
				$lv_mnt_attr['usage'] = $match[4];
				$lv_mnt_attr['mountdir'] = $match[5];
				$is_get = TRUE;
				break;
			}
			else
			{
				;
			}
			$line = next($output);
		}
		
		if( $is_get === FALSE )
		{
			return FALSE;
		}
		
		return $lv_mnt_attr;
	}
	
	private function ListLv()
	{
		global $volume_prefix_str;
		global $lang;
		$this->lv_list = array();
		exec(CMD_LVS . "--noheadings --separator \";\"", $output, $retval);
		if( $retval != 0 )
		{
			return FALSE;
		}
		
		foreach( $output as $line )
		{
			// lv0 vg0 -wi-a- 100.00G
			// lv1 vg1 -wi-a- 100.00G
			
			$line = trim($line);
			if($line == "")
			{
				continue;
			}
			$lv_info = array();
			$lv = array();
			$lv_info = split(";", $line);
			
			$lv['name'] = $lv_info[0];
			$lv['size'] = $lv_info[3];
			$lv['vg']   = $lv_info[1];
			$lv['origin'] = $volume_prefix_str[$lang];
			$lv['path'] = "/dev/" . $lv['vg'] . "/" . $lv['name'];
			$lv['mapper'] = $this->GetLvMapperDev( $lv['path'] );
			
			// 获取挂载属性，如果没有挂载则相应属性置空
			/*
				array(
					"name"=>"lv0",
					"path"=>"/dev/vg0/lv0",
					"origin"=>"volume",
					"mapper"=>"/dev/mapper/vg0-lv0",
					"mountdir"=>"/mnt/lv0",
					"size"=>"1024 MB",
					"free"=>"512 MB",
					"usage"=>"50%",
					"vg"=>"vg0"
				)
 			*/
			$lv_mnt_attr = $this->GetLvMountedAttrbute( $lv['path'] );
			if( $lv_mnt_attr !== FALSE )
			{
				$lv['mountdir'] = $lv_mnt_attr['mountdir'];
				$lv['size'] = $lv_mnt_attr['size'];
				$lv['free'] = $lv_mnt_attr['free'];
				$lv['usage'] = $lv_mnt_attr['usage'];
			}
			else
			{
				$lv['mountdir'] = "";
				$lv['free'] = "";
				$lv['usage'] = "";
			}
			
			$this->lv_list[] = $lv;
		}
		
		return TRUE;
	}
	
	private function ListLvOfIt()
	{
		$this->lv_list_of_it = array();
		$iscsiTarget = new Iscsi_target();
		
		$this->lv_list_of_it = $iscsiTarget->GetMappedLvList();
		if($this->lv_list_of_it === FALSE)
		{
			return FALSE;
		}
		
		return TRUE;
	}
}


///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

/* 
 * 说明：逻辑卷组类
 * 1、创建、删除逻辑卷组
 * 2、获取逻辑卷组列表
 * 3、获取某一逻辑卷组的附属物理卷
 * 4、获取某一逻辑卷组的逻辑卷成员 
 * 
 * created by 王大典, 2009-11-09
 */
class LogicVolumeGroup
{
	private $vg_list = array(
		/*
		 array(
		 	"name"=>"vg0",
		 	"size"=>"1024 MB",
		 	"free"="256 MB"
		 ),
		 ...
		 */
	);
	
	
	// 构造函数
	function __construct()
	{
		
	}
	
	/*
	 * 说明：创建逻辑卷组
	 * 参数：$vg_name：逻辑卷组名称
	 * 		 $pv_device_list：逻辑卷组包含的物理卷数组，数组应类似
	 * 						  array("/dev/sda", "/dev/sdb");
	 * 返回：成功-TRUE，失败-FALSE
	 */
	function CreateVg($vg_name, $pv_device_list)
	{
		// 检查名称合法性
		if( ! IsLvmNameOk($vg_name) )
		{
			return FALSE;
		}
		
		$command = CMD_VGCREATE . $vg_name . " ";
		if( is_array($pv_device_list) )
		{
			foreach($pv_device_list as $pv_device)
			{
				$command .= $pv_device . " ";
			}
		}
		else
		{
			return FALSE;
		}
		
		// 执行
		exec($command, $output, $retval);
		if( $retval != 0 )
		{
			return FALSE;
		}

		return TRUE;
	}
	
	/*
	 * 说明：获取某一逻辑卷组的附属物理卷
	 * 参数：$vg_name: 逻辑卷组名称
	 * 返回：成功返回物理卷列表（如果没有成员，则为空数组），失败返回FALSE
	 */
	function GetPvListOfVg($vg_name)
	{
		$pv_list_of_vg = array(
	/*
		 array(
		 	"name"=>"sda",
		 	"device"=>"/dev/sda",
		 	"size"=>"1024 MB",
		 	"vg"="vg0"
		 ),
		 ...
	*/
		);// 此逻辑卷组的物理卷列表，用作返回
		
		$pv_list = array();
		$pv = new PhysicalVolume();
		if ( ($pv_list = $pv->GetPvList()) !== FALSE )
		{
			foreach( $pv_list as $entry)
			{
				if( $entry['vg'] == $vg_name )
				{
					$pv_list_of_vg[] = $entry;
				}
			}
			
			return $pv_list_of_vg;
		}
		else
		{
			return FALSE;
		}
	}
	
	/*
	 * 说明：获取某一逻辑卷组的逻辑卷成员 
	 * 参数：$vg_name: 逻辑卷组名称
	 * 返回：成功返回逻辑卷成员列表（如果没有成员，则为空数组），失败返回FALSE
	 */
	function GetLvListOfVg($vg_name)
	{
		$lv_list_of_vg = array(
/*
			array(
				"name"=>"lv0",
				"size"=>"1024 MB",
				"vg"=>"vg0"
			),
			...
 */
		);
		$lv_list = array();
		$lv = new LogicVolume();
		if( ($lv_list=$lv->GetLvList()) !== FALSE )
		{
			foreach($lv_list as $entry)
			{
				if( $entry['vg'] == $vg_name )
				{
					$lv_list_of_vg[] = $entry;
				}
			}
			
			return $lv_list_of_vg;
		}
		else
		{
			return FALSE;
		}
	}
	
	/*
	 * 说明：获取逻辑卷组的列表
	 * 参数：无
	 * 返回：成功返回逻辑卷组列表（如果没有成员，则为空数组），否则返回FALSE
	 */
	function GetVgList()
	{
		if ($this->ListVg() !== FALSE)
		{
			return $this->vg_list;
		}
		else
		{
			return FALSE;
		}
	}
	
	/*
	 * 说明：删除逻辑卷组
	 * 参数：$vg_name: 逻辑卷组名称
	 * 返回：成功-TRUE，失败-FALSE
	 */
	function RemoveVg($vg_name)
	{
		exec(CMD_VGREMOVE . $vg_name, $output, $retval);
		if( $retval != 0 )
		{
			return FALSE;
		}

		return TRUE;
	}

	//////////////////////////////////////
	// private
	private function ListVg()
	{
		$this->vg_list = array();
		exec(CMD_VGS . "--noheadings --separator \";\"", $output, $retval);
		if( $retval != 0 )
		{
			return FALSE;
		}
		
		foreach( $output as $line )
		{
			// vg_0   1   0   0 wz--n- 596.02G 256.02G
			$line = trim($line);
			if($line == "")
			{
				continue;
			}
			$vg_info = array();
			$vg = array();
			$vg_info = split(";", $line);
			
			$vg['name'] = $vg_info[0];
			$vg['size'] = $vg_info[5];
			$vg['free'] = $vg_info[6];
			
			$this->vg_list[] = $vg;
		}
		
		return TRUE;
	}
}

?>