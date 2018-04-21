<?php
require_once("function.php");
require_once("nfs_server.php");
require_once("lvm.php");

$lang=load_lang();

$disk_prefix_str=array(
	"磁盘",
	"disk"
);
$local_str=array(
	"本地",
	"local"
);
$local_dir_str=array(
	"本地目录",
	"Local directory"
);

define('CMD_MKFS', "export LANG=C; /usr/bin/sudo /sbin/mkfs.");
define('CMD_BLKID', "export LANG=C; /usr/bin/sudo /sbin/blkid ");
define('NFS_SHARE_OPT', "ro,no_root_squash,async");


/*
 * 说明：获取磁盘(非存在分区的磁盘)
 * 参数：无
 * 返回：成功返回磁盘列表，否则返回FALSE
	array(
	 	array(
		 	"name"=>"磁盘0"
			"device"=>"sda",
			"path"=>"/dev/sda",
			"size"=>"1024 GB"
	 	),
		... 
	)
 * created by 王大典, 2009-11-13
 */
function GetDiskList()
{
	global $disk_prefix_str;
	global $lang;
	$disk_list = array();

	$diskList = array();// 所有的磁盘
	$diskEntry = array(
/*
 			"name"=>"磁盘0"
			"device"=>"sda",
			"path"=>"/dev/sda",
			"size"=>"1024 GB"
*/
	); // 单个磁盘

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
		// 存在分区，剔除
		if( count( GetDiskPartitions("/dev/" . $disk) ) != 0 )
		{
			continue;
		}
		
		// 磁盘名称
		$diskEntry["name"] = $disk_prefix_str[$lang] . $index;
		$diskEntry["device"] = $disk;
		// 获取磁盘路径
		$diskEntry['path'] = "/dev/" . $disk;
		// 获取磁盘大小
		//    8     0  312571224 sda
		$command = "export LANG=C; /bin/cat /proc/partitions |/bin/grep $disk";
		exec($command, $output, $retval);
		if( $retval!=0 || !isset($output[0]) )
		{
			continue;//没有此磁盘的大小信息，可能是光驱。剔除
		}
		$output = preg_split("/\s+/", trim($output[0] ));
		$diskEntry['size'] = format_bytesize($output[2] * 1024);
		$index++;
		$disk_list[] = $diskEntry;
		unset($output);
	}
	
	return $disk_list;
} 
/*
 * 说明：获取可以创建物理卷的磁盘列表
 * 参数：无
 * 返回：成功返回列表，否则返回FALSE
	array(
	 	array(
		 	"name"=>"磁盘0"
			"device"=>"sda",
			"path"=>"/dev/sda",
			"size"=>"1024 GB"
	 	),
		... 
	)
 * created by 王大典, 2009-12-08
 */
function GetDiskListForPv()
{
	$disklist = GetDiskList();
	$disk_for_pv_list = array();
	if( count($disklist) == 0 )
	{
		return $disk_for_pv_list;
	}

	// 过滤列表
	$pv = new PhysicalVolume();
	foreach( $disklist as $entry )
	{
		// 如果已挂载，剔除
		if( IsDiskMounted($entry['path']) )
		{
			continue;
		}
		
		// 检查磁盘来源，如果是iscsi则剔除
		if( GetDiskOriginType($entry['path']) == "iscsi" )
		{
			continue;
		}
		
		//检查是否已经作为物理卷
		if( $pv->IsPv($entry['path']) )
		{
			continue;
		}
		
		$disk_for_pv_list[] = $entry;
	}

	return $disk_for_pv_list;
}

/*
 * 说明：获取已挂载磁盘列表
 * 参数：无
 * 返回：成功返回磁盘列表（没有则返回空数组），否则返回FALSE
 * 		array(
 * 			"/dev/sda",
 * 			"/dev/sdb",
 * 			...
 * 		)
 * 
 * created by 王大典, 2009-11-13
 */
function GetMountedDiskList()
{
	$disk_list = array();
	exec("export LANG=C; /bin/df -h | /bin/grep \"^/dev/\"", $output, $retval);
	if($retval != 0)
	{
		return $disk_list;
	}
	
	$disk_array = GetDiskList();
	$disk_list_array = array();
	foreach($disk_array as $entry)
	{
		if( isset($entry['path']) )
		{
			$disk_list_array[] = $entry['path'];
		}
	}
	
	foreach($output as $line)
	{
		if(preg_match("|^([^\s]*)|i", trim($line), $match))
		{
			$disk = $match[1];
			if( in_array($disk, $disk_list_array) )
			{
				$disk_list[] = $disk;
			}
		}
	}
	
	
	return $disk_list;
} 

