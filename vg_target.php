<?php
require("./include/authenticated.php");
require_once("./include/function.php");
require_once("./include/lvm.php");

$lang=load_lang();

$lg_info_str=array(
	"卷组信息",
	"Logic Volume Group Information"
);
$create_new_lg_str=array(
	"创建新的卷组",
	"Create New Logic Volume Group"
);
$vg_name_str=array(
	"卷组名称",
	"Volume Group Name"
);
$total_size_str=array(
	"总大小",
	"Total Size"
);
$left_size_str=array(
	"剩余大小",
	"Unused size"
);
$vg_member_str=array(
	"成员",
	"Member"
);
$operate_str=array(
	"操作",
	"Operation"
);
$member_view_str=array(
	"物理卷成员查看",
	"View Physical Volume(s)"
);
$remove_str=array(
	"删除",
	"Remove"
);

$create_tip_str=array(
	"有效的卷组名称字符：A-Z a-z 0-9 _",
	"Valid characters for volume group name: A-Z a-z 0-9 _"
);
$enter_name_tip_str=array(
	"输入卷组名称（无空格，如vg0）：",
	"Enter volume group name(no whitespace, vg0 e.g.): "
);
$sel_pv_tip_str=array(
	"选择需要包含的物理磁盘：",
	"Select physical volume(s) to the group: "
);
$create_vg_str=array(
	"创建卷组",
	"Create Logic Volume Group"
);
$none_str=array(
	"- 无 -",
	"- None -"
);
$no_pv_for_use_str=array(
	"没有可用的物理卷",
	"None physical volume"
);
$delete_vg_str=array(
	"删除逻辑卷",
	"Delete logic volume group"
);
$ok_str=array(
	"成功",
	"OK"
);
$failed_str=array(
	"失败",
	"failed"
);
$pv_in_str=array(
	"物理卷在",
	"Physical volume(s) in"
);
$close_window_str=array(
	"关闭窗口",
	"Close Window"
);
$device_str=array(
	"设备",
	"Device"
);
$device_size_str=array(
	"大小",
	"Size"
);
$inuse_str=array(
	"正在使用",
	"In use"
);
$allocated_str=array(
	"已分配",
	"Allocated"
);
$select_pv_tip_str=array(
	"请先选择物理卷！",
	"Please select physical volume(s) first!"
);
$vg_name_error_str=array(
	"请输入合法的卷组名称！",
	"Please enter one correct volume group name!"
);
?>

<?php 
$message = "";
$del_postfix = "_del_sumbit";
$bHasItem = FALSE;
$logicVolumeGroup = new LogicVolumeGroup();
$physicalVolume = new PhysicalVolume();

// 删除逻辑卷组
$vg_list = array();
$vg_list = $logicVolumeGroup->GetVgList();
if($vg_list != FALSE)
{
	foreach( $vg_list as $entry)
	{
		$field = $entry['name'] . $del_postfix;
		if( isset($_POST["$field"]) )
		{
			$retval = $logicVolumeGroup->RemoveVg($entry['name']);
			if ( $retval != TRUE )
			{
				$message = $delete_vg_str[$lang] . " " . $entry['name'] . " " . $failed_str[$lang];
			}
			else
			{
				header("Location: vg_target.php");
			}
			break;
		}
		else
		{
			continue;
		}
	}
}

// 创建逻辑卷组
if( isset($_POST['create_vg_submit']) && isset($_POST['vg_name']) && isset($_POST['pvlist']) )
{
	$vg_name = $_POST['vg_name'];
	$pv_list = $_POST['pvlist'];// array
	$retval = $logicVolumeGroup->CreateVg($vg_name, $pv_list);
	if($retval == FALSE)
	{
		$message = $create_vg_str[$lang] . " " . $vg_name . " " . $failed_str[$lang];
	}
	else
	{
		header("Location: vg_target.php");
	}
}
?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<link rel="stylesheet" href="css/target.css" type="text/css" />
<script defer type="text/javascript" src="js/pngfix.js"></script>
<script defer type="text/javascript" src="js/function.js"></script>
<script src="js/utility.js" type="text/javascript" language="JavaScript"></script>
<script src="js/popup.js" type="text/javascript" language="JavaScript"></script>
<script type="text/javascript">
function check_vgname()
{
	var vgname = document.lg_form.vg_name.value;
	if(vgname == "")
	{
		document.lg_form.create_vg_submit.disabled = true;
		return false;
	}
	var retval = IsLvmNameOk(vgname);
	if(retval == true)
	{
		document.getElementById("vg_name_span").innerHTML = "<img src='./images/right_icon.png'></img>";
		document.lg_form.create_vg_submit.disabled = false;
	}
	else
	{
		document.getElementById("vg_name_span").innerHTML = "<img src='./images/error_icon.png'></img>";
		document.lg_form.create_vg_submit.disabled = true;
	}
	return true;
}

