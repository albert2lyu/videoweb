<?php
require("./include/authenticated.php");
require_once("./include/function.php");
require_once("./include/mvpprofile.php");
require_once("./include/log.php");

if( !IsMVPExisted())
	exit("no nvr service!");

$lang=load_lang();

$mvp_manager_str=array(
	"NVR Service ����",
	"NVR Service Management"
);
?>

<?php 

$log = new Log();
/*
 * ������ BEGIN
 */
// ����MVP
if( isset($_POST['start_submit']) )
{
	$retval = StartMVPServer();
	if($retval === TRUE)
	{
		$log->VstorWebLog(LOG_INFOS, MOD_MVP, "start  NVR service ok.");
		$log->VstorWebLog(LOG_INFOS, MOD_MVP, "���� ����ɹ���", CN_LANG);
	}
	else
	{
		$log->VstorWebLog(LOG_INFOS, MOD_MVP, "start NVR service failed.");
		$log->VstorWebLog(LOG_INFOS, MOD_MVP, "���� ����ʧ�ܡ�", CN_LANG);
	}
}
// ֹͣMVP
if( isset($_POST['stop_submit']) )
{
	$retval = StopMVPServer();
	if($retval === TRUE)
	{
		$log->VstorWebLog(LOG_WARN, MOD_MVP, "stop NVR service ok.");
		$log->VstorWebLog(LOG_WARN, MOD_MVP, "ֹͣ����ɹ���", CN_LANG);
	}
	else
	{
		$log->VstorWebLog(LOG_WARN, MOD_MVP, "stop NVR service failed.");
		$log->VstorWebLog(LOG_WARN, MOD_MVP, "ֹͣ����ʧ�ܡ�", CN_LANG);
	}
}
// ����MVP
if( isset($_POST['restart_submit']) )
{
	$retval = RestartMVPServer();
	if($retval === TRUE)
	{
		$log->VstorWebLog(LOG_WARN, MOD_MVP, "restart NVR service ok.");
		$log->VstorWebLog(LOG_WARN, MOD_MVP, "��������ɹ���", CN_LANG);
	}
	else
	{
		$log->VstorWebLog(LOG_WARN, MOD_MVP, "restart NVR service failed.");
		$log->VstorWebLog(LOG_WARN, MOD_MVP, "��������ʧ�ܡ�", CN_LANG);
	}
}
/*
 * ������ END
 */
?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<link rel="stylesheet" href="css/target.css" type="text/css" />
<script type="text/javascript" language="javascript" src="js/ajax_function.js"></script>
<script defer type="text/javascript" src="js/pngfix.js"></script>
<script defer type="text/javascript" src="js/function.js"></script>
<script type="text/javascript">
</script>
</head>

<body>
<form id="mvpmanager_form" name="mvpmanager_form" action="mvpmanage_target.php" method="post">
<table align="center" width="100%">
	<tr>
	<td class="bar_nopanel"><?php print $mvp_manager_str[$lang];?> </td>
	</tr>
	<tr>
	<td height="20" > </td>
	</tr>
</table>

<script type="text/javascript">
	var xmlhttp = null;
	loadDoc(xmlhttp, "mvpstate.php", mvp_state_ajax);
	window.setInterval("loadDoc(xmlhttp, 'mvpstate.php', mvp_state_ajax)", 5000);
</script>

<div id="mvpmanager">
</div>
</form>

</body>
</html>
