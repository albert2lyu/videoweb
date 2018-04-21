<?php
require_once("./include/function.php");
require_once("./include/authenticated.php");
require_once("./include/lvm.php");
require_once("./include/log.php");


$lang=load_lang();

$sel_vg_str=array(
	"ѡ�����",
	"Select Logic Volume Group"
);
$sel_vg_tip=array(
	"ѡ����Ҫ��ʾ�����õľ��顣",
	"Select to view the Logic volume group."
);
$select_vg=array(
	"ѡ��",
	"Select"
);
$vg_name_str=array(
	"��������",
	"Volume Group Name"
);
$total_size=array(
	"�ܴ�С",
	"Total Size"
);
$left_size=array(
	"ʣ���С",
	"Unused Size"
);
$vg_info_str=array(
	"�����е��߼�����Ϣ",
	"Logic Volume Group Information"
);
$volume_name=array(
	"����",
	"Volume Name"
);
$volume_size=array(
	"��С",
	"Size"
);
$volume_fs=array(
	"�ļ�ϵͳ����",
	"Filesystem"
);
$operate=array(
	"����",
	"Opreration"
);
$delete=array(
	"ɾ ��",
	"Remove"
);
$create_new_lv_str=array(
	"�����µ��߼����� ",
	"Create New Logic Volume on "
);
$set_lv_name=array(
	"�߼������ƣ���Ч�ַ�A-Z a-z 0-9 _����lv_0��",
	"Logic Volume Name(Valid character:A-Z a-z 0-9 _, lv_0 e.g.)"
);
$set_lv_size=array(
	"�趨�߼����С",
	"Set size"
);
$set_lv_fs=array(
	"�ļ�ϵͳ����",
	"Filesystem Type"
);
$create_lv_str=array(
	"�����߼���",
	"Create Logic Volume"
);
$remove_lv_str=array(
	"ɾ���߼���",
	"Remove logic volume"
);
$ok_str=array(
	"�ɹ�",
	"OK"
);
$failed_str=array(
	"ʧ��",
	"failed"
);
$none_str=array(
	"- �� -",
	"- None -"
);
$inuse_str=array(
	"����ʹ��",
	"In use"
);
$allocated_str=array(
	"�ѷ���",
	"Allocated"
);
$size_set_error_str=array(
	"�߼����С���ô���",
	"Set logical volume size error!"
);
?>
<?php 
$message = "";
$del_postfix = "_del_submit";
$bHasItem = FALSE;
$name_select_vg = "";
$logicVolumeGroup = new LogicVolumeGroup();
$logicVolume = new LogicVolume();
$log = new Log();

// ѡ����鴦��
$vg_list = array();
$vg_list = $logicVolumeGroup->GetVgList();

if($vg_list != FALSE && count($vg_list)>0 && !isset($_GET['vg']))
{
	$name_select_vg = $vg_list[0]['name'];
}
if( isset($_GET['vg']) )
{
	$name_select_vg = $_GET['vg'];
}
else if( isset($_POST['select_vg']) )
{
	$name_select_vg = $_POST['select_vg'];
}


// ɾ���߼�����
if( $vg_list != FALSE )
{
	$bFind = FALSE;
	foreach( $vg_list as $vg_entry )
	{
		$lv_list_vg = array();
		$lv_list_vg = $logicVolumeGroup->GetLvListOfVg($vg_entry['name']);
		if( $lv_list_vg != FALSE )
		{
			foreach( $lv_list_vg as $lv_entry )
			{
				$field = $vg_entry['name'] . "_" . $lv_entry['name'] . $del_postfix;
				if( isset($_POST["$field"]) )
				{
					$retval = $logicVolume->RemoveLv($lv_entry['name'], $vg_entry['name']);
					if( $retval != TRUE )
					{
						$message = $remove_lv_str[$lang] . " " . $vg_entry['name'] . "/" . $lv_entry['name'] . " " . $failed_str[$lang];
						$log->VstorWebLog(LOG_ERROR, MOD_VOLUME, "delete logic volume ".$lv_entry['name']." of ".$vg_entry['name']." failed.");
						$log->VstorWebLog(LOG_ERROR, MOD_VOLUME, "ɾ������" . $vg_entry['name'] . "�е��߼���" . $lv_entry['name'] . "ʧ�ܡ�", CN_LANG);
					}
					else
					{
						$log->VstorWebLog(LOG_WARN, MOD_VOLUME, "delete logic volume ".$lv_entry['name']." of ".$vg_entry['name']." ok.");
						$log->VstorWebLog(LOG_WARN, MOD_VOLUME, "ɾ������" . $vg_entry['name'] . "�е��߼���" . $lv_entry['name'] . "�ɹ���", CN_LANG);
					}
					$bFind = TRUE;
					break;
				}
			}
		}
		if($bFind)
			break;
	}
}

