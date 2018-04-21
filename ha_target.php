<?php
require_once("./include/function.php");
require_once("./include/network.php");
require_once("./include/vis_ha.php");
require_once("./include/log.php");

$lang=load_lang();

$vis_ha_setup_str=array(
	"VIS�ȱ�����",
	"VIS High-Availability Setup(HA)"
);
$node_config_str=array(
	"�ڵ�����",
	"Node Config"
);
$node_type_str=array(
	"�ڵ�����",
	"Node Type"
);
$ip_address_str=array(
	"IP��ַ",
	"IP Address"
);
$hostname_str=array(
	"��������",
	"Host Name"
);
$nic_dev_select_str=array(
	"�����豸ѡ��",
	"Network Device"
);
$use_local_str=array(
	"ʹ�ñ���",
	"Use Local Host"
);
$active_node_str=array(
	"��������",
	"Master Node"
);
$standby_node_str=array(
	"�ӷ�����",
	"Slave Node"
);
$cluster_config_str=array(
	"��Ⱥ����",
	"Cluster Config"
);
$active_node_hostname_str=array(
	"���������ڵ�����",
	"Master Node Hostname"
);
$cluster_ip_str=array(
	"��ȺIP��ַ",
	"Cluster IP Address"
);
$netmask_str=array(
	"��������",
	"Netmask"
);
$enable_ha_str=array(
	"����VIS�ȱ�",
	"Enable VIS HA"
);
$disable_ha_str=array(
	"ͣ��VIS�ȱ�",
	"Stop VIS HA"
);
$vis_ha_info_str=array(
	"VIS�ȱ�������Ϣ",
	"VIS High-Availability(HA) Information"
);
$node_info_str=array(
	"�ڵ���Ϣ",
	"Node Information"
);
$cluster_info_str=array(
	"��Ⱥ��Ϣ",
	"Cluster Information"
);
$enable_ha_confirm_str=array(
	"ȷ������VIS�ȱ���",
	"Confirm to continue?"
);
$need_complete_info_str=array(
	"��Ҫ����Ϣ��д����",
	"Need complete information"
);
$disable_ha_confirm_str=array(
	"ȷ��ͣ��VIS�ȱ���",
	"Confirm to continue?"
);
$enable_ha_ok_str=array(
	"����VIS�ȱ��ɹ���",
	"Enable VIS HA OK."
);
$enable_ha_failed_str=array(
	"����VIS�ȱ�ʧ�ܣ�",
	"Enable VIS HA failed!"
);
$disable_ha_ok_str=array(
	"ͣ��VIS�ȱ��ɹ���",
	"Stop VIS HA OK."
);
$disable_ha_failed_str=array(
	"ͣ��VIS�ȱ�ʧ�ܣ�",
	"Stop VIS HA failed!"
);
$ip_error_str=array(
	"IP����ȷ��",
	"IP address is incorrect!"
);
$hostname_same_error_str=array(
	"�����ӷ������������Ʋ�����ͬ��",
	"The hostname of master and slave node must be not the same!"
);
?>

<?php
$message="";
$log = new Log();
$network = new NetWork();
$vis_ha = new VisHa();
// ��ȡ���������б�
$netmask_list = $network->GenerateNetmasks();
// ��ȡ����IP����������
$hostname = $network->GetHostname();
$hostip = $_SERVER['SERVER_ADDR'];
// ��ȡ�����ӵġ�������IP�������豸��������slave device
$nic_devices = $network->ListDevices(1);
$active_devices = array();
$active_devices_ip = array();
foreach($nic_devices as $entry)
{
	if(preg_match("/bond/i", $entry))
	{
		$bond = new Bond($entry);
		$bond_nic = new NetworkCard($entry);
		$slaves = $bond->ListSlaves();
		if($bond_nic->GetIP() === FALSE)
		{
			continue;
		}
		if($slaves !== FALSE)
		{
			foreach($slaves as $slave)
			{
				$nic = new NetworkCard($slave);
				if( $nic->IsConnected()  )
				{
					$active_devices[] = $entry;
					$active_devices_ip[] = $bond_nic->GetIP();
					break;
				}
			}
		}
	}
	else
	{
		$nic = new NetworkCard($entry);
		if($nic->GetIP() === FALSE)
		{
			continue;
		}
		if( $nic->IsConnected() )
		{
			$active_devices[] = $entry;
			$active_devices_ip[] = $nic->GetIP();
		}
	}
}
?>

