<?php
require("./include/function.php");
require("./include/authenticated.php");
require_once("./include/iscsi_target.php");
require_once("./include/lvm.php");
require_once("./include/log.php");

$lang=load_lang();

$it_name_prefix=NAME_PREFIX;

$it_setup_info=array(
	"iscsi-target配置信息",
	"iscsi-target Setup Information"
);
$create_new_it_str=array(
	"iscsi-target的名称(A-Z a-z 0-9 _)",
	"iscsi-target name(A-Z a-z 0-9 _)"
);
$select_it_str=array(
	"选择已有的iscsi-target",
	"Select iscsi-target"
);
$setup_it_str=array(
	"配置: ",
	"Setup: "
);
$mapped_lv_str=array(
	"已映射的逻辑卷在iscsi-target: ",
	"LUNs mapped on iscsi-target: "
);
$unmapped_lv_str=array(
	"映射新的逻辑卷到 iscsi-target: ",
	"Map New LUNs to iscsi-target: "
);
$del_it=array(
	"删除iscsi-target",
	"Delete iscsi-target"
);
$lun_id_str=array(
	"LUN ID",
	"LUN ID"
);
$lun_name_str=array(
	"LUN 名称",
	"LUN Name"
);
$lun_path_str=array(
	"LUN 路径",
	"LUN Path"
);
$lun_size_str=array(
	"LUN 大小",
	"LUN Size"
);
$transfer_mode_str=array(
	"传输模式",
	"Transfer Mode"
);
$operate_str=array(
	"操作",
	"Operation"
);
$unmap_str=array(
	"取消映射",
	"Unmap"
);
$map_str=array(
	"映 射",
	"Map"
);
$create_str=array(
	"新 建",
	"Create"
);
$select_str=array(
	"选 择",
	"Select"
);
$create_it_str=array(
	"新建iscsi-target ",
	"Create new iscsi-target "
);
$map_lun_str=array(
	"映射LUN ",
	"Map LUN "
);
$ok_str=array(
	"成功",
	"OK"
);
$failed_str=array(
	"失败",
	"failed"
);
$none_str=array(
	"- 无 -",
	"- None -"
);
$allocated_str=array(
	"已分配",
	"Allocated"
);
$iet_status_str=array(
	"连接状态：",
	"Connection State: "
);

$identifier_str=array(
	"标识符",
	"ID"
);

$initiator_name_str=array(
	"Iscsi-initiator名称",
	"Iscsi-initiator Name"
);
$inuse_str=array(
	"正在使用",
	"In use"
);
$connect_ip_str=array(
	"连接IP",
	"Connected IP"
);
/* ！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！
 *说明：下面的字符串中插入的\\n，是为了防止php传值\n给js时，
 *		已将\n解析而对js的参数产生传递错误。 
 */
$confirm_unmap_str=array(
	"取消映射会造成已建立的iscsi-target连接失效并导致读写错误！\\n确认取消映射吗？",
	"Unmap lun will disable the connected iscsi-target!\\n Confirm to unmap the lun?"
);
$confirm_unmap_str2=array(
	"确认取消映射吗？",
	"To Unmap the Lun, confirm?"
);
$confirm_remove_it_str=array(
	"删除iscsi-target会造成已建立的连接失效并导致读写错误！\\n确认删除？",
	"Remove the iscsi-target will disable the connection with this iscsi-target! \\nConfirm to remove?"
);
$confirm_remove_it_str2=array(
	"删除不可恢复，确认继续？",
	"To remove iscsi-target, confirm?"
);
$input_password_str=array(
	"执行操作需要管理员密码，请输入：",
	"To Implement the operation, please enter administrator's password:"
);
?>

<?php 
$message = "";
$unmap_postfix = "_unmap_submit";
$map_postfix = "_map_submit";
$bHasItem = FALSE;
$name_select_it = "";
$iscsiTarget = new Iscsi_target();
$logicVolume = new LogicVolume();
$log = new Log();

// 选择iscsi-target处理
$it_list = array();
$it_list = $iscsiTarget->GetItList();

if($it_list !== FALSE && count($it_list)>0 && !isset($_GET['it']))
{
	$name_select_it = $it_list[0]['name'];
}

