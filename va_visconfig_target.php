<?php 
require_once("./include/function.php");
require_once("./include/visprofile.php");

if( !IsVisExisted())
	exit("no vis server!");


$lang=load_lang();

$vis_config_str=array(
	"VIS 服务器配置",
	"VIS Server Config"
);
$vis_service_config_str=array(
	"VIS 服务配置",
	"VIS Service config"
);
$vci_enable_str=array(
	"VCI 开启",
	"VCI enable"
);
$open_str=array(
	"开启",
	"Open"
);
$close_str=array(
	"关闭",
	"Close"
);
$server_mode_str=array(
	"服务器模式",
	"Server mode"
);
$storage_server_str=array(
	"存储服务器",
	"Storage server"
);
$vod_server_str=array(
	"点播服务器",
	"VOD server"
);
$transmit_server_str=array(
	"转发服务器",
	"Transmit server"
);
$download_server_str=array(
	"下载服务器",
	"Download server"
);
$manager_server_str=array(
	"管理服务器",
	"Manager server"
);
$manager_server_ip_str=array(
	"管理服务器IP地址",
	"Manager server ip address"
);
$op_info_lang=array(
	"Operator信息显示语言",
	"Operator information language"
);
$chinese_str=array(
	"中文",
	"Chinese"
);
$english_str=array(
	"英文",
	"English"
);
$storage_buffer_str=array(
	"存储转发的缓冲区大小 [5-50MB]",
	"Storage and transmit's buffer size [5-50MB]"
);
$scan_mode_str=array(
	"巡检模式",
	"Scan mode"
);
$sync_str=array(
	"同步",
	"Sync"
);
$async_st=array(
	"异步",
	"Async"
);
$vis_storage_policy_str=array(
	"VIS 存储策略配置",
	"VIS Storage Policy Config"
);
$policy_select_str=array(
	"策略选择",
	"Select policy"
);
$default_policy_str=array(
	"默认策略",
	"Default"
);
$record_file_length_str=array(
	"录像文件长度",
	"Recording file length"
);
$record_time_str=array(
	"录像时间",
	"Recording time"
);
$both_policy_str=array(
	"同时限定",
	"Both Policy"
);
$max_length_of_one_record_str=array(
	"单个录像文件大小 [100-30000M]",
	"Recording file length [100-30000M]"
);
$max_record_time_str=array(
	"单个录像文件时间 [1-300分钟]",
	"Recording time [1-300 minutes]"
);
$vis_userlog_setup_str=array(
	"VIS 用户日志配置",
	"VIS User Log Config"
);
$userlog_state_str=array(
	"用户日志状态",
	"User log State"
);
$max_record_count_str=array(
	"最大记录数量 [10000-100000]",
	"Max record count [10000-100000]"
);
$max_record_days_str=array(
	"最大记录天数 [10-90]",
	"Max record days [10-90]"
);
$update_str=array(
	"更 新",
	"Update"
);
$setup_str=array(
	"设 置",
	"Set"
);
$refresh_str=array(
	"刷 新",
	"Refresh"
);
?>
<?php 
$visprofile = new VisProfile();
/*
 * 表单请求响应处理部分 ------------------------------
 */

if( isset($_GET['access']) && $_GET['access']=="yes" )
{
	
}
else
{
	exit("No Access!");
}


