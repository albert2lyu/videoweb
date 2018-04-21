<?php 
require("./include/authenticated.php");
require_once("./include/function.php");
require_once("./include/nfs_server.php");
require_once("./include/iscsi_initiator.php");
require_once("./include/disk.php");
require_once("./include/log.php");
require_once("./include/lvm.php");

$DiskRaData = array(
	256=>"128KB",
	2048=>"1MB",
	8192=>"4MB",
	16384=>"8MB",
	32768=>"16MB"
);



$lang=load_lang();

$ipsan_storage_config_str=array(
	"IPSAN �洢����",
	"IPSAN Storage Config"
);
$ipsan_ip_str=array(
	"IPSAN IP��ַ",
	"IPSAN IP address"
);
$get_target_str=array(
	"��ȡĿ��",
	"Get targets"
);
$target_list_str=array(
	"iscsi-target�б�",
	"Iscsi-target List"
);
$target_name_str=array(
	"Ŀ������",
	"Target name"
);
$operation_str=array(
	"����",
	"Operation"
);
$connect_str=array(
	"����",
	"Connect"
);
$disconnect_str=array(
	"�Ͽ�����",
	"Disconnect"
);
$ipsan_state_str=array(
	"IPSAN ������Ϣ",
	"IPSAN Connection Information"
);
$fs_type_str=array(
	"�ļ�ϵͳ",
	"Filesystem"
);
$disk_str=array(
	"����",
	"Disk"
);
$total_size_str=array(
	"�ܴ�С",
	"Total size"
);
$free_size_str=array(
	"ʣ���С",
	"Free size"
);
$usage_str=array(
	"ʹ�ðٷֱ�",
	"Usage"
);
$mount_str=array(
	"����",
	"Mount"
);
$unmount_str=array(
	"ж��",
	"Unmount"
);
$format_str=array(
	"��ʽ��",
	"Format"
);
$readahead_str=array(
	"Ԥ����С",
	"Readahead size"
);
$xfs_repair_str=array(
	"xfs�޸�",
	"xfs Repair"
);
$mysqldump_str=array(
	"mysql����",
	"mysql backup"
);
$cancel_mysqldump_str=array(
	"ȡ��mysql����",
	"Cancel backup"
);
$ok_str=array(
	"�ɹ�",
	"OK"
);
$failed_str=array(
	"ʧ��",
	"failed"
);
$none_str=array(
	"- ��   -",
	"- None -"
);
$confirm_connect_str=array(
	"ȷ�����ӣ�",
	"Confirm to connect?"
);
$confirm_disconnect_str=array(
	"ȷ�϶Ͽ����ӣ�",
	"Confirm to disconnect?"
);
$confirm_mount_str=array(
	"ȷ�Ϲ��أ�",
	"Confirm to mount?"
);
$confirm_unmount_str=array(
	"ȷ��ж�أ�",
	"Confirm to unmount?"
);
$has_connected_str=array(
	"������",
	"Has Connected"
);
$in_use_str=array(
	"����ʹ��",
	"in use"
);
$storage_management_str=array(
	"�洢���ع���",
	"Storage Management"
);
$disk_not_mounted_str=array(
	"δ���صĴ���",
	"Not Mounted Disk(s)"
);
$disk_mounted_str=array(
	"�ѹ��صĴ���",
	"Mounted Disk(s)"
);
$disk_origin_str=array(
	"��������",
	"Disk Type"
);
$ip_error_str=array(
	"IP��ַ����!",
	"IP address is incorrect!"
);
$confirm_setra_str=array(
	"ȷ���޸Ĵ��̵�Ԥ����С��",
	"Confirm to change disk's read ahead size?"
);
$set_ra_ok_str=array(
	"����Ԥ���ɹ���",
	"Set read ahead OK."
);
$set_ra_failed_str=array(
	"����Ԥ��ʧ�ܣ�",
	"Set read ahead Failed!"
);
$xfs_repair_ok_str=array(
	"xfs�޸��ɹ���",
	"xfs repair OK."
);
$xfs_repair_failed_str=array(
	"xfs�޸�ʧ�ܣ�",
	"xfs repair FAILED!"
);
$confirm_set_bk_str=array(
	"ȷ�����ñ��ݣ�",
	"Confirm to set backup?"
);
$confirm_unset_bk_str=array(
	"ȷ��ȡ����",
	"Confirm to cancel?"
);
$set_bk_ok_str=array(
	"����mysql���ݳɹ�",
	"Set backup ok."
);
$unset_bk_ok_str=array(
	"ȡ�����ñ��ݳɹ�",
	"Cancel backup ok."
);
$set_bk_failed_str=array(
	"����mysql����ʧ��",
	"Set backup failed."
);
$unset_bk_failed_str=array(
	"ȡ�����ñ���ʧ��",
	"Cancel backup failed."
);
?>

