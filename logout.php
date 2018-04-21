<?php
require_once("./include/log.php");
$log = new Log();

$lang_type = $_SESSION['g_Language'];

setcookie(session_name(),session_id(),time()-3600);
$_SESSION = array();
//$_SESSION['g_bLogin'] = FALSE;
session_destroy();

$log->VstorWebLog(LOG_INFOS, MOD_SYSTEM, $_SESSION['g_username'] . "log out.");
$log->VstorWebLog(LOG_INFOS, MOD_SYSTEM, $_SESSION['g_username'] . "退出。");

// 返回登陆界面，界面语言使用之前的设置
if( $lang_type == CN_LANG )
{
	header("Location: login.php?lang=cn");
}
else
{
	header("Location: login.php?lang=en");
}

?>