<?php
require("./include/authenticated.php");
require_once("./include/function.php");
require_once("./include/log.php");

$lang=load_lang();

$linked_to_3ware_web = "https://" . $_SERVER["HTTP_HOST"] . ":888";

$enter_str=array(
	"����",
	"Enter"
);

$tip_str=array(
	"����RAID������棬���������롱���ӡ�",
	"To manipulate RAID, please click \"Enter\" link. "
);
$attention_str=array(
	"����raid���ã���Ի���raid����������ƻ���<br/>
		1����������ݶ�ʧ�����ɶ�д��<br/>
		2��iscsi-target initiator�����ݶ�ʧ�����ɶ�д��<br/><p>
	",
	"To change raid configure will dagame \"Volume Management\":<br/>
		1. LVM: configure data lose and not restored.<br/>
		2. Iscsi-target: On initiator end, data will lose.<br/><p>
	"
);
?>


<?php 
///////////////////////////////////////////////
// ֱ���Զ����ӵ�3ware��raid�������
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
	$log->VstorWebLog(LOG_INFOS, MOD_RAID, "����RAID������ҳ��", CN_LANG);
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
