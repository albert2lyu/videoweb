<?php
require_once("./include/function.php");

$lang = load_lang();
// 只允许中文：
$lang = 0;

$cpsz_str=array(
	"产品设置",
	"Product Setup"
);
$select_product_str=array(
	"选择当前服务器所属产品",
	"Select product type for current host"
);
$is_show_vis_str=array(
	"是否显示VIS管理",
	"To be show \"VIS\" part"
);
$yes_str=array(
	"是",
	"yes"
);
$no_str=array(
	"否",
	"no"
);
$config_str=array(
	"设 置",
	"Config"
);
$config_ok_str=array(
	"设置成功",
	"config ok."
);
$config_failed_str=array(
	"设置失败",
	"config failed."
);

/*
 * 说明：获取产品列表
 * 参数：无
 * 返回：成功返回列表，否则返回FALSE
 */
function get_product_list()
{
	$product_list = array();
	$product_str = "";
	$file=new File("./config/vstorweb.conf");
	if( $file->Load() )
	{
		$needle = "PRODUCT_LIST=";
		while (!$file->EOF())
		{
			if (preg_match("/^" . $needle . "([^\r\n]*)/i", $file->GetLine(), $match))
			{
				$product_str = $match[1];
				break;
			}
		}
	}
	if($product_str == "")
	{
		return FALSE;
	}
	
	$product_list=explode(",", $product_str);
	
	return $product_list;
}

/*
 * 说明：获取产品对应的是否显示RAID管理
 * 参数：无
 * 返回：列表，0显示，1不显示
 */
function get_raidshow_list()
{
	$show_list = array();
	$show_str = "";
	$file=new File("./config/vstorweb.conf");
	if( $file->Load() )
	{
		$needle = "RAIDMGR_SHOW_LIST=";
		while (!$file->EOF())
		{
			if (preg_match("/^" . $needle . "([^\r\n]*)/i", $file->GetLine(), $match))
			{
				$show_str = $match[1];
				break;
			}
		}
	}
	if($show_str == "")
	{
		return FALSE;
	}
	
	$show_list=explode(",", $show_str);
	
	return $show_list;
}
?>

<?php 
/*
 * 表单处理 BEGIN
 */
$message = "";
$product_list = array();
$product_list = get_product_list();
$raidshow_list = get_raidshow_list();
$local_product = get_product_name();
$be_show_vis = ( is_show_vismgr() === TRUE ) ? 0 : 1;

/*
// url必须是 http://ip/cpsz.php?inc=bocom
if( !isset($_GET['inc']) || $_GET['inc']!="bocom" )
{
	exit("no access!");
}
*/
if( isset($_POST['cpsz_submit']) && isset($_POST['product_list_select']) )
{
	$product = $_POST['product_list_select'];
	if ( isset($_POST['vis_show_01']) )
	{
		$vis_show = $_POST['vis_show_01'];
	}
	else
	{
		$vis_show = 1;
	}
	
	$file = new File("./config/vstorweb.conf");
	if( ! $file->Load() )
	{
		$message = $config_failed_str[$lang];
	}
	else
	{
		$index = 0;
		$raid_show=0;
		foreach($product_list as $entry)
		{
			if($entry == $product)
			{
				$raid_show = $raidshow_list[$index];
				break;
			}
			$index++;
		}
		
		$file->EditLine("VISMGR_SHOW=", "VISMGR_SHOW=" . $vis_show);
		$file->EditLine("LOCAL_PRODUCT_IS=", "LOCAL_PRODUCT_IS=" . $product);
		$file->EditLine("RAIDMGR_SHOW=", "RAIDMGR_SHOW=" . $raid_show);
		$file->Save();
		$message = $config_ok_str[$lang];
	}
}
/*
 * 表单处理 END
 */
// 重新获取
$product_list = get_product_list();
$local_product = get_product_name();
$be_show_vis = ( is_show_vismgr() === TRUE ) ? 0 : 1;
?>

<html>
<head>
<title><?php print $cpsz_str[$lang];?></title>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<link rel="stylesheet" href="css/target.css" type="text/css" />
<script defer type="text/javascript" src="js/pngfix.js"></script>
<script type="text/javascript">

</script>
</head>

<body>
<table align="center" width="100%">
	<tr>
	<td class="bar_nopanel"><?php print $cpsz_str[$lang];?></td>
	</tr>
</table>

<form name="cpsz_form" id="cpsz_form" action="cpsz.php" method="post">
<table align="center" width="70%" border="0" cellpadding="6">
  <tr>
    <td class="field_title">
	<?php print $select_product_str[$lang];?>
	</td>
	<td class="field_data1">
	<select name="product_list_select">
	<?php
	if($product_list !== FALSE)
	{
		foreach($product_list as $entry)
		{
			if($entry == $local_product)
			{
				print "<option value=\"{$entry}\" selected>{$entry}\n";
			}
			else
			{
				print "<option value=\"{$entry}\">{$entry}\n";
			}
		}
	}
	?>
	</select>
	</td>
  </tr>

<!--  
   <tr>
    <td class="field_title">
	<?php print $is_show_vis_str[$lang];?>
	</td>
    <td class="field_data2">
    <?php 
    if($be_show_vis == 0)
    {
    print " 
    	<input type=\"radio\" name=\"vis_show_01\" checked=\"checked\" value=\"0\">{$yes_str[$lang]}
		<input type=\"radio\" name=\"vis_show_01\" value=\"1\">{$no_str[$lang]}
		";
    }
    else
    {
    print " 
    	<input type=\"radio\" name=\"vis_show_01\" value=\"0\">{$yes_str[$lang]}
		<input type=\"radio\" name=\"vis_show_01\" checked=\"checked\" value=\"1\">{$no_str[$lang]}
		";
    }
    ?>
	</td>
  </tr>
-->
  <tr>
  <td colspan="2">
  <input type="submit" name="cpsz_submit" value="<?php print $config_str[$lang];?>" />
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
</body>
</html>