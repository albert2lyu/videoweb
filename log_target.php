<?php
require("./include/authenticated.php");
require_once("./include/function.php");
require_once("./include/log.php");

$lang=load_lang();

$vstor_web_log_str=array(
	" 网页日志记录",
	" Web Log"
);
$action_str=array(
	"动作",
	"Action"
);
$all_log_str=array(
	"全部",
	"All"
);
$info_log_str=array(
	"信息",
	"Info"
);
$warn_log_str=array(
	"警告",
	"Warn"
);
$error_log_str=array(
	"错误",	
	"Error"
);
$refresh_log_str=array(
	"刷新",
	"Refresh"
);
$download_log_str=array(
	"下载",
	"Download"
);
$clear_log_str=array(
	"全部清空",
	"Clear All"
);
$confirm_clear_log_tip_str=array(
	"确定清空所有的日志？",
	"Clear all logs, confirm to continue?"
);
$index_str=array(
	"序号",
	"Index"
);
$log_level_str=array(
	"日志类型",
	"Log Type"
);
$log_time_str=array(
	"记录时间",
	"Record Time"
);
$remote_ip_str=array(
	"连接端IP",
	"Remote IP"
);
$log_message_str=array(
	"内容",
	"Message"
);
$none_str=array(
	"- 无 -",
	"- None -"
);
$module_str=array(
	"模块",
	"Module"
);
$system_mod_str=array(
	"系统",
	"System"
);
$network_mod_str=array(
	"网络",
	"Network"
);
$clock_mod_str=array(
	"时钟",
	"Clock"
);
$raid_mod_str=array(
	"RAID",
	"RAID"
);
$volume_mod_str=array(
	"卷",
	"Volume"
);
$vis_mod_str=array(
	"NVR",
	"NVR"
);
$account_mod_str=array(
	"帐户",
	"Account"
);
$record_count_str=array(
	"记录数",
	"Record Count"
);
?>

<?php 
$show = "all";
$action = "";
$module = "all";
$message = "";
$log_list_show = array();
$log = new Log();
$type_select_arr = array("", "", "", "");
$mod_select_arr = array("", "", "", "", "", "", "", "");

/*
 * 表单处理----------------
 */

if( isset($_GET['show']) && $_GET['show']!="" )
{
	$show = $_GET['show'];
}
if( isset($_GET['action']) && $_GET['action']!="" )
{
	$action = $_GET['action'];
}
if( isset($_GET['module']) && $_GET['module']!="" )
{
	$module = $_GET['module'];
}

// 清空log
if($action == "clear")
{
	$log->ClearLog();
	$log->VstorWebLog(LOG_WARN, MOD_SYSTEM, "clear all logs.");
	$log->VstorWebLog(LOG_WARN, MOD_SYSTEM, "清空所有日志。", CN_LANG);
}