/*
 * 说明：获取未挂载磁盘列表
 * 参数：无
 * 返回：成功返回磁盘列表(没有则返回空数组)，否则返回FALSE
 * 		array(
 * 			"/dev/sda",
 * 			"/dev/sdb",
 * 			...
 * 		)
 * 
 * created by 王大典, 2009-11-13
 */
function GetUnmountedDiskList()
{
	$disk_umount_list = array();
	$disk_mount_list = GetMountedDiskList();
	$disk_list = GetDiskList();
	
	$pv = new PhysicalVolume();
	foreach($disk_list as $entry)
	{
		if( isset($entry['path']) && !in_array($entry['path'], $disk_mount_list) )
		{
			if( $pv->IsPv($entry['path']) ) // 如果已经是物理卷则不处理
				continue;
			$disk_umount_list[] = $entry['path'];
		}
	}
	
	return $disk_umount_list;
}

/*
 * 说明：获取磁盘信息
 * 参数：$disk:磁盘路径，如/dev/sda
 * 返回：返回磁盘信息，失败返回FALSE
 * 		 磁盘信息：
 * 		array(
 * 			"name"=>"磁盘0",
 * 			"disk"=>"/dev/sda",
 * 			"size"=>"1024G",
 * 			"free"=>"512G",
 * 			"usage"=>"50%",
 * 			"fs"=>"xfs",
 * 			"mountdir"=>"/mnt/sda"
 * 		)
 */
function GetDiskInfo($disk)
{
	$diskinfo = array(
					"name"=>"",
		  			"disk"=>$disk,
		  			"size"=>"",
		  			"free"=>"",
		  			"usage"=>"",
		  			"fs"=>"",
					"mountdir"=>""
  				);
  	
  	// 获取磁盘名称
  	$disk_list = GetDiskList();
	foreach($disk_list as $entry)
	{
		if( isset($entry['name']) && $entry['path']==$disk )
		{
			$diskinfo['name'] = $entry['name'];
		}
	}
  				
	// 获取磁盘大小
	//    8     0  312571224 sda
	$diskname = substr(strrchr($disk, "/"), 1);
	$command = "export LANG=C; /bin/cat /proc/partitions |/bin/grep $diskname";
	exec($command, $output, $retval);
	if( $retval!=0 || !isset($output[0]) )
	{
		return FALSE;//没有此磁盘的大小信息，可能是光驱。
	}
	$output = preg_split("/\s+/", trim($output[0] ));
	$diskinfo['size'] = format_bytesize($output[2] * 1024);
	
	// 获取文件系统类型
	$diskinfo['fs'] = GetDiskFsType($disk);
	
	// 获取磁盘的剩余大小、使用率信息
	unset($output);
  	if( IsDiskMounted($disk) )
  	{
  		exec("export LANG=C; /bin/df | /bin/grep {$disk}", $output, $retval);
  		if($retval != 0)
  		{
  			return $diskinfo;
  		}
		// 去掉首尾空格，并使用一个";"替换行中的空格，以方面转换为数组处理
		$line = trim($output[0]);
		$line = preg_replace("/\s+/i", ";", $line);
		$line_array = explode(";", $line);
		//赋值
		$diskinfo['free'] = format_bytesize($line_array[3] * 1024);
		$diskinfo['usage'] = $line_array[4];
		$diskinfo['mountdir'] = $line_array[5];
  	}
  	
  	return $diskinfo;
}

/*
 * 说明：获取磁盘的健康状态
 * 参数：$disk：磁盘，/dev/sda
 * 返回：磁盘状态
 */
function GetDiskHealthState($disk)
{
	$unsupported = "Unsupported";
	$command = "export LANG=C; /usr/bin/sudo /usr/sbin/smartctl ";
	exec($command . "-s on " . $disk, $output, $retval);
	if( $retval != 0 )
	{
		return $unsupported;
	}
	
	$needle = "SMART overall-health self-assessment test result:";
	exec($command . "-a " . $disk . " | /bin/grep -i \"" . $needle . "\"", $output, $retval);
	if( preg_match("/{$needle}\s*(.*)/i", trim($output[0]), $match) )
	{
		return $match[1];
	}
	
	return $unsupported;
}

