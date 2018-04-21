<?php 
require("./include/authenticated.php");
require_once("./include/function.php");
require_once("./include/log.php");
require_once("./include/LSIMegaRAID.php");


$lang=load_lang();

$vd_maintenance_str=array(
	"RAID组管理",
	"Unit Maintenance"
);
$no_created_vd_str=array(
        "没有已创建的RAID组",
        "No Available RAID Units"
);
$select_controller_str=array(
	"选择控制器",
	"Select Controller"
);
$no_controller_str=array(
	"无可用控制器",
	"No Controller"
);
$rescan_controller_str=array(
	"重新扫描控制器",
	"Rescan Controller"
);
$rescan_controller_ok_str=array(
        "重新扫描控制器成功",
        "Rescan Controller OK"
);
$vd_created_str=array(
	"已创建的RAID组",
	"Unit Created"
);
$pd_str=array(
	"磁盘",
	"drive"
);
$pds_str=array(
	"磁盘",
	"drives"
);
$vd_str=array(
	"RAID组",
	"Unit"
);
$delete_str=array(
	"删除",
	"Delete"
);
$available_pd_str=array(
	"可用的磁盘",
	"Available Drives"
);
$de_select_all_pd_str=array(
	"（取消）选择全部磁盘",
	"(De-)Select All Drives"
);
$create_str=array(
        "创  建",
        "Create"
);
$create_vd_str=array(
	"创建RAID组",
	"Create Unit"
);
$create_hs_str=array(
        "创建热备盘",
        "Add Hotspare"
);
$pd_select_str=array(
	"选择",
	"Select"
);
$create_type_str=array(
	"创建类型",
	"Type"
);
$vd_name_str=array(
	"名称",
	"Name"
);
$strip_size_str=array(
	"条带大小",
	"Strip Size"
);

$io_policy_str=array(
        "I/O策略",
        "I/O Policy"
);
$read_policy_str=array(
        "读取策略",
        "Read Policy"
);
$write_policy_str=array(
        "写入策略",
        "Write Policy"
);
$io_cached_str=array(
        "缓冲I/O",
        "Cached I/O"
);
$io_direct_str=array(
        "直接I/O",
        "Direct I/O"
);
$read_ra_str=array(
        "预读",
        "Read-Ahead"
);
$read_nora_str=array(
        "非预读",
        "No Read-Ahead"
);
$write_wt_str=array(
        "通过写",
        "Write-Through"
);
$write_wb_str=array(
        "回写",
        "Write-Back"
);
$write_awb_str=array(
        "总是回写",
        "Always Write-Back"
);