<html>
<head>
<base target="_self"></base>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<link rel="stylesheet" href="css/target.css" type="text/css" />
<style type="text/css">
tr.different
{
	color: blue;
}
</style>
<script defer type="text/javascript" src="js/pngfix.js"></script>
<script defer type="text/javascript" src="js/function.js"></script>
<script type="text/javascript">
function set_disk_xfsrepair(disk)
{
    document.getElementById("xfs_repair_disk").value=disk;
	document.getElementById("mount_disk_form").submit();
	return true;
}
function SetRa_mform(disk, msg)
{
	if( confirm(msg) )
	{
	    document.getElementById("disk_setra_m").value=disk;
	    document.getElementById("mount_disk_form").submit();
		return true;
	}
	else
	{
		window.location.reload();
		return false;
	}
}
function SetRa_umform(disk, msg)
{
	if( confirm(msg) )
	{
	    document.getElementById("disk_setra_um").value=disk;
	    document.getElementById("unmount_disk_form").submit();
		return true;
	}
	else
	{
		window.location.reload();
		return false;
	}
}
function do_it_connect(it, ip, msg)
{
    document.getElementById("it_conn_name").value = it + ";" + ip;
	if( confirm(msg) )
		return true;
	else
		return false;
}
function do_it_disconnect(it, ip, msg)
{
    document.getElementById("it_disconn_name").value = it + ";" + ip;
	if( confirm(msg) )
		return true;
	else
		return false;
}
function do_format(disk, name, ip)
{
	var page='formatdisk.php?ppage=storage_target.php&' + 'disk=' + disk + '&name=' + name + '&ip=' + ip;
	window.open (
			 page,
			 'format_window',
			 'height=200,width=360,top=300,left=400,resizable=no,location=no,status=yes'
	);
	return true;
}
function do_mount(disk, msg)
{
    document.getElementById("fm_disk").value = disk;
	if( confirm(msg) )
		return true;
	else
		return false;
}

function do_unmount(disk, msg)
{
    document.getElementById("um_disk").value = disk;
	if( confirm(msg) )
		return true;
	else
		return false;
}
function check_ip()
{
	var ipaddr = document.getElementById("ipsan_ip").value;
	if(ipaddr == "")
	{
		document.getElementById("ipsan_ip_span").innerHTML = "<img src='./images/right_icon.png'  style='visibility: hidden;'></img>";
		return true;
	}
	var retval = IsIpOk(ipaddr);
	if(retval == true)
	{
		document.getElementById("ipsan_ip_span").innerHTML = "<img src='./images/right_icon.png'></img>";
		document.getElementById("ipsan_ip_submit").disabled = false;
	}
	else
	{
		document.getElementById("ipsan_ip_span").innerHTML = "<img src='./images/error_icon.png'></img>";
		document.getElementById("ipsan_ip_submit").disabled = true;
	}
	return true;
}
function do_set_bk(dir, msg)
{
    document.getElementById("set_bk_dir").value = dir;
	if( confirm(msg) )
	{
		return true;
	}
	else
	{
		return false;
	}
}
function do_unset_bk(dir, msg)
{
    document.getElementById("unset_bk_dir").value = dir;
	if( confirm(msg) )
	{
		return true;
	}
	else
	{
		return false;
	}
}
</script>
</head>

<?php 
/*
 * ��������
 */
$message = "";
$log = new Log();
$logicVolume = new LogicVolume();
$iscsi_initiator = new IscsiInitiator();

$ipsan_ip = "";
if( isset($_GET['ip']) )
{
	$ipsan_ip = trim($_GET['ip']);
}
if( isset($_POST['ipsan_ip']) )
{
	$ipsan_ip = trim($_POST['ipsan_ip']);
}

// ����iscsi-targetĿ��
if( isset($_POST['connect_submit']) && isset($_POST['it_conn_name']))
{
	$str = $_POST['it_conn_name'];
	$items = explode(";", $str);
	$ip = $items[1];
	$it_name = $items[0];
	
	if( $iscsi_initiator->LoginIt($ip, $it_name) === TRUE)
	{
		$log->VstorWebLog(LOG_INFOS, MOD_SYSTEM, "iscsi-initiator login {$it_name}({$ip}) ok.");
		$log->VstorWebLog(LOG_INFOS, MOD_SYSTEM, "iscsi-initiator��¼ {$it_name}({$ip})�ɹ���", CN_LANG);
	}
	else
	{
		$log->VstorWebLog(LOG_ERROR, MOD_SYSTEM, "iscsi-initiator login {$it_name}({$ip}) failed.");
		$log->VstorWebLog(LOG_ERROR, MOD_SYSTEM, "iscsi-initiator��¼{$it_name}({$ip})ʧ�ܡ�", CN_LANG);
	}
	// �ȴ������������
	sleep(1);
}

