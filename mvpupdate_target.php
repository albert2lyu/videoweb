<?php
require("./include/authenticated.php");
require_once("./include/function.php");
require_once("./include/mvpprofile.php");
require_once("./include/log.php");

$lang=load_lang();

$backup_update_manage_str=array(
	"备份/更新",
	"Backup/Upgrade"
);

$backup_MVP_str=array(
	"备份NVR Service",
	"Backup NVR Service"
);
$update_MVP_str=array(
	"更新NVR Service",
	"Update NVR Service"
);
$update_nvrweb_str=array(
		"更新管理网页",
		"Update Webpages"
);
$backup_tip_str=array(
	"下载当前NVR Service程序，备份到本地。",
	"Download current NVR Service program, and backup to local computer."
);
$update_tip_str=array(
	"更新新的文件到服务器(*.tgz文件)。",
	"Update new file to server(*.tgz file)."
);
$confirm_upload_str=array(
	"更新会覆盖原有文件，确定更新？",
	"Confirm the update?"
);
$update_ok_str=array(
	"更新成功。",
	"Updated OK."
);
$update_failed_str=array(
	"更新失败！",
	"Updated failed！"
);

?>

<?php
$mvp_dir = "/opt/driver/ /opt/MVP64 /opt/MVP64/start ";
$tmpdir = "/tmp/backup_mvp/";
$web_dir = "/opt/vstor/";
$message = "";
$log = new Log();

/*
 * 表单处理部分------------
 */
// 备份MVP文件
if( isset($_POST['backup_submit']) )
{
	CreateDir($tmpdir);

	$ipaddr = $_SERVER['SERVER_ADDR'];
	$filename = "MVP_" . $ipaddr . "_backup_" . strftime("%Y%m%d%H%M%S", time()) . ".tgz";
	$tmpfile = $tmpdir . $filename;

	// tar /opt/library files
	$command = "export LANG=C; /usr/bin/sudo /bin/tar --ignore-failed-read -czPpf " . $tmpfile . " /opt/lib/libcommon.so /opt/lib/libMiddleware.so.3.0.0.1 " 
		. " --exclude=/opt/MVP64/Exception.log --exclude=/opt/library/*log --exclude=core* --exclude=net2scom.ini --exclude=vgcore* --exclude=/opt/library/SysException.log {$mvp_dir}";
	exec($command);

	// download file
	DownloadFile($tmpfile);	
	$log->VstorWebLog(LOG_INFOS, MOD_MVP, "backup NVR Service and download it.");
	$log->VstorWebLog(LOG_INFOS, MOD_MVP, "备份并下载了NVR Service。", CN_LANG);
	// 删除备份留在服务器的文件
	$command = "export LANG=C; /usr/bin/sudo /bin/rm -f ". $tmpfile;
	exec($command);
}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<link rel="stylesheet" href="css/target.css" type="text/css" />
<script defer type="text/javascript" src="js/pngfix.js"></script>
<script type="text/javascript">
function upload_confirm(msg)
{
	if( confirm(msg) )
	{
		return true;

		
	}
	else
	{
		return false;
	}
}
</script>
</head>

<body>
<table align="center" width="100%">
	<tr>
	<td class="bar_nopanel"><?php print $backup_update_manage_str[$lang];?></td>
	</tr>
</table>

<form name="backup_MVP_form" id="backup_MVP_form" action="mvpupdate_target.php" method="post">
<table align="center" width="70%" border="0" cellpadding="6">
  <tr>
    <td class="title"><?php print $backup_MVP_str[$lang];?></td>
  </tr>
  <tr>
    <td class="field_data1">
    	<?php print $backup_tip_str[$lang];?>
    	<br/><p>
		<input type="submit" name="backup_submit" value="<?php print $backup_MVP_str[$lang];?>" />
	</td>
  </tr>
</table>
</form>

<?php
/*
 * 表单处理部分
 */
// 更新MVP文件