$show_vd_detail_tip_str=array(
	"查看此RAID组的详细信息",
	"Show Unit's Detail Information"
);
$show_pd_detail_tip_str=array(
        "查看此磁盘的详细信息",
        "Show Drive's Detail Information"
);
$no_pds_str=array(
	"无磁盘",
	"No Drives"
);
$no_useful_pds_str=array(
        "无可用磁盘",
        "No Useful Drives"
);
$not_present_str=array(
	"不存在",
	"Not Present"
);
$pds_per_subunit_str=array(
	"每组磁盘个数",
	"Drives Per Subunit"
);
$pd_count_max_str=array(
	"选择磁盘的个数不能超过32个！",
	"The number of drives selected must not be larger than 32!"
);
$delete_unit_confirm_str=array(
	"删除此RAID组？",
	"Delete this raid unit?"
);
$invalid_unit_name_str=array(
	"无效RAID组名称！\\n有效字符:A-Za-z0-9_-，长度<=15",
	"Invalid Unit Name！\\nValid Character:A-Za-z0-9_-, Length <= 15"
);
$hs_created_str=array(
        "已创建的热备盘",
        "HotSapre Drives Information"
);
$no_hs_str=array(
        "无可用的热备盘",
        "None HotSpare Drvive"
);
$global_hotspare_str=array(
        "全局热备",
        "Global HotSpare"

);
$dedicated_hotspare_str=array(
        "专用热备",
        "Dedicated HotSpare"
);
$index_str=array(
        "序号",
        "Index"
);
$pd_enc_str=array(
        "机箱",
        "Enclosuer"
);
$pd_slot_str=array(
        "插槽",
        "Slot"
);
$pd_model_str=array(
        "型号",
        "Model"
);
$pd_capacity_str=array(
        "容量",
        "Capacity"
);
$pd_intf_str=array(
        "接口",
        "Interface"
);
$pd_smart_str=array(
        "报警",
        "Alert"
);
$pd_hstype_str=array(
        "热备类型",
        "HotSpare Type"
);
$pd_hs_vd_str=array(
        "用于RAID组",
        "For RAID Unit"
);
$pd_status_str=array(
        "状态",
        "Status"
);
$hs_process_str=array(
        "操作",
        "Operation"
);
$delete_hs_confirm_str=array(
        "删除此热备盘？",
        "Delete this Hotspare Drive?"
);
$select_vd_str=array(
        "选择已创建的RAID组",
        "Select Created RAID Unit"
);
$select_vd_tip_str=array(
        "创建专属热备盘，必须勾选至少一个已创建的RAID组！",
        "Should Be Select One or More Created RAID Unit!"
);
$count_pd_selected_str=array(
        "已选择的磁盘数：",
        "Number of Selected Drive: "
);
$create_vd_tip_str=array(
        "开始创建RAID组，点击确认继续",
        "Create RAID Unit, Click OK to Continue"
);
$ok_str=array(
        "操作成功",
        "Config Success"
);
$failed_str=array(
        "设置失败",
        "Config Failed"
);
$tip_please_wait_str=array(
        "正在处理中，请稍等....",
        "Processing, Please Wait..."
);
?>

<?php 
$objCtlFunc = new CLSIMegaRAIDFunc();
$objLog = new Log();
$message = "";
$id_controller_selected = -1; // 选择的控制器ID

if ( isset($_GET['cid']) )
{
    $id_controller_selected = intval($_GET['cid']);
}

