<?php
require("./include/authenticated.php");
require_once("./include/function.php");

$lang=load_lang();

$vstor_server_ip = $_SERVER['SERVER_ADDR']; // used by JTA for ssh

$tip_str=array(
	"双击\"Esc\"键，可自动补全相匹配的指令。",
	"Double hitting \"Esc\" key could complete command automatically."
);
$download_jre_tip_str=array(
	"如果无法正常显示，请下载安装JRE环境：",
	"If page show abnormally, please download and setup the jre: "
);
$jre_str=array(
	"JRE软件包",
	"JRE Software"
);
?>

<html>
<head>

<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<link rel="stylesheet" href="css/target.css" type="text/css" />
<script defer type="text/javascript" src="js/pngfix.js"></script>
<style type="text/css">
a{
color:#00008B;
text-decoration:underline;
}
a:hover,a:focus{
color:#00008B;
font-weight:bold;
text-decoration:underline;
}
</style>
</head>

<div style="text-align:left;color:black;margin-left:10px;">
	<?php 
		print $download_jre_tip_str[$lang]; 
	?>

<!--
<a href="tools/j2sdk-1_4_2_04-windows-i586-p.exe">
	<?php 
		//print $jre_str[$lang];
	?>
</a>
-->

</div>
 
<body>

		<applet width="98%" height="96%" archive="sshapplet/SSHTermApplet-signed.jar,sshapplet/SSHTermApplet-jdkbug-workaround-signed.jar" 
		        code="com.sshtools.sshterm.SshTermApplet" codebase="." 
		        style="border-style: ridge; border-width: 2px;">
		<param name="sshapps.connection.host" value="<?php print($vstor_server_ip); ?>"/>
		<param name="sshapps.connection.userName" value="root"/>
		<param name="sshapps.connection.connectImmediately" value="true"/>
		<param name="sshapps.connection.authenticationMethod" value="password"/>
		<param name="sshapps.connection.showConnectionDialog" value="false"/>
		<param name="sshapps.connection.disableHostKeyVerification" value="true"/>
		<param name="sshterm.ui.scrollBar" value="false"/>
		</applet>
</body>
</html>

