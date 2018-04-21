<?php
require("./include/authenticated.php");
require_once("./include/function.php");

$lang=load_lang();


$tip_loading_str=array(
        "正在加载中，请稍等....",
        "Loading, Please Wait..."
);
?>

<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\" <html xmlns="http://www.w3.org/1999/xhtml">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<link rel="stylesheet" href="css/target.css" type="text/css" />
<script type="text/javascript">
function load()
{
    window.location.href="drive_target2.php";
    return true;
}
</script>
</head>
<body onload="load()">
<div id="div_loading">
<table width="100%" height="100%" bgcolor="#FEF9E9" >
	<tr>
		<td align="center">
			<img src="images/loading_01.gif">
			<br><br>
			<?php print $tip_loading_str[$lang]; ?>
		</td>
	</tr>
</table>
</div>
</body>
</html>