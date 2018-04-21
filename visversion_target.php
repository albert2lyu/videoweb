<?php 
require("./include/authenticated.php");
require_once("./include/function.php");
require_once("./include/log.php");

if( !IsVisExisted())
	exit("no vis server!");

$lang=load_lang();

$vis_version_information_str=array(
	"VIS服务 版本信息",
	"VIS Server Version"
);
$index_str=array(
	"序号",
	"Index"
);
$modules_name_str=array(
	"模块名称",
	"Module Name"
);
$version_str = array(
	"版本号",
	"Version"
);
$builddate_str=array(
	"创建日期",
	"Build Date"
);
$none_str=array(
	"- 无 -",
	"- None -"
);

?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<link rel="stylesheet" href="css/target.css" type="text/css" />
<script defer type="text/javascript" src="js/pngfix.js"></script>
</head>

<body>

<div id="visversion_target">
<table align="center" width="100%">
	<tr>
	<td class="bar_nopanel"><?php print $vis_version_information_str[$lang];?></td>
	</tr>
</table>

<table width="80%" border="0" cellpadding="6" align="center">
  <tr>
	<td class="field_title"><?php print $index_str[$lang];?></td>
    <td class="field_title"><?php print $modules_name_str[$lang];?></td>
    <td class="field_title"><?php print $version_str[$lang];?></td>
	<td class="field_title"><?php print $builddate_str[$lang];?></td>
  </tr>
<?php 
$log = new Log();
$bHasItem = FALSE;
$file_buffer = rfts("/opt/library/version.txt");
if($file_buffer !== FALSE)
{
	$lines = explode("\n", $file_buffer);
	$modules_name_list = array();
	$version_list = array();
	$builddate_list = array();
	
	foreach($lines as $line)
	{
		if( preg_match("/Module\s*:\s*([^\n]*)/i", $line, $match) )
		{
			$modules_name_list[] = trim($match[1]);
		}
		
		if( preg_match("/Version\s*:\s*([^\n]*)/i", $line, $match) )
		{
			$version_list[] = trim($match[1]);
		}
		
		if( preg_match("/BuildDate\s*:\s*([^\n]*)/i", $line, $match) )
		{
			$builddate_list[] = trim($match[1]);
		}
	}
	
	$iLoop = count($modules_name_list);
	$td_class = "field_data1";
	for($i=0; $i<$iLoop; $i++)
	{
		print "<tr>";
		$index = sprintf("%02d", ($i + 1));
		print "<td class=\"{$td_class}\">{$index}</td>";
		print "<td class=\"{$td_class}\">{$modules_name_list[$i]}</td>";
		if(isset($version_list[$i]))
		{
			print "<td class=\"{$td_class}\">{$version_list[$i]}</td>";
		}
		else
		{
			print "<td class=\"{$td_class}\"></td>";
		}
		if(isset($builddate_list[$i]))
		{
			print "<td class=\"{$td_class}\">{$builddate_list[$i]}</td>";
		}
		else
		{
			print "<td class=\"{$td_class}\"></td>";
		}		
		print "</tr>";
		
		$bHasItem = TRUE;
		if($td_class == "field_data1")
		{
			$td_class = "field_data2";
		}
		else
		{
			$td_class = "field_data1";
		}
	}
	
}
else
{
	$log->VstorWebLog(LOG_ERROR, MOD_VIS, "get vis version failed.");
	$log->VstorWebLog(LOG_ERROR, MOD_VIS, "获取VIS Server版本信息失败。", CN_LANG);
}

if($bHasItem === FALSE)
{
	print "<tr><td class=\"field_data2\" colspan=\"4\">{$none_str[$lang]}</td></tr>";
}

?>
  
</table>

</div>

</body>
</html>