/*
 * 说明：格式化磁盘（xfs或者ext3）
 * 参数： $disk：磁盘路径，如/dev/sda
 * 		 $fs：     文件系统类型，"xfs"(默认)/"ext3"
 * 返回：成功返回TRUE，否则返回FALSE
 * created by 王大典, 2009-12-02
 */
function FormatDisk($disk, $fs="xfs")
{
	$command = CMD_MKFS . $fs;
	
	$command .= " -f " . $disk;
	exec($command, $output, $retval);
	return ($retval == 0) ? TRUE : FALSE;
}

/*
 * 说明：判断磁盘是否已经挂载
 * 参数：$disk：磁盘，如/dev/sda
 * 返回：已经挂载返回TRUE，否则返回FALSE
 * CREATED BY 王大典, 2009-12-02
 */
function IsDiskMounted($disk)
{
	// 检查是否挂载到目录
	$command = "export LANG=C; /usr/bin/sudo /bin/mount";
	exec($command, $output, $retval);
	if($retval != 0)
	{
		return FALSE;
	}
	foreach($output as $line)
	{
		if( preg_match("|^{$disk}[^\w]|i", trim($line)) )
		{
			return TRUE;
		}
	}
	
	return FALSE;
}

/*
 * 说明：判断磁盘是否为交换区
 * 参数：$disk：磁盘，/dev/sda, /dev/mapper/vg0-lv0
 * 返回：是交换区返回TRUE，否则返回FALSE
 * 
 */
function IsDiskSwapon($disk)
{
	$swap_file = "/proc/swaps";
	$file_buffer = rfts($swap_file);
	if( $file_buffer === FALSE )
	{
		return FALSE;
	}
	
	$lines = explode("\n", $file_buffer);

	//Filename                                Type            Size    Used    Priority
	///dev/mapper/VolGroup00-LogVol01         partition       2031608 0       -1
	foreach($lines as $line)
	{
		if( preg_match("|^{$disk}\s+|i", trim($line)) )
		{
			return TRUE;
		}
	}
	
	return FALSE;
}

/*
 * 说明：获取磁盘的预读扇区个数（每个扇区512Byte）
 * 参数：$disk:磁盘，如/dev/sda
 * 返回：预读扇区个数，失败返回FALSE
 * CREATED BY 王大典, 2010-03-17
 */
function GetDiskRa($disk)
{
	$command = "export LANG=C; /usr/bin/sudo /sbin/blockdev --getra " . $disk;
	exec($command, $output, $retval);
	if( $retval !== 0 )
	{
		return FALSE;
	}
	
	$ra_size = trim($output[0]);
	return $ra_size;
}

/*
 * 说明：设置预读扇区个数
 * 参数：$disk: 磁盘，如/dev/sda
 *       $ra：预读的扇区个数（每个扇区512字节）
 * 返回：成功返回TRUE，否则返回FALSE
 * CREATED BY auto, 2010-03-17
 */
function SetDiskRa($disk, $ra)
{
	$cmd = "/sbin/blockdev --setra " . $ra . " " . $disk;;
	$command = "export LANG=C; /usr/bin/sudo " . $cmd;
	exec($command, $output, $retval);
	if( $retval !== 0 )
	{
		return FALSE;
	}
	
	// 写入rc.local
	$rclocal_file = "/etc/rc.d/rc.local";
	SetFileMode($rclocal_file, 'w');
	$file = New File($rclocal_file);
	if($file->Load() === TRUE)
	{
		$file->DeleteLine("/sbin/blockdev --setra\s+[0-9]+\s+{$disk}");
		$file->AddLine($cmd);
		$file->Save();
	}
	return TRUE;
}

/*
 * 说明：修复xfs文件系统
 * 参数：$disk：磁盘，如/dev/sda
 * 返回：成功返回TRUE，否则返回FALSE
 * CREATED BY auto，2010-03-23
 */
function RepairXfs($disk)
{
	$command = "export LANG=C; /usr/bin/sudo /sbin/xfs_repair " . $disk;
	exec($command, $output, $retval);
	if( $retval !== 0 )
	{
		return FALSE;
	}
	
	return TRUE;
}


