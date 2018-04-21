<?php
require("./include/authenticated.php");
require_once("./include/function.php");
require_once("./include/network.php");
require_once("./include/log.php");

$lang=load_lang();

$network_setup_str=array(
	"网络设置",
	"Network Setup"
);
$hostname_str=array(
	"主机名称",
	"Host Name"
);
$update=array(
	"更 新",
	"Update"
);
$nework_device_setup_str=array(
	"网络连接设置",
	"Network Interface Setup"
);
$sel_nic=array(
	"选择网络设备",
	"Select Device"
);
$ip_addr_str=array(
	"IP地址",
	"IP Address"
);
$netmask_str=array(
	"网络掩码",
	"Netmask"
);
$gateway_str=array(
	"网关IP",
	"Gateway IP"
);
$remove_ip_str=array(
	"删除IP信息",
	"Remove IP information"
);

$bond_setup_str=array(
	"网卡绑定设置",
	"Device Bonding Setup"
);
$sel_nic_to_bond=array(
	"选择需要绑定的设备",
	"Select Devices to Bind"
);
$bond_info=array(
	"网卡绑定信息",
	"Bonding Information"
);
$bond_name_str=array(
	"绑定设备",
	"Device"
);
$salave_nic_str=array(
	"附属网口",
	"Slave NIC"
);
$bond_mode_str=array(
	"绑定模式",
	"Bonding Mode"
);
$operate=array(
	"操作",
	"Operation"
);
$cancel_bond=array(
	"取消绑定",
	"Remove"
);

$cancel_bond_confirm_str=array(
	"确定要取消此绑定吗？",
	"Confirm to cancel the bonding?"
);
$none_str=array(
	"- 无 -",
	"- None -"
);
$ip_error_str=array(
	"IP地址错误!",
	"IP address is incorrect!"
);
$has_connected_str=array(
	"已连接",
	"Connected"
);
$not_connected_str=array(
	"未连接",
	"Not Connected"
);
$has_enabled_str=array(
	"已启用",
	"Enabled"
);
$not_enabled_str=array(
	"未启用",
	"Not Enabled"
);
$hostname_error_str=array(
	"主机名称错误！",
	"Set hostname error!"
);
$select_nic_to_bond_tip_str=array(
	"请先选择需要绑定的设备！",
	"Please select device(s) to bind!"
);
$remove_ip_ok_tip_str=array(
	"删除IP成功。",
	"Remove IP OK."
);
$remove_ip_failed_tip_str=array(
	"删除IP失败！",
	"Remove IP failed!"
);
?>