// �Ͽ�����iscsi-targetĿ��
if( isset($_POST['disconnect_submit']) && isset($_POST['it_disconn_name']))
{
	$str = $_POST['it_disconn_name'];
	$items = explode(";", $str);
	$ip = $items[1];
	$it_name = $items[0];
	
	if( $iscsi_initiator->LogoutIt($ip, $it_name) === TRUE )
	{
		$log->VstorWebLog(LOG_WARN, MOD_SYSTEM, "iscsi-initiator logout {$it_name}({$ip}) ok.");
		$log->VstorWebLog(LOG_WARN, MOD_SYSTEM, "iscsi-initiator �ǳ�{$it_name}({$ip})�ɹ���", CN_LANG);
	}
	else
	{
		$log->VstorWebLog(LOG_ERROR, MOD_SYSTEM, "iscsi-initiator logout {$it_name}({$ip}) failed.");
		$log->VstorWebLog(LOG_ERROR, MOD_SYSTEM, "iscsi-initiator �ǳ�{$it_name}({$ip})ʧ�ܡ�", CN_LANG);
	}
}

// Ԥ������()
if( isset($_POST['m_disk_ra_list']) && isset($_POST['disk_setra_m']) )
{
	$ra = $_POST['m_disk_ra_list'];
	$disk = $_POST['disk_setra_m'];
	if( trim($disk) !== "" )
	{
		$retval = SetDiskRa($disk, $ra);
		if($retval === TRUE)
		{
			$message = $set_ra_ok_str[$lang];
			$log->VstorWebLog(LOG_INFOS, MOD_SYSTEM, "set disk({$disk}) read ahead[{$DiskRaData[$ra]}] ok.");
			$log->VstorWebLog(LOG_INFOS, MOD_SYSTEM, "���ô���({$disk})Ԥ��[{$DiskRaData[$ra]}]�ɹ���", CN_LANG);
		}
		else
		{
			$message = $set_ra_failed_str[$lang];
			$log->VstorWebLog(LOG_ERROR, MOD_SYSTEM, "set disk({$disk}) read ahead[{$DiskRaData[$ra]}] failed.");
			$log->VstorWebLog(LOG_ERROR, MOD_SYSTEM, "���ô���({$disk})Ԥ��[{$DiskRaData[$ra]}]ʧ�ܡ�", CN_LANG);
		}
	}
}

// Ԥ������()
if( isset($_POST['um_disk_ra_list']) && isset($_POST['disk_setra_um']) )
{
	$ra = $_POST['um_disk_ra_list'];
	$disk = $_POST['disk_setra_um'];
	if( trim($disk) !== "" )
	{
		$retval = SetDiskRa($disk, $ra);
		if($retval === TRUE)
		{
			$message = $set_ra_ok_str[$lang];
			$log->VstorWebLog(LOG_INFOS, MOD_SYSTEM, "set disk({$disk}) read ahead ok.");
			$log->VstorWebLog(LOG_INFOS, MOD_SYSTEM, "���ô���({$disk})Ԥ��[{$DiskRaData[$ra]}]�ɹ���", CN_LANG);
		}
		else
		{
			$message = $set_ra_failed_str[$lang];
			$log->VstorWebLog(LOG_ERROR, MOD_SYSTEM, "set disk({$disk}) read ahead failed.");
			$log->VstorWebLog(LOG_ERROR, MOD_SYSTEM, "���ô���({$disk})Ԥ��[{$DiskRaData[$ra]}]ʧ�ܡ�", CN_LANG);
		}
	}
}

// xfs�޸�
if( isset($_POST['xfsrepair_submit']) &&  isset($_POST['xfs_repair_disk']) )
{
	$disk = $_POST['xfs_repair_disk'];
	$retval = RepairXfs($disk);
	if($retval === TRUE)
	{
		$message = $xfs_repair_ok_str[$lang];
		$log->VstorWebLog(LOG_INFOS, MOD_SYSTEM, "xfs repair({$disk}) ok.");
		$log->VstorWebLog(LOG_INFOS, MOD_SYSTEM, "xfs�޸�({$disk})�ɹ���", CN_LANG);
	}
	else
	{
		$message = $xfs_repair_failed_str[$lang];
		$log->VstorWebLog(LOG_ERROR, MOD_SYSTEM, "xfs repair({$disk}) failed.");
		$log->VstorWebLog(LOG_ERROR, MOD_SYSTEM, "xfs�޸�({$disk})ʧ�ܡ�", CN_LANG);
	}
} 

// ��ʽ������
if( isset($_POST['format_submit']) && isset($_POST['fm_disk']) )
{
	$str = $_POST['fm_disk'];
	if( ! FormatDisk($str) )
	{
		$message = $format_str[$lang] . $failed_str[$lang];
	}
	else
	{
		$message = $format_str[$lang] . $ok_str[$lang];
	}
}