<?php 
/*
 * ���������� ---------------------
 */


// ͣ��HA
if( isset($_POST['disable_ha_submit']) )
{
	if( $vis_ha->DisableVisHa() === TRUE )
	{
		// NOTHING
	}
	else
	{
		$message = $disable_ha_failed_str[$lang];
	}
}

// ����HA
if( isset($_POST['enable_ha_submit']) )
{
	$act_name = $_POST['active_hostname'];
	$act_ip = $_POST['active_ip'];
	$act_bcast = $_POST['nic_select_active'];
	$use_local = $_POST['use_localhost'];
	$std_name = $_POST['standby_hostname'];
	$std_ip = $_POST['standby_ip'];
	$std_bcast = $_POST['nic_select_standby'];
	$ha_act_name = $_POST['ha_active_hostname'];
	$ha_ip = $_POST['ha_ip'];
	$ha_prefix = $_POST['ha_netmask_list'];
	
	$bcastdev = "";
	if($use_local == "active")
	{
		$bcastdev = $act_bcast;
	}
	else if($use_local == "standby")
	{
		$bcastdev = $std_bcast;
	}
	
	$broadcast = substr($ha_ip, 0, (strrpos($ha_ip, ".")+1)) . "255";
	if(
		$vis_ha->SetHacfInfo($act_ip, $act_name, $std_ip, $std_name, $bcastdev) === TRUE && 
		$vis_ha->SetHaResourcesInfo($ha_act_name, $ha_ip, $ha_prefix, $broadcast) === TRUE && 
		$vis_ha->EnableVisHa() === TRUE
	  )
	{
		$log->VstorWebLog(LOG_INFOS, MOD_VIS, "set  High-Availability ok.");
		$log->VstorWebLog(LOG_INFOS, MOD_VIS, "�����ȱ��ɹ���", CN_LANG);
	}
	else
	{
		$message = $enable_ha_failed_str[$lang];
		$log->VstorWebLog(LOG_ERROR, MOD_VIS, "set High-Availability failed.");
		$log->VstorWebLog(LOG_ERROR, MOD_VIS, "�����ȱ�ʧ�ܡ�", CN_LANG);
	}
}

/*
 *  --------------------- ����������
 */

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
	{
		return true;
	}
	else
	{
		return false;
	}

	return true;
}

function enable_ha_confirm(msg)
{
	// �����Ϣ�Ƿ���д����
	var value1 = document.ha_form.active_hostname.value;
	var value2 = document.ha_form.active_ip.value;
	if(IsIpOk(value2) == false)
	{
		alert('<?php print $ip_error_str[$lang];?>');
		document.ha_form.active_ip.focus();
		document.ha_form.active_ip.select();
		return false;
	}
	var value3 = document.ha_form.standby_hostname.value;
	var value4 = document.ha_form.standby_ip.value;
	if(IsIpOk(value4) == false)
	{
		alert('<?php print $ip_error_str[$lang];?>');
		document.ha_form.standby_ip.focus();
		document.ha_form.standby_ip.select();
		return false;
	}
	var i = 0;
	for (; i < document.getElementsByName("use_localhost").length; i++)
	{
		if(document.getElementsByName("use_localhost")[i].checked)
			host = document.getElementsByName("use_localhost")[i].value;
	}

	var value5 = "";
	if(host == "active")
	{
		value5 = document.ha_form.nic_select_active.value;
	}
	else if( host == "standby")
	{
		value5 = document.ha_form.nic_select_standby.value;
	}

	var value6 = document.ha_form.ha_active_hostname.value;
	var value7 = document.ha_form.ha_ip.value;
	if(IsIpOk(value7) == false)
	{
		alert('<?php print $ip_error_str[$lang];?>');
		document.ha_form.ha_ip.focus();
		document.ha_form.ha_ip.select();
		return false;
	}
	var bEmpty = false;

	var value = new Array(value1, value2, value3, value4, value5, value6, value7);
	i = 0;
	for(; i<value.length; i++)
	{
		if( value[i] == "")
		{
			bEmpty = true;
			break;
		}	
	}

	if(i == value.length)
	{
		bEmpty = false;
	}

	if( bEmpty == true )
	{
		alert('<?php print $need_complete_info_str[$lang];?>');
		return false;
	}

	//��������ӷ����������������Ƿ���ͬ
	if( value1 == value3 )
	{
		alert('<?php print $hostname_same_error_str[$lang];?>');
		return false;
	}
	
	if( confirm(msg) )
	{
		return true;
	}
	else
	{
		return false;
	}

	return false;	
}

