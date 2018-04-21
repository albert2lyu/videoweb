<?php
require("./include/authenticated.php");
require_once("./include/function.php");
require_once("./include/log.php");

$lang=load_lang();

$ping_test_str=array(
	"Ping测试",
	"Ping Test"
);
$host_ip_addr_str=array(
	"主机IP地址",
	"Host IP Address"
);
$count_str=array(
	"数量",
	"Count"
);
$count_tip_str=array(
	"发送（或接收）N个包后停止ping操作。",
	"Stop after sending (and receiving) N packets."
);
$implement_ping_str=array(
	"执行ping测试",
	"Implement Ping Test"
);
$cmd_output_str=array(
	"命令输出：",
	"Command Ouptut: "
);
$ip_error_str=array(
	"IP地址不正确!",
	"IP address is incorrect!"
);
$ping_test_ok_str=array(
	"Ping测试通过。",
	"Ping test OK."
);
$ping_test_failed_str=array(
	"Ping测试未通过！",
	"Ping test FAILED!"
);
?>

<?php 
$log = new Log();
$do_ping = FALSE;
$message = "";
$host_ip = "";
$ping_count = 3;
if( isset($_POST['ping_submit']) && isset($_POST['hostip']) && isset($_POST['count_select']) )
{
	$host_ip = $_POST['hostip'];
	$ping_count = $_POST['count_select'];
	if( IsIpOk($host_ip) )
	{
		$do_ping = TRUE;
	}
	else
	{
		$message = $ip_error_str[$lang];
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
function check_ip()
{
	var ipaddr = document.ping_form.hostip.value;
	if(ipaddr == "")
	{
		document.getElementById("ip_span").innerHTML = "<img src='./images/right_icon.png' style='visibility: hidden;'></img>";
		return true;
	}
	var retval = IsIpOk(ipaddr);
	if(retval == true)
	{
		document.getElementById("ip_span").innerHTML = "<img src='./images/right_icon.png'></img>";
		//document.ping_form.ping_submit.disabled = false;
	}
	else
	{
		document.getElementById("ip_span").innerHTML = "<img src='./images/error_icon.png'></img>";
		//document.ping_form.ping_submit.disabled = true;
	}
	return true;
}
</script>
</head>

<body>

<table align="center" width="100%">
  <tr>
    <td class="bar_nopanel"><?php print $ping_test_str[$lang];?></td>
  </tr>
</table>
<form name="ping_form" id="ping_form" action="ping_target.php" method="post">
<table width="100%" border="0" cellpadding="6" align="center">
  <tr>
    <td colspan="2" class="title">&nbsp;</td>
  </tr>
  <tr>
    <td class="field_title_left"><?php print $host_ip_addr_str[$lang];?></td>
    <td class="field_data1_left">
	<input type="text" onchange ="return check_ip();"  onkeyup="return check_ip();" name="hostip" id="hostip" value="<?php print $host_ip;?>">
	<span id="ip_span"><img src='./images/error_icon.png' style="visibility: hidden;"></img></span>
	</td>
  </tr>
  <tr>
    <td class="field_title_left"><?php print $count_str[$lang];?></td>
    <td class="field_data2_left">
	    <select name="count_select">
		<script type="text/javascript">
		var option_str = "";
		for(var i=1; i<=10; i++)
		{
			if(i == <?php print $ping_count;?>)
			{
				option_str = "<option value=" + i + " selected>" + i;
			}
			else
			{
				option_str = "<option value=" + i + ">" + i;
			}
			document.write( option_str );
		}
		</script>
	    </select>
    <br/><?php print $count_tip_str[$lang];?></td>
  </tr>
  <tr>
    <td colspan="2" align="left">
		<input type="submit" name="ping_submit" value="<?php print $implement_ping_str[$lang];?>">
	</td>
  </tr>
  <tr>
    <td colspan="2" class="ping_output">
		<div style="font-weight:bold;font-size:14px;"><?php print $cmd_output_str[$lang];?></div>
		<?php 
		if( $do_ping === TRUE )
		{
			ob_end_flush();
			print "<pre style=\"font:13px;color:#444444;\">";
			system("export LANG=C; /usr/bin/sudo /bin/ping -c {$ping_count} " . escapeshellarg($host_ip), $retval);
			print "</pre>";
			
			if($retval == 0)
			{
				$message = $ping_test_ok_str[$lang];
				$log->VstorWebLog(LOG_INFOS, MOD_SYSTEM, "ping {$host_ip}: passed.");
				$log->VstorWebLog(LOG_INFOS, MOD_SYSTEM, "ping {$host_ip}: 通过。", CN_LANG);
			}
			else
			{
				$message = $ping_test_failed_str[$lang];
				$log->VstorWebLog(LOG_WARN, MOD_SYSTEM, "ping {$host_ip}: unreachable.");
				$log->VstorWebLog(LOG_WARN, MOD_SYSTEM, "ping {$host_ip}: 未通过。", CN_LANG);
			}
			print_msg_block($message);
			$message = "";
		}
		?>
		<br/>
	</td>
  </tr>
</table>
</form>

<script type="text/javascript">
window.document.ping_form.hostip.focus();
check_ip();
</script>

<?php 
if($message != "")
{
	print_msg_block($message);
}
?>

</body>
</html>