/* LOG结构：
array(
	array(
		"level"=>"information",
		"time"=>"2010-10-10 10:10:10",
		"remote_ip"=>"192.168.58.43",
		"module"=>"System",
		"log"=>"Hello, World!"
	),
	...
)，
*/
$log_list = $log->GetLog($lang);
if($log_list !== FALSE)
{
	// 通过log类型过滤显示log
	if($show == "all")
	{
		$type_select_arr[0] = " selected ";
		$log_list_show = $log_list;
	}
	else if($show == "info")
	{
		$type_select_arr[1] = " selected ";
		foreach($log_list as $entry)
		{
			if($entry['level'] == LOG_INFOS)
			{
				$log_list_show[] = $entry;
			}
		}
	}
	else if($show == "warn")
	{
		$type_select_arr[2] = " selected ";
		foreach($log_list as $entry)
		{
			if($entry['level'] == LOG_WARN)
			{
				$log_list_show[] = $entry;
			}
		}
	}
	else if($show == "error")
	{
		$type_select_arr[3] = " selected ";
		foreach($log_list as $entry)
		{
			if($entry['level'] == LOG_ERROR)
			{
				$log_list_show[] = $entry;
			}
		}
	}
	else
	{
		$type_select_arr[0] = " selected ";
		$show = "all";
		$log_list_show = $log_list;
	}
	
	// 再通过LOG模块过滤LOG显示
	$tmp_log_list_show = array();
	if($module == "all")
	{
		$mod_select_arr[0] = " selected ";
		$tmp_log_list_show = $log_list_show;
	}
	else if($module == "system")
	{
		$mod_select_arr[1] = " selected ";
		foreach($log_list_show as $entry)
		{
			if($entry['module'] == MOD_SYSTEM)
			{
				$tmp_log_list_show[] = $entry;
			}
		}
	}
	else if($module == "network")
	{
		$mod_select_arr[2] = " selected ";
		foreach($log_list_show as $entry)
		{
			if($entry['module'] == MOD_NETWORK)
			{
				$tmp_log_list_show[] = $entry;
			}
		}
	}
	else if($module == "clock")
	{
		$mod_select_arr[3] = " selected ";
		foreach($log_list_show as $entry)
		{
			if($entry['module'] == MOD_CLOCK)
			{
				$tmp_log_list_show[] = $entry;
			}
		}
	}
	else if($module == "nvr")
	{
		$mod_select_arr[4] = " selected ";
		foreach($log_list_show as $entry)
		{
			if($entry['module'] == MOD_MVP)
			{
				$tmp_log_list_show[] = $entry;
			}
		}
	}
	else if($module == "volume")
	{
		$mod_select_arr[5] = " selected ";
		foreach($log_list_show as $entry)
		{
			if($entry['module'] == MOD_VOLUME)
			{
				$tmp_log_list_show[] = $entry;
			}
		}
	}
	else if($module == "account")
	{
		$mod_select_arr[6] = " selected ";
		foreach($log_list_show as $entry)
		{
			if($entry['module'] == MOD_ACCOUNT)
			{
				$tmp_log_list_show[] = $entry;
			}
		}
	}
	else if($module == "raid")
	{
		$mod_select_arr[7] = " selected ";
		foreach($log_list_show as $entry)
		{
			if($entry['module'] == MOD_RAID)
			{
				$tmp_log_list_show[] = $entry;
			}
		}
	}
	else
	{
		$mod_select_arr[0] = " selected ";
		$module = "all";
		$tmp_log_list_show = $log_list_show;
	}
	$log_list_show = $tmp_log_list_show;
	
	//
}
else
{
	$log_list_show = FALSE;
}
/*
 * -----------------表单处理
 */
?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<link rel="stylesheet" href="css/target.css" type="text/css" />
<script defer type="text/javascript" src="js/pngfix.js"></script>
<script defer type="text/javascript" src="js/function.js"></script>
<script type="text/javascript">
function SelectLogType(obj)
{
	var module = '<?php print $module;?>';
	var show = obj.value;
	window.location.href="log_target.php?show=" + show + "&module=" + module;
	return true;
}
function SelectLogModule(obj)
{
	var show = '<?php print $show;?>';
	var module = obj.value;
	window.location.href="log_target.php?show=" + show + "&module=" + module;
	return true;
}
function clear_confirm()
{
	var tip = '<?php print $confirm_clear_log_tip_str[$lang];?>';
	if( confirm(tip) )
	{
		return true;
	}
	else
	{
		return false;
	}
}

</script>
</head>

<body>
<table align="center" width="100%">
<tr><td class="bar_nopanel"><?php print $vstor_web_log_str[$lang];?></td></tr>
</table>

<?php 
// 显示的信息类型的字体样式设置
$all_style = "log_link_font";
$info_style = "log_link_font";
$warn_style = "log_link_font";
$error_style = "log_link_font";
if($show == "all")
{
	$all_style="log_link_font_sel";
}
else if($show == "info")
{
	$info_style = "log_link_font_sel";
}
else if($show == "warn")
{
	$warn_style = "log_link_font_sel";
}
else if($show == "error")
{
	$error_style = "log_link_font_sel";
}

// log控制区
print "
	<div align=\"left\" style=\"width:100%;height:30px;border:1 #404040 solid;
		vertical-align:middle;background:#404040;\">
	";
print "
	<font style=\"color:#FFFFFF;\">{$log_level_str[$lang]}" . ": </font>" . "
	<select name=\"type_select\" onChange=\"SelectLogType(this);\" style=\"font-size:12px;\">
		<option value=\"all\" {$type_select_arr[0]}>{$all_log_str[$lang]}
		<option value=\"info\" {$type_select_arr[1]}>{$info_log_str[$lang]}
		<option value=\"warn\" {$type_select_arr[2]}>{$warn_log_str[$lang]}
		<option value=\"error\" {$type_select_arr[3]}>{$error_log_str[$lang]}
	</select>
	";
