<?php
require("./include/authenticated.php");
require_once("./include/function.php");
require_once("./include/mvpprofile.php");
require_once("./include/log.php");

$lang=load_lang();

$backup_update_manage_str=array(
	"����/����NVR Service����",
	"Backup/Update NVR Service"
);

$backup_mvp_str=array(
	"����NVR Service",
	"Backup NVR Service"
);
$update_mvp_str=array(
	"����NVR Service",
	"Update NVR Service"
);
$backup_tip_str=array(
	"���ص�ǰNVR Service���򣬱��ݵ����ء�",
	"Download current NVR Service program, and backup to local computer."
);
$update_tip_str=array(
	"�����µ�NVR Service���򵽷�����(*.tgz�ļ�)����ֹͣ����NVR Service��",
	"Update new NVR Service program to server(*.tgz file). Stop NVR Service first."
);
$confirm_upload_str=array(
	"���»Ḳ��ԭ��MVP����ȷ�����£�",
	"Updating will overwrite the previous MVP program, confirm to update?"
);
$update_ok_str=array(
	"����MVP����ɹ���",
	"Updated NVR Service OK."
);
$update_failed_str=array(
	"����MVP����ʧ�ܣ�",
	"Updated NVR Service failed��"
);

?>

<?php
$mvp_dir = "/opt/library/ /opt/driver/ /opt/MVP/start";
$tmpdir = "/tmp/backup_mvp/";
$message = "";
$log = new Log();

/*
 * ��������------------
 */
// ����MVP�ļ�

if( isset($_POST['backup_submit']) )
{
	CreateDir($tmpdir);

	$ipaddr = $_SERVER['SERVER_ADDR'];
	$filename = "mvp_" . $ipaddr . "_backup_" . strftime("%Y%m%d%H%M%S", time()) . ".tgz";
	$tmpfile = $tmpdir . $filename;

	// tar /opt/library files
	$command = "export LANG=C; /usr/bin/sudo /bin/tar --ignore-failed-read -czPpf " . $tmpfile 
		. " --exclude /opt/library/Exception.log --exclude /opt/library/UsbKey.log --exclude /opt/library/SysException.log {$mvp_dir}";
	exec($command);

	// download file
	DownloadFile($tmpfile);	
	$log->VstorWebLog(LOG_INFOS, MOD_MVP, "backup NVR Service and download it.");
	$log->VstorWebLog(LOG_INFOS, MOD_MVP, "���ݲ�������NVR Service��", CN_LANG);
	// ɾ���������ڷ��������ļ�
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

<form name="backup_mvp_form" id="backup_mvp_form" action="mvpupdate_target.php" method="post">
<table align="center" width="70%" border="0" cellpadding="6">
  <tr>
    <td class="title"><?php print $backup_mvp_str[$lang];?></td>
  </tr>
  <tr>
    <td class="field_data1">
    	<?php print $backup_tip_str[$lang];?>
    	<br/><p>
		<input type="submit" name="backup_submit" value="<?php print $backup_mvp_str[$lang];?>" />
	</td>
  </tr>
</table>
</form>

<hr>

<?php 
/*
 * ��������
 */
// ����MVP�ļ�
if( isset($_POST['update_submit']) )
{
	if(
		is_uploaded_file($_FILES['mvp_update_file']['tmp_name']) 
		&& 
		substr(strrchr($_FILES['mvp_update_file']['name'], "."), 1)=="tgz"
		&& 
		$_FILES['mvp_update_file']['size']<83886080
		&& 
		$_FILES['mvp_update_file']['size']>0
	)
	{
		$update_file = "/tmp/" . $_FILES['mvp_update_file']['name'];
		move_uploaded_file($_FILES['mvp_update_file']['tmp_name'], $update_file);
		// ��ѹ�ļ�
		$command = "export LANG=C; /usr/bin/sudo /bin/tar -xzf " . $update_file . " -C /tmp/";
		exec($command);
		
		// ����ѹ����ļ�ת�Ƶ�/opt/����ӦĿ¼
		if( is_dir("/tmp/opt/") )
		{
			// ɾ�����ļ�
			$command = "export LANG=C; /usr/bin/sudo /bin/rm -rf {$mvp_dir}";
			exec($command);
			
			// �����ļ�
			$command = "export LANG=C; /usr/bin/sudo /bin/cp -ar /tmp/opt/* /opt/";
			exec($command);
			
			// ɾ���ϴ����ļ���ѹ����ļ�
			$command = "export LANG=C; /usr/bin/sudo /bin/rm -rf /tmp/opt/ {$update_file}";
			exec($command);
			
			// ʹmvp�ļ���ִ�С������
			$command = "export LANG=C; /usr/bin/sudo /bin/chmod a+x /opt/library/mvpServer";
			exec($command);
			$command = "export LANG=C; /usr/bin/sudo /bin/chmod a+x /opt/startmvp";
			exec($command);
			$command = "export LANG=C; /usr/bin/sudo /sbin/ldconfig & ";
			exec($command);
			
			$message = $update_ok_str[$lang];
			$log->VstorWebLog(LOG_INFOS, MOD_MVP, "update NVR Service ok.");
			$log->VstorWebLog(LOG_INFOS, MOD_MVP, "����NVR Service�ɹ���", CN_LANG);
		}
		else
		{
			$message = $update_failed_str[$lang];
			$log->VstorWebLog(LOG_ERROR, MOD_MVP, "update NVR Service failed.");
			$log->VstorWebLog(LOG_ERROR, MOD_MVP, "����NVR Serviceʧ�ܡ�", CN_LANG);
		}
		
		// ɾ���ϴ����ļ�
		$command = "export LANG=C; /usr/bin/sudo /bin/rm -f {$update_file}";
		exec($command);
	}
	else
	{
		$message = $update_failed_str[$lang];
		$log->VstorWebLog(LOG_ERROR, MOD_MVP, "update NVR Service failed.");
		$log->VstorWebLog(LOG_ERROR, MOD_MVP, "����NVR Serviceʧ�ܡ�", CN_LANG);
	}
}


// �ж�mvp�Ƿ������У�������в��������
$bmvp_running = FALSE;
if( IsVisRunning() )
{
	$bmvp_running = TRUE;
}
$disabled = "";
if($bmvp_running)
{
	$disabled = " disabled=\"disabled\" ";
}
?>
<form name="update_mvp_form" id="update_mvp_form" enctype="multipart/form-data" action="mvpupdate_target.php" method="post">
<table align="center" width="70%" border="0" cellpadding="6">
  <tr>
    <td class="title"><?php print $update_mvp_str[$lang];?></td>
  </tr>
  <tr>
    <td class="field_data2">
    <?php print $update_tip_str[$lang];?>
    <br/><p>
    <!-- <input type="hidden" name="MAX_FILE_SIZE" value="83886080" /> -->
	<input type="file" <?php print $disabled;?> name="mvp_update_file" size="49%" />
	<br/><p>
	<input type="submit" <?php print $disabled;?> name="update_submit" onClick="return upload_confirm('<?php print $confirm_upload_str[$lang];?>');" value="<?php print $update_mvp_str[$lang];?>" />
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