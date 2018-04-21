<?php
require("./include/ajax_authenticated.php");
require_once("./include/function.php");
require_once("./include/visprofile.php");

$lang=load_lang();

$vis_run_status_str=array(
	"VIS Server 运行状态：",
	"VIS Server Status: "
);
$vis_running_str=array(
	"运行中",
	"running"
);
$vis_stopped_str=array(
	"已停止",
	"stopped"
);
$start_str=array(
	"启 动",
	"Start"
);
$stop_str=array(
	"停 止",
	"Stop"
);
$restart_str=array(
	"重 启",
	"Restart"
);
?>

<?php 

// 输出开始
ob_start();
$bVisRunning = FALSE;
$bVisRunning = IsVisRunning();

print "<table width=\"60%\" border=\"0\" cellpadding=\"6\" align=\"center\">";
print "  <tr>";
print "  	<td colspan=\"3\"  class=\"field_title\">";
print $vis_run_status_str[$lang];
if( $bVisRunning === TRUE )
{
	 print $vis_running_str[$lang];
}
else
{
	print $vis_stopped_str[$lang];
}
print "	</td>";
print " </tr>";
print " <tr>";
print " <td >";
print "	<input type=\"submit\" name=\"start_submit\"";
if( $bVisRunning )
{
	print " disabled=\"disabled\" ";
}
print "	 value=\"$start_str[$lang]\">";
print "	</td>";
print " <td >";
print "	<input type=\"submit\" name=\"stop_submit\"";
if( ! $bVisRunning )
{
	print " disabled=\"disabled\" ";
}
print "	 value=\"$stop_str[$lang]\">";
print "	</td>";
print " <td >";
print "	<input type=\"submit\" name=\"restart_submit\" "; 
if( ! $bVisRunning )
{
	print " disabled=\"disabled\" ";
}
print "	value=\"$restart_str[$lang]\">";
print "	</td>";
print " </tr>";
print "</table>";


// 输出结束，并提交输出
$output_content = ob_get_contents();
ob_end_clean();
print($output_content);
?>