<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<link rel="stylesheet" href="css/target.css" type="text/css" />
<script defer type="text/javascript" src="js/pngfix.js"></script>
<script defer type="text/javascript" src="js/function.js"></script>
<script defer type="text/javascript" src="js/basic.js"></script>
<style type="text/css">
span:hover{
text-decoration:underline;
cursor:default;
}
</style>
<script type="text/javascript">
function check_ip(obj)
{
	var value = obj.value;
	if(value.Trim() == "")
	{
		return true;
	}
	var retval = IsIpOk(value);
	if(retval == true)
	{
		return true;
	}
	else
	{
		alert('<?php print $ip_error_str[$lang];?>');
		obj.focus();
		obj.select();
		return false;
	}
	
	return true;
}
function check_hostname2(msg)
{
    var objHostName = document.getElementById("hostname");
	var hostname = objHostName.value;
	if(hostname.Trim() == "")
	{
		alert(msg);
		objHostName.value=hostname.Trim();
		objHostName.focus();
		return false;
	}
	
	var retval = IsHostnameOk(hostname);
	if(retval == true)
	{
		return true;
	}
	else
	{
		alert(msg);
		return false;
	}
	return true;
}
function check_hostname()
{
    var objHostName = document.getElementById("hostname");
	var hostname = objHostName.value;
	if(hostname == "")
	{
		return true;
	}
	var retval = IsHostnameOk(hostname);
	if(retval == true)
	{
		document.getElementById("hostname_span").innerHTML = "<img src='./images/right_icon.png'></img>";
	}
	else
	{
		document.getElementById("hostname_span").innerHTML = "<img src='./images/error_icon.png'></img>";
	}
	return true;
}
function check_gw_ip()
{
    var objIpaddr = document.getElementById("gateway");
	var ipaddr = objIpaddr.value;
	if(ipaddr == "")
	{
		return true;
	}
	var retval = IsIpOk(ipaddr);
	if(retval == true)
	{
		document.getElementById("gateway_span").innerHTML = "<img src='./images/right_icon.png'></img>";
	}
	else
	{
		document.getElementById("gateway_span").innerHTML = "<img src='./images/error_icon.png'></img>";
	}
	return true;
}
function network_form_check()
{
	var ret = 0;
	var objGW = document.getElementById("gateway");
	ret = check_ip(objGW);
	if( ret == false )
	{
		return false;
	}
	ret = check_hostname2('<?php print $hostname_error_str[$lang];?>');
	if(ret == false)
	{
		return false;
	}
	return true;		
}
function check_nic_ip()
{
    var objIP = document.getElementById("nic_ipaddr");
	var ipaddr = objIP.value;
	if(ipaddr == "")
	{
		return true;
	}
	var retval = IsIpOk(ipaddr);
	if(retval == true)
	{
		document.getElementById("nic_ipaddr_span").innerHTML = "<img src='./images/right_icon.png'></img>";
	}
	else
	{
		document.getElementById("nic_ipaddr_span").innerHTML = "<img src='./images/error_icon.png'></img>";
	}
	return true;
}
function check_bond_ip()
{
    var objIP = document.getElementById("bond_ipaddr");
	var ipaddr = objIP.value;
	if(ipaddr == "")
	{
		return true;
	}
	var retval = IsIpOk(ipaddr);
	if(retval == true)
	{
		document.getElementById("bond_ipaddr_span").innerHTML = "<img src='./images/right_icon.png'></img>";
	}
	else
	{
		document.getElementById("bond_ipaddr_span").innerHTML = "<img src='./images/error_icon.png'></img>";
	}
	return true;
}
function bond_form_check()
{
    var objBIP = document.getElementById("bond_ipaddr");
	var no_select_nic_msg = '<?php print $select_nic_to_bond_tip_str[$lang];?>';
	for (i = 0; i < document.getElementsByName("bond[]").length; i++)
	{
		if(document.getElementsByName("bond[]")[i].checked)
		{
			if( objBIP.value.Trim() == "" )
			{
				alert('<?php print $ip_error_str[$lang];?>');
				return false;
			}
			else
			{	
				if( check_ip(objBIP)==true )
				{
					return true;
				}
				else
				{
					return false;
				}
			}
		}
	}
	
	alert(no_select_nic_msg);
	return false;
}
</script>

<?php 
$bRefreshPage = FALSE;
$message="";
$network = new NetWork();
$log = new Log();
// 获取子网掩码列表
$netmask_list = $network->GenerateNetmasks();

// 设置主机名称、DNS名称
while( isset($_POST['network_submit']) )
{
	$hostname = trim($_POST['hostname']);
	$gateway = trim($_POST['gateway']);
	//$dns1 = trim($_POST['dns1']);
	//$dns2 = trim($_POST['dns2']);
	
	if($hostname != $network->GetHostname())
	{
		if($hostname != "")
		{
			$network->SetHostname($hostname);
			$log->VstorWebLog(LOG_INFOS, MOD_NETWORK, "set hostname to " . $hostname . " ok.");
			$log->VstorWebLog(LOG_INFOS, MOD_NETWORK, "设置主机名称为" . $hostname . " 成功。", CN_LANG);
		}
	}
	if($gateway != $network->GetGateway())
	{
		if( $gateway == "" || IsIpOk($gateway) )
		{
			$network->SetGateway($gateway);
			if($gateway == "")
			{
				$log->VstorWebLog(LOG_INFOS, MOD_NETWORK, "unset gateway ok.");
				$log->VstorWebLog(LOG_INFOS, MOD_NETWORK, "取消设置网关IP成功。", CN_LANG);
			}
			else
			{
				$log->VstorWebLog(LOG_INFOS, MOD_NETWORK, "set gateway to [" . $gateway . "] ok.");
				$log->VstorWebLog(LOG_INFOS, MOD_NETWORK, "设置网关IP地址为[" . $gateway . "]成功。", CN_LANG);
			}
			// 修改网关，重启网络
			$network->Restart();
		}
		else
		{
			$message = $gateway_str[$lang] . ": " . $ip_error_str[$lang];
			break;
		}
	}

	break;
}