/*
 * 说明：挂载磁盘
 * 参数： $disk：磁盘路径，如/dev/sda
 * 		 $dir：挂载路径，如/mnt/sda,
 * 		 $fs：文件系统类型，FS_XFS（默认）/FS_EXT3
 *		 $auto:如果为FALSE，则开机不自挂载；如果为TRUE，则开机自挂载（默认）
 * 返回：成功返回TRUE，否则返回FALSE
 * CREATED BY 王大典, 2009-12-02
 */
function MountDisk($disk, $dir, $fs=FS_XFS, $auto=TRUE)
{
	if( ! is_dir($dir) )
	{
		return false;
	}
	
	$command = "export LANG=C; /usr/bin/sudo /bin/mount " . $disk . " " . $dir;
	exec($command, $output, $retval);
	if($retval != 0)
	{
		return FALSE;
	}
	
	//设置开机自动挂载
	if($auto === TRUE)
	{
		$fp = fopen("/etc/fstab", 'at');
		if($fp === FALSE)
		{
			return FALSE;
		}
		//获取磁盘UUID
		$uuid = GetDiskUUID($disk);
		$buffer = "UUID=" . $uuid . " " . $dir . " " . $fs . " defaults 0 0\n";
		fputs($fp, $buffer);
		fflush($fp);
		fclose($fp);
	}
	
	return TRUE;
}

/*
 * 说明：卸载磁盘
 * 参数： $disk：磁盘路径，如/dev/sda
 * 		 $dir：挂载目录
 * 		 $auto:如果为FALSE，则不取消开机自挂载；如果为TRUE，则取消开机自挂载（默认）
 * 返回：成功返回TRUE，否则返回FALSE
 * CREATED BY 王大典, 2009-12-02
 */
function UnmountDisk($disk, $auto=TRUE)
{
	// 获取挂载路径
	$dir = GetDiskMountedDir($disk);
	
	$command = "export LANG=C; /usr/bin/sudo /bin/umount " . $disk;
	exec($command, $output, $retval);
	if($retval != 0)
	{
		return FALSE;
	}

	//取消开机自动挂载
	if($auto === TRUE)
	{
		$file_buffer = rfts("/etc/fstab");
		if( $file_buffer === FALSE )
		{
			return FALSE;
		}
		
		$file_lines = array();
		$lines = explode("\n", $file_buffer);
		// 剔除最后的空元素，防止造成写入文件后多出一空行
		array_pop($lines);
		
		//获取磁盘UUID
		$uuid = GetDiskUUID($disk);
		
		if ( $uuid !== FALSE )
		{
			foreach($lines as $line)
			{
				if(preg_match("|{$uuid}\s+{$dir}|i", trim($line)))
				{
					continue;
				}
				$file_lines[] = $line . "\n";
			}
			
			$fp = fopen("/etc/fstab", 'wt');
			if($fp === FALSE)
			{
				return FALSE;
			}
			foreach($file_lines as $line)
			{
				fputs($fp, $line);
			}
			fflush($fp);
			fclose($fp);
		}
	}
	
	return TRUE;
}

/*
 * 说明：判断磁盘是否已经被格式化
 * 参数：$disk：磁盘路径，如/dev/sda
 * 返回：已格式化返回TRUE，否则返回FALSE
 * CREATED BY 王大典, 2009-12-04
 */
function IsDiskFormatted($disk)
{
	$command = CMD_BLKID . $disk;
	exec($command, $output, $retval);
	if($retval != 0)
	{
		return FALSE;
	}
	
	return TRUE;
}

/*
 * 说明：获取磁盘的scsi id号
 * 参数：$disk：磁盘，如/dev/sda
 * 返回：磁盘的scsi id号，失败返回FALSE
 */
function GetDiskScsiId($disk)
{
	$diskname = substr(strrchr($disk, "/"), 1);
	$command = "export LANG=C; /usr/bin/sudo /sbin/scsi_id -g -u -s /block/" . $diskname; // rhel5
	// rhel6 获取磁盘scsi id： scsi_id --whitelisted --replace-whitespace --device=/dev/sdb
	exec($command, $output, $retval);
	if($retval != 0 || !isset($output[0]))
	{
		return FALSE;
	}
	
	return trim( $output[0] );
}

