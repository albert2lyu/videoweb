<?php
require("./include/authenticated.php");
require_once("./include/function.php");
require_once("./include/timezone.php");
require_once("./include/log.php");

$lang=load_lang();

$bar_date_str=array(
	"�ֶ��޸����ں�ʱ��",
	"Customize System Data&Time"
);

$modify_time_str=array(
	"����ʱ��",
	"Modify Time"
);
$getlocaltime_str=array(
	"����ʱ��",
	"Local time"
);
$update_str=array(
	"�� ��",
	"Update"
);
$default_set_str=array(
	"Ĭ��ʱ������",
	"Set Default Timezone"
);
$bar_ntp_str=array(
	"NTP����",
	"NTP Setup"
);
$open_str=array(
	"����",
	"Open"
);
$close_str=array(
	"�ر�",
	"Close"
);
$timezone_config_str=array(
	"ʱ������",
	"Time Zone Setup"
);
$select_zone_str=array(
	"ѡ��ʱ��",
	"Select Zone"
);
$set_utc_str=array(
	"ϵͳʱ��ʹ��UTC",
	"System clock uses UTC"
);
$tip_ntp_str=array(
	"��շ�����IP��ַ����,�ٵ�������¡���ť������ȡ����NTP������ʱ��ͬ�������á�",
	"To cancel time synchronization: Empty NTP server IP content, then click \"Update\" button."
);
$set_ntp_server_str=array(
	"���ñ���ΪNTP������",
	"Set localhost as NTP server"
);
$set_ntp_ip_str=array(
	"����NTP������IP��ַ",
	"Set NTP Server IP Address:"
);
$modify_time_ok=array(
	"ʱ���޸ĳɹ���",
	"Modify time OK."
);
$modify_time_failed=array(
	"ʱ���޸�ʧ�ܣ�",
	"Modify time failed!"
);

$ntp_ip_error=array(
	"NTP ������IP��ַ��ʽ����",
	"Invalid NTP server IP!"
);
$ntp_set_ok=array(
	"NTP���óɹ���",
	"Set NTP OK."
);
$ntp_set_failed=array(
	"NTP����ʧ�ܣ�",
	"Set NTP failed!"
);
$zone_set_ok_str=array(
	"ʱ�����óɹ���",
	"Set time zone ok."
);
$zone_set_error_str=array(
	"ʱ������ʧ�ܣ�",
	"Set time zone failed!"
);
?>
<?php 
function isNtpdRunning()
{
	$command = "export LANG=C; /usr/bin/sudo /sbin/pidof ntpd";
	exec($command, $output, $retval);
	return $retval==0 ? TRUE : FALSE;
}
function GetNtpServerIp()
{
	//	*/2 * * * * /usr/sbin/ntpdate -u 192.168.51.10
	//  */2 * * * * /sbin/hwclock -w
	$command = "export LANG=C; /usr/bin/sudo /usr/bin/crontab -u root -l";
	exec($command, $output, $retval);
	if($retval != 0)
		return "";
	
	foreach($output as $line)
	{
		if( preg_match("|/usr/sbin/ntpdate -u ([0-9\.]*)|i", trim($line), $match) )
		{
			return $match[1];
		}
	}
	
	return "";	
}
?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<link rel="stylesheet" href="css/target.css" type="text/css" />
<script defer type="text/javascript" src="js/pngfix.js"></script>
<script defer type="text/javascript" src="js/function.js"></script>
<script type="text/javascript">
function getlocaltime()
{
    var today = new Date();
    var yearNow = today.getFullYear();
    var monthNow = today.getMonth()+1;
    var dateNow = today.getDate();
    var hourNow = today.getHours();
    var minNow = today.getMinutes();
    var secNow = today.getSeconds();

    if(monthNow<10) monthNow = "0" + monthNow;
    if(dateNow<10) dateNow = "0" + dateNow;
    if(hourNow<10) hourNow = "0" + hourNow;
    if(minNow<10) minNow = "0" + minNow;
    if(secNow<10) secNow = "0" + secNow;

    var time_str = yearNow + "-" + monthNow + "-" + dateNow + " "
    			   + hourNow + ":" + minNow + ":" + secNow;
    document.clock_form.time.value = time_str;
    return true;
}
function setutc()
{
	var checked = document.zone_form.utc_enable.checked;
	if(checked == true)
	{
		document.zone_form.utc_value.value = "yes";
	}
	else
	{
		document.zone_form.utc_value.value = "no"
	}

	return true;
}
function check_ntp_ip()
{
	var ipaddr = document.ntp_form.ntp_ip.value;

	var retval = IsIpOk(ipaddr);
	if(retval == true || ipaddr == "")
	{
		document.getElementById("ntp_ip_span").innerHTML = "<img src='./images/right_icon.png'></img>";
		document.ntp_form.submit.disabled = false;
	}
	else
	{
		document.getElementById("ntp_ip_span").innerHTML = "<img src='./images/error_icon.png'></img>";
		document.ntp_form.submit.disabled = true;
	}
	return true;
}
</script>
<?php 
if($lang==0)//����
{
?>
	<script language="javascript" src="js/calendar_cn.js" type="text/javascript"></script>
<?php 
}
else if($lang==1)//English
{
?>
	<script language="javascript" src="js/calendar_en.js" type="text/javascript"></script>
<?php 
}
?>

