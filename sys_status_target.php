<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<link rel="stylesheet" href="css/target.css" type="text/css" />
<script type="text/javascript" language="javascript" src="js/ajax_function.js"></script>
<script defer type="text/javascript" src="js/pngfix.js"></script>

<style type="text/css">


td{
font-size:13px;
text-align:left;
}

.bolder{
font-weight:bolder;
}

</style>
</head>
<body>
<?php 
require_once("./include/authenticated.php");
require_once("./include/function.php");

$lang=load_lang();

$sys_status=array(
	"系统信息",
	"System Status"
);
$loading_str=array(
	"加载中",
	"Loading"
);
?>

<table align="center" width="100%" border="0">
	<tr>
	<td class="bar_nopanel"><?php print $sys_status[$lang];?></td>
	</tr>
</table>

<script type="text/javascript">
	var xmlhttp = null;
	loadDoc(xmlhttp, 'system_status_info.php', system_status_ajax);
	window.setInterval("loadDoc(xmlhttp, 'system_status_info.php', system_status_ajax)", 3000);
</script>

<div id="system_status_div">
<br/><br/><br/><br/><br/><br/>
<br/><br/><br/><br/><br/><br/>
<br/><br/><br/><br/><br/><br/>
<img src="images/loading.gif" border="0" />
<br/><?php print $loading_str[$lang];?>
</div>

</body>
</html>
