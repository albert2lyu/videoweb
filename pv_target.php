<?php
require("./include/authenticated.php");
require_once("./include/function.php");
require_once("./include/lvm.php");
require_once("./include/disk.php");
require_once("./include/log.php");

$lang=load_lang();

$block_device_str=array(
	"磁盘设备",
	"Block Devices"
);
$device_str=array(
	"设备",
	"Device"
);
$device_size_str=array(
	"大小",
	"Size"
);
$device_select_str=array(
	"选择",
	"To Select"
);
$create_pv_str=array(
	"创建物理卷",
	"Create Physical Volume"
);

$pv_str=array(
	"物理卷",
	"Physical Volume"
);
$operate_str=array(
	"操作",
	"Operate"
);
$delete_str=array(
	"删除",
	"Delete"
);
$inuse_str=array(
	"正在使用",
	"In use"
);
$allocated_str=array(
	"已分配",
	"Allocated"
);
$ok_str=array(
	"成功",
	"OK"
);
$failed_str=array(
	"失败",
	"failed"
);
$create_pv_str=array(
	"创建物理卷",
	"Create physical volume"
);
$del_pv_str=array(
	"删除物理卷",
	"Delete physical volume"
);
$none_str=array(
	"- 无 -",
	"- None -"
);
$select_pv_tip_str=array(
	"请先选择磁盘!",
	"Please select disk(s) first!"
);
?>

<?php 
$message = "";
$log = new Log();
// 磁盘设备管理
$physical_volume = new PhysicalVolume();

if( isset($_POST['create_pv_submit']) && isset($_POST['blockdevices']) )
{
	$block_devive_list = $_POST['blockdevices'];
	foreach($block_devive_list as $entry)
	{
		$retval = $physical_volume->CreatePv($entry);
		if($retval===TRUE)
		{
			$message = $create_pv_str[$lang] . $ok_str[$lang];
			$log->VstorWebLog(LOG_INFOS, MOD_VOLUME, "create {$entry} as physical volume ok.");
			$log->VstorWebLog(LOG_INFOS, MOD_VOLUME, "创建{$entry}为物理卷成功。", CN_LANG);
		}
		else
		{
			$message = $create_pv_str[$lang] . $failed_str[$lang];
			$log->VstorWebLog(LOG_ERROR, MOD_VOLUME, "create {$entry} as physical volume failed.");
			$log->VstorWebLog(LOG_ERROR, MOD_VOLUME, "创建{$entry}为物理卷失败。", CN_LANG);
		}
	}
	// 刷新物理卷列表
	$physical_volume->RefreshPvList();
}

// 物理卷管理
$del_postfix = "_del_pv_sumbit";
$pv_list = array();
$pv_list = $physical_volume->GetPvList();
if( $pv_list !== FALSE )
{
	foreach( $pv_list as $entry )
	{
		$field = $entry['disk'] . $del_postfix;
		if( isset($_POST["$field"]) )
		{
			if( $physical_volume->RemovePv($entry['device']) !== FALSE )
			{
				$message = $del_pv_str[$lang] . $ok_str[$lang];
				$log->VstorWebLog(LOG_WARN, MOD_VOLUME, "remove physical volume {$entry['device']} ok.");
				$log->VstorWebLog(LOG_WARN, MOD_VOLUME, "删除物理卷{$entry['device']}成功。", CN_LANG);
			}
			else
			{
				$message = $del_pv_str[$lang] . $failed_str[$lang];
				$log->VstorWebLog(LOG_ERROR, MOD_VOLUME, "remove physical volume {$entry['device']} failed.");
				$log->VstorWebLog(LOG_ERROR, MOD_VOLUME, "删除物理卷{$entry['device']}失败。", CN_LANG);
			}
 			break;
		}
		else
		{
			continue;
		}
	}
	// 刷新物理卷列表
	$physical_volume->RefreshPvList();
}

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<link rel="stylesheet" href="css/target.css" type="text/css" />
<script defer type="text/javascript" src="js/pngfix.js"></script>
<script type="text/javascript">
function check_disk_select(msg)
{
	for (i = 0; i < document.getElementsByName("blockdevices[]").length; i++)
	{
		if(document.getElementsByName("blockdevices[]")[i].checked)
			return true;
	}
	alert(msg);
	return false;
}
</script>
</head>

