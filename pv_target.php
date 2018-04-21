<?php
require("./include/authenticated.php");
require_once("./include/function.php");
require_once("./include/lvm.php");
require_once("./include/disk.php");
require_once("./include/log.php");

$lang=load_lang();

$block_device_str=array(
	"�����豸",
	"Block Devices"
);
$device_str=array(
	"�豸",
	"Device"
);
$device_size_str=array(
	"��С",
	"Size"
);
$device_select_str=array(
	"ѡ��",
	"To Select"
);
$create_pv_str=array(
	"���������",
	"Create Physical Volume"
);

$pv_str=array(
	"�����",
	"Physical Volume"
);
$operate_str=array(
	"����",
	"Operate"
);
$delete_str=array(
	"ɾ��",
	"Delete"
);
$inuse_str=array(
	"����ʹ��",
	"In use"
);
$allocated_str=array(
	"�ѷ���",
	"Allocated"
);
$ok_str=array(
	"�ɹ�",
	"OK"
);
$failed_str=array(
	"ʧ��",
	"failed"
);
$create_pv_str=array(
	"���������",
	"Create physical volume"
);
$del_pv_str=array(
	"ɾ�������",
	"Delete physical volume"
);
$none_str=array(
	"- �� -",
	"- None -"
);
$select_pv_tip_str=array(
	"����ѡ�����!",
	"Please select disk(s) first!"
);
?>

<?php 
$message = "";
$log = new Log();
// �����豸����
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
			$log->VstorWebLog(LOG_INFOS, MOD_VOLUME, "����{$entry}Ϊ�����ɹ���", CN_LANG);
		}
		else
		{
			$message = $create_pv_str[$lang] . $failed_str[$lang];
			$log->VstorWebLog(LOG_ERROR, MOD_VOLUME, "create {$entry} as physical volume failed.");
			$log->VstorWebLog(LOG_ERROR, MOD_VOLUME, "����{$entry}Ϊ�����ʧ�ܡ�", CN_LANG);
		}
	}
	// ˢ��������б�
	$physical_volume->RefreshPvList();
}

// ��������
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
				$log->VstorWebLog(LOG_WARN, MOD_VOLUME, "ɾ�������{$entry['device']}�ɹ���", CN_LANG);
			}
			else
			{
				$message = $del_pv_str[$lang] . $failed_str[$lang];
				$log->VstorWebLog(LOG_ERROR, MOD_VOLUME, "remove physical volume {$entry['device']} failed.");
				$log->VstorWebLog(LOG_ERROR, MOD_VOLUME, "ɾ�������{$entry['device']}ʧ�ܡ�", CN_LANG);
			}
 			break;
		}
		else
		{
			continue;
		}
	}
	// ˢ��������б�
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
// ��ȡ�����б�
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
