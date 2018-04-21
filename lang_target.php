<?php
require_once("./include/function.php");
require("./include/authenticated.php");
require_once("./include/log.php");

$lang=load_lang();

$tip_str=array(
	"选择语言：中文或者英文。",
	"Select language type: Chinese or English."
);

$td_title=array(
	"语言种类",
	"Language Type"
);
$btn_name=array(
	"更 新",
	"Update"
);
$lang_cn_str=array(
	"中文",
	"Chinese"
);
$lang_en_str=array(
	"英文",
	"English"
);

$log = new Log();
$bLanguageChanged = FALSE;
if( isset($_POST['language']) )
{
	$bLanguageChanged = TRUE;
	$language = $_POST['language'];
	$content = CN_LANG;
	if( $language == "en" )
	{
		$content = EN_LANG;
	}
		
	$file = new File("./config/vstorweb.conf");
	if( ! $file->Load() )
	{
		$log->VstorWebLog(LOG_ERROR, MOD_SYSTEM, "set   web language to [" . $language . "] failed.");
		$log->VstorWebLog(LOG_ERROR, MOD_SYSTEM, "设置 网页语言为[" . $language . "]失败。", CN_LANG);
	}
	else
	{
		$file->EditLine("LANG=", "LANG=" . $content);
		$file->Save();
		$_SESSION['g_Language'] = $content;
		$log->VstorWebLog(LOG_INFOS, MOD_SYSTEM, "set   web language to [" . $language . "] ok.");
		$log->VstorWebLog(LOG_INFOS, MOD_SYSTEM, "设置 网页语言为[" . $language . "]成功。", CN_LANG);
	}
}

print "
<html>
<head>

<meta http-equiv=\"Content-Type\" content=\"text/html; charset=gb2312\" />
<link rel=\"stylesheet\" href=\"css/target.css\" type=\"text/css\" />
<script defer type=\"text/javascript\" src=\"js/pngfix.js\"></script>

</head>

<body>
<div id=\"lang_target\">
	<div id=\"panel_top\"></div>
	
	<div id=\"lang\">
	<form id=\"lang_form\" name=\"lang_form\" action=\"lang_target.php\" method=\"post\"> 
	  <table width=\"80%\" border=\"0\" cellpadding=\"6\" align=\"center\" height=\"90%\">
	  <tr><td colspan=\"2\" class=\"tip_data\">
	  <img src=\"images/tip.gif\" />{$tip_str[$lang]}
	  </td></tr>
        <tr>
          <td class=\"field_title\">{$td_title[$lang]}
		  </td>		  	
		  <td class=\"field_data1\">
			<table align=\"center\" border=\"0\" cellpadding=\"2\">
			<tr>
				<td align=\"left\">
				<input type=\"radio\" name=\"language\" checked=\"checked\" value=\"cn\" />{$lang_cn_str[$lang]}<br/>
				</td>
			</tr>
			<tr>
				<td align=\"left\">
			  	<input type=\"radio\" name=\"language\" value=\"en\" />{$lang_en_str[$lang]}
				</td>
			</tr>
			</table>
		  </td>
        </tr>
		<tr >
		<td colspan=\"2\">
		<input type=\"submit\" name=\"submit\" value=\"{$btn_name[$lang]}\"/>
		</td>
		</tr>
      </table>
	</form>
	</div>
	
	<div id=\"panel_btm\"></div>
</div>
";
?>

<script type="text/javascript">
function refreshParentPage()
{
	window.parent.location.href="system.php?redirect=lang_target.php";
}
<?php
if($bLanguageChanged == TRUE)
{
	$bLanguageChanged = FALSE;
?>
	refreshParentPage();
<?php 
}
?>
</script>

<?php 
print "
</body>
</html>
";
?>

