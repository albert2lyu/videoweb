<?php

require_once("function.php");
require_once("./include/iscsi_target.php");
require_once("./include/disk.php");

/*
 * ˵����
 * 1��GetDiskDevices������ɨ��ǹ��ء��������Ĵ���
 * 2��LVM��������ࣺ������ࡢ�߼������ࡢ�߼�����
 * 
 * created by �����, 2009-11-09
 */


/*
 * lvm���shell����
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
	"����",
	"disk"
);
$volume_prefix_str=array(
	"��",
	"volume"
);

/*
 * ˵����ɨ����̣���ȡ�ǹ��ء��������Ĵ���
 * 
 * ���أ������б�;���û���ҵ����̣��򷵻�FALSE.
 * 
 * created by �����, 2009-11-09
 */
function GetDiskDevices()
{
	global $disk_prefix_str;
	global $lang;
	$diskList = array();// ���еĴ���
	$diskEntry = array(
/*
			"name"=>"����0",
			"path"=>"/dev/sda",
			"size"=>"1024 GB"
*/
	); // ��������
	
	$diskListToPv = array(
/*		
  		array(//$diskEntry
			"name"=>"����0",
			"path"=>"/dev/sda",
			"size"=>"1024 GB"
			),
		...
*/
	); // ����Ϊ������̵Ĵ��̣���������
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
		// ����Ƿ��ѹ���
		exec("mount | grep \"" . $disk . "\"", $output, $retval);
		if( $retval == 0 )// �ѹ���
		{
			continue;
		}
		//����Ƿ��Ѿ���Ϊ�����
		// pvs --noheadings --separator " " | grep "sdb"
		$command = CMD_PVS . "--noheadings | grep  $disk";
		unset($output);
		exec($command, $output, $retval);
		if( (isset($output[0]) && trim($output[0])!="") )
		{
			continue;
		}
		// ��������
		$diskEntry["name"] = $disk_prefix_str[$lang] . $index;
		// ��ȡ����·��
		$diskEntry['path'] = "/dev/" . $disk;
		// ��������Դ�������iscsi���޳�
		$disk_origintype = GetDiskOriginType($diskEntry['path']);
		if($disk_origintype == "iscsi")
		{
			continue;
		}
		// ��ȡ���̴�С
		//    8     0  312571224 sda
		$command = "export LANG=C; /bin/cat /proc/partitions |grep $disk";
		unset($output);
		exec($command, $output, $retval);
		if( $retval!=0 || !isset($output[0]) )
		{
			continue;//û�д˴��̵Ĵ�С��Ϣ�������ǹ������޳�
		}
		$output = preg_split("/\s+/", trim($output[0] ));
		$diskEntry['size'] = format_bytesize($output[2] * 1024);
		$index++;
		$diskListToPv[] = $diskEntry;
	}

	// ���û���ҵ������򷵻�FALSE
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
 *  ˵�����������
 * 		1����ȡ������б�
 * 		2��������ɾ�������
 * 
 * 	created by �����, 2009-11-09
 */
class PhysicalVolume
{
	private $pv_list=array(
		/*
		 array(
		 	"name"=>"����0"
		 	"disk"=>"sda",
		 	"device"=>"/dev/sda",
		 	"size"=>"1024 MB",
		 	"vg"="vg0"
		 ),
		 ...
		 */
	);
	
	// ���캯��
	function __construct()
	{
		$this->RefreshPvList();
	}
	
	/*
	 * ˵�����½�������̣�physical volume��
	 * ������$device: ����·������/dev/sda
	 * ���أ��ɹ�-TRUE��ʧ��-FALSE
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
	 * ˵����ˢ��������б��ڴ�����ɾ����ʹ�ã������ʹGetPvList��ȡ���б�����
	 * ��������
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
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
	 * ˵������ȡ������б�·������С���������飩
	 * ��������
	 * ���أ��ɹ�����������б����û�г�Ա����Ϊ�����飩��ʧ�ܷ���FALSE
	 */
	function GetPvList()
	{
		return $this->pv_list;
	}
	
	/*
	 * ˵����ɾ�������
	 * ��������
	 * ���أ��ɹ�-TRUE��ʧ��-FALSE
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
	 * ˵�����Ƿ��������
	 * ������$disk����/dev/sda
	 * ���أ���������򷵻�TRUE�����򷵻�FALSE
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
 * ˵�����߼�����
 * 1��������ɾ���߼���
 * 2����ȡ�߼����б����ơ���С�������߼��������ƣ�
 * 
 * created by �����, 2009-11-09
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
	
	// ����Ϊiscsi-target���߼����б�
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
	
	
	// ���캯��
	function __construct()
	{
		
	}
	
	/*
	 * ˵���������߼���
	 * ������$lv_name:�߼�������
	 * 		 $lv_size:�߼����С����λΪMB
	 * 		 $vg_name:�߼���������
	 * ���أ��ɹ�����TRUE�����򷵻�FALSE
	 */
	function CreateLv($lv_name, $lv_size, $vg_name)
	{
		// ������ƺϷ���
		if ( ! IsLvmNameOk($lv_name) )
		{
			return FALSE;
		}
		// �����߼����С
		// ����Ƿ���������
		if(preg_match("/[^0-9\.]/", $lv_size)>0)
		{
			return FALSE;
		}
		$lv_size = $lv_size . "M";
		
		// ����ִ��
		$command = CMD_LVCREATE . "-L " . $lv_size . " -n " . $lv_name . " " . $vg_name;
		exec($command, $output, $retval);
		if( $retval != 0 )
		{
			return FALSE;
		}

		return TRUE;
	}
	