// ���ش���
if( isset($_POST['mount_submit']) && isset($_POST['fm_disk']) )
{
	$str = $_POST['fm_disk'];
	$log_msg = array();
	if($str == "local")
	{
		$log_msg = array(
			"���ر���Ŀ¼",
			"mount local directory "
		);
	}
	else
	{
		$str = $_POST['fm_disk'];
		$log_msg = array(
			"����{$str}",
			"mount {$str} "
		);
	} 
	
	if(! visMountDisk($str) )
	{
		$message = $mount_str[$lang] . $failed_str[$lang];
		$log->VstorWebLog(LOG_ERROR, MOD_SYSTEM, $log_msg[EN_LANG] ."failed.");
		$log->VstorWebLog(LOG_ERROR, MOD_SYSTEM, $log_msg[CN_LANG] ."ʧ�ܡ�", CN_LANG);
	}
	else
	{
		$message = $mount_str[$lang] . $ok_str[$lang];
		$log->VstorWebLog(LOG_INFOS, MOD_SYSTEM, $log_msg[EN_LANG] . "ok.");
		$log->VstorWebLog(LOG_INFOS, MOD_SYSTEM, $log_msg[CN_LANG] . "�ɹ���", CN_LANG);
	}
}

// ж�ش���
if( isset($_POST['unmount_submit']) && isset($_POST['um_disk']) )
{
	$str = $_POST['um_disk'];
	$log_msg = array();
	if($str == "local")
	{
		$log_msg = array(
			"ж�ر���Ŀ¼",
			"unmount local directory "
		);
	}
	else
	{
		$str = $_POST['um_disk'];
		$log_msg = array(
			"ж��{$str}",
			"unmount {$str} "
		);
	}
	
	if( ! visUnmountDisk($str) )
	{
		$message = $unmount_str[$lang] . $failed_str[$lang];
		$log->VstorWebLog(LOG_ERROR, MOD_SYSTEM, $log_msg[EN_LANG] ."failed.");
		$log->VstorWebLog(LOG_ERROR, MOD_SYSTEM, $log_msg[CN_LANG] ."ʧ�ܡ�", CN_LANG);
	}
	else
	{
		$message = $unmount_str[$lang] . $ok_str[$lang];
		$log->VstorWebLog(LOG_WARN, MOD_SYSTEM, $log_msg[EN_LANG] . "ok.");
		$log->VstorWebLog(LOG_WARN, MOD_SYSTEM, $log_msg[CN_LANG] . "�ɹ���", CN_LANG);
	}
}

// ���ñ���mysql
if( isset($_POST['set_bk_submit']) && isset($_POST['set_bk_dir']) )
{
	$bk_dir = $_POST['set_bk_dir'];
	if( SetMysqlStorageFileTableBk($bk_dir) === TRUE )
	{
		$message = $set_bk_ok_str[$lang];
	}
	else
	{
		$message = $set_bk_failed_str[$lang];
	}
}

// ȡ�����ñ���mydql
if( isset($_POST['unset_bk_submit']) && isset($_POST['unset_bk_dir']) )
{
	$bk_dir = $_POST['unset_bk_dir'];
	if( UnsetMysqlStorageFileTableBk($bk_dir) === TRUE )
	{
		$message = $unset_bk_ok_str[$lang];
	}
	else
	{
		$message = $unset_bk_failed_str[$lang];
	}
}

?>

<body>
<div>
<table align="center" width="100%">
	<tr>
	<td class="bar_nopanel"><?php print $ipsan_storage_config_str[$lang];?></td>
	</tr>
</table>
<form name="ipsan_ip_form" id="ipsan_ip_form" action="storage_target.php?ip=<?php print $ipsan_ip;?>" method="post">
  
  <table width="100%" border="0" align="center" cellpadding="6">
    <tr>
      <td class="field_title"><?php print $ipsan_ip_str[$lang];?></td>
      <td class="field_data1">
	  	<input type="text" maxlength="15" onchange="return check_ip();" onkeyup="return check_ip();" name="ipsan_ip" value="<?php print $ipsan_ip;?>">
		<span id="ipsan_ip_span"><img src='./images/error_icon.png' style="visibility: hidden;"></img></span>
		<input type="submit" 
		<?php 
		if($ipsan_ip=="" || !IsIpOk($ipsan_ip))
		{
			print " disabled=\"disabled\" ";
		}
		?>
		name="ipsan_ip_submit" value="<?php print $get_target_str[$lang];?>">
	  </td>
    </tr>
  </table>
</form>
</div>