/*
 * 说明：获取磁盘的文件系统类型
 * 参数：$disk：磁盘路径，如/dev/sda
 * 返回：成功返回文件系统类型（如"xfs"/"ext3"），否则返回空FALSE(没有此磁盘或未格式化)
 * CREATED BY 王大典, 2009-12-04
 */
function GetDiskFsType($disk)
{
	$command = CMD_BLKID . $disk;
	exec($command, $output, $retval);
	if($retval != 0)
	{
		return FALSE;
	}
	
	// /dev/sda: UUID="28f64cc5-ca78-428e-aa78-309b12755db1" TYPE="xfs"
	foreach($output as $line)
	{
		if( preg_match("|\s+TYPE=\"(.*)\"|i", trim($line), $match) )
		{
			$type = $match[1];
			return $type;
		}
	}
		
	return FALSE;
}

/*
 * 说明：获取磁盘的UUID
 * 参数：$disk：磁盘路径，如/dev/sda
 * 返回：成功返回UUID（如"28f64cc5-ca78-428e-aa78-309b12755db1"），否则返回空FALSE(没有此磁盘或未格式化)
 * CREATED BY 王大典, 2010-07-01
 */
function GetDiskUUID($disk)
{
	$command = CMD_BLKID . $disk;
	exec($command, $output, $retval);
	if($retval != 0)
	{
		return FALSE;
	}
	
	// /dev/sda: UUID="28f64cc5-ca78-428e-aa78-309b12755db1" TYPE="xfs"
	foreach($output as $line)
	{
		if( preg_match("|\s+UUID=\"(.*)\"\s+|i", trim($line), $match) )
		{
			return $match[1];
		}
	}
		
	return FALSE;
}

/*
 * 说明：获取磁盘的分区列表
 * 参数：$disk：磁盘路径，只接受类似/dev/sda的数据
 * 返回：成功返回分区列表（如果没有分区为空），否则返回FALSE
 *       分区列表：
 *       array(
 *       	"/dev/sda1",
 *       	"/dev/sda2",
 *       	...
 *       )
 * CREATED BY 王大典, 2009-12-04
 */
function GetDiskPartitions($disk)
{
	$part_list = array();
	$diskname = substr( strrchr($disk, "/"), 1 );
	
	$command = "export LANG=C; /bin/cat /proc/partitions | /bin/grep " . $diskname;
	exec($command, $output, $retval);
	if($retval != 0)
	{
		return FALSE;
	}
	
	foreach($output as $line)
	{
		if( preg_match("|\s+({$diskname}[0-9]+)|i", trim($line), $match) )
		{
			$part_list[] = $match[1];
		}
	}
	
	return $part_list;
}

/*
 * 说明：获取iscsi-target连接后对应的盘符
 * 参数： $ip：提供iscsi-target的服务端IP
 * 		 $it_name：iscsi-target名称
 * 返回：返回盘符名称，如/dev/sda，失败返回FALSE
 * CREATED BY 王大典, 2009-12-04
 */
function GetDiskOfIt($ip, $it_name)
{
	$path = "/dev/disk/by-path";
	$command = "export LANG=C; /bin/ls -l " . $path;
	exec($command, $output, $retval);
	if($retval != 0)
	{
		return FALSE;
	}
		
	// ip-192.168.58.222:3260-iscsi-iqn.sikeyuan.cn:nvr.xyz -> ../../sdb
	// pci-0000:03:00.0-scsi-0:0:0:0 -> ../../sdb
	foreach($output as $line)
	{
		if( preg_match("|{$ip}.*{$it_name}.*\.\.\/\.\.\/([^$]*)|i", trim($line), $match) )
		{
			return "/dev/" . $match[1];
		}
	}
	
	return FALSE;
}

/*
 * 说明：根据磁盘获取磁盘来源
 * 参数：$disk：类似/dev/sda
 * 返回：来源字符串，失败返回FALSE
 */
