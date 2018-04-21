<?php 
require("./include/authenticated.php");
require_once("./include/function.php");
require_once("./include/controller.php");
require_once("./include/unit.php");
require_once("./include/drive.php");
require_once("./include/log.php");

$lang=load_lang();

$controller_setting_str=array(
	"����������",
	"Controller Setting"
);
$select_controller_str=array(
	"ѡ�������",
	"Select Controller"
);
$no_controller_str=array(
	"�޿��ÿ�����",
	"No Controller"
);
$rescan_controller_str=array(
	"����ɨ�������",
	"Rescan Controller"
);
$bg_test_rate_str=array(
	"��̨������������",
	"Background Task Rate"
);
$rebuild_rate_str=array(
	"�ؽ�����",
	"Rebuild Rate"
);
$verify_rate_str=array(
	"У������",
	"Verify Rate"
);
$faster_rebuild_str=array(
	"�����ؽ�",
	"Faster Rebuild"
);
$faster_io_str=array(
	"���ٶ�д",
	"Faster I/O"
);
$faster_verify_str=array(
	"����У��",
	"Faster Verify "
);
$unit_policies_setting_str=array(
	"RAID���������",
	"Unit Policies Setting"
);
$write_cache_str=array(
	"д����",
	"Write Cache"
);
$auto_verify_str=array(
	"�Զ�У��",
	"Auto Verify"
);
$queuing_str=array(
	"����",
	"Queuing"
);
$overwrite_ecc_str=array(
	"��дECC",
	"Overwrite ECC"
);
$storsave_str=array(
	"�洢����",
	"StorSave"
);
$rapid_raid_recovery_str=array(
	"����RAID�޸�",
	"Rapid RAID Recovery"
);
$unit_name_setting_str=array(
	"RAID����������",
	"Unit Name Setting"
);
$save_names_str=array(
	"��������",
	"Save Names"
);
$reset_names_str=array(
	"��������",
	"Reset Names"
);
$no_available_units_str=array(
	"�޿���RAID��",
	"No Available Units"
);
$unit_str=array(
	"RAID��",
	"Unit"
);
$rrr_warning_str=array(
	"���ÿ���RAID�޸�Ϊ�����ã���Ч�󣬽����������޸ġ�",
	"Setting Rapid RAID Recovery to Disable is permanent for this unit and CANNOT be changed at a later time."
);
$invalid_unit_name_str=array(
	"��ЧRAID�����ƣ�\\n��Ч�ַ�:A-Za-z0-9_-������<=15",
	"Invalid Unit Name��\\nValid Character:A-Za-z0-9_-, Length <= 15"
);
?>