function check_pv_select()
{
	for (i = 0; i < document.getElementsByName("pvlist[]").length; i++)
	{
		if(document.getElementsByName("pvlist[]")[i].checked)
			return true;
	}
	alert('<?php print $select_pv_tip_str[$lang];?>');
	return false;
}
</script>
</head>

<body>

<div id="logicgroup_target">
<?php 
//////////////////////////////////////////////////////////////////////
// div setup
//////////////////////////////////////////////////////////////////////
$vg_list = array();
$bHasItem = FALSE;
$vg_list = $logicVolumeGroup->GetVgList();
if( $vg_list != FALSE )
{
	for ($i = 0; $i < count($vg_list); $i++)
	{
		$rand_id = sha1($vg_list[$i]["name"]);
		print("<div id=\"popup-" . $rand_id . "\" onclick=\"event.cancelBubble = true;\" onmousedown=\"dragpopup(this, event)\" class=\"pvspopup\">\n");
		print("<h3 align=\"center\">" . $pv_in_str[$lang] . " " . $vg_list[$i]["name"] . "</h3>\n");
		print("<table width=\"70%\" cellpadding=\"8\" cellspacing=\"2\" border=\"0\" align=\"center\">\n");
		print("<tr>\n");
		print("\t<td class=\"field_title\">{$device_str[$lang]}</td>\n");
		print("\t<td class=\"field_title\">{$device_size_str[$lang]}</td>\n");
		print("</tr>\n");
		
		$pv_list_vg = $logicVolumeGroup->GetPvListOfVg($vg_list[$i]['name']);
		$td_class = "field_data1";
		for ($j = 0; $j < count($pv_list_vg); $j++)
		{
			print("<tr>\n");
			print("\t<td class=\"{$td_class}\">" . $pv_list_vg[$j]['name'] . "</td>\n");
			print("\t<td class=\"{$td_class}\">" . $pv_list_vg[$j]['size'] . "</td>\n");
			print("</tr>\n");
			
			if($td_class == "field_data1")
			{
				$td_class = "field_data2";
			}
			else
			{
				$td_class = "field_data1";
			}
		}

		print("</table>\n");

		print("<p><a href=\"#\" onclick=\"hideCurrentPopup(); return false;\">{$close_window_str[$lang]}</a></p>\n");

		print("<p align=\"center\">&nbsp;</p>\n");

		print("</div>\n");
	}
}
?>

  <div id="logicgroup">
	  <table align="center" width="100%">
	  	<tr>
		<td class="bar_nopanel"><?php print $lg_info_str[$lang]; ?></td>
		</tr>
	  </table>
	  	<form id="lginfo_form" name="lginfo_form" action="vg_target.php" method="post"> 
		  <table width="70%" border="0" cellpadding="6" align="center">
			<tr>
			  <td class="field_title"><?php print $vg_name_str[$lang]; ?></td>
			  <td class="field_title"><?php print $total_size_str[$lang]; ?></td>
			  <td class="field_title"><?php print $left_size_str[$lang]; ?></td>
			  <td class="field_title"><?php print $vg_member_str[$lang]; ?></td>
			  <td class="field_title"><?php print $operate_str[$lang]; ?></td>
			</tr>
