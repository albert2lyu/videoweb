<?php 
require("./include/authenticated.php");
require_once("./include/function.php");
require_once("./include/controller.php");
require_once("./include/unit.php");
require_once("./include/drive.php");
require_once("./include/log.php");

$lang=load_lang();

$index_str=array(
	"���",
	"Index"
);
$unit_information_str=array(
	"RAID����Ϣ",
	"Unit Information"
);
$select_controller_str=array(
	"ѡ�������",
	"Select Controller"
);
$number_of_unit_str=array(
	"������RAID����",
	"Number of Units"
);
$no_controller_str=array(
	"�޿��ÿ�����",
	"No Controller"
);
$rescan_controller_str=array(
	"����ɨ�������",
	"Rescan Controller"
);
$unit_list_info_str=array(
	"RAID���б���Ϣ",
	"Unit List Information"
);
$unit_str=array(
	"RAID��",
	"Unit"
);
$name_str=array(
	"����",
	"Name"
);
$capacity_str=array(
	"����",
	"Capacity"
);
$raidtype_str=array(
	"����",
	"Type"
);
$status_str=array(
	"״̬",
	"Status"
);
$identify_unit_str=array(
	"��λ",
	"Identify"
);
$is_identify_unit_str=array(
	"�Ƿ�λ��RAID��?",
	"Identify the Unit?"
);
$identify_unit_tip_str=array(
	"��λ��RAID��",
	"Identify the Unit"
);
$show_unit_detail_tip_str=array(
	"�򿪴�RAID�����ϸ��Ϣ",
	"Open Unit Detail Information"
);
$unit_detail_info_str=array(
	"RAID����ϸ��Ϣ",
	"Unit Detail Information"
);
$stripe_size_str=array(
	"Stripe��С",
	"Stripe Size"
);
$number_of_drives_str=array(
	"������Ŀ",
	"Number Of Drives"
);
$serial_str=array(
	"���к�",
	"Serial Number"
);
$write_cache_str=array(
	"д����",
	"Write Cache"
);
$auto_verify_str=array(
	"�Զ�У��",
	"Auto Verify"
);
$ecc_str=array(
	"��дECC",
	"Overwrite ECC"
);
$queuing_str=array(
	"����",
	"Queuing"
);
$storsave_str=array(
	"�洢����",
	"StorSave"
);
$rrr_str=array(
	"����RAID�޸�",
	"Rapid RAID Recovery"
);
$enabled_str=array(
	"������",
	"Enabled"
);
$disabled_str=array(
	"δ����",
	"Disabled"
);
$unsupported_str=array(
	"��֧��",
	"Unsupported"
);
$protection_str=array(
	"��ȫģʽ",
	"Protection"
);
$balance_str=array(
	"ƽ��ģʽ",
	"Balance"
);
$performance_str=array(
	"����ģʽ",
	"Performance"
);
$rebuild_str=array(
	"�ؽ�",
	"Rebuild"
);
$all_str=array(
	"ȫ��",
	"All"
);
$back_str=array(
	"����",
	"Back"
);
$drive_name_str=array(
	"����",
	"Name"
);
$drive_list_of_unit_str=array(
	"�����Ĵ����б���Ϣ",
	"Drives Information Of Unit"
);
$drive_model_str=array(
	"�ͺ�",
 	"Model"
);
$drive_capacity_str=array(
	"����",
	"Capacity"
);
$drive_type_str=array(
	"����",
	"Type"
);
$drive_slot_str=array(
	"���",
	"Slot"
);
$drive_temperature_str=array(
	"�¶�",
	"Temperature"
);
$drive_status_str=array(
	"״̬",
	"Status"
);
$no_drives_str=array(
	"�޴���",
	"None Drives"
);
$not_present_str=array(
	"������",
	"Not Present"
);
?>

