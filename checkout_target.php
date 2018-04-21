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
$detect_result_str=array(
	"检测结果：",
	"Detected Result: "
);
?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
</head>

<body style="font-size: 13px;background:#E4E4E4;">
<div id="load_div" align="center">
<br/><br/><br/><br/><br/><br/><br/><br/><br/>
<img alt="" src="images/loading.gif" />
<br/>
<?php print $beDetecting_str[$lang];?>
</div>
<div id="result_tip_div" style="font-weight:bold;font-size:14px;"></div>
<?php 
	ob_flush();
	print "<pre style=\"font:13px;color:#444444;\">";
	system("export LANG=C; /usr/bin/sudo /opt/CheckOut", $retval);
	print "</pre>";
?>


<script type="text/javascript">
document.getElementById('load_div').innerHTML="";
document.getElementById('result_tip_div').innerHTML='<?php print $detect_result_str[$lang];?>';
</script>

</body>
</html>