<?php
require("./include/authenticated.php");
require_once("./include/function.php");
require_once("./include/log.php");

$lang=load_lang();

$tip_str=array(
	"选择关机类型；延时关机分钟数需输入正整数值。",
	"Select shutdown type; time delay need integer(larger than or equal 0)."
);

$shutdown_type_str=array(
	"关机类型",
	"Shutdown Type"
);

$shutdown_str=array(
	"关闭",
	"Shutdown"
);

$reboot_str=array(
	"重启",
	"Reboot  "
);

$delay_time_str=array(
	"延时[0-1000]",
	"Delay-time"
);

$minute_str=array(
	" 分钟",
	" Minute(s)"
);

$execute_str=array(
	"执 行",
	"Implement"
);
$shutdown_ok_str=array(
	"执行成功.",
	"Implement OK."
);
$shutdown_failed_str=array(
	"关机失败！",
	"Failed to shutdown!"
);
$shutdown_delay_set_error_str=array(
	"关机延时设置错误！",
	"Set delay time error!"
);
?>


<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<link rel="stylesheet" href="css/target.css" type="text/css" />
<script defer type="text/javascript" src="js/pngfix.js"></script>

</head>

<body>
<div id="shutdown_target">
	<div id="panel_top"></div>
	
	<div id="shutdown">
	
<?php 
$message = "";
$log = new Log();
$action_str = array();
if(isset($_POST["shutdown_submit"]) )
{
	$shutdown = $_POST["shutdown"];
	$delay = $_POST["shutdown_delay"];
	//如果含有非数字字符则设置延时关机为0.
	if( preg_match("/[^0-9]/", $delay) )
	{
		$delay = 0;
	}
	
	$shutdowncommand = "export LANG=C;/usr/bin/sudo /sbin/shutdown ";
	if ($shutdown == "shutdown")
	{
		$action_str = $shutdown_str;
		$shutdowncommand .= "-h ";
	}
	else if ($shutdown == "reboot")
	{
		$action_str = $reboot_str;
		$shutdowncommand .= "-r ";
	}
	else
	{
		$action_str = $reboot_str;
		$shutdowncommand .= "-r ";
	}
	
//	if( $delay < 0 ) $delay = 0;
//	if( $delay > 1000) $delay = 1000;

	$shutdowncommand .= $delay;

	exec($shutdowncommand, $output, $retval);
	
	if($retval == 0)
	{
		$message = $shutdown_ok_str[$lang];
		$log->VstorWebLog(LOG_WARN, MOD_SYSTEM, "{$action_str[EN_LANG]} system ok.");
		$log->VstorWebLog(LOG_WARN, MOD_SYSTEM, "{$action_str[CN_LANG]}系统成功。", CN_LANG);
	}
	else
	{
		$message = $shutdown_failed_str[$lang];
		$log->VstorWebLog(LOG_ERROR, MOD_SYSTEM, "{$action_str[EN_LANG]} system failed.");
		$log->VstorWebLog(LOG_ERROR, MOD_SYSTEM, "{$action_str[CN_LANG]}系统失败。", CN_LANG);
	}
}

?>
	<script type="text/javascript">
	function shutdown_confirm()
	{
		var type, min;
		var obj = document.getElementsByName("shutdown");
		for (i = 0; i < obj.length; i++)
		{
			if( obj[i].checked )
				type = obj[i].value;
		}
		if(type=="reboot")
		{
			type = "<?php print $reboot_str[$lang];?>";
		}
		else
		{
			type = "<?php print $shutdown_str[$lang];?>";	
		}
		min = document.shutdown_form.shutdown_delay.value;
		// 判断有效字符
		var valid_char = "0123456789";
		for(var i=0; i<min.length; i++)
		{
			var chr = min.charAt(i);
			if( valid_char.indexOf(chr) == -1 )
			{
				alert('<?php print $shutdown_delay_set_error_str[$lang];?>');
				document.shutdown_form.shutdown_delay.focus();
				document.shutdown_form.shutdown_delay.select();
				return false;
			}
		}
		if(min < 0 || min>1000)
		{
			 alert('<?php print $shutdown_delay_set_error_str[$lang];?>');
			 document.shutdown_form.shutdown_delay.focus();
			 document.shutdown_form.shutdown_delay.select();
			 return false;
		}
		
		var info = "<?php print $shutdown_type_str[$lang];?>: " + type + "\n"
					+ "<?php print $delay_time_str[$lang];?>: " + min + "<?php print $minute_str[$lang];?>\n"
					+ "<?php print $execute_str[$lang];?>?";
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
	
	<form id="shutdown_form" name="shutdown_form" action="shutdown_target.php" method="post"> 
      <table width="80%" border="0" cellpadding="6" align="center" height="100%">
		<tr>
			<td colspan="2" class="tip_data">
			<img src="images/tip.gif" /><?php print $tip_str[$lang]; ?>
			</td>
		</tr>
        <tr>
          <td class="field_title"><?php print $shutdown_type_str[$lang]; ?>
		  </td>
		  		  	
		  <td class="field_data1">
			  <table align="center">
				<tr>
				<td align="left">
			  	<input type="radio" name="shutdown" value="shutdown"                   /><?php print $shutdown_str[$lang]; ?><br/>
				<input type="radio" name="shutdown" value="reboot" checked="checked" /><?php print $reboot_str[$lang]; ?>
			  	</td>
			  	</tr>
			  </table>
		  </td>
        </tr>

		<tr>
			<td class="field_title"><?php print $delay_time_str[$lang]; ?>
			</td>
			<td class="field_data2">
				<input type="text" maxlength="4" id="shutdown_delay" name="shutdown_delay"  value="0" size="4"/>&nbsp;<?php print $minute_str[$lang]; ?>
			</td>
		</tr>

		<tr>
			<td colspan="2">
			<input type="submit" name="shutdown_submit" onClick="return shutdown_confirm();" 
				value="<?php print $execute_str[$lang]; ?>" />
			</td>
		</tr>
      </table>
	</form>
<?php 
if($message != "")
	print_msg_block($message);
?>
	</div>

	<div id="panel_btm"></div>
</div>

</body>
</html>