// 获取主机名称、DNS名称
$hostname = $network->GetHostname();
$dns_array = $network->GetDNS();
$gateway = $network->GetGateway();

?>

<!--  
/////////////////////////////////////////////////////////////
-->

<?php 
$list_dev_to_config = $network->ListDevices(1);
//获取网络设备的简要信息，用于显示与提示信息
$dev_tip_str_arr = array();
foreach($list_dev_to_config as $entry)
{
  	$tmpnic = new NetworkCard($entry);
  	if( $tmpnic->IsEnabled() )
  	{
  		if( preg_match("/eth/i", $entry) ) // 普通eth设备
  		{
	  		if( $tmpnic->IsConnected() )
	  		{
	  			$dev_tip_str_arr["{$entry}"] = $has_connected_str[$lang] . ": " . $tmpnic->GetSpeed();
	  		}
	  		else
	  		{
	  			$dev_tip_str_arr["{$entry}"] = $not_connected_str[$lang];
	  		}
  		}
  		else if( preg_match("/bond/i", $entry) ) // 认为是绑定设备
  		{
  			$dev_tip_str_arr["{$entry}"] = "";
  			$tmpbond = new Bond($entry);
  			$tmpslaves = $tmpbond->ListSlaves();
  			if($tmpslaves !== FALSE)
  			{
  			  	foreach($tmpslaves as $slave)
	  			{
	  				$tmpnic2 = new NetworkCard($slave);
	  				if( $tmpnic2->IsConnected() )
			  		{
			  			$dev_tip_str_arr["{$entry}"] .= " " . $slave . ": " . $has_connected_str[$lang] . "," 
			  											. $tmpnic2->GetSpeed();
			  		}
			  		else
			  		{
			  			$dev_tip_str_arr["{$entry}"] .= " " . $slave . ": " . $not_connected_str[$lang];
			  		}
	  			}
  			}

  		}
  		else 
  		{
  			$dev_tip_str_arr["{$entry}"] = "";
  		}
  	}
  	else
  	{
  		$dev_tip_str_arr["{$entry}"] = $not_enabled_str[$lang];
  	}
}


//网卡配置
if( isset($_POST['nic_dev_submit']) && isset($_POST['nic_dev'])/*防止没有选择设备*/)
{
	$nic_dev = $_POST['nic_dev'];
	$nic = new NetworkCard($nic_dev);
	$nic_ipaddr = trim($_POST['nic_ipaddr']);
	$nic_netmask = $_POST['nic_netmask_list'];
	
	if( isset($_POST['rm_ip_cb']) )
	{
		$nic->RemoveIP();
		$log->VstorWebLog(LOG_INFOS, MOD_NETWORK, "remove NIC device ({$nic_dev}) IP ok.");
		$log->VstorWebLog(LOG_INFOS, MOD_NETWORK, "删除网卡{$nic_dev} IP信息成功。", CN_LANG);
		// 重启网络服务
		$network->Restart();		
	}
	else
	{
		if( IsIpOk($nic_ipaddr) )
		{
			$nic->SetIP($nic_ipaddr);
			$nic->SetMask($nic_netmask);
			$nic->SetBroadcast($nic_ipaddr);
			$log->VstorWebLog(LOG_INFOS, MOD_NETWORK, "set NIC device config {$nic_dev}({$nic_ipaddr}/{$nic_netmask}) ok.");
			$log->VstorWebLog(LOG_INFOS, MOD_NETWORK, "设置网卡设备信息{$nic_dev}({$nic_ipaddr}/{$nic_netmask})成功。", CN_LANG);
			
			// 重启网络服务
			$network->Restart();
		}
		else
		{
			$message = $ip_error_str[$lang];
		}
	}
}
?>