if( isset($_POST['vis_config_submit']) )
{
	// vci
	if( isset($_POST['vci_enable']) )
	{
		$vcienable = $_POST['vci_enable'];
		$visprofile->SetFieldValue("VCIEnable", $vcienable);
	}
	
	// server mode
	if( isset($_POST['vis_mode']) )
	{
		$server_array = $_POST['vis_mode'];
		$mode = 0;
		foreach($server_array as $entry)
		{
			$mode += $entry;
		}
		if($mode == 0)
		{
			$mode = 128; // 默认管理服务器
		}
		$visprofile->SetFieldValue("ServerMode", $mode);
	}
	
	// manager ip
	if( isset($_POST['manager_ip']) )
	{
		$manager_ip = $_POST['manager_ip'];
		if( IsIpOk($manager_ip))
		{
			$visprofile->SetFieldValue("ManagerIP", $manager_ip);
		}
	}
	
	// return operator language
	if( isset($_POST['return_language']) )
	{
		$return_op_lang = $_POST['return_language'];
		$visprofile->SetFieldValue("ErrCodeLanguage", $return_op_lang);
	}
	
	// buffer size
	if( isset($_POST['transmit_buffer']) )
	{
		$buffersize = $_POST['transmit_buffer'];
		if($buffersize<5 || $buffersize>50)
		{
			$buffersize = 10;
		}
		$visprofile->SetFieldValue("BufferSize", $buffersize);
	}
	
	// scan mode
	if( isset($_POST['scan_mode']) )
	{
		$scan_mode = $_POST['scan_mode'];
		$visprofile->SetFieldValue("ScanSync", $scan_mode);
	}
	
	// storage policy
	if( isset($_POST['storage_policy']) )
	{
		$policy = $_POST['storage_policy'];
		$visprofile->SetFieldValue("Choice", $policy);
	}
	if( isset($_POST['record_maxlength']) )
	{
		$record_length = $_POST['record_maxlength'];
		if($record_length<100 || $record_length>30000)
		{
			$record_length = 1000;
		}
		$visprofile->SetFieldValue("MaxSize", $record_length);
	}
	if( isset($_POST['record_time']) )
	{
		$record_time = $_POST['record_time'];
		if($record_time<1 || $record_time>300)
		{
			$record_time = 60;
		}
		$visprofile->SetFieldValue("MaxTime", $record_time);
	}
	
	// use log
	if( isset($_POST['userlog_open']) )
	{
		$log_open = $_POST['userlog_open'];
		$visprofile->SetFieldValue("Open", $log_open);
	}
	if( isset($_POST['log_maxcount']) )
	{
		$log_maxcount = $_POST['log_maxcount'];
		if($log_maxcount<10000 || $log_maxcount>100000)
		{
			$log_maxcount = 100000;
		}
		$visprofile->SetFieldValue("MaxCount", $log_maxcount);
	}
	if( isset($_POST['log_maxday']) )
	{
		$log_maxday = $_POST['log_maxday'];
		if($log_maxday<10 || $log_maxday>90)
		{
			$log_maxday = 30;
		}			
		$visprofile->SetFieldValue("MaxDays", $log_maxday);
	}
	
	// 保存配置
	$visprofile->Save();
	
	// 刷新页面
	print "<script type=\"text/javascript\">";
	print "window.parent.location.href=\"va_visconfig.php\";";
	print "</script>";
}

/*
 * -------------- 表单请求响应处理部分 END
 */
?>
<html> 
<head>
<base target="_self"></base>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<link rel="stylesheet" href="css/target.css" type="text/css" />
<script defer type="text/javascript" src="js/pngfix.js"></script>
<script defer type="text/javascript" src="js/function.js"></script>
<script type="text/javascript">
function vis_config_refresh()
{
	window.location.href='va_visconfig_target.php?access=yes';
	return false;
}
function EnableManagerIp(obj)
{
	if(obj.checked == true)
		document.vis_config_form.manager_ip.disabled = true;
	else
		document.vis_config_form.manager_ip.disabled = false;
	return true;
}
function EnableStoragePolicy(obj)
{
	if(obj.checked == true)
	{
		document.vis_config_form.storage_policy.disabled = false;
		SelectStoragePolicy();
	}
	else
	{
		document.vis_config_form.storage_policy.disabled = true;
		document.vis_config_form.record_maxlength.disabled = true;
		document.vis_config_form.record_time.disabled = true;
	}
	return true;
}

function  UserLogOpen(obj)
{
	document.vis_config_form.log_maxcount.disabled = false;
	document.vis_config_form.log_maxday.disabled = false;
	return true;
}
function  UserLogClose(obj)
{
	document.vis_config_form.log_maxcount.disabled = true;
	document.vis_config_form.log_maxday.disabled = true;
	return true;
}

function SelectStoragePolicy()
{
	var policy = document.vis_config_form.storage_policy.value;
	if(policy == 0)
	{
		document.vis_config_form.record_maxlength.disabled = false;
		document.vis_config_form.record_time.disabled = false;
	}
	else if(policy == 1)
	{
		document.vis_config_form.record_maxlength.disabled = false;
		document.vis_config_form.record_time.disabled = true;
	}
	else if(policy == 2)
	{
		document.vis_config_form.record_maxlength.disabled = true;
		document.vis_config_form.record_time.disabled = false;
	}
	else if(policy == 3)
	{
		document.vis_config_form.record_maxlength.disabled = false;
		document.vis_config_form.record_time.disabled = false;
	}
	return true;
}
function check_manager_ip()
{
	var ipaddr = document.vis_config_form.manager_ip.value;

	var retval = IsIpOk(ipaddr);
	if(retval == true || ipaddr == "")
	{
		document.getElementById("manager_ip_span").innerHTML = "<img src='./images/right_icon.png'></img>";
	}
	else
	{
		document.getElementById("manager_ip_span").innerHTML = "<img src='./images/error_icon.png'></img>";
	}
	return true;
}
</script>
</head>

<body>

<div id="visconfig_target">
<form id="vis_config_form" name="vis_config_form" action="va_visconfig_target.php?access=yes" method="post">
<table align="center" width="100%">
	<tr>
	<td class="bar_nopanel"><?php print $vis_config_str[$lang];?></td>
	</tr>