// �����߼���
if( isset($_POST['create_lv_submit']) && isset($_POST['lv_name']) && isset($_POST['lv_size']) )
{
	$lv_name = $_POST['lv_name'];
	$lv_size = $_POST['lv_size'];
	$lv_unit = $_POST['lv_size_unit_select'];
	if($lv_unit == "G")
		$lv_size = $lv_size * 1024;
	$name = $name_select_vg . "_" . $lv_name;
	$retval = $logicVolume->CreateLv($name, $lv_size, $name_select_vg);
	if( $retval != TRUE )
	{
		$message = $create_new_lv_str[$lang] . $name_select_vg . " " . $failed_str[$lang];
		$log->VstorWebLog(LOG_ERROR, MOD_VOLUME, "create logic volume ".$name." of ".$name_select_vg." failed.");
		$log->VstorWebLog(LOG_ERROR, MOD_VOLUME, "�ھ���" . $name_select_vg . "�ϴ����߼���" . $name . "ʧ�ܡ�", CN_LANG);
	}
	else
	{
		$log->VstorWebLog(LOG_INFOS, MOD_VOLUME, "create logic volume ".$name." of ".$name_select_vg." ok.");
		$log->VstorWebLog(LOG_INFOS, MOD_VOLUME, "�ھ���" . $name_select_vg . "�ϴ����߼���" . $name . "�ɹ���", CN_LANG);
	}
}
?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<link rel="stylesheet" href="css/target.css" type="text/css" />
<script defer type="text/javascript" src="js/pngfix.js"></script>
<script defer type="text/javascript" src="js/function.js"></script>
<script defer type="text/javascript" src="js/basic.js"></script>
<script type="text/javascript">
function SelectVg()
{
	document.sel_vg_form.submit();
	return true;
}
function check_lvname()
{
	var lvname = document.create_lv_form.lv_name.value;
	if(lvname == "")
	{
		document.create_lv_form.create_lv_submit.disabled = true;
		return true;
	}
	var retval = IsLvmNameOk(lvname);
	if(retval == true)
	{
		document.getElementById("lv_name_span").innerHTML = "<img src='./images/right_icon.png'></img>";
		document.create_lv_form.create_lv_submit.disabled = false;
	}
	else
	{
		document.getElementById("lv_name_span").innerHTML = "<img src='./images/error_icon.png'></img>";
		document.create_lv_form.create_lv_submit.disabled = true;
	}
	return true;
}
function check_lv_size(obj)
{
	if(obj.value.Trim() == "")
	{
		alert('<?php print $size_set_error_str[$lang];?>');
		obj.value = "";
		obj.focus();
		return false;
	}
	value = obj.value;
	
	// �ж���Ч�ַ�
	var valid_char = "0123456789.";
	for(var i=0; i<value.length; i++)
	{
		var chr = value.charAt(i);
		if( valid_char.indexOf(chr) == -1 )
		{
			alert('<?php print $size_set_error_str[$lang];?>');
			obj.focus();
			obj.select();
			return false;
		}
	}
}
</script>
</head>

<body>

<div id="logicvolume_target">

  <div id="logicvolume">
	  <table align="center" width="100%">
	  	<tr>
		<td class="bar_nopanel"><?php print $sel_vg_str[$lang]; ?></td>
		</tr>
	  </table>
	  	<form id="sel_vg_form" name="sel_vg_form" action="lv_target.php" method="post"> 
		  <table width="70%" border="0" cellpadding="6" align="center">
			<tr>
				<td class="tip_data">
					<img src="images/tip.gif" />
					<?php print $sel_vg_tip[$lang]; ?>
				</td>
			</tr>
			<tr>
			  <td class="field_data1">
			    <select name="select_vg" onChange="SelectVg();">
<?php 
$vg_list = array();
$vg_list = $logicVolumeGroup->GetVgList();
if($vg_list !== FALSE)
{
	foreach( $vg_list as $entry )
	{
		if($entry['name'] == $name_select_vg)
		{
			print "<option value=\"{$entry['name']}\" selected>{$entry['name']}";
		}
		else
		{
			print "<option value=\"{$entry['name']}\">{$entry['name']}";
		}
		$bHasItem = TRUE;
	}
}
?>
				</select>
			  </td>
			</tr>
		</table>
			
		  <table width="70%" border="0" cellpadding="6" align="center">
			<tr>
				<td class="field_title"><?php print $vg_name_str[$lang]; ?></td>
				<td class="field_title"><?php print $total_size[$lang]; ?></td>
				<td class="field_title"><?php print $left_size[$lang]; ?></td>
			</tr>