function GetDiskOrigin($disk)
{
	$diskname = substr( strrchr($disk, "/"), 1);
	global $local_str, $lang;
	$path = "/dev/disk/by-path";
	$command = "export LANG=C; /bin/ls -l " . $path;
	exec($command, $output, $retval);
	if($retval != 0)
	{
		return FALSE;
	}
	
	// ip-192.168.58.222:3260-iscsi-iqn.sikeyuan.cn:nvr.xyz -> ../../sdb
	// pci-0000:03:00.0-scsi-0:0:0:0 -> ../../sdb
	// pci-0000:00:1f.1-ide-0:0 -> ../../hda
	foreach($output as $line)
	{
		if( preg_match("|{$diskname}$|i", trim($line)) )
		{
			if( preg_match("|ip-([0-9\.]*).*-iscsi-([^\s]*).*|i", trim($line), $match) )
			{
				if( preg_match("|iqn.sikeyuan.cn:nvr.(.*)|i", $match[2], $match2) )
				{
					$target = $match2[1];
				}
				else
				{
					$target = $match[2];
				}
				$buffer = "IPSAN-" . $match[1] . ":" . $target;
				return $buffer;
			}
			if( preg_match("|-scsi-|i", trim($line), $match) )
			{
				return $local_str[$lang] . " SCSI";
			}
			if( preg_match("|-ide-|i", trim($line), $match) )
			{
				return $local_str[$lang] . " IDE";
			}
		}

	}
	
	return FALSE;
}

/*
 * 说明：获取磁盘的iscsi信息
 * 参数：$disk：磁盘，/dev/sda
 * 返回：如果是iscsi返回信息，否则返回FALSE，错误返回FALSE
 * CREATED BY 王大典, 2009-12-08
 */
function GetDiskIscsiInfo($disk)
{
	$diskname = substr( strrchr($disk, "/"), 1);
	global $local_str, $lang;
	$path = "/dev/disk/by-path";
	$command = "export LANG=C; /bin/ls -l " . $path;
	exec($command, $output, $retval);
	if($retval != 0)
	{
		return FALSE;
	}
		
	$iscsi_info = array();
	
	// ip-192.168.58.222:3260-iscsi-iqn.sikeyuan.cn:nvr.xyz -> ../../sdb
	// pci-0000:03:00.0-scsi-0:0:0:0 -> ../../sdb
	// pci-0000:00:1f.1-ide-0:0 -> ../../hda
	foreach($output as $line)
	{
		if( preg_match("|{$diskname}$|i", trim($line)) )
		{
			if( preg_match("|ip-([0-9\.]*).*-iscsi-([^\s]*).*|i", trim($line), $match) )
			{
				$iscsi_info['disk'] = $disk;
				$iscsi_info['server'] = $match[1];
				$iscsi_info['target'] = $match[2];
				return $iscsi_info;
			}
		}
	}
	
	return FALSE;
}

/*
 * 说明：根据磁盘获取磁盘类型
 * 参数：$disk：类似/dev/sda
 * 返回：磁盘类型（如iscsi，scsi，ide），失败返回FALSE
 */
function GetDiskOriginType($disk)
{
	$diskname = substr( strrchr($disk, "/"), 1);
	global $local_str, $lang;
	$path = "/dev/disk/by-path";
	$command = "export LANG=C; /bin/ls -l " . $path;
	exec($command, $output, $retval);
	if($retval != 0)
	{
		return FALSE;
	}
	
	// ip-192.168.58.222:3260-iscsi-iqn.sikeyuan.cn:nvr.xyz -> ../../sdb
	// pci-0000:03:00.0-scsi-0:0:0:0 -> ../../sdb
	// pci-0000:00:1f.1-ide-0:0 -> ../../hda
	foreach($output as $line)
	{
		if( preg_match("|{$diskname}$|i", trim($line)) )
		{
			if( preg_match("|-iscsi-|i", trim($line), $match) )
			{
				return "iscsi";
			}
			if( preg_match("|-scsi-|i", trim($line), $match) )
			{
				return "scsi";
			}
			if( preg_match("|-ide-|i", trim($line), $match) )
			{
				return "ide";
			}
		}

	}
	
	return FALSE;
}

//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
/*
 * 【 VIS 专用disk处理----------------
 */


/*
 * 说明：挂载磁盘(VIS存储应用)
 * 参数： $disk：磁盘路径，如/dev/sda
 * 返回：成功返回TRUE，否则返回FALSE
 * CREATED BY auto, 2009-12-02
 */