<div>
<form name="ipsan_form" id="ipsan_form" action="storage_target.php?ip=<?php print $ipsan_ip;?>" method="post">
  <input type="hidden" name="it_conn_name" id="it_conn_name" value="">
  <table width="100%" border="0" align="center" cellpadding="6">
    <tr>
      <td colspan="2" class="title"><?php print  $ipsan_ip . ": " . $target_list_str[$lang];?></td>
      </tr>
    <tr>
      <td class="field_title"><?php print $target_name_str[$lang];?></td>
      <td class="field_title"><?php print $operation_str[$lang];?></td>
    </tr>
<?php
$bHasItem = FALSE;
$it_list = array(); 
$td_class = "field_data1";
if( IsIpOk($ipsan_ip) )
{
	$it_list = $iscsi_initiator->GetItList($ipsan_ip);
	if( $it_list !== FALSE )
	{
		foreach( $it_list as $entry )
		{
			print "<tr>";
			print "<td class=\"{$td_class}\">{$entry['target']}</td>";
			print "<td class=\"{$td_class}\">";
			if($iscsi_initiator->IsConnected($entry['server'], $entry['target']))
			{
				print $has_connected_str[$lang];
			}
			else
			{
				print "<input type=\"submit\" name=\"connect_submit\" onClick=\"return do_it_connect('{$entry['target']}', '{$entry['server']}', '{$confirm_connect_str[$lang]}');\" value=\"{$connect_str[$lang]}\" />";
			}
			print "</td>";
			print "</tr>";
			
			$bHasItem = TRUE;
			if($td_class == "field_data1")
			{
				$td_class = "field_data2";
			}
			else
			{
				$td_class = "field_data1";
			}
		}
	}
}
if($bHasItem === FALSE)
{
	print "<tr><td class=\"field_data2\" colspan=\"2\">{$none_str[$lang]}</td></tr>";
}

?>
  </table>
</form>
</div>

<div>
<!-- �Ͽ����Ӻ����ɻ�ȡ��IP��ַ��iscsi-targetĿ����Ϣ -->
<!--
<form name="ipsan_state_form" id="ipsan_state_form" action="storage_target.php?ip=<?php print $ipsan_ip;?>" method="post">
-->

<!-- �Ͽ����Ӻ󣬲��ٻ�ȡ��IP��ַ��iscsi-targetĿ����Ϣ -->
<form name="ipsan_state_form" id="ipsan_state_form" action="storage_target.php" method="post">

<input type="hidden" name="it_disconn_name" id="it_disconn_name" value="">
<table width="100%" border="0" align="center" cellpadding="6">
  <tr>
    <td colspan="3" class="title"><?php print $ipsan_state_str[$lang];?></td>
    </tr>
  <tr>
    <td class="field_title"><?php print $ipsan_ip_str[$lang];?></td>
    <td class="field_title"><?php print $target_name_str[$lang];?></td>
	<td class="field_title"><?php print $operation_str[$lang];?></td>
  </tr>
<?php 
$bHasItem = FALSE;
$it_list_connected = $iscsi_initiator->GetConnectedIt();
$td_class = "field_data1";
if($it_list_connected !== FALSE)
{
	foreach( $it_list_connected as $entry )
	{
		print "<tr>";
		print "<td class=\"{$td_class}\">
		<a href=\"http://{$entry['server']}\" target=\"_blank\" >{$entry['server']}</a>
		</td>";
		print "<td class=\"{$td_class}\">{$entry['target']}</td>";
		print "<td class=\"{$td_class}\">";
		$disk = GetDiskOfIt($entry['server'], $entry['target']);
		$disabled = "";
		if($disk !== FALSE && IsDiskMounted($disk))
		{
			$disabled = "disabled=\"disabled\"";
		}
		print "<input type=\"submit\" {$disabled} name=\"disconnect_submit\" onClick=\"return do_it_disconnect('{$entry['target']}', '{$entry['server']}', '{$confirm_disconnect_str[$lang]}');\" value=\"{$disconnect_str[$lang]}\" />";
		print "</td>";
		print "</tr>";
		
		$bHasItem = TRUE;
		if($td_class == "field_data1")
		{
			$td_class = "field_data2";
		}
		else
		{
			$td_class = "field_data1";
		}
	}
}
if($bHasItem === FALSE)
{
	print "<tr><td class=\"field_data2\" colspan=\"3\">{$none_str[$lang]}</td></tr>";
}
?>

</table>
</form>
</div>

<!-- -->

<div>
<table align="center" width="100%">
	<tr>
	<td class="bar_nopanel"><?php print $storage_management_str[$lang];?></td>
	</tr>
</table>
</div>
<div>
<form name="mount_disk_form" id="mount_disk_form" action="storage_target.php?ip=<?php print $ipsan_ip;?>" method="post">
<input type="hidden" id="fm_disk" name="fm_disk" value="" />
<input type="hidden" id="disk_setra_m" name="disk_setra_m" value="">
<input type="hidden" id="xfs_repair_disk" name="xfs_repair_disk" value="">