<?php 
$objCtl = new Controller();
$drive_obj = new Drive();
$unit_obj = new Unit();
$objLog = new Log();
$name_controller_selected = ""; // ѡ��Ŀ���������
$id_controller_selected = ""; // ѡ��Ŀ�����ID
$controller_selected = array(); // ѡ��Ŀ�����
$listCtrl = array();
$b_have_controller = TRUE;
$unit_id_selected = "";// ѡ���unit ID
$message = "";

$listCtrl = $objCtl->GetControllerList();
if($listCtrl === FALSE)
{
	$b_have_controller = FALSE;
}
// ѡ�������
if( isset($_POST['select_controller']) )
{
	$name_controller_selected = $_POST['select_controller'];
}

// ��ɨ�������
if( isset($_POST['rescan_controller_submit']) )
{
	// ��¼֮ǰѡ��Ŀ���������
	$name_controller_selected = $_POST['name_controller_selected_h'];
	$retval = $objCtl->RescanController($_POST['id_controller_selected_h']);
	if( $retval === FALSE)
	{
		$message = $objCtl->GetLastErrorInfo();
	}
	// ���»�ȡ�������б�
	$listCtrl = $objCtl->GetControllerList();
	if($listCtrl === FALSE)
	{
		$b_have_controller = FALSE;
	}
	else
	{
		$b_have_controller = TRUE;
	}
}
// ��ȡѡ��Ŀ���������
if($b_have_controller !== FALSE)
{
	$controller_selected = FALSE;
	foreach($listCtrl as $entry)
	{
		if($entry['name'] == $name_controller_selected || $entry['id'] == $id_controller_selected)
		{
			$controller_selected = $entry;
			break;
		}
	}
}
// ѡ���unit
if( isset($_GET['unit_sel_id']) )
{
	if( IsIdOk( $_GET['unit_sel_id'] ) )
	{
		$unit_id_selected = $_GET['unit_sel_id'];
	}
}
if ( isset($_GET['unit_sel_number']) && isset($_GET['controller_sel_id']) )
{
	$id_controller_selected = $_GET['controller_sel_id'];
	$unit_id_tmp = $unit_obj->GetUnitIdFormUnitNumber($_GET['controller_sel_id'], $_GET['unit_sel_number']);
	if($unit_id_tmp !== FALSE)
	{
		$unit_id_selected = $unit_id_tmp;
	}
}
// ��λUNIT
if( isset($_POST['identify_unit_id']) )
{
	$unit_obj->IdentifyUnit( $_POST['identify_unit_id'] );
}
?>
<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\" <html xmlns="http://www.w3.org/1999/xhtml">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<link rel="stylesheet" href="css/target.css" type="text/css" />
<script defer type="text/javascript" src="js/pngfix.js"></script>
<script type="text/javascript">
function SelectController()
{
	document.sel_controller_form.submit();
	return true;
}
function RescanController(c_name, c_id)
{
	document.sel_controller_form.name_controller_selected_h.value = c_name;
	document.sel_controller_form.id_controller_selected_h.value = c_id;
	return true;	
}
function IdentifyUnit(id, msg)
{
	document.unit_list_form.identify_unit_id.value = id;
	if( confirm(msg) )
	{
		document.unit_list_form.submit();
		return true;
	}
	return false;
}
</script>
</head>

<body>

<table align="center" width="100%">
  <tr>
    <td class="bar_nopanel"><?php print $unit_information_str[$lang]; ?></td>
  </tr>