function input_active_hostname()
{
	var hostname = document.ha_form.active_hostname.value;
	document.ha_form.ha_active_hostname.value = hostname;
	return true;
}

function select_localhost()
{
	for (var i = 0; i < document.getElementsByName("use_localhost").length; i++)
	{
		if(document.getElementsByName("use_localhost")[i].checked)
			value = document.getElementsByName("use_localhost")[i].value;
	}
	
	if(value == "active")
	{
		document.ha_form.active_hostname.readOnly=true;
		document.ha_form.standby_hostname.readOnly=false;
		document.ha_form.active_ip.readOnly=true;
		document.ha_form.standby_ip.readOnly=false;
		
		select_nic(document.ha_form.nic_select_active);
		document.ha_form.active_hostname.value = "<?php print $hostname;?>";
		
		document.ha_form.standby_ip.value = "";
		document.ha_form.standby_hostname.value = "";
		
		document.ha_form.nic_select_active.style.visibility = "visible";
		document.ha_form.nic_select_standby.style.visibility = "hidden";
	}
	else if(value == "standby")
	{
		document.ha_form.active_hostname.readOnly=false;
		document.ha_form.standby_hostname.readOnly=true;
		document.ha_form.active_ip.readOnly=false;
		document.ha_form.standby_ip.readOnly=true;
		
		select_nic(document.ha_form.nic_select_standby);
		document.ha_form.standby_hostname.value = "<?php print $hostname;?>";
		
		document.ha_form.active_ip.value = "";
		document.ha_form.active_hostname.value = "";
		
		document.ha_form.nic_select_active.style.visibility  = "hidden";
		document.ha_form.nic_select_standby.style.visibility = "visible";
	}
	else
	{
		return false;
	}
	// �޸ļ�Ⱥ�Ļ�Ծ��������Ϣ
	input_active_hostname();
	
	return true;
}
function select_nic(obj)
{
	device = obj.value;
	<?php 
	$devlist_str = "";
	$iplist_str = "";
	// ��ȡÿ���豸��IP
	foreach($active_devices as $dev)
	{
		$devlist_str .= $dev . ";";
	}
	foreach($active_devices_ip as $ip)
	{
		$iplist_str .= $ip . ";";
	}
	?>

	var devlist_str = "<?php print $devlist_str;?>";
	var iplist_str = "<?php print $iplist_str;?>";
	var dev_array = new Array();
	var ip_array = new Array();
	dev_array = devlist_str.split(";");
	ip_array  = iplist_str.split(";");

	for (var i = 0; i < document.getElementsByName("use_localhost").length; i++)
	{
		if(document.getElementsByName("use_localhost")[i].checked)
			hostvalue = document.getElementsByName("use_localhost")[i].value;
	}
	
	for(var i=0; i<dev_array.length; i++)
	{
		if(dev_array[i]==device)
		{
			if(hostvalue == "active")
			{
				document.ha_form.active_ip.value = ip_array[i];
			}
			else if(hostvalue == "standby")
			{
				document.ha_form.standby_ip.value = ip_array[i];
			}
			return true;
		}
	}

	return true;
}

</script>
</head>

<body>

