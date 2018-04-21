<?php
require("./include/authenticated.php");
require_once("./include/function.php");
require_once("./include/visprofile.php");
require_once("./include/log.php");

if( !IsVisExisted())
	exit("no vis server!");

$lang=load_lang();

$vis_manager_str=array(
	"VIS Server ����",
	"VIS Server Management"
);
?>

<?php 

$log = new Log();
/*
 * ������ BEGIN
 */
// ����VIS
if( isset($_POST['start_submit']) )
{
	$retval = StartVisServer();
	if($retval === TRUE)
	{
		$log->VstorWebLog(LOG_INFOS, MOD_VIS, "start VIS Server ok.");
		$log->VstorWebLog(LOG_INFOS, MOD_VIS, "����VIS Server�ɹ���", CN_LANG);
	}
	else
	{
		$log->VstorWebLog(LOG_INFOS, MOD_VIS, "start VIS Server failed.");
		$log->VstorWebLog(LOG_INFOS, MOD_VIS, "����VIS Serverʧ�ܡ�", CN_LANG);
	}
}
// ֹͣVIS
if( isset($_POST['stop_submit']) )
{
	$retval = StopVisServer();
	if($retval === TRUE)
	{
		$log->VstorWebLog(LOG_WARN, MOD_VIS, "stop VIS Server ok.");
		$log->VstorWebLog(LOG_WARN, MOD_VIS, "ֹͣVIS Server�ɹ���", CN_LANG);
	}
	else
	{
		$log->VstorWebLog(LOG_WARN, MOD_VIS, "stop VIS Server failed.");
		$log->VstorWebLog(LOG_WARN, MOD_VIS, "ֹͣVIS Serverʧ�ܡ�", CN_LANG);
	}
}
// ����VIS
if( isset($_POST['restart_submit']) )
{
	$retval = RestartVisServer();
	if($retval === TRUE)
	{
		$log->VstorWebLog(LOG_WARN, MOD_VIS, "restart VIS Server ok.");
		$log->VstorWebLog(LOG_WARN, MOD_VIS, "����VIS Server�ɹ���", CN_LANG);
	}
	else
	{
		$log->VstorWebLog(LOG_WARN, MOD_VIS, "restart VIS Server failed.");
		$log->VstorWebLog(LOG_WARN, MOD_VIS, "����VIS Serverʧ�ܡ�", CN_LANG);
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
<form id="vismanager_form" name="vismanager_form" action="vismanage_target.php" method="post">
<table align="center" width="100%">
	<tr>
	<td class="bar_nopanel"><?php print $vis_manager_str[$lang];?> </td>
	</tr>
</table>

<script type="text/javascript">
	var xmlhttp = null;
	loadDoc(xmlhttp, 'visstate.php', vis_state_ajax);
	window.setInterval("loadDoc(xmlhttp, 'visstate.php', vis_state_ajax)", 5000);
</script>

<div id="vismanager">
</div>
</form>

</body>
</html>