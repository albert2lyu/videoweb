<?php 
require("./include/authenticated.php");
require_once("./include/function.php");
require_once("./include/LSIMegaRAID.php");
require_once("./include/log.php");

$lang=load_lang();

$index_str=array(
	"序号",
	"Index"
);
$unit_information_str=array(
	"RAID组信息",
	"Unit Information"
);
$select_controller_str=array(
	"选择控制器",
	"Select Controller"
);
$count_of_vd_str=array(
	"创建的RAID组数",
	"Count of RAID"
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
$vd_list_info_str=array(
	"RAID组列表信息",
	"RAID List Information"
);
$no_created_vd_str=array(
        "没有已创建的RAID组",
        "No Available RAID Units"
);
$vd_str=array(
	"RAID组",
	"RAID"
);
$name_str=array(
	"名称",
	"Name"
);
$capacity_str=array(
	"容量",
	"Capacity"
);
$raidtype_str=array(
	"类型",
	"Type"
);
$status_str=array(
	"状态",
	"Status"
);
$create_time_str=array(
	"创建时间",
	"Creation Time"
);
$strip_size_str=array(
        "Strip大小",
        "Strip Size"
);
$locate_vd_str=array(
	"定位",
	"Identify"
);
$is_identify_unit_str=array(
	"是否定位此RAID组?",
	"Locate this RAID?"
);
$locate_vd_tip_str=array(
	"定位此RAID组",
	"Locate this RAID"
);
$show_vd_detail_tip_str=array(
	"查看此RAID组的详细信息",
	"Show RAID Detail Information"
);
$vd_detail_info_str=array(
	"RAID组详细信息",
	"RAID Detail Information"
);
$count_of_drives_str=array(
	"磁盘数目",
	"Count Of Drives"
);
$back_str=array(
	"返回",
	"Back"
);
$drive_list_of_vd_str=array(
	"包含的磁盘列表信息",
	"Drives Information Of RAID"
);
$pd_index_str=array(
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
$pd_temperature_str=array(
        "温度",
        "Temperature"
);
$pd_intf_str=array(
        "接口",
        "Interface"
);
$pd_manu_str=array(
        "厂商",
        "Manufacturer"
);
$pd_status_str=array(
        "状态",
        "Status"
);
$pd_locate_str=array(
        "定位",
        "Identify"
);
$pd_smart_str=array(
        "报警",
        "Alert"
);
$pd_detail_str=array(
        "详细信息",
        "Detail"
);

$number_of_pds_str=array(
        "连接磁盘数",
        "Number of Drives"
);
$no_controller_str=array(
        "无可用控制器",
        "No Controller"
);
$no_pd_linked_str=array(
        "没有磁盘",
        "None Drive"
);
$is_locate_pd_str=array(
        "是否定位此磁盘?",
        "Locate this drive?"
);
$show_detail_str=array(
        "显示磁盘详细信息",
        "Show drive detail"
);
$locate_pd_str=array(
        "定位此磁盘",
        "Locate this drive"
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
$tip_please_wait_str=array(
        "正在处理中，请稍等....",
        "Processing, Please Wait..."
);
?>

<?php 
$objCtlList = new CLSIMegaRAIDList();
$objSelectedCtl = new CLSIMegaRAID(); // 选择的控制器
$listCtrl = array();
$objLog = new Log();
$id_controller_selected = -1; // 选择的控制器ID
$b_have_controller = TRUE;
$id_vd_selected = -1;// 选择的unit ID
$message = "";

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
// 选择控制器
if( isset($_POST['select_controller']) )
{
	$id_controller_selected = intval($_POST['select_controller']);
}

if ( isset($_GET['vid']) && isset($_GET['cid']) )
{
	$id_controller_selected = intval($_GET['cid']);
	$id_vd_selected = intval($_GET['vid']);
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
			$objSelectedCtl = $entry;
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
<script type="text/javascript" language="javascript" src="js/ajax_function.js"></script>
<script type="text/javascript">
function SelectController()
{
    showTip();
    document.getElementById("sel_controller_form").submit();
	return true;
}
function RescanController(c_id)
{
    document.getElementById("id_controller_selected_h").value = c_id;
	showTip();
	return true;	
}

function LocateVD(cid, eid, sid, msg)
{
	if( ! confirm(msg) )
	{
		return false;
	}
	var url = "locateVD.php?cid=" + cid + "&vid=" + vid;
	//alert(url);
	var xmlhttp = null;
	loadDoc(xmlhttp, url, locate_ajax);
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
    <td class="bar_nopanel"><?php print $unit_information_str[$lang]; ?></td>
  </tr>
</table>
<?php 
if($b_have_controller === TRUE)
{
	// 控制器选择
	print "<form id=\"sel_controller_form\" name=\"sel_controller_form\" action=\"unit_target2.php\" method=\"post\">";
	print "  <input type=\"hidden\" name=\"id_controller_selected_h\" id=\"id_controller_selected_h\" value=\"\">";
	print "  <table width=\"100%\" border=\"0\" cellpadding=\"6\" align=\"center\">";
	print "  	<tr>";
	print "  	  <td class=\"field_title\">";
	print $select_controller_str[$lang] . ":";
	print "  	  </td>";
	print "  	  <td class=\"field_data1\">";
	print "  	    <select id=\"select_controller\" name=\"select_controller\" onChange=\"SelectController();\">";

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
	print "  	  <td class=\"field_title\">";
	print $count_of_vd_str[$lang] . ":";
	print "  	  </td>";
	print "  	  <td class=\"field_data1\">";
	print count($objSelectedCtl->VDs);
	print "  	  </td>";

	print "  	  <td class=\"field_data1\">";
	print "			<input type=\"submit\" name=\"rescan_controller_submit\" onclick=\"RescanController('{$objSelectedCtl->ID}');\" value=\"{$rescan_controller_str[$lang]}\" />";
	print "  	  </td>";
	print "  	</tr>";
	print "  </table>";
	print "</form>";
	
	// 已创建的unit基本信息列表显示
	if( $id_vd_selected == -1 )
	{
		$listVdInfo = $objSelectedCtl->VDs;
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// -----已创建的unit基本信息列表显示 BEGIN
		print "<form id=\"vd_list_form\" name=\"vd_list_form\" action=\"unit_target2.php\" method=\"post\">\n";
		print "  <table width=\"100%\" border=\"0\" cellpadding=\"6\" align=\"center\">\n";
		print "  <tr>\n";
		print "    <td class=\"title\" colspan=\"9\">" . $vd_list_info_str[$lang] . "</td>\n";
		print "  </tr>\n";
		
		print "  <tr>\n";
		print "    <td class=\"field_title\">" . $index_str[$lang] ."</td>\n";
		print "    <td class=\"field_title\">" . $vd_str[$lang] ."</td>\n";
		print "    <td class=\"field_title\">" . $name_str[$lang] ."</td>\n";
		print "    <td class=\"field_title\">" . $capacity_str[$lang] ."</td>\n";
		print "    <td class=\"field_title\">" . $raidtype_str[$lang] ."</td>\n";
		print "    <td class=\"field_title\">" . $strip_size_str[$lang] ."</td>\n";
		print "    <td class=\"field_title\">" . $count_of_drives_str[$lang] ."</td>\n";
		print "    <td class=\"field_title\">" . $status_str[$lang] ."</td>\n";
		print "    <td class=\"field_title\">" . $locate_vd_str[$lang] ."</td>\n";
		print "  </tr>\n";
		
		$td_class = "field_data1";
		$drive_index = 1;
		foreach( $listVdInfo as $vdInfo )
		{
			print "  <tr>";
			print "    <td class=\"{$td_class}\">{$drive_index}</td>\n";
			
			$href = "unit_target2.php?vid=" . $vdInfo['VD'] . "&cid=" . $objSelectedCtl->ID;
			print "    <td class=\"{$td_class}\">";
			print "<a title=\"{$show_vd_detail_tip_str[$lang]}\" class=\"general_link\" href=\"{$href}\">";
			print $vdInfo['VD'];
			print "    </a></td>\n";
			
			print "    <td class=\"{$td_class}\">";
			print "<a title=\"{$show_vd_detail_tip_str[$lang]}\" class=\"general_link\" href=\"{$href}\">";
			print $vdInfo['Name'];
			print "    </a></td>\n";
			
			print "    <td class=\"{$td_class}\">{$vdInfo['Size']}</td>\n";
			print "    <td class=\"{$td_class}\">{$vdInfo['TYPE']}</td>\n";
			print "    <td class=\"{$td_class}\">{$vdInfo['Strip']}</td>\n";
			$pdCount = count($vdInfo['PDs']);
			print "    <td class=\"{$td_class}\">{$pdCount}</td>\n";
			print "    <td class=\"{$td_class}\">";
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
			print "    </td>\n";
			
			print "    <td class=\"{$td_class}\">";
			print "<img style=\"cursor:crosshair\" title=\"{$locate_vd_tip_str[$lang]}\" 
					src=\"images/unit_identify_icon.gif\" 
					onclick=\"LocateVD('{$objSelectedCtl->ID}', {$vdInfo['VD']}, '{$is_identify_unit_str[$lang]}');\">
				  </img>\n";
			print "    </td>\n";
			
			print "    </tr>\n";
			$td_class = ($td_class == "field_data1") ? "field_data2" : "field_data1";
			$drive_index++;
		}
		
		print "  </table>\n";
		print "</form>\n";
		
		if(count($listVdInfo) == 0)
		{
		    print_msg_block( $no_created_vd_str[$lang] );
		}
		
// -----已创建的unit基本信息列表显示 END
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	}// if( $id_vd_selected == -1 )

	// 显示选择的UNIT的详细信息
	else
	{
	    
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// -----显示选择的UNIT的详细信息 BEGIN
		// 返回按钮
  		print "<table><tr><td>\n";
		print "  <input type=\"button\" onClick=\"window.history.back();\" value=\"{$back_str[$lang]}\">\n";
		print "</td></tr></table>\n";
		//print "<h1>CTL:". $id_controller_selected . ", VD:" . $id_vd_selected . "</h1>";

		$VdInfo_Sel = $objSelectedCtl->GetVdInfoByID($id_vd_selected);
		
		if($VdInfo_Sel === FALSE)
		{
			$message = $objSelectedCtl->GetLastErrorInfo();
		}
		else
		{
			// 详细信息
			print "<table width=\"100%\" border=\"0\" cellpadding=\"6\" align=\"center\">\n";
			print "  <tr>\n";
			print "    <td class=\"title_left\" colspan=\"2\">";
			print $vd_detail_info_str[$lang] . " - " . $vd_str[$lang] . " " . $VdInfo_Sel['VD'];
			print "    </td>\n";
			print "  </tr>\n";
			print "  <tr>\n";
			print "    <td class=\"field_title_left\">{$status_str[$lang]}</td>\n";
			print "    <td class=\"field_data1_left\">";
			if( $VdInfo_Sel['State'] == "Optl")
			{
				print "<font class=\"statusOK\">";
				print $VdInfo_Sel['State'];
				print "</font>";
			}
			else
			{
			    print "<font class=\"statusOther\">";
				print $VdInfo_Sel['State'];
				print "</font>";
			}
			print "    </td>\n";
			print "  </tr>\n";
			print "  <tr>\n";
			print "    <td class=\"field_title_left\">{$name_str[$lang]}</td>\n";
			print "    <td class=\"field_data2_left\">{$VdInfo_Sel['Name']}</td>\n";
			print "  </tr>\n";
			print "  <tr>\n";
			print "    <td class=\"field_title_left\">{$raidtype_str[$lang]}</td>\n";
			print "    <td class=\"field_data1_left\">{$VdInfo_Sel['TYPE']}</td>\n";
			print "  </tr>";
			print "  <tr>";
			print "    <td class=\"field_title_left\">{$capacity_str[$lang]}</td>\n";
			print "    <td class=\"field_data2_left\">{$VdInfo_Sel['Size']}</td>\n";
			print "  </tr>\n";
			print "  <tr>\n";
			print "    <td class=\"field_title_left\">{$strip_size_str[$lang]}</td>\n";
			print "    <td class=\"field_data1_left\">{$VdInfo_Sel['Strip']}</td>\n";
			print "  </tr>\n";
			print "  <tr>\n";
			print "    <td class=\"field_title_left\">{$io_policy_str[$lang]}</td>\n";
			print "    <td class=\"field_data2_left\">{$VdInfo_Sel["io"]["Text"]}</td>\n";
			print "  </tr>\n";
			print "  <tr>\n";
			print "    <td class=\"field_title_left\">{$read_policy_str[$lang]}</td>\n";
			print "    <td class=\"field_data1_left\">{$VdInfo_Sel["rdcache"]["Text"]}</td>\n";
			print "  </tr>\n";
			print "  <tr>\n";
			print "    <td class=\"field_title_left\">{$write_policy_str[$lang]}</td>";
			print "    <td class=\"field_data2_left\">{$VdInfo_Sel["wrcache"]["Text"]}</td>";
			print "  </tr>\n";
			print "  <tr>\n";
			print "    <td class=\"field_title_left\">{$create_time_str[$lang]}</td>\n";
			$creationTime = $VdInfo_Sel['Cdate'] . " " . $VdInfo_Sel['Ctime'];
			print "    <td class=\"field_data1_left\">{$creationTime}</td>\n";
			print "  </tr>\n";
			print "  <tr>\n";
			print "    <td class=\"field_title_left\">{$count_of_drives_str[$lang]}</td>\n";
			$pdCountOfVD = count($VdInfo_Sel["PDs"]);
			print "    <td class=\"field_data2_left\">{$pdCountOfVD}</td>\n";
			print "  </tr>\n";
			print "</table>\n";
		
			// RAID组所含磁盘列表
			print "<table width=\"100%\" border=\"0\" cellpadding=\"4\" align=\"center\">\n";
			print "  <tr>\n";
			print "    <td class=\"title_left\" colspan=\"10\">\n";
			print $drive_list_of_vd_str[$lang] . " - " . $vd_str[$lang] . " " . $VdInfo_Sel['VD'];
			print "    </td>\n";
			print "  </tr>\n";
			print "  <tr>\n";
			print "    <td class=\"field_title\">" . $index_str[$lang] ."</td>\n";
    		print "    <td class=\"field_title\">" . $pd_enc_str[$lang] ."</td>\n";
    		print "    <td class=\"field_title\">" . $pd_slot_str[$lang] ."</td>\n";
    		print "    <td class=\"field_title\">" . $pd_model_str[$lang] ."</td>\n";
    		print "    <td class=\"field_title\">" . $pd_capacity_str[$lang] ."</td>\n";
    		print "    <td class=\"field_title\">" . $pd_intf_str[$lang] ."</td>\n";
    		print "    <td class=\"field_title\">" . $pd_smart_str[$lang] ."</td>\n";
    		print "    <td class=\"field_title\">" . $pd_temperature_str[$lang] ."</td>\n";
    		print "    <td class=\"field_title\">" . $pd_status_str[$lang] ."</td>\n";
    		print "    <td class=\"field_title\">" . $pd_locate_str[$lang] ."</td>\n";
			print "  </tr>\n";
			$listPdInfoOfVd = $VdInfo_Sel["PDs"];
			$index_pd = 1;
			$td_class = "field_data1";
			foreach($listPdInfoOfVd as $pdInfo)
			{
			    print "  <tr>";
			    print "    <td class=\"{$td_class}\">{$index_pd}</td>\n";
			    print "    <td class=\"{$td_class}\">{$pdInfo["EID"]}</td>\n";
			    print "    <td class=\"{$td_class}\">{$pdInfo["Slot"]}</td>\n";
			    $model = $pdInfo["Manufacturer"] . ":" .  $pdInfo["Model"];
			    print "    <td class=\"{$td_class}\">{$model}</td>\n";
			    print "    <td class=\"{$td_class}\">{$pdInfo["Size"]}</td>\n";
			    $intf = $pdInfo["Intf"];// . ":" . $pdInfo["Link Speed"];
			    print "    <td class=\"{$td_class}\">{$intf}</td>\n";
			    print "    <td class=\"{$td_class}\">{$pdInfo["S.M.A.R.T alert flagged by drive"]}</td>";
			    print "    <td class=\"{$td_class}\">{$pdInfo["Temperature"]}</td>\n";
			    print "    <td class=\"{$td_class}\">";
			    $status = $pdInfo["State"];
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
			    print "</td>\n";
			    print "    <td class=\"{$td_class}\">";
			    print "<img style=\"cursor:pointer\" title=\"{$locate_pd_str[$lang]}\" src=\"images/drive_identify_icon.gif\" onclick=\"LocatePD('{$pdInfo['Ctl']}', '{$pdInfo['EID']}', '{$pdInfo['Slot']}', '{$is_locate_pd_str[$lang]}');\"></img>\n";
			    print "    </td>\n";
			    //print "    <td class=\"{$td_class}\">";
			    //print "<img style=\"cursor:pointer\" title=\"{$show_detail_str[$lang]}\" src=\"images/drive_detail_icon.gif\" onclick=\"GetPdDetail('{$controller_selected['name']}', '{$pdInfo['id']}');\"></img>";
			    //print "    </td>";
			    print "  </tr>\n";
			    $td_class = ($td_class == "field_data1") ? "field_data2" : "field_data1";
			    $index_pd++;
			}
			print "</table>\n";
		}
        
// -----显示选择的UNIT的详细信息 END
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