<?php 
// 逻辑卷组信息
$bHasItem = FALSE;
if( $vg_list != FALSE )
{
	//////////////////////////////////////////////////////////////////////
	// table show
	//////////////////////////////////////////////////////////////////////
	$td_class = "field_data1";
	foreach( $vg_list as $entry )
	{
		$rand_id = sha1($entry["name"]);
		print "<tr>";
		print "<td class=\"{$td_class}\">" . $entry['name'] . "</td>";
		print "<td class=\"{$td_class}\">" . $entry['size'] . "</td>";
		print "<td class=\"{$td_class}\">" . $entry['free'] . "</td>";
		
		print "<td class=\"{$td_class}\">";
		print "<a href=\"#\" onclick=\"return !showPopup('popup-{$rand_id}', event);\">" . $member_view_str[$lang] . "</a>";
		print "</td>";
		
		print "<td class=\"{$td_class}\">";
		// 是否有逻辑卷，如果有则不允许删除
		$lv_list = $logicVolumeGroup->GetLvListOfVg($entry['name']);
		if($lv_list==FALSE || count($lv_list)==0)
		{
			print "	<input type=\"submit\" name=\"{$entry['name']}{$del_postfix}\" value=\"{$remove_str[$lang]}\"/>";
		}
		else
		{
			print $allocated_str[$lang];
		}
		
		print "</td>";
		
		print "</tr>";
		
		$bHasItem = TRUE;
		if($td_class == "field_data1")
		{
			$td_class = "field_data2";
		}
		else
		{
			$td_class = "field_data1";
		}
	}
}
if($bHasItem == FALSE)
{
	print "<tr><td class=\"field_data2\" colspan=\"5\">{$none_str[$lang]}</td></tr>";
}
?>
		  </table>
		</form>
	<!--     -->
	  <table align="center" width="100%">
	  	<tr>
		<td class="bar_nopanel"><?php print $create_new_lg_str[$lang]; ?></td>
		</tr>
	  </table>
	  <table align="center" width="70%">
	  	<tr>
		<td class="tip_data">
			<img src="images/tip.gif" />
			<?php print $create_tip_str[$lang]; ?>
		</td>
		</tr>
	  </table>

	  	<form id="lg_form" name="lg_form" action="vg_target.php" method="post"> 
		  <table border="0" cellpadding="6" align="center" width="70%">
			<tr>
			  <td class="field_title">
			  	<?php print $enter_name_tip_str[$lang]; ?>
			  </td>
			</tr>
			<tr>
			  <td class="field_data1">
			  	<input type="text" name="vg_name" onkeyup="return check_vgname();" onchange="return check_vgname();" value="" size="20"  maxlength="10" />
			  	<span id="vg_name_span"><img src='./images/error_icon.png' style="visibility: hidden;"></img></span>
			  </td>
			</tr>
			<tr>
			  <td class="field_title"><?php print $sel_pv_tip_str[$lang]; ?></td>
			</tr>
<?php 
// 列出可用的物理卷
$pv_list = array();
$bHasItem = FALSE;
$pv_list = $physicalVolume->GetPvList();
if($pv_list != FALSE)
{
	$td_class = "field_data1";
	foreach($pv_list as $entry)
	{
		// 如果此物理卷已属于某一卷组，则不列出
		if( $entry['vg'] != "" )
		{
			continue;
		}
		print "<tr>";
		print "<td class=\"{$td_class}\" align=\"left\">";
		print "<input type=\"checkbox\" name=\"pvlist[]\" value=\"{$entry['device']}\" />";
		print "&nbsp;" . $entry['name'] . "&ensp;&ensp;" . $entry['size'];
		print "</td>";		
		print "</tr>";	
					
		$bHasItem = TRUE;				
		if($td_class == "field_data1")
		{
			$td_class = "field_data2";
		}
		else
		{
			$td_class = "field_data1";
		}
	}
}
if($bHasItem == FALSE)
{
	print "<tr><td class=\"field_data2\">{$none_str[$lang]}</td></tr>";
}
?>
			<tr>
			  <td>
			  	<input type="submit" name="create_vg_submit" disabled="disalbed" 
			  	onClick="return check_pv_select();" 
			  	<?php
			  	  	if( $bHasItem == FALSE)
				  	{
				  		print "disabled=\"disabled\"";
				  	}
			  	?>
			  	 value="<?php print $create_vg_str[$lang]; ?>"/>
			  </td>
			</tr>
		  </table>
		</form>
<?php 
if($message != "")
{
	print_msg_block($message);
}
?>
	</div>
</div>

</body>
</html>
