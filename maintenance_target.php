<?php 
require("./include/authenticated.php");
require_once("./include/function.php");
require_once("./include/controller.php");
require_once("./include/unit.php");
require_once("./include/drive.php");
require_once("./include/log.php");
require_once("./include/file.php");

$lang=load_lang();

$unit_maintenance_str=array(
	"RAID�����",
	"Unit Maintenance"
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
$unit_created_str=array(
	"�Ѵ�����RAID��",
	"Unit Created"
);
$drive_str=array(
	"����",
	"drive"
);
$drives_str=array(
	"����",
	"drives"
);
$unit_str=array(
	"RAID��",
	"Unit"
);
$verifying_str=array(
	"У��",
	"Verifying"
);
$verify_str=array(
	"У��",
	"Verify"
);
$stop_verify_str=array(
	"ֹͣУ��",
	"Stop Verify"
);
$rebuild_str=array(
	"�ؽ�",
	"Rebuild"
);
$remove_str=array(
	"����",
	"Remove"
);
$delete_str=array(
	"ɾ��",
	"Delete"
);
$available_drives_str=array(
	"���õĴ���",
	"Available Drives"
);
$de_select_all_drives_str=array(
	"��ȡ����ѡ��ȫ������",
	"(De-)Select All Drives"
);
$create_unit_str=array(
	"����RAID��",
	"Create Unit"
);
$drives_selected_str=array(
	"ѡ��Ĵ���",
	"Drives Selected"
);
$raid_type_str=array(
	"RAID������",
	"Type"
);
$raid_name_str=array(
	"����",
	"Name"
);
$stripe_size_str=array(
	"������С",
	"Stripe"
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
$rapid_raid_recovery_str=array(
	"����RAID�޸�",
	"Rapid RAID Recovery"
);
$all_str=array(
	"ȫ��",
	"All"
);
$disable_str=array(
	"������",
	"Disable"
);
$str_ok=array(
	"ȷ��",
	"OK"
);
$str_cancel=array(
	"ȡ��",
	"Cancel"
);
$show_unit_detail_tip_str=array(
	"�򿪴�RAID�����ϸ��Ϣ",
	"Open Unit Detail Information"
);
$no_drives_str=array(
	"�޴���",
	"No Drives"
);
$not_present_str=array(
	"������",
	"Not Present"
);
$close_str=array(
	"�ر�",
	"Close"
);
$drives_per_subunit_str=array(
	"ÿ����̸���",
	"Drives Per Subunit"
);
$rrr_warning_str=array(
	"���ÿ���RAID�޸�Ϊ�����ã���Ч�󣬽����������޸ġ�",
	"Setting Rapid RAID Recovery to Disable is permanent for this unit and CANNOT be changed at a later time."
);
$drives_count_max_str=array(
	"ѡ����̵ĸ������ܳ���32����",
	"The number of drives selected must not be larger than 32!"
);
$delete_unit_confirm_str=array(
	"ɾ����RAID�飿",
	"Delete the unit?"
);
$invalid_unit_name_str=array(
	"��ЧRAID�����ƣ�\\n��Ч�ַ�:A-Za-z0-9_-������<=20",
	"Invalid Unit Name��\\nValid Character:A-Za-z0-9_-, Length <= 20"
);
$action_verify_unit_str      = "verify_unit_action";
$action_stop_verify_unit_str = "stop_verify_unit_action";
$action_rebuild_unit_str     = "rebuild_unit_action";
$action_delete_unit_str      = "delete_unit_action";
$action_create_unit_str      = "create_unit_action";
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
// RAID�����
if( isset($_POST['unit_id_selected_h']) && isset($_POST['unit_process_h']) )
{
	$unit_id_h = $_POST['unit_id_selected_h'];
	$process_h = $_POST['unit_process_h'];
	
	if( $process_h == $action_verify_unit_str )
	{
		$retval = $unit_obj->VerifyUnit($unit_id_h);
	}
	else if( $process_h == $action_stop_verify_unit_str )
	{
		$retval = $unit_obj->StopVerifyUnit($unit_id_h);
	}
	else if( $process_h == $action_rebuild_unit_str )
	{
		$retval = $unit_obj->RebuildUnit();
	}
	else if( $process_h == $action_delete_unit_str )
	{
		$retval = $unit_obj->DeleteUnit($unit_id_h);
	}
	
	if($retval !== TRUE)
	{
		print_msg_block($unit_obj->GetLastErrorInfo());
	}
}

// RAID����
while( isset($_POST['drives_id_selected_h']) && trim($_POST['drives_id_selected_h']) != "")
{
	$drives_id_sel_list_string = $_POST['drives_id_selected_h'];
	$drives_id_sel_list = explode(";", $drives_id_sel_list_string);
	// ���һ��Ϊ�գ�����
	array_pop($drives_id_sel_list);
	$drives_sel_count = count($drives_id_sel_list);
	// �ж�ѡ��Ĵ�������
	if($drives_sel_count === 0 || $drives_sel_count > 32)
	{
		break;
	}
	// ������ID�б�д���ļ�/tmp/drive_id_list
	$drives_id_file = "/tmp/drive_id_list";
	$drv_fp = fopen($drives_id_file, 'w');
	if( $drv_fp === FALSE )
	{
		break;
	}
	foreach( $drives_id_sel_list as $entry )
	{
		fputs($drv_fp, $entry . "\n");
	}
	fflush($drv_fp);
	fclose($drv_fp);
	
	// ��UNIT����ز���д���ļ�/tmp/unit_params
	$unit_params_file = "/tmp/unit_params";
	$up_fp = fopen($unit_params_file, 'w');
	if( $up_fp === FALSE )
	{
		break;
	}
	// RAID����
	$raid_type = "";
	$raid_type_number = 0;
	if( ! isset($_POST['raid_type_select']) )
	{
		break;
	}
	$raid_type = $_POST['raid_type_select'];
	//0-SingleDisk 1-Spare 2-RAID0 3-RAID1 4-RAID5 5-RAID6 6-RAID10 7-RAID50
	if($raid_type == "singledisk")
	{
		$raid_type_number = 0;
	}
	else if($raid_type == "sparedisk")
	{
		$raid_type_number = 1;
	}
	else if($raid_type == "raid0")
	{
		$raid_type_number = 2;
	}
	else if($raid_type == "raid1")
	{
		$raid_type_number = 3;
	}
	else if($raid_type == "raid5")
	{
		$raid_type_number = 4;
	}
	else if($raid_type == "raid6")
	{
		$raid_type_number = 5;
	}
	else if($raid_type == "raid10")
	{
		$raid_type_number = 6;
	}
	else if($raid_type == "raid50")
	{
		$raid_type_number = 7;
	}
	fputs($up_fp, $raid_type_number . "\n");
	
	// unit����
	$unit_name = "";
	if( ! isset($_POST['raid_name_text']) )
	{
		$unit_name = "";
	}
	else
	{
		$unit_name = $_POST['raid_name_text'];
	}
	fputs($up_fp, $unit_name . "\n");
	
	// ������С
	$strip_size = 64;
	if( ! isset($_POST['raid_stripe_select']) )
	{
		$strip_size = -1;
	}
	else
	{
		$strip_size = $_POST['raid_stripe_select'];
	}
	if($raid_type == "singledisk" || $raid_type == "sparedisk" || $raid_type == "raid1")
	{
		$strip_size = -1;
	}
	fputs($up_fp, $strip_size . "\n");
	
	// ÿ����̸���������RAID50
	$drives_per_subunit = 0;
	if( $raid_type == "raid50" && isset($_POST['drives_per_subunit_select']) )
	{
		$drives_per_subunit = $_POST['drives_per_subunit_select'];
	}
	else
	{
		$drives_per_subunit = -1;
	}
	fputs($up_fp, $drives_per_subunit . "\n");
	
	// д����
	$write_cache = 0;
	if($raid_type == "sparedisk")
	{
		$write_cache = -1;
	}
	else
	{
		if( isset($_POST['write_cache_cb']) )
		{
			$write_cache = 0;
		}
		else
		{
			$write_cache = 1;
		}
	}
	fputs($up_fp, $write_cache . "\n");
	
	// �Զ�У��
	$auto_verify = 0;
	if( isset($_POST['auto_verify_cb']) )
	{
		$auto_verify = 0;
	}
	else
	{
		$auto_verify = 1;
	}
	fputs($up_fp, $auto_verify . "\n");
	
	// ����
	$queuing = 0;
	if($raid_type == "sparedisk")
	{
		$queuing = -1;
	}
	else
	{
		if( isset($_POST['queuing_cb']) )
		{
			$queuing = 0;
		}
		else
		{
			$queuing = 1;
		}
	}
	fputs($up_fp, $queuing . "\n");
	
	// ecc
	$ecc = 1;
	if($raid_type == "sparedisk" || $raid_type == "singledisk" || $raid_type == "raid0")
	{
		$ecc = -1;
	}
	else
	{
		if( isset($_POST['ecc_cb']) )
		{
			$ecc = 0;
		}
		else
		{
			$ecc = 1;
		}
	}
	fputs($up_fp, $ecc . "\n");
	
	// �洢���� 0-����,1-ƽ�⣬2-���ܣ�-1-��֧��
	$storsave = 0;
	if($raid_type == "sparedisk")
	{
		$storsave = -1;
	}
	else
	{
		if( isset($_POST['storsave_select']) )
		{
			$storsave_action = $_POST['storsave_select'];
			if($storsave_action == "protection")
			{
				$storsave = 0;
			}
			else if($storsave_action == "balance")
			{
				$storsave = 1;
			}
			else if($storsave_action == "performance")
			{
				$storsave = 2;
			}
		}
	}
	fputs($up_fp, $storsave . "\n");
	
	// ����RAID�޸� 0-�ؽ���1-����, 2-δ�򿪣�-1-��֧��
	$rrr = 1;
	if($raid_type == "sparedisk" || $raid_type == "singledisk" || $raid_type == "raid0")
	{
		$rrr = -1;
	}
	else
	{
		if( isset($_POST['rrr_select']) )
		{
			$rrr_action = $_POST['rrr_select'];
			if($rrr_action == "rebuild")
			{
				$rrr = 0;
			}
			else if($rrr_action == "all")
			{
				$rrr = 1;
			}
			else if($rrr_action == "disable")
			{
				$rrr = 2;
			}
		}
	}
	fputs($up_fp, $rrr . "\n");
	fflush($up_fp);
	fclose($up_fp);
	
	$retval = $unit_obj->CreateUnit($drives_id_file, $unit_params_file);
	if($retval !== TRUE)
	{
		print_msg_block($unit_obj->GetLastErrorInfo());
	}
	
	break;
}

?>
<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\" <html xmlns="http://www.w3.org/1999/xhtml">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<link rel="stylesheet" href="css/target.css" type="text/css" />
<script defer type="text/javascript" src="js/pngfix.js"></script>
<script defer type="text/javascript" src="js/popupdiv.js"></script>
<script defer type="text/javascript" src="js/function.js"></script>
<script type="text/javascript">
var selected_all = true;
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
function  select_all_drives()
{
	for (i = 0; i < document.getElementsByName("c_u_drives[]").length; i++)
	{
		document.getElementsByName("c_u_drives[]")[i].checked = true;
	}
	return true;
}
function  deselect_all_drives()
{
	for (i = 0; i < document.getElementsByName("c_u_drives[]").length; i++)
	{
		document.getElementsByName("c_u_drives[]")[i].checked = false;
	}
	return true;
}
function select_drives(msg)
{
	if( selected_all )
	{
		selected_all = false;
		select_all_drives();
	}
	else
	{
		selected_all = true;
		deselect_all_drives();
	}
	drive_selected(msg);
	return true;
}
function drive_selected(msg)
{
	var drives_count_selected = 0;
	for (i = 0; i < document.getElementsByName("c_u_drives[]").length; i++)
	{
		if( document.getElementsByName("c_u_drives[]")[i].checked )
		{
			drives_count_selected++;
		}
	}
	// ���ƴ���ѡ�������������һ��UNIT�����֧�ִ�����32
	if(drives_count_selected > 32)
	{
		create_unit_whole.style.display = "none";
		alert(msg);
		return false;
	}
	// ���ƴ���������ʾ���
	var retval = 0;
	if(drives_count_selected > 0)
	{
		create_unit_whole.style.display = "";
		// ����raid����ѡ����ʾ
		ctl_raidtype_select_show(drives_count_selected);
		// ����stripeѡ����ʾ
		ctl_stripe_select_show();
		// ����ÿ����̸�����ʾ
		ctl_drives_per_subunit_select_show();
		// ����д������ʾ
		ctl_write_cache_show();
		// �����Զ�У����ʾ
		ctl_auto_verify_show();
		// ����ECC��ʾ
		ctl_ecc_show();
		// ���ƶ�����ʾ
		ctl_queuing_show();
		// ���ƴ洢������ʾ
		ctl_storsave_show();
		// ���ƿ���RAID�޸���ʾ
		ctl_rrr_show();
	}
	else
	{
		create_unit_whole.style.display = "none";
	}
	
	return true;
}
function ctl_raidtype_select_show(drives_count)
{
	if(drives_count == 0)
	{
		return false;
	}
	var newOption = new Option('','');
	var saveObject=document.getElementById("raid_type_select");
	// ɾ��ԭ�е�����Ԫ��
	saveObject.innerHTML = "";
	// ����������̬����Ԫ��
	if(drives_count == 1)
	{
		// single disk , spare disk
		newOption = new Option('Single Disk','singledisk');
		saveObject.options.add(newOption);
		newOption = new Option('Spare Disk','sparedisk');
		saveObject.options.add(newOption);
		saveObject.value="singledisk";
	}
	else if(drives_count == 2)
	{
		// raid 0/1
		newOption = new Option('RAID 0','raid0');
		saveObject.options.add(newOption);
		newOption = new Option('RAID 1','raid1');
		saveObject.options.add(newOption);
		saveObject.value="raid0";
	}
	else if(drives_count == 3)
	{
		// raid 0/5
		newOption = new Option('RAID 0','raid0');
		saveObject.options.add(newOption);
		newOption = new Option('RAID 5','raid5');
		saveObject.options.add(newOption);
		saveObject.value="raid5";
	}
	else if(drives_count == 4)
	{
		// raid 0/5/10
		newOption = new Option('RAID 0','raid0');
		saveObject.options.add(newOption);
		newOption = new Option('RAID 5','raid5');
		saveObject.options.add(newOption);
		newOption = new Option('RAID 10','raid10');
		saveObject.options.add(newOption);
		saveObject.value="raid5";
	}
	else if(drives_count == 5)
	{
		// raid 0/5/6
		newOption = new Option('RAID 0','raid0');
		saveObject.options.add(newOption);
		newOption = new Option('RAID 5','raid5');
		saveObject.options.add(newOption);
		newOption = new Option('RAID 6','raid6');
		saveObject.options.add(newOption);
		saveObject.value="raid5";
	}
	else if(drives_count == 6)
	{
		// raid 0/5/6
		newOption = new Option('RAID 0','raid0');
		saveObject.options.add(newOption);
		newOption = new Option('RAID 5','raid5');
		saveObject.options.add(newOption);
		newOption = new Option('RAID 6','raid6');
		saveObject.options.add(newOption);
		newOption = new Option('RAID 10','raid10');
		saveObject.options.add(newOption);
		newOption = new Option('RAID 50','raid50');
		saveObject.options.add(newOption);
		saveObject.value="raid5";
	}
	else if(drives_count >= 7)
	{
		// raid 0/5/6
		newOption = new Option('RAID 0','raid0');
		saveObject.options.add(newOption);
		newOption = new Option('RAID 5','raid5');
		saveObject.options.add(newOption);
		newOption = new Option('RAID 6','raid6');
		saveObject.options.add(newOption);
		// ��������֧��raid 10
		if( drives_count%2 == 0 )
		{
			newOption = new Option('RAID 10','raid10');
			saveObject.options.add(newOption);
		}
		if(drives_count%2==0 || drives_count%3==0 || drives_count%4==0)
		{
			newOption = new Option('RAID 50','raid50');
			saveObject.options.add(newOption);
		}
		saveObject.value="raid5";
	}
	return true;
}
function ctl_stripe_select_show()
{
	var typeObject=document.getElementById("raid_type_select");
	var newOption = new Option('','');
	var saveObject=document.getElementById("raid_stripe_select");
	var raidtype = typeObject.value;
	saveObject.innerHTML="";
	if(raidtype == "singledisk" || raidtype == "sparedisk" || raidtype == "raid1")
	{
		raid_stripe_span.style.display = "none";
	}
	else if(raidtype == "raid0" || raidtype == "raid5" || raidtype == "raid10" || raidtype == "raid50")
	{
		raid_stripe_span.style.display = "";
		newOption = new Option('16 KB','16');
		saveObject.options.add(newOption);
		newOption = new Option('64 KB','64');
		saveObject.options.add(newOption);
		newOption = new Option('256 KB','256');
		saveObject.options.add(newOption);
		saveObject.value="64";
	}
	else if(raidtype == "raid6")
	{
		raid_stripe_span.style.display = "";
		newOption = new Option('64 KB','64');
		saveObject.options.add(newOption);
	}
	else
	{
		raid_stripe_span.style.display = "none";
	}
	return true;
}
function ctl_drives_per_subunit_select_show()
{
	var drives_count = 0;
	for (i = 0; i < document.getElementsByName("c_u_drives[]").length; i++)
	{
		if( document.getElementsByName("c_u_drives[]")[i].checked )
		{
			drives_count++;
		}
	}
	var typeObject=document.getElementById("raid_type_select");
	var raidtype = typeObject.value;
	var newOption = new Option('','');
	var saveObject=document.getElementById("drives_per_subunit_select");
	saveObject.innerHTML="";
	if(raidtype == "raid50")
	{
		drives_per_subunit_span.style.display = "";
		var drives_per_subunit = 0;
		
		if(drives_count%2 == 0)
		{
			drives_per_subunit = drives_count/2;
			if(drives_per_subunit >= 3)
			{
				newOption = new Option(drives_per_subunit,drives_per_subunit);
				saveObject.options.add(newOption);
			}
		}
		if(drives_count%3 == 0)
		{
			drives_per_subunit = drives_count/3;
			if(drives_per_subunit >= 3)
			{
				newOption = new Option(drives_per_subunit,drives_per_subunit);
				saveObject.options.add(newOption);
			}
		}
		if(drives_count%4 == 0)
		{
			drives_per_subunit = drives_count/4;
			if(drives_per_subunit >= 3)
			{
				newOption = new Option(drives_per_subunit,drives_per_subunit);
				saveObject.options.add(newOption);
			}
		}
	}
	else
	{
		drives_per_subunit_span.style.display = "none";
	}
	return true;
}
function ctl_write_cache_show()
{
	var typeObject=document.getElementById("raid_type_select");
	var wcObject=document.getElementById("write_cache_cb");
	var raidtype = typeObject.value;
	if(raidtype == "sparedisk")
	{
		write_cache_span.style.display = "none";
	}
	else
	{
		write_cache_span.style.display = "";
		wcObject.checked = true;
	}
	return true;
}
function ctl_auto_verify_show()
{
	var avObject=document.getElementById("auto_verify_cb");
	auto_verify_span.style.display = "";
	avObject.checked = true;
	return true;
}
function ctl_ecc_show()
{
	var typeObject=document.getElementById("raid_type_select");
	var eccObject=document.getElementById("ecc_cb");
	var raidtype = typeObject.value;
	if(raidtype == "sparedisk" || raidtype == "raid0" || raidtype == "singledisk")
	{
		ecc_span.style.display = "none";
	}
	else
	{
		ecc_span.style.display = "";
		eccObject.checked = false;
	}
	return true;
}
function ctl_queuing_show()
{
	var typeObject=document.getElementById("raid_type_select");
	var qObject=document.getElementById("queuing_cb");
	var raidtype = typeObject.value;
	if(raidtype == "sparedisk")
	{
		queuing_span.style.display = "none";
	}
	else
	{
		queuing_span.style.display = "";
		qObject.checked = true;
	}
	return true;
}
function ctl_storsave_show()
{
	var typeObject=document.getElementById("raid_type_select");
	var raidtype = typeObject.value;
	var newOption = new Option('','');
	var saveObject=document.getElementById("storsave_select");
	saveObject.innerHTML = "";
	if(raidtype == "sparedisk")
	{
		storsave_span.style.display = "none";
	}
	else
	{
		storsave_span.style.display = "";
		newOption = new Option('Protection','protection');
		saveObject.options.add(newOption);
		newOption = new Option('Balance','balance');
		saveObject.options.add(newOption);
		newOption = new Option('Performance','performance');
		saveObject.options.add(newOption);
		saveObject.value="protection";
	}
	return true;
}
function ctl_rrr_show()
{
	var typeObject=document.getElementById("raid_type_select");
	var raidtype = typeObject.value;
	var newOption = new Option('','');
	var saveObject=document.getElementById("rrr_select");
	saveObject.innerHTML = "";
	
	if(raidtype == "sparedisk"  ||
	   raidtype == "singledisk" ||
	   raidtype == "raid0"
	)
	{
		rrr_span.style.display = "none";
	}
	else
	{
		rrr_span.style.display = "";
		newOption = new Option('All','all');
		saveObject.options.add(newOption);
		newOption = new Option('Rebuild','rebuild');
		saveObject.options.add(newOption);
		newOption = new Option('Disable','disable');
		saveObject.options.add(newOption);
		saveObject.value="all";
	}
	return true;
}
function raid_type_select_fuc()
{
	// ����stripeѡ������
	ctl_stripe_select_show();
	// ����ÿ����̸�����ʾ
	ctl_drives_per_subunit_select_show();
	// ����д������ʾ
	ctl_write_cache_show();
	// �����Զ�У����ʾ
	ctl_auto_verify_show();
	// ����ECC��ʾ
	ctl_ecc_show();
	// ���ƶ�����ʾ
	ctl_queuing_show();
	// ���ƴ洢������ʾ
	ctl_storsave_show();
	// ���ƿ���RAID�޸���ʾ
	ctl_rrr_show();
	return true;
}
function rrr_select_fuc(msg)
{
	var rrrObject = document.getElementById("rrr_select");
	var value = rrrObject.value;
	if(value == "disable")
	{
		alert(msg);
	}
	return true;
}
function StopVerifyUnit(uid, process)
{
	document.unit_list_form.unit_id_selected_h.value = uid;
	document.unit_list_form.unit_process_h.value = process;
	document.unit_list_form.submit();
	return true;
}
function VerifyUnit(uid, process)
{
	document.unit_list_form.unit_id_selected_h.value = uid;
	document.unit_list_form.unit_process_h.value = process;
	document.unit_list_form.submit();
	return true;
}
function RebuildUnit(uid, process)
{
	document.unit_list_form.unit_id_selected_h.value = uid;
	document.unit_list_form.unit_process_h.value = process;
	//document.unit_list_form.submit();
	return true;
}
function DeleteUnit(uid, process, msg)
{
	if( ! confirm(msg) )
	{
		return false;
	}
	document.unit_list_form.unit_id_selected_h.value = uid;
	document.unit_list_form.unit_process_h.value = process;
	document.unit_list_form.submit();
	return true;
}
function create_unit(msg1)
{
	// ���unit�����Ƿ�Ϸ�
	var unit_name = document.drive_list_form.raid_name_text.value;
	if( ! IsUnitNameOk(unit_name) )
	{
		alert(msg1);
		document.drive_list_form.raid_name_text.focus();
		document.drive_list_form.raid_name_text.select();
		return false;
	}
	
	var text_str = "";
	var i = 0;
	obj = document.getElementsByName("c_u_drives[]");
	for (i = 0; i < obj.length; i++)
	{
		if( obj[i].checked )
		{
			text_str += obj[i].value + ";";
		}
	}
	document.drive_list_form.drives_id_selected_h.value = text_str;
	document.drive_list_form.submit();
	return true;
}

</script>
</head>

<body>
<table align="center" width="100%">
  <tr>
    <td class="bar_nopanel"><?php print $unit_maintenance_str[$lang]; ?></td>
  </tr>
</table>
<?php 
if($b_have_controller === TRUE)
{
	// ������ѡ��
	print "<form id=\"sel_controller_form\" name=\"sel_controller_form\" action=\"maintenance_target.php\" method=\"post\">";
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
	
	// �Ѵ�����unit������Ϣ�б���ʾ
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
			print "<form id=\"unit_list_form\" name=\"unit_list_form\" action=\"maintenance_target.php?sl_c_id={$controller_selected['id']}\" method=\"post\">";
			print "  <input type=\"hidden\" name=\"unit_id_selected_h\" id=\"unit_id_selected_h\" value=\"\">";
			print "  <input type=\"hidden\" name=\"unit_process_h\" id=\"unit_process_h\" value=\"\">";
			print "  <table width=\"100%\" border=\"0\" cellpadding=\"6\" align=\"center\">";
			print "  <tr>";
			print "    <td class=\"title\" colspan=\"7\">" . $unit_created_str[$lang] . "</td>";
			print "  </tr>";
			
			foreach( $UnitBasicInfoList as $UnitBasicInfo )
			{
				print "  <tr>";
				print "    <td class=\"field_title_left\">";
				print "<a title=\"{$show_unit_detail_tip_str[$lang]}\" class=\"general_link\" href=\"unit_target.php?unit_sel_id={$UnitBasicInfo['id']}\">";
				print $unit_str[$lang]. " " . $UnitBasicInfo['number'];
				print "    </a></td>";
				print "    <td class=\"field_title_left\">";
				print $UnitBasicInfo['drive_number'];
				if($UnitBasicInfo['drive_number'] == 1)
				{
					print " " . $drive_str[$lang];
				}
				else
				{
					print " " . $drives_str[$lang];
				}
				print "    </td>";
				print "    <td class=\"field_title_left\">";
				print $UnitBasicInfo['name'];
				print "    </td>";
				print "    </td>";
				print "    <td class=\"field_title_left\">";
				print $UnitBasicInfo['type'];
				print "    </td>";
				print "    <td class=\"field_title_left\">{$UnitBasicInfo['capacity']}</td>";				
				print "    <td class=\"field_title_left\">";
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
				
				print "    <td class=\"field_title_left\">";
				if(preg_match("|{$verifying_str[$lang]}|i", $UnitBasicInfo['status']))
				{
					print "<input type=\"button\" value=\"{$stop_verify_str[$lang]}\" onclick=\"StopVerifyUnit('{$UnitBasicInfo['id']}', '{$action_stop_verify_unit_str}');\" />";
				}
				else
				{
					print "<input type=\"button\" value=\"{$verify_str[$lang]}\" onclick=\"VerifyUnit('{$UnitBasicInfo['id']}', '{$action_verify_unit_str}');\"/>";
				}
				//print "<input type=\"button\" value=\"{$rebuild_str[$lang]}\" onclick=\"RebuildUnit('{$UnitBasicInfo['id']}', '{$action_rebuild_unit_str}');\"/>";
				print "<input type=\"button\" value=\"{$delete_str[$lang]}\" onclick=\"DeleteUnit('{$UnitBasicInfo['id']}', '{$action_delete_unit_str}', '{$delete_unit_confirm_str[$lang]}');\"/>";
				print "    </td>";
				print "  </tr>";
				
				// ��ʾUnit�İ����Ĵ����б���Ϣ
				$Drive_Id_List = $UnitBasicInfo['drive_id_list'];
				if( count($Drive_Id_List) === 0 )
				{
					$message = $no_drives_str[$lang];
				}
				else
				{
					foreach( $Drive_Id_List as $Drive_Id )
					{
						$DriveBasicInfo = $drive_obj->GetDriveBasicInfo($Drive_Id);
						if($DriveBasicInfo === FALSE)
						{
							print "  <tr>";
							print "    <td class=\"field_data1_left\"></td>";
							print "    <td class=\"field_data1_left\">--</td>";
							print "    <td class=\"field_data1_left\">--</td>";
							print "    <td class=\"field_data1_left\">--</td>";
							print "    <td class=\"field_data1_left\">--</td>";
							print "    <td class=\"field_data1_left\">{$not_present_str[$lang]}</td>";
							print "  </tr>";
						}
						else
						{
							print "  <tr>";
							print "    <td class=\"field_data1_left\"></td>";
							print "    <td class=\"field_data1_left\">";
							//print "<a class=\"general_link\" href=\"drive_target.php?hl_d_id={$DriveBasicInfo['id']}&sl_c_id={$controller_selected['id']}\">";
							print  $DriveBasicInfo['name'];
							//print "</a>";
							print "</td>";
							print "    <td class=\"field_data1_left\">" . $DriveBasicInfo['model'] . "</td>";
							print "    <td class=\"field_data1_left\">" . $DriveBasicInfo['type'] . "</td>";
							print "    <td class=\"field_data1_left\">" . $DriveBasicInfo['capacity'] . "</td>";
							print "    <td class=\"field_data1_left\">";
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
							print "    <td class=\"field_data1_left\"></td>";
							print "  </tr>";
						}
					}
				}
			}
			
			print "  </table>";
			print "</form>";
		}// if($b_error == true)
	}// if($UnitIdList !== FALSE)
	
	// ��ʾ���õĴ����б�
//	print "<div id=\"create_unit_div\" class=\"hidden\">";
//	print "<span style=\"display:block; line-height:30px; text-align:right; padding-right:10px; background:#f1f1f1;\">";
//	print "<a class=\"general_link\" onclick=\"POPUP.cw('create_unit_div');\">{$close_str[$lang]}</a></span>";
//	print "<form id=\"create_unit_form\" name=\"create_unit_form\" action=\"maintenance_target.php\" method=\"post\">";
//	print "  <table width=\"100%\" border=\"0\" cellpadding=\"6\" align=\"center\">";
//	print "  <tr>";
//	print "    <td class=\"title\" colspan=\"7\">" . $drives_selected_str[$lang] . "</td>";
//	print "  </tr>";	
//	print "  </table>";
//	print "</form>";
//	print "</div>";

	print "<form id=\"drive_list_form\" name=\"drive_list_form\" action=\"maintenance_target.php?sl_c_id={$controller_selected['id']}\" method=\"post\">";
	print "  <input type=\"hidden\" name=\"drives_id_selected_h\" id=\"drives_id_selected_h\" value=\"\">";
	print "  <table width=\"100%\" border=\"0\" cellpadding=\"6\" align=\"center\">";
	print "  <tr>";
	print "    <td class=\"title\" colspan=\"7\">" . $available_drives_str[$lang] . "</td>";
	print "  </tr>";
	$td_class = "field_data1";
	$Drive_Id_List = $drive_obj->GetDriveIdList($controller_selected['id']);
	$has_items = FALSE;
	if($Drive_Id_List === FALSE)
	{
		$message = $drive_obj->GetLastErrorInfo();
	}
	else
	{
		$td_class = "field_data1";
		foreach( $Drive_Id_List as $Drive_Id )
		{
			$unit_number = $drive_obj->GetUnitOfDrive($Drive_Id);
			if( $unit_number !== FALSE && $unit_number === "" )
			{
				$DriveBasicInfo = $drive_obj->GetDriveBasicInfo($Drive_Id);
				if($DriveBasicInfo !== FALSE)
				{
					$has_items = TRUE;
					print "  <tr>";
					print "    <td class=\"{$td_class}\">";
					print "    <input type=\"checkbox\" name=\"c_u_drives[]\" onclick=\"drive_selected('{$drives_count_max_str[$lang]}');\" value=\"{$DriveBasicInfo['id']}\" />";
					print "   </td>";
					print "    <td class=\"{$td_class}\">";
					//print "<a class=\"general_link\" href=\"drive_target.php?hl_d_id={$DriveBasicInfo['id']}&sl_c_id={$controller_selected['id']}\">";
					print  $DriveBasicInfo['name'];
					//print "</a>";
					print "</td>";
					print "    <td class=\"{$td_class}\">" . $DriveBasicInfo['slot'] . "</td>";
					print "    <td class=\"{$td_class}\">" . $DriveBasicInfo['model'] . "</td>";
					print "    <td class=\"{$td_class}\">" . $DriveBasicInfo['type'] . "</td>";
					print "    <td class=\"{$td_class}\">" . $DriveBasicInfo['capacity'] . "</td>";
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
					print "</tr>";
					$td_class = ($td_class == "field_data1") ? "field_data2" : "field_data1";
				}
			}
		}
	}
	if( $has_items === FALSE )
	{
		print "  <tr>";
		print "    <td class=\"{$td_class}\">";
		print_msg_block( $no_drives_str[$lang] );
		print "    </td>";
		print "  </tr>";
	}
	else
	{
		print "\n";
		print "  <tr>";
		print "    <td class=\"field_title_left\" colspan=\"7\">";
		print "<p>";
		print "<a class=\"general_link\" onclick=\"select_drives('{$drives_count_max_str[$lang]}');\">{$de_select_all_drives_str[$lang]}";
		print "</a>";
		print "</p>";
		print "<span id=\"create_unit_whole\" style=\"display:none;\">";
		print "<p>";
		print "{$raid_type_str[$lang]}";
		print "<select name=\"raid_type_select\" id=\"raid_type_select\" onChange=\"raid_type_select_fuc();\">";
		print "</select>";
		print "&emsp;";
		print "{$raid_name_str[$lang]}";
		print "<input type=\"text\" name=\"raid_name_text\" value=\"\" />";
		print "&emsp;";
		print "<span id=\"raid_stripe_span\">";
		print "{$stripe_size_str[$lang]}";
		print "<select name=\"raid_stripe_select\" id=\"raid_stripe_select\">";
		print "</select>";
		print "</span>";
		print "&emsp;";
		print "<span id=\"drives_per_subunit_span\">";
		print $drives_per_subunit_str[$lang];
		print "<select name=\"drives_per_subunit_select\" id=\"drives_per_subunit_select\">";
		print "</select>";
		print "</span>";
		print "</p>";
		print "<p>";
		print "<span id=\"write_cache_span\">";
		print "<input type=\"checkbox\" name=\"write_cache_cb\" id=\"write_cache_cb\" value=\"1\" />{$write_cache_str[$lang]}";
		print "</span>";
		print "&emsp;";
		print "<span id=\"auto_verify_span\">";
		print "<input type=\"checkbox\" name=\"auto_verify_cb\" id=\"auto_verify_cb\" value=\"1\" />{$auto_verify_str[$lang]}";
		print "</span>";
		print "&emsp;";
		print "<span id=\"queuing_span\">";
		print "<input type=\"checkbox\" name=\"queuing_cb\" id=\"queuing_cb\" value=\"1\" />{$queuing_str[$lang]}";
		print "</span>";
		print "&emsp;";
		print "<span id=\"ecc_span\">";
		print "<input type=\"checkbox\" name=\"ecc_cb\" id=\"ecc_cb\" value=\"1\" />{$overwrite_ecc_str[$lang]}";
		print "</span>";
		print "</p>";
		print "<p>";
		print "<span id=\"storsave_span\">";
		print "{$storsave_str[$lang]}";
		print "<select name=\"storsave_select\" id=\"storsave_select\">";
		print "</select>";
		print "</span>";
		print "&emsp;";
		print "<span id=\"rrr_span\">";
		print "{$rapid_raid_recovery_str[$lang]}";
		print "<select name=\"rrr_select\" id=\"rrr_select\" onChange=\"rrr_select_fuc('{$rrr_warning_str[$lang]}');\">";
		print "</select>";
		print "</span>";
		print "</p>";
		print "<p>";
		print "<input type=\"button\" value=\"{$create_unit_str[$lang]}\" onclick=\"create_unit('{$invalid_unit_name_str[$lang]}');\">";
		print "</p>";
		print "</span>";
		print "    </td>";
		print "  </tr>";
	}
	print "  </table>";
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
