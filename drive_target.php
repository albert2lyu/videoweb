<?php
require("./include/authenticated.php");
require_once("./include/function.php");
require_once("./include/controller.php");
require_once("./include/drive.php");
require_once("./include/log.php");

$lang=load_lang();

$drive_information_str=array(
	"������Ϣ",
	"Drive Information"
);
$drive_list_str=array(
	"�����б�",
	"Drive List"
);
$drive_index_str=array(
	"���",
	"Index"
);
$drive_name_str=array(
	"����",
	"Name"
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
$drive_unit_str=array(
	"RAID��",
	"Unit"
);
$drive_temperature_str=array(
	"�¶�",
	"Temperature"
);
$drive_status_str=array(
	"״̬",
	"Status"
);
$drive_identify_str=array(
	"��λ",
	"Identify"
);
$drive_detail_str=array(
	"��ϸ��Ϣ",
	"Detail"
);
$select_controller_str=array(
	"ѡ�������",
	"Select Controller"
);
$number_of_drives_str=array(
	"���Ӵ�����",
	"Number of Drives"
);
$no_controller_str=array(
	"�޿��ÿ�����",
	"No Controller"
);
$no_drive_linked_str=array(
	"û�д���",
	"None Drive"
);
$is_identify_drive_str=array(
	"�Ƿ�λ�˴���?",
	"Identify the drive?"
);
$show_detail_str=array(
	"��ʾ������ϸ��Ϣ",
	"Show drive detail"
);
$identify_drive_str=array(
	"��λ�˴���",
	"Identify the drive"
);
$rescan_controller_str=array(
	"����ɨ�������",
	"Rescan Controller"
);
?>

<?php 
$objCtl = new Controller();
$drive_obj      = new Drive();
$objLog = new Log();
$name_controller_selected = ""; // ѡ��Ŀ���������
$id_controller_selected = ""; // ѡ��Ŀ�����ID
$controller_selected = array(); // ѡ��Ŀ�����
$listCtrl = array();
$b_have_controller = TRUE;
$id_drive_highlight = "";
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
// ������ʾ�Ĵ���
if( isset($_GET['hl_d_id']) )
{
	$id_drive_highlight = trim($_GET['hl_d_id']);
	if( ! IsIdOk($id_drive_highlight) )
	{
		$id_drive_highlight = "";
	}
}
// ��λ����
if( isset($_POST['identify_drive_id']) )
{
	$drive_obj->IdentifyDrive($_POST['identify_drive_id']);
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
function GetDriveDetail(c_name, id)
{
	var page='drive_detail_info.php?c_name=' + c_name  + '&id=' + id;
	window.open (
			 page,
			 'drive_detail_info_window',
			 'height=620,width=500,top=100,left=200,resizable=no,location=no,status=yes'
	);
	return true;
}
function IdentifyDrive(id, msg)
{
	document.drive_info_form.identify_drive_id.value=id;
	if( confirm(msg) )
	{
		document.drive_info_form.submit();
		return true;
	}
	return false;
}

</script>
</head>

<body>
<table align="center" width="100%">
  <tr>
    <td class="bar_nopanel"><?php print $drive_information_str[$lang]; ?></td>
  </tr>
</table>
<?php 
if($b_have_controller === TRUE)
{
	// ������ѡ��
	print "<form id=\"sel_controller_form\" name=\"sel_controller_form\" action=\"drive_target.php\" method=\"post\">";
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
	print $number_of_drives_str[$lang] . ":";
	print "  	  </td>";
	print "  	  <td class=\"field_data1\">";
	print $controller_selected['drives'];
	print "  	  </td>";
	print "  	  <td class=\"field_data1\">";
	print "			<input type=\"submit\" name=\"rescan_controller_submit\" onclick=\"RescanController('{$controller_selected['name']}','{$controller_selected['id']}');\" value=\"{$rescan_controller_str[$lang]}\" />";
	print "  	  </td>";
	print "  	</tr>";
	print "  </table>";
	print "</form>";
	
	// ������Ϣ�б���ʾ
	$drive_id_list = array();
	$driveInfo = array();
	$listDriveInfo = array();
	$b_error = FALSE;
	if( $controller_selected['drives'] != 0 )
	{
		$drive_id_list = $drive_obj->GetDriveIdList( $controller_selected['id'] );
		if( $drive_id_list !== FALSE )
		{
			foreach( $drive_id_list as $drive_id )
			{
				$driveInfo = $drive_obj->GetDriveBasicInfo( $drive_id );
				if( $driveInfo === FALSE )
				{
					$b_error = TRUE;
					break;
				}
				$listDriveInfo[] = $driveInfo;
			}
			if( $b_error === FALSE )
			{
///////////////////////////////////////////////////////////////////////////////////////
// ��ʾ�����б���Ϣ
print "<form id=\"drive_info_form\" name=\"drive_info_form\" action=\"drive_target.php\" method=\"post\">";
print "<input type=\"hidden\" name=\"identify_drive_id\" id=\"identify_drive_id\" value=\"\">";		
print "<table width=\"100%\" border=\"0\" cellpadding=\"6\" align=\"center\">";

print "  <tr>";
print "    <td class=\"title\" colspan=\"10\">" . $drive_list_str[$lang] . "</td>";
print "  </tr>";

print "  <tr>";
print "    <td class=\"field_title\">" . $drive_index_str[$lang] ."</td>";
print "    <td class=\"field_title\">" . $drive_name_str[$lang] ."</td>";
print "    <td class=\"field_title\">" . $drive_model_str[$lang] ."</td>";
print "    <td class=\"field_title\">" . $drive_capacity_str[$lang] ."</td>";
print "    <td class=\"field_title\">" . $drive_type_str[$lang] ."</td>";
print "    <td class=\"field_title\">" . $drive_slot_str[$lang] ."</td>";
print "    <td class=\"field_title\">" . $drive_unit_str[$lang] ."</td>";
//print "    <td class=\"field_title\">" . $drive_temperature_str[$lang] ."</td>";
print "    <td class=\"field_title\">" . $drive_status_str[$lang] ."</td>";
print "    <td class=\"field_title\">" . $drive_identify_str[$lang] ."</td>";
print "    <td class=\"field_title\">" . $drive_detail_str[$lang] ."</td>";
print "  </tr>";

$td_class = "field_data1";
$drive_index = 1;
foreach( $listDriveInfo as $driveInfo )
{
	if( $id_drive_highlight == $driveInfo['id'] )
	{
		print "  <tr>";
		print "    <td class=\"field_highlight\">{$drive_index}</td>";
		print "    <td class=\"field_highlight\">{$driveInfo['name']}</td>";
		print "    <td class=\"field_highlight\">{$driveInfo['model']}</td>";
		print "    <td class=\"field_highlight\">{$driveInfo['capacity']}</td>";
		print "    <td class=\"field_highlight\">{$driveInfo['type']}</td>";
		print "    <td class=\"field_highlight\">{$driveInfo['slot']}</td>";
		print "    <td class=\"field_highlight\">";
		if( $driveInfo['unit'] == "")
		{
			print "--";
		}
		else
		{
			print "<a href=\"unit_target.php?unit_sel_number={$driveInfo['unit']}&controller_sel_id={$controller_selected['id']}\" class=\"general_link\">{$driveInfo['unit']}</a>";
		}
		print "</td>";
	//	print "    <td class=\"field_highlight\">{$drive_basic_info['temperature']}</td>";
		print "    <td class=\"field_highlight\">";
		if( $driveInfo['status'] == "OK")
		{
			print "<font class=\"statusOK\">";
			print $driveInfo['status'];
			print "</font>";
		}
		else
		{
			print "<font class=\"statusOther\">";
			print $driveInfo['status'];
			print "</font>";
		}
		print "</td>";
		print "    <td class=\"field_highlight\">";
		print "<img style=\"cursor:crosshair\" title=\"{$identify_drive_str[$lang]}\" src=\"images/drive_identify_icon.gif\" onclick=\"IdentifyDrive('{$driveInfo['id']}', '{$is_identify_drive_str[$lang]}');\"></img>";
		print "    </td>";
		print "    <td class=\"field_highlight\">";
		print "<img style=\"cursor:pointer\" title=\"{$show_detail_str[$lang]}\" src=\"images/drive_detail_icon.gif\" onclick=\"GetDriveDetail('{$controller_selected['name']}', '{$driveInfo['id']}');\"></img>";
		print "    </td>";
		print "  </tr>";
	}
	else
	{
		print "  <tr>";
		print "    <td class=\"{$td_class}\">{$drive_index}</td>";
		print "    <td class=\"{$td_class}\">{$driveInfo['name']}</td>";
		print "    <td class=\"{$td_class}\">{$driveInfo['model']}</td>";
		print "    <td class=\"{$td_class}\">{$driveInfo['capacity']}</td>";
		print "    <td class=\"{$td_class}\">{$driveInfo['type']}</td>";
		print "    <td class=\"{$td_class}\">{$driveInfo['slot']}</td>";
		print "    <td class=\"{$td_class}\">";
		if( $driveInfo['unit'] == "")
		{
			print "--";
		}
		else
		{
			print "<a href=\"unit_target.php?unit_sel_number={$driveInfo['unit']}&controller_sel_id={$controller_selected['id']}\" class=\"general_link\">{$driveInfo['unit']}</a>";
		}
		print "</td>";
	//	print "    <td class=\"{$td_class}\">{$drive_basic_info['temperature']}</td>";
		print "    <td class=\"{$td_class}\">";
		if( $driveInfo['status'] == "OK")
		{
			print "<font class=\"statusOK\">";
			print $driveInfo['status'];
			print "</font>";
		}
		else
		{
			print "<font class=\"statusOther\">";
			print $driveInfo['status'];
			print "</font>";
		}
		print "</td>";
		print "</td>";
		print "    <td class=\"{$td_class}\">";
		print "<img style=\"cursor:crosshair\" title=\"{$identify_drive_str[$lang]}\" src=\"images/drive_identify_icon.gif\" onclick=\"IdentifyDrive('{$driveInfo['id']}', '{$is_identify_drive_str[$lang]}');\"></img>";
		print "    </td>";
		print "    <td class=\"{$td_class}\">";
		print "<img style=\"cursor:pointer\" title=\"{$show_detail_str[$lang]}\" src=\"images/drive_detail_icon.gif\" onclick=\"GetDriveDetail('{$controller_selected['name']}', '{$driveInfo['id']}');\"></img>";
		print "    </td>";
		print "  </tr>";
	}
	
	$td_class = ($td_class == "field_data1") ? "field_data2" : "field_data1";
	$drive_index++;
}

print "</table>";
print "</form>";
///////////////////////////////////////////////////////////////////////////////////////
			}
			else
			{
				print_msg_block($drive_obj->GetLastErrorInfo());
			}
			
		}
		else
		{
			print_msg_block($drive_obj->GetLastErrorInfo());
		}
	}
	else
	{
		print_msg_block($no_drive_linked_str[$lang]);
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

