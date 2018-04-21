<?php
require_once("./include/function.php");
require_once("./include/auth.php");
require_once("./include/disk.php");
require_once("./include/log.php");

$lang=load_lang();

$format_disk_str=array(
	"格式化磁盘",
	"Format Disk"
);
$implement_str=array(
	"执行",
	"Implement"
);
$cancel_str=array(
	"关闭",
	"Close"
);
$input_password_str=array(
	"输入超级密码",
	"Input super password"
);
$implement_format_str=array(
	"执行格式化磁盘：",
	"Implement format: "
);
$format_ok_str=array(
	"格式化成功。",
	"Format success."
);
$format_failed_str=array(
	"格式化失败！",
	"Format failed!"
);
$ps_error_str=array(
	"密码错误！",
	"Password incorrect!"
);
$formatting_str=array(
	"格式化中……",
	"Formatting ..."
);
$tip_str=array(
	"格式化造成磁盘数据丢失并不可恢复！",
	"The operation of formatting will destroy the data on the disk!"
);
?>

<html>
<head>
<title><?php print $format_disk_str[$lang];?></title>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<link rel="stylesheet" href="css/target.css" type="text/css" />
<script defer type="text/javascript" src="js/pngfix.js"></script>
<script type="text/javascript">
function closewindow()
{
	window.close();
	return true;
}
function do_format(disk, name, ip, ppage)
{
	document.format_form.disk.value = disk;
	document.format_form.name.value = name;
	document.format_form.ip.value = ip;
	document.format_form.ppage.value = ppage;
	return true;
}

</script>
</head>
<body>

<?php 
$disk = "";
$disk_dev = "";
$disk_name = "";
$ip = "";
$ppage = "";
$log = new Log();

if( isset($_GET['ppage']) )
{
	$ppage = $_GET['ppage'];
}
else if(isset($_POST['ppage']))
{
	$ppage = $_POST['ppage'];
}
else
{
	exit("No Access!");
}

if(isset($_GET['disk']))
{
	$disk = $_GET['disk'];
	$disk_dev = $disk;
}
if(isset($_POST['disk']))
{
	$disk = $_POST['disk'];
	$disk_dev = $disk;
}

if(isset($_GET['ip']))
{
	$ip = $_GET['ip'];
}
if(isset($_POST['ip']))
{
	$ip = $_POST['ip'];
}

if(isset($_GET['name']))
{
	$disk_name = $_GET['name'];
}
if(isset($_POST['name']))
{
	$disk_name = $_POST['name'];
}

/*
 * 表单处理部分
 */

// 验证密码
if(isset($_POST['password']))
{
	$username = "superadmin";
	$password = $_POST['password'];
	if( check_authenticated($username, $password) !== TRUE )
	{
		prev_ui();
		print "<script type=\"text/javascript\">";
		print "alert('{$ps_error_str[$lang]}');";
		print "</script>";
	}
	else
	{
		// 执行
		if(FormatDisk($disk_dev) === FALSE)
		{
			formatted_ui($format_failed_str[$lang]);
			$log->VstorWebLog(LOG_ERROR, MOD_SYSTEM, "format disk [" . $disk_name . "](" . $disk_dev . ") failed.");
			$log->VstorWebLog(LOG_ERROR, MOD_SYSTEM, "格式化磁盘 [" . $disk_name . "](" . $disk_dev . ")失败。", CN_LANG);
		}
		else
		{
			formatted_ui($format_ok_str[$lang]);
			$log->VstorWebLog(LOG_WARN, MOD_SYSTEM, "format disk [" . $disk_name . "](" . $disk_dev . ") ok.");
			$log->VstorWebLog(LOG_WARN, MOD_SYSTEM, "格式化磁盘[" . $disk_name . "](" . $disk_dev . ")成功。", CN_LANG);
			// 刷新父页
			print "<script type=\"text/javascript\">";
			//print "window.opener.location.href=window.opener.location.href + '{$ip}';";
			print "window.opener.location.href= '{$ppage}?ip=' + '{$ip}';";
			print "</script>";
		}
	}
}
else
{
	prev_ui();
}


function prev_ui()
{
	global $implement_format_str, $lang, $disk_name, $tip_str, $input_password_str;
	global $disk, $cancel_str, $ip, $ppage, $implement_str;
	print "
		<form name=\"format_form\" id=\"format_form\" action=\"formatdisk.php\" method=\"post\">
		<table align=\"center\" width=\"100%\">
			<tr>
			<td class=\"bar_nopanel\">{$implement_format_str[$lang]}{$disk_name}</td>
			</tr>
		</table>
		
		<input type=\"hidden\" name=\"disk\" id=\"disk\" value=\"{$disk}\">
		<input type=\"hidden\" name=\"name\" id=\"name\" value=\"{$disk_name}\">
		<input type=\"hidden\" name=\"ip\" id=\"ip\" value=\"{$ip}\">
		<input type=\"hidden\" name=\"ppage\" id=\"ppage\" value=\"{$ppage}\">
		
		<table width=\"100%\" border=\"0\" align=\"center\" cellpadding=\"6\">
		  <tr>
		    <td colspan=\"2\" class=\"tip_data_red\"><img src=\"images/tip.gif\" />{$tip_str[$lang]}</td>
		  </tr>
		  <tr>
		    <td align=\"left\" class=\"field_title\">{$input_password_str[$lang]}</td>
		    <td class=\"field_data2\">
			  <input name=\"password\" type=\"password\" class=\"user_passwd\" id=\"password\" maxlength=\"16\" />
			</td>
		  </tr>
		  <tr>
		    <td colspan=\"2\" align=\"center\" valign=\"middle\">
			<input type=\"submit\" id=\"implement_submit\" onClick=\"return do_format('{$disk}', '{$disk_name}', '{$ip}', '{$ppage}');\" name=\"implement_submit\" value=\"{$implement_str[$lang]}\" />
			&emsp;
		    <input type=\"button\" id=\"close\" onClick=\"return closewindow();\" name=\"close\" value=\"{$cancel_str[$lang]}\" />
		    </td>
		  </tr>
		</table>
		</form>
		<script type=\"text/javascript\">
			window.document.format_form.password.focus();
		</script>
	";
}

function formatted_ui($msg)
{
	global $cancel_str, $lang;
	print "
	<table width=\"100%\" height=\"100%\" align=\"center\">
	<tr>
		<td>
			<span style=\"color:#4B0082; font-size:16px;\">{$msg}</span>
			<p><br/>
			<a href=\"#\" onClick=\"return closewindow();\" style=\"font-size:14px;\">{$cancel_str[$lang]}</a>
		</td>
	</tr>
	</table>
	";
}

?>

</body>
</html>