<table width="100%" border="0" align="center" cellpadding="6">
  <tr>
    <td colspan="6" class="title"><?php print $disk_not_mounted_str[$lang];?></td>
    </tr>
  <tr>
    <td class="field_title"><?php print $disk_str[$lang];?></td>
    <td class="field_title"><?php print $total_size_str[$lang];?></td>
    <td class="field_title"><?php print $fs_type_str[$lang];?></td>
    <td class="field_title"><?php print $disk_origin_str[$lang];?></td>
    <td class="field_title"><?php print $readahead_str[$lang];?></td>
    <td class="field_title"><?php print $operation_str[$lang];?></td>
  </tr>
  
<?php 

$bHasItem = FALSE;
$disk_unmounted_list = GetUnmountedDiskList();
$td_class = "field_data1";
if(count($disk_unmounted_list) != 0)
{
	foreach( $disk_unmounted_list as $entry )
	{
		$diskinfo = GetDiskInfo($entry);
		if($diskinfo === FALSE)
			continue;
		$diskname = substr( strrchr($diskinfo['disk'], "/"), 1);
		print "<tr>";
		print "<td class=\"{$td_class}\">{$diskinfo['name']}</td>";
		print "<td class=\"{$td_class}\">{$diskinfo['size']}</td>";
		print "<td class=\"{$td_class}\">{$diskinfo['fs']}</td>";
		print "<td class=\"{$td_class}\">" . GetDiskOrigin($diskinfo['disk']) . "</td>";
		print "<td class=\"{$td_class}\">";
		// Ԥ��
		$ra = GetDiskRa($entry);
		print "<select name=\"m_disk_ra_list\" id=\"m_disk_ra_list\" onChange=\"SetRa_mform('{$entry}','{$confirm_setra_str[$lang]}');\">";
	  	print_ra_of_select($ra);
	  	print "</select>";
		print "</td>";
		print "<td class=\"{$td_class}\">";
		$disabled = "";
		print "<input type=\"button\" name=\"format_button\" onClick=\"return do_format('{$diskinfo['disk']}', '{$diskinfo['name']}', '{$ipsan_ip}');\" value=\"{$format_str[$lang]}\" />&ensp;";
		if( ! IsDiskFormatted($diskinfo['disk']) )
		{
			$disabled = "disabled=\"disabled\"";
		}
		print "<input type=\"submit\" {$disabled} name=\"xfsrepair_submit\" onClick=\"return set_disk_xfsrepair('{$entry}');\"  value=\"{$xfs_repair_str[$lang]}\" />&ensp;";
		print "<input type=\"submit\" {$disabled} name=\"mount_submit\"  onClick=\"return do_mount('{$diskinfo['disk']}', '$confirm_mount_str[$lang]');\" value=\"{$mount_str[$lang]}\" />";

		print "</td>";
		print "</tr>";
		
		$bHasItem = TRUE;
		if($td_class == "field_data1")
		{
			$td_class = "field_data2";
		}
		else
		{
			$td_class = "field_data1";
		}
	}
}
// ��洢Ŀ¼
$lv_list = array();
if( ($lv_list=$logicVolume->GetLvList()) !== FALSE )
{
	$index = 1;
	foreach( $lv_list as $lv )
	{
		if( $lv['mountdir']=="" && ($logicVolume->IsAsItLun($lv['vg'], $lv['name'])===FALSE) )
		{
			$bHasItem = TRUE;
			if($td_class == "field_data1")
			{
				$td_class = "field_data2";
			}
			else
			{
				$td_class = "field_data1";
			}
			print "<tr>";
			print "<td class=\"{$td_class}\">{$lv['origin']}{$index}</td>";
			print "<td class=\"{$td_class}\">{$lv['size']}</td>";
			print "<td class=\"{$td_class}\">" . GetDiskFsType($lv['path']) . "</td>";
			print "<td class=\"{$td_class}\">{$lv['origin']}</td>";
			print "<td class=\"{$td_class}\">";
			// Ԥ��
			$ra = $logicVolume->GetLvRa($lv['path']);
			print "<select name=\"m_disk_ra_list\" id=\"m_disk_ra_list\" onChange=\"SetRa_mform('{$lv['path']}','{$confirm_setra_str[$lang]}');\">";
		  	print_ra_of_select($ra);
		  	print "</select>";
			print "</td>";
			print "<td class=\"{$td_class}\">";
			$disabled = "";
			print "<input type=\"button\" name=\"format_button\" onClick=\"return do_format('{$lv['path']}', '{{$lv['origin']}{$index}}', '{$ipsan_ip}');\" value=\"{$format_str[$lang]}\" />&ensp;";
			if( ! IsDiskFormatted($lv['path']) )
			{
				$disabled = "disabled=\"disabled\"";
			}
			print "<input type=\"submit\" {$disabled} name=\"xfsrepair_submit\" onClick=\"return set_disk_xfsrepair('{$lv['path']}');\"  value=\"{$xfs_repair_str[$lang]}\" />&ensp;";
			print "<input type=\"submit\" {$disabled} name=\"mount_submit\"  onClick=\"return do_mount('{$lv['mapper']}', '$confirm_mount_str[$lang]');\" value=\"{$mount_str[$lang]}\" />";
	
			print "</td>";
			print "</tr>";
			$index++;
		}
	}
}

