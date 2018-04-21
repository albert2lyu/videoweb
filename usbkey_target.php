<?php
require("./include/authenticated.php");
require_once("./include/function.php");
require_once("./include/log.php");
require_once("./include/usbkey.php");

if( !IsVisExisted())
	exit("no vis server!");


$lang=load_lang();

$mvplic_management_str=array(
	"UsbKey 授权管理",
	"UsbKey Management"
);
$mvplic_info_str=array(
	"UsbKey 信息",
	"UsbKey Information"
);
$mvplic_authen_info_str=array(
	"授权信息：",
	"Authorization Information: "
);
$mvplic_basic_info_str=array(
	"基本信息：",
	"Basic Information: "
);
$authen_version_str=array(
	"授权方案版本",
	"Authorization Version"
);
$authen_product_str=array(
	"授权于产品",
	"Authenticate to Product"
);
$rom_id_str=array(
	"ROM ID",
	"ROM ID"
);
$module_name_str=array(
	"模块代码",
	"Module Code"
);
$authen_count_str=array(
	"授权数量",
	"Authorization count"
);
$req_update_key_str=array(
	"请求升级UsbKey",
	"Require to Update UsbKey"
);
$update_key_str=array(
	"应用升级UsbKey",
	"Apply to Update UsbKey"
);
$req_update_tip_str=array(
	"下载请求升级的文件（*.req）。",
	"Download file to require to update usbkey(*.req)."
);
$update_key_tip_str=array(
	"选择升级文件（*.upk）升级当前UsbKey。",
	"Select proper file(*.upk) to update current usbkey."
);
$mvplic_no_key_str=array(
	"没有找到UsbKey",
	"No UsbKey Found"
);
$mvplic_no_auth_str=array(
	"没有任何授权",
	"None Authorization"
);
$confirm_update_usbkey_str=array(
	"确认升级UsbKey？",
	"Confirm to update usbkey?"
);
$none_str=array(
	"- 无 -",
	"- none -"
);
$req_updatekey_ok_str=array(
	"请求升级Key成功。",
	"Require to update key OK."
);
$req_updatekey_failed_str=array(
	"请求升级Key失败！",
	"Require to update key failed!"
);
$update_key_ok_str=array(
	"更新Key程序成功。",
	"Updated key OK."
);
$update_key_failed_str=array(
	"更新Key程序失败！",
	"Updated key failed！"
);
$detect_key_str=array(
	"正在检测Key……",
	"Detecting Key..."
);
?>

<?php 
/*
 * 表单处理 --------------begin
 */
$message = "";
$log = new Log();
$mvplic = new UsbKey();

/*
 * 请求升级key
 */
while( isset($_POST['req_update_submit']) )
{
	$req_file = $mvplic->GetReqUpdateFile();
	if($req_file === FALSE)
	{
		$message = $req_updatekey_failed_str[$lang];
		$log->VstorWebLog(LOG_ERROR, MOD_VIS, "require to update key failed.");
		$log->VstorWebLog(LOG_ERROR, MOD_VIS, "请求升级Key失败。", CN_LANG);
		break;
	}
	//print $req_file;
	DownLoadFile($req_file);
	$message = $req_updatekey_ok_str[$lang];
	$log->VstorWebLog(LOG_INFOS, MOD_VIS, "require to update key ok.");
	$log->VstorWebLog(LOG_INFOS, MOD_VIS, "请求升级Key成功。", CN_LANG);
	
	// 删除备份留在服务器的文件
	$command = "export LANG=C; /usr/bin/sudo /bin/rm -f ". $req_file;
	exec($command);
	
	break;
}

/*
 * 应用升级key
 */
$update_file = "";
while( isset($_POST['update_key_submit']) )
{
	
	if(
		is_uploaded_file($_FILES['key_update_file']['tmp_name']) 
		&& 
		substr(strrchr($_FILES['key_update_file']['name'], "."), 1)=="info"
		&& 
		$_FILES['key_update_file']['size']<2097152
		&& 
		$_FILES['key_update_file']['size']>0
	)
	{
		$update_file = "/tmp/" . $_FILES['key_update_file']['name'];
		move_uploaded_file($_FILES['key_update_file']['tmp_name'], $update_file);
		// 应用升级
		$retval = $mvplic->UpdateUsbKey($update_file);
		// 升级成功
		if($retval === TRUE)
		{
			$message = $update_key_ok_str[$lang];
			$log->VstorWebLog(LOG_INFOS, MOD_VIS, "update key ok.");
			$log->VstorWebLog(LOG_INFOS, MOD_VIS, "升级Key成功。", CN_LANG);
			break;
		}		
	}// if
	
	// 升级失败
	$message = $update_key_failed_str[$lang];
	$log->VstorWebLog(LOG_ERROR, MOD_VIS, "update key failed.");
	$log->VstorWebLog(LOG_ERROR, MOD_VIS, "升级Key失败。", CN_LANG);
	break;
}
// 删除上传的文件
if($update_file != "")
{
	$command = "export LANG=C; /usr/bin/sudo /bin/rm -f {$update_file}";
	exec($command);
}
/*
 * 表单处理---------------end
 */
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<link rel="stylesheet" href="css/target.css" type="text/css" />
<script type="text/javascript" language="javascript" src="js/ajax_function.js"></script>
<script defer type="text/javascript" src="js/pngfix.js"></script>