if( isset($_GET['it']) )
{
	$name_select_it = $_GET['it'];
}
else if( isset($_POST['select_it']) )
{
	$name_select_it = $_POST['select_it'];
}
// 新建iscsi-target
if( isset($_POST['it_name']) && trim($_POST['it_name'])!=="" )
{
	$name_select_it = $it_name_prefix . $_POST['it_name'];
	
	$retval = $iscsiTarget->Create($_POST['it_name']);
	if($retval !== TRUE)
	{
		$message = $create_it_str[$lang] . $failed_str[$lang];
		$log->VstorWebLog(LOG_ERROR, MOD_VOLUME, "create iscsi-target " . $name_select_it . " failed.");
		$log->VstorWebLog(LOG_ERROR, MOD_VOLUME, "创建iscsi-target " . $name_select_it . "失败。", CN_LANG);
	}
	else
	{
		$log->VstorWebLog(LOG_INFOS, MOD_VOLUME, "create iscsi-target " . $name_select_it . " ok.");
		$log->VstorWebLog(LOG_INFOS, MOD_VOLUME, "创建iscsi-target " . $name_select_it . "成功。", CN_LANG);
		header("Location: it_target.php?it={$name_select_it}");
	}
}

// 取消LUN映射
$it_list = array();
$it_list = $iscsiTarget->GetItList();
if( $it_list !== FALSE)
{
	foreach($it_list as $it)
	{
		if($name_select_it == $it['name'])
		{
			if( isset($it['lun']) )
			{
				foreach($it['lun'] as $entry)
				{
					$field = $entry['id'] . $unmap_postfix;
					if( isset($_POST["$field"]) )
					{
						$retval = $iscsiTarget->Unmap($it['name'], $entry['id']);
						if($retval != TRUE)
						{
							$message = $unmap_str[$lang] . $failed_str[$lang];
							$log->VstorWebLog(LOG_ERROR, MOD_VOLUME, "unmap ". $entry['path'] . " to iscsi-target " . $name_select_it . " failed.");
							$log->VstorWebLog(LOG_ERROR, MOD_VOLUME, "取消映射". $entry['path'] . "到iscsi-target " . $name_select_it . "失败。", CN_LANG);
						}
						else
						{
							$log->VstorWebLog(LOG_WARN, MOD_VOLUME, "unmap ". $entry['path'] . " to iscsi-target " . $name_select_it . " ok.");
							$log->VstorWebLog(LOG_WARN, MOD_VOLUME, "取消映射 ". $entry['path'] . "到iscsi-target " . $name_select_it . "成功。", CN_LANG);
						}
						break;
					}
				}
			}
		}
	}
}

// 删除iscsi-target
if ( isset($_POST['delete_it_submit']) )
{
	$retval = $iscsiTarget->Remove($name_select_it);
	if($retval !== TRUE)
	{
		$message = $del_it[$lang] . $failed_str[$lang];
		$log->VstorWebLog(LOG_ERROR, MOD_VOLUME, "delete iscsi-target " . $name_select_it . " failed.");
		$log->VstorWebLog(LOG_ERROR, MOD_VOLUME, "删除iscsi-target " . $name_select_it . "失败。", CN_LANG);
	}
	else
	{
		/*$message = $del_it[$lang] . $ok_str[$lang];
		print_msg_block($message);*/
		$log->VstorWebLog(LOG_WARN, MOD_VOLUME, "delete iscsi-target " . $name_select_it . " ok.");
		$log->VstorWebLog(LOG_WARN, MOD_VOLUME, "删除iscsi-target " . $name_select_it . "成功。", CN_LANG);
		header("Location: it_target.php");
	}
}

// 映射
$unmapped_lv_list = array();
$unmapped_lv_list = $iscsiTarget->GetUnmappedLvList();
if($unmapped_lv_list != FALSE)
{
	foreach($unmapped_lv_list as $entry)
	{
		$field = $entry['vg'] . "_" . $entry['name'] . $map_postfix;
		if( isset($_POST["$field"]) )
		{
			$retval = $iscsiTarget->Map($name_select_it, $entry);
			if( $retval != TRUE )
			{
				$log->VstorWebLog(LOG_ERROR, MOD_VOLUME, "map ". $entry['path'] ." to iscsi-target " . $name_select_it . " failed.");
				$log->VstorWebLog(LOG_ERROR, MOD_VOLUME, "映射". $entry['path'] ."到iscsi-target " . $name_select_it . "失败。", CN_LANG);
				$message = $map_lun_str[$lang] . $failed_str[$lang];
			}
			else
			{
				$log->VstorWebLog(LOG_INFOS, MOD_VOLUME, "map ". $entry['path'] ." to iscsi-target " . $name_select_it . " ok.");
				$log->VstorWebLog(LOG_INFOS, MOD_VOLUME, "映射". $entry['path'] ."到 iscsi-target " . $name_select_it . "成功。", CN_LANG);
			}
			break;
		}
	}
}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<link rel="stylesheet" href="css/target.css" type="text/css" />
<script defer type="text/javascript" src="js/pngfix.js"></script>
<script defer type="text/javascript" src="js/function.js"></script>