</table>
<?php 
if($b_have_controller === TRUE)
{
	// ������ѡ��
	print "<form id=\"sel_controller_form\" name=\"sel_controller_form\" action=\"unit_target.php\" method=\"post\">";
	print "  <input type=\"hidden\" name=\"name_controller_selected_h\" id=\"name_controller_selected_h\" value=\"\">";
	print "  <input type=\"hidden\" name=\"id_controller_selected_h\" id=\"id_controller_selected_h\" value=\"\">";
	print "  <table width=\"100%\" border=\"0\" cellpadding=\"6\" align=\"center\">";
	print "  	<tr>";
	print "  	  <td class=\"field_title\">";
	print $select_controller_str[$lang] . ":";
	print "  	  </td>";
	print "  	  <td class=\"field_data1\">";
	print "  	    <select name=\"select_controller\" onChange=\"SelectController();\">";
	$controller_selected = $listCtrl[0];
	foreach( $listCtrl as $entry )
	{
		if($entry['name'] == $name_controller_selected || $id_controller_selected == $entry['id'])
		{
			print "<option value=\"{$entry['name']}\" selected>{$entry['name']}";
			$controller_selected = $entry;
		}
		else
		{
			print "<option value=\"{$entry['name']}\">{$entry['name']}";
		}
	}
	print "  	    </select>";
	print "  	  </td>";

	print "  	  <td class=\"field_title\">";
	print $number_of_unit_str[$lang] . ":";
	print "  	  </td>";
	print "  	  <td class=\"field_data1\">";
	print $controller_selected['units'];
	print "  	  </td>";

	print "  	  <td class=\"field_data1\">";
	print "			<input type=\"submit\" name=\"rescan_controller_submit\" onclick=\"RescanController('{$controller_selected['name']}','{$controller_selected['id']}');\" value=\"{$rescan_controller_str[$lang]}\" />";
	print "  	  </td>";
	print "  	</tr>";
	print "  </table>";
	print "</form>";
	
	// �Ѵ�����unit������Ϣ�б���ʾ
	if( $unit_id_selected == "" )
	{
		$b_error = FALSE;
		$UnitBasicInfo = array();
		$UnitBasicInfoList = array();
		$UnitIdList = $unit_obj->GetUnitIdList($controller_selected['id']);
		if($UnitIdList !== FALSE)
		{
			foreach($UnitIdList as $UnitId)
			{
				$UnitBasicInfo = $unit_obj->GetUnitBasicInfo($UnitId);
				if($UnitBasicInfo === FALSE)
				{
					$b_error = TRUE;
					break;
				}
				$UnitBasicInfoList[] = $UnitBasicInfo;
			}
			if($b_error !== TRUE)
			{
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// -----�Ѵ�����unit������Ϣ�б���ʾ BEGIN
print "<form id=\"unit_list_form\" name=\"unit_list_form\" action=\"unit_target.php\" method=\"post\">";
print "<input type=\"hidden\" name=\"identify_unit_id\" id=\"identify_unit_id\" value=\"\">";	
print "  <table width=\"100%\" border=\"0\" cellpadding=\"6\" align=\"center\">";
print "  <tr>";
print "    <td class=\"title\" colspan=\"7\">" . $unit_list_info_str[$lang] . "</td>";
print "  </tr>";

print "  <tr>";
print "    <td class=\"field_title\">" . $index_str[$lang] ."</td>";
print "    <td class=\"field_title\">" . $unit_str[$lang] ."</td>";
print "    <td class=\"field_title\">" . $name_str[$lang] ."</td>";
print "    <td class=\"field_title\">" . $capacity_str[$lang] ."</td>";
print "    <td class=\"field_title\">" . $raidtype_str[$lang] ."</td>";
print "    <td class=\"field_title\">" . $status_str[$lang] ."</td>";
print "    <td class=\"field_title\">" . $identify_unit_str[$lang] ."</td>";
print "  </tr>";

$td_class = "field_data1";
$drive_index = 1;
foreach( $UnitBasicInfoList as $UnitBasicInfo )
{
	print "  <tr>";
	print "    <td class=\"{$td_class}\">{$drive_index}</td>";
	
	print "    <td class=\"{$td_class}\">";
	print "<a title=\"{$show_unit_detail_tip_str[$lang]}\" class=\"general_link\" href=\"unit_target.php?unit_sel_id={$UnitBasicInfo['id']}\">";
	print $UnitBasicInfo['number'];
	print "    </a></td>";
	
	print "    <td class=\"{$td_class}\">{$UnitBasicInfo['name']}</td>";
	print "    <td class=\"{$td_class}\">{$UnitBasicInfo['capacity']}</td>";
	print "    <td class=\"{$td_class}\">{$UnitBasicInfo['type']}</td>";
	print "    <td class=\"{$td_class}\">";
	if( $UnitBasicInfo['status'] == "OK")
	{
		print "<font class=\"statusOK\">";
		print $UnitBasicInfo['status'];
		print "</font>";
	}
	else
	{
		print $UnitBasicInfo['status'];
	}
	print "    </td>";
	
	print "    <td class=\"{$td_class}\">";
	print "<img style=\"cursor:crosshair\" title=\"{$identify_unit_tip_str[$lang]}\" src=\"images/unit_identify_icon.gif\" onclick=\"IdentifyUnit('{$UnitBasicInfo['id']}', '{$is_identify_unit_str[$lang]}');\"></img>";
	print "    </td>";
	print "    </tr>";
	$td_class = ($td_class == "field_data1") ? "field_data2" : "field_data1";
	$drive_index++;
}

print "  </table>";
print "</form>";
			}// if($b_error == true)
		}// if($UnitIdList !== FALSE)
		
// -----�Ѵ�����unit������Ϣ�б���ʾ END
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	}// if( $unit_id_selected == "" )

	// ��ʾѡ���UNIT����ϸ��Ϣ
	else
	{
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// -----��ʾѡ���UNIT����ϸ��Ϣ BEGIN
// ���ذ�ť
print "<table><tr><td>";
print "<input type=\"button\" onClick=\"window.history.back();\" value=\"{$back_str[$lang]}\">";
print "</td></tr></table>";
$UnitDetailInfo = $unit_obj->GetUnitDetailInfo($unit_id_selected);
if($UnitDetailInfo === FALSE)
{
	$message = $unit_obj->GetLastErrorInfo();
}
else
{
	// ��ϸ��Ϣ
	print "<table width=\"100%\" border=\"0\" cellpadding=\"6\" align=\"center\">";
	print "  <tr>";
	print "    <td class=\"title_left\" colspan=\"2\">";
	print $unit_detail_info_str[$lang] . " - " . $unit_str[$lang] . " " . $UnitDetailInfo['number'];
	print "    </td>";
	print "  </tr>";
	print "  <tr>";
	print "    <td class=\"field_title_left\">{$status_str[$lang]}</td>";
	print "    <td class=\"field_data1_left\">";
	if( $UnitDetailInfo['status'] == "OK")
	{
		print "<font class=\"statusOK\">";
		print $UnitDetailInfo['status'];
		print "</font>";
	}
	else
	{
		print $UnitDetailInfo['status'];
	}
	print "    </td>";
	print "  </tr>";
	print "  <tr>";
	print "    <td class=\"field_title_left\">{$name_str[$lang]}</td>";
	print "    <td class=\"field_data2_left\">{$UnitDetailInfo['name']}</td>";
	print "  </tr>";
	print "  <tr>";
	print "    <td class=\"field_title_left\">{$serial_str[$lang]}</td>";
	print "    <td class=\"field_data1_left\">{$UnitDetailInfo['serial']}</td>";
	print "  </tr>";
	print "  <tr>";
	print "    <td class=\"field_title_left\">{$capacity_str[$lang]}</td>";
	print "    <td class=\"field_data2_left\">{$UnitDetailInfo['capacity']}</td>";
	print "  </tr>";
	print "  <tr>";
	print "    <td class=\"field_title_left\">{$raidtype_str[$lang]}</td>";
	print "    <td class=\"field_data1_left\">{$UnitDetailInfo['type']}</td>";
	print "  </tr>";
	print "  <tr>";
	print "    <td class=\"field_title_left\">{$stripe_size_str[$lang]}</td>";
	print "    <td class=\"field_data2_left\">{$UnitDetailInfo['stripe']}</td>";
	print "  </tr>";
	print "  <tr>";
	print "    <td class=\"field_title_left\">{$number_of_drives_str[$lang]}</td>";
	print "    <td class=\"field_data1_left\">{$UnitDetailInfo['drive_number']}</td>";
	print "  </tr>";
	print "  <tr>";
	print "    <td class=\"field_title_left\">{$write_cache_str[$lang]}</td>";
	print "    <td class=\"field_data2_left\">";
	if( $UnitDetailInfo['write_cache'] == 0 )
	{
		print $enabled_str[$lang];
	}
	else if( $UnitDetailInfo['write_cache'] == 1 )
	{
		print $disabled_str[$lang];
	}
	else
	{
		print $unsupported_str[$lang];
	}
	print "    </td>";
	print "  </tr>";
	print "  <tr>";
	print "    <td class=\"field_title_left\">{$auto_verify_str[$lang]}</td>";
	print "    <td class=\"field_data1_left\">";
	if( $UnitDetailInfo['auto_verify'] == 0 )
	{
		print $enabled_str[$lang];
	}
	else if( $UnitDetailInfo['auto_verify'] == 1 )
	{
		print $disabled_str[$lang];
	}
	else
	{
		print $unsupported_str[$lang];
	}
	print "    </td>";
	print "  </tr>";
	print "  <tr>";
	print "    <td class=\"field_title_left\">{$queuing_str[$lang]}</td>";
	print "    <td class=\"field_data1_left\">";
	if( $UnitDetailInfo['queue'] == 0 )
	{
		print $enabled_str[$lang];
	}
	else if( $UnitDetailInfo['queue'] == 1 )
	{
		print $disabled_str[$lang];
	}
	else
	{
		print $unsupported_str[$lang];
	}
	print "    </td>";
	print "  </tr>";
	print "  <tr>";
	print "    <td class=\"field_title_left\">{$ecc_str[$lang]}</td>";
	print "    <td class=\"field_data2_left\">";
	if( $UnitDetailInfo['ecc'] == 0 )
	{
		print $enabled_str[$lang];
	}
	else if( $UnitDetailInfo['ecc'] == 1 )
	{
		print $disabled_str[$lang];
	}
	else
	{
		print $unsupported_str[$lang];
	}
	print "    </td>";
	print "  </tr>";
	print "  <tr>";
	print "    <td class=\"field_title_left\">{$storsave_str[$lang]}</td>";
	print "    <td class=\"field_data2_left\">";
	if( $UnitDetailInfo['storsave'] == 0 )
	{
		print $protection_str[$lang];
	}
	else if( $UnitDetailInfo['storsave'] == 1 )
	{
		print $balance_str[$lang];
	}
	else if( $UnitDetailInfo['storsave'] == 2 )
	{
		print $performance_str[$lang];
	}
	else
	{
		print $unsupported_str[$lang];
	}
	print "    </td>";
	print "  </tr>";
	print "  <tr>";
	print "    <td class=\"field_title_left\">{$rrr_str[$lang]}</td>";
	print "    <td class=\"field_data1_left\">";
	if( $UnitDetailInfo['rrr'] == 0 )
	{
		print $rebuild_str[$lang];
	}
	else if( $UnitDetailInfo['rrr'] == 1 )
	{
		print $all_str[$lang];
	}
	else if( $UnitDetailInfo['rrr'] == 2 )
	{
		print $disabled_str[$lang];
	}
	else
	{
		print $unsupported_str[$lang];
	}
	print "    </td>";
	print "  </tr>";
	print "</table>";
	
	// �����б�
	print "<table width=\"100%\" border=\"0\" cellpadding=\"6\" align=\"center\">";
	print "  <tr>";
	print "    <td class=\"title_left\" colspan=\"8\">";
	print $drive_list_of_unit_str[$lang] . " - " . $unit_str[$lang] . " " . $UnitDetailInfo['number'];
	print "    </td>";
	print "  </tr>";
	print "  <tr>";
	print "    <td class=\"field_title\">" . $index_str[$lang] ."</td>";
	print "    <td class=\"field_title\">" . $drive_name_str[$lang] ."</td>";
	print "    <td class=\"field_title\">" . $drive_model_str[$lang] ."</td>";
	print "    <td class=\"field_title\">" . $drive_capacity_str[$lang] ."</td>";
	print "    <td class=\"field_title\">" . $drive_type_str[$lang] ."</td>";
	print "    <td class=\"field_title\">" . $drive_slot_str[$lang] ."</td>";
	print "    <td class=\"field_title\">" . $drive_temperature_str[$lang] ."</td>";
	print "    <td class=\"field_title\">" . $drive_status_str[$lang] ."</td>";
	print "  </tr>";
	
	$Drive_Id_List = $UnitDetailInfo['drive_id_list'];
	if( count($Drive_Id_List) === 0 )
	{
		$message = $no_drives_str[$lang];
	}
	else
	{
		$drive_index = 1;
		$td_class = "field_data1";
		foreach( $Drive_Id_List as $Drive_Id )
		{
			$DriveBasicInfo = $drive_obj->GetDriveBasicInfo($Drive_Id);
			if($DriveBasicInfo === FALSE)
			{
				print "  <tr>";
				print "    <td class=\"{$td_class}\">" . $drive_index . "</td>";
				print "    <td class=\"{$td_class}\">--</td>";
				print "    <td class=\"{$td_class}\">--</td>";
				print "    <td class=\"{$td_class}\">--</td>";
				print "    <td class=\"{$td_class}\">--</td>";
				print "    <td class=\"{$td_class}\">--</td>";
				print "    <td class=\"{$td_class}\">--</td>";
				print "    <td class=\"{$td_class}\">{$not_present_str[$lang]}</td>";
				print "  </tr>";
			}
			else
			{
				print "  <tr>";
				print "    <td class=\"{$td_class}\">" . $drive_index . "</td>";
				print "    <td class=\"{$td_class}\">";
				print "<a class=\"general_link\" href=\"drive_target.php?hl_d_id={$DriveBasicInfo['id']}&sl_c_id={$controller_selected['id']}\">";
				print  $DriveBasicInfo['name'];
				print "</a>";
				print "</td>";
				print "    <td class=\"{$td_class}\">" . $DriveBasicInfo['model'] . "</td>";
				print "    <td class=\"{$td_class}\">" . $DriveBasicInfo['capacity'] . "</td>";
				print "    <td class=\"{$td_class}\">" . $DriveBasicInfo['type'] . "</td>";
				print "    <td class=\"{$td_class}\">" . $DriveBasicInfo['slot'] . "</td>";
				print "    <td class=\"{$td_class}\">" . $DriveBasicInfo['temperature'] . "</td>";
				print "    <td class=\"{$td_class}\">";
				if( $DriveBasicInfo['status'] == "OK")
				{
					print "<font class=\"statusOK\">";
					print $DriveBasicInfo['status'];
					print "</font>";
				}
				else
				{
					print "<font class=\"statusOther\">";
					print $DriveBasicInfo['status'];
					print "</font>";
				}
				print "</td>";
				print "  </tr>";
			}
			
			$td_class = ($td_class == "field_data1") ? "field_data2" : "field_data1";
			$drive_index++;
		}
	}
	
	print "</table>";
}
		
// -----��ʾѡ���UNIT����ϸ��Ϣ END
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	}

}
else
{
	print_msg_block( $no_controller_str[$lang] );
}

if( $message !== "" )
{
	print_msg_block( $message );
}
?>


</body>
</html>
