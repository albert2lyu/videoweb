<?php 
require_once("./include/function.php");
$lang=load_lang();

$device_detect_str=array(
	"设备检测",
	"Detect Devices"
);
$beDetecting_str=array(
	"正在检测……",
	"Detecting..."
);
?>

<html>
<head>
<title><?php print $device_detect_str[$lang];?></title>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
</head>

<body>
<div id="load_div" align="center">
<br/><br/><br/><br/><br/><br/><br/><br/><br/>
<img alt="" src="images/loading.gif" />
<br/>
<?php print $beDetecting_str[$lang];?>
</div>
<?php 
	ob_flush();
	print "<pre style=\"font:13px;color:#444444;\">";
	system("export LANG=C; /usr/bin/sudo /opt/CheckOut", $retval);
	print "</pre>";
?>

<script type="text/javascript">
document.getElementById('load_div').innerHTML="";
</script>

</body>
</html>