if( isset($_POST['update_submit']) )
{

	if(
		is_uploaded_file($_FILES['MVP_update_file']['tmp_name']) 
		&& 
		substr(strrchr($_FILES['MVP_update_file']['name'], "."), 1)=="tgz"
		&& 
		$_FILES['MVP_update_file']['size']<83886080
		&& 
		$_FILES['MVP_update_file']['size']>0
	)
	{

		//print( "mvp is running. stop MVP ... \n");
		$command = "export LANG=C; /usr/bin/sudo /opt/MVP64/start stop  ";
		exec($command);
	
		$update_file = "/tmp/" . $_FILES['MVP_update_file']['name'];
		
		move_uploaded_file($_FILES['MVP_update_file']['tmp_name'], $update_file);
		// 解压文件
		$command = "export LANG=C; /usr/bin/sudo /bin/tar -xzf " . $update_file . " -C /tmp/";
		exec($command);
		
		// 将解压后的文件转移到/opt/的相应目录
		if( is_dir("/tmp/opt/") )
		{
			// 删除旧文件
			//$command = "export LANG=C; /usr/bin/sudo /bin/rm -rf {$mvp_dir}";
			//exec($command);
			
			// 更新文件
			$command = "export LANG=C; /usr/bin/sudo /bin/cp -ar /tmp/opt/* /opt/";
			exec($command);
			
			// 删除上传的文件解压后的文件
			//$command = "export LANG=C; /usr/bin/sudo /bin/rm -rf /tmp/opt/ {$update_file}";
			//exec($command);
			
			// 使MVP文件可执行、库可用
			$command = "export LANG=C; /usr/bin/sudo /bin/chmod a+x /opt/MVP64/mvp";
			exec($command);
			$command = "export LANG=C; /usr/bin/sudo /bin/chmod a+x /opt/MVP64/start";
			exec($command);
			$command = "export LANG=C; /usr/bin/sudo /sbin/ldconfig & ";
			exec($command);

			$command = "export LANG=C; /usr/bin/sudo /usr/bin/mysql -f < /opt/MVP64/update.sql ";
			exec($command);

	
			$command = "export LANG=C; /usr/bin/sudo /opt/MVP64/start & ";
			exec($command);
			
			$message = $update_ok_str[$lang];
			$log->VstorWebLog(LOG_INFOS, MOD_MVP, "update NVR Service ok.");
			$log->VstorWebLog(LOG_INFOS, MOD_MVP, "升级NVR Service成功。", CN_LANG);
		}
		else
		{
			$message = $update_failed_str[$lang];
			$log->VstorWebLog(LOG_ERROR, MOD_MVP, "update NVR Service failed.");
			$log->VstorWebLog(LOG_ERROR, MOD_MVP, "升级NVR Service失败。", CN_LANG);
		}
		
		// 删除上传的文件
		$command = "export LANG=C; /usr/bin/sudo /bin/rm -f {$update_file}";
		exec($command);
	}
	else
	{
		$message = $update_failed_str[$lang];
		$log->VstorWebLog(LOG_ERROR, MOD_MVP, "update NVR Service failed.");
		$log->VstorWebLog(LOG_ERROR, MOD_MVP, "升级NVR Service失败。", CN_LANG);
	}
}

// 判断MVP是否在运行，如果运行不允许更新
$bMVP_running = FALSE;
if( IsMVPRunning() )
{		
	//$bMVP_running = TRUE;
}

$disabled = "";
if($bMVP_running)
{
	$disabled = " disabled=\"disabled\" ";
}

?>
<form name="update_MVP_form" id="update_MVP_form" enctype="multipart/form-data" action="mvpupdate_target.php" method="post">
<table align="center" width="70%" border="0" cellpadding="6">
  <tr>
    <td class="title"><?php print $update_MVP_str[$lang];?></td>
  </tr>
  <tr>
    <td class="field_data2">
    <?php print $update_tip_str[$lang];?>
    <br/><p>
    <!-- <input type="hidden" name="MAX_FILE_SIZE" value="83886080" /> -->
	<input type="file" <?php print $disabled;?> name="MVP_update_file" size="49%" />
	<br/><p>
	<input type="submit" <?php print $disabled;?> name="update_submit" onClick="return upload_confirm('<?php print $confirm_upload_str[$lang];?>');" value="<?php print $update_MVP_str[$lang];?>" />
	</td>
    </tr>