// RAID组管理
// 管理类型：1-删除，其他无效
if( isset($_POST['vd_id_selected_h']) && isset($_POST['vd_process_type_h']) )
{
    $vid = intval($_POST['vd_id_selected_h']);
    $vd_process_type = intval($_POST['vd_process_type_h']);
    $ret = "";
    if($vd_process_type == 1) //删除
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

// RAID创建
while( isset($_POST['pd_id_selected_h']) && isset($_POST['dhs_vds_h']))
{
    //var_dump($_POST);
    
    //磁盘列表不能为空
    if(trim($_POST['pd_id_selected_h']) == "")
    {
        break;
    }
    // 解析磁盘列表 格式"控制器ID:enclosureID:SlotID;..."
    $listPds = array();
    /*
     array(
        array(
            "Ctrl"=>0, //控制器ID
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
    
    //获取raid类型
    if( ! isset($_POST['raid_type_select']) )
    {
        $message = $failed_str[$lang];
        break;
    }
    $typeRAID = strtolower(trim($_POST['raid_type_select']));
    if($typeRAID == "hotsparedrive1")// 创建全局热备盘
    {
        foreach($listPds as $pd)
        {
            $tmp = array(); // 全局热备传入空数组（不指定raid组）
            $objCtlFunc->AddHotSpare($pd["Ctl"], $pd["EID"], $pd["Slot"], $tmp, $message);
        }
    }
    else if($typeRAID == "hotsparedrive2") // 创建专属热备盘
    {
        $arrVDs = explode(";", trim($_POST["dhs_vds_h"]));
        foreach($listPds as $pd)
        {
            $objCtlFunc->AddHotSpare($pd["Ctl"], $pd["EID"], $pd["Slot"], $arrVDs, $message);
        }
        
    }
    else // 创建其他raid类型
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
        // 去掉最后一个","
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
    
    
    // 退出循环
    break;
}

// 热备盘管理
// 管理类型：1-删除，其他无效
if( isset($_POST['hs_pdid_selected_h']) && isset($_POST['hs_process_type_h']) )
{
    $szSid = trim($_POST['hs_pdid_selected_h']); // "/c0/e4/s9"
    $hs_process_type = intval($_POST['hs_process_type_h']);
    $ret = "";
    if($hs_process_type == 1) //删除
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
$objSelectedCtl = new CLSIMegaRAID(); // 选择的控制器
$listCtrl = array();
$b_have_controller = TRUE;

// 选择控制器
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
// 重扫描控制器
if( isset($_POST['rescan_controller_submit']) )
{
    $message = $rescan_controller_ok_str[$lang];
	$id_controller_selected = intval($_POST['id_controller_selected_h']);
}

// 获取选择的控制器对象
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

// 判断当前创建的raid环境中，是否有必要显示创建“热备”选项
$bShowHSSelect = 0; // 0不显示，1-显示
$listVdCreated = $objSelectedCtl->VDs;
foreach($listVdCreated as $vd)
{
    if($vd["TYPE"] == "RAID0") // raid0不支持热备
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
    // 首先判断是否已经全部选择
	for (i = 0; i < document.getElementsByName("c_u_drives[]").length; i++)
	{
		if( document.getElementsByName("c_u_drives[]")[i].checked == false )
		{
		    selected_all = false;
		    break;
		}
	}
	
	if( selected_all == false ) // 未全选，则选择全部
	{
		select_all_drives();
	}
	else // 取消全选
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
	
	// 控制磁盘选择个数，不超过一个UNIT的最大支持磁盘数32
	if(drives_count_selected > 32)
	{
	    document.getElementById("create_vd_whole").style.display = "none";
		alert(msg);
		return false;
	}
	// 控制创建区域显示与否
	var retval = 0;
	if(drives_count_selected > 0)
	{
	    document.getElementById("create_vd_whole").style.display = "block";
		// 控制raid类型选择显示
		ctl_raidtype_select_show(drives_count_selected);
		// 根据生成的raid类型显示属性区域，等同于触发raid类型选择控件的onselect事件
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
    // 选中的磁盘不能超过32
	if(drives_count == 0 || drives_count > 32)
	{
		return false;
	}
	var newOption = new Option('','');
	var saveObject=document.getElementById("raid_type_select");
	// 删除原有的所有元素
	saveObject.innerHTML = "";
	// 根据条件动态生成元素
	
	// 任意个数的盘数均支持raid0
	newOption = new Option('RAID 0','raid0');
	saveObject.options.add(newOption);

	// 偶数盘，生效raid1
	if(drives_count%2 == 0)
	{
		newOption = new Option('RAID 1','raid1');
		saveObject.options.add(newOption);
	}

	// 至少3快盘时，生效raid5
	if(drives_count >= 3)
	{
		newOption = new Option('RAID 5','raid5');
		saveObject.options.add(newOption);
	}

	// 至少4块盘时，生效raid6
	if(drives_count >= 4)
	{
		newOption = new Option('RAID 6','raid6');
		saveObject.options.add(newOption);
	}
	
	// 盘数为4的倍数时生效raid10
	if(drives_count%4 == 0)
	{
		newOption = new Option('RAID 10','raid10');
		saveObject.options.add(newOption);
	}

	// 生效raid50:至少6快盘，每组3-32块盘，并且为总组数不超过8
	if(drives_count >= 6)
	{
		for(var i=3; i<=32; i++) // i表示每组磁盘个数
		{
			if((drives_count%i)==0 && // 每组磁盘个数相同
			   (drives_count/i)>=2    // 并且至少2组
			)
			{
			    newOption = new Option('RAID 50','raid50');
				saveObject.options.add(newOption);
				break;
			}
		}
	}

	// 生效raid60：至少8块盘，每组4-32个，并且总组数不超过8
	if(drives_count >= 8)
	{
		for(var i=4; i<=32; i++) // i表示每组磁盘个数
		{
			if((drives_count%i)==0 && // 每组磁盘个数相同
			   (drives_count/i)>=2    // 并且至少2组
			)
			{
			    newOption = new Option('RAID 60','raid60');
				saveObject.options.add(newOption);
				break;
			}
		}
	}

	// 任意盘数支持hotspare
	// hotsparedrive1:全局热备， hotsparedrive2:专用热备
	var bShowHS = parseInt("<?php print $bShowHSSelect;?>"); // 0不显示，1-显示
	if(bShowHS == 1)
	{
    	newOption = new Option('<?php print $global_hotspare_str[$lang];?>','hotsparedrive1');
    	saveObject.options.add(newOption);
    	newOption = new Option('<?php print $dedicated_hotspare_str[$lang];?>','hotsparedrive2');
    	saveObject.options.add(newOption);
	}

	// 设置默认选中项
	// 只有一个磁盘时，默认选中全局热备或者raid0
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
	else if(drives_count == 2) // 两块盘时，默认选中raid0
	{
	    saveObject.value="raid0"; 
	}
	else if(drives_count >= 3 && drives_count < 16) // 3-16块盘时默认选中raid5
	{
	    saveObject.value="raid5";
	}
	else // 等于或者超过16块盘时，默认选中raid6
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

	//仅有当选择raid50/60显示
	var saveObject=document.getElementById("pds_per_subunit_select");
	// 删除原有的所有元素
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
	//raid10：磁盘数除以2
	if(raidtype == "raid10")
	{
	    drives_per_subunit = drives_count/2;
	    newOption = new Option(drives_per_subunit, drives_per_subunit);
		saveObject.options.add(newOption);
	}
	// raid50：至少6块盘，每组3-32块盘，并且为总组数不超过8
	else if(raidtype == "raid50")
	{
		for(var i=3; i<=32; i++) // i表示每组磁盘个数
		{
			if((drives_count%i)==0 && // 先保证每组磁盘个数相同
			   (drives_count/i)>=2 && // 并且至少2组
			   (drives_count/i)<=8    // 并且最多8组
			)
			{
			    drives_per_subunit = i;
			    newOption = new Option(drives_per_subunit, drives_per_subunit);
				saveObject.options.add(newOption);
			}
		}
	}
	// raid60：至少8块盘，每组4-32个，并且总组数不超过8
	else if(raidtype == "raid60")
	{
		for(var i=4; i<=32; i++) // i表示每组磁盘个数
		{
			if((drives_count%i)==0 && // 先保证每组磁盘个数相同
			   (drives_count/i)>=2 && // 并且至少2组
			   (drives_count/i)<=8    // 并且最多8组
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

// 控制raid组属性设置区域显示与否
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

// 专用热备盘，raid组列表显示与否
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
	// 控制每组磁盘个数显示
	ctl_pds_per_subunit_select_show();
	// 控制raid属性区域显示与否
	vd_property_show();
	// 专用热备盘，raid组列表显示与否
	dhs_for_vdlist_show();
	return true;
}

//管理类型：1-删除，其他无效
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

	//勾选的磁盘列表
	var pdlist_str = "";
	var i = 0;
	obj = document.getElementsByName("c_u_drives[]"); // 选择的磁盘
	for (i = 0; i < obj.length; i++)
	{
		if( obj[i].checked == true )
		{
		    pdlist_str += obj[i].value + ";";
		}
	}
	// 去掉最有一个字符---分隔符";"
	pdlist_str = pdlist_str.substring(0, (pdlist_str.length-1));
	document.getElementById("pd_id_selected_h").value = pdlist_str;
	
	if(raidtype == "hotsparedrive1") // 全局热备
	{
		// do nothing
	}
	else if(raidtype == "hotsparedrive2") // 专属热备
	{
		// 获取勾选的RAID组列表
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
		// 如果未勾选，则提示勾选
		if(bChecked == false)
		{
			alert("<?php print $select_vd_tip_str[$lang]; ?>");
			return false;
		}
		// 去掉最有一个字符---分隔符";"
		raidlist_str = raidlist_str.substring(0, (raidlist_str.length-1));
		document.getElementById("dhs_vds_h").value = raidlist_str;
	}
	else // 其他raid类型
	{
		// 检查unit名称是否合法
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
	// 控制器选择
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
	
	// 已创建的unit基本信息列表显示
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
    		
    		// 显示Unit的包含的磁盘列表信息
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

	// 已创建的热备盘信息
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
    	    if($hsInfo["HSType"] ==1) // 全局热备
    	    {
    	        print $global_hotspare_str[$lang];
    	    }
    	    else if($hsInfo["HSType"] == 2) // 专用磁盘组热备
    	    {
    	        print $dedicated_hotspare_str[$lang];
    	    }
    	    print "</td>";
    	    print "    <td class=\"{$td_class}\">";
    	    if($hsInfo["HSType"] == 1)// 全局热备
    	    {
    	        print $global_hotspare_str[$lang];
    	    }
    	    else if($hsInfo["HSType"] == 2) // 专用磁盘组热备
    	    {
    	        print $vd_str[$lang] . ": ";
    	        $href = "";
    	        for ($i=0; $i<count($hsInfo["DHS Array"]); $i++)
    	        {
    	            $href = "unit_target2.php?vid={$hsInfo["DHS Array"][$i]}&cid={$objSelectedCtl->ID}";
    	            print "<a title=\"{$show_vd_detail_tip_str[$lang]}\" href=\"{$href}\" class=\"general_link\" style=\"text-decoration: none;\">{$hsInfo["DHS Array"][$i]}</a>";
    	            if(($i+1) != count($hsInfo["DHS Array"])) // 不是最后一个元素，使用空格符分隔
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
	

	// 显示可用的磁盘列表
	$listUPDs = $objSelectedCtl->UPDs;
	if(count($listUPDs) > 0)
	{
    	print "<form id=\"unuse_pd_list_form\" name=\"unuse_pd_list_form\" action=\"maintenance_target2.php?cid={$objSelectedCtl->ID}\" method=\"post\">";
    	print "  <input type=\"hidden\" name=\"pd_id_selected_h\" id=\"pd_id_selected_h\" value=\"\">"; // 保存勾选的磁盘列表
    	print "  <input type=\"hidden\" name=\"dhs_vds_h\" id=\"dhs_vds_h\" value=\"\">";// 保存专属热备勾选的raid组ID
    	
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
    		$chk_value = $updInfo["Ctl"] . ":" . $updInfo["EID"] . ":" . $updInfo["Slot"]; // 控制器ID:enclosureID:SlotID
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
    
    	// 创建磁盘组操作区域
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
		// raid类型
		print "&emsp;{$create_type_str[$lang]}:&nbsp;";
		print "<select name=\"raid_type_select\" id=\"raid_type_select\" onChange=\"raid_type_select_func();\">";
		print "</select>";
		print "&emsp;&emsp;";
		// 每组磁盘数量
		print "<span id=\"drives_per_subunit_span\">";
		print "&emsp;{$pds_per_subunit_str[$lang]}:&nbsp;";
		print "<select name=\"pds_per_subunit_select\" id=\"pds_per_subunit_select\">";
		print "</select>";
		print "</span>"; // drives_per_subunit_span 
		print "</p>";
		
		// 专用热备，勾选制定raid组显示区域
		print "<span id=\"dhs_for_vdlist_span\">";
		print "<p>";
		print "&emsp;{$select_vd_str[$lang]}:&nbsp;";
		$listVdCreated = $objSelectedCtl->VDs;
		foreach($listVdCreated as $vd)
		{
		    if($vd["TYPE"] == "RAID0") // raid0不支持热备
		    {
		        continue;
		    }
		    print "<input type=\"checkbox\" name=\"c_u_vds[]\" value=\"{$vd["VD"]}\" />";
		    print $vd_str[$lang] . $vd["VD"] . " " . $vd["Name"] . " [" . $vd["TYPE"] . "]";
		    print "&emsp;&emsp;";
		}
		print "</p>";
		print "</span>"; // dhs_for_vdlist_span
		
		// raid组属性设置区域
		print "<span id=\"vd_property_span\">";
		print "<p>";
		// raid组名称		
		print "&emsp;{$vd_name_str[$lang]}:&nbsp;";
		print "<input type=\"text\" size=\"18\" id=\"raid_name_text\" name=\"raid_name_text\" value=\"\" maxlength=\"15\"/>";
		print "&emsp;&emsp;";
		// strip 大小
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

<!--等待提示-->
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
