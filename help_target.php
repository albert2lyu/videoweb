<?php
require("./include/authenticated.php");
require_once("./include/function.php");
$lang=load_lang();

$help_manual_str=array(
	"帮助手册",
	"Manual"
);
$vstor_manual_str=array(
	"NVR Web 操作手册",
	"NVR Web Manual"
);
$index_str=array(
	"序号",
	"Index"
);
$manual_name_str=array(
	"手册名称",
	"Manual Name"
);

/*
 * 手册数据列表
 * 
 * 注：后续添加，只需在此数组中按照原有的格式正确添加即可。
 */
$manual_list=array(

	// 第一个手册
	array(
		"name"=>array(
			"NVR Web 操作手册",
			"NVR Web Manual"
			),
		"link"=>"help/nvrweb_manual.pdf"
	),
	
	// 第二个手册（同上格式，后同，以此类推）

);
?>

<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\" <html xmlns="http://www.w3.org/1999/xhtml">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<link rel="stylesheet" href="css/target.css" type="text/css" />
<script defer type="text/javascript" src="js/pngfix.js"></script>
<style type="text/css">
a{
color:#00008B;
padding-bottom:4px;
text-decoration:underline;
}
a:hover,a:focus{
color:#00008B;
font-weight:bold;
text-decoration:underline;
}
</style>
</head>

<body>
<table align="center" width="100%">
	<tr>
	<td class="bar_nopanel"><?php print $help_manual_str[$lang];?></td>
	</tr>
</table>
 
<table width="70%" border="0" cellpadding="6" align="center">
  <tr>
  <td class="field_title" width="20%"><?php print $index_str[$lang];?></td>
  <td class="field_title">
	<?php print $manual_name_str[$lang];?>
  </td>
  </tr>
<?php 
$td_class = "field_data2";
$index = 1;
foreach($manual_list as $entry)
{
	print "<tr>";
	print "<td class=\"{$td_class}\">{$index}</td>";
	print "<td class=\"{$td_class}\">";
	print "<a href=\"{$entry['link']}\" target=\"_blank\">{$entry['name'][$lang]}</a>";
	print "</td>";
	print "</tr>";
	
	if($td_class == "field_data1")
	{
		$td_class = "field_data2";
	}
	else
	{
		$td_class = "field_data1";
	}
	$index++;
}
?>
</table>

</body>
</html>