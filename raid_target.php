<?php
require("./include/authenticated.php");
require_once("./include/function.php");
require_once("./include/log.php");

$lang=load_lang();

$linked_to_3ware_web = "https://" . $_SERVER["HTTP_HOST"] . ":888";

$enter_str=array(
	"进入",
	"Enter"
);

$tip_str=array(
	"进入RAID管理界面，请点击“进入”链接。",
	"To manipulate RAID, please click \"Enter\" link. "
);
$attention_str=array(
	"更改raid配置，会对基于raid的配置造成破坏：<br/>
		1、卷管理数据丢失、卷不可读写。<br/>
		2、iscsi-target initiator端数据丢失、不可读写。<br/><p>
	",
	"To change raid configure will dagame \"Volume Management\":<br/>
		1. LVM: configure data lose and not restored.<br/>
		2. Iscsi-target: On initiator end, data will lose.<br/><p>
	"
);
?>


<?php 
///////////////////////////////////////////////
// 直接自动链接到3ware的raid管理界面
//header("Location: $linked_to_3ware_web");
///////////////////////////////////////////////
?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<script defer type="text/javascript" src="js/pngfix.js"></script>
<link rel="stylesheet" type="text/css" href="css/target.css" />
<script type="text/javascript">
function raid_enter()
{
	<?php 
	$log = new Log();
	$log->VstorWebLog(LOG_INFOS, MOD_RAID, "visit RAID managerment web.");
	$log->VstorWebLog(LOG_INFOS, MOD_RAID, "访问RAID管理网页。", CN_LANG);
	?>	
	return true;
}
</script>
</head>

<body>
<div id="raid_target">
	<div id="panel_top"></div>
	
	<div id="raid">
      <table width="80%" border="0" cellpadding="6" align="center" height="100%">
	  <tr><td width="100%" colspan="2" height="20%"></td></tr>
        <tr>
          <td  colspan="2" class="tip_data"><img src="images/tip.gif" />
          </td>
        </tr>
        <tr><td colspan="2" class="tip_data" align="left">
        <?php print $attention_str[$lang] . $tip_str[$lang]; ?>
        </td></tr>
		<tr>
			<td colspan="2">
			<a href="<?php print $linked_to_3ware_web; ?>" onClick="return raid_enter();" target="_self" class="enter_link">
			<?php print $enter_str[$lang] . " >>>";?>
			</a>
			</td>
		</tr>
		<tr><td width="100%" colspan="2" height="20%"></td></tr>
      </table>
	</div>
	
	<div id="panel_btm"></div>
</div>
</body>
</html>