</table>
<table width="60%" border="0" cellpadding="6" align="center">
  <tr>
	<td class="title" colspan="2"><?php print $vis_service_config_str[$lang];?></td>
  </tr>
  <tr>
    <td class="field_title_left"><?php print $server_mode_str[$lang];?> </td>
    <td class="field_data1" align="left">
	<?php 
	$server_mode= $visprofile->GetFieldValue("ServerMode");
	$server_array = array_values(GetVisServerMode($server_mode));
	$server_checked = array();
	foreach($server_array as $entry)
	{
		if($entry == 1)
		{
			$server_checked[] = "checked=\"checked\"";
		}
		else
		{
			$server_checked[] = "";
		}
	}
	
	?>
     &emsp;&emsp;<input type="checkbox" id="storageserver" name="vis_mode[]" <?php print $server_checked[0];?> value="1" onClick="EnableStoragePolicy(this);"><?php print $storage_server_str[$lang];?> <br/>
	 &emsp;&emsp;<input type="checkbox" name="vis_mode[]" <?php print $server_checked[1];?> value="2"><?php print $vod_server_str[$lang];?> <br/>
	 &emsp;&emsp;<input type="checkbox" name="vis_mode[]" <?php print $server_checked[2];?> value="4"><?php print $transmit_server_str[$lang];?> <br/>
	 &emsp;&emsp;<input type="checkbox" name="vis_mode[]" <?php print $server_checked[3];?> value="8"><?php print $download_server_str[$lang];?> <br/>
	 &emsp;&emsp;<input type="checkbox" id="managerserver" name="vis_mode[]" <?php print $server_checked[4];?> value="128" onClick="EnableManagerIp(this);"><?php print $manager_server_str[$lang];?> 
	 </td>
  </tr>
  <tr>
    <td class="field_title_left"><?php print $manager_server_ip_str[$lang];?> </td>
    <td class="field_data2">
    <?php 
    if($server_array[4] == 1)
    	$disabled = "disabled=\"disabled\"";
    else
    	$disabled = "";
    ?>
      <input size="17" id="manager_ip" name="manager_ip" onchange ="return check_manager_ip();"  onkeyup="return check_manager_ip();" maxlength="15" type="text" <?php print $disabled;?> value="<?php print $visprofile->GetFieldValue("ManagerIP");?>">
      <span id="manager_ip_span"><img src='./images/error_icon.png' style="visibility: hidden;"></img></span>    
	</td>
  </tr>
  <tr>
    <td class="field_title_left"><?php print $vci_enable_str[$lang];?></td>
    <td class="field_data2">
	<?php 
	$vcienable = $visprofile->GetFieldValue("VCIEnable");
	if($vcienable == 0)
	{
		print "
      	<input type=\"radio\" name=\"vci_enable\" value=\"1\">{$open_str[$lang]} 
      	<input type=\"radio\" name=\"vci_enable\" checked=\"checked\" value=\"0\">{$close_str[$lang]}
		";
	}
	else
	{
		print "
      	<input type=\"radio\" name=\"vci_enable\" checked=\"checked\" value=\"1\">{$open_str[$lang]}
      	<input type=\"radio\" name=\"vci_enable\" value=\"0\">{$close_str[$lang]}
		";
	}
    ?> 
	</td>
  </tr>
  <tr>
    <td class="field_title_left"><?php print $op_info_lang[$lang];?> </td>
    <td class="field_data1">
    <?php 
    $return_lang = $visprofile->GetFieldValue("ErrCodeLanguage");
    $cn_checked = "";
    $en_checked = "";
    if($return_lang == 1)
    	$cn_checked = "checked=\"checked\"";
    else
    	$en_checked = "checked=\"checked\"";
    ?>
		<input type="radio" name="return_language" <?php print $cn_checked;?> value="1"><?php print $chinese_str[$lang];?> 
		<input type="radio" name="return_language" <?php print $en_checked;?> value="2"><?php print $english_str[$lang];?> 
	</td>
  </tr>
  <tr>
    <td class="field_title_left"><?php print $storage_buffer_str[$lang];?> </td>
    <td class="field_data1">
		<input name="transmit_buffer" type="text" maxlength="2" value="<?php print $visprofile->GetFieldValue("BufferSize");?>">
	</td>
  </tr>
  <tr>
    <td class="field_title_left"><?php print $scan_mode_str[$lang];?> </td>
    <td class="field_data2">
    <?php 
    $scan_mode = $visprofile->GetFieldValue("ScanSync");
    $sync_checked = "";
    $async_checked = "";
    if($scan_mode == 1)
    {
    	$sync_checked = "checked=\"checked\"";
    }
    else
    {
    	$async_checked = "checked=\"checked\"";
    }
    ?>
		<input type="radio" name="scan_mode" <?php print $sync_checked;?> value="1"><?php print $sync_str[$lang];?> 
		<input type="radio" name="scan_mode" <?php print $async_checked;?> value="0"><?php print $async_st[$lang];?> 
	</td>
  </tr>
  
  
  <tr>
	<td class="title" colspan="2"><?php print $vis_storage_policy_str[$lang];?> </td>
  </tr>
  <tr>
    <td class="field_title_left"><?php print $policy_select_str[$lang];?> </td>
    <td class="field_data1">
    <?php 
    $policy = $visprofile->GetFieldValue("Choice");
    $selected = array();
    for($i=0; $i<4; $i++)
    {
    	if($i == $policy)
    	{
    		$selected[] = "selected";
    		continue;
    	}
    	$selected[] = "";
    }
    
    if($server_array[0] == 1)
    	$disabled = "";
    else
    	$disabled = "disabled=\"disabled\"";
    ?>
	<select id="storage_policy" name="storage_policy" <?php print $disabled;?> onChange="SelectStoragePolicy();" >
		<option value="0" <?php print $selected[0];?>><?php print $default_policy_str[$lang];?> 
		<option value="1" <?php print $selected[1];?>><?php print $record_file_length_str[$lang];?> 
		<option value="2" <?php print $selected[2];?>><?php print $record_time_str[$lang];?> 
		<option value="3" <?php print $selected[3];?>><?php print $both_policy_str[$lang];?> 
	</select>
	</td>
  </tr>
  <tr>
    <td class="field_title_left"><?php print $max_length_of_one_record_str[$lang];?> </td>
    <td class="field_data1">
    <?php 
    $length_disabled = "";
    $time_disabled = "";
    if($policy==2)
    {
    	$length_disabled = "disabled=\"disabled\"";
    }
    if($policy==1)
    {
    	$time_disabled = "disabled=\"disabled\"";
    }
    ?>
		<input <?php print $disabled . " " . $length_disabled;?> name="record_maxlength" maxlength="5" type="text" value="<?php print $visprofile->GetFieldValue("MaxSize");?>">
	</td>
  </tr>
  <tr>
    <td class="field_title_left"><?php print $max_record_time_str[$lang];?> </td>
    <td class="field_data2">
		<input <?php print $disabled . " " . $time_disabled;?> name="record_time" maxlength="3" type="text" value="<?php print $visprofile->GetFieldValue("MaxTime");?>">
	</td>
  </tr>
  
  
  <tr>
		<td class="title" colspan="2"><?php print $vis_userlog_setup_str[$lang];?> </td>
	</tr>
  <tr>
    <td class="field_title_left"><?php print $userlog_state_str[$lang];?> </td>
    <td class="field_data2">
    <?php 
    $userlog_open = $visprofile->GetFieldValue("Open");
    if($userlog_open == 1)
    {
    	print "
    		<input type=\"radio\" id=\"userlog_open\" name=\"userlog_open\" checked=\"checked\" value=\"1\" onClick=\"UserLogOpen(this);\">$open_str[$lang] 
      		<input type=\"radio\" id=\"userlog_close\" name=\"userlog_open\" value=\"0\" onClick=\"UserLogClose(this);\">$close_str[$lang] 
    	";
    	$disabled = "";
    } 
    else
    {
    	print "
    		<input type=\"radio\" id=\"userlog_open\" name=\"userlog_open\" value=\"1\" onClick=\"UserLogOpen(this);\">$open_str[$lang] 
      		<input type=\"radio\" id=\"userlog_close\" name=\"userlog_open\" checked=\"checked\" value=\"0\" onClick=\"UserLogClose(this);\">$close_str[$lang] 
    	";
    	$disabled = "disabled=\"disabled\"";
    }
    ?>
	</td>
  </tr>
  <tr>
    <td class="field_title_left"><?php print $max_record_count_str[$lang];?> </td>
    <td class="field_data2">
		<input name="log_maxcount" <?php print $disabled;?> type="text" maxlength="6" value="<?php print $visprofile->GetFieldValue("MaxCount");?>">
	</td>
  </tr>
  <tr>
    <td class="field_title_left"><?php print $max_record_days_str[$lang];?> </td>
    <td class="field_data2">
		<input name="log_maxday" <?php print $disabled;?> type="text" maxlength="2" value="<?php print $visprofile->GetFieldValue("MaxDays");?>">
	</td>
  </tr>
  <tr>
    <td colspan="2">
	<input type="submit" name="vis_config_submit" value="<?php print $setup_str[$lang];?>">
	<input type="button" onClick="vis_config_refresh();" name="refresh" value="<?php print $refresh_str[$lang];?>">
	</td>
   </tr>
</table>
</form>

</div>
</body>
</html>
