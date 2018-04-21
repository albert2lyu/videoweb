<?php
require_once("function.php");
require_once("nfs_server.php");
require_once("lvm.php");

$lang=load_lang();

$disk_prefix_str=array(
	"����",
	"disk"
);
$local_str=array(
	"����",
	"local"
);
$local_dir_str=array(
	"����Ŀ¼",
	"Local directory"
);

define('CMD_MKFS', "export LANG=C; /usr/bin/sudo /sbin/mkfs.");
define('CMD_BLKID', "export LANG=C; /usr/bin/sudo /sbin/blkid ");
define('NFS_SHARE_OPT', "ro,no_root_squash,async");


/*
 * ˵������ȡ����(�Ǵ��ڷ����Ĵ���)
 * ��������
 * ���أ��ɹ����ش����б����򷵻�FALSE
	array(
	 	array(
		 	"name"=>"����0"
			"device"=>"sda",
			"path"=>"/dev/sda",
			"size"=>"1024 GB"
	 	),
		... 
	)
 * created by �����, 2009-11-13
 */
function GetDiskList()
{
	global $disk_prefix_str;
	global $lang;
	$disk_list = array();

	$diskList = array();// ���еĴ���
	$diskEntry = array(
/*
 			"name"=>"����0"
			"device"=>"sda",
			"path"=>"/dev/sda",
			"size"=>"1024 GB"
*/
	); // ��������

	$fp = popen("ls /sys/block/ | grep '[s|h]d\([a-z]\{1,\}\)'", 'r');
	while( !feof($fp) )
	{
		$tmp = trim( fgets($fp) );// ȥ�����ҿո����Ļ��з�(\n, \r)
		if( $tmp != "" )//�����һ�����У��ڴ˹��ˡ�
		{
			$diskList[] = $tmp;
		}
	}
	
	$index = 0;
	foreach($diskList as $disk)
	{
		// ���ڷ������޳�
		if( count( GetDiskPartitions("/dev/" . $disk) ) != 0 )
		{
			continue;
		}
		
		// ��������
		$diskEntry["name"] = $disk_prefix_str[$lang] . $index;
		$diskEntry["device"] = $disk;
		// ��ȡ����·��
		$diskEntry['path'] = "/dev/" . $disk;
		// ��ȡ���̴�С
		//    8     0  312571224 sda
		$command = "export LANG=C; /bin/cat /proc/partitions |/bin/grep $disk";
		exec($command, $output, $retval);
		if( $retval!=0 || !isset($output[0]) )
		{
			continue;//û�д˴��̵Ĵ�С��Ϣ�������ǹ������޳�
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
 * ˵������ȡ���Դ��������Ĵ����б�
 * ��������
 * ���أ��ɹ������б����򷵻�FALSE
	array(
	 	array(
		 	"name"=>"����0"
			"device"=>"sda",
			"path"=>"/dev/sda",
			"size"=>"1024 GB"
	 	),
		... 
	)
 * created by �����, 2009-12-08
 */
function GetDiskListForPv()
{
	$disklist = GetDiskList();
	$disk_for_pv_list = array();
	if( count($disklist) == 0 )
	{
		return $disk_for_pv_list;
	}

	// �����б�
	$pv = new PhysicalVolume();
	foreach( $disklist as $entry )
	{
		// ����ѹ��أ��޳�
		if( IsDiskMounted($entry['path']) )
		{
			continue;
		}
		
		// ��������Դ�������iscsi���޳�
		if( GetDiskOriginType($entry['path']) == "iscsi" )
		{
			continue;
		}
		
		//����Ƿ��Ѿ���Ϊ�����
		if( $pv->IsPv($entry['path']) )
		{
			continue;
		}
		
		$disk_for_pv_list[] = $entry;
	}

	return $disk_for_pv_list;
}

/*
 * ˵������ȡ�ѹ��ش����б�
 * ��������
 * ���أ��ɹ����ش����б�û���򷵻ؿ����飩�����򷵻�FALSE
 * 		array(
 * 			"/dev/sda",
 * 			"/dev/sdb",
 * 			...
 * 		)
 * 
 * created by �����, 2009-11-13
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
 * ˵������ȡδ���ش����б�
 * ��������
 * ���أ��ɹ����ش����б�(û���򷵻ؿ�����)�����򷵻�FALSE
 * 		array(
 * 			"/dev/sda",
 * 			"/dev/sdb",
 * 			...
 * 		)
 * 
 * created by �����, 2009-11-13
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
			if( $pv->IsPv($entry['path']) ) // ����Ѿ���������򲻴���
				continue;
			$disk_umount_list[] = $entry['path'];
		}
	}
	
	return $disk_umount_list;
}

/*
 * ˵������ȡ������Ϣ
 * ������$disk:����·������/dev/sda
 * ���أ����ش�����Ϣ��ʧ�ܷ���FALSE
 * 		 ������Ϣ��
 * 		array(
 * 			"name"=>"����0",
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
  	
  	// ��ȡ��������
  	$disk_list = GetDiskList();
	foreach($disk_list as $entry)
	{
		if( isset($entry['name']) && $entry['path']==$disk )
		{
			$diskinfo['name'] = $entry['name'];
		}
	}
  				
	// ��ȡ���̴�С
	//    8     0  312571224 sda
	$diskname = substr(strrchr($disk, "/"), 1);
	$command = "export LANG=C; /bin/cat /proc/partitions |/bin/grep $diskname";
	exec($command, $output, $retval);
	if( $retval!=0 || !isset($output[0]) )
	{
		return FALSE;//û�д˴��̵Ĵ�С��Ϣ�������ǹ�����
	}
	$output = preg_split("/\s+/", trim($output[0] ));
	$diskinfo['size'] = format_bytesize($output[2] * 1024);
	
	// ��ȡ�ļ�ϵͳ����
	$diskinfo['fs'] = GetDiskFsType($disk);
	
	// ��ȡ���̵�ʣ���С��ʹ������Ϣ
	unset($output);
  	if( IsDiskMounted($disk) )
  	{
  		exec("export LANG=C; /bin/df | /bin/grep {$disk}", $output, $retval);
  		if($retval != 0)
  		{
  			return $diskinfo;
  		}
		// ȥ����β�ո񣬲�ʹ��һ��";"�滻���еĿո��Է���ת��Ϊ���鴦��
		$line = trim($output[0]);
		$line = preg_replace("/\s+/i", ";", $line);
		$line_array = explode(";", $line);
		//��ֵ
		$diskinfo['free'] = format_bytesize($line_array[3] * 1024);
		$diskinfo['usage'] = $line_array[4];
		$diskinfo['mountdir'] = $line_array[5];
  	}
  	
  	return $diskinfo;
}

/*
 * ˵������ȡ���̵Ľ���״̬
 * ������$disk�����̣�/dev/sda
 * ���أ�����״̬
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
 * ˵������ʽ�����̣�xfs����ext3��
 * ������ $disk������·������/dev/sda
 * 		 $fs��     �ļ�ϵͳ���ͣ�"xfs"(Ĭ��)/"ext3"
 * ���أ��ɹ�����TRUE�����򷵻�FALSE
 * created by �����, 2009-12-02
 */
function FormatDisk($disk, $fs="xfs")
{
	$command = CMD_MKFS . $fs;
	
	$command .= " -f " . $disk;
	exec($command, $output, $retval);
	return ($retval == 0) ? TRUE : FALSE;
}

/*
 * ˵�����жϴ����Ƿ��Ѿ�����
 * ������$disk�����̣���/dev/sda
 * ���أ��Ѿ����ط���TRUE�����򷵻�FALSE
 * CREATED BY �����, 2009-12-02
 */
function IsDiskMounted($disk)
{
	// ����Ƿ���ص�Ŀ¼
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
 * ˵�����жϴ����Ƿ�Ϊ������
 * ������$disk�����̣�/dev/sda, /dev/mapper/vg0-lv0
 * ���أ��ǽ���������TRUE�����򷵻�FALSE
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
 * ˵������ȡ���̵�Ԥ������������ÿ������512Byte��
 * ������$disk:���̣���/dev/sda
 * ���أ�Ԥ������������ʧ�ܷ���FALSE
 * CREATED BY �����, 2010-03-17
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
 * ˵��������Ԥ����������
 * ������$disk: ���̣���/dev/sda
 *       $ra��Ԥ��������������ÿ������512�ֽڣ�
 * ���أ��ɹ�����TRUE�����򷵻�FALSE
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
	
	// д��rc.local
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
 * ˵�����޸�xfs�ļ�ϵͳ
 * ������$disk�����̣���/dev/sda
 * ���أ��ɹ�����TRUE�����򷵻�FALSE
 * CREATED BY auto��2010-03-23
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
 * ˵�������ش���
 * ������ $disk������·������/dev/sda
 * 		 $dir������·������/mnt/sda,
 * 		 $fs���ļ�ϵͳ���ͣ�FS_XFS��Ĭ�ϣ�/FS_EXT3
 *		 $auto:���ΪFALSE���򿪻����Թ��أ����ΪTRUE���򿪻��Թ��أ�Ĭ�ϣ�
 * ���أ��ɹ�����TRUE�����򷵻�FALSE
 * CREATED BY �����, 2009-12-02
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
	
	//���ÿ����Զ�����
	if($auto === TRUE)
	{
		$fp = fopen("/etc/fstab", 'at');
		if($fp === FALSE)
		{
			return FALSE;
		}
		//��ȡ����UUID
		$uuid = GetDiskUUID($disk);
		$buffer = "UUID=" . $uuid . " " . $dir . " " . $fs . " defaults 0 0\n";
		fputs($fp, $buffer);
		fflush($fp);
		fclose($fp);
	}
	
	return TRUE;
}

/*
 * ˵����ж�ش���
 * ������ $disk������·������/dev/sda
 * 		 $dir������Ŀ¼
 * 		 $auto:���ΪFALSE����ȡ�������Թ��أ����ΪTRUE����ȡ�������Թ��أ�Ĭ�ϣ�
 * ���أ��ɹ�����TRUE�����򷵻�FALSE
 * CREATED BY �����, 2009-12-02
 */
function UnmountDisk($disk, $auto=TRUE)
{
	// ��ȡ����·��
	$dir = GetDiskMountedDir($disk);
	
	$command = "export LANG=C; /usr/bin/sudo /bin/umount " . $disk;
	exec($command, $output, $retval);
	if($retval != 0)
	{
		return FALSE;
	}

	//ȡ�������Զ�����
	if($auto === TRUE)
	{
		$file_buffer = rfts("/etc/fstab");
		if( $file_buffer === FALSE )
		{
			return FALSE;
		}
		
		$file_lines = array();
		$lines = explode("\n", $file_buffer);
		// �޳����Ŀ�Ԫ�أ���ֹ���д���ļ�����һ����
		array_pop($lines);
		
		//��ȡ����UUID
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
 * ˵�����жϴ����Ƿ��Ѿ�����ʽ��
 * ������$disk������·������/dev/sda
 * ���أ��Ѹ�ʽ������TRUE�����򷵻�FALSE
 * CREATED BY �����, 2009-12-04
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
 * ˵������ȡ���̵�scsi id��
 * ������$disk�����̣���/dev/sda
 * ���أ����̵�scsi id�ţ�ʧ�ܷ���FALSE
 */
function GetDiskScsiId($disk)
{
	$diskname = substr(strrchr($disk, "/"), 1);
	$command = "export LANG=C; /usr/bin/sudo /sbin/scsi_id -g -u -s /block/" . $diskname; // rhel5
	// rhel6 ��ȡ����scsi id�� scsi_id --whitelisted --replace-whitespace --device=/dev/sdb
	exec($command, $output, $retval);
	if($retval != 0 || !isset($output[0]))
	{
		return FALSE;
	}
	
	return trim( $output[0] );
}

/*
 * ˵������ȡ���̵��ļ�ϵͳ����
 * ������$disk������·������/dev/sda
 * ���أ��ɹ������ļ�ϵͳ���ͣ���"xfs"/"ext3"�������򷵻ؿ�FALSE(û�д˴��̻�δ��ʽ��)
 * CREATED BY �����, 2009-12-04
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
 * ˵������ȡ���̵�UUID
 * ������$disk������·������/dev/sda
 * ���أ��ɹ�����UUID����"28f64cc5-ca78-428e-aa78-309b12755db1"�������򷵻ؿ�FALSE(û�д˴��̻�δ��ʽ��)
 * CREATED BY �����, 2010-07-01
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
 * ˵������ȡ���̵ķ����б�
 * ������$disk������·����ֻ��������/dev/sda������
 * ���أ��ɹ����ط����б����û�з���Ϊ�գ������򷵻�FALSE
 *       �����б�
 *       array(
 *       	"/dev/sda1",
 *       	"/dev/sda2",
 *       	...
 *       )
 * CREATED BY �����, 2009-12-04
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
 * ˵������ȡiscsi-target���Ӻ��Ӧ���̷�
 * ������ $ip���ṩiscsi-target�ķ����IP
 * 		 $it_name��iscsi-target����
 * ���أ������̷����ƣ���/dev/sda��ʧ�ܷ���FALSE
 * CREATED BY �����, 2009-12-04
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
 * ˵�������ݴ��̻�ȡ������Դ
 * ������$disk������/dev/sda
 * ���أ���Դ�ַ�����ʧ�ܷ���FALSE
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
 * ˵������ȡ���̵�iscsi��Ϣ
 * ������$disk�����̣�/dev/sda
 * ���أ������iscsi������Ϣ�����򷵻�FALSE�����󷵻�FALSE
 * CREATED BY �����, 2009-12-08
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
 * ˵�������ݴ��̻�ȡ��������
 * ������$disk������/dev/sda
 * ���أ��������ͣ���iscsi��scsi��ide����ʧ�ܷ���FALSE
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
 * �� VIS ר��disk����----------------
 */


/*
 * ˵�������ش���(VIS�洢Ӧ��)
 * ������ $disk������·������/dev/sda
 * ���أ��ɹ�����TRUE�����򷵻�FALSE
 * CREATED BY auto, 2009-12-02
 */
function visMountDisk($disk)
{
	$mountdir = "";
	//����Ǳ��ش洢
	if( $disk == "local" )
	{
		return visSetLocalStorage();
	}
	
	if(! IsDiskFormatted($disk) )
	{
		return FALSE;
	}
	
	$fs = GetDiskFsType($disk);
	
	// ����·������
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
	
	//����mysql����
	SetMysqlStorageFileTableBk($mountdir);
	
	// ����NFS����
	// ����NFS����
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
 * ˵������ȡ���̵���Ŀ¼
 * ����������
 * ���أ�����·��������FALSE
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
 * ˵����ж�ش���(VIS�洢Ӧ��)
 * ������ $disk������·������/dev/sda
 * ���أ��ɹ�����TRUE�����򷵻�FALSE
 * CREATED BY �����, 2009-12-02
 */
function visUnmountDisk($disk)
{
	//����Ǳ��ش洢
	if($disk == "local")
	{
		return visUnsetLocalStorage();
	}
	
	// ��ȡ����·��
	$dir = GetDiskMountedDir($disk);
	
	//ȡ��mysql����
	UnsetMysqlStorageFileTableBk($dir);
	
	// ȡ��NFS����
	// ��ȡ��NFS���������޷�ж�سɹ�
	$nfsserver = new NfsServer();	
	$nfsserver->Unshare($dir);

	if(UnmountDisk($disk) === FALSE)
	{
		return FALSE;
	}
	
	return TRUE;
}

/*
 * ˵����ʹ�ñ���Ŀ¼��Ϊ�洢
 * ��������
 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
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
	
	// ����NFS����
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
 * ˵����ȡ��ʹ�ñ���Ŀ¼��Ϊ�洢
 * ��������
 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
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
	
	// ȡ��NFS����
	$nfsserver = new NfsServer();	
	$nfsserver->Unshare($localdir);
	
	return TRUE;
}

/*
 * ˵�����Ƿ������ñ���Ŀ¼��Ϊ�洢
 * ��������
 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
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
 * ˵������ȡ���ش洢����Ϣ
 * ��������
 * ���أ��ɹ�������Ϣ�б�
 * 		array(
 * 			"name"=>"local",
 * 			"origin"=>"����Ŀ¼",
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
 * ---------------------------VIS ר��disk����  ��
 */
//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
?>