// ���ش洢Ŀ¼�����û��ʹ�ã����г�
if( ! visIsLocalStorageSet() )
{
	$bHasItem = TRUE;
	$local_info = visGetLocalStorageInfo();
	print "<tr class=\"different\">";
	print "<td class=\"{$td_class}\">{$local_info['origin']}</td>";
	print "<td class=\"{$td_class}\">{$local_info['size']}</td>";
	print "<td class=\"{$td_class}\">{$local_info['fs']}</td>";
	print "<td class=\"{$td_class}\">{$local_info['origin']}</td>";
	print "<td class=\"{$td_class}\">";
	print "-";
	print "</td>";
	print "<td class=\"{$td_class}\">";
	print "<input disabled=\"disabled\" style=\"visibility: visible;\" type=\"button\" name=\"format_button\"  value=\"{$format_str[$lang]}\" />&ensp;";
	print "<input disabled=\"disabled\" style=\"visibility: visible;\" type=\"submit\" name=\"xfsrepair_submit\"  value=\"{$xfs_repair_str[$lang]}\" />&ensp;";
	print "<input type=\"submit\" name=\"mount_submit\"  onClick=\"return do_mount('local', '$confirm_mount_str[$lang]');\" value=\"{$mount_str[$lang]}\" />";
	print "</td";
	print "</tr>";
}

if($bHasItem === FALSE)
{
	print "<tr><td class=\"field_data2\" colspan=\"6\">{$none_str[$lang]}</td></tr>";
}

?>
</table>
</form>
</div>
<div>
<form name="unmount_disk_form" id="unmount_disk_form" action="storage_target.php?ip=<?php print $ipsan_ip;?>" method="post">
<input type="hidden" name="um_disk" id="um_disk" value="" />
<input type="hidden" name="set_bk_dir" id="set_bk_dir" value="" />
<input type="hidden" name="unset_bk_dir" id="unset_bk_dir" value="" />
<table width="100%" border="0" align="center" cellpadding="6">
  <tr>
    <td colspan="8" class="title"><?php print $disk_mounted_str[$lang];?></td>
    </tr>
  <tr>
    <td class="field_title"><?php print $disk_str[$lang];?></td>
    <td class="field_title"><?php print $fs_type_str[$lang];?></td>
    <td class="field_title"><?php print $disk_origin_str[$lang];?></td>
    <td class="field_title"><?php print $readahead_str[$lang];?></td>
    <td class="field_title"><?php print $total_size_str[$lang];?></td>
    <td class="field_title"><?php print $free_size_str[$lang];?></td>
    <td class="field_title"><?php print $usage_str[$lang];?></td>
    <td class="field_title"><?php print $operation_str[$lang];?></td>
  </tr>