<script type="text/javascript">
function updatekey_confirm(msg)
{
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

<body>

<div id="load_div">
<br/><br/><br/><br/><br/><br/><br/><br/>
<img alt="" src="images/loading.gif" />
<br/>
<?php print $detect_key_str[$lang];?>
</div>

<div>
<table align="center" width="100%">
	<tr>
	<td class="bar_nopanel"><?php print $mvplic_management_str[$lang];?></td>
	</tr>
</table>

<table align="center" width="70%" border="0" cellpadding="6">
  <tr>
    <td colspan="3" class="title"><?php print $mvplic_info_str[$lang];?></td>
  </tr>
<?php 
$mvplic_info = $mvplic->GetUsbKeyInfo();
if($mvplic_info !== FALSE)
{
	print "
  <tr>
    <td width=\"30%\" class=\"field_title\">{$mvplic_basic_info_str[$lang]}</td>  
    <td width=\"35%\" class=\"field_title\">{$authen_version_str[$lang]}</td>  
    <td class=\"field_data1\">{$mvplic_info['basic']['version']}</td>
  </tr>
  <tr>
    <td class=\"field_title\"></td>
    <td class=\"field_title\">{$authen_product_str[$lang]}</td>
    ";
	if($mvplic_info['basic']['version'] == 1)
	{
		print "<td class=\"field_data2\"></td>";
	}
	else
	{
		print "<td class=\"field_data2\">{$mvplic_info['basic']['product']}</td>";	
	}

    print "
  </tr>
  </table>
  <hr style=\"visibility:hidden\">
  ";
	print "
  <table align=\"center\" width=\"70%\" border=\"0\" cellpadding=\"6\">
  <tr>
    <td width=\"30%\" class=\"field_title\">{$mvplic_authen_info_str[$lang]}</td>
    <td width=\"35%\" class=\"field_title\">{$module_name_str[$lang]}</td>
    <td class=\"field_title\">{$authen_count_str[$lang]}</td>
  </tr>
  ";
	
	$td_class = "field_data1";
	foreach($mvplic_info['auth'] as $entry)
	{
		print "<tr>";
		print "<td class=\"field_title\"></td>";
		print "<td class=\"{$td_class}\">{$entry['module']}</td>";
		if($mvplic_info['basic']['version'] == 1)
		{
			print "<td class=\"{$td_class}\"></td>";
		}
		else
		{
			print "<td class=\"{$td_class}\">{$entry['count']}</td>";
		}		
		print "</tr>\n";
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
else
{
	print "
	  <tr>
	    <td class=\"field_data2\" colspan=\"3\">{$none_str[$lang]}</td>
	  </tr>
	  ";
}
?>
</table>

<hr/>

<form name="req_update_form" id="req_update_form" action="usbkey_target.php" method="post">
<table align="center" width="70%" border="0" cellpadding="6">
  <tr>
    <td class="title"><?php print $req_update_key_str[$lang];?></td>
  </tr>
  <tr>
    <td class="field_data2">
	<?php print $req_update_tip_str[$lang];?>
	<br/><p>
	<input name="req_update_submit" type="submit" 
	<?php 
		if($mvplic_info === FALSE)
		{
			print " disabled=\"disabled\" ";
		}/*
		else if($mvplic_info['basic']['version'] == 1)
		{
			print " disabled=\"disabled\" ";
		}*/
	?>	
	 id="req_update_submit" value="<?php print $req_update_key_str[$lang];?>" /></td>
  </tr>
</table>
</form>

<hr/>

<form name="update_key_form" id="update_key_form" enctype="multipart/form-data" 
      action="usbkey_target.php" method="post">
<table align="center" width="70%" border="0" cellpadding="6">
  <tr>
    <td class="title"><?php print $update_key_str[$lang];?></td>
  </tr>
  <tr>
    <td class="field_data1">
	<?php print $update_key_tip_str[$lang];?>
    <br/><p>
    <input type="hidden" name="MAX_FILE_SIZE" value="2097152" /><!-- 2M -->
	<input 
		<?php 
		if($mvplic_info === FALSE)
		{
			print " disabled=\"disabled\" ";
		}/*
		else if($mvplic_info['basic']['version'] == 1)
		{
			print " disabled=\"disabled\" ";
		}*/
		?>
	 type="file" name="key_update_file" size="49%" />
	<br/><p>
	<input type="submit"  name="update_key_submit" 
		<?php 
		if($mvplic_info === FALSE)
		{
			print " disabled=\"disabled\" ";
		}/*
		else if($mvplic_info['basic']['version'] == 1)
		{
			print " disabled=\"disabled\" ";
		}*/
		?>
	   onClick="return updatekey_confirm('<?php print $confirm_update_usbkey_str[$lang];?>');" 
       value="<?php print $update_key_str[$lang];?>" />
	</td>
  </tr>
</table>
</form>
</div>

<script type="text/javascript">
document.getElementById('load_div').innerHTML="";
</script>

<?php 
if($message != "")
{
	print_msg_block($message);
}
?>
</body>
</html>