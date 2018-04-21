<?php 
require("./include/authenticated.php");
require_once("./include/function.php");
require_once("./include/log.php");
require_once("./include/LSIMegaRAID.php");

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
$rescan_controller_ok_str=array(
        "����ɨ��������ɹ�",
        "Rescan Controller OK"
);
$no_created_vd_str=array(
        "û���Ѵ�����RAID��",
        "No Available RAID Units"
);
$set_ctl_time_str=array(
	"���¿�����ʱ��",
	"Update Controller Time"
);
$use_localtime_str=array(
        "ʹ�ñ�����ʱ�����",
        "Update by This Machine Time"
);
$use_servertime_str=array(
        "ʹ�÷�����ʱ�����",
        "Update by Server Time"
);
$user_set_time_str=array(
        "�ֶ����¿�����ʱ��",
        "Set Controller Time"
);
$ctl_current_time_str=array(
        "��������ǰʱ��",
        "Current Time of Controller"
);
$sys_current_time_str=array(
        "��������ǰʱ��",
        "Current Time of Server"
);
$local_current_time_str=array(
        "������ǰʱ��",
        "Current Time of This Machine"
);

$vd_property_config_str=array(
        "RAID����������",
        "RAID Unit Properties Config"
);
$show_vd_detail_tip_str=array(
        "�鿴��RAID�����ϸ��Ϣ",
        "Show RAID Detail Information"
);
$vd_str=array(
        "RAID��",
        "RAID"
);
$raidtype_str=array(
        "����",
        "Type"
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
$vd_rename_str=array(
        "������",
        "Rename"
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
$invalid_vd_name_str=array(
        "��ЧRAID�����ƣ�\\n��Ч�ַ�:A-Za-z0-9_-������<=15",
        "Invalid Unit Name��\\nShould Be: A-Za-z0-9_-, Length <= 15"
);
$ok_str=array(
        "���óɹ�",
        "Update Success"
);
$failed_str=array(
        "����ʧ��",
        "Update Failed"
);
$tip_please_wait_str=array(
        "���ڴ����У����Ե�....",
        "Processing, Please Wait..."
);
?>

<?php
//print time() . "\n";
$objCtlFunc = new CLSIMegaRAIDFunc();
$id_controller_selected = -1; // ѡ��Ŀ�����ID
$message = "";
$objLog = new Log();

if ( isset($_GET['cid']) )
{
    $id_controller_selected = intval($_GET['cid']);
}
// ���ÿ�����ʱ��
// �˶δ��������ǰ�������ñ���ʱ������ʱ���������ȡ��������Ϣ��ʱ���������õ�ʱ�䲻׼ȷ
if( isset($_POST['set_time_type_h']) && isset($_POST['set_time_str_h']) )
{
    // 1-�ֶ����ã�2-ʹ�÷�����ʱ�䣬3-����ʱ�䣬����-��Ч
    $typeSetCtlTime = intval($_POST['set_time_type_h']);
    $valueSetCtlTime = trim($_POST['set_time_str_h']); // 2017-12-18 12:38:20
    // �滻��ʱ���ַ����е�"-"���޸�Ϊ���������ܵ�ʱ���ʽ"20171218 12:38:20"
    $valueSetCtlTime = str_replace("-", "", $valueSetCtlTime);
    switch($typeSetCtlTime)
    {
        case 1: // �ֶ�����
        case 3: // ʹ�ñ���ʱ��
            $objCtlFunc->SetCtlTime($id_controller_selected, $valueSetCtlTime, $message);
            break;
        case 2: // ʹ�÷�����ʱ��
            $objCtlFunc->SetCtlTime($id_controller_selected, "systemtime", $message);
            break;
        default:
             
            break;
    }
    if($message !== "")
    {
        $message = $failed_str[$lang] . ": " . $message;
    }
    else
    {
        $message = $ok_str[$lang];
    }
}
// ����raid�����
// �������ͣ�1-����IO���ԣ�2-���ö�ȡ���ԣ�3-����д����ԣ�4-������VD��������Ч
if(isset($_POST['vd_id_h']) && isset($_POST['vd_policy_type_h'])
        && isset($_POST['vd_text_h']))
{
    //var_dump($_POST);
    $v_id = intval($_POST['vd_id_h']);
    $vd_policy_type = intval($_POST['vd_policy_type_h']);
    $vd_text = strtolower(trim($_POST['vd_text_h']));
    $property = "";
    $value = $vd_text;
    switch($vd_policy_type)
    {
        case 1:
            $property = "iopolicy";
            break;
        case 2:
            $property = "rdcache";
            break;
        case 3:
            $property = "wrcache";
            break;
        case 4:
            $property = "name";
            break;
        default:
            $message = "unkown configure type!";
            break;
    }
    if($property != "")
    {
        $objCtlFunc->SetVdProperty($id_controller_selected, $v_id, $property, $value, $message);
    }
    if($message !== "")
    {
        $message = $failed_str[$lang] . ": " . $message;
    }
    else
    {
        $message = $ok_str[$lang];
    }
}
//print time() . "\n";
$objCtlList = new CLSIMegaRAIDList();
$objSelectedCtl = new CLSIMegaRAID(); // ѡ��Ŀ�����
$listCtrl = array();
$b_have_controller = TRUE;

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
// ѡ�������
if( isset($_POST['select_controller']) )
{
	$id_controller_selected = intval($_POST['select_controller']);
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
<script defer type="text/javascript" src="js/function.js"></script>
<script type="text/javascript" language="javascript" src="js/ajax_function.js"></script>
<?php 
if($lang==0)//����
{
?>
	<script language="javascript" src="js/calendar_cn.js" type="text/javascript"></script>
<?php 
}
else if($lang==1)//English
{
?>
	<script language="javascript" src="js/calendar_en.js" type="text/javascript"></script>
<?php 
}
?>
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

// ����ʱ�䣬POST�ϴ�
// type��1-�ֶ����ã�2-ʹ�÷�����ʱ�䣬3-����ʱ�䣬����-��Ч
function SetCtlTime(type)
{
    var tt = parseInt(type);

    var objType = document.getElementById("set_time_type_h");
    var objStr = document.getElementById("set_time_str_h");
	var objCtlTime = document.getElementById("ctl_time");
	
    objType.value = tt;
	switch(tt)
	{
	case 1:// �ֶ�����
	    objStr.value = objCtlTime.value;
		break;
	case 2:// ʹ�÷�����ʱ��
		objStr.value = "sytemtime";
		break;
	case 3:// ����ʱ������
		objStr.value = getlocaltime();
		break;
	default:
		return false;
	}
	showTip();
	document.set_ctl_time_form.submit();
	return true;
}

function getlocaltime()
{
    var today = new Date();
    var yearNow = today.getFullYear();
    var monthNow = today.getMonth()+1;
    var dateNow = today.getDate();
    var hourNow = today.getHours();
    var minNow = today.getMinutes();
    var secNow = today.getSeconds();

    if(monthNow<10) monthNow = "0" + monthNow;
    if(dateNow<10) dateNow = "0" + dateNow;
    if(hourNow<10) hourNow = "0" + hourNow;
    if(minNow<10) minNow = "0" + minNow;
    if(secNow<10) secNow = "0" + secNow;

    var time_str = yearNow + "-" + monthNow + "-" + dateNow + " "
    			   + hourNow + ":" + minNow + ":" + secNow;
    
    return time_str;
}
function reloadLocalTime()
{
	var time = getlocaltime();
	document.getElementById('localtime_div').innerHTML = time;
}
function reloadServerTime()
{
	var xmlhttp = null;
	loadDoc(xmlhttp, "getSysTime.php", load_server_time_ajax);
}
function reloadCtlTime(cid)
{
	var xmlhttp = null;
	var url = "getCtlTime.php?cid=" + cid;
	loadDoc(xmlhttp, url, load_ctl_time_ajax);
}

function loadTime(cid)
{
	reloadCtlTime(cid);
	reloadServerTime();
	reloadLocalTime();
	return true;
}
// ��������
// �������ͣ�1-����IO���ԣ�2-���ö�ȡ���ԣ�3-����д����ԣ�4-������VD��������Ч
function SetIOPolicy(obj, vid)
{
	var objVIDh = document.getElementById('vd_id_h');
	var objVPTh = document.getElementById('vd_policy_type_h');
	var objVTh = document.getElementById('vd_text_h');
	objVPTh.value = "1"; // ����IO����
	objVIDh.value = vid;
	objVTh.value = obj.value;
	
	showTip();

	document.getElementById('vd_policies_form').submit();
	return true;
}

function SetReadPolicy(obj, vid)
{
	var objVIDh = document.getElementById('vd_id_h');
	var objVPTh = document.getElementById('vd_policy_type_h');
	var objVTh = document.getElementById('vd_text_h');
	objVPTh.value = "2"; // ���ö�ȡ����
	objVIDh.value = vid;
	objVTh.value = obj.value;
	showTip();
	document.getElementById('vd_policies_form').submit();
	return true;
}

function SetWritePolicy(obj, vid)
{
	var objVIDh = document.getElementById('vd_id_h');
	var objVPTh = document.getElementById('vd_policy_type_h');
	var objVTh = document.getElementById('vd_text_h');
	objVPTh.value = "3"; // ����д�����
	objVIDh.value = vid;
	objVTh.value = obj.value;
	showTip();
	document.getElementById('vd_policies_form').submit();
	return true;
}

function SaveVDName(txtid, vid, msg)
{
	var objVIDh = document.getElementById('vd_id_h');
	var objVPTh = document.getElementById('vd_policy_type_h');
	var objVTh = document.getElementById('vd_text_h');
	var objNameTxt = document.getElementById(txtid);

	// �ж������Ƿ���Ч
	if( IsUnitNameOk(objNameTxt.value) )
	{
    	objVPTh.value = "4"; // ������VD
    	objVIDh.value = vid;
    	objVTh.value = objNameTxt.value;
    	
    	showTip();
    	document.getElementById('vd_policies_form').submit();
    	return true;
	}
	else
	{
	    alert(msg);
	    objNameTxt.focus();
	    objNameTxt.select();
	    return false;
	}
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
    <td class="bar_nopanel"><?php print $controller_setting_str[$lang]; ?></td>
  </tr>
</table>

<?php   
if($b_have_controller === TRUE)
{
	// ������ѡ��
	print "<form id=\"sel_controller_form\" name=\"sel_controller_form\" action=\"controller_target2.php\" method=\"post\">";
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
	
	// ����ʱ��	
	print "<form id=\"set_ctl_time_form\" name=\"set_ctl_time_form\" action=\"controller_target2.php?cid={$objSelectedCtl->ID}\" method=\"post\">";
	print "  <input type=\"hidden\" name=\"set_time_str_h\" id=\"set_time_str_h\" value=\"\">";
	print "  <input type=\"hidden\" name=\"set_time_type_h\" id=\"set_time_type_h\" value=\"\">";
	
	print "  <table width=\"100%\" border=\"0\" cellpadding=\"6\" align=\"center\">";
	
	print "  <tr>";
	print "    <td class=\"title\" colspan=\"3\">" . $set_ctl_time_str[$lang] . "</td>";
	print "  </tr>";

	// SetCtlTime �� 1-�ֶ����ã�2-ʹ�÷�����ʱ�䣬3-����ʱ��
	print "  <tr>";
	print "    <td class=\"field_title\">" . $ctl_current_time_str[$lang] . "</td>";
	print "    <td class=\"field_data1\" width=\"100px\">";
	print "      <div id=\"ctltime_div\"></div>";
	$systime_str = date("Y-m-d H:i:s");
	print "    </td>";
	print "    <td class=\"field_data1\" align=\"left\">";
	print "      <input type=\"text\" size=\"20\" value=\"{$systime_str}\" id=\"ctl_time\" name=\"ctl_time\"
	onClick=\"SelectDate(this,'yyyy-MM-dd hh:mm:ss',0,0)\" readonly=\"readonly\" />";
    print "      <input type=\"button\" id=\"user_set_time\" name=\"user_set_time\" onClick=\"return SetCtlTime(1);\" value=\"{$user_set_time_str[$lang]}\" />";
	print "    </td>";
	print "  </tr>";
	
	print "  <tr>";
	print "    <td class=\"field_title\">" . $sys_current_time_str[$lang] . "</td>";
	print "    <td class=\"field_data1\">";
	print "      <div id=\"systime_div\"></div>";
	print "    </td>";
	print "    <td class=\"field_data1\" align=\"left\">";
	print "      <input type=\"button\" id=\"use_server_time\" name=\"use_server_time\" onClick=\"return SetCtlTime(2);\" value=\"{$use_servertime_str[$lang]}\" />";
	print "    </td>";
	print "  </tr>";
	
	print "  <tr>";
	print "    <td class=\"field_title\">" . $local_current_time_str[$lang] . "</td>";
	print "    <td class=\"field_data1\">";
	print "      <div id=\"localtime_div\"></div>";
	print "    </td>";
	print "    <td class=\"field_data1\" align=\"left\">";
	print "      <input type=\"button\" id=\"use_local_time\" name=\"use_local_time\" onClick=\"return SetCtlTime(3);\" value=\"{$use_localtime_str[$lang]}\" />";
	print "    </td>";
	print "  </tr>";
	
	print "</table>";
	print "</form>";
	// ��ȡ��չʾʱ��
	print "<script type=\"text/javascript\">";
	print "loadTime({$id_controller_selected});";
	print "window.setInterval(\"loadTime({$id_controller_selected})\", 1000);";
	print "</script>";
	
	// UNIT��������
	print "<form id=\"vd_policies_form\" name=\"vd_policies_form\" action=\"controller_target2.php?cid={$objSelectedCtl->ID}\" method=\"post\">";
	print "  <input type=\"hidden\" name=\"vd_id_h\" id=\"vd_id_h\" value=\"\">";// ����Ҫ�����unit id
	print "  <input type=\"hidden\" name=\"vd_policy_type_h\" id=\"vd_policy_type_h\" value=\"\">";// ��������ַ�������
	print "  <input type=\"hidden\" name=\"vd_text_h\" id=\"vd_text_h\" value=\"\">";// ����Ҫ�������Ϣ
	print "";
	print "  <table width=\"100%\" border=\"0\" cellpadding=\"6\" align=\"center\">";
	print "  <tr>";
	print "    <td class=\"title\" colspan=\"6\">" . $vd_property_config_str[$lang] . "</td>";
	print "  </tr>";
	print "  <tr>";
	print "    <td class=\"field_title\"></td>";
	print "    <td class=\"field_title\">" . $raidtype_str[$lang] . "</td>";
	print "    <td class=\"field_title\">" . $io_policy_str[$lang] . "</td>";
	print "    <td class=\"field_title\">" . $read_policy_str[$lang] . "</td>";
	print "    <td class=\"field_title\">" . $write_policy_str[$lang] . "</td>";
	print "    <td class=\"field_title\">" . $vd_rename_str[$lang] . "</td>";
	print "  </tr>";
	// �Ѵ�����unit������Ϣ�б���ʾ
	$listVdInfo = $objSelectedCtl->VDs;

	$td_class = "field_data1";
	foreach( $listVdInfo as $vdInfo )
	{
		print "  <tr>";
		print "    <td class=\"{$td_class}_left\">";
		$href = "unit_target2.php?vid=" . $vdInfo['VD'] . "&cid=" . $objSelectedCtl->ID;
		print "<a title=\"{$show_vd_detail_tip_str[$lang]}\" class=\"general_link\" href=\"{$href}\">";
		print $vd_str[$lang]. " " . $vdInfo['VD'];
		print "</a>&emsp;[{$vdInfo['Name']}]</td>";
		
		// RAID����
		print "    <td class=\"{$td_class}\">{$vdInfo["TYPE"]}</td>";
        
		// IO����
	    $direct_sel = "";
	    $cached_sel = "";
	    if($vdInfo["io"]["Type"] == "direct")
	    {
	        $direct_sel = "selected";
	    }
	    else if($vdInfo["io"]["Type"] == "cached")
	    {
	        $cached_sel = "selected";
	    }
	    $id_iopolicy = "io_policy_sel" . $vdInfo["VD"];
	    print "    <td class=\"{$td_class}\">";
		print "<select name=\"{$id_iopolicy}\" id=\"{$id_iopolicy}\" onchange=\"SetIOPolicy(this, '{$vdInfo["VD"]}');\" >";
		print "<option value=\"direct\" {$direct_sel}/>{$io_direct_str[$lang]}";
		print "<option value=\"cached\" {$cached_sel}/>{$io_cached_str[$lang]}";
		print "</select>";
		print "    </td>";
		
		// ��ȡ����
	    $ra_sel = "";
	    $nora_sel = "";
	    if($vdInfo["rdcache"]["Type"] == "ra")
	    {
	        $ra_sel = "selected";
	    }
	    else if($vdInfo["rdcache"]["Type"] == "nora")
	    {
	        $nora_sel = "selected";
	    }
	    $id_readpolicy = "read_policy_sel" . $vdInfo["VD"];
	    print "    <td class=\"{$td_class}\">";
		print "       <select name=\"{$id_readpolicy}\" id=\"{$id_readpolicy}\" onchange=\"SetReadPolicy(this, '{$vdInfo["VD"]}');\" >";
		print "       <option value=\"ra\" {$ra_sel}/>{$read_ra_str[$lang]}";
		print "       <option value=\"nora\" {$nora_sel}/>{$read_nora_str[$lang]}";
		print "       </select>";
		print "    </td>";

		// д�����
		$wt_sel = "";
		$wb_sel = "";
	    $awb_sel = "";
	    if($vdInfo["wrcache"]["Type"] == "wt")
	    {
	        $wt_sel = "selected";
	    }
	    else if($vdInfo["wrcache"]["Type"] == "wb")
	    {
	        $wb_sel = "selected";
	    }
	    else if($vdInfo["wrcache"]["Type"] == "awb")
	    {
	        $awb_sel = "selected";
	    }
	    $id_writepolicy = "write_policy_sel" . $vdInfo["VD"];
	    print "    <td class=\"{$td_class}\">";
		print "       <select name=\"{$id_writepolicy}\" id=\"{$id_writepolicy}\" onchange=\"SetWritePolicy(this, '{$vdInfo["VD"]}');\" >";
		print "       <option value=\"wt\" {$wt_sel}/>{$write_wt_str[$lang]}";
		//print "       <option value=\"wb\" {$wb_sel}/>{$write_wb_str[$lang]}";
		print "       <option value=\"awb\" {$awb_sel}/>{$write_awb_str[$lang]}";
		print "       </select>";
		print "    </td>";
		
		// �޸�����
		$id_vd_rename_text = "vd_rename_text" . $vdInfo["VD"];
		print "    <td class=\"{$td_class}\">";
		print "       <input type=\"text\" size=\"18\" value=\"{$vdInfo['Name']}\" name=\"{$id_vd_rename_text}\" id=\"{$id_vd_rename_text}\" maxlength=\"15\"/>";
		print "       <input type=\"button\" value=\"{$vd_rename_str[$lang]}\" onclick=\"SaveVDName('{$id_vd_rename_text}','{$vdInfo["VD"]}','{$invalid_vd_name_str[$lang]}');\"/>";
		print "    </td>";

		print "  </tr>";
	   $td_class = ($td_class == "field_data1") ? "field_data2" : "field_data1";
	}
	print "  </table>";
	print "</form>";
	if(count($listVdInfo) == 0)
	{
	    print_msg_block( $no_created_vd_str[$lang] );
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