function visMountDisk($disk)
{
	$mountdir = "";
	//如果是本地存储
	if( $disk == "local" )
	{
		return visSetLocalStorage();
	}
	
	if(! IsDiskFormatted($disk) )
	{
		return FALSE;
	}
	
	$fs = GetDiskFsType($disk);
	
	// 挂载路径设置
	// lvm
	if( preg_match("|/dev/mapper/.*|i", $disk) )
	{
		$dir = substr( strrchr($disk, "/"), 1);
		$mountdir = "/mnt/" . "Storage" . "/" . $dir;
	}
	// /dev/sd*
	else
	{
		$scsi_id = GetDiskScsiId($disk);
		if($scsi_id === FALSE)
		{
			return FALSE;
		}
			
		$mountdir = "/mnt/" . "Storage" . "/" . $scsi_id;
	}
	
	if(CreateDir($mountdir) !== TRUE)
	{
		return FALSE;
	}
	
	if(MountDisk($disk, $mountdir, $fs) !== TRUE)
	{
		return FALSE;
	}
	
	$id_file = $mountdir . "/" . "id.txt";
	if(CreateFile($id_file) !== TRUE)
	{
		UnmountDisk($disk, $mountdir);
		return FALSE;
	}
	SetFileMode($id_file, "rw");
		
	$fp = fopen($id_file, 'wt');
	if($fp === FALSE)
	{
		UnmountDisk($disk, $mountdir);
		return FALSE;
	}
	if( preg_match("|/dev/mapper/.*|i", $disk) )
	{
		fputs($fp, "id=" . $dir);
	}
	else
	{
		fputs($fp, "id=" . $scsi_id);
	}
	
	fflush($fp);
	fclose($fp);
	
	SetFileMode("/mnt/", "rw");
	SetFileMode("/mnt/" ."Storage", "rw");
	SetFileMode($mountdir, "rw");
	
	//设置mysql备份
	SetMysqlStorageFileTableBk($mountdir);
	
	// 设置NFS共享
	// 设置NFS共享
	$hosts = $_SERVER['SERVER_ADDR'] . "/16";
	//$share_dir = "/mnt/" . $_SERVER['SERVER_ADDR'];
	$share_dir = $mountdir;
	$nfsserver = new NfsServer();
	if( ! $nfsserver->IsShared($share_dir) )
	{
		$nfsserver->Share($share_dir, $hosts, NFS_SHARE_OPT);
	}
	
	return TRUE;
}
/*
 * 说明：获取磁盘的在目录
 * 参数：磁盘
 * 返回：挂载路径，或者FALSE
 */
function GetDiskMountedDir($disk)
{
	$command = "export LANG=C; /usr/bin/sudo /bin/mount";
	exec($command, $output, $retval);
	if($retval !== 0)
	{
		return FALSE;
	}
	foreach( $output as $line )
	{
		if( preg_match("|^{$disk}\s+on\s+([^\s]*)|i", trim($line), $match) )
		{
			return $match[1];
		}
	}
	
	return FALSE;
}

/*
 * 说明：卸载磁盘(VIS存储应用)
 * 参数： $disk：磁盘路径，如/dev/sda
 * 返回：成功返回TRUE，否则返回FALSE
 * CREATED BY 王大典, 2009-12-02
 */
function visUnmountDisk($disk)
{
	//如果是本地存储
	if($disk == "local")
	{
		return visUnsetLocalStorage();
	}
	
	// 获取挂载路径
	$dir = GetDiskMountedDir($disk);
	
	//取消mysql备份
	UnsetMysqlStorageFileTableBk($dir);
	
	// 取消NFS共享
	// 先取消NFS共享，否则无法卸载成功
	$nfsserver = new NfsServer();	
	$nfsserver->Unshare($dir);

	if(UnmountDisk($disk) === FALSE)
	{
		return FALSE;
	}
	
	return TRUE;
}

/*
 * 说明：使用本地目录作为存储
 * 参数：无
 * 返回：成功返回TRUE，失败返回FALSE
 */