</table>
</form>

<hr>

<?php 
// 更新web
if( isset($_POST['webupdate_submit']) )
{
	if(
		is_uploaded_file($_FILES['nvrweb_update_file']['tmp_name']) 
		&& 
		substr(strrchr($_FILES['nvrweb_update_file']['name'], "."), 1)=="tgz"
		&& 
		$_FILES['nvrweb_update_file']['size']<83886080
		&& 
		$_FILES['nvrweb_update_file']['size']>0
	)
	{
		$update_file = "/tmp/" . $_FILES['nvrweb_update_file']['name'];
		
		move_uploaded_file($_FILES['nvrweb_update_file']['tmp_name'], $update_file);
		// 解压文件
		$command = "export LANG=C; /usr/bin/sudo /bin/tar -xzf " . $update_file . " -C /tmp/";
		exec($command);
		
		// 将解压后的文件转移到/opt/的相应目录
		if( is_dir("/tmp/vstor/") )
		{
			// 删除旧文件
			$command = "export LANG=C; /usr/bin/sudo /bin/rm -rf /opt/vstor/*";
			exec($command);
			
			// 更新文件
			$command = "export LANG=C; /usr/bin/sudo /bin/cp -ar /tmp/vstor/* /opt/vstor/";
			exec($command);
			
			$command = "export LANG=C; /usr/bin/sudo /bin/chmod a+x /opt/vstor/bin/*";
			exec($command);
			$command = "export LANG=C; /usr/bin/sudo /bin/chown vstor.vstor /opt/vstor/ -R";
			exec($command);
			
			// 删除上传的文件解压后的文件
			$command = "export LANG=C; /usr/bin/sudo /bin/rm -rf /tmp/vstor/";
			exec($command);
			
			$message = $update_ok_str[$lang];
			$log->VstorWebLog(LOG_INFOS, MOD_MVP, "update webpages ok.");
			$log->VstorWebLog(LOG_INFOS, MOD_MVP, "升级网页成功。", CN_LANG);
		}
		else
		{
			$message = $update_failed_str[$lang];
			$log->VstorWebLog(LOG_ERROR, MOD_MVP, "update webpages failed.");
			$log->VstorWebLog(LOG_ERROR, MOD_MVP, "升级网页失败。", CN_LANG);
		}
		
		// 删除上传的文件
		$command = "export LANG=C; /usr/bin/sudo /bin/rm -f {$update_file}";
		exec($command);
	}
	else
	{
		$message = $update_failed_str[$lang];
		$log->VstorWebLog(LOG_ERROR, MOD_MVP, "update webpages failed.");
		$log->VstorWebLog(LOG_ERROR, MOD_MVP, "升级网页失败。", CN_LANG);
	}
}
?>

<form name="update_nvrweb_form" id=""update_nvrweb_form"" enctype="multipart/form-data" action="mvpupdate_target.php" method="post">
<table align="center" width="70%" border="0" cellpadding="6">
  <tr>
    <td class="title"><?php print $update_nvrweb_str[$lang];?></td>
  </tr>
  <tr>
    <td class="field_data1">
    <?php print $update_tip_str[$lang];?>
    <br/><p>
    <!-- <input type="hidden" name="MAX_FILE_SIZE" value="83886080" /> -->
	<input type="file" name="nvrweb_update_file" size="49%" />
	<br/><p>
	<input type="submit" name="webupdate_submit" onClick="return upload_confirm('<?php print $confirm_upload_str[$lang];?>');" value="<?php print $update_nvrweb_str[$lang];?>" />
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


