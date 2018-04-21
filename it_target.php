<?php
require("./include/function.php");
require("./include/authenticated.php");
require_once("./include/iscsi_target.php");
require_once("./include/lvm.php");
require_once("./include/log.php");

$lang=load_lang();

$it_name_prefix=NAME_PREFIX;

$it_setup_info=array(
	"iscsi-target������Ϣ",
	"iscsi-target Setup Information"
);
$create_new_it_str=array(
	"iscsi-target������(A-Z a-z 0-9 _)",
	"iscsi-target name(A-Z a-z 0-9 _)"
);
$select_it_str=array(
	"ѡ�����е�iscsi-target",
	"Select iscsi-target"
);
$setup_it_str=array(
	"����: ",
	"Setup: "
);
$mapped_lv_str=array(
	"��ӳ����߼�����iscsi-target: ",
	"LUNs mapped on iscsi-target: "
);
$unmapped_lv_str=array(
	"ӳ���µ��߼��� iscsi-target: ",
	"Map New LUNs to iscsi-target: "
);
$del_it=array(
	"ɾ��iscsi-target",
	"Delete iscsi-target"
);
$lun_id_str=array(
	"LUN ID",
	"LUN ID"
);
$lun_name_str=array(
	"LUN ����",
	"LUN Name"
);
$lun_path_str=array(
	"LUN ·��",
	"LUN Path"
);
$lun_size_str=array(
	"LUN ��С",
	"LUN Size"
);
$transfer_mode_str=array(
	"����ģʽ",
	"Transfer Mode"
);
$operate_str=array(
	"����",
	"Operation"
);
$unmap_str=array(
	"ȡ��ӳ��",
	"Unmap"
);
$map_str=array(
	"ӳ ��",
	"Map"
);
$create_str=array(
	"�� ��",
	"Create"
);
$select_str=array(
	"ѡ ��",
	"Select"
);
$create_it_str=array(
	"�½�iscsi-target ",
	"Create new iscsi-target "
);
$map_lun_str=array(
	"ӳ��LUN ",
	"Map LUN "
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
$allocated_str=array(
	"�ѷ���",
	"Allocated"
);
$iet_status_str=array(
	"����״̬��",
	"Connection State: "
);

$identifier_str=array(
	"��ʶ��",
	"ID"
);

$initiator_name_str=array(
	"Iscsi-initiator����",
	"Iscsi-initiator Name"
);
$inuse_str=array(
	"����ʹ��",
	"In use"
);
$connect_ip_str=array(
	"����IP",
	"Connected IP"
);
/* ������������������������������������������������������������������
 *˵����������ַ����в����\\n����Ϊ�˷�ֹphp��ֵ\n��jsʱ��
 *		�ѽ�\n��������js�Ĳ����������ݴ��� 
 */
$confirm_unmap_str=array(
	"ȡ��ӳ�������ѽ�����iscsi-target����ʧЧ�����¶�д����\\nȷ��ȡ��ӳ����",
	"Unmap lun will disable the connected iscsi-target!\\n Confirm to unmap the lun?"
);
$confirm_unmap_str2=array(
	"ȷ��ȡ��ӳ����",
	"To Unmap the Lun, confirm?"
);
$confirm_remove_it_str=array(
	"ɾ��iscsi-target������ѽ���������ʧЧ�����¶�д����\\nȷ��ɾ����",
	"Remove the iscsi-target will disable the connection with this iscsi-target! \\nConfirm to remove?"
);
$confirm_remove_it_str2=array(
	"ɾ�����ɻָ���ȷ�ϼ�����",
	"To remove iscsi-target, confirm?"
);
$input_password_str=array(
	"ִ�в�����Ҫ����Ա���룬�����룺",
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

// ѡ��iscsi-target����
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
// �½�iscsi-target
if( isset($_POST['it_name']) && trim($_POST['it_name'])!=="" )
{
	$name_select_it = $it_name_prefix . $_POST['it_name'];
	
	$retval = $iscsiTarget->Create($_POST['it_name']);
	if($retval !== TRUE)
	{
		$message = $create_it_str[$lang] . $failed_str[$lang];
		$log->VstorWebLog(LOG_ERROR, MOD_VOLUME, "create iscsi-target " . $name_select_it . " failed.");
		$log->VstorWebLog(LOG_ERROR, MOD_VOLUME, "����iscsi-target " . $name_select_it . "ʧ�ܡ�", CN_LANG);
	}
	else
	{
		$log->VstorWebLog(LOG_INFOS, MOD_VOLUME, "create iscsi-target " . $name_select_it . " ok.");
		$log->VstorWebLog(LOG_INFOS, MOD_VOLUME, "����iscsi-target " . $name_select_it . "�ɹ���", CN_LANG);
		header("Location: it_target.php?it={$name_select_it}");
	}
}

// ȡ��LUNӳ��
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
							$log->VstorWebLog(LOG_ERROR, MOD_VOLUME, "ȡ��ӳ��". $entry['path'] . "��iscsi-target " . $name_select_it . "ʧ�ܡ�", CN_LANG);
						}
						else
						{
							$log->VstorWebLog(LOG_WARN, MOD_VOLUME, "unmap ". $entry['path'] . " to iscsi-target " . $name_select_it . " ok.");
							$log->VstorWebLog(LOG_WARN, MOD_VOLUME, "ȡ��ӳ�� ". $entry['path'] . "��iscsi-target " . $name_select_it . "�ɹ���", CN_LANG);
						}
						break;
					}
				}
			}
		}
	}
}

