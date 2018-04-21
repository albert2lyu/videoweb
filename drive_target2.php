<?php
require("./include/authenticated.php");
require_once("./include/function.php");
require_once("./include/LSIMegaRAID.php");
require_once("./include/log.php");

$lang=load_lang();

$pd_info_str=array(
	"磁盘信息",
	"Drive Information"
);
$vd_str=array(
        "RAID组",
        "Unit"
);
$pd_list_str=array(
	"磁盘列表",
	"Drive List"
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
$pd_vd_str=array(
	"所属RAID组",
	"RAID"
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
$select_controller_str=array(
	"选择控制器",
	"Select Controller"
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
$show_vd_detail_tip_str=array(
        "查看此RAID组的详细信息",
        "Show Unit's Detail Information"
);
$locate_pd_str=array(
	"定位此磁盘",
	"Locate this drive"
);
$rescan_controller_str=array(
	"重新扫描控制器",
	"Rescan Controller"
);
$rescan_controller_ok_str=array(
    "重新扫描控制器成功",
    "Rescan Controller OK"
);
$global_hotspare_str=array(
        "全局热备",
        "Global HotSpare"
        
);
$dedicated_hotspare_str=array(
        "专用热备",
        "Dedicated HotSpare"
);
$pd_operation_str=array(
        "操作",
        "Operation"
);
$pd_set_good_str=array(
        "设置磁盘状态为可用",
        "Set Drive State 'good'"
);
$pd_set_good_tip_str=array(
        "是否设置磁盘状态为可用",
        "To Set Drive State 'good'?"
);
$pd_start_init_str=array(
        "开始初始化磁盘",
        "Start The Initialization of Drive"
);
$pd_start_init_tip_str=array(
        "是否开始初始化磁盘，将清空所有数据！",
        "Start The Initialization of Drive? All Data Will Be Earsed!"
);
$pd_show_init_str=array(
        "磁盘正在初始化，进度：",
        "Drive Initializing, Progress: "
);
$pd_stop_init_str=array(
        "停止初始化磁盘",
        "Stop The Initialization of Drive"
);
$pd_stop_init_tip_str=array(
        "是否停止初始化磁盘",
        "Stop The Initialization of Drive？"
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

// 磁盘操作处理
while(isset($_POST['pd_operation_h']) && isset($_POST['pds_selected_h']))
{
    // 磁盘操作类型: 1-定位磁盘，2-设置磁盘状态为good，3-开始初始化，4-停止初始化
    $op_type = intval(trim($_POST['pd_operation_h']));
    $pds_selected = trim($_POST['pds_selected_h']);
    
    $pdStrList = explode(";", $pds_selected);
    $pdInfoList = array();
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
    foreach($pdStrList as $pdStr)
    {
        $pdInfo = explode(":", $pdStr);
        $pd = array();
        $pd["Ctl"] = intval(trim($pdInfo[0]));
        $pd["EID"] = intval(trim($pdInfo[1]));
        $pd["Slot"] = intval(trim($pdInfo[2]));
        
        $pdInfoList[] = $pd;
    }
    
    switch($op_type)
    {
        case 1: // 定位磁盘
            
            break;
        case 2: // 设置磁盘状态为good
            foreach($pdInfoList as $pdInfo)
            {
                $objCtlFunc->SetPdUbadToUgood($pd['Ctl'], $pd['EID'], $pd['Slot'], TRUE, $message);
            }
            break;
        case 3: // 开始初始化磁盘
            foreach($pdInfoList as $pdInfo)
            {
                $objCtlFunc->StartPdInitialization($pd['Ctl'], $pd['EID'], $pd['Slot'], $message);
            }
            break;
        case 4: // 停止磁盘初始化
            foreach($pdInfoList as $pdInfo)
            {
                $objCtlFunc->StopPdInitialization($pd['Ctl'], $pd['EID'], $pd['Slot'], $message);
            }
            break;
        default:
            $message = "Not support this operation: " . $op_type;
            break;
    }
    
    if($message == "")
    {
        $message = $ok_str[$lang];
    }
    else
    {
        $message = $failed_str[$lang] . ": " . $message;
    }
    
    break;
}
$objCtlList = new CLSIMegaRAIDList();
$id_controller_selected = -1; // 选择的控制器ID
$objSelectedCtl = new CLSIMegaRAID(); // 选择的控制器
$listCtrl = array();
$b_have_controller = TRUE;
$id_enc_highlight = -1;
$id_pd_highlight = -1;

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
// 重新选择了控制器
if( isset($_POST['select_controller']) )
{
	$id_controller_selected = intval($_POST['select_controller']);
}

if( isset($_GET['cid']) )
{
	$id_controller_selected = intval($_GET['cid']);
}

// 高亮显示的磁盘
if( isset($_GET['cid']) && isset($_GET['eid']) && isset($_GET['sid']) )
{
	$id_controller_selected = intval($_GET['cid']);
	$id_enc_highlight = intval($_GET['eid']);
	$id_pd_highlight = intval($_GET['sid']);
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
	foreach($listCtrl as $obj)
	{
		if($obj->ID == $id_controller_selected)
		{
			$objSelectedCtl = $obj;
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
function RescanController(cid)
{
    document.getElementById("id_controller_selected_h").value = cid;
	showTip();
	return true;	
}
function GetPdDetail(c_name, id)
{
	/*
	var page='drive_detail_info.php?c_name=' + c_name  + '&id=' + id;
	window.open (
			 page,
			 'drive_detail_info_window',
			 'height=620,width=500,top=100,left=200,resizable=no,location=no,status=yes'
	);
	*/
	return true;
}
function LocatePD(cid, eid, sid, msg)
{
	if( confirm(msg) )
	{
		var url = "locatePD.php?cid=" + cid + "&eid=" + eid + "&sid=" + sid;
		//alert(url);
		var xmlhttp = null;
		loadDoc(xmlhttp, url, locate_ajax);
		return true;
	}
}

// 磁盘操作类型: 1-定位磁盘，2-设置磁盘状态为good，3-开始初始化，4-停止初始化
function OperatePD(type, cid, eid, sid, msg)
{
    if( !confirm(msg) )
	{
    	return false;
	}
    var objOp = document.getElementById("pd_operation_h");
    var objPdStr = document.getElementById("pds_selected_h");
    objOp.value = type;
    objPdStr.value = cid + ":" + eid + ":" + sid;
    
	showTip();

	document.getElementById("pds_list_form").submit();
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
    <td class="bar_nopanel"><?php print $pd_info_str[$lang]; ?></td>
  </tr>
</table>
<?php 
if($b_have_controller === TRUE)
{
	// 控制器选择
	print "<form id=\"sel_controller_form\" name=\"sel_controller_form\" action=\"drive_target2.php\" method=\"post\">\n";
	print "  <input type=\"hidden\" name=\"id_controller_selected_h\" id=\"id_controller_selected_h\" value=\"\">\n";
	print "  <table width=\"100%\" border=\"0\" cellpadding=\"6\" align=\"center\">\n";
	print "  	<tr>\n";
	print "  	  <td class=\"field_title\">\n";
	print $select_controller_str[$lang] . ":";
	print "  	  </td>\n";
	print "  	  <td class=\"field_data1\">";
	print "  	    <select id=\"select_controller\" name=\"select_controller\" onChange=\"SelectController();\">\n";
	
	foreach( $listCtrl as $obj )
	{
		if($id_controller_selected == $obj->ID)
		{
			print "<option value=\"{$obj->ID}\" selected>{$obj->INFO['Model']}\n";
			$objSelectedCtl = $obj;
		}
		else
		{
			print "<option value=\"{$obj->ID}\">{$obj->INFO['Model']}\n";
		}
	}
	print "  	    </select>\n";
	print "  	  </td>\n";
	print "  	  <td class=\"field_title\">\n";
	print $number_of_pds_str[$lang] . ":";
	print "  	  </td>\n";
	print "  	  <td class=\"field_data1\">\n";
	print $objSelectedCtl->INFO['PDs'];
	print "  	  </td>\n";
	print "  	  <td class=\"field_data1\">\n";
	print "			<input type=\"submit\" name=\"rescan_controller_submit\" onclick=\"RescanController('{$objSelectedCtl->ID}');\" value=\"{$rescan_controller_str[$lang]}\" />\n";
	print "  	  </td>\n";
	print "  	</tr>\n";
	print "  </table>\n";
	print "</form>\n";
	
	// 磁盘信息列表显示
	$listPdInfo = array();
	if( $objSelectedCtl->INFO['PDs'] > 0 )
	{
		$listPdInfo = $objSelectedCtl->PDs;
		//////////////////////////////////////////////////////////////
		// 显示磁盘列表信息
		print "<form id=\"pds_list_form\" name=\"pds_list_form\" action=\"drive_target2.php?cid={$objSelectedCtl->ID}\" method=\"post\">\n";
		//保存针对磁盘的操作类型
        print "<input type=\"hidden\" name=\"pd_operation_h\" id=\"pd_operation_h\" value=\"\">\n";
        // 保存操作的磁盘信息，格式“控制器ID:enclosureID:SlotID”，多个用";"分隔
        print "<input type=\"hidden\" name=\"pds_selected_h\" id=\"pds_selected_h\" value=\"\">\n"; 
		print "<table width=\"100%\" border=\"0\" cellpadding=\"4\" align=\"center\">\n";
		
		print "  <tr>\n";
		print "    <td class=\"title\" colspan=\"10\">" . $pd_list_str[$lang] . "</td>\n";
		print "  </tr>\n";
		
		print "  <tr>\n";
		print "    <td class=\"field_title\">" . $pd_enc_str[$lang] ."</td>\n";
		print "    <td class=\"field_title\">" . $pd_slot_str[$lang] ."</td>\n";
		print "    <td class=\"field_title\">" . $pd_model_str[$lang] ."</td>\n";
		print "    <td class=\"field_title\">" . $pd_capacity_str[$lang] ."</td>\n";
		print "    <td class=\"field_title\">" . $pd_intf_str[$lang] ."</td>\n";
		print "    <td class=\"field_title\">" . $pd_smart_str[$lang] ."</td>\n";
		print "    <td class=\"field_title\">" . $pd_temperature_str[$lang] ."</td>\n";
		print "    <td class=\"field_title\">" . $pd_vd_str[$lang] ."</td>\n";
		print "    <td class=\"field_title\">" . $pd_status_str[$lang] ."</td>\n";
		print "    <td class=\"field_title\">" . $pd_operation_str[$lang] ."</td>\n";
		print "  </tr>\n";
		
		$td_class_toggle = "field_data1";
		foreach( $listPdInfo as $pdInfo )
		{
			$enc = $pdInfo["EID"];
			$slot = $pdInfo["Slot"];
			$model = $pdInfo["Manufacturer"] . ":" .  $pdInfo["Model"];
			$capacity = $pdInfo["Size"];
			$intf = $pdInfo["Intf"];// . ":" . $pdInfo["Link Speed"];
			$smart = $pdInfo["S.M.A.R.T alert flagged by drive"];
			$temperature = $pdInfo["Temperature"];
			$status = $pdInfo["State"];
			$td_class = $td_class_toggle;
			//print "    <td class=\"field_highlight\">{$enc}</td>";
			if( $id_controller_selected == $objSelectedCtl->ID && 
				$id_enc_highlight == $enc && 
				$id_pd_highlight == $slot )
			{
			    $td_class = "field_highlight"; // 高亮显示
			}

			print "  <tr>\n";
			print "    <td class=\"{$td_class}\">{$enc}</td>\n";
			print "    <td class=\"{$td_class}\">{$slot}</td>\n";
			print "    <td class=\"{$td_class}\">{$model}</td>\n";
			print "    <td class=\"{$td_class}\">{$capacity}</td>\n";
			print "    <td class=\"{$td_class}\">{$intf}</td>\n";
			print "    <td class=\"{$td_class}\">{$smart}</td>\n";
			print "    <td class=\"{$td_class}\">{$temperature}</td>\n";
			print "    <td class=\"{$td_class}\" align=\"left\">";
			if( $pdInfo['VDID'] == -1 && $pdInfo["HSType"] == 0) // 未配置的磁盘
			{
				print "-";
			}
			else if($pdInfo["HSType"] > 0) // 热备磁盘
			{
			    if($pdInfo["HSType"] == 1)// 全局热备
			    {
			        print $global_hotspare_str[$lang];
			    }
			    else if($pdInfo["HSType"] == 2) // 指定磁盘组热备
			    {
			        print $dedicated_hotspare_str[$lang] . ":" . $vd_str[$lang] . " ";
			        $href = "";
			        for ($i=0; $i<count($pdInfo["DHS Array"]); $i++)
			        {
			            $href = "unit_target2.php?vid={$pdInfo["DHS Array"][$i]}&cid={$objSelectedCtl->ID}";
			            if(($i+1) == count($pdInfo["DHS Array"])) // 最后一个元素
			            {
			                print "<a title=\"{$show_vd_detail_tip_str[$lang]}\" href=\"{$href}\" class=\"general_link\" style=\"text-decoration: none;\">{$pdInfo["DHS Array"][$i]}</a>";
			            }
			            else
			            {
			                print "<a title=\"{$show_vd_detail_tip_str[$lang]}\" href=\"{$href}\" class=\"general_link\" style=\"text-decoration: none;\">{$pdInfo["DHS Array"][$i]}</a>";
			                print ", ";
			            }
			        }
			    }
			}
			else if($pdInfo["VDID"] >= 0) // RADI组中的磁盘
			{
				$linkname = $vd_str[$lang] . $pdInfo["VDID"];
				print "<a title=\"{$show_vd_detail_tip_str[$lang]}\" href=\"unit_target2.php?vid={$pdInfo['VDID']}&cid={$objSelectedCtl->ID}\" class=\"general_link\" style=\"text-decoration: none;\">{$linkname}</a>&nbsp;[{$pdInfo['VDName']}]";
			}
			print "</td>\n";
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
			print "</td>\n";
			print "    <td class=\"{$td_class}\"  align=\"left\">";
			// 定位
			print "<img style=\"cursor:pointer\" title=\"{$locate_pd_str[$lang]}\" src=\"images/drive_identify_icon.gif\" onclick=\"LocatePD('{$pdInfo['Ctl']}', '{$pdInfo['EID']}', '{$pdInfo['Slot']}', '{$is_locate_pd_str[$lang]}');\"></img>";
			// 磁盘初始化
			// 没有正在初始化，显示开始初始化按钮
			if($status == "UGood" && $pdInfo['IsInit'] == 0)
			{
			    print "<img style=\"cursor:pointer\" title=\"{$pd_start_init_str[$lang]}\" src=\"images/start.png\" onclick=\"OperatePD(3, '{$pdInfo['Ctl']}', '{$pdInfo['EID']}', '{$pdInfo['Slot']}', '{$pd_start_init_tip_str[$lang]}');\"></img>";
			     
			}
			// 正在初始化，显示停止初始化按钮
			else if($status == "UGood" && $pdInfo['IsInit'] == 1)
			{
			    print "<img style=\"cursor:pointer\" title=\"{$pd_show_init_str[$lang]}{$pdInfo["Init Progress"]}&#10;{$pd_stop_init_str[$lang]}\" src=\"images/stop.png\" onclick=\"OperatePD(4, '{$pdInfo['Ctl']}', '{$pdInfo['EID']}', '{$pdInfo['Slot']}', '{$pd_stop_init_tip_str[$lang]}');\"></img>";
			}
			
			// 设置磁盘状态为可用
			if($status == "UBad")
			{
			    print "<img style=\"cursor:pointer\" title=\"{$pd_set_good_str[$lang]}\" src=\"images/set_pd_good.png\" onclick=\"OperatePD(2, '{$pdInfo['Ctl']}', '{$pdInfo['EID']}', '{$pdInfo['Slot']}', '{$pd_set_good_tip_str[$lang]}');\"></img>";
			
			}
			print "    </td>\n";
			//print "    <td class=\"{$td_class}\">";
			//print "<img style=\"cursor:pointer\" title=\"{$show_detail_str[$lang]}\" src=\"images/drive_detail_icon.gif\" onclick=\"GetPdDetail('{$controller_selected['name']}', '{$pdInfo['id']}');\"></img>";
			//print "    </td>";
			print "  </tr>\n";
			
			$td_class_toggle = ($td_class_toggle == "field_data1") ? "field_data2" : "field_data1";
		}
		
		print "</table>\n";
		print "</form>\n";
		/////////////////////////////////////////////////////////////
	}
	else
	{
		print_msg_block($no_pd_linked_str[$lang]);
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

