<?php 
require_once("./include/function.php");
require_once("./include/nfs_client.php");

if( !IsVisExisted())
	exit("no vis server!");


$lang=load_lang();

$vod_server_config_str=array(
	"VOD服务器设置",
	"VOD Server Config"
);
$storage_ip_str=array(
	"存储服务器IP地址",
	"Storage ip address"
);
$nfs_shared_list_str=array(
	"共享列表",
	"Shared List"
);
$get_target_str=array(
	"获取目标",
	"Get targets"
);
$target_dir_str=array(
	"目标目录",
	"Target"
);
$hosts_to_share_str=array(
	"共享的网络/主机",
	"Network/Host Shared to"
);
$storage_str=array(
	"存储服务器",
	"Storage Server"
);
$operation_str=array(
	"操作",
	"Operate"
);
$mount_str=array(
	"挂载",
	"Mount"
);
$unmount_str=array(
	"卸载",
	"Unmount"
);
$storage_connect_state_str=array(
	"VOD服务器共享目录挂载信息",
	"VOD Server Mounted Information"
);
$mounted_shareddir_list_str=array(
	"挂载的NFS共享目录列表",
	"Mounted NFS List"
);
$ip_error_str=array(
	"IP格式错误",
	"It's not a IP address"
);
$ok_str=array(
	"成功",
	"OK"
);
$failed_str=array(
	"失败",
	"failed"
);
$none_str=array(
	"- 无 -",
	"- None -"
);
$confirm_mount_str=array(
	"确认挂载？",
	"Confirm to mount?"
);
$confirm_unmount_str=array(
	"确认卸载？",
	"Confirm to unmount?"
);
$has_mounted_str=array(
	"已挂载",
	"Has Mounted"
);
?>

<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\" <html xmlns="http://www.w3.org/1999/xhtml">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<link rel="stylesheet" href="css/target.css" type="text/css" />
<script defer type="text/javascript" src="js/pngfix.js"></script>
<script type="text/javascript">
function do_submit(value, msg)
{
	document.storage_form.text1.value = value;
	if( confirm(msg) )
		return true;
	else
		return false;
}
function do_submit2(value1, value2, msg)
{
	document.storage_state_form.text2.value = value1 + ";" + value2;
	if( confirm(msg) )
		return true;
	else
		return false;
}
</script>
</head>