<?php 
// ���HAû�����ã�����ʾ���ý���
if( ! $vis_ha->IsVisHaEnabled() )
{
	print "
	<form name=\"ha_form\" id=\"ha_form\" action=\"ha_target.php\" method=\"post\">
	<table align=\"center\" width=\"100%\">
		<tr>
		<td class=\"bar_nopanel\">{$vis_ha_setup_str[$lang]}</td>
		</tr>
	</table>
	
	<table width=\"80%\" border=\"0\" align=\"center\" cellpadding=\"6\">
	  <tr>
	    <td colspan=\"5\" class=\"title\">{$node_config_str[$lang]}</td>
	  </tr>
	  <tr>
	    <td class=\"field_title\">{$node_type_str[$lang]}</td>
	    <td class=\"field_title\">{$ip_address_str[$lang]}</td>
	    <td class=\"field_title\">{$hostname_str[$lang]}</td>
	    <td class=\"field_title\">{$nic_dev_select_str[$lang]}</td>
		<td class=\"field_title\">{$use_local_str[$lang]}</td>
	  </tr>
	  <tr>
	    <td class=\"field_title\">{$active_node_str[$lang]}</td>
	    <td class=\"field_data1\">
			<input type=\"text\" name=\"active_ip\" id=\"active_ip\" value=\"\" maxlength=\"16\" size=\"20\" >
		</td>
	    <td class=\"field_data2\">
	    	<input type=\"text\" name=\"active_hostname\" id=\"active_hostname\" onKeyUp=\"return input_active_hostname();\" value=\"\" maxlength=\"16\" size=\"20\" >
	    </td>
	    <td class=\"field_data1\">
			<select name=\"nic_select_active\" id=\"nic_select_active\"  onChange=\"return select_nic(this);\">
		";
	
	foreach($active_devices as $entry)
	{
		print "<option value=\"{$entry}\">{$entry}";
	}
	
	print "
			</select>	
		</td>
		<td class=\"field_data2\">
			<input type=\"radio\" name=\"use_localhost\" checked=\"checked\" onClick=\"return select_localhost();\" value=\"active\">	
		</td>
	  </tr>
	  <tr>
	    <td class=\"field_title\">{$standby_node_str[$lang]}</td>
	    <td class=\"field_data1\">
			<input type=\"text\" name=\"standby_ip\" id=\"standby_ip\" value=\"\" maxlength=\"16\" size=\"20\" >
		</td>
	    <td class=\"field_data2\">
	    	<input type=\"text\" name=\"standby_hostname\" id=\"standby_hostname\" value=\"\" maxlength=\"16\" size=\"20\" >
	    </td>
	    <td class=\"field_data1\">
			<select name=\"nic_select_standby\" id=\"nic_select_standby\" onChange=\"return select_nic(this);\">
		";
	
	foreach($active_devices as $entry)
	{
		print "<option value=\"{$entry}\">{$entry}";
	}
		
	print "
			</select>	
		</td>
		<td class=\"field_data2\">
			<input type=\"radio\" name=\"use_localhost\" onClick=\"return select_localhost();\" value=\"standby\">
		</td>
	  </tr>
	</table>
	
	<table width=\"80%\" border=\"0\" align=\"center\" cellpadding=\"6\">
	  <tr>
	    <td colspan=\"4\" class=\"title\">{$cluster_config_str[$lang]}</td>
	  </tr>
	  <tr>
	    <td class=\"field_title\">{$active_node_hostname_str[$lang]}</td>
	    <td class=\"field_title\">{$cluster_ip_str[$lang]}</td>
	    <td class=\"field_title\">{$netmask_str[$lang]}</td>
	  </tr>
	  <tr>
	    <td class=\"field_data1\">
			<input type=\"text\" name=\"ha_active_hostname\" id=\"ha_active_hostname\" maxlength=\"16\" size=\"20\" readonly=\"readonly\" >	
		</td>
	    <td class=\"field_data2\">
			<input type=\"text\" name=\"ha_ip\" id=\"ha_ip\" maxlength=\"16\" size=\"20\">
		</td>
	    <td class=\"field_data1\">
		<select name=\"ha_netmask_list\" id=\"ha_netmask_list\">
		";
	
		print_netmask_of_select_byprefix();
		
	print "
		</select>
		</td>
	  </tr>
	  <tr>
	    <td colspan=\"3\">
			<input type=\"submit\" onClick=\"return enable_ha_confirm('{$enable_ha_confirm_str[$lang]}');\" name=\"enable_ha_submit\" value=\"{$enable_ha_str[$lang]}\">
		</td>
	  </tr>
	</table>
	</form>
	<script type=\"text/javascript\">
		select_localhost();
	</script>
	";
}
///////////////////////////////////////////////////////////////////
/*
 * �Ѿ�����HA����ʾHA��Ϣ����
 */