</head>

<body>
<div id="clock_target">
	<div id="panel_top"></div>

	<div id="clock">
	<table align="center"><tr><td class="bar"><?php print $bar_date_str[$lang]; ?></td></tr></table>
<?php 
/*
 * ��������-----------------
 */
// �޸�ʱ��
$message = "";
$timezone = new Timezone();
$log = new Log();

if(isset($_POST["time"]))
{
	$time_str = trim($_POST['time']);
	exec("/usr/bin/sudo  /bin/date -s \"" . $time_str . "\"", $output, $retval);
	exec("/usr/bin/sudo /sbin/hwclock -w");
	
	if($retval == 0)
	{
		$message = $modify_time_ok[$lang];
		$log->VstorWebLog(LOG_INFOS, MOD_CLOCK, "update time to " . $time_str . " ok.");
		$log->VstorWebLog(LOG_INFOS, MOD_CLOCK, "�޸�ʱ��Ϊ" . $time_str . "�ɹ���", CN_LANG);
	}
	else
	{
		$message = $modify_time_failed[$lang];
		$log->VstorWebLog(LOG_ERROR, MOD_CLOCK, "update time to " . $time_str . "failed.");
		$log->VstorWebLog(LOG_ERROR, MOD_CLOCK, "�޸�ʱ��Ϊ" . $time_str . "ʧ�ܡ�", CN_LANG);
	}
}

// ���ñ���Ϊntp������
if( isset($_POST['ntp_server_enable']) )
{
	$set_ntp_server = $_POST['ntp_server_enable'];
	if( $set_ntp_server == 0 )
	{
		// ����ntpd����
		exec("export LANG=C; /usr/bin/sudo /sbin/service ntpd restart");
		exec("export LANG=C; /usr/bin/sudo /sbin/chkconfig ntpd on");
		$log->VstorWebLog(LOG_INFOS, MOD_CLOCK, "set localhost as ntp server ok.");
		$log->VstorWebLog(LOG_INFOS, MOD_CLOCK, "���ñ���ΪNTP�������ɹ���", CN_LANG);
	}
	else if( $set_ntp_server == 1 )
	{
		// ֹͣntpd����
		$index = 0;
		while( isNtpdRunning() && ($index++)<10 )
		{
			exec("export LANG=C; /usr/bin/sudo /sbin/service ntpd stop");
		}
		exec("export LANG=C; /usr/bin/sudo /sbin/chkconfig ntpd off");
		$log->VstorWebLog(LOG_WARN, MOD_CLOCK, "unset localhost as ntp server ok.");
		$log->VstorWebLog(LOG_WARN, MOD_CLOCK, "ȡ������ΪNTP�������ɹ���", CN_LANG);
	}

}