<body>
<?php 
/*
 * 表单处理部分
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

$storage_ip = "";
if( isset($_GET['sip']) )
{
	$storage_ip = trim($_GET['sip']);
}
if( isset($_POST['storage_server_ip']) )
{
	$storage_ip = trim($_POST['storage_server_ip']);
}

$nfs_client = new NfsClient();

// 挂载处理
$sharedir = "";
if( $storage_ip != "" && IsIpOk($storage_ip) )
{
	if( isset($_POST['text1']) )
	{
		$sharedir = $_POST['text1'];
		//保证都挂载在/mnt目录下面
		if(!preg_match("|^(/mnt)|i", $sharedir))
			$mountdir = "/mnt/" . $sharedir;
		else
			$mountdir = $sharedir;
	
		// 创建目录
		exec("export LANG=C; /usr/bin/sudo /bin/mkdir -p " . $mountdir);
		$nfs_client->Mount($sharedir, $mountdir, $storage_ip);
	}
}

// 卸载处理
$sharedir = "";
if( isset($_POST['text2']) )
{
	$str = $_POST['text2'];
	$items = explode(";", $str);
	$sharedir = $items[0];
	$server = $items[1];
	$nfs_client->UnMount($sharedir, $server);
}
?>

<div>
<table align="center" width="100%">
	<tr>
	<td class="bar_nopanel"><?php print $vod_server_config_str[$lang];?></td>
	</tr>
</table>

<form name="storage_ip_form" id="storage_ip_form" action="va_vod_target.php?sip=<?php print $storage_ip;?>" method="post">
<table width="70%" border="0" cellpadding="6" align="center">
  <tr>
    <td class="field_title"><?php print $storage_ip_str[$lang];?></td>
    <td class="field_data1">
		<input name="storage_server_ip" maxlength="15" type="text" class="field_data1" value="<?php print $storage_ip;?>">
		&emsp;
		<input type="submit" name="storage_ip_submit" value="<?php print $get_target_str[$lang];?>">
	</td>
  </tr>
</table>
</form>

<form name="storage_form" id="storage_form" action="va_vod_target.php?sip=<?php print $storage_ip;?>" method="post">
  <input type="hidden" name="text1" id="text1" value="">
  <table width="70%" border="0" align="center" cellpadding="6">
    <tr>
      <td colspan="3" class="title"><?php print  $storage_ip . ": " . $nfs_shared_list_str[$lang];?></td>
    </tr>
    <tr>
      <td class="field_title"><?php print $target_dir_str[$lang];?></td>
      <td class="field_title"><?php print $hosts_to_share_str[$lang];?></td>
	  <td class="field_title"><?php print $operation_str[$lang];?></td>
    </tr>
<?php 
$bHasItem = FALSE;
if( $storage_ip != "" && IsIpOk($storage_ip) )
{
	$shareList = $nfs_client->GetShareLists($storage_ip);
	if($shareList !== FALSE)
	{
		$td_class = "field_data1";
		foreach( $shareList as $entry )
		{
			print "<tr>";
			print "<td class=\"{$td_class}\">{$entry['sharedir']}</td>";
			print "<td class=\"{$td_class}\">{$entry['hosts']}</td>";
			print "<td class=\"{$td_class}\">";
			if( $nfs_client->IsMounted($entry['sharedir'], $storage_ip) )
			{
				print $has_mounted_str[$lang];
			}
			else
			{
				print "<input type=\"submit\" name=\"submit\" onClick=\"return do_submit('{$entry['sharedir']}', '{$confirm_mount_str[$lang]}');\" value=\"{$mount_str[$lang]}\" />";
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
if($bHasItem == FALSE)
{
	print "<tr><td class=\"field_data2\" colspan=\"3\">{$none_str[$lang]}</td></tr>";
}

?>
  </table>
</form>
 
 <!-- -->
<table align="center" width="100%">
	<tr>
	<td class="bar_nopanel"><?php print $storage_connect_state_str[$lang];?></td>
	</tr>
</table>

<form name="storage_state_form" id="storage_state_form" action="va_vod_target.php?sip=<?php print $storage_ip;?>" method="post">
  <input type="hidden" name="text2" id="text2" value="">
  <table width="70%" border="0" align="center" cellpadding="6">
    <tr>
      <td colspan="3" class="title"><?php print  $mounted_shareddir_list_str[$lang];?></td>
    </tr>
    <tr>
	  <td class="field_title"><?php print $storage_str[$lang];?></td>
      <td class="field_title"><?php print $target_dir_str[$lang];?></td>
      <td class="field_title"><?php print $operation_str[$lang];?></td>
    </tr>
<?php 
$bHasItem = FALSE;

$mountedList = $nfs_client->GetNfsMounted();
if( $mountedList !== FALSE )
{
	$td_class = "field_data1";
	foreach( $mountedList as $entry )
	{
		print "<tr>";
		print "<td class=\"{$td_class}\">
		<a href=\"http://{$entry['server']}\" target=\"_blank\" >{$entry['server']}</a>
		</td>";
		print "<td class=\"{$td_class}\">{$entry['sharedir']}</td>";
		print "<td class=\"{$td_class}\">";
		if(IsDirUsing($entry['mountdir']))
		{
			$disabled = "disabled=\"disabled\"";
		}
		else
		{
			$disabled = "";
		}
		print "<input type=\"submit\" name=\"submit\" {$disabled} onClick=\"return do_submit2('{$entry['sharedir']}', '{$entry['server']}', '{$confirm_unmount_str[$lang]}');\" value=\"{$unmount_str[$lang]}\" />";
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

if($bHasItem == FALSE)
{
	print "<tr><td class=\"field_data2\" colspan=\"3\">{$none_str[$lang]}</td></tr>";
}
?>
  </table>
</form>

</div>
</body>
</html>