///////////////////////////////////////////////////////////////////
else
{
	$hainfo = $vis_ha->GetVisHaInfo();
	if($hainfo === FALSE)
	{
		//$vis_ha->DisableVisHa();
		//header("Location: ./ha_target.php");
	}
/*
 	��Ϣ���б�
 	array(
 		"active"=>array(
 			"hostname"=>"VIS230",
 			"ipaddr"=>"192.168.58.230",
 			"bcast"=>"eth0"
 		),
 		
 		"standby"=>array(
 			"hostname"=>"VIS231",
 			"ipaddr"=>"192.168.58.231",
 			"bcast"=>""
 		),
 		
 		"cluster"=>array(
 			"node"=>"VIS230",
 			"ipaddr"=>"",
 			"prefix"=>"24",
 			"bcast"=>"192.168.58.255",
 			"resources"=>"visserver"
 		)
 	)
*/
	print "
	
	<table align=\"center\" width=\"100%\">
	  <tr>
	    <td class=\"bar_nopanel\">{$vis_ha_info_str[$lang]}</td>
	  </tr>
	</table>
	<table width=\"80%\" border=\"0\" align=\"center\" cellpadding=\"6\">
	  <tr>
	    <td colspan=\"4\" class=\"title\">{$node_info_str[$lang]}</td>
	  </tr>
	  <tr>
	    <td class=\"field_title\">{$node_type_str[$lang]}</td>
	    <td class=\"field_title\">{$hostname_str[$lang]}</td>
	    <td class=\"field_title\">{$ip_address_str[$lang]}</td>
	    <td class=\"field_title\">{$nic_dev_select_str[$lang]}</td>
	  </tr>
	  <tr>
	    <td class=\"field_title\">{$active_node_str[$lang]}</td>
	    <td class=\"field_data1\">{$hainfo['active']['hostname']}</td>
	    <td class=\"field_data2\">{$hainfo['active']['ipaddr']}</td>
	    <td class=\"field_data1\">{$hainfo['active']['bcast']}</td>
	  </tr>
	  <tr>
	    <td class=\"field_title\">{$standby_node_str[$lang]}</td>
	    <td class=\"field_data1\">{$hainfo['standby']['hostname']}</td>
	    <td class=\"field_data2\">{$hainfo['standby']['ipaddr']}</td>
	    <td class=\"field_data1\">{$hainfo['standby']['bcast']}</td>
	  </tr>
	</table>
	<table width=\"80%\" border=\"0\" align=\"center\" cellpadding=\"6\">
	  <tr>
	    <td colspan=\"2\" class=\"title\">{$cluster_info_str[$lang]}</td>
	  </tr>
	  <tr>
	    <td class=\"field_title\">{$cluster_ip_str[$lang]}</td>
	    <td class=\"field_title\">{$hostname_str[$lang]}</td>
	  </tr>
	  <tr>
	    <td class=\"field_data1\">{$hainfo['cluster']['ipaddr']}</td>
	    <td class=\"field_data2\">{$hainfo['cluster']['node']}</td>
	  </tr>
	</table>
	
	<form name=\"ha_form\" id=\"ha_form\" action=\"ha_target.php\" method=\"post\">
	<table width=\"80%\" border=\"0\" align=\"center\" cellpadding=\"6\">
	<tr>
	<td>
	<input type=\"submit\" onClick=\"return do_confirm('{$disable_ha_confirm_str[$lang]}');\" name=\"disable_ha_submit\" id=\"disable_ha_submit\" value=\"{$disable_ha_str[$lang]}\">
	</td>
	</tr>
	</table>
	</form>
	
	";
}

// ��ӡ��Ϣ
if($message != "")
{
	print_msg_block($message);
}
?>

</body>
</html>