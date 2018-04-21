<?php
require("./include/authenticated.php");
require_once("./include/data.php");
require_once("./include/mvpprofile.php");
require_once("./include/function.php");
/*
 * 函数名： ShowHtmlView
 * 参数：$select  选择的视图
 * 返回值：成功  true，失败  failse
 * created by auto，2009-10-09
 */
function ShowHtmlView($selected)
{
	require("./include/authenticated.php");

	if( isset($_GET['redirect']))
	{
		$param_redirect = $_GET['redirect'];
	}
	else
	{
		$param_redirect = "";
	}

	/*
	 * 验证redirect是否属于href中的一个。
	 */	
	if($param_redirect != "")
	{
		$sub = $GLOBALS["mainview_data"][$GLOBALS["VSTORWEB_LANG"]][$selected];
		$href = $sub["href"];
		$count = count($href);
		$bHref = FALSE;
		for($i=0; $i<$count; $i++)
		{
			$tmphrep_arr = explode("=", $href[$i]);
			$tmphrep = $tmphrep_arr[count($tmphrep_arr)-1];
			if($tmphrep == $param_redirect)
			{
				$bHref = TRUE;
				break;
			}
			else
			{
				continue;
			}
		}
		if($bHref === FALSE)
		{
			exit("Not Found");
		}
	}
	
	$title = $GLOBALS["mainview_data"][$GLOBALS["VSTORWEB_LANG"]][$selected]["title"];
	html_begin();
	print_header($title);
	print_topper();
	print_tools($selected);
	print_break1();
	print_manager($selected, $param_redirect);
	print_break2();
	print_footer();
	html_end();
	
	return true;
}

////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////

function html_begin()
{
?>
	<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\" <html xmlns="http://www.w3.org/1999/xhtml">
	<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />

<?php 
}

function print_header($title=" ")
{
?>
	<title><?php print $title; ?></title>
	<link rel=stylesheet href="css/basic.css" type="text/css">
	<script defer type="text/javascript" src="js/pngfix.js"></script>
	<script type="text/javascript" language="javascript" src="js/ajax_function.js"></script>

 	<link rel="icon" type="image/png" media="screen, print" href="images/vstor-icon.gif" />
	<link rel="shortcut icon" type="image/x-icon" media="screen, print" href="images/vstor-icon.gif" />

	</head>
	<body>
	<div id="box">
<?php 
}

function print_topper()
{
?>
	<div id="topper">
	<table width="1024" border="0" cellpadding="0" cellspacing="0" align="center">
	  <tr height="55" valign="bottom">
		 <td width="400" > <!--<img name="" src="images/logo.gif" width="180" height="45" alt="" /> --> </td>
		<td width="300" align="center" >
			<span class="bocom_titile_font">
			<?php print $GLOBALS["PRODUCT_NAME"];?>
			</span></td>
		<td align="center" >
			<a href="status.php" class="lt_link_font"><img src="images/status_bar.gif" /><?php print $GLOBALS["status_link"][$GLOBALS["VSTORWEB_LANG"]]; ?></a>
			|
			<a href="system.php?redirect=log_target.php" target="_self" class="lt_link_font"><img src="images/log_bar.gif" /><?php print $GLOBALS["log_link"][$GLOBALS["VSTORWEB_LANG"]]; ?></a>
			|
			<a href="system.php?redirect=help_target.php" target="_self" class="lt_link_font"><img src="images/help_bar.gif" /><?php print $GLOBALS["help_link"][$GLOBALS["VSTORWEB_LANG"]]; ?></a>
			|
			<a href="logout.php" class="lt_link_font"><img src="images/logout_bar.bmp" /><?php print $GLOBALS["exit_link"][$GLOBALS["VSTORWEB_LANG"]]; ?></a>
		</td>
	  </tr>
	</table>
	</div>
<?php 
}

function print_tools($selected)
{
?>
	<div id="tools">
	<table width="1024" border="0" cellpadding="0" cellspacing="0" align="center" height="100%">
		<tr>
<?php 
	$view_data = $GLOBALS["mainview_data"][$GLOBALS["VSTORWEB_LANG"]];
	$index=0;
	foreach( $view_data as $sub)
	{
		/*
		 * 是否显示VIS工具
		 */
		if( $index==VIS_SEL && !is_show_vismgr() )
		{
			$index++;
			continue;
		}
		/*
		 * 是否显示RAID管理
		 */
		if( $index==RAID_SEL && !is_show_raidmgr() )
		{
			$index++;
			continue;
		}
		
		print "<td class=\"td_tool_setup\" width=\"120px\">";
		print "<a href=\"" . $sub["link"] . "\" class=\"tool_item_link\">";
		$choose=0;
		if($index==$selected)
		{
			$choose=1;
		}
		else
		{
			$choose=0;	
		}
		print "<img src=\"images/tools/" . $sub["icon"][$choose] . "\" />";
		print "<span class=\"" . $sub["font"][$choose] . "\">" . $sub["name"] . "</span></a>";
		print "</td>";

		$index++;
	}
?>
	<td></td>
	<td width="276">
	<!-- uptime 信息 -->
		<script type="text/javascript">
			var xmlhttp = null;
			loadDoc(xmlhttp, 'uptime.php',uptime_ajax);
			window.setInterval("loadDoc(xmlhttp, 'uptime.php',uptime_ajax)", 5000);
		</script>
		<div id="uptime_div"></div>
	</td>
	</tr>
	</table>
	</div>
<?php 
	return true;
}