function visSetLocalStorage()
{
	$localdir = "/mnt/" . "Storage" . "/local";
	$id_file  = $localdir . "/id.txt";
	
	if( CreateDir($localdir) === FALSE )
	{
		return FALSE;
	}
	if( CreateFile($id_file)===FALSE || SetFileMode($id_file, 'rw')===FALSE )
	{
		return FALSE;
	}
	
	$fp = fopen($id_file, 'wt');
	if($fp === FALSE)
	{
		return FALSE;
	}
	fputs($fp, "id=local");
	fflush($fp);
	fclose($fp);
	
	// 设置NFS共享
	$hosts = $_SERVER['SERVER_ADDR'] . "/16";
	$share_dir = $localdir;
	$nfsserver = new NfsServer();
	if( ! $nfsserver->IsShared($share_dir) )
	{
		$nfsserver->Share($share_dir, $hosts, NFS_SHARE_OPT);
	}
	
	return TRUE;
}

/*
 * 说明：取消使用本地目录作为存储
 * 参数：无
 * 返回：成功返回TRUE，失败返回FALSE
 */
function visUnsetLocalStorage()
{
	$localdir = "/mnt/" . "Storage" . "/local";
	$id_file  = $localdir . "/id.txt";
	if( ! is_file($id_file) )
	{
		return FALSE;
	}
	
	if(RemoveFile($id_file) === FALSE)
	{
		return FALSE;
	}
	
	// 取消NFS共享
	$nfsserver = new NfsServer();	
	$nfsserver->Unshare($localdir);
	
	return TRUE;
}

/*
 * 说明：是否已设置本地目录作为存储
 * 参数：无
 * 返回：成功返回TRUE，失败返回FALSE
 */
function visIsLocalStorageSet()
{
	$localdir = "/mnt/" . "Storage" . "/local";
	$id_file  = $localdir . "/id.txt";
	
	if( ! file_exists($id_file)  )
	{
		return FALSE;
	}
	
	$fp = fopen($id_file, 'r');
	if($fp === FALSE)
	{
		return FALSE;
	}
	$buffer = fgets($fp);
	fclose($fp);
	
	if( ! preg_match("/^id=local$/i", trim($buffer)) )
	{
		return FALSE;
	}
	
	return TRUE;
}

/*
 * 说明：获取本地存储的信息
 * 参数：无
 * 返回：成功返回信息列表
 * 		array(
 * 			"name"=>"local",
 * 			"origin"=>"本地目录",
 * 			"size"=>"1024G",
 * 			"free"=>"512G",
 * 			"usage"=>"50%",
 * 			"fs"=>"xfs",
 * 			"dir"=>"/mnt/192.168.58.230/local"
 * 		)
 */
function visGetLocalStorageInfo()
{
	global $local_dir_str, $lang;
	$localdir = "/mnt/" . "Storage". "/local";
	$local_info=array(
		"name"=>"local",
		"origin"=>"{$local_dir_str[$lang]}",
		"size"=>"",
		"free"=>"",
		"usage"=>"",
		"fs"=>"",
		"dir"=>"{$localdir}"
	);
	
	if( CreateDir($localdir) === FALSE )
	{
		return $local_info;
	}

	$command = "export LANG=C; /usr/bin/sudo /bin/df -h " . $localdir;
	exec($command, $output, $retval);
	if($retval != 0)
	{
		return $local_info;
	}
	
	/* 
	Filesystem            Size  Used Avail Use% Mounted on
	/dev/mapper/VolGroup00-LogVol00
	                      287G  7.3G  265G   3% /
	/dev/sda1              99M   22M   72M  24% /boot
	*/
	//array_shift($output);
	foreach($output as $line)
	{
		if( preg_match("|([0-9\.]+[TGMKB]+)\s+([0-9\.]+[TGMKB]+)\s+([0-9\.]+[TGMKB]+)\s+([0-9\.]+%)\s+(.*)|i", trim($line), $match) )
		{
			$local_info['size'] = $match[1];
			$local_info['free'] = $match[3];
			$local_info['usage'] = $match[4];
			$sys_dir = $match[5];
			
			$command = "export LANG=C; /usr/bin/sudo /bin/mount";
			exec($command, $output_mnt, $retval);
			if($retval == 0)
			{
				foreach($output_mnt as $line_mnt)
				{
					if( preg_match("|\s+{$sys_dir}\s+type\s+(\w*)|i", trim($line_mnt), $match_mnt) )
					{
						$local_info['fs'] = $match_mnt[1];
						break;
					}
				}
			}
			
			return $local_info;
		}
	}
	
	return $local_info;
}

/*
 * ---------------------------VIS 专用disk处理  】
 */
//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
?>