print "
	<font style=\"color:#FFFFFF;\">{$module_str[$lang]}" . ": </font>" . "
	<select name=\"module_select\" onChange=\"SelectLogModule(this);\" style=\"font-size:12px;\">
		<option value=\"all\" {$mod_select_arr[0]}>{$all_log_str[$lang]}
		<option value=\"system\" {$mod_select_arr[1]}>{$system_mod_str[$lang]}
		<option value=\"network\" {$mod_select_arr[2]}>{$network_mod_str[$lang]}
		<option value=\"clock\" {$mod_select_arr[3]}>{$clock_mod_str[$lang]}
		<option value=\"nvr\" {$mod_select_arr[4]}>{$vis_mod_str[$lang]}
		<option value=\"volume\" {$mod_select_arr[5]}>{$volume_mod_str[$lang]}
		<option value=\"account\" {$mod_select_arr[6]}>{$account_mod_str[$lang]}
		<option value=\"raid\" {$mod_select_arr[7]}>{$raid_mod_str[$lang]}
	</select>
	";
print "
	<input type=\"text\" style=\"width:20px;visibility:hidden;\" />
	";
print "
	<font style=\"color:#FFFFFF;\">{$action_str[$lang]}" . ": </font>" . "
	<a href=\"log_target.php?show={$show}&module={$module}\" class=\"log_link_font\">{$refresh_log_str[$lang]}</a>
	<a href=\"log_target.php?action=clear\" class=\"log_link_font\" onClick=\"return clear_confirm();\">{$clear_log_str[$lang]}</a>
	";
print "
	<input type=\"text\" style=\"width:120px;visibility:hidden;\" />
	";
print "
	<font style=\"color:#FFFFFF;\">{$record_count_str[$lang]}" . ": ". count($log_list_show) . "</font>
	";
print "
	</div>
	";

// 显示log列表的标题
print "
<div style=\"width:100%;border:1 #CCCCCC solid;\">
	<table width=\"100%\" border=\"1\" cellspacing=\"0\" cellpadding=\"1\">
	  <tr>
	    <td width=\"45\" class=\"field_title_left\">{$index_str[$lang]}</td>
	    <td width=\"70\" class=\"field_title_left\">{$log_level_str[$lang]}</td>
	    <td width=\"140\" class=\"field_title_left\">{$log_time_str[$lang]}</td>
	    <td width=\"100\" class=\"field_title_left\">{$remote_ip_str[$lang]}</td>
	    <td class=\"field_title_left\">{$log_message_str[$lang]}</td>
	  </tr>
	</table>
</div>
";

// 显示log列表区
print "
<div style=\"width:100%;height:432px;overflow:auto;border:1 #CCCCCC solid;\">
	<table width=\"100%\" border=\"1\" cellspacing=\"0\" cellpadding=\"1\">
";

/* LOG结构：
array(
	array(
		"level"=>"information",
		"time"=>"2010-10-10 10:10:10",
		"remote_ip"=>"192.168.58.43",
		"module"=>"System",
		"log"=>"Hello, World!"
	),
	...
)，
*/
$bHasItem = FALSE;
if($log_list_show !== FALSE && count($log_list_show)>0)
{
	$td_class = "field_data1_left";
	$index = count($log_list_show);
	foreach( $log_list_show as $entry )
	{
		print "<tr>";
		print "<td width=\"45\" class=\"{$td_class}\" style=\"text-align:center\">{$index}</td>";
		print "<td width=\"70\" class=\"{$td_class}\" style=\"text-align:center\">";
		switch( $entry['level'] )
		{
			case LOG_INFOS:
				print "
				<img src=\"./images/log_info.gif\" title=\"{$info_log_str[$lang]}\"/>
				";
				break;
			case LOG_WARN:
				print "
				<img src=\"./images/log_warning.gif\" title=\"{$warn_log_str[$lang]}\"/>
				";
				break;
			case LOG_ERROR:
				print "
				<img src=\"./images/log_error.gif\" title=\"{$error_log_str[$lang]}\"/>
				";
				break;
			default:
				break;
		}
		print "</td>";
		print "<td width=\"140\" class=\"{$td_class}\">{$entry['time']}</td>";
		print "<td width=\"100\" class=\"{$td_class}\">{$entry['remote_ip']}</td>";
		print "<td class=\"{$td_class}\">{$entry['log']}</td>";
		print "</tr>";
		
		$bHasItem = TRUE;
		$index--;
		if($td_class == "field_data1_left")
		{
			$td_class = "field_data2_left";
		}
		else
		{
			$td_class = "field_data1_left";
		}
	}
}
else
{
	print "<tr><td class=\"field_data2\" colspan=\"5\">{$none_str[$lang]}</td></tr>";
}

print "
	</table>
</div>
	";
?>

</body>
</html>


