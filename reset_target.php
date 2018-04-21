<?php
require("./include/authenticated.php");
require_once("./include/function.php");
require_once("./include/log.php");

$lang=load_lang();

$factory_setting_str=array(
	"�ָ���������",
	"Factory Setting"
);

$db_fs_str=array(
	"������������",
	"Reset Congfiguration"
);
$reset_str=array(
	"����",
	"Reset"
);
$db_fs_tip_str=array(
	"�ָ����ù���������õ����ݿ�����Ϊ��ʼ״̬��",
	"Reset database data as the initial state."
);
$confirm_str=array(
	"ȷ���������ݣ�",
	"Confirm to reset?"
);
$reset_ok_str=array(
	"���óɹ���",
	"Reset OK."
);
$reset_failed_str=array(
	"����ʧ�ܣ�",
	"Reset failed��"
);

?>

<?php
$db_fs_file="/opt/vstor/etc/DB_Factory_setting.sql";
$message = "";
$log = new Log();

/*
 * ��������------------
 */
// �������ݿ�����
if( isset($_POST['db_reset_submit']) )
{
	if ( is_file($db_fs_file) )
	{
		$command = "export LANG=C;/usr/bin/sudo /usr/bin/mysql -f < {$db_fs_file}";
		exec($command);
	
		$message=$reset_ok_str[$lang];
		$log->VstorWebLog(LOG_INFOS, MOD_MVP, "Reset database data OK.");
		$log->VstorWebLog(LOG_INFOS, MOD_MVP, "�������ݿ����ݳɹ�", CN_LANG);	
	}
	else 
	{
		$message=$reset_failed_str[$lang];
		$log->VstorWebLog(LOG_ERROR, MOD_MVP, "Reset database data failed.");
		$log->VstorWebLog(LOG_ERROR, MOD_MVP, "�������ݿ�����ʧ��", CN_LANG);
	}
}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<link rel="stylesheet" href="css/target.css" type="text/css" />
<script defer type="text/javascript" src="js/pngfix.js"></script>
<script type="text/javascript">
function reset_confirm(msg)
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
<table align="center" width="100%">
	<tr>
	<td class="bar_nopanel"><?php print $factory_setting_str[$lang];?></td>
	</tr>
</table>

<form name="reset_db_form" id="reset_db_form" action="reset_target.php" method="post">
<table align="center" width="70%" border="0" cellpadding="6">
  <tr>
    <td class="title"><?php print $db_fs_str[$lang];?></td>
  </tr>
  <tr>
    <td class="field_data1">
    	<?php print $db_fs_tip_str[$lang];?>
    	<br/><p>
		<input type="submit" name="db_reset_submit" 
			onClick="return reset_confirm'<?php print $confirm_str[$lang];?>');"
			value="<?php print $reset_str[$lang];?>" />
	</td>
  </tr>
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


