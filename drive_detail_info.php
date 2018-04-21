<?php
require("./include/authenticated.php");
require_once("./include/function.php");
require_once("./include/drive.php");

$lang=load_lang();

$close_str=array(
	"�ر�",
	"Close"
);
$drive_detail_info_str=array(
	"��ϸ��Ϣ",
	"Information"
);
$smart_data_str=array(
	"S.M.A.R.T. ����",
	"S.M.A.R.T. Data"
);

$model_str=array(
	"�ͺ�",
 	"Model"
);
$capacity_str=array(
	"����",
	"Capacity"
);
$type_str=array(
	"����",
	"Type"
);
$firmware_str=array(
	"�̼��汾",
	"Firmware Version"
);
$serial_str=array(
	"���к�",
	"Serial"
);
$wwn_str=array(
	"WWN",
	"WWN"
);
$temperature_str=array(
	"�¶�",
	"Temperature"
);
$poweronhours_str=array(
	"�ϵ�Сʱ��",
	"Power On Hours"
);
$spindle_speed_str=array(
	"����ת��",
	"Spindle Speed"
);
$queue_capability_str=array(
	"����֧��",
	"Queuing Supported"
);
$queue_enabled_str=array(
	"��������",
	"Queuing Enabled"
);
$link_supported_str=array(
	"��������֧��",
	"Link Supported"
);
$link_enabled_str=array(
	"��������״̬",
	"Linke Enabled"
);

$Drive_Id = "";
$Contrller_Name = "";
$Drive_Detail_Info = array();
$Drive_Smart_Data = array();
$drive_obj = new Drive();
if( isset($_GET['id']) )
{
	$Drive_Id = trim($_GET['id']);
}
if( isset($_GET['c_name']) )
{
	$Contrller_Name = trim($_GET['c_name']);
}
if( IsIdOk($Drive_Id) !== TRUE )
{
	exit("No Access!");
}
?>
<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\" <html xmlns="http://www.w3.org/1999/xhtml">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<link rel="stylesheet" href="css/target.css" type="text/css" />
<script defer type="text/javascript" src="js/pngfix.js"></script>
<script type="text/javascript">
</script>
</head>

<body>
<div style="width:100%;height:600px;overflow:auto;border:1 #CCCCCC solid;">
<?php 
// �رհ�ť
print "<form>";
print "<input type=\"button\" onClick=\"window.close();\" value=\"{$close_str[$lang]}\">";
print "</form>";

$Drive_Detail_Info = $drive_obj->GetDriveDetailInfo($Drive_Id);
if( $Drive_Detail_Info === FALSE )
{
	print_msg_block( $drive_obj->GetLastErrorInfo() );
}
else
{
	// ������ϸ��Ϣ
	print "<table width=\"100%\" border=\"0\" cellpadding=\"6\" align=\"center\">";
	print "  <tr>";
	print "    <td class=\"title_left\" colspan=\"2\">";
	print $Contrller_Name . " " . $Drive_Detail_Info['name'] . " " . $drive_detail_info_str[$lang];
	print "    </td>";
	print "  </tr>";
	print "  <tr>";
	print "    <td class=\"field_title_left\">{$type_str[$lang]}</td>";
	print "    <td class=\"field_data1_left\">{$Drive_Detail_Info['type']}</td>";
	print "  </tr>";
	print "  <tr>";
	print "    <td class=\"field_title_left\">{$serial_str[$lang]}</td>";
	print "    <td class=\"field_data2_left\">{$Drive_Detail_Info['serial']}</td>";
	print "  </tr>";
	print "  <tr>";
	print "    <td class=\"field_title_left\">{$firmware_str[$lang]}</td>";
	print "    <td class=\"field_data1_left\">{$Drive_Detail_Info['firmware']}</td>";
	print "  </tr>";
	print "  <tr>";
	print "    <td class=\"field_title_left\">{$wwn_str[$lang]}</td>";
	print "    <td class=\"field_data2_left\">{$Drive_Detail_Info['wwn']}</td>";
	print "  </tr>";
	print "  <tr>";
	print "    <td class=\"field_title_left\">{$poweronhours_str[$lang]}</td>";
	print "    <td class=\"field_data1_left\">{$Drive_Detail_Info['hours']}</td>";
	print "  </tr>";
	print "  <tr>";
	print "    <td class=\"field_title_left\">{$temperature_str[$lang]}</td>";
	print "    <td class=\"field_data2_left\">{$Drive_Detail_Info['temperature']}</td>";
	print "  </tr>";
	print "  <tr>";
	print "    <td class=\"field_title_left\">{$spindle_speed_str[$lang]}</td>";
	print "    <td class=\"field_data1_left\">{$Drive_Detail_Info['spindle']}</td>";
	print "  </tr>";
	print "  <tr>";
	print "    <td class=\"field_title_left\">{$queue_capability_str[$lang]}</td>";
	print "    <td class=\"field_data2_left\">{$Drive_Detail_Info['queue_c']}</td>";
	print "  </tr>";
	print "  <tr>";
	print "    <td class=\"field_title_left\">{$queue_enabled_str[$lang]}</td>";
	print "    <td class=\"field_data1_left\">{$Drive_Detail_Info['queue_m']}</td>";
	print "  </tr>";
	print "  <tr>";
	print "    <td class=\"field_title_left\">{$link_supported_str[$lang]}</td>";
	print "    <td class=\"field_data2_left\">{$Drive_Detail_Info['link_c']}</td>";
	print "  </tr>";
	print "  <tr>";
	print "    <td class=\"field_title_left\">{$link_enabled_str[$lang]}</td>";
	print "    <td class=\"field_data1_left\">{$Drive_Detail_Info['link_s']}</td>";
	print "  </tr>";
	print "</table>";
}
// SMART ����
$Drive_Smart_Data = $drive_obj->GetDriveSmartData($Drive_Id);
if( $Drive_Detail_Info === FALSE )
{
	print_msg_block( $drive_obj->GetLastErrorInfo() );
}
else
{
	print "<table width=\"100%\" border=\"0\" cellpadding=\"6\" align=\"center\">";
	print "  <tr>";
	print "    <td class=\"title_left\">";
	print $Contrller_Name . " " . $Drive_Detail_Info['name'] . " " .  $smart_data_str[$lang];
	print "    </td>";
	print "  </tr>";
	print "  <tr>";
	print "    <td align=\"left\" vertical-align:left;><font style=\"font-size: 9pt;font-family: monospace;\">";
	foreach( $Drive_Smart_Data as $line )
	{
		print $line . "<br>";
	}
	print "    </font></td>";
	print "  </tr>";
	print "</table>";
}
// �رհ�ť
print "<form>";
print "<input type=\"button\" onClick=\"window.close();\" value=\"{$close_str[$lang]}\">";
print "</form>";
?>
</div>
</body>
</html>