// ����NTP server IP
if(isset($_POST['ntp_ip']))
{
	$ntp_server_ip = trim($_POST['ntp_ip']);
	if($ntp_server_ip == "") // ȡ��ntp��ʱ
	{
		$command = "export LANG=C; /usr/bin/sudo /usr/bin/crontab -u root -r";
		exec($command);
		$message = $ntp_set_ok[$lang];
		$log->VstorWebLog(LOG_INFOS, MOD_CLOCK, "unset ntp server ip ok.");
		$log->VstorWebLog(LOG_INFOS, MOD_CLOCK, "ȡ������NTP������IP��ַ�ɹ���", CN_LANG);
	}
	else
	{
		// �ж�IP��ʽ�Ƿ���ȷ
		if( IsIpOk($ntp_server_ip) )
		{
			// д��/opt/vstor/etc/ntptime.cron �ļ�
			$fp = fopen("/opt/vstor/etc/ntptime.cron", 'wt');
			if( $fp !== FALSE )
			{
				fputs($fp, "*/2 * * * * /usr/sbin/ntpdate -u {$ntp_server_ip}\n");
				fputs($fp, "*/2 * * * * /usr/sbin/hwclock -w\n");
				fflush($fp);
				fclose($fp);
				
				$command = "export LANG=C; /usr/bin/sudo /usr/bin/crontab -u root /opt/vstor/etc/ntptime.cron";
				exec($command);
				$message = $ntp_set_ok[$lang];
				$log->VstorWebLog(LOG_INFOS, MOD_CLOCK, "set ntp server ip to [". $ntp_server_ip . "] ok.");
				$log->VstorWebLog(LOG_INFOS, MOD_CLOCK, "����NTP������IP��ַΪ[". $ntp_server_ip . "]�ɹ���", CN_LANG);
			}
			else
			{
				$message = $ntp_set_failed[$lang];
			}
		}
		else
		{
			$message = $ntp_ip_error[$lang];
			$log->VstorWebLog(LOG_ERROR, MOD_CLOCK, "set ntp server ip to [". $ntp_server_ip . "] failed.");
			$log->VstorWebLog(LOG_ERROR, MOD_CLOCK, "����NTP������IP��ַΪ[". $ntp_server_ip . "]ʧ�ܡ�", CN_LANG);
		}
	}
}

// ʱ������
if( isset($_POST['timezone']) && isset($_POST['utc_value']) )
{
	$time_zone_value = trim($_POST['timezone']);
	$utc_enabled = ($_POST['utc_value']=="yes") ? TRUE : FALSE;
	if( $timezone->SetTimezone($time_zone_value, $utc_enabled) === TRUE )
	{
		$message = $zone_set_ok_str[$lang];
		$log->VstorWebLog(LOG_INFOS, MOD_CLOCK, "set time zone to [". $time_zone_value . "] ok.");
		$log->VstorWebLog(LOG_INFOS, MOD_CLOCK, "����ʱ��Ϊ[". $time_zone_value . "]�ɹ���", CN_LANG);
	}
	else
	{
		$message = $zone_set_error_str[$lang];
		$log->VstorWebLog(LOG_ERROR, MOD_CLOCK, "set time zone to [". $time_zone_value . "] failed.");
		$log->VstorWebLog(LOG_ERROR, MOD_CLOCK, "����ʱ��Ϊ[". $time_zone_value . "]ʧ�ܡ�", CN_LANG);
	}
}
// Ĭ��ʱ������
if( isset($_POST['df_zone_submit']) )
{
	if( $timezone->SetDefaultTimezone() === TRUE )
	{
		$message = $zone_set_ok_str[$lang];
		$log->VstorWebLog(LOG_INFOS, MOD_CLOCK, "set time zone to default zone ok.");
		$log->VstorWebLog(LOG_INFOS, MOD_CLOCK, "����ΪĬ��ʱ���ɹ���", CN_LANG);
	}
	else
	{
		$message = $zone_set_error_str[$lang];
		$log->VstorWebLog(LOG_ERROR, MOD_CLOCK, "set time zone to default zone failed.");
		$log->VstorWebLog(LOG_ERROR, MOD_CLOCK, "����ΪĬ��ʱ��ʧ�ܡ�", CN_LANG);
	}
}
/*
 * -----------------��������(END)
 */

// ��ȡ��ǰ���õ�ʱ����utc
$bUtcEnabled = FALSE;
$valueTimezone = "";
$timezone->GetTimezone($valueTimezone, $bUtcEnabled);
?>