<?php
$bHasItem = FALSE;
$disk_mounted_list = GetMountedDiskList();
if(count($disk_mounted_list) != 0)
{
	$td_class = "field_data1";
	foreach( $disk_mounted_list as $entry )
	{
		$diskinfo = GetDiskInfo($entry);
		if($diskinfo === FALSE)
			continue;
		$diskname = substr( strrchr($diskinfo['disk'], "/"), 1);
		print "<tr>";
		print "<td class=\"{$td_class}\">{$diskinfo['name']}</td>";
		print "<td class=\"{$td_class}\">{$diskinfo['fs']}</td>";
		print "<td class=\"{$td_class}\">" . GetDiskOrigin($diskinfo['disk']) . "</td>";
		print "<td class=\"{$td_class}\">";
		// Ԥ��
		$ra = GetDiskRa($entry);
		print "<input type=\"hidden\" name=\"disk_setra_um\" value=\"\">";
		print "<select name=\"um_disk_ra_list\" id=\"um_disk_ra_list\" onChange=\"SetRa_umform('{$entry}','{$confirm_setra_str[$lang]}');\">";
	  	print_ra_of_select($ra);
	  	print "</select>";
		print "</td>";
		print "<td class=\"{$td_class}\">{$diskinfo['size']}</td>";
		print "<td class=\"{$td_class}\">{$diskinfo['free']}</td>";
		print "<td class=\"{$td_class}\">" . create_percent_bar($diskinfo['usage']) . "</td>";
		print "<td class=\"{$td_class}\">";
		$disabled = "";
		if( IsDirUsing($diskinfo['mountdir']) )
		{
			$disabled = "disabled=\"disabled\"";
		}
		print "<input type=\"submit\" {$disabled} name=\"unmount_submit\" onClick=\"return do_unmount('{$diskinfo['disk']}', '{$confirm_unmount_str[$lang]}')\" value=\"{$unmount_str[$lang]}\" />";
/*
		if( IsMysqlStorageFileBkDir($diskinfo['mountdir']) === FALSE )
		{
			print "<input type=\"submit\" name=\"set_bk_submit\" onClick=\"return do_set_bk('{$diskinfo['mountdir']}', '{$confirm_set_bk_str[$lang]}')\" value=\"{$mysqldump_str[$lang]}\" />";
		}
		else
		{
			print "<input type=\"submit\" name=\"unset_bk_submit\" onClick=\"return do_unset_bk('{$diskinfo['mountdir']}', '{$confirm_unset_bk_str[$lang]}')\" value=\"{$cancel_mysqldump_str[$lang]}\" />";
		}
*/
		
		print "</td>";
		print "</tr>";
		
		$bHasItem = TRUE;
		if($td_class == "field_data1")
		{
			$td_class = "field_data2";
		}
		else
		{
			$td_class = "field_data1";
		}
	}
}
// ��洢Ŀ¼
$lv_list = array();
if( ($lv_list=$logicVolume->GetLvList()) !== FALSE )
{
	$index = 1;
	foreach( $lv_list as $lv )
	{
		if( $lv['mountdir']!="" && ($logicVolume->IsAsItLun($lv['vg'], $lv['name'])===FALSE) )
		{
			$bHasItem = TRUE;
			if($td_class == "field_data1")
			{
				$td_class = "field_data2";
			}
			else
			{
				$td_class = "field_data1";
			}
			print "<tr>";
			print "<td class=\"{$td_class}\">{$lv['origin']}{$index}</td>";
			print "<td class=\"{$td_class}\">" . GetDiskFsType($lv['path']) . "</td>";
			print "<td class=\"{$td_class}\">{$lv['origin']}</td>";
			print "<td class=\"{$td_class}\">";
			// Ԥ��
			$ra = $logicVolume->GetLvRa($lv['path']);
			print "<select name=\"m_disk_ra_list\" id=\"m_disk_ra_list\" onChange=\"SetRa_mform('{$lv['path']}','{$confirm_setra_str[$lang]}');\">";
		  	print_ra_of_select($ra);
		  	print "</select>";
			print "</td>";
			print "<td class=\"{$td_class}\">{$lv['size']}</td>";
			print "<td class=\"{$td_class}\">{$lv['free']}</td>";
			print "<td class=\"{$td_class}\">" . create_percent_bar($lv['usage']) . "</td>";
			print "<td class=\"{$td_class}\">";
			$disabled = "";
			if( IsDirUsing($lv['mountdir']) )
			{
				$disabled = "disabled=\"disabled\"";
			}
			print "<input type=\"submit\" {$disabled} name=\"unmount_submit\" onClick=\"return do_unmount('{$lv['mapper']}', '{$confirm_unmount_str[$lang]}')\" value=\"{$unmount_str[$lang]}\" />";
					
			print "</td>";
			print "</tr>";
			$index++;
		}
	}
}


// ���ش洢Ŀ¼�����ʹ�ã����г�
if( visIsLocalStorageSet() )
{
	$bHasItem = TRUE;
	$local_info = visGetLocalStorageInfo();
	print "<tr class=\"different\">";
	print "<td class=\"{$td_class}\">{$local_info['origin']}</td>";
	print "<td class=\"{$td_class}\">{$local_info['fs']}</td>";
	print "<td class=\"{$td_class}\">{$local_info['origin']}</td>";
	print "<td class=\"{$td_class}\">";
	// Ԥ��
	print "-";
	print "</td>";
	print "<td class=\"{$td_class}\">{$local_info['size']}</td>";
	print "<td class=\"{$td_class}\">{$local_info['free']}</td>";
	print "<td class=\"{$td_class}\">" . create_percent_bar("{$local_info['usage']}") . "</td>";
	print "<td class=\"{$td_class}\">";
	print "<input type=\"submit\" name=\"unmount_submit\"  onClick=\"return do_unmount('local', '$confirm_unmount_str[$lang]');\" value=\"{$unmount_str[$lang]}\" />";
	print "</td>";
	print "</tr>";
}

if($bHasItem === FALSE)
{
	print "<tr><td class=\"field_data2\" colspan=\"8\">{$none_str[$lang]}</td></tr>";
}

?>
</table>
</form>
<?php 
if($message != "")
{
	print_msg_block($message);
}
?>
</div>
</body>
</html>
