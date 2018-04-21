<?php
//require("./include/authenticated.php");
require_once("./include/function.php");
require_once("./include/log.php");
require_once("./include/mvplic.php");

if( !IsMvpExisted())
	exit("no NVR Service!");


$lang=load_lang();

$mvplic_management_str=array(
	"��Ȩ����",
	"License Management"
);

$req_update_key_str=array(
	"������Ȩ�ļ�",
	"Require License Source File"
);
$update_key_str=array(
	"Ӧ����Ȩ�ļ�",
	"Apply License File"
);
$req_update_tip_str=array(
	"����������Ȩ���ļ���source*.info����",
	"Download file(source*.info)."
);
$update_key_tip_str=array(
	"ѡ����Ȩ�ļ���lic*.info����",
	"Select proper file(lic*.info)."
);
$confirm_update_usbkey_str=array(
	"ȷ��Ӧ����Ȩ�ļ�",
	"Confirm use this license"
);

$none_str=array(
	"- �� -",
	"- none -"
);
$ok_str=array(
	"�ɹ���",
	"OK."
);
$failed_str=array(
	"ʧ�ܣ�",
	"Failed!"
);
?>

<?php 
/*
 * ������ --------------begin
 */
$message = "";
$log = new Log();
$mvplic = new MvpLic();

/*
 * ��������key
 */
while( isset($_POST['req_update_submit']) )
{
	$req_file = $mvplic->GetSourceFile();
	if($req_file === FALSE)
	{
		$message = $failed_str[$lang];
		$log->VstorWebLog(LOG_ERROR, MOD_VIS, "require to get lic source failed.");
		$log->VstorWebLog(LOG_ERROR, MOD_VIS, "������Ȩ�ļ�ʧ�ܡ�", CN_LANG);
		break;
	}
	DownLoadFile($req_file);
	$message = $ok_str[$lang];
	$log->VstorWebLog(LOG_INFOS, MOD_VIS, "require to get lic source ok.");
	$log->VstorWebLog(LOG_INFOS, MOD_VIS, "������Ȩ�ļ��ɹ���", CN_LANG);
	
	// ɾ���������ڷ��������ļ�
	$command = "export LANG=C; /usr/bin/sudo /bin/rm -f ". $req_file;
	exec($command);
	
	break;
}

/*
 * Ӧ������key
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
		// Ӧ������
		$retval = $mvplic->UpdateMvpLic($update_file);
		// �����ɹ�
		if($retval === TRUE)
		{
			$message = $ok_str[$lang];
			$log->VstorWebLog(LOG_INFOS, MOD_VIS, "��Ȩ�ɹ���", CN_LANG);
			break;
		}		
	}// if
	
	// ����ʧ��
	$message = $failed_str[$lang];
	$log->VstorWebLog(LOG_ERROR, MOD_VIS, "��Ȩʧ�ܡ�", CN_LANG);
	break;
}

// ɾ���ϴ����ļ�
if($update_file != "")
{
	$command = "export LANG=C; /usr/bin/sudo /bin/rm -f {$update_file}";
	exec($command);
}
/*
 * ������---------------end
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