<?php
require("./include/authenticated.php");
require_once("./include/function.php");
require_once("./include/log.php");
require_once("./include/usbkey.php");

if( !IsVisExisted())
	exit("no vis server!");


$lang=load_lang();

$mvplic_management_str=array(
	"UsbKey ��Ȩ����",
	"UsbKey Management"
);
$mvplic_info_str=array(
	"UsbKey ��Ϣ",
	"UsbKey Information"
);
$mvplic_authen_info_str=array(
	"��Ȩ��Ϣ��",
	"Authorization Information: "
);
$mvplic_basic_info_str=array(
	"������Ϣ��",
	"Basic Information: "
);
$authen_version_str=array(
	"��Ȩ�����汾",
	"Authorization Version"
);
$authen_product_str=array(
	"��Ȩ�ڲ�Ʒ",
	"Authenticate to Product"
);
$rom_id_str=array(
	"ROM ID",
	"ROM ID"
);
$module_name_str=array(
	"ģ�����",
	"Module Code"
);
$authen_count_str=array(
	"��Ȩ����",
	"Authorization count"
);
$req_update_key_str=array(
	"��������UsbKey",
	"Require to Update UsbKey"
);
$update_key_str=array(
	"Ӧ������UsbKey",
	"Apply to Update UsbKey"
);
$req_update_tip_str=array(
	"���������������ļ���*.req����",
	"Download file to require to update usbkey(*.req)."
);
$update_key_tip_str=array(
	"ѡ�������ļ���*.upk��������ǰUsbKey��",
	"Select proper file(*.upk) to update current usbkey."
);
$mvplic_no_key_str=array(
	"û���ҵ�UsbKey",
	"No UsbKey Found"
);
$mvplic_no_auth_str=array(
	"û���κ���Ȩ",
	"None Authorization"
);
$confirm_update_usbkey_str=array(
	"ȷ������UsbKey��",
	"Confirm to update usbkey?"
);
$none_str=array(
	"- �� -",
	"- none -"
);
$req_updatekey_ok_str=array(
	"��������Key�ɹ���",
	"Require to update key OK."
);
$req_updatekey_failed_str=array(
	"��������Keyʧ�ܣ�",
	"Require to update key failed!"
);
$update_key_ok_str=array(
	"����Key����ɹ���",
	"Updated key OK."
);
$update_key_failed_str=array(
	"����Key����ʧ�ܣ�",
	"Updated key failed��"
);
$detect_key_str=array(
	"���ڼ��Key����",
	"Detecting Key..."
);
?>

<?php 
/*
 * ������ --------------begin
 */
$message = "";
$log = new Log();
$mvplic = new UsbKey();

/*
 * ��������key
 */
while( isset($_POST['req_update_submit']) )
{
	$req_file = $mvplic->GetReqUpdateFile();
	if($req_file === FALSE)
	{
		$message = $req_updatekey_failed_str[$lang];
		$log->VstorWebLog(LOG_ERROR, MOD_VIS, "require to update key failed.");
		$log->VstorWebLog(LOG_ERROR, MOD_VIS, "��������Keyʧ�ܡ�", CN_LANG);
		break;
	}
	//print $req_file;
	DownLoadFile($req_file);
	$message = $req_updatekey_ok_str[$lang];
	$log->VstorWebLog(LOG_INFOS, MOD_VIS, "require to update key ok.");
	$log->VstorWebLog(LOG_INFOS, MOD_VIS, "��������Key�ɹ���", CN_LANG);
	
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
		$retval = $mvplic->UpdateUsbKey($update_file);
		// �����ɹ�
		if($retval === TRUE)
		{
			$message = $update_key_ok_str[$lang];
			$log->VstorWebLog(LOG_INFOS, MOD_VIS, "update key ok.");
			$log->VstorWebLog(LOG_INFOS, MOD_VIS, "����Key�ɹ���", CN_LANG);
			break;
		}		
	}// if
	
	// ����ʧ��
	$message = $update_key_failed_str[$lang];
	$log->VstorWebLog(LOG_ERROR, MOD_VIS, "update key failed.");
	$log->VstorWebLog(LOG_ERROR, MOD_VIS, "����Keyʧ�ܡ�", CN_LANG);
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