function print_break1()
{
?>
	<div id="break1"></div>
<?php 
}

function print_manager($selected, $param_redirect)
{
?>
	<div id="manager">
	    <table width="100%" border="0" cellpadding="0" cellspacing="0">
		  <tr>
			<td rowspan="2" class="left_menu_bg">
				<table class="table_left_menu_setup" cellpadding="0" cellspacing="0" width="100%">
					<tr>
					<td class="td_menu_setup">
<?php 	
	$sub = $GLOBALS["mainview_data"][$GLOBALS["VSTORWEB_LANG"]][$selected];
	$index=0;
	
	print "			<span class=\"menu_font\"><img src=\"images/menu_bar.gif\" />" . $sub["name"] . "</span>";
	print "			</td>";
	print "			</tr>";
	
	$sub_title = $sub["subtitle"];
	$href      = $sub["href"];
	$count = count($sub_title);
	
	if($param_redirect == "")
	{
		$param_redirect = $href[0];
		$param_redirect = explode("=", $param_redirect);
		$param_redirect = $param_redirect[count($param_redirect)-1];
	}
	
	// 如果是VIS，则判断服务器类型，然后选择显示哪些菜单项
	// 存储服务器显示“存储配置”，vod服务器显示“vod配置”
	if($selected == VIS_SEL)
	{
		$storage = 0;
		$vod = 0;
		$visprofile = new VisProfile();
		$server_mode= $visprofile->GetFieldValue("ServerMode");
		$server_array = GetVisServerMode($server_mode);
		if($server_array['storage'] == 1)
		{
			$storage = 1;
		}
		if($server_array['vod'] == 1)
		{
			$vod = 1;
		}
	}
	//

	// 左边子标题
	for($i=0; $i<$count; $i++)
	{
		
		// 如果是选择的VIS，菜单项显示有所不同
		if($selected == VIS_SEL)
		{
			if($i==3)//指向“配置存储服务器”
			{
				if($storage == 1)
				{
					// nothing
				}
				else
				{
					continue;
				}
			}

			if($i == 4)//指向“配置VOD服务器”
			{
				if($vod == 1)
				{
					// nothing
				}
				else
				{
					continue;
				}
			}
		}
		//
		
		print "<tr>";
		$tmphrep = explode("=", $href[$i]);
		$tmphrep = $tmphrep[count($tmphrep)-1];
		if($tmphrep == $param_redirect)
		{
			print "<td class=\"td_item_setup_select\">";
			print "<a href=\"" . $href[$i] . "\" target=\"_self\" class=\"menu_item_link\">";		
			print "<span class=\"menu_item_select\"><img src=\"images/menu_item_icon.gif\"/>" . $sub_title[$i] . "</span>";		
			print "</a>";
			print "</td>";
		}
		else
		{
			print "<td class=\"td_item_setup\">";
			print "<a href=\"" . $href[$i] . "\" target=\"_self\" class=\"menu_item_link\">";		
			print "<span class=\"menu_item\"><img src=\"images/menu_item_icon.gif\"/>" . $sub_title[$i] . "</span>";		
			print "</a>";
			print "</td>";
		}

		print "</tr>";
	}
?>
	</table>
	</td>
	<td class="<?php /*print $sub["img"];*/ ?>"></td>
	</tr>

	<tr>
	<td>
				<iframe name="target" frameborder="0" class="target_style" src="<?php print $param_redirect; ?>">
				</iframe>
				</td>
			  </tr>
			</table>
		</div>
<?php 
}

function print_break2()
{
?>
<!--
	<div id=break2></div>
-->
<?php 
}

function print_footer()
{
?>
	<div id="footer">
<?php 
	print $GLOBALS["copyright"][$GLOBALS["VSTORWEB_LANG"]];
?>
	</div>
<?php 
}

function html_end()
{
?>
	</div>
	</body>
	</html>
<?php 
}

?>
