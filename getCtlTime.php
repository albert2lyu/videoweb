<?php
require("./include/authenticated.php");
require_once("./include/function.php");
require_once("./include/LSIMegaRAID.php");

ob_start();
$output = "";
$msg = "";
if(isset($_GET['cid'])) // 获取控制器时间
{
	$cid = intval($_GET['cid']);
	$obj = new CLSIMegaRAIDFunc();
	$time = $obj->GetCtlTime($cid, $msg);
	if($time === FALSE)
	{
	    $output = $msg;
	}
	else
	{
        $output = $time;
	    //$output = "<input type=\"text\" size=\"20\" value=\"{$time}\" id=\"ctl_time\" name=\"ctl_time\" 
	    //       onClick=\"SelectDate(this,\'yyyy-MM-dd hh:mm:ss\',0,0)\" readonly=\"readonly\" />";
	}
}
else
{
	// ?
}

$output_content = $output;
ob_end_clean();
print($output_content);
?>