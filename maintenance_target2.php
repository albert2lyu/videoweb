<?php 
require("./include/authenticated.php");
require_once("./include/function.php");
require_once("./include/log.php");
require_once("./include/LSIMegaRAID.php");


$lang=load_lang();

$vd_maintenance_str=array(
	"RAID�����",
	"Unit Maintenance"
);
$no_created_vd_str=array(
        "û���Ѵ�����RAID��",
        "No Available RAID Units"
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
$rescan_controller_ok_str=array(
        "����ɨ��������ɹ�",
        "Rescan Controller OK"
);
$vd_created_str=array(
	"�Ѵ�����RAID��",
	"Unit Created"
);
$pd_str=array(
	"����",
	"drive"
);
$pds_str=array(
	"����",
	"drives"
);
$vd_str=array(
	"RAID��",
	"Unit"
);
$delete_str=array(
	"ɾ��",
	"Delete"
);
$available_pd_str=array(
	"���õĴ���",
	"Available Drives"
);
$de_select_all_pd_str=array(
	"��ȡ����ѡ��ȫ������",
	"(De-)Select All Drives"
);
$create_str=array(
        "��  ��",
        "Create"
);
$create_vd_str=array(
	"����RAID��",
	"Create Unit"
);
$create_hs_str=array(
        "�����ȱ���",
        "Add Hotspare"
);
$pd_select_str=array(
	"ѡ��",
	"Select"
);
$create_type_str=array(
	"��������",
	"Type"
);
$vd_name_str=array(
	"����",
	"Name"
);
$strip_size_str=array(
	"������С",
	"Strip Size"
);

$io_policy_str=array(
        "I/O����",
        "I/O Policy"
);
$read_policy_str=array(
        "��ȡ����",
        "Read Policy"
);
$write_policy_str=array(
        "д�����",
        "Write Policy"
);
$io_cached_str=array(
        "����I/O",
        "Cached I/O"
);
$io_direct_str=array(
        "ֱ��I/O",
        "Direct I/O"
);
$read_ra_str=array(
        "Ԥ��",
        "Read-Ahead"
);
$read_nora_str=array(
        "��Ԥ��",
        "No Read-Ahead"
);
$write_wt_str=array(
        "ͨ��д",
        "Write-Through"
);
$write_wb_str=array(
        "��д",
        "Write-Back"
);
$write_awb_str=array(
        "���ǻ�д",
        "Always Write-Back"
);

$show_vd_detail_tip_str=array(
	"�鿴��RAID�����ϸ��Ϣ",
	"Show Unit's Detail Information"
);
$show_pd_detail_tip_str=array(
        "�鿴�˴��̵���ϸ��Ϣ",
        "Show Drive's Detail Information"
);
$no_pds_str=array(
	"�޴���",
	"No Drives"
);
$no_useful_pds_str=array(
        "�޿��ô���",
        "No Useful Drives"
);
$not_present_str=array(
	"������",
	"Not Present"
);
$pds_per_subunit_str=array(
	"ÿ����̸���",
	"Drives Per Subunit"
);
$pd_count_max_str=array(
	"ѡ����̵ĸ������ܳ���32����",
	"The number of drives selected must not be larger than 32!"
);
$delete_unit_confirm_str=array(
	"ɾ����RAID�飿",
	"Delete this raid unit?"
);
$invalid_unit_name_str=array(
	"��ЧRAID�����ƣ�\\n��Ч�ַ�:A-Za-z0-9_-������<=15",
	"Invalid Unit Name��\\nValid Character:A-Za-z0-9_-, Length <= 15"
);
$hs_created_str=array(
        "�Ѵ������ȱ���",
        "HotSapre Drives Information"
);
$no_hs_str=array(
        "�޿��õ��ȱ���",
        "None HotSpare Drvive"
);
$global_hotspare_str=array(
        "ȫ���ȱ�",
        "Global HotSpare"

);
$dedicated_hotspare_str=array(
        "ר���ȱ�",
        "Dedicated HotSpare"
);
$index_str=array(
        "���",
        "Index"
);
$pd_enc_str=array(
        "����",
        "Enclosuer"
);
$pd_slot_str=array(
        "���",
        "Slot"
);
$pd_model_str=array(
        "�ͺ�",
        "Model"
);
$pd_capacity_str=array(
        "����",
        "Capacity"
);
$pd_intf_str=array(
        "�ӿ�",
        "Interface"
);
$pd_smart_str=array(
        "����",
        "Alert"
);
$pd_hstype_str=array(
        "�ȱ�����",
        "HotSpare Type"
);
$pd_hs_vd_str=array(
        "����RAID��",
        "For RAID Unit"
);
$pd_status_str=array(
        "״̬",
        "Status"
);
$hs_process_str=array(
        "����",
        "Operation"
);
$delete_hs_confirm_str=array(
        "ɾ�����ȱ��̣�",
        "Delete this Hotspare Drive?"
);
$select_vd_str=array(
        "ѡ���Ѵ�����RAID��",
        "Select Created RAID Unit"
);
$select_vd_tip_str=array(
        "����ר���ȱ��̣����빴ѡ����һ���Ѵ�����RAID�飡",
        "Should Be Select One or More Created RAID Unit!"
);
$count_pd_selected_str=array(
        "��ѡ��Ĵ�������",
        "Number of Selected Drive: "
);
$create_vd_tip_str=array(
        "��ʼ����RAID�飬���ȷ�ϼ���",
        "Create RAID Unit, Click OK to Continue"
);
$ok_str=array(
        "�����ɹ�",
        "Config Success"
);
$failed_str=array(
        "����ʧ��",
        "Config Failed"
);
$tip_please_wait_str=array(
        "���ڴ����У����Ե�....",
        "Processing, Please Wait..."
);
?>

<?php 
$objCtlFunc = new CLSIMegaRAIDFunc();
$objLog = new Log();
$message = "";
$id_controller_selected = -1; // ѡ��Ŀ�����ID

if ( isset($_GET['cid']) )
{
    $id_controller_selected = intval($_GET['cid']);
}

// RAID�����
// �������ͣ�1-ɾ����������Ч
if( isset($_POST['vd_id_selected_h']) && isset($_POST['vd_process_type_h']) )
{
    $vid = intval($_POST['vd_id_selected_h']);
    $vd_process_type = intval($_POST['vd_process_type_h']);
    $ret = "";
    if($vd_process_type == 1) //ɾ��
    {
        $ret = $objCtlFunc->DeleteVD($id_controller_selected, $vid, $message);
    }
    
    if($ret === TRUE)
    {
        $message = $ok_str[$lang];
    }
    else if($ret === FALSE)
    {
        $message = $failed_str[$lang] . ": " . $message;
    }
}

// RAID����
while( isset($_POST['pd_id_selected_h']) && isset($_POST['dhs_vds_h']))
{
    //var_dump($_POST);
    
    //�����б���Ϊ��
    if(trim($_POST['pd_id_selected_h']) == "")
    {
        break;
    }
    // ���������б� ��ʽ"������ID:enclosureID:SlotID;..."
    $listPds = array();
    /*
     array(
        array(
            "Ctrl"=>0, //������ID
            "EID"=>4,  //enclosure ID
            "Slot"=>5,     // SlotID
        ),
        ...
     )
     */
    $arrPdsStr = explode(";", trim($_POST['pd_id_selected_h']));
    foreach($arrPdsStr as $entry)
    {
        $arrPD = array();
        $tmpArr = explode(":", trim($entry));
        $arrPD["Ctl"] = intval($tmpArr[0]);
        $arrPD["EID"] = intval($tmpArr[1]);
        $arrPD["Slot"] = intval($tmpArr[2]);
        $listPds[] = $arrPD;
    }
    
    //��ȡraid����
    if( ! isset($_POST['raid_type_select']) )
    {
        $message = $failed_str[$lang];
        break;
    }
    $typeRAID = strtolower(trim($_POST['raid_type_select']));
    if($typeRAID == "hotsparedrive1")// ����ȫ���ȱ���
    {
        foreach($listPds as $pd)
        {
            $tmp = array(); // ȫ���ȱ���������飨��ָ��raid�飩
            $objCtlFunc->AddHotSpare($pd["Ctl"], $pd["EID"], $pd["Slot"], $tmp, $message);
        }
    }
    else if($typeRAID == "hotsparedrive2") // ����ר���ȱ���
    {
        $arrVDs = explode(";", trim($_POST["dhs_vds_h"]));
        foreach($listPds as $pd)
        {
            $objCtlFunc->AddHotSpare($pd["Ctl"], $pd["EID"], $pd["Slot"], $arrVDs, $message);
        }
        
    }
    else // ��������raid����
    {
        $name = trim($_POST["raid_name_text"]);
        $strip = trim($_POST["raid_strip_select"]);
        $io_policy = trim($_POST["io_policy_select"]);
        $write_policy = trim($_POST["write_policy_select"]);
        $read_policy = trim($_POST["read_policy_select"]);
        $pds_per_array = 0;
        if(isset($_POST["pds_per_subunit_select"]))
        {
            $pds_per_array = intval($_POST["pds_per_subunit_select"]);
        }
        $drives = "";
        foreach($listPds as $pd)
        {
            $drives .= $pd["EID"] . ":" . $pd["Slot"] . ",";
        }
        // ȥ�����һ��","
        $drives = substr($drives, 0, strlen($drives)-1);
        $objCtlFunc->AddVD($id_controller_selected, $typeRAID, $name, $drives, 
                            $io_policy, $write_policy, $read_policy, $strip, 
                            $pds_per_array, $message);
    }
    
    if($message == "")
    {
        $message = $ok_str[$lang];
    }
    else
    {
        $message = $failed_str[$lang] . ": " . $message;
    }
    
    
    // �˳�ѭ��
    break;
}

// �ȱ��̹���
// �������ͣ�1-ɾ����������Ч
if( isset($_POST['hs_pdid_selected_h']) && isset($_POST['hs_process_type_h']) )
{
    $szSid = trim($_POST['hs_pdid_selected_h']); // "/c0/e4/s9"
    $hs_process_type = intval($_POST['hs_process_type_h']);
    $ret = "";
    if($hs_process_type == 1) //ɾ��
    {
        $ret = $objCtlFunc->DeleteHotSpareEx($szSid, $message);
    }
    
    if($ret === TRUE)
    {
        $message = $ok_str[$lang];
    }
    else if($ret === FALSE)
    {
        $message = $failed_str[$lang] . ": " . $message;
    }
}


$objCtlList = new CLSIMegaRAIDList();
$objSelectedCtl = new CLSIMegaRAID(); // ѡ��Ŀ�����
$listCtrl = array();
$b_have_controller = TRUE;

// ѡ�������
if( isset($_POST['select_controller']) )
{
    $id_controller_selected = intval($_POST['select_controller']);
}

$listCtrl = $objCtlList->GetCtlList();
if(count($listCtrl) <= 0)
{
	$b_have_controller = FALSE;
}
else
{
	$objSelectedCtl = $listCtrl[0];
	$id_controller_selected = 0;
}
// ��ɨ�������
if( isset($_POST['rescan_controller_submit']) )
{
    $message = $rescan_controller_ok_str[$lang];
	$id_controller_selected = intval($_POST['id_controller_selected_h']);
}

// ��ȡѡ��Ŀ���������
if($b_have_controller !== FALSE)
{
	foreach($listCtrl as $entry)
	{
		if($entry->ID == $id_controller_selected)
		{
			//$objSelectedCtl = $entry;
			break;
		}
	}
}

// �жϵ�ǰ������raid�����У��Ƿ��б�Ҫ��ʾ�������ȱ���ѡ��
$bShowHSSelect = 0; // 0����ʾ��1-��ʾ
$listVdCreated = $objSelectedCtl->VDs;
foreach($listVdCreated as $vd)
{
    if($vd["TYPE"] == "RAID0") // raid0��֧���ȱ�
    {
        continue;
    }
    $bShowHSSelect = 1;
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
<script defer type="text/javascript" src="js/basic.js"></script>
<script type="text/javascript">

function SelectController()
{
    showTip();

    document.getElementById("sel_controller_form").submit();	
	return true;
}
function RescanController(c_name, c_id)
{
    document.getElementById("id_controller_selected_h").value = c_id;
	showTip();
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
    var selected_all = true;
    // �����ж��Ƿ��Ѿ�ȫ��ѡ��
	for (i = 0; i < document.getElementsByName("c_u_drives[]").length; i++)
	{
		if( document.getElementsByName("c_u_drives[]")[i].checked == false )
		{
		    selected_all = false;
		    break;
		}
	}
	
	if( selected_all == false ) // δȫѡ����ѡ��ȫ��
	{
		select_all_drives();
	}
	else // ȡ��ȫѡ
	{
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
	setNumberOfPdSelect(drives_count_selected);
	
	// ���ƴ���ѡ�������������һ��UNIT�����֧�ִ�����32
	if(drives_count_selected > 32)
	{
	    document.getElementById("create_vd_whole").style.display = "none";
		alert(msg);
		return false;
	}
	// ���ƴ���������ʾ���
	var retval = 0;
	if(drives_count_selected > 0)
	{
	    document.getElementById("create_vd_whole").style.display = "block";
		// ����raid����ѡ����ʾ
		ctl_raidtype_select_show(drives_count_selected);
		// �������ɵ�raid������ʾ�������򣬵�ͬ�ڴ���raid����ѡ��ؼ���onselect�¼�
		raid_type_select_func();
	}
	else
	{
		document.getElementById("create_vd_whole").style.display = "none";
	}
	
	return true;
}
function setNumberOfPdSelect(number)
{
    var objLB = document.getElementById("lb_pd_sel_count");
    objLB.innerHTML = number;
    return true;
}

function ctl_raidtype_select_show(drives_count)
{
    // ѡ�еĴ��̲��ܳ���32
	if(drives_count == 0 || drives_count > 32)
	{
		return false;
	}
	var newOption = new Option('','');
	var saveObject=document.getElementById("raid_type_select");
	// ɾ��ԭ�е�����Ԫ��
	saveObject.innerHTML = "";
	// ����������̬����Ԫ��
	
	// ���������������֧��raid0
	newOption = new Option('RAID 0','raid0');
	saveObject.options.add(newOption);

	// ż���̣���Чraid1
	if(drives_count%2 == 0)
	{
		newOption = new Option('RAID 1','raid1');
		saveObject.options.add(newOption);
	}

	// ����3����ʱ����Чraid5
	if(drives_count >= 3)
	{
		newOption = new Option('RAID 5','raid5');
		saveObject.options.add(newOption);
	}

	// ����4����ʱ����Чraid6
	if(drives_count >= 4)
	{
		newOption = new Option('RAID 6','raid6');
		saveObject.options.add(newOption);
	}
	
	// ����Ϊ4�ı���ʱ��Чraid10
	if(drives_count%4 == 0)
	{
		newOption = new Option('RAID 10','raid10');
		saveObject.options.add(newOption);
	}

	// ��Чraid50:����6���̣�ÿ��3-32���̣�����Ϊ������������8
	if(drives_count >= 6)
	{
		for(var i=3; i<=32; i++) // i��ʾÿ����̸���
		{
			if((drives_count%i)==0 && // ÿ����̸�����ͬ
			   (drives_count/i)>=2    // ��������2��
			)
			{
			    newOption = new Option('RAID 50','raid50');
				saveObject.options.add(newOption);
				break;
			}
		}
	}

	// ��Чraid60������8���̣�ÿ��4-32��������������������8
	if(drives_count >= 8)
	{
		for(var i=4; i<=32; i++) // i��ʾÿ����̸���
		{
			if((drives_count%i)==0 && // ÿ����̸�����ͬ
			   (drives_count/i)>=2    // ��������2��
			)
			{
			    newOption = new Option('RAID 60','raid60');
				saveObject.options.add(newOption);
				break;
			}
		}
	}

	// ��������֧��hotspare
	// hotsparedrive1:ȫ���ȱ��� hotsparedrive2:ר���ȱ�
	var bShowHS = parseInt("<?php print $bShowHSSelect;?>"); // 0����ʾ��1-��ʾ
	if(bShowHS == 1)
	{
    	newOption = new Option('<?php print $global_hotspare_str[$lang];?>','hotsparedrive1');
    	saveObject.options.add(newOption);
    	newOption = new Option('<?php print $dedicated_hotspare_str[$lang];?>','hotsparedrive2');
    	saveObject.options.add(newOption);
	}

	// ����Ĭ��ѡ����
	// ֻ��һ������ʱ��Ĭ��ѡ��ȫ���ȱ�����raid0
	if(drives_count == 1)
	{
		if(bShowHS == 1)
		{
			saveObject.value="hotsparedrive1";
		}
		else
		{
		    saveObject.value="raid0";
		}
	}
	else if(drives_count == 2) // ������ʱ��Ĭ��ѡ��raid0
	{
	    saveObject.value="raid0"; 
	}
	else if(drives_count >= 3 && drives_count < 16) // 3-16����ʱĬ��ѡ��raid5
	{
	    saveObject.value="raid5";
	}
	else // ���ڻ��߳���16����ʱ��Ĭ��ѡ��raid6
	{
	    saveObject.value="raid6";
	}
	return true;
}

function ctl_pds_per_subunit_select_show()
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

	//���е�ѡ��raid50/60��ʾ
	var saveObject=document.getElementById("pds_per_subunit_select");
	// ɾ��ԭ�е�����Ԫ��
	saveObject.innerHTML = "";
	
	if(raidtype == "raid10" || raidtype == "raid50" || raidtype == "raid60")
	{
	    document.getElementById("drives_per_subunit_span").style.display = "block";
	}
	else
	{
	    document.getElementById("drives_per_subunit_span").style.display = "none";
	    return true;
	}

	var drives_per_subunit = 0;
	//raid10������������2
	if(raidtype == "raid10")
	{
	    drives_per_subunit = drives_count/2;
	    newOption = new Option(drives_per_subunit, drives_per_subunit);
		saveObject.options.add(newOption);
	}
	// raid50������6���̣�ÿ��3-32���̣�����Ϊ������������8
	else if(raidtype == "raid50")
	{
		for(var i=3; i<=32; i++) // i��ʾÿ����̸���
		{
			if((drives_count%i)==0 && // �ȱ�֤ÿ����̸�����ͬ
			   (drives_count/i)>=2 && // ��������2��
			   (drives_count/i)<=8    // �������8��
			)
			{
			    drives_per_subunit = i;
			    newOption = new Option(drives_per_subunit, drives_per_subunit);
				saveObject.options.add(newOption);
			}
		}
	}
	// raid60������8���̣�ÿ��4-32��������������������8
	else if(raidtype == "raid60")
	{
		for(var i=4; i<=32; i++) // i��ʾÿ����̸���
		{
			if((drives_count%i)==0 && // �ȱ�֤ÿ����̸�����ͬ
			   (drives_count/i)>=2 && // ��������2��
			   (drives_count/i)<=8    // �������8��
			)
			{
			    drives_per_subunit = i;
			    newOption = new Option(drives_per_subunit, drives_per_subunit);
				saveObject.options.add(newOption);
			}
		}
	}
	
	return true;
}

// ����raid����������������ʾ���
function vd_property_show()
{
    var typeObject=document.getElementById("raid_type_select");
	var raidtype = typeObject.value;
	if(raidtype == "hotsparedrive1" || raidtype == "hotsparedrive2")
	{
	    document.getElementById("vd_property_span").style.display = "none";
	}
	else
	{
		document.getElementById("vd_property_span").style.display = "block";
	}
	
    return true;
}

// ר���ȱ��̣�raid���б���ʾ���
function dhs_for_vdlist_show()
{
    var typeObject=document.getElementById("raid_type_select");
	var raidtype = typeObject.value;
	if(raidtype == "hotsparedrive2")
	{
	    document.getElementById("dhs_for_vdlist_span").style.display = "block";
	}
	else
	{
		document.getElementById("dhs_for_vdlist_span").style.display = "none";
	}
	return true;
}

function raid_type_select_func()
{
	// ����ÿ����̸�����ʾ
	ctl_pds_per_subunit_select_show();
	// ����raid����������ʾ���
	vd_property_show();
	// ר���ȱ��̣�raid���б���ʾ���
	dhs_for_vdlist_show();
	return true;
}

//�������ͣ�1-ɾ����������Ч
function DeleteVD(vid, msg)
{
	if( ! confirm(msg) )
	{
		return false;
	}
	document.getElementById("vd_id_selected_h").value = vid;
	document.getElementById("vd_process_type_h").value = 1;

	showTip();
	
	document.getElementById("vd_list_form").submit();
	return true;
}
function DeleteHS(sid, msg)
{
	if( ! confirm(msg) )
	{
		return false;
	}

	document.getElementById("hs_pdid_selected_h").value = sid;
	document.getElementById("hs_process_type_h").value = 1;

	showTip();
	
	document.getElementById("hs_list_form").submit();
	return true;
}
function create_unit(msg1)
{
    var typeObject=document.getElementById("raid_type_select");
	var raidtype = typeObject.value;

	//��ѡ�Ĵ����б�
	var pdlist_str = "";
	var i = 0;
	obj = document.getElementsByName("c_u_drives[]"); // ѡ��Ĵ���
	for (i = 0; i < obj.length; i++)
	{
		if( obj[i].checked == true )
		{
		    pdlist_str += obj[i].value + ";";
		}
	}
	// ȥ������һ���ַ�---�ָ���";"
	pdlist_str = pdlist_str.substring(0, (pdlist_str.length-1));
	document.getElementById("pd_id_selected_h").value = pdlist_str;
	
	if(raidtype == "hotsparedrive1") // ȫ���ȱ�
	{
		// do nothing
	}
	else if(raidtype == "hotsparedrive2") // ר���ȱ�
	{
		// ��ȡ��ѡ��RAID���б�
		var raidlist_str = "";
		var i = 0;
		obj = document.getElementsByName("c_u_vds[]");
		var bChecked = false;
		for (i = 0; i < obj.length; i++)
		{
			if( obj[i].checked == true )
			{
			    raidlist_str += obj[i].value + ";";
			    bChecked = true;
			}
		}
		// ���δ��ѡ������ʾ��ѡ
		if(bChecked == false)
		{
			alert("<?php print $select_vd_tip_str[$lang]; ?>");
			return false;
		}
		// ȥ������һ���ַ�---�ָ���";"
		raidlist_str = raidlist_str.substring(0, (raidlist_str.length-1));
		document.getElementById("dhs_vds_h").value = raidlist_str;
	}
	else // ����raid����
	{
		// ���unit�����Ƿ�Ϸ�
		var objRaidName = document.getElementById("raid_name_text");
		var unit_name = objRaidName.value;
		unit_name = unit_name.Trim();
		objRaidName.value = unit_name;
		if( ! IsUnitNameOk(unit_name) )
		{
			alert("<?php print $invalid_unit_name_str[$lang];?>");
			objRaidName.focus();
			objRaidName.select();
			return false;
		}		
	}

	var tipMsg = "<?php print $create_vd_tip_str[$lang];?>";
	if( !confirm(tipMsg) )
	{
		return false;
	}
	
	showTip();
	document.getElementById("unuse_pd_list_form").submit();
	return true;
}
function showTip()
{
    document.getElementById('div_work').style.display="none";
    document.getElementById('div_waiting').style.display="block";
}
</script>
</head>

<body>
<div id="div_work" style="display:block;">
<table align="center" width="100%">
  <tr>
    <td class="bar_nopanel"><?php print $vd_maintenance_str[$lang]; ?></td>
  </tr>
</table>
<?php 
if($b_have_controller === TRUE)
{
	// ������ѡ��
	print "<form id=\"sel_controller_form\" name=\"sel_controller_form\" action=\"maintenance_target2.php\" method=\"post\">";
	print "  <input type=\"hidden\" name=\"id_controller_selected_h\" id=\"id_controller_selected_h\" value=\"\">";
	print "  <table width=\"100%\" border=\"0\" cellpadding=\"6\" align=\"center\">";
	print "  	<tr>";
	print "  	  <td class=\"field_title\">";
	print $select_controller_str[$lang] . ":";
	print "  	  </td>";
	print "  	  <td class=\"field_data1\">";
	print "  	    <select name=\"select_controller\" onChange=\"SelectController();\">";
	foreach( $listCtrl as $obj )
	{
		if($id_controller_selected == $obj->ID)
		{
			print "<option value=\"{$obj->ID}\" selected>{$obj->INFO['Model']}";
			$objSelectedCtl = $obj;
		}
		else
		{
			print "<option value=\"{$obj->ID}\">{$obj->INFO['Model']}";
		}
	}
	print "  	    </select>";
	print "  	  </td>";

	print "  	  <td class=\"field_data1\">";
	print "			<input type=\"submit\" name=\"rescan_controller_submit\" onclick=\"RescanController('{$objSelectedCtl->ID}');\" value=\"{$rescan_controller_str[$lang]}\" />";
	print "  	  </td>";
	print "  	</tr>";
	print "  </table>";
	print "</form>";
	
	// �Ѵ�����unit������Ϣ�б���ʾ
	$listVdInfo = $objSelectedCtl->VDs;
	if(count($listVdInfo) > 0)
	{
    	print "<form id=\"vd_list_form\" name=\"vd_list_form\" action=\"maintenance_target2.php?cid={$objSelectedCtl->ID}\" method=\"post\">";
    	print "  <input type=\"hidden\" name=\"vd_id_selected_h\" id=\"vd_id_selected_h\" value=\"\">";
    	print "  <input  type=\"hidden\" name=\"vd_process_type_h\" id=\"vd_process_type_h\" value=\"\">";
    	print "  <table width=\"100%\" border=\"0\" cellpadding=\"6\" align=\"center\">";
    	print "  <tr>";
    	print "    <td class=\"title\" colspan=\"7\">" . $vd_created_str[$lang] . "</td>";
    	print "  </tr>";
    	
    	foreach( $listVdInfo as $vdInfo )
    	{
    		print "  <tr>";
    		print "    <td class=\"field_title_left\">";
    		$href = "unit_target2.php?vid=" . $vdInfo['VD'] . "&cid=" . $objSelectedCtl->ID;
    		print "<a title=\"{$show_vd_detail_tip_str[$lang]}\" class=\"general_link\" href=\"{$href}\">";
    		print $vd_str[$lang]. " " . $vdInfo['VD'];
    		print "    </a></td>";
    		print "    <td class=\"field_title_left\">";
    		$pdCountOfVD = count($vdInfo["PDs"]);
    		print $pdCountOfVD;
    		if($pdCountOfVD == 1)
    		{
    			print " " . $pd_str[$lang];
    		}
    		else
    		{
    			print " " . $pds_str[$lang];
    		}
    		print "    </td>";
    		print "    <td class=\"field_title_left\">";
    		print $vdInfo['Name'];
    		print "    </td>";
    		print "    </td>";
    		print "    <td class=\"field_title_left\">";
    		print $vdInfo['TYPE'];
    		print "    </td>";
    		print "    <td class=\"field_title_left\">{$vdInfo['Size']}</td>";				
    		print "    <td class=\"field_title_left\">";
            if( $vdInfo['State'] == "Optl")
            {
            	print "<font class=\"statusOK\">";
            	print $vdInfo['State'];
            	print "</font>";
            }
            else
            {
            	print "<font class=\"statusOther\">";
            	print $vdInfo['State'];
            	print "</font>";
            }
    		print "    </td>";
    		print "    <td class=\"field_title_left\">";
    		print "<input type=\"button\" value=\"{$delete_str[$lang]}\" onclick=\"DeleteVD('{$vdInfo["VD"]}', '{$delete_unit_confirm_str[$lang]}');\"/>";
    		print "    </td>";
    		print "  </tr>";
    		
    		// ��ʾUnit�İ����Ĵ����б���Ϣ
    		$listPdInfoOfVd = $vdInfo["PDs"];
    		foreach( $listPdInfoOfVd as $pdInfo )
    		{
    			print "  <tr>";
    			print "    <td class=\"field_data1_left\"></td>";
    			print "    <td class=\"field_data1_left\">";
    			$href = "drive_target2.php?cid=" . $objSelectedCtl->ID . "&eid=" . $pdInfo["EID"] . "&sid=" . $pdInfo["Slot"];
    			print "<a title=\"{$show_pd_detail_tip_str[$lang]}\" class=\"general_link\" href=\"{$href}\" >";
    			print  $pd_str[$lang] . $pdInfo['EID'] . ":" . $pdInfo["Slot"];
    			print "</a>";
    			print "</td>";
    			print "    <td class=\"field_data1_left\">" . $pdInfo['Manufacturer'] . ": " . $pdInfo['Model'] . "</td>";
    			print "    <td class=\"field_data1_left\">" . $pdInfo['Intf'] . "</td>";
    			print "    <td class=\"field_data1_left\">" . $pdInfo['Size'] . "</td>";
    			print "    <td class=\"field_data1_left\">";
                if( $pdInfo["State"] == "UGood"
                	|| $pdInfo["State"] == "Onln"
                	|| $pdInfo["State"] == "GHS"
                	|| $pdInfo["State"] == "DHS"
                )
                {
                	print "<font class=\"statusOK\">";
                	print $pdInfo["State"];
                	print "</font>";
                }
                else
                {
                	print "<font class=\"statusOther\">";
                	print $pdInfo["State"];
                	print "</font>";
                }
    			print "</td>";
    			print "    <td class=\"field_data1_left\"></td>";
    			print "  </tr>";
    		}
    	}
    	print "  </table>";
    	print "</form>";
	}

	// �Ѵ������ȱ�����Ϣ
	$listHsInfo = $objSelectedCtl->HSs;
	if(count($listHsInfo) > 0)
	{
    	print "<form id=\"hs_list_form\" name=\"hs_list_form\" action=\"maintenance_target2.php?cid={$objSelectedCtl->ID}\" method=\"post\">";
    	print "  <input type=\"hidden\" name=\"hs_pdid_selected_h\" id=\"hs_pdid_selected_h\" value=\"\">";
    	print "  <input  type=\"hidden\" name=\"hs_process_type_h\" id=\"hs_process_type_h\" value=\"\">";
    	print "  <table width=\"100%\" border=\"0\" cellpadding=\"6\" align=\"center\">";
    	print "  <tr>";
    	print "    <td class=\"title\" colspan=\"7\">" . $hs_created_str[$lang] . "</td>";
    	print "  </tr>";
    	
    	print "  <tr>";
    	print "    <td class=\"field_title\">" . $pd_hstype_str[$lang] ."</td>";
    	print "    <td class=\"field_title\">" . $pd_hs_vd_str[$lang] ."</td>";
    	print "    <td class=\"field_title\">" . $pd_str[$lang] ."</td>";
    	print "    <td class=\"field_title\">" . $pd_model_str[$lang] ."</td>";
    	print "    <td class=\"field_title\">" . $pd_capacity_str[$lang] ."</td>";
    	print "    <td class=\"field_title\">" . $pd_status_str[$lang] ."</td>";
    	print "    <td class=\"field_title\">" . $hs_process_str[$lang] ."</td>";
    	print "  </tr>";
    	
    	$td_class = "field_data1";
    	foreach( $listHsInfo as $hsInfo )
    	{
    	    $name = $pd_str[$lang] . $hsInfo["EID"] . ":" . $hsInfo["Slot"];
    	    $model = $hsInfo["Manufacturer"] . ":" .  $hsInfo["Model"];
    	    $capacity = $hsInfo["Size"];
    	    $status = $hsInfo["State"];
    	    
    	    print "  <tr>";
    	    print "    <td class=\"{$td_class}\">";
    	    if($hsInfo["HSType"] ==1) // ȫ���ȱ�
    	    {
    	        print $global_hotspare_str[$lang];
    	    }
    	    else if($hsInfo["HSType"] == 2) // ר�ô������ȱ�
    	    {
    	        print $dedicated_hotspare_str[$lang];
    	    }
    	    print "</td>";
    	    print "    <td class=\"{$td_class}\">";
    	    if($hsInfo["HSType"] == 1)// ȫ���ȱ�
    	    {
    	        print $global_hotspare_str[$lang];
    	    }
    	    else if($hsInfo["HSType"] == 2) // ר�ô������ȱ�
    	    {
    	        print $vd_str[$lang] . ": ";
    	        $href = "";
    	        for ($i=0; $i<count($hsInfo["DHS Array"]); $i++)
    	        {
    	            $href = "unit_target2.php?vid={$hsInfo["DHS Array"][$i]}&cid={$objSelectedCtl->ID}";
    	            print "<a title=\"{$show_vd_detail_tip_str[$lang]}\" href=\"{$href}\" class=\"general_link\" style=\"text-decoration: none;\">{$hsInfo["DHS Array"][$i]}</a>";
    	            if(($i+1) != count($hsInfo["DHS Array"])) // �������һ��Ԫ�أ�ʹ�ÿո���ָ�
    	            {
    	                print " ";
    	            }
    	        }
    	    }
    	    print "</td>";
    	    print "    <td class=\"{$td_class}\">";
    	    $href = "drive_target2.php?cid=" . $objSelectedCtl->ID . "&eid=" . $hsInfo["EID"] . "&sid=" . $hsInfo["Slot"];
    	    print "<a title=\"{$show_pd_detail_tip_str[$lang]}\" class=\"general_link\" href=\"{$href}\" >{$name}</a>";
    	    print "</td>";
    	    print "    <td class=\"{$td_class}\">{$model}</td>";
    	    print "    <td class=\"{$td_class}\">{$capacity}</td>";
    	    print "    <td class=\"{$td_class}\">";
    	    if( $status == "UGood"
    	            || $status == "Onln"
    	            || $status == "GHS"
    	            || $status == "DHS"
    	    )
    	    {
    	        print "<font class=\"statusOK\">";
    	        print $status;
    	        print "</font>";
    	    }
    	    else
    	    {
    	        print "<font class=\"statusOther\">";
    	        print $status;
    	        print "</font>";
    	    }
    	    print "</td>";
    	    print "    <td class=\"{$td_class}\">";
    	    print "<input type=\"button\" value=\"{$delete_str[$lang]}\" onclick=\"DeleteHS('{$hsInfo["StrID"]}', '{$delete_hs_confirm_str[$lang]}');\"/>";
    	    print "</td>";
    	    
    	    print "  </tr>";
    	    
    	    $td_class = ($td_class == "field_data1") ? "field_data2" : "field_data1";
    	}
    	print "  </table>";
    	print "</form>";
	}
	

	// ��ʾ���õĴ����б�
	$listUPDs = $objSelectedCtl->UPDs;
	if(count($listUPDs) > 0)
	{
    	print "<form id=\"unuse_pd_list_form\" name=\"unuse_pd_list_form\" action=\"maintenance_target2.php?cid={$objSelectedCtl->ID}\" method=\"post\">";
    	print "  <input type=\"hidden\" name=\"pd_id_selected_h\" id=\"pd_id_selected_h\" value=\"\">"; // ���湴ѡ�Ĵ����б�
    	print "  <input type=\"hidden\" name=\"dhs_vds_h\" id=\"dhs_vds_h\" value=\"\">";// ����ר���ȱ���ѡ��raid��ID
    	
    	print "  <table width=\"100%\" border=\"0\" cellpadding=\"6\" align=\"center\">";
    	print "  <tr>";
    	print "    <td class=\"title\" colspan=\"7\">" . $available_pd_str[$lang] . "</td>";
    	print "  </tr>";
    	
    	print "  <tr>";
    	print "    <td class=\"field_title\">" . $pd_select_str[$lang] ."</td>";
    	print "    <td class=\"field_title\">" . $pd_str[$lang] ."</td>";
    	print "    <td class=\"field_title\">" . $pd_model_str[$lang] ."</td>";
    	print "    <td class=\"field_title\">" . $pd_capacity_str[$lang] ."</td>";
    	print "    <td class=\"field_title\">" . $pd_intf_str[$lang] ."</td>";
    	print "    <td class=\"field_title\">" . $pd_smart_str[$lang] ."</td>";
    	print "    <td class=\"field_title\">" . $pd_status_str[$lang] ."</td>";
    	print "  </tr>";
    	
    	$td_class = "field_data1";
    	foreach( $listUPDs as $updInfo )
    	{
    	    $name = $pd_str[$lang] . $updInfo["EID"] . ":" . $updInfo["Slot"];
    	    $model = $updInfo["Manufacturer"] . ":" .  $updInfo["Model"];
    	    $capacity = $updInfo["Size"];
    	    $intf = $updInfo["Intf"];// . ":" . $updInfo["Link Speed"];
    	    $smart = $updInfo["S.M.A.R.T alert flagged by drive"];
    	    $status = $updInfo["State"];
    	    
    		print "  <tr>";
    		print "    <td class=\"{$td_class}\">";
    		$chk_value = $updInfo["Ctl"] . ":" . $updInfo["EID"] . ":" . $updInfo["Slot"]; // ������ID:enclosureID:SlotID
    		print "    <input type=\"checkbox\" name=\"c_u_drives[]\" onclick=\"drive_selected('{$pd_count_max_str[$lang]}');\" value=\"{$chk_value}\" />";
    		print "    </td>";
            print "    <td class=\"{$td_class}\">";
    	    $href = "drive_target2.php?cid=" . $objSelectedCtl->ID . "&eid=" . $updInfo["EID"] . "&sid=" . $updInfo["Slot"];
    	    print "      <a title=\"{$show_pd_detail_tip_str[$lang]}\" class=\"general_link\" href=\"{$href}\" >{$name}</a>";
    	    print "    </td>";
    		print "    <td class=\"{$td_class}\">" . $model . "</td>";
    		print "    <td class=\"{$td_class}\">" . $capacity . "</td>";
    		print "    <td class=\"{$td_class}\">" . $intf . "</td>";
    		print "    <td class=\"{$td_class}\">" . $smart . "</td>";
    		print "    <td class=\"{$td_class}\">";
            if( $status == "UGood"
    	            || $status == "Onln"
    	            || $status == "GHS"
    	            || $status == "DHS"
    	    )
    	    {
    	        print "<font class=\"statusOK\">";
    	        print $status;
    	        print "</font>";
    	    }
    	    else
    	    {
    	        print "<font class=\"statusOther\">";
    	        print $status;
    	        print "</font>";
    	    }
    	    print "    </td>";
    	    print "  </tr>";
    	    
    		$td_class = ($td_class == "field_data1") ? "field_data2" : "field_data1";
    	}
    
    	// �����������������
		print "<br>";
		print "  <tr>";
		print "    <td class=\"field_title_left\" colspan=\"7\">";
		
		print "<p>";
		print "&emsp;<a class=\"general_link\" onclick=\"select_drives('{$pd_count_max_str[$lang]}');\">{$de_select_all_pd_str[$lang]}";
		print "</a>";
		print "&emsp;&emsp;";
		print "{$count_pd_selected_str[$lang]}<label id=\"lb_pd_sel_count\" name=\"lb_pd_sel_count\">0</label>";
		print "</p>";
		
		print "<span id=\"create_vd_whole\" style=\"display:none;\">";
		
		print "<p>";
		// raid����
		print "&emsp;{$create_type_str[$lang]}:&nbsp;";
		print "<select name=\"raid_type_select\" id=\"raid_type_select\" onChange=\"raid_type_select_func();\">";
		print "</select>";
		print "&emsp;&emsp;";
		// ÿ���������
		print "<span id=\"drives_per_subunit_span\">";
		print "&emsp;{$pds_per_subunit_str[$lang]}:&nbsp;";
		print "<select name=\"pds_per_subunit_select\" id=\"pds_per_subunit_select\">";
		print "</select>";
		print "</span>"; // drives_per_subunit_span 
		print "</p>";
		
		// ר���ȱ�����ѡ�ƶ�raid����ʾ����
		print "<span id=\"dhs_for_vdlist_span\">";
		print "<p>";
		print "&emsp;{$select_vd_str[$lang]}:&nbsp;";
		$listVdCreated = $objSelectedCtl->VDs;
		foreach($listVdCreated as $vd)
		{
		    if($vd["TYPE"] == "RAID0") // raid0��֧���ȱ�
		    {
		        continue;
		    }
		    print "<input type=\"checkbox\" name=\"c_u_vds[]\" value=\"{$vd["VD"]}\" />";
		    print $vd_str[$lang] . $vd["VD"] . " " . $vd["Name"] . " [" . $vd["TYPE"] . "]";
		    print "&emsp;&emsp;";
		}
		print "</p>";
		print "</span>"; // dhs_for_vdlist_span
		
		// raid��������������
		print "<span id=\"vd_property_span\">";
		print "<p>";
		// raid������		
		print "&emsp;{$vd_name_str[$lang]}:&nbsp;";
		print "<input type=\"text\" size=\"18\" id=\"raid_name_text\" name=\"raid_name_text\" value=\"\" maxlength=\"15\"/>";
		print "&emsp;&emsp;";
		// strip ��С
		print "{$strip_size_str[$lang]}:&nbsp;";
		print "<select name=\"raid_strip_select\" id=\"raid_strip_select\">";
		// 8, 16, 32, 64, 128, 256, 512, 1024.
		print "  <option value=\"8\"/>8 KB";
		print "  <option value=\"16\"/>16 KB";
		print "  <option value=\"32\"/>32 KB";
		print "  <option value=\"64\"/>64 KB";
		print "  <option value=\"128\"/>128 KB";
		print "  <option value=\"256\" selected/>256 KB";		
		print "  <option value=\"512\"/>512 KB";
		print "  <option value=\"1024\"/>1024 KB";
		print "</select>";
		print "</p>";
		
		print "<p>";
		print "&emsp;{$io_policy_str[$lang]}:&nbsp;";
		print "<select name=\"io_policy_select\" id=\"io_policy_select\">";
		print "  <option value=\"direct\"/>{$io_direct_str[$lang]}";
		print "  <option value=\"cached\"/>{$io_cached_str[$lang]}";
		print "</select>";
		print "&emsp;&emsp;";
		print "{$write_policy_str[$lang]}:&nbsp;";
		print "<select name=\"write_policy_select\" id=\"write_policy_select\">";
		print "  <option value=\"wt\"/>{$write_wt_str[$lang]}";
		//print "  <option value=\"wb\"/>{$write_wb_str[$lang]}";
		print "  <option value=\"awb\"/>{$write_awb_str[$lang]}";
		print "</select>";
		print "&emsp;&emsp;";
		print "{$read_policy_str[$lang]}:&nbsp;";
		print "<select name=\"read_policy_select\" id=\"read_policy_select\">";
		print "  <option value=\"ra\"/>{$read_ra_str[$lang]}";
		print "  <option value=\"nora\"/>{$read_nora_str[$lang]}";
		print "</select>";
		print "</p>";
		print "</span>"; // vd_property_span
		
		print "<p>";
		print "&emsp;<input type=\"button\" id=\"create_submit\" name=\"create_submit\" style=\"width: 62px; height: 24px;\" value=\"{$create_str[$lang]}\" onclick=\"create_unit();\">";
		print "</p>";
		
		print "</span>"; // create_vd_whole
		print "    </td>";
		
		//print "    <td class=\"field_title_left\" colspan=\"3\">";
		//print "    </td>";
		
		print "  </tr>";
    		
    	print "  </table>";
    	print "</form>";
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

</div>

<!--�ȴ���ʾ-->
<div id="div_waiting" style="display:none;">
<table width="100%" height="100%" bgcolor="#FEF9E9" >
	<tr>
		<td align="center">
			<img src="images/loading_01.gif">
			<br><br>
			<?php print $tip_please_wait_str[$lang];?>
		</td>
	</tr>
</table>
</div>

</body>
</html>
