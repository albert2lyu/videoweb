<?php
require("./include/authenticated.php");
require_once("./include/function.php");

$lang=load_lang();

$process_status_str=array(
	"进程状态信息",
	"Process Status Information"
);
$loading_str=array(
	"加载中",
	"Loading"
);
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<link rel="stylesheet" href="css/target.css" type="text/css" />
<script type="text/javascript" language="javascript" src="js/ajax_function.js"></script>
<script defer type="text/javascript" src="js/pngfix.js"></script>
</head>

<body>

<table align="center" width="100%" border="0">
	<tr>
	<td class="bar_nopanel"><?php print $process_status_str[$lang];?></td>
	</tr>
</table>

<script type="text/javascript">
	var xmlhttp = null;
	loadDoc(xmlhttp, 'top.php', process_status_ajax);
	window.setInterval("loadDoc(xmlhttp, 'top.php', process_status_ajax)", 3000);
</script>

<table align="center" width="100%" border="0">
	<tr>
		<td class="process_output" id="process_status">
		</td>
	</tr>
</table>
</body>
</html>