<body>

<div id="logicgroup_target">

  <div id="logicgroup">
	  <table align="center" width="100%">
	  	<tr>
		<td class="bar_nopanel"><?php print $block_device_str[$lang];?></td>
		</tr>
	  </table>
	  	<form id="diskinfo_form" name="diskinfo_form" action="pv_target.php" method="post"> 
		  <table width="60%" border="0" cellpadding="6" align="center">
			<tr>
			  <td class="field_title"><?php print $device_str[$lang];?></td>
			  <td class="field_title"><?php print $device_size_str[$lang];?></td>
			  <td class="field_title"><?php print $device_select_str[$lang];?></td>
			</tr>
<?php 
// 获取磁盘列表
$device_list = array();
$bHasItem = FALSE;
$device_list = GetDiskListForPv();
if( count($device_list) != 0 )
{
	$td_class = "field_data1";
	foreach($device_list as $entry)
	{
		print "<tr>";
		print "<td class=\"{$td_class}\">" . $entry['name'] . "</td>";
		print "<td class=\"{$td_class}\">" . $entry['size'] . "</td>";
		print "<td class=\"{$td_class}\">";
		print "	<input type=\"checkbox\" name=\"blockdevices[]\" value=\"{$entry['path']}\">";
		print "</td>";
		print "</tr>\n";
		
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
	print "<tr><td class=\"field_data2\" colspan=\"3\">{$none_str[$lang]}</td></tr>";
}
?>
			<tr>
			  <td colspan="3">
			  	<input type="submit" name="create_pv_submit"  
			  	onClick="return check_disk_select('<?php print $select_pv_tip_str[$lang];?>');"  
			  	<?php 
			  	if( $bHasItem == FALSE)
			  	{
			  		print "disabled=\"disabled\"";
			  	}
			  	?>
			  	 value="<?php print $create_pv_str[$lang];?>"/>
			  </td>
			</tr>
		  </table>
		</form>
	<!--     -->
	  <table align="center" width="100%">
	  	<tr>
		<td class="bar_nopanel"><?php print $pv_str[$lang];?></td>
		</tr>
	  </table>

	  	<form id="lg_form" name="lg_form" action="pv_target.php" method="post"> 
		  <table width="60%" border="0" cellpadding="6" align="center">
			<tr>
			  <td class="field_title"><?php print $device_str[$lang];?></td>
			  <td class="field_title"><?php print $device_size_str[$lang];?></td>
			  <td class="field_title"><?php print $operate_str[$lang];?></td>
			</tr>
<?php 
$pv_list = array();
$pv_list = $physical_volume->GetPvList();
$bHasItem = FALSE;

if ( $pv_list != FALSE)
{
	$td_class = "field_data1";
	foreach($pv_list as $entry)
	{
		print "<tr>";
		print "<td class=\"{$td_class}\">" . $entry['name'] . "</td>";
		print "<td class=\"{$td_class}\">" . $entry['size'] . "</td>";

		print "<td class=\"{$td_class}\">";
		if( $entry['vg'] == "" )
		{
			print "<input type=\"submit\" name=\"{$entry['disk']}{$del_postfix}\" value=\"$delete_str[$lang]\">";
		}
		else
		{
			print $allocated_str[$lang];
		}
		print"</td>";
		print "</tr>\n";
		
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
	print "<tr><td class=\"field_data2\" colspan=\"3\">{$none_str[$lang]}</td></tr>";
}
?>
		  </table>
		</form>
<?php 
if($message != "")
	print_msg_block($message);
?>
	</div>
</div>

</body>
</html>