<script type="text/javascript">
function do_confirm(msg)
{
	if( confirm(msg) )
		return true;
	else
		return false;
}
function SelectIt()
{
	document.sel_it_form.submit();
	return true;
}
function check_itname()
{
	var itname = document.create_it_form.it_name.value;
	if(itname == "")
	{
		document.create_it_form.create_it_submit.disabled = true;
		return true;
	}
	var retval = IsLvmNameOk(itname);
	if(retval == true)
	{
		document.getElementById("it_name_span").innerHTML = "<img src='./images/right_icon.png'></img>";
		document.create_it_form.create_it_submit.disabled = false;
	}
	else
	{
		document.getElementById("it_name_span").innerHTML = "<img src='./images/error_icon.png'></img>";
		document.create_it_form.create_it_submit.disabled = true;
	}
	return true;
}
</script>

</head>

<body>

<div id="iscsi_target">

  <div id="iscsi">
  <!-- Iscsi-target创建、选择  -->
	  <table align="center" width="100%">
	  	<tr>
		<td class="bar_nopanel"><?php print $it_setup_info[$lang]; ?></td>
		</tr>
	  </table>
	  	<form id="create_it_form" name="create_it_form" action="it_target.php" method="post"> 
		  <table width="80%" border="0" cellpadding="6" align="center">
			<tr>
				<td class="field_title_left" width="43%"><?php print $create_new_it_str[$lang]; ?></td>
				<td class="field_data1" align="left">
					<?php print $it_name_prefix;?>
					<input type="text" value="" name="it_name" onkeyup="return check_itname();" onchange="return check_itname();" size="12"  maxlength="12"/>
					<span id="it_name_span"><img src='./images/error_icon.png' style="visibility: hidden;"></img></span>
					<input type="submit" disabled="disabled" name="create_it_submit" value="<?php print $create_str[$lang]; ?>" />
				</td>
			</tr>
		  </table>
		  </form>
		  <form id="sel_it_form" name="sel_it_form" action="it_target.php" method="post">
		  <table width="80%" border="0" cellpadding="6" align="center">
			<tr>
			<td class="field_title_left"  width="43%"><?php print $select_it_str[$lang]; ?></td>
			  <td class="field_data2">
			    <select name="select_it" onChange="SelectIt();">