<form id="clock_form" name="clock_form" action="clock_target.php" method="post"> 
      <table width="80%" border="0" cellpadding="6" align="center">
        <tr>
          <td class="field_title"><?php print $modify_time_str[$lang]; ?></td>
	  <td class="field_data1">
		<input type="text" size="20" value="<?php 
		//"2009-10-19 10:02:10";
		// ��ȡ��ǰϵͳʱ�䣬����ʽ��Ϊ��yyyy-mm-dd hh:mm:ss��
		date_default_timezone_set($valueTimezone);
		print date("Y-m-d H:i:s");
		?>" 
		 id="time" name="time" onClick="SelectDate(this,'yyyy-MM-dd hh:mm:ss',0,0)" readonly="readonly" />
		<input type="button" id="localtime" name="localtime" onClick="return getlocaltime();" value="<?php print $getlocaltime_str[$lang];?>" />
		</td>
        </tr>
	<tr >
	<td colspan="2"><input type="submit" name="submit" value="<?php print $update_str[$lang]; ?>" /></td>
	</tr>
      </table>
</form>

<form id="ntp_form" name="ntp_form" action="clock_target.php" method="post"> 
<table align="center"><tr><td class="bar"><?php print $bar_ntp_str[$lang]; ?></td></tr></table>
<table width="80%" border="0" cellpadding="6" align="center">
  <tr>
	<td class="field_title"><?php print $set_ntp_server_str[$lang];?></td>
	<td class="field_data1">
		<input type="radio" <?php print (isNtpdRunning()) ? "checked=\"checked\"" : "";?>  name="ntp_server_enable"  value="0" /><?php print $open_str[$lang];?>
		<input type="radio" <?php print (!isNtpdRunning()) ? "checked=\"checked\"" : "";?> name="ntp_server_enable"  value="1" /><?php print $close_str[$lang];?>
	</td>
  </tr>
  <tr>
	<td class="field_title"><?php print $set_ntp_ip_str[$lang]; ?></td>
	<td class="field_data2">
		<input type="text" maxlength="15" name="ntp_ip" onchange="return check_ntp_ip();"  onkeyup="return check_ntp_ip();"  value="<?php print GetNtpServerIp();?>" size="20"/>
		<span id="ntp_ip_span"><img src='./images/error_icon.png' style="visibility: hidden;"></img></span>
	</td>
  </tr>
  <tr>
	<td colspan="2"><input type="submit" name="submit" value="<?php print $update_str[$lang];?>" /></td>
  </tr>
      </table>
</form>

<form id="zone_form" name="zone_form" action="clock_target.php" method="post"> 
<table align="center">
	<tr><td class="bar"><?php print $timezone_config_str[$lang];?></td></tr>
</table>
<table width="80%" border="0" cellpadding="6" align="center">
  <tr>
	<td class="field_data1"><?php print $select_zone_str[$lang];?>:&ensp;
	  <select name="timezone">
		<?php 
		$zonelist = $timezone->GetTimezoneList();
		if($zonelist !== FALSE)
		{
			foreach( $zonelist as $entry )
			{
				if( $entry == $valueTimezone )
				{
					print "<option value=\"" . $entry . "\" selected>" . $entry;
				}
				else
				{
					print "<option value=\"" . $entry . "\">" . $entry;
				}
			}
		}
		?>
	  </select>
	</td>
  </tr>
  <tr>
	<td class="field_data2">
	<input type="hidden" name="utc_value" id="utc_value" value="">
	<input type="checkbox" <?php print ($bUtcEnabled===TRUE) ? " checked=\"checked\" " : " ";?> name="utc_enable" id="utc_enable" value=""><?php print $set_utc_str[$lang];?>
	</td>
  </tr>
  <tr>
	<td colspan="2">
	<input type="submit" id="df_zone_submit" name="df_zone_submit" value="<?php print $default_set_str[$lang];?>" />
	<input type="submit" onClick="return setutc();" id="zone_submit" name="zone_submit" value="<?php print $update_str[$lang];?>" />
	</td>
  </tr>
      </table>
</form>

<?php 
if($message != "")
{
	print_msg_block($message);
}
?>	
</div>
	
<div id="panel_btm"></div>
</div>
</body>
</html>