<?php 
$vg_selected = array();
if( $name_select_vg != "" )
{
	// ��������Ӧ�ľ���
	foreach( $vg_list as $entry )
	{
		if($name_select_vg == $entry['name'])
		{
			$vg_selected = $entry;
			break;
		}
	}
	print "<tr>";
	print "<td class=\"field_data1\">{$vg_selected['name']}</td>";
	print "<td class=\"field_data1\">{$vg_selected['size']}</td>";
	print "<td class=\"field_data1\">{$vg_selected['free']}</td>";
	print "</tr>";
}
else
{
	print "<tr>";
	print "<td class=\"field_data2\">-</td>";
	print "<td class=\"field_data2\">-</td>";
	print "<td class=\"field_data2\">-</td>";
	print "</tr>";
}
?>
			</table>
		</form>
		
	  <form name="op_vg_form" id="op_vg_form" action="lv_target.php<?php print "?vg=". $name_select_vg;?>" method="post">
	  
	  <table align="center" width="70%" height="26" >
	  	<tr>
		<td class="title">
		<?php print $name_select_vg . " : " . $vg_info_str[$lang]; ?>
		</td>
		</tr>
	  </table>
	  
	  <table width="70%" border="0" cellpadding="6" align="center">
		<tr>
			<td class="field_title"><?php print $volume_name[$lang]; ?></td>
			<td class="field_title"><?php print $volume_size[$lang]; ?></td>
			<!-- <td class="field_title"><?php //print $volume_fs[$lang]; ?></td> -->
			<td class="field_title"><?php print $operate[$lang]; ?></td>
		</tr>
<?php 
if( $name_select_vg != "" )
{
	$lv_list_vg = array();
	$lv_list_vg = $logicVolumeGroup->GetLvListOfVg($vg_selected['name']);
	$bHasItem = FALSE;
	if( $lv_list_vg != FALSE )
	{
		$td_class = "field_data1";
		foreach( $lv_list_vg as $entry )
		{
			print "<tr>";
			print "<td class=\"{$td_class}\">{$entry['name']}</td>";
			print "<td class=\"{$td_class}\">{$entry['size']}</td>";
			print "<td class=\"{$td_class}\">";
			if( !$logicVolume->IsAsItLun($entry['vg'], $entry['name']) && $entry['mountdir']=="" )
			{
				print "<input type=\"submit\" name=\"{$entry['vg']}_{$entry['name']}{$del_postfix}\" value=\"{$delete[$lang]}\" />";
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
}
if($bHasItem == FALSE)
{
	print "<tr><td class=\"field_data2\" colspan=\"3\">{$none_str[$lang]}</td></tr>";
}
?>
		</table>

	  </form>
	  <table align="center" width="100%">
	  	<tr>
		<td class="bar_nopanel"><?php print $create_new_lv_str[$lang] . $name_select_vg; ?></td>
		</tr>
	  </table>
	  	<form id="create_lv_form" name="create_lv_form" action="lv_target.php<?php print "?vg=". $name_select_vg;?>" method="post"> 
		  <table border="0" cellpadding="6" align="center" width="70%">
			<tr>
			  <td class="field_title">
			  	<?php print $set_lv_name[$lang]; ?>
			  </td>
			  <td class="field_data1">
			  	<?php print $name_select_vg . "_";?>
			  	<input type="text" size="10"  maxlength="8" onKeyUp="return check_lvname();" onChange="return check_lvname();" name="lv_name" value="" />
			  	<span id="lv_name_span"><img src='./images/error_icon.png' style="visibility: hidden;"></img></span>
			  </td> 
			</tr>
			<tr>
			  <td class="field_title">
			  	<?php print $set_lv_size[$lang]; ?>
			  </td>
			  <td class="field_data2">
			  	<input type="text" size="10" name="lv_size" value="" />
			  	<select name="lv_size_unit_select">
			  	<option value="M">MB</option>
			  	<option value="G">GB</option>			  	
			  	</select>
			  </td> 
			</tr>
<!-- 
			<tr>
			  <td class="field_title">
			  	<?php //print $set_lv_fs[$lang]; ?>
			  </td>
			  <td class="field_data1">
			  	<select name="lv_fs_sel">
			  		<option value="iSCSI">iSCSI
					<option value="xfs">XFS					
				</select>
			  </td> 
			</tr>
-->
			<tr>
			  <td colspan="2">
			  	<input type="submit" name="create_lv_submit" 
			  	disabled="disabled" 
			  	onClick="return check_lv_size(document.create_lv_form.lv_size);" "
			  	<?php
			  	 if( $name_select_vg == "" )
			  	 {
			  	 	print "disabled=\"disabled\"";
			  	 }
			  	 ?>
			  	 value="<?php print $create_lv_str[$lang]; ?>"/>
			  </td>
			</tr>
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

