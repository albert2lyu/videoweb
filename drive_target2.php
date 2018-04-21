<?php
require("./include/authenticated.php");
require_once("./include/function.php");
require_once("./include/LSIMegaRAID.php");
require_once("./include/log.php");

$lang=load_lang();

$pd_info_str=array(
	"������Ϣ",
	"Drive Information"
);
$vd_str=array(
        "RAID��",
        "Unit"
);
$pd_list_str=array(
	"�����б�",
	"Drive List"
);
$pd_index_str=array(
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
$pd_temperature_str=array(
	"�¶�",
	"Temperature"
);
$pd_intf_str=array(
	"�ӿ�",
	"Interface"
);
$pd_manu_str=array(
	"����",
	"Manufacturer"
);
$pd_vd_str=array(
	"����RAID��",
	"RAID"
);
$pd_status_str=array(
	"״̬",
	"Status"
);
$pd_locate_str=array(
	"��λ",
	"Identify"
);
$pd_smart_str=array(
	"����",
	"Alert"
);
$pd_detail_str=array(
	"��ϸ��Ϣ",
	"Detail"
);
$select_controller_str=array(
	"ѡ�������",
	"Select Controller"
);
$number_of_pds_str=array(
	"���Ӵ�����",
	"Number of Drives"
);
$no_controller_str=array(
	"�޿��ÿ�����",
	"No Controller"
);
$no_pd_linked_str=array(
	"û�д���",
	"None Drive"
);
$is_locate_pd_str=array(
	"�Ƿ�λ�˴���?",
	"Locate this drive?"
);
$show_detail_str=array(
	"��ʾ������ϸ��Ϣ",
	"Show drive detail"
);
$show_vd_detail_tip_str=array(
        "�鿴��RAID�����ϸ��Ϣ",
        "Show Unit's Detail Information"
);
$locate_pd_str=array(
	"��λ�˴���",
	"Locate this drive"
);
$rescan_controller_str=array(
	"����ɨ�������",
	"Rescan Controller"
);
$rescan_controller_ok_str=array(
    "����ɨ��������ɹ�",
    "Rescan Controller OK"
);
$global_hotspare_str=array(
        "ȫ���ȱ�",
        "Global HotSpare"
        
);
$dedicated_hotspare_str=array(
        "ר���ȱ�",
        "Dedicated HotSpare"
);
$pd_operation_str=array(
        "����",
        "Operation"
);
$pd_set_good_str=array(
        "���ô���״̬Ϊ����",
        "Set Drive State 'good'"
);
$pd_set_good_tip_str=array(
        "�Ƿ����ô���״̬Ϊ����",
        "To Set Drive State 'good'?"
);
$pd_start_init_str=array(
        "��ʼ��ʼ������",
        "Start The Initialization of Drive"
);
$pd_start_init_tip_str=array(
        "�Ƿ�ʼ��ʼ�����̣�������������ݣ�",
        "Start The Initialization of Drive? All Data Will Be Earsed!"
);
$pd_show_init_str=array(
        "�������ڳ�ʼ�������ȣ�",
        "Drive Initializing, Progress: "
);
$pd_stop_init_str=array(
        "ֹͣ��ʼ������",
        "Stop The Initialization of Drive"
);
$pd_stop_init_tip_str=array(
        "�Ƿ�ֹͣ��ʼ������",
        "Stop The Initialization of Drive��"
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

// ���̲�������
while(isset($_POST['pd_operation_h']) && isset($_POST['pds_selected_h']))
{
    // ���̲�������: 1-��λ���̣�2-���ô���״̬Ϊgood��3-��ʼ��ʼ����4-ֹͣ��ʼ��
    $op_type = intval(trim($_POST['pd_operation_h']));
    $pds_selected = trim($_POST['pds_selected_h']);
    
    $pdStrList = explode(";", $pds_selected);
    $pdInfoList = array();
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
        case 1: // ��λ����
            
            break;
        case 2: // ���ô���״̬Ϊgood
            foreach($pdInfoList as $pdInfo)
            {
                $objCtlFunc->SetPdUbadToUgood($pd['Ctl'], $pd['EID'], $pd['Slot'], TRUE, $message);
            }
            break;
        case 3: // ��ʼ��ʼ������
            foreach($pdInfoList as $pdInfo)
            {
                $objCtlFunc->StartPdInitialization($pd['Ctl'], $pd['EID'], $pd['Slot'], $message);
            }
            break;
        case 4: // ֹͣ���̳�ʼ��
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
$id_controller_selected = -1; // ѡ��Ŀ�����ID
$objSelectedCtl = new CLSIMegaRAID(); // ѡ��Ŀ�����
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
// ����ѡ���˿�����
if( isset($_POST['select_controller']) )
{
	$id_controller_selected = intval($_POST['select_controller']);
}

if( isset($_GET['cid']) )
{
	$id_controller_selected = intval($_GET['cid']);
}

// ������ʾ�Ĵ���
if( isset($_GET['cid']) && isset($_GET['eid']) && isset($_GET['sid']) )
{
	$id_controller_selected = intval($_GET['cid']);
	$id_enc_highlight = intval($_GET['eid']);
	$id_pd_highlight = intval($_GET['sid']);
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

// ���̲�������: 1-��λ���̣�2-���ô���״̬Ϊgood��3-��ʼ��ʼ����4-ֹͣ��ʼ��
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
	// ������ѡ��
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
	
	// ������Ϣ�б���ʾ
	$listPdInfo = array();
	if( $objSelectedCtl->INFO['PDs'] > 0 )
	{
		$listPdInfo = $objSelectedCtl->PDs;
		//////////////////////////////////////////////////////////////
		// ��ʾ�����б���Ϣ
		print "<form id=\"pds_list_form\" name=\"pds_list_form\" action=\"drive_target2.php?cid={$objSelectedCtl->ID}\" method=\"post\">\n";
		//������Դ��̵Ĳ�������
        print "<input type=\"hidden\" name=\"pd_operation_h\" id=\"pd_operation_h\" value=\"\">\n";
        // ��������Ĵ�����Ϣ����ʽ��������ID:enclosureID:SlotID���������";"�ָ�
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
			    $td_class = "field_highlight"; // ������ʾ
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
			if( $pdInfo['VDID'] == -1 && $pdInfo["HSType"] == 0) // δ���õĴ���
			{
				print "-";
			}
			else if($pdInfo["HSType"] > 0) // �ȱ�����
			{
			    if($pdInfo["HSType"] == 1)// ȫ���ȱ�
			    {
			        print $global_hotspare_str[$lang];
			    }
			    else if($pdInfo["HSType"] == 2) // ָ���������ȱ�
			    {
			        print $dedicated_hotspare_str[$lang] . ":" . $vd_str[$lang] . " ";
			        $href = "";
			        for ($i=0; $i<count($pdInfo["DHS Array"]); $i++)
			        {
			            $href = "unit_target2.php?vid={$pdInfo["DHS Array"][$i]}&cid={$objSelectedCtl->ID}";
			            if(($i+1) == count($pdInfo["DHS Array"])) // ���һ��Ԫ��
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
			else if($pdInfo["VDID"] >= 0) // RADI���еĴ���
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
			// ��λ
			print "<img style=\"cursor:pointer\" title=\"{$locate_pd_str[$lang]}\" src=\"images/drive_identify_icon.gif\" onclick=\"LocatePD('{$pdInfo['Ctl']}', '{$pdInfo['EID']}', '{$pdInfo['Slot']}', '{$is_locate_pd_str[$lang]}');\"></img>";
			// ���̳�ʼ��
			// û�����ڳ�ʼ������ʾ��ʼ��ʼ����ť
			if($status == "UGood" && $pdInfo['IsInit'] == 0)
			{
			    print "<img style=\"cursor:pointer\" title=\"{$pd_start_init_str[$lang]}\" src=\"images/start.png\" onclick=\"OperatePD(3, '{$pdInfo['Ctl']}', '{$pdInfo['EID']}', '{$pdInfo['Slot']}', '{$pd_start_init_tip_str[$lang]}');\"></img>";
			     
			}
			// ���ڳ�ʼ������ʾֹͣ��ʼ����ť
			else if($status == "UGood" && $pdInfo['IsInit'] == 1)
			{
			    print "<img style=\"cursor:pointer\" title=\"{$pd_show_init_str[$lang]}{$pdInfo["Init Progress"]}&#10;{$pd_stop_init_str[$lang]}\" src=\"images/stop.png\" onclick=\"OperatePD(4, '{$pdInfo['Ctl']}', '{$pdInfo['EID']}', '{$pdInfo['Slot']}', '{$pd_stop_init_tip_str[$lang]}');\"></img>";
			}
			
			// ���ô���״̬Ϊ����
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