<script type="text/javascript">
function setDeviceConfig()
{
<?php 

//获取所有的设备的IP、NetMask
$device_buffer = "";
$config_buffer = "";
foreach($list_dev_to_config as $nic_dev_entry)
{
	$tmp_nic = new NetworkCard($nic_dev_entry);
	$device_buffer .= $nic_dev_entry . ";";
	$config_buffer .= $tmp_nic->GetIP() . "," . $tmp_nic->GetMask() . ";";
}
?>
	var sel = 0;
	var i = 0;
	var device = "";
	for (i = 0; i < document.getElementsByName("nic_dev").length; i++)
	{
		if(document.getElementsByName("nic_dev")[i].checked)
			device = document.getElementsByName("nic_dev")[i].value;
	}
	var device_buffer = "<?php print $device_buffer;?>";
	var config_buffer = "<?php print $config_buffer;?>";
	var server_ip = "<?php print $_SERVER['SERVER_ADDR']?>";
	
	//分开每个设备
	var device_array = new Array();
	var config_array = new Array();
	var dev_config_array = new Array();
	device_array = device_buffer.split(";");

	var objIP = document.getElementById("nic_ipaddr");
	var objNM = document.getElementById("nic_netmask_list");
	var objRMIP = document.getElementById("rm_ip_cb");
	for(i=0; i<device_array.length; i++)
	{
		if(device_array[i]==device)
		{
			config_array = config_buffer.split(";");
			dev_config = config_array[i];
			dev_config_array = dev_config.split(",");
			var ip = dev_config_array[0];
			var netmask = dev_config_array[1];
			//设置界面显示
			objIP.value=ip;
			objNM.value=netmask;

			if(ip == server_ip || ip == ""              // 是否为当前web连接使用的IP，不允许删除
			  // || (str=device.substr(0,4) == "bond")  // 是否为绑定设备，绑定设备不允许删除IP
			  )
			{
			    objRMIP.disabled = true;
			}
			else
			{
			    objRMIP.disabled = false;
			}
		}
	}

	return true;
}

</script>

<?php
// 获取可以做绑定的设备列表
$list_dev_to_bond = $network->ListDevices(2);
if( isset($_POST['bond_submit']) && isset($_POST['bond']) )
{
	// 获取作为slave设备的所有名称(数组)
	$dev_slave_array = $_POST['bond'];
	$bond_ipaddr_new = trim($_POST['bond_ipaddr']);
	$bond_netmask_new = $_POST['bond_netmask_list'];
	$bond_mode = $_POST['bond_mode_list'];

	if( IsIpOk($bond_ipaddr_new) )
	{
		//设置新的bond设备的名称
		$bond_dev_existed = $network->ListBonds();
		$index = 0;
		$bond_name_new = "bond" . $index;
		for($index=0; $index<=count($bond_dev_existed); $index++)
		{
			$bond_name_new = "bond" . $index;
			if( in_array($bond_name_new, $bond_dev_existed) )
			{
				continue;
			}
			else
			{
				break;
			}
		}
		
		$slave_dev_str = "";
		$bond_dev_new = new Bond($bond_name_new);
		foreach($dev_slave_array as $slave)
		{
			$bond_dev_new->AddSlave($slave);
			$slave_dev_str .= $slave . ",";
		}
		$bond_dev_new->Create($bond_ipaddr_new, $bond_netmask_new, $bond_mode);
		
		$slave_dev_str= substr($slave_dev_str, 0, strlen($slave_dev_str)-1);
		$log->VstorWebLog(LOG_INFOS, MOD_NETWORK, "bind {$slave_dev_str} as {$bond_name_new}({$bond_ipaddr_new}/{$bond_netmask_new},{$bond_mode}) ok.");
		$log->VstorWebLog(LOG_INFOS, MOD_NETWORK, "绑定{$slave_dev_str}为{$bond_name_new}({$bond_ipaddr_new}/{$bond_netmask_new},{$bond_mode})成功。", CN_LANG);
		
		// 重启网络
		$network->Restart();
		// 刷新
		$bRefreshPage = TRUE;
	}
	else
	{
		$message = $ip_error_str[$lang];
	}
}

