<?php
require("./include/function.php");
require("./include/auth.php");
require("./include/authenticated.php");
require_once("./include/log.php");

$lang=load_lang();

$tip_str=array(
	"Ϊ�û��޸�����",
	"To change password for user"
);

$select_user_str=array(
	"ѡ���û�",
	"Select User"
);

$current_passwd_str=array(
	"��ǰ����",
	"Current Password"
);

$new_passwd_str=array(
	"������",
	"New Password"
);

$confirm_passd_str=array(
	"ȷ������",
	"Confirm New Password"
);

$chang_passwd_str=array(
	"�޸�����",
	"Change Password"
);

$chang_ps_ok=array(
	"�޸�����ɹ�!",
	"Change password successfully!"
);
$chang_ps_failed=array(
	"�޸�����ʧ��!",
	"Change password failed!"
);
$current_ps_error=array(
	"��ǰ�������",
	"Current password incorrect!"
);
$new_ps_differnt=array(
	"�������������벻��ͬ��",
	"Both new passwords not equal!"
);

?>


<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<link rel="stylesheet" href="css/target.css" type="text/css" />
<script defer type="text/javascript" src="js/pngfix.js"></script>
</head>

<body>

<div id="account_target">
	<div id="panel_top"></div>

	<div id="account">
	<form id="account_form" name="account_form" method="post" action="">
      <table width="80%" border="0" cellpadding="6" align="center" height="100%">
	  <tr><td width="100%" colspan="2" height="10%"></td></tr>

<?php
$log = new Log();
$message = "";
if(isset($_POST['current_ps']) && isset($_POST['new_ps']) && isset($_POST['confirm_new_ps'])
	// &&
	// // ��ǰ���벻Ϊ�գ������������ȷ����������һ����Ϊ��
	// $_POST['current_ps']!="" && ($_POST['new_ps']!=""|| $_POST['confirm_new_ps']!="")
)
{
		// ��ǰ���벻Ϊ�գ������������ȷ����������һ����Ϊ��
	if ( $_POST['current_ps']!="" && $_POST['new_ps']!="" && $_POST['confirm_new_ps']!="" )
	{
		$username = $_POST['user_select'];
		$current_ps = $_POST['current_ps'];
		$new_ps = $_POST['new_ps'];
		$confirm_new_ps = $_POST['confirm_new_ps'];
		$retval = change_password($username, $current_ps, $new_ps, $confirm_new_ps);

		if($retval == 0)
		{
			$message = "<img src=\"images/chang_ps_ok.png\" />" . $chang_ps_ok[$lang];
			$log->VstorWebLog(LOG_INFOS, MOD_ACCOUNT, "change password ok.");
			$log->VstorWebLog(LOG_INFOS, MOD_ACCOUNT, "�޸�����ɹ���", CN_LANG);
		}
		else
		{
			$message = "<img src=\"images/chang_ps_failed.png\" />";
			if($retval == 1)
			{
				$message .= $current_ps_error[$lang];
			}
			else if($retval == 2)
			{
				$message .= $new_ps_differnt[$lang];
			}
			else //if($retval == -1)
			{
				$message .= $chang_ps_failed[$lang];
			}
			$log->VstorWebLog(LOG_ERROR, MOD_ACCOUNT, "change password failed.");
			$log->VstorWebLog(LOG_ERROR, MOD_ACCOUNT, "�޸�����ʧ�ܡ�", CN_LANG);
		}
	}
	else
	{
		$message = "<img src=\"images/chang_ps_failed.png\" />";
		if($_POST['current_ps']=="")
		{
			$message .= $current_ps_error[$lang];
		}
		else if($_POST['new_ps'] != $_POST['confirm_new_ps'])
		{
			$message .= $new_ps_differnt[$lang];
		}
		else
		{
			$message .= $chang_ps_failed[$lang];
		}
		$log->VstorWebLog(LOG_ERROR, MOD_ACCOUNT, "change password failed.");
		$log->VstorWebLog(LOG_ERROR, MOD_ACCOUNT, "�޸�����ʧ�ܡ�", CN_LANG);
	}
}
?>

        <tr>
          <td class="tip_data" colspan="2"><img src="images/tip.gif" /><?php print $tip_str[$lang]; ?>
          </td>
        </tr>
        <tr>
          <td class="field_title"><?php print $select_user_str[$lang];?></td>
          <td class="field_data1">
		  <select name="user_select">
		  <option value="admin" selected>admin
		  <option value="superadmin">superadmin
		  </select>
		  </td>
        </tr>
        <tr>
          <td class="field_title"><?php print $current_passwd_str[$lang];?></td>
          <td class="field_data1">
		  <input type="password" class="user_passwd" name="current_ps" maxlength="16"
		  	oncontextmenu="return false;" oncut="return false;" onselectstart="return false;"
		  	ondragstart="return false;" ondrop="return false;" onpaste="return false;"
		  	/>
		  </td>
        </tr>
        <tr>
          <td class="field_title"><?php print $new_passwd_str[$lang]; ?></td>
          <td class="field_data2">
		  <input type="password" class="user_passwd" name="new_ps"  maxlength="16"
		    oncontextmenu="return false;" oncut="return false;" onselectstart="return false;"
		  	ondragstart="return false;" ondrop="return false;" onpaste="return false;"
		  />
		  </td>
        </tr>
        <tr>
          <td class="field_title"><?php print $confirm_passd_str[$lang]; ?></td>
          <td class="field_data1">
		  <input type="password" class="user_passwd" name="confirm_new_ps" maxlength="16"
		  	oncontextmenu="return false;" oncut="return false;" onselectstart="return false;"
		  	ondragstart="return false;" ondrop="return false;" onpaste="return false;"
		  />
		  </td>
        </tr>
        <tr>
          <td colspan="2">
          <input type="submit" name="submit" value="<?php print $chang_passwd_str[$lang];?>"/>
          </td>
        </tr>
		<tr><td width="100%" colspan="2" height="10%"></td></tr>
      </table>
	</form>
	<?php
	if($message != "")
		print_msg_block($message);
	?>
	</div>

	<div id="panel_btm"></div>
</div>

</body>
</html>

