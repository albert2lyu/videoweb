<?php 
require_once("./include/function.php");
require_once("./include/nfs_server.php");
require_once("./include/iscsi_initiator.php");
require_once("./include/disk.php");

if( !IsVisExisted())
	exit("no vis server!");

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
$readahead_str=array(
	"Ԥ����С",
	"Readahead size"
);
$xfs_repair_str=array(
	"xfs�޸�",
	"xfs Repair"
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
	"�ļ�ϵͳ����",
	"Filesystem Type"
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
$ok_str=array(
	"�ɹ�",
	"OK"
);
$failed_str=array(
	"ʧ��",
	"failed"
);
$none_str=array(
	"- �� -",
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
	"Disk not Mounted"
);
$disk_mounted_str=array(
	"�ѹ��صĴ���",
	"Disk Has been Mounted"
);
$disk_origin_str=array(
	"������Դ",
	"Disk Origin"
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
$ip_error_str=array(
	"IP��ַ����!",
	"IP address is incorrect!"
);
$xfs_repair_ok_str=array(
	"xfs�޸��ɹ���",
	"xfs repair OK."
);
$xfs_repair_failed_str=array(
	"xfs�޸�ʧ�ܣ�",
	"xfs repair FAILED!"
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
<script type="text/javascript">
function set_disk_xfsrepair(disk)
{
	document.va_storage_form.xfs_repair_disk.value=disk;
	document.va_storage_form.submit();
	return true;
}
function SetRa_mform(disk, msg)
{
	if( confirm(msg) )
	{
		document.va_storage_form.disk_setra_m.value=disk;
		document.va_storage_form.submit();
		return true;
	}
	else
	{
		return false;
	}
}
function SetRa_umform(disk, msg)
{
	if( confirm(msg) )
	{
		document.va_storage_form.disk_setra_um.value=disk;
		document.va_storage_form.submit();
		return true;
	}
	else
	{
		return false;
	}
}
function do_it_connect(it, ip, msg)
{
	document.va_storage_form.it_conn_name.value = it + ";" + ip;
	if( confirm(msg) )
		return true;
	else
		return false;
}
function do_it_disconnect(it, ip, msg)
{
	document.va_storage_form.it_disconn_name.value = it + ";" + ip;
	if( confirm(msg) )
		return true;
	else
		return false;
}
function do_format(disk, name, ip)
{
	var page='formatdisk.php?ppage=va_storage_target.php&' + 'disk=' + disk + '&name=' + name + '&ip=' + ip;
	window.open (
			 page,
			 'format_window',
			 'height=200,width=360,top=300,left=400,resizable=no,location=no,status=yes'
	);
	return true;
}
function do_mount(disk, msg)
{
	document.va_storage_form.fm_disk.value = disk;
	if( confirm(msg) )
		return true;
	else
		return false;
}

function do_unmount(disk, msg)
{
	document.va_storage_form.um_disk.value = disk;
	if( confirm(msg) )
		return true;
	else
		return false;
}
</script>
</head>


<?php 
/*
 * ��������
 */
/*
if( isset($_GET['access']) && $_GET['access']=="yes" )
{
	
}
else
{
	exit("No Access!");
}
*/
$message = "";
$iscsi_initiator = new IscsiInitiator();

$ipsan_ip = "";
if( isset($_GET['ip']) )
{
	$ipsan_ip = trim($_GET['ip']);
}
// ��ȡ�洢ip
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
	
	$iscsi_initiator->LoginIt($ip, $it_name);
}
// �Ͽ�����iscsi-targetĿ��
if( isset($_POST['disconnect_submit']) && isset($_POST['it_disconn_name']))
{
	$str = $_POST['it_disconn_name'];
	$items = explode(";", $str);
	$ip = $items[1];
	$it_name = $items[0];
	
	$iscsi_initiator->LogoutIt($ip, $it_name);
}

// ��ʽ������
if( isset($_POST['format_submit']) && isset($_POST['fm_disk']) )
{
	$str = "/dev/" . $_POST['fm_disk'];
	if( ! FormatDisk($str) )
	{
		$message = $format_str[$lang] . $failed_str[$lang];
	}
	else
	{
		$message = $format_str[$lang] . $ok_str[$lang];
	}
}

// Ԥ������()
if( isset($_POST['m_disk_ra_list']) && isset($_POST['disk_setra_m']) )
{
	$ra = $_POST['m_disk_ra_list'];
	$disk = $_POST['disk_setra_m'];
	$retval = SetDiskRa($disk, $ra);
	if($retval === TRUE)
	{
		$message = $set_ra_ok_str[$lang];
	}
	else
	{
		$message = $set_ra_failed_str[$lang];
	}
}

// Ԥ������()
if( isset($_POST['um_disk_ra_list']) && isset($_POST['disk_setra_um']) )
{
	$ra = $_POST['um_disk_ra_list'];
	$disk = $_POST['disk_setra_um'];
	$retval = SetDiskRa($disk, $ra);
	if($retval === TRUE)
	{
		$message = $set_ra_ok_str[$lang];
	}
	else
	{
		$message = $set_ra_failed_str[$lang];
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
	}
	else
	{
		$message = $xfs_repair_failed_str[$lang];
	}
} 

// ���ش���
if( isset($_POST['mount_submit']) && isset($_POST['fm_disk']) )
{
	$str = $_POST['fm_disk'];
	if($str == "local")
	{
		// nothing
	}
	else
	{
		$str = "/dev/" . $_POST['fm_disk'];
	}
	
	if(! visMountDisk($str) )
	{
		$message = $mount_str[$lang] . $failed_str[$lang];
	}
	else
	{
		$message = $mount_str[$lang] . $ok_str[$lang];
	}
}

// ж�ش���
if( isset($_POST['unmount_submit']) && isset($_POST['um_disk']) )
{
	$str = $_POST['um_disk'];
	if($str == "local")
	{
		// nothing
	}
	else
	{
		$str = "/dev/" . $_POST['um_disk'];
	}
	
	if(! visUnmountDisk($str) )
	{
		$message = $unmount_str[$lang] . $failed_str[$lang];
	}
	else
	{
		$message = $unmount_str[$lang] . $ok_str[$lang];
	}
}
?>


<body>

<table align="center" width="100%">
	<tr>
	<td class="bar_nopanel"><?php print $ipsan_storage_config_str[$lang];?></td>
	</tr>
</table>

<form name="va_storage_form" id="va_storage_form" action="va_storage_target.php?ip=<?php print $ipsan_ip;?>" method="post">
  <table width="80%" border="0" align="center" cellpadding="6">
    <tr>
      <td class="field_title"><?php print $ipsan_ip_str[$lang];?></td>
      <td class="field_data1">
	  	<input type="text" maxlength="15" name="ipsan_ip" value="<?php print $ipsan_ip;?>">
		&emsp;
		<input type="submit" name="ipsan_ip_submit" value="<?php print $get_target_str[$lang];?>">
	  </td>
    </tr>
  </table>


  <input type="hidden" name="it_conn_name" id="it_conn_name" value="">
  <table width="80%" border="0" align="center" cellpadding="6">
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
if( IsIpOk($ipsan_ip) )
{
	$it_list = $iscsi_initiator->GetItList($ipsan_ip);
	if( $it_list !== FALSE )
	{
		$td_class = "field_data1";
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

<input type="hidden" name="it_disconn_name" id="it_disconn_name" value="">
<table width="80%" border="0" align="center" cellpadding="6">
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
if($it_list_connected !== FALSE)
{
	$td_class = "field_data1";
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

<!-- -->

<table align="center" width="100%">
	<tr>
	<td class="bar_nopanel"><?php print $storage_management_str[$lang];?></td>
	</tr>
</table>

<input type="hidden" name="fm_disk" id="fm_disk" value="" />
<input type="hidden" name="xfs_repair_disk" value="">
<table width="85%" border="0" align="center" cellpadding="6">
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
if(count($disk_unmounted_list) != 0)
{
	$td_class = "field_data1";
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
		print "<input type=\"hidden\" name=\"disk_setra_m\" value=\"\">";
		print "<select name=\"m_disk_ra_list\" id=\"m_disk_ra_list\" onChange=\"SetRa_mform('{$entry}','{$confirm_setra_str[$lang]}');\">";
	  	print_ra_of_select($ra);
	  	print "</select>";
		print "</td>";
		print "<td class=\"{$td_class}\">";
		$disabled = "";
		print "<input type=\"button\" name=\"format_button\" onClick=\"return do_format('{$diskname}', '{$diskinfo['name']}', '{$ipsan_ip}');\" value=\"{$format_str[$lang]}\" />&ensp;";
		if( ! IsDiskFormatted($diskinfo['disk']) )
		{
			$disabled = "disabled=\"disabled\"";
		}
		print "<input type=\"submit\" {$disabled} name=\"xfsrepair_submit\" onClick=\"return set_disk_xfsrepair('{$entry}');\"  value=\"{$xfs_repair_str[$lang]}\" />&ensp;";
		print "<input disabled=\"disabled\" style=\"visibility: visible;\" type=\"submit\" name=\"xfsrepair_submit\"  value=\"{$xfs_repair_str[$lang]}\" />&ensp;";
		print "<input type=\"submit\" {$disabled} name=\"mount_submit\"  onClick=\"return do_mount('{$diskname}', '$confirm_mount_str[$lang]');\" value=\"{$mount_str[$lang]}\" />";
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

<input type="hidden" name="um_disk" id="um_disk" value="" />
<table width="85%" border="0" align="center" cellpadding="6">
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
		print "<input type=\"submit\" {$disabled} name=\"unmount_submit\" onClick=\"return do_unmount('{$diskname}', '{$confirm_unmount_str[$lang]}')\" value=\"{$unmount_str[$lang]}\" />";

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
</body>
</html>
