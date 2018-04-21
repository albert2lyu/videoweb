<?php 
require_once("./include/function.php");

if( !IsVisExisted())
	exit("no vis server!");

$lang=load_lang();

$title_str=array(
	"存储配置",
	"Storage Config"
);

?>

<html>
<head>
<title><?php print $title_str[$lang];?></title>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<link rel="stylesheet" href="css/basic.css" type="text/css" />
</head>

<body>
<table width="100%" height="100%" border="0" cellpadding="6">
  <tr>
    <td width="10%"></td>
    <td>
		<iframe width="100%" height="100%" name="target" frameborder="0" src="va_storage_target.php">
		</iframe>
	</td>
    <td width="10%"></td>
  </tr>
</table>
</body>
</html>
