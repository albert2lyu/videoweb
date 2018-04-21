<?php
//require("./include/authenticated.php");
require_once("./include/function.php");
require_once("./include/log.php");
require_once("./include/mvplic.php");

if( !IsMvpExisted())
	exit("no NVR Service!");


$lang=load_lang();

$mvplic_management_str=array(
	"授权管理",
	"License Management"
);

$req_update_key_str=array(
	"请求授权文件",
	"Require License Source File"
);
$update_key_str=array(
	"应用授权文件",
	"Apply License File"
);
$req_update_tip_str=array(
	"下载请求授权的文件（source*.info）。",
	"Download file(source*.info)."
);
$update_key_tip_str=array(
	"选择授权文件（lic*.info）。",
	"Select proper file(lic*.info)."
);
$confirm_update_usbkey_str=array(
	"确认应用授权文件",
	"Confirm use this license"
);

$none_str=array(
	"- 无 -",
	"- none -"
);
$ok_str=array(
	"成功。",
	"OK."
);
$failed_str=array(
	"失败！",
	"Failed!"
);
?>

<?php 
/*
 * 表单处理 --------------begin
 */
$message = "";
$log = new Log();
$mvplic = new MvpLic();

/*
 * 请求升级key
 */
while( isset($_POST['req_update_submit']) )
{
	$req_file = $mvplic->GetSourceFile();
	if($req_file === FALSE)
	{
		$message = $failed_str[$lang];
		$log->VstorWebLog(LOG_ERROR, MOD_VIS, "require to get lic source failed.");
		$log->VstorWebLog(LOG_ERROR, MOD_VIS, "请求授权文件失败。", CN_LANG);
		break;
	}
	DownLoadFile($req_file);
	$message = $ok_str[$lang];
	$log->VstorWebLog(LOG_INFOS, MOD_VIS, "require to get lic source ok.");
	$log->VstorWebLog(LOG_INFOS, MOD_VIS, "请求授权文件成功。", CN_LANG);
	
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
		$retval = $mvplic->UpdateMvpLic($update_file);
		// 升级成功
		if($retval === TRUE)
		{
			$message = $ok_str[$lang];
			$log->VstorWebLog(LOG_INFOS, MOD_VIS, "授权成功。", CN_LANG);
			break;
		}		
	}// if
	
	// 升级失败
	$message = $failed_str[$lang];
	$log->VstorWebLog(LOG_ERROR, MOD_VIS, "授权失败。", CN_LANG);
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
<title><?php print $mvplic_management_str[$lang];?></title>
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

<div>
<table align="center" width="100%">
	<tr>
	<td class="bar_nopanel"><?php print $mvplic_management_str[$lang];?></td>
	</tr>
</table>

<form name="req_update_form" id="req_update_form" action="lic.php" method="post">
<table align="center" width="70%" border="0" cellpadding="6">
  <tr>
    <td class="title"><?php print $req_update_key_str[$lang];?></td>
  </tr>
  <tr>
    <td class="field_data2">
	<?php print $req_update_tip_str[$lang];?>
	<br/><p>
	<input name="req_update_submit" type="submit" 
	 id="req_update_submit" value="<?php print $req_update_key_str[$lang];?>" /></td>
  </tr>
</table>
</form>

<hr/>

<form name="update_key_form" id="update_key_form" enctype="multipart/form-data" 
      action="lic.php" method="post">
<table align="center" width="70%" border="0" cellpadding="6">
  <tr>
    <td class="title"><?php print $update_key_str[$lang];?></td>
  </tr>
  <tr>
    <td class="field_data1">
	<?php print $update_key_tip_str[$lang];?>
    <br/><p>
    <input type="hidden" name="MAX_FILE_SIZE" value="2097152" /><!-- 2M -->
	<input type="file" name="key_update_file" size="49%" />
	<br/><p>
	<input type="submit"  name="update_key_submit" 
		onClick="return updatekey_confirm('<?php print $confirm_update_usbkey_str[$lang];?>');"
		value="<?php print $update_key_str[$lang];?>" />
	</td>
  </tr>
</table>
</form>
</div>

<?php 
if($message != "")
{
	print_msg_block($message);
}
?>
</body>
</html>