<?php 
$objCtl = new Controller();
$drive_obj = new Drive();
$unit_obj = new Unit();
$log = new Log();
$name_controller_selected = ""; // ѡ��Ŀ���������
$id_controller_selected = ""; // ѡ��Ŀ�����ID
$controller_selected = array(); // ѡ��Ŀ�����
$listCtrl = array();
$b_have_controller = TRUE;
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
else if( isset($_GET['sl_c_id']) )
{
	$id_controller_selected = $_GET['sl_c_id'];
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
// ��̨������������
if( isset($_POST['rebuild_rate_radio']) )
{
	if($controller_selected !== FALSE)
	{
		$retval = $objCtl->SetBgRebuildRate($controller_selected['id'], $_POST['rebuild_rate_radio']);
		if($retval !== TRUE)
		{
			print_msg_block($objCtl->GetLastErrorInfo());
		}
	}
}
if( isset($_POST['verify_rate_radio']) )
{
	if($controller_selected !== FALSE)
	{
		$retval = $objCtl->SetBgVerifyRate($controller_selected['id'], $_POST['verify_rate_radio']);
		if($retval !== TRUE)
		{
			print_msg_block($objCtl->GetLastErrorInfo());
		}
	}
}
// �����������
if( isset($_POST['unit_id_h']) && isset($_POST['process_h']) )
{
	$unit_id_h = $_POST['unit_id_h'];
	$process_h = $_POST['process_h'];
	// ��д����
	if($process_h == "enable_write_cache")
	{
		$retval = $unit_obj->SetUnitWriteCache($unit_id_h, TRUE);
	}
	// �ر�д����
	else if($process_h == "disable_write_cache")
	{
		$retval = $unit_obj->SetUnitWriteCache($unit_id_h, FALSE);
	}
	// ���Զ�У��
	else if($process_h == "enable_auto_verify")
	{
		$retval = $unit_obj->SetUnitAutoVerify($unit_id_h, TRUE);
	}
	// �ر��Զ�����
	else if($process_h == "disable_auto_verify")
	{
		$retval = $unit_obj->SetUnitAutoVerify($unit_id_h, FALSE);
	}
	// ��ECC
	else if($process_h == "enable_ecc")
	{
		$retval = $unit_obj->SetUnitECC($unit_id_h, TRUE);
	}
	// �ر�ECC
	else if($process_h == "disable_ecc")
	{
		$retval = $unit_obj->SetUnitECC($unit_id_h, FALSE);
	}
	// �򿪶���
	else if($process_h == "enable_queue")
	{
		$retval = $unit_obj->SetUnitQueuing($unit_id_h, TRUE);
	}
	// �رն���
	else if($process_h == "disable_queue")
	{
		$retval = $unit_obj->SetUnitQueuing($unit_id_h, FALSE);
	}
	// ���ô洢����Ϊ����ģʽ
	else if($process_h == "protection")
	{
		$retval = $unit_obj->SetUnitStorsave($unit_id_h, STORSAVE_PROTECTION);
	}
	// ���ô洢����Ϊƽ��ģʽ
	else if($process_h == "balance")
	{
		$retval = $unit_obj->SetUnitStorsave($unit_id_h, STORSAVE_BALANCE);
	}
	// ���ô洢����Ϊ����ģʽ
	else if($process_h == "performance")
	{
		$retval = $unit_obj->SetUnitStorsave($unit_id_h, STORSAVE_PERFORMANCE);
	}
	// ���ÿ���RAID�޸�Ϊȫ��
	else if($process_h == "all")
	{
		$retval = $unit_obj->SetUnitRapidRecoveryControl($unit_id_h, RRC_ALL);
	}
	// ���ÿ���RAID�޸�Ϊ�ؽ�
	else if($process_h == "rebuild")
	{
		$retval = $unit_obj->SetUnitRapidRecoveryControl($unit_id_h, RRC_REBUILD);
	}
	// ���ÿ���RAID�޸�Ϊ������
	else if($process_h == "disable")
	{
		$retval = $unit_obj->SetUnitRapidRecoveryControl($unit_id_h, RRC_DISABLE);
	}
	
	if($retval !== TRUE)
	{
		print_msg_block($objCtl->GetLastErrorInfo());
	}
}
// ����unit����
if( isset($_POST['unsf_unit_name_h']) && isset($_POST['unsf_unit_id_h']) )
{
	$unsf_unit_name_h = $_POST['unsf_unit_name_h'];
	$unsf_unit_id_h = $_POST['unsf_unit_id_h'];
	
	$retval = $unit_obj->SetUnitName($unsf_unit_id_h, $unsf_unit_name_h);
	if($retval !== TRUE)
	{
		print_msg_block($objCtl->GetLastErrorInfo());
	}
}
?>
<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\" <html xmlns="http://www.w3.org/1999/xhtml">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<link rel="stylesheet" href="css/target.css" type="text/css" />
<script defer type="text/javascript" src="js/pngfix.js"></script>
<script defer type="text/javascript" src="js/function.js"></script>
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
function VerifyRateChange()
{
	document.bg_task_rate_form.submit();
	return true;
}
function RebuildRateChange()
{
	document.bg_task_rate_form.submit();
	return true;
}
function SetWriteCache(obj, unit_id)
{
	if(obj.checked)
	{
		document.unit_policies_form.process_h.value = "enable_write_cache";
	}
	else
	{
		document.unit_policies_form.process_h.value = "disable_write_cache";
	}
	document.unit_policies_form.unit_id_h.value = unit_id;
	document.unit_policies_form.submit();
	return true;
}
function SetAutoVerify(obj, unit_id)
{
	if(obj.checked)
	{
		document.unit_policies_form.process_h.value = "enable_auto_verify";
	}
	else
	{
		document.unit_policies_form.process_h.value = "disable_auto_verify";
	}
	document.unit_policies_form.unit_id_h.value = unit_id;
	document.unit_policies_form.submit();
	return true;
}
function SetECC(obj, unit_id)
{
	if(obj.checked)
	{
		document.unit_policies_form.process_h.value = "enable_ecc";
	}
	else
	{
		document.unit_policies_form.process_h.value = "disable_ecc";
	}
	document.unit_policies_form.unit_id_h.value = unit_id;
	document.unit_policies_form.submit();
	return true;
}
function SetQueuing(obj, unit_id)
{
	if(obj.checked)
	{
		document.unit_policies_form.process_h.value = "enable_queue";
	}
	else
	{
		document.unit_policies_form.process_h.value = "disable_queue";
	}
	document.unit_policies_form.unit_id_h.value = unit_id;
	document.unit_policies_form.submit();
	return true;
}
function SetStorsave(obj, unit_id)
{
	document.unit_policies_form.process_h.value = obj.value;
	document.unit_policies_form.unit_id_h.value = unit_id;
	document.unit_policies_form.submit();
	return true;
}
function SetRapidRecovery(obj, unit_id, msg, origin_value)
{
	if(obj.value=="disable")
	{
		if( confirm(msg) )
		{
			// 
		}
		else
		{
			obj.value = origin_value;
			return false;
		}
	}
	
	document.unit_policies_form.process_h.value = obj.value;
	document.unit_policies_form.unit_id_h.value = unit_id;
	document.unit_policies_form.submit();
	
	return true;
}
function SaveUnitName(textid, unit_id, msg)
{
	textobj = document.getElementById(textid);
	document.unit_name_set_form.unsf_unit_id_h.value = unit_id;
	document.unit_name_set_form.unsf_unit_name_h.value = textobj.value;
	if(IsUnitNameOk(textobj.value))
	{
		document.unit_name_set_form.submit();
	}
	else
	{
		alert(msg);
		textobj.focus();
		textobj.select();
	}
	return true;
}
function ResetUnitName(textid, name)
{
	textobj = document.getElementById(textid);
	textobj.value = name;
	textobj.focus();
	//textobj.select();
	return true;
}
</script>
</head>

<body>

<table align="center" width="100%">
  <tr>
    <td class="bar_nopanel"><?php print $controller_setting_str[$lang]; ?></td>
  </tr>
</table>
<?php 
if($b_have_controller === TRUE)
{
	// ������ѡ��
	print "<form id=\"sel_controller_form\" name=\"sel_controller_form\" action=\"controller_target.php\" method=\"post\">";
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
	/*
	print "  	  <td class=\"field_title\">";
	print $number_of_drives_str[$lang] . ":";
	print "  	  </td>";
	print "  	  <td class=\"field_data1\">";
	print $controller_selected['drives'];
	print "  	  </td>";
	*/
	print "  	  <td class=\"field_data1\">";
	print "			<input type=\"submit\" name=\"rescan_controller_submit\" onclick=\"RescanController('{$controller_selected['name']}','{$controller_selected['id']}');\" value=\"{$rescan_controller_str[$lang]}\" />";
	print "  	  </td>";
	print "  	</tr>";
	print "  </table>";
	print "</form>";
	
	// ���ú�̨��������
	$bg_rebuild_rate = $objCtl->GetBgRebuildRate($controller_selected['id']);
	$bg_verify_rate  = $objCtl->GetBgVerifyRate($controller_selected['id']);
	$checked = "";
	print "<form id=\"bg_task_rate_form\" name=\"bg_task_rate_form\" action=\"controller_target.php?sl_c_id={$controller_selected['id']}\" method=\"post\">";
	print "  <table width=\"100%\" border=\"0\" cellpadding=\"6\" align=\"center\">";
	print "  <tr>";
	print "    <td class=\"title\" colspan=\"2\">" . $bg_test_rate_str[$lang] . "</td>";
	print "  </tr>";
	print "  <tr>";
	print "    <td class=\"field_title\">" . $rebuild_rate_str[$lang] . "</td>";
	print "    <td class=\"field_data1\">";
	print $faster_rebuild_str[$lang];
	$rate_array = array( BG_TASK_RATE_HIGH, BG_TASK_RATE_MED_HI, BG_TASK_RATE_MEDIUM,
						 BG_TASK_RATE_MED_LOW, BG_TASK_RATE_LOW );
	foreach($rate_array as $entry)
	{
		print "&emsp;";
		if($entry == $bg_rebuild_rate)
		{
			$checked = "checked";
		}
		else
		{
			$checked = "";
		}
		print "<input type=\"radio\" value=\"".$entry."\" {$checked} name=\"rebuild_rate_radio\" onclick=\"RebuildRateChange();\"/>\n";
	}
//	print "&emsp;";
//	print "<input type=\"radio\" value=\"".BG_TASK_RATE_HIGH. "\" name=\"rebuild_rate_radio\" onclick=\"RebuildRateChange();\"/>\n";
//	print "&emsp;";
//	print "<input type=\"radio\" value=\"".BG_TASK_RATE_MED_HI. "\" name=\"rebuild_rate_radio\" onclick=\"RebuildRateChange();\"/>\n";
//	print "&emsp;";
//	print "<input type=\"radio\" value=\"".BG_TASK_RATE_MEDIUM. "\"  name=\"rebuild_rate_radio\" onclick=\"RebuildRateChange();\"/>\n";
//	print "&emsp;";
//	print "<input type=\"radio\" value=\"".BG_TASK_RATE_MED_LOW. "\"  name=\"rebuild_rate_radio\" onclick=\"RebuildRateChange();\"/>\n";
//	print "&emsp;";
//	print "<input type=\"radio\" value=\"".BG_TASK_RATE_LOW. "\"  name=\"rebuild_rate_radio\" onclick=\"RebuildRateChange();\"/>\n";
	print "&emsp;";
	print $faster_io_str[$lang];
	print "    </td>";
	print "  </tr>";
	print "  <tr>";
	print "    <td class=\"field_title\">" . $verify_rate_str[$lang] . "</td>";
	print "    <td class=\"field_data1\">";
	print $faster_verify_str[$lang];
	foreach($rate_array as $entry)
	{
		print "&emsp;";
		if($entry == $bg_verify_rate)
		{
			$checked = "checked";
		}
		else
		{
			$checked = "";
		}
		print "<input type=\"radio\" value=\"".$entry."\" name=\"verify_rate_radio\" {$checked} onclick=\"VerifyRateChange();\"/>\n";	
	}
//	print "&emsp;";
//	print "<input type=\"radio\" value=\"".BG_TASK_RATE_HIGH. "\" name=\"verify_rate_radio\" onclick=\"VerifyRateChange();\"/>\n";
//	print "&emsp;";
//	print "<input type=\"radio\" value=\"".BG_TASK_RATE_MED_HI. "\" name=\"verify_rate_radio\" onclick=\"VerifyRateChange();\"/>\n";
//	print "&emsp;";
//	print "<input type=\"radio\" value=\"".BG_TASK_RATE_MEDIUM. "\"  name=\"verify_rate_radio\" onclick=\"VerifyRateChange();\"/>\n";
//	print "&emsp;";
//	print "<input type=\"radio\" value=\"".BG_TASK_RATE_MED_LOW. "\"  name=\"verify_rate_radio\" onclick=\"VerifyRateChange();\"/>\n";
//	print "&emsp;";
//	print "<input type=\"radio\" value=\"".BG_TASK_RATE_LOW. "\"  name=\"verify_rate_radio\" onclick=\"VerifyRateChange();\"/>\n";
	print "&emsp;";
	print $faster_io_str[$lang];
	print "    </td>";
	print "  </tr>";
	print "</table>";
	print "</form>";
	
	// UNIT��������
	print "<form id=\"unit_policies_form\" name=\"unit_policies_form\" action=\"controller_target.php?sl_c_id={$controller_selected['id']}\" method=\"post\">";
	print "  <input type=\"hidden\" name=\"unit_id_h\" id=\"unit_id_h\" value=\"\">";// ����Ҫ�����unit id
	print "  <input type=\"hidden\" name=\"process_h\" id=\"process_h\" value=\"\">";// ���洦����
	print "  <table width=\"100%\" border=\"0\" cellpadding=\"6\" align=\"center\">";
	print "  <tr>";
	print "    <td class=\"title\" colspan=\"7\">" . $unit_policies_setting_str[$lang] . "</td>";
	print "  </tr>";
	print "  <tr>";
	print "    <td class=\"field_title\"></td>";
	print "    <td class=\"field_title\">" . $write_cache_str[$lang] . "</td>";
	print "    <td class=\"field_title\">" . $auto_verify_str[$lang] . "</td>";
	print "    <td class=\"field_title\">" . $overwrite_ecc_str[$lang] . "</td>";
	print "    <td class=\"field_title\">" . $queuing_str[$lang] . "</td>";
	print "    <td class=\"field_title\">" . $storsave_str[$lang] . "</td>";
	print "    <td class=\"field_title\">" . $rapid_raid_recovery_str[$lang] . "</td>";
	print "  </tr>";
	// �Ѵ�����unit������Ϣ�б���ʾ
	$b_has_item = FALSE;
	$b_error = FALSE;
	$UnitDetailInfo = array();
	$UnitDetailInfoList = array();
	$UnitIdList = $unit_obj->GetUnitIdList($controller_selected['id']);
	if($UnitIdList !== FALSE)
	{
		foreach($UnitIdList as $UnitId)
		{
			$UnitDetailInfo = $unit_obj->GetUnitDetailInfo($UnitId);
			if($UnitDetailInfo === FALSE)
			{
				$b_error = TRUE;
				break;
			}
			$UnitDetailInfoList[] = $UnitDetailInfo;
		}
		if($b_error !== TRUE)
		{
			$td_class = "field_data1";
			$checked = "";
			foreach( $UnitDetailInfoList as $UnitDetailInfo )
			{
				$b_has_item = TRUE;
				print "  <tr>";
				print "    <td class=\"{$td_class}_left\">";
				print "<a class=\"general_link\" href=\"unit_target.php?unit_sel_id={$UnitDetailInfo['id']}\">";
				print $unit_str[$lang]. " " . $UnitDetailInfo['number'];
				print "</a>&emsp;[{$UnitDetailInfo['type']}]</td>";
				// д����
				print "    <td class=\"{$td_class}\">";
				if($UnitDetailInfo['write_cache'] == -1)
				{
					print "--";
				}
				else
				{
					if($UnitDetailInfo['write_cache'] == 0)
					{
						$checked = "checked";
					}
					else if($UnitDetailInfo['write_cache'] == 1)
					{
						$checked = "";
					}
					print "<input type=\"checkbox\" {$checked} onclick=\"SetWriteCache(this, '{$UnitDetailInfo['id']}');\" name=\"wc_cb\" />";
				}
				print "    </td>";
				// �Զ�У��
				print "    <td class=\"{$td_class}\">";
				if($UnitDetailInfo['auto_verify'] == -1)
				{
					print "--";
				}
				else
				{
					if($UnitDetailInfo['auto_verify'] == 0)
					{
						$checked = "checked";
					}
					else if($UnitDetailInfo['auto_verify'] == 1)
					{
						$checked = "";
					}
					print "<input type=\"checkbox\" {$checked} onclick=\"SetAutoVerify(this, '{$UnitDetailInfo['id']}');\" name=\"av_cb\" />";
				}
				print "    </td>";
				// ECC
				print "    <td class=\"{$td_class}\">";
				if($UnitDetailInfo['ecc'] == -1)
				{
					print "--";
				}
				else
				{
					if($UnitDetailInfo['ecc'] == 0)
					{
						$checked = "checked";
					}
					else if($UnitDetailInfo['ecc'] == 1)
					{
						$checked = "";
					}
					print "<input type=\"checkbox\" {$checked} onclick=\"SetECC(this, '{$UnitDetailInfo['id']}');\" name=\"ecc_cb\" />";
				}
				print "    </td>";
				// ����
				print "    <td class=\"{$td_class}\">";
				if($UnitDetailInfo['queue'] == -1)
				{
					print "--";
				}
				else
				{
					if($UnitDetailInfo['queue'] == 0)
					{
						$checked = "checked";
					}
					else if($UnitDetailInfo['queue'] == 1)
					{
						$checked = "";
					}
					print "<input type=\"checkbox\" {$checked} onclick=\"SetQueuing(this, '{$UnitDetailInfo['id']}');\" name=\"queue_cb\" />";
				}
				print "    </td>";
				// �洢����
				print "    <td class=\"{$td_class}\">";
				$protection_sel = "";
				$balance_sel = "";
				$performance_sel = "";
				if($UnitDetailInfo['storsave'] == -1)
				{
					print "--";
				}
				else
				{
					//0-������Protection��,1-ƽ��(Balance)��2-����(Performance)
					if($UnitDetailInfo['storsave'] == 0)
					{
						$protection_sel = "selected";
						$balance_sel = "";
						$performance_sel = "";
					}
					else if($UnitDetailInfo['storsave'] == 1)
					{
						$protection_sel = "";
						$balance_sel = "selected";
						$performance_sel = "";
					}
					else if($UnitDetailInfo['storsave'] == 2)
					{
						$protection_sel = "";
						$balance_sel = "";
						$performance_sel = "selected";
					}
					print "<select name=\"storsave_sel\" onchange=\"SetStorsave(this, '{$UnitDetailInfo['id']}');\" >";
					print "<option value=\"protection\" {$protection_sel}/>Protection";
					print "<option value=\"balance\" {$balance_sel}/>Balance";
					print "<option value=\"performance\" {$performance_sel}/>Performance";
					print "</select>";
				}
				print "    </td>";
				// ����RAID�޸�
				print "    <td class=\"{$td_class}\">";
				$all_sel = "";
				$rebuild_sel = "";
				$disable_sel = "";
				$make_select_disabled = "";
				$original_value = "";
				if($UnitDetailInfo['rrr'] == -1)
				{
					print "--";
				}
				else
				{
					// 0-�ؽ���Rebuild����1-���У�All��,2-δ��
					if($UnitDetailInfo['rrr'] == 0)
					{
						$all_sel = "";
						$rebuild_sel = "selected";
						$disable_sel = "";
						$make_select_disabled = "";
						$original_value = "rebuild";
					}
					else if($UnitDetailInfo['rrr'] == 1)
					{
						$all_sel = "selected";
						$rebuild_sel = "";
						$disable_sel = "";
						$make_select_disabled = "";
						$original_value = "all";
					}
					else if($UnitDetailInfo['rrr'] == 2)
					{
						$all_sel = "";
						$rebuild_sel = "";
						$disable_sel = "selected";
						$make_select_disabled = "disabled";
						$original_value = "disable";
					}
					print "<select name=\"rrr_sel\" {$make_select_disabled} onchange=\"SetRapidRecovery(this, '{$UnitDetailInfo['id']}', '{$rrr_warning_str[$lang]}', '{$original_value}');\" >";
					print "<option value=\"all\" {$all_sel}/>All";
					print "<option value=\"rebuild\" {$rebuild_sel}/>Rebuild";
					print "<option value=\"disable\" {$disable_sel}/>Disable";
					print "</select>";
				}
				print "    </td>";
				print "  </tr>";
				
				$td_class = ($td_class == "field_data1") ? "field_data2" : "field_data1";
			}
			if($b_has_item === FALSE)
			{
				print_msg_block( $no_available_units_str[$lang] );
			}
			print "  </table>";
			print "</form>";
		}// if($b_error == true)
	}// if($UnitIdList !== FALSE)
	
		// UNIT��������
	print "<form id=\"unit_name_set_form\" name=\"unit_name_set_form\" action=\"controller_target.php?sl_c_id={$controller_selected['id']}\" method=\"post\">";
	print "  <input type=\"hidden\" name=\"unsf_unit_id_h\" id=\"unsf_unit_id_h\" value=\"\">";// ����Ҫ�����unit id
	print "  <input type=\"hidden\" name=\"unsf_unit_name_h\" id=\"unsf_unit_name_h\" value=\"\">";// ��������
	print "  <table width=\"100%\" border=\"0\" cellpadding=\"6\" align=\"center\">";
	print "  <tr>";
	print "    <td class=\"title\" colspan=\"3\">" . $unit_name_setting_str[$lang] . "</td>";
	print "  </tr>";
	$b_has_item = FALSE;
	if($b_error !== TRUE)
	{
		$td_class = "field_data1";
		foreach( $UnitDetailInfoList as $UnitDetailInfo )
		{
			$b_has_item = TRUE;
			print "  <tr>";
			print "    <td class=\"{$td_class}_left\">";
			print "<a class=\"general_link\" href=\"unit_target.php?unit_sel_id={$UnitDetailInfo['id']}\">";
			print $unit_str[$lang]. " " . $UnitDetailInfo['number'];
			print "</a>&emsp;[{$UnitDetailInfo['type']}]</td>";
			
			print "    <td class=\"{$td_class}\">";
			$original_name = $UnitDetailInfo['name'];
			$text_id = "unit_name_{$UnitDetailInfo['number']}";
			print "<input type=\"text\" value=\"{$UnitDetailInfo['name']}\" id=\"{$text_id}\" maxlength=\"15\"/>";
			print "    </td>";
			
			print "    <td class=\"{$td_class}\">";
			print "<input type=\"button\" value=\"{$save_names_str[$lang]}\" onclick=\"SaveUnitName('{$text_id}','{$UnitDetailInfo['id']}','{$invalid_unit_name_str[$lang]}');\" tabIndex=\"0\" />";
			print "<input type=\"button\" value=\"{$reset_names_str[$lang]}\" onclick=\"ResetUnitName('{$text_id}','{$original_name}');\"/>";
			print "    </td>";
			print "  </tr>";
			$td_class = ($td_class == "field_data1") ? "field_data2" : "field_data1";
		}
	}
	
	print "</table>";
	print "</form>";
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