?>

<?php
// 取消绑定
$list_bond = $network->ListBonds();
$postfix = "_del_submit";
foreach($list_bond as $bond_entry)
{
	$field = $bond_entry . $postfix;
	if(isset($_POST["$field"]))
	{
		$bond_dev = new Bond($bond_entry);
		$bond_nic = new NetworkCard($bond_entry);
		$ipaddr = $bond_nic->GetIP();
		$netmask = $bond_nic->GetMask();

		$slave_dev_str = "";
		$list_slave = $bond_dev->ListSlaves();
		foreach($list_slave as $slave_entry)
		{
			$slave_dev_str .= $slave_entry . ",";
		}
		$bond_dev->Remove();
		$slave_dev_str= substr($slave_dev_str, 0, strlen($slave_dev_str)-1);
		$log->VstorWebLog(LOG_WARN, MOD_NETWORK, "unset {$bond_entry}({$ipaddr}/{$netmask} {$slave_dev_str}) ok.");
		$log->VstorWebLog(LOG_WARN, MOD_NETWORK, "取消绑定设备{$bond_entry}({$ipaddr}/{$netmask} {$slave_dev_str})成功。", CN_LANG);
		
		// 取消绑定后，将之前绑定设备的IP配置到其第一个附属网口上，防止网络中断。
		$dev_name = $list_slave[0];
		$dev = new NetworkCard($dev_name);
		$dev->SetIP($ipaddr);
		$dev->SetBroadcast($ipaddr);
		$dev->SetMask($netmask);
		
		// 重启网络服务
		//$network->Restart();
		// 卸载bonding模块
		exec("export LANG=C; /usr/bin/sudo /sbin/rmmod bonding");
		// 重启网络
		$network->Restart();
		// 刷新
		$bRefreshPage = TRUE;
	}
}
?>
</head>


<body>

<?php 
// 刷新页面（绑定、取消绑定后，防止界面显示因没有获取最新数据而出现错误）
if( $bRefreshPage === TRUE )
{
	print "
	<script type=\"text/javascript\">
		window.location.href=\"network_target.php\";
	</script>
	";
	$bRefreshPage = FALSE;
}
?>

<div id="network_target">

  <div id="network">
 
<form id="network_form" name="network_form" action="network_target.php" method="post"> 
  <table align="center" width="100%">
  	<tr>
	<td class="bar_nopanel"><?php print $network_setup_str[$lang]; ?></td>
	</tr>
  </table>
  <table width="80%" border="0" cellpadding="6" align="center">
	<tr>
	  <td class="field_title"><?php print $hostname_str[$lang]; ?></td>
	  <td class="field_data1">
	  	<input type="text" name="hostname" maxlength="16" onchange ="return check_hostname();"  onkeyup="return check_hostname();" value="<?php print $hostname; ?>" size="20" />
	  	<span id="hostname_span"><img src='./images/error_icon.png' style="visibility: hidden;"></img></span>
	  </td>
	</tr>

	<tr>
	  <td class="field_title"><?php print $gateway_str[$lang]; ?></td>
	  <td class="field_data2">
	  	<input type="text" id="gateway" maxlength="15" name="gateway" onchange ="return check_gw_ip();"  onkeyup="return check_gw_ip();" value="<?php print $gateway;?>" size="20" />
	  	<span id="gateway_span"><img src='./images/error_icon.png' style="visibility: hidden;"></img></span>
	  </td>
	</tr>
	<tr>
	  <td colspan="2">
	  	<input type="submit" name="network_submit" onClick="return network_form_check();" value="<?php print $update[$lang]; ?>" />
	  </td>
	</tr>
  </table>
