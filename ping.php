<?php
require("./include/ajax_authenticated.php");
require_once("./include/function.php");
require_once("./include/log.php");

$lang=load_lang();

$ping_test_ok_str=array(
	"Ping²âÊÔÍ¨¹ý¡£",
	"Ping test OK."
);
$ping_test_failed_str=array(
	"Ping²âÊÔÎ´Í¨¹ý£¡",
	"Ping test FAILED!"
);
$cmd_output_str=array(
	"ÃüÁîÊä³ö£º",
	"Command Ouptut: "
);
///////////////////////////////////////////////////////
/*
$host_ip = "";
$pack_count = 3;
$message = "";
$log = new Log();

if( isset($_GET['ip']) )
{
	$host_ip = $_GET['ip'];
	if( ! IsIpOk($host_ip) )
	{
		exit;
	}
}

if( isset($_GET['count']) )
{
	$pack_count = intval($_GET['count']);
	if( $pack_count<=0 || $pack_count>10 )
	{
		$pack_count = 3;
	}
}

// ping
print "
	<div style=\"font-weight:bold;font-size:14px;\">{$cmd_output_str[$lang]}</div>
";

print "<pre style=\"font:13px;color:#444444;\">";
system("export LANG=C; /usr/bin/sudo /bin/ping -c {$pack_count} " . escapeshellarg($host_ip), $retval);
print "</pre>";

if($retval == 0)
{
	$message = $ping_test_ok_str[$lang];
	$log->VstorWebLog(LOG_INFOS, MOD_SYSTEM, "ping {$host_ip}: passed.");
}
else
{
	$message = $ping_test_failed_str[$lang];
	$log->VstorWebLog(LOG_WARN, MOD_SYSTEM, "ping {$host_ip}: unreachable.");
}
print_msg_block($message);
print "<br/>";
*/
?>