// ɾ��iscsi-target
if ( isset($_POST['delete_it_submit']) )
{
	$retval = $iscsiTarget->Remove($name_select_it);
	if($retval !== TRUE)
	{
		$message = $del_it[$lang] . $failed_str[$lang];
		$log->VstorWebLog(LOG_ERROR, MOD_VOLUME, "delete iscsi-target " . $name_select_it . " failed.");
		$log->VstorWebLog(LOG_ERROR, MOD_VOLUME, "ɾ��iscsi-target " . $name_select_it . "ʧ�ܡ�", CN_LANG);
	}
	else
	{
		/*$message = $del_it[$lang] . $ok_str[$lang];
		print_msg_block($message);*/
		$log->VstorWebLog(LOG_WARN, MOD_VOLUME, "delete iscsi-target " . $name_select_it . " ok.");
		$log->VstorWebLog(LOG_WARN, MOD_VOLUME, "ɾ��iscsi-target " . $name_select_it . "�ɹ���", CN_LANG);
		header("Location: it_target.php");
	}
}

// ӳ��
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
				$log->VstorWebLog(LOG_ERROR, MOD_VOLUME, "ӳ��". $entry['path'] ."��iscsi-target " . $name_select_it . "ʧ�ܡ�", CN_LANG);
				$message = $map_lun_str[$lang] . $failed_str[$lang];
			}
			else
			{
				$log->VstorWebLog(LOG_INFOS, MOD_VOLUME, "map ". $entry['path'] ." to iscsi-target " . $name_select_it . " ok.");
				$log->VstorWebLog(LOG_INFOS, MOD_VOLUME, "ӳ��". $entry['path'] ."�� iscsi-target " . $name_select_it . "�ɹ���", CN_LANG);
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
  <!-- Iscsi-target������ѡ��  -->
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

<!-- iscsi-target����״̬ -->
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

<!-- �Ѿ�ӳ�䵽iscsi-target��lun��ɾ��iscsi-target -->
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
	
<!-- δӳ�䵽iscsi-target��lun  -->
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
	//��ӳ�䣬�Ͳ�������ӳ���ˣ�Ϊ�˷��㴦��initiator��iscsi-target���̷��Ķ�Ӧ��
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