</form>
	
	<!--     -->

<form id="nic_form" name="nic_form" action="network_target.php" method="post"> 
  <table align="center" width="100%">
  	<tr>
	<td class="bar_nopanel"><?php print $nework_device_setup_str[$lang]; ?></td>
	</tr>
  </table>

  <table width="80%" border="0" cellpadding="6" align="center">
	<tr>
	  <td class="field_title"><?php print $sel_nic[$lang]; ?></td>
	  <td class="field_data1">
	  <?php 
	  $list_dev_to_config = $network->ListDevices(1);
	  $index = 0;
	  foreach($list_dev_to_config as $entry)
	  {
			if($index == 0)
			{
				print "<input type=\"radio\" checked=\"checked\" name=\"nic_dev\" value=\"" 
						. $entry . "\" onclick=\"return setDeviceConfig();\"/>"
						. "<span title=\"{$dev_tip_str_arr["{$entry}"]}\">{$entry}</span>\n";
			}
			else
			{
				print "<input type=\"radio\" name=\"nic_dev\" value=\"" . $entry 
					. "\" onclick=\"return setDeviceConfig();\"/>" 
					. "<span title=\"{$dev_tip_str_arr["{$entry}"]}\">{$entry}</span>\n";
			}
			$index++;
	  }
	  ?>
	  </td>
	</tr>
	<tr>
	  <td class="field_title"><?php print $ip_addr_str[$lang]; ?></td>
	  <td class="field_data2">
	  	<input type="text" maxlength="15" id="nic_ipaddr" name="nic_ipaddr" value="" onchange ="return check_nic_ip();"  onkeyup="return check_nic_ip();" size="20" />
	  	<span id="nic_ipaddr_span"><img src='./images/error_icon.png' style="visibility: hidden;"></img></span>
	  </td>
	</tr>
	<tr>
	  <td class="field_title"><?php print $netmask_str[$lang]; ?></td>
	  <td class="field_data1">
	  	<?php 
	  	print "<select name=\"nic_netmask_list\" id=\"nic_netmask_list\">";
	  	print_netmask_of_select_bynetmask();
	  	print "</select>";
	  	?>
	  </td>
	</tr>
	<tr>
	  <td class="field_title"><?php print $operate[$lang]; ?></td>
	  <td class="field_data2">
	  <input type="checkbox" name="rm_ip_cb" id="rm_ip_cb"><?php print $remove_ip_str[$lang];?>
	  </td>
	</tr>
	<tr>
	  <td colspan="2">
	  	<input type="submit" name="nic_dev_submit" onClick="return check_ip(document.nic_form.nic_ipaddr);" value="<?php print $update[$lang]; ?>" onClick=""/>
	  </td>
	</tr>
  </table>
</form>

<script type="text/javascript">
	setDeviceConfig();
</script>

<!--     -->