<?php 
	$it_list = array();
	$it_list = $iscsiTarget->GetItList();
	$bHasItem = FALSE;
	if( $it_list !== FALSE )
	{
		foreach($it_list as $entry)
		{
			if($entry['name'] == $name_select_it)
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
		</form>

<!-- iscsi-target连接状态 -->
	<table align="center" width="80%" height="26">
	<tr>
		<td class="title"><?php print $iet_status_str[$lang] . $name_select_it;?></td>
	</tr>
	</table>
	<table width="80%" border="0" cellpadding="6" align="center">
	<tr>
		<td class="field_title"><?php print $identifier_str[$lang];?></td>
		<td class="field_title"><?php print $initiator_name_str[$lang];?></td>
		<td class="field_title"><?php print $connect_ip_str[$lang];?></td>
	</tr>

<?php 
$bHasItem = FALSE;
$bConnected = FALSE;
$it_list = $iscsiTarget->GetItList();

if( $it_list !== FALSE )
{
	foreach( $it_list as $it )
	{
		if($it['name'] != $name_select_it)
		{
			continue;
		}

		$bHasItem = FALSE;
		if( isset($it['session']) )
		{
			$td_class = "field_data1";			
			foreach($it['session'] as $entry)
			{
				print "<tr>";
				print "<td class=\"{$td_class}\">{$entry['sid']}</td>";
				print "<td class=\"{$td_class}\">{$entry['initiator']}</td>";
				print "<td class=\"{$td_class}\">";
				print "<a href=\"http://{$entry['ip']}\" target=\"_blank\">{$entry['ip']}</a>";
				print "</td>";
				print "</tr>";
		
				$bConnected = TRUE;
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
}

if($bHasItem === FALSE)
{
	print "<tr><td class=\"field_data2\" colspan=\"3\">{$none_str[$lang]}</td></tr>";
}
?>
	</table>

<!-- 已经映射到iscsi-target的lun、删除iscsi-target -->
	  <p>  
	  <table align="center" width="80%" height="26" >
	  	<tr>
		<td class="title"><?php print $setup_it_str[$lang] . $name_select_it;?></td>
		</tr>
	  </table>

	  <form name="mapped_it" id="mapped_it" action="it_target.php<?php print "?it={$name_select_it}";?>" method="post">
	  	<table width="80%" border="0" cellpadding="6" align="center">
		  <tr>
			<td  style="color:royalblue;" align="left" colspan="4">
			<?php print $mapped_lv_str[$lang] . $name_select_it; ?>
			</td>
		  </tr>
		  <tr>
			<td class="field_title"><?php print $lun_id_str[$lang]; ?></td>
			<td class="field_title"><?php print $lun_name_str[$lang]; ?></td>
			<td class="field_title"><?php print $lun_size_str[$lang]; ?></td>
			<td class="field_title"><?php print $transfer_mode_str[$lang]; ?></td>
			<td class="field_title"><?php print $operate_str[$lang]; ?></td>
		  </tr>
		  
<?php 
$mapped_lun_list = $iscsiTarget->GetItLunList($name_select_it);
$bHasItem = FALSE;
if($mapped_lun_list !== FALSE)
{
	$td_class = "field_data1";
	foreach( $mapped_lun_list as $entry )
	{
		print "<tr>";
		print "<td class=\"{$td_class}\">{$entry['id']}</td>";
		print "<td class=\"{$td_class}\">{$entry['name']}</td>";
		print "<td class=\"{$td_class}\">{$entry['size']}</td>";
		print "<td class=\"{$td_class}\">{$entry['type']}</td>";
		print "<td class=\"{$td_class}\">";
		if( $bConnected )
			print "<input type=\"submit\" name=\"{$entry['id']}{$unmap_postfix}\" onClick=\"return do_confirm('{$confirm_unmap_str[$lang]}');\" value=\"{$unmap_str[$lang]}\" />";
		else
			print "<input type=\"submit\" name=\"{$entry['id']}{$unmap_postfix}\" onClick=\"return do_confirm('{$confirm_unmap_str2[$lang]}');\" value=\"{$unmap_str[$lang]}\" />";
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
if($bHasItem === FALSE)
{
	print "<tr><td class=\"field_data2\" colspan=\"5\">{$none_str[$lang]}</td></tr>";
}
?>
		  <tr>
		  <td colspan="5">
		  	<input type="submit" name="delete_it_submit" width="20px" 
		  	<?php
		  		if( $name_select_it === "" )
		  		{
		  			print " disabled=\"disabled\" ";
		  		}
				if( $bConnected )
				{
					print " onClick=\"return do_confirm('{$confirm_remove_it_str[$lang]}');\" ";
				}
				else
				{
					print " onClick=\"return do_confirm('{$confirm_remove_it_str2[$lang]}');\" ";
				}
		  	?>
		  	 value="<?php print $del_it[$lang]; ?>" />
		  </td>
		  </tr>
		</table>
	  </form>
	
<!-- 未映射到iscsi-target的lun  -->
	  <form name="unmapped_it" id="unmapped_it" action="it_target.php<?php print "?it={$name_select_it}";?>" method="post" >
	  	<table width="80%" border="0" cellpadding="6" align="center">
		  <tr>
			<td style="color:#CC3300;" colspan="4" align="left">
			<?php print $unmapped_lv_str[$lang] . $name_select_it; ?>
			</td>
		  </tr>
		  <tr>
			<td class="field_title"><?php print $lun_name_str[$lang]; ?></td>
			<td class="field_title"><?php print $lun_size_str[$lang];?></td>
			<td class="field_title"><?php print $operate_str[$lang]; ?></td>
		  </tr>
<?php 
$unmapped_lv_list = array();
$unmapped_lv_list = $iscsiTarget->GetUnmappedLvList();
$bHasItem = FALSE;
if($unmapped_lv_list !== FALSE)
{
	$disabled = "";
	//已映射，就不允许再映射了：为了方便处理initiator端iscsi-target和盘符的对应。
	if( $name_select_it == "" || $iscsiTarget->IsLunMapped($name_select_it)===TRUE)
	{
		$disabled = "disabled=\"disabled\"";
	}
	$td_class = "field_data1";
	foreach( $unmapped_lv_list as $entry )
	{
		print "<tr>";
		print "<td class=\"{$td_class}\">{$entry['name']}</td>";
		print "<td class=\"{$td_class}\">{$entry['size']}</td>";
		print "<td class=\"{$td_class}\">";
		print "<input type=\"submit\" {$disabled} name=\"{$entry['vg']}_{$entry['name']}{$map_postfix}\" value=\"{$map_str[$lang]}\" />";
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
if($bHasItem === FALSE)
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