	/*
	 * ˵������ȡ���е��߼����б�
	 * ��������
	 * ���أ��ɹ������߼����б����û�г�Ա���򷵻ؿ����飩�����򷵻�FALSE
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
	 * ˵����ɾ���߼���
	 * ������$lv_name:�߼�������
	 * 		 $vg_name:�����߼���������
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
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
	 * ˵��������·����ȡ�߼���Ĵ�С
	 * ������$path���߼���·����/dev/vg0/lv0
	 * ���أ��ɹ����ش�С�ַ�����ʧ�ܷ���FALSE
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
	 * ˵�������ĳһ���߼����Ƿ��Ѿ���Ϊiscsi-target��lun��
	 * ������$vg_name����������
	 * 		 $lv_name���߼�������
	 * ���أ����򷵻�TRUE�����򷵻�FALSE
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
	 * ˵������ȡLV��Ӧ���豸��/dev/vg0/vg0_0 -> /dev/mapper/vg0-vg0_0
	 * �������߼���·����/dev/vg0/vg0_1
	 * ���أ����ض�Ӧ�豸��ʧ�ܷ���FALSE
	 */
	function GetLvMapperDev($lv)
	{
		// ��ȡlv�Ķ�Ӧ�豸��/dev/mapper/...
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
	 * ˵������ȡLV��Ԥ������������ÿ������512Byte��
	 * �������߼���·����/dev/vg0/vg0_1
	 * ���أ��ɹ����ش�С��ʧ�ܷ���FALSE
	 */
	function GetLvRa($lv)
	{
		return GetDiskRa($lv);
	}
	
	/*
	 * ˵��������Ԥ����������
	 * ������$lv: ���̣���/dev/sda
	 *       $ra��Ԥ��������������ÿ������512�ֽڣ�
	 * ���أ��ɹ�����TRUE�����򷵻�FALSE
	 */
	function SetLvRa($lv, $ra)
	{
		return 	SetDiskRa($lv, $ra);
	}
	
	//////////////////////////////////////
	// private
		/* 
	 * ˵������ȡlv�Ĺ�������
	 * �������߼���·����/dev/vg0/vg0_1
	 * ���أ�����lv�Ĺ������ԣ����lvû�й��ط���FALSE
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
			 * ƥ�䣺
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
			 * ƥ�䣺
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
			
			// ��ȡ�������ԣ����û�й�������Ӧ�����ÿ�
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
 * ˵�����߼�������
 * 1��������ɾ���߼�����
 * 2����ȡ�߼������б�
 * 3����ȡĳһ�߼�����ĸ��������
 * 4����ȡĳһ�߼�������߼����Ա 
 * 
 * created by �����, 2009-11-09
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
	
	
	// ���캯��
	function __construct()
	{
		
	}
	
	/*
	 * ˵���������߼�����
	 * ������$vg_name���߼���������
	 * 		 $pv_device_list���߼������������������飬����Ӧ����
	 * 						  array("/dev/sda", "/dev/sdb");
	 * ���أ��ɹ�-TRUE��ʧ��-FALSE
	 */
	function CreateVg($vg_name, $pv_device_list)
	{
		// ������ƺϷ���
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
		
		// ִ��
		exec($command, $output, $retval);
		if( $retval != 0 )
		{
			return FALSE;
		}

		return TRUE;
	}
	
	/*
	 * ˵������ȡĳһ�߼�����ĸ��������
	 * ������$vg_name: �߼���������
	 * ���أ��ɹ�����������б����û�г�Ա����Ϊ�����飩��ʧ�ܷ���FALSE
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
		);// ���߼������������б���������
		
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
	 * ˵������ȡĳһ�߼�������߼����Ա 
	 * ������$vg_name: �߼���������
	 * ���أ��ɹ������߼����Ա�б����û�г�Ա����Ϊ�����飩��ʧ�ܷ���FALSE
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
	 * ˵������ȡ�߼�������б�
	 * ��������
	 * ���أ��ɹ������߼������б����û�г�Ա����Ϊ�����飩�����򷵻�FALSE
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
	 * ˵����ɾ���߼�����
	 * ������$vg_name: �߼���������
	 * ���أ��ɹ�-TRUE��ʧ��-FALSE
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