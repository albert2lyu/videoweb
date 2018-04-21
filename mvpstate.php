<?php
require("./include/ajax_authenticated.php");
require_once("./include/function.php");
require_once("./include/mvpprofile.php");

$lang=load_lang();

$mvp_run_status_str=array(
	"NVR Service ����״̬��",
	"NVR Service Status: "
);
$mvp_running_str=array(
	"������",
	"running"
);
$mvp_stopped_str=array(
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
$bMVPRunning = FALSE;
$bMVPRunning = IsMVPRunning();

print "<table width=\"60%\" border=\"0\" cellpadding=\"6\" align=\"center\">";
print "  <tr>";
print "  	<td colspan=\"3\"  class=\"field_title\">";
print $mvp_run_status_str[$lang];
if( $bMVPRunning === TRUE )
{
	 print $mvp_running_str[$lang];
}
else
{
	print $mvp_stopped_str[$lang];
}
print "	</td>";
print " </tr>";
print " <tr>";
print " <td >";
print "	<input type=\"submit\" name=\"start_submit\"";
if( $bMVPRunning )
{
	print " disabled=\"disabled\" ";
}
print "	 value=\"$start_str[$lang]\">";
print "	</td>";
print " <td >";
print "	<input type=\"submit\" name=\"stop_submit\"";
if( ! $bMVPRunning )
{
	print " disabled=\"disabled\" ";
}
print "	 value=\"$stop_str[$lang]\">";
print "	</td>";
print " <td >";
print "	<input type=\"submit\" name=\"restart_submit\" "; 
if( ! $bMVPRunning )
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
