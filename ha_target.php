<?php
require_once("./include/function.php");
require_once("./include/network.php");
require_once("./include/vis_ha.php");
require_once("./include/log.php");

$lang=load_lang();

$vis_ha_setup_str=array(
	"VIS热备设置",
	"VIS High-Availability Setup(HA)"
);
$node_config_str=array(
	"节点配置",
	"Node Config"
);
$node_type_str=array(
	"节点类型",
	"Node Type"
);
$ip_address_str=array(
	"IP地址",
	"IP Address"
);
$hostname_str=array(
	"主机名称",
	"Host Name"
);
$nic_dev_select_str=array(
	"网络设备选择",
	"Network Device"
);
$use_local_str=array(
	"使用本机",
	"Use Local Host"
);
$active_node_str=array(
	"主服务器",
	"Master Node"
);
$standby_node_str=array(
	"从服务器",
	"Slave Node"
);
$cluster_config_str=array(
	"集群设置",
	"Cluster Config"
);
$active_node_hostname_str=array(
	"主服务器节点名称",
	"Master Node Hostname"
);
$cluster_ip_str=array(
	"集群IP地址",
	"Cluster IP Address"
);
$netmask_str=array(
	"子网掩码",
	"Netmask"
);
$enable_ha_str=array(
	"启用VIS热备",
	"Enable VIS HA"
);
$disable_ha_str=array(
	"停用VIS热备",
	"Stop VIS HA"
);
$vis_ha_info_str=array(
	"VIS热备配置信息",
	"VIS High-Availability(HA) Information"
);
$node_info_str=array(
	"节点信息",
	"Node Information"
);
$cluster_info_str=array(
	"集群信息",
	"Cluster Information"
);
$enable_ha_confirm_str=array(
	"确认启用VIS热备？",
	"Confirm to continue?"
);
$need_complete_info_str=array(
	"需要将信息填写完整",
	"Need complete information"
);
$disable_ha_confirm_str=array(
	"确认停用VIS热备？",
	"Confirm to continue?"
);
$enable_ha_ok_str=array(
	"启用VIS热备成功。",
	"Enable VIS HA OK."
);
$enable_ha_failed_str=array(
	"启用VIS热备失败！",
	"Enable VIS HA failed!"
);
$disable_ha_ok_str=array(
	"停用VIS热备成功。",
	"Stop VIS HA OK."
);
$disable_ha_failed_str=array(
	"停用VIS热备失败！",
	"Stop VIS HA failed!"
);
$ip_error_str=array(
	"IP不正确！",
	"IP address is incorrect!"
);
$hostname_same_error_str=array(
	"主、从服务器主机名称不能相同！",
	"The hostname of master and slave node must be not the same!"
);
?>

<?php
$message="";
$log = new Log();
$network = new NetWork();
$vis_ha = new VisHa();
// 获取子网掩码列表
$netmask_list = $network->GenerateNetmasks();
// 获取本机IP，主机名称
$hostname = $network->GetHostname();
$hostip = $_SERVER['SERVER_ADDR'];
// 获取已连接的、配置有IP的网络设备，不包括slave device
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
 * 表单请求处理部分 ---------------------
 */


// 停用HA
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

// 启用HA
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
		$log->VstorWebLog(LOG_INFOS, MOD_VIS, "设置热备成功。", CN_LANG);
	}
	else
	{
		$message = $enable_ha_failed_str[$lang];
		$log->VstorWebLog(LOG_ERROR, MOD_VIS, "set High-Availability failed.");
		$log->VstorWebLog(LOG_ERROR, MOD_VIS, "设置热备失败。", CN_LANG);
	}
}

/*
 *  --------------------- 表单请求处理部分
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
	// 检查信息是否填写完整
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

	//检查主、从服务器的主机名称是否相同
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
	// 修改集群的活跃主机名信息
	input_active_hostname();
	
	return true;
}
function select_nic(obj)
{
	device = obj.value;
	<?php 
	$devlist_str = "";
	$iplist_str = "";
	// 获取每个设备的IP
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
// 如果HA没有启用，则显示配置界面
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
 * 已经启动HA，显示HA信息界面
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
 	信息里列表：
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

// 打印消息
if($message != "")
{
	print_msg_block($message);
}
?>

</body>
</html>