<?php
require("./include/ajax_authenticated.php");
require_once("./include/function.php");
require_once("./include/visprofile.php");

$lang=load_lang();

$vis_run_status_str=array(
	"VIS Server ����״̬��",
	"VIS Server Status: "
);
$vis_running_str=array(
	"������",
	"running"
);
$vis_stopped_str=array(
	"��ֹͣ",
	"stopped"
);
$start_str=array(
	"�� ��",
	"Start"
);
$stop_str=array(
	"ͣ ֹ",
	"Stop"
);
$restart_str=array(
	"�� ��",
	"Restart"
);
?>

<?php 

// �����ʼ
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


// ������������ύ���
$output_content = ob_get_contents();
ob_end_clean();
print($output_content);
?>