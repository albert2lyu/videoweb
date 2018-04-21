<?php 
require_once("./include/data.php");
require_once("./include/log.php");
session_start();
$lang=load_lang();

$title=array(
	"  登录",
	" Login"
);
$username_str=array(
	"用户名：",
	"Username: "
);
$password_str=array(
	"密     码：",
	"Password: "
);
$login_str=array(
	"登 录",
	"Login"
);

$login_error=array(
	"用户名或密码错误！",
	"Username or password is not correct!"
);
$login_no_str=array(
	"用户未登录！",
	"User has not login yet!"
);

$admin_has_login=array(
	"admin 已登录，欢迎",
	"Welcome admin."
);

$admin_from_here=array(
	"从这里开始管理 ",
	"You can administrate   from here"
);

$admin_logout=array(
	"退出",
	"Log out"
);
$cn_lang_str=array(
	"中文",
	"Chinese"
);
$en_lang_str=array(
	"英文",
	"English"
);
$select_lang_str=array(
	"选择语言：",
	"Language: "
);
$tip_not_login_str=array(
	"请先登录！",
	"Please login first!"
);

$tip_license_error_str=array(
	"无效的授权文件",
	"INVALID LICENSE"		
);

?>

<?php
if( isset($_GET['lang']) )
{
	if($_GET['lang'] == "cn")
	{
		$lang = CN_LANG;
	}
	else if($_GET['lang'] == "en")
	{
		$lang = EN_LANG;
	}
}
$_SESSION['g_Language'] = $lang;
?>
<html>
<head>
<title><?php print $title[$lang]; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<script defer type="text/javascript" src="js/pngfix.js"></script>
<link rel="icon" type="image/png" media="screen, print" href="images/vstor-icon.gif" />
<link rel="shortcut icon" type="image/x-icon" media="screen, print" href="images/vstor-icon.gif" />
<style type="text/css">
<!--
.STYLE6 {
	font-size: xx-large;
	font-family: Arial, Helvetica, sans-serif;
	color: #FFFFFF;
}
.STYLE8 {
	color: #FFFFFF;
	font-size: 12px;
}

.STYLE13 {
	font-family: "微软雅黑";
	font-weight: bold;
	font-size: 15px;
	color: #000060;
}

.STYLE14 {
	font-family: "微软雅黑";
	font-weight: bold;
	font-size: 26px;
	color: #FF0000;
}

input[type="text"], input[type="password"]
{
        border: 1px solid rgb(149, 149, 149);
        padding: 4px;
}
input[type="text"]:focus, input[type="password"]:focus
{
        border: 2px solid rgb(149, 149, 149);
        padding: 3px;
        background-color: #FCFFCD;
}

input[type="text"]:hover, input[type="password"]:hover
{
        border: 2px solid rgb(149, 149, 149);
        padding: 3px;
}
input[readonly="readonly"]
{
        background: #e2e2e2;         
}
.bocom_titile_font{
margin-left:10px;
vertical-align:middle;
font-weight:bolder;
font-family:"Courier New", Courier, monospace;
font-size:24px;
color:#000000;
letter-spacing:2px;
word-spacing:8px;
}
a:hover {
color:maroon;
}
a.lt_link_font{
font-size: 12px;
color:#660000;
font-weight: bold;
padding-bottom:4px;
text-decoration:none;
}
a.lt_link_font:hover,a.lt_link_font:focus{
color:#FFFFFF;
text-decoration:underline;
}
-->
</style>

</head>
<body style="margin: 0;padding: 0;">
<div style="margin:0 auto;margin-top:opx;position: relative;padding: 0;">
<table align="center" width="1024" border="0" cellspacing="0" cellpadding="0"  style="margin-top:0px;">
  <tr>
    <td colspan="2" bgcolor="#D9D9D9">
    <!-- 
    <img name="" src="images/logo.gif" width="208" height="45" alt="logo" />
	<span class="bocom_titile_font">
	<?php print $GLOBALS["PRODUCT_NAME"];?>
	</span>
	-->
	<table width="1024" border="0" cellpadding="0" cellspacing="0" align="center">
	  <tr height="55" valign="bottom">
		<td width="400"><!--<img name="" src="images/logo.gif" width="208" height="45" alt="logo" />--></td>
		<td width="400" align="center" >
			<span class="bocom_titile_font">
			<?php print $GLOBALS["PRODUCT_NAME"];?>
			</span></td>
		<td align="center" >
			<span style="font-size:12px;"><?php print $select_lang_str[$lang];?>
			<a href="login.php?lang=cn" target="_self"  class="lt_link_font">中文</a>
			|
			<a href="login.php?lang=en" target="_self" class="lt_link_font">English</a>
			</span>
		</td>
	  </tr>
	</table>
	</td>
  </tr>
  <tr>
    <td colspan="2" bgcolor="#404040">&nbsp;</td>
  </tr>
  <tr>
    <td colspan="2" bgcolor="#173452">&nbsp;</td>
  </tr>
  <tr align="center">
    <td width="222" height="300" rowspan="2" align="center"  valign="middle" bgcolor="#BBBBBB">

<?php 

$log = new Log();

if( (isset($_SESSION['g_bLogin']) && $_SESSION['g_bLogin'] !== TRUE)
||
	!isset($_SESSION['g_bLogin'])
)
{
	include_once("./include/auth.php");
	
	//用户名或密码错误时，记录最后输入的用户名
	$strLastUserName = "";
	if(isset($_POST['username']) && isset($_POST['password']))
	{
		$bLogin = FALSE;
		$username = $_POST['username'];
		$password = $_POST['password'];
		// superadmin不允许登录
		if( $username=="superadmin" || $username=="" || $password=="")
		{
			$strLastUserName = $username;
			print "
			<div style=\"color:red;font-weight:bold;height: 40px;\">
				{$login_error[$lang]}
			</div>
			";
			$log->VstorWebLog(LOG_WARN, MOD_SYSTEM, "{$username} login failed.");
			$log->VstorWebLog(LOG_WARN, MOD_SYSTEM, "{$username} 登录失败。", CN_LANG);
		}
		else
		{		
			if( check_authenticated($username,$password) === TRUE )
			{
				$bLogin = TRUE;
			}
		
			if( $bLogin === TRUE )
			{
				$_SESSION['g_bLogin'] = TRUE;
				$_SESSION['g_username'] = $username;
				
				//session_start();
    
				$log->VstorWebLog(LOG_INFOS, MOD_SYSTEM, "{$username} login.");
				$log->VstorWebLog(LOG_INFOS, MOD_SYSTEM, "{$username} 登录。", CN_LANG);
				if( isset($_SESSION['ReqAddrBeforeLogin']) )
				{
					header("Location: {$_SESSION['ReqAddrBeforeLogin']}");
				}
				else
				{
				    print $_SESSION['g_bLogin']. " ".$_SESSION['g_username']."<BR>";
					header("Location: status.php");
				}
			}
			else
			{
				print "
				<div style=\"color:red;font-weight:bold;height: 40px;\">
					{$login_error[$lang]}
				</div>
				";
				$log->VstorWebLog(LOG_WARN, MOD_SYSTEM, "{$username} login failed.");
				$log->VstorWebLog(LOG_WARN, MOD_SYSTEM, "{$username}登录失败。", CN_LANG);
				$strLastUserName = $username;
			}
		}
	}
	
	// 检查是否NVR已正常授权
	exec("export LANG=C; /usr/bin/sudo /opt/vstor/bin/CheckLic", $output, $retval);
	if( $retval === 0 )//授权成功
	{
?>
	      <form  align="center" id="login_form" name="login_form" method="post" action="login.php">
	      <table width="1024" height="125"  border="0" align="center" cellpadding="0" cellspacing="0">
		  <tr align="center">	
		    <td align="center"><span class="STYLE13"><?php print $username_str[$lang]; ?></span></td>
		  </tr>
		  <tr align="center">
		    <td><input type="text" name="username" size="16" value="<?php  print $strLastUserName; ?>" />
			</td>
		  </tr>
		  <tr align="center"><td height="10"></td></tr>
		  <tr align="center">
		    <td align="center"><span class="STYLE13"><?php print $password_str[$lang]; ?></span></td>
		  </tr>
		  <tr align="center">
			<td >
			<input type="password" name="password" size="16"
			  	oncontextmenu="return false;" oncut="return false;" onselectstart="return false;" 
			  	ondragstart="return false;" ondrop="return false;" onpaste="return false;" 
			/>
			</td>
		  </tr>
		  <tr align="center">
			<td colspan="2" valign="bottom" height="30">
			  <input type="submit" name="submit" value="<?php print $login_str[$lang]; ?>"/>
			</td>
		  </tr>
		</table>
		</form>
		<script type="text/javascript">
		// 设置用户名输入框自动焦点
		window.document.login_form.username.focus();
		window.document.login_form.username.select();
		</script>
<?php 
	}
	else // 授权不正常，禁止登陆
	{
?>
		<table width="1024" height="125"  border="0" align="center" cellpadding="0" cellspacing="0">
			<tr align="center">
				<td align="center"><span class="STYLE14"><?php print $tip_license_error_str[$lang]; ?></span></td>
			</tr>
		</table>
<?php 
	}
}
else
{
?>
      <table align="center" width="1024" border="0" cellspacing="0" cellpadding="0">
	  <tr>	
	    <td align="center"><span class="STYLE13"><?php print $admin_has_login[$lang]; ?></span></td>
	  </tr>
	  <tr><td height="10"></td></tr>
	  <tr>
	    <td align="center"><span class="STYLE13"><a href="status.php"><?php print $admin_from_here[$lang]; ?>
	    </a></span></td>
	  </tr>
	  <tr><td height="16"></td></tr>
	  <tr>	
	    <td align="center"><span class="STYLE13"><a href="logout.php" target="_self">
	    <?php print $admin_logout[$lang]; ?></a></span></td>
	  </tr>
	</table>

<?php 
}
?>
	<p>&nbsp;</p></td>
    
  </tr>

  <tr>
  </tr>
</table>

</div>



</body>
</html>