<form id="bond_form" name="bond_form" action="network_target.php" method="post"> 
  <table align="center" width="100%">
  	<tr>
	<td class="bar_nopanel"><?php print $bond_setup_str[$lang]; ?></td>
	</tr>
  </table>
	  
  <table width="80%" border="0" cellpadding="6" align="center">
	<tr>
	  <td class="field_title"><?php print $sel_nic_to_bond[$lang]; ?></td>
	  <td class="field_data1">
	  	<?php 
	  	$bHasItem = FALSE;
	  	$list_dev_to_bond = $network->ListDevices(2);
	  	foreach($list_dev_to_bond as $entry)
	  	{
	  		$bHasItem = TRUE;
	  		print "<input type=\"checkbox\" name=\"bond[]\" value=\"" . $entry . "\" />" 
	  			  . "<span title=\"{$dev_tip_str_arr["{$entry}"]}\">{$entry}</span>\n";
	  	}
	  	?>
	  </td>
	</tr>
	<tr>
	  <td class="field_title"><?php print $ip_addr_str[$lang]; ?></td>
	  <td class="field_data2">
	  	<input type="text" maxlength="15" name="bond_ipaddr" value="" onchange ="return check_bond_ip();"  onkeyup="return check_bond_ip();" size="20" />
	  	<span id="bond_ipaddr_span"><img src='./images/error_icon.png' style="visibility: hidden;"></img></span>
	  </td>
	</tr>
	<tr>
	  <td class="field_title"><?php print $netmask_str[$lang]; ?></td>
	  <td class="field_data1">
	  	<?php 
	  	print "<select name=\"bond_netmask_list\" id=\"bond_netmask_list\">";
		print_netmask_of_select_bynetmask();
	  	print "</select>";
	  	?>
	  </td>
	</tr>
	<tr>
	  <td class="field_title"><?php print $bond_mode_str[$lang]; ?></td>
	  <td class="field_data2">
	  	<?php 
	  	print "<select name=\"bond_mode_list\" id=\"bond_mode_list\">";
		print_bond_mode_list_of_select();
	  	print "</select>";
	  	?>
	  </td>
	</tr>
	<tr>
	  <td colspan="2">
	  	<input type="submit" name="bond_submit"  
	  	onClick="return bond_form_check();"  
	  	<?php 
	  		if( !$bHasItem )
	  		{
	  			print " disabled=\"disabled\" ";
	  		}
	  	?>			  	
	  	value="<?php print $update[$lang]; ?>" />
	  </td>
	</tr>
  </table>
</form>
		

<!--   -->

<script type="text/javascript">
function cancel_bond_confirm(bond)
{
	var info = bond + ": <?php print $cancel_bond_confirm_str[$lang];?>";
	if( confirm(info) )
	{
		return true;
	}
	else
	{
		return false;
	}
}
</script>

<form id="bondinfo_form" name="bondinfo_form" action="network_target.php" method="post" > 
  <table width="80%" border="0" cellpadding="6" align="center">
	<tr>
	  <td class="title" colspan="6">
	  <strong><?php print $bond_info[$lang]; ?></strong>				
	  </td>
	</tr>
	<tr>
	  <td class="field_title"><?php print $bond_name_str[$lang]; ?></td>
	  <td class="field_title"><?php print $ip_addr_str[$lang]; ?></td>
	  <td class="field_title"><?php print $netmask_str[$lang];?></td>
	  <td class="field_title"><?php print $bond_mode_str[$lang];?></td>
	  <td class="field_title"><?php print $salave_nic_str[$lang]; ?></td>
	  <td class="field_title"><?php print $operate[$lang]; ?></td>
	</tr>
	<?php 
	$bHasItem = FALSE;
	$list_bond = $network->ListBonds();
	foreach($list_bond as $bond_entry)
	{
		$td_class = "field_data1";
		$bond_dev = new Bond($bond_entry);
		$nic_bond = new NetworkCard($bond_entry);
		$list_slave = $bond_dev->ListSlaves();
		if($list_slave !== FALSE)
		{
			$slave_str = implode(",", $list_slave);
		}
		else
		{
			$slave_str = "";
		}
		print "<tr>";
		print "<td class=\"$td_class\">" . $nic_bond->GetDevice() . "</td>";
		print "<td class=\"$td_class\">" . $nic_bond->GetIP() . "</td>"; 
		print "<td class=\"$td_class\">" . $nic_bond->GetMask() . "</td>"; 
		print "<td class=\"$td_class\">" . $bond_dev->GetBondModeStr() . "</td>";
		print "<td class=\"$td_class\">" . $slave_str . "</td>";
		print "<td class=\"$td_class\"><input type=\"submit\" name=\"{$bond_entry}{$postfix}\" onClick=\"return cancel_bond_confirm('{$bond_entry}');\" value=\"$cancel_bond[$lang]\"/></td>";
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
	if($bHasItem == FALSE)
	{
		print "<tr><td class=\"field_data2\" colspan=\"6\">{$none_str[$lang]}</td></tr>";
	}
	?>
  </table>
</form>

	</div>
</div>
<?php 
if($message != "")
{
	print_msg_block($message);
}
?>
</body>
</html>
