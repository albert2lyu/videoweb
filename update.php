<?php
require_once("./include/function.php");
require_once("./include/visprofile.php");

$lang=load_lang();

$backup_update_manage_str=array(
	"����/����Web����",
	"Backup/Update Web Program"
);

$backup_web_str=array(
	"����Web����",
	"Backup Web Program"
);
$update_web_str=array(
	"����Web����",
	"Update Web Program"
);
$backup_tip_str=array(
	"���ص�ǰWeb���򣬱��ݵ����ء�",
	"Download current web program, and backup to local computer."
);
$update_tip_str=array(
	"�����µ�web���򵽷�����(*.tgz�ļ�)��",
	"Update new VIS Server program to server(*.tgz file)."
);
$confirm_upload_str=array(
	"���»Ḳ��ԭ��web����ȷ�����£�",
	"Updating will overwrite the previous web program, confirm to update?"
);
$update_web_ok_str=array(
	"����web����ɹ���",
	"Updated Web Program OK."
);
$update_web_failed_str=array(
	"����web����ʧ�ܣ�",
	"Updated Web Program failed��"
);

?>

<?php
$web_dir = "/opt/vstor/web /opt/vstor/etc";
$tmpdir = "/tmp/backup_web/";
$message = "";

/*
 * ����������WEB�� BEGIN
 */
// ����web�ļ�
if( isset($_POST['backup_submit']) )
{
	CreateDir($tmpdir);

	$ipaddr = $_SERVER['SERVER_ADDR'];
	$filename = "web_" . $ipaddr . "_backup_" . strftime("%Y%m%d%H%M%S", time()) . ".tgz";
	$tmpfile = $tmpdir . $filename;

	// tar files
	$command = "export LANG=C; /usr/bin/sudo /bin/tar --ignore-failed-read -czPpf " . $tmpfile 
		. " --exclude /opt/vstor/web/log/vstor.log {$web_dir}";
	exec($command);

	// download file
	DownloadFile($tmpfile);	
}
// ɾ���������ڷ��������ļ�
SetFileMode($tmpdir . "*", 'w');
$command = "export LANG=C; /usr/bin/sudo /bin/rm -rf ". $tmpdir;
exec($command);

/*
 * ������  END
 */
?>
<html>
<head>
<title><?php print $backup_update_manage_str[$lang];?></title>
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

<form name="backup_web_form" id="backup_web_form" action="update.php" method="post">
<table align="center" width="70%" border="0" cellpadding="6">
  <tr>
    <td class="title"><?php print $backup_web_str[$lang];?></td>
  </tr>
  <tr>
    <td class="field_data1">
    	<?php print $backup_tip_str[$lang];?>
    	<br/><p>
		<input type="submit" name="backup_submit" value="<?php print $backup_web_str[$lang];?>" />
	</td>
  </tr>
</table>
</form>

<hr>

<?php 
/*
 * ��������
 */
// ����VIS�ļ�
if( isset($_POST['update_submit']) )
{
	if(
		is_uploaded_file($_FILES['web_update_file']['tmp_name']) 
		&& 
		substr(strrchr($_FILES['web_update_file']['name'], "."), 1)=="tgz"
		&& 
		$_FILES['web_update_file']['size']<83886080
		&& 
		$_FILES['web_update_file']['size']>0
	)
	{
		$update_file = "/tmp/" . $_FILES['web_update_file']['name'];
		move_uploaded_file($_FILES['web_update_file']['tmp_name'], $update_file);
		// ��ѹ�ļ�
		$command = "export LANG=C; /usr/bin/sudo /bin/tar -xzf " . $update_file . " -C /tmp/";
		exec($command);
		
		// ����ѹ����ļ�ת�Ƶ�/opt/����ӦĿ¼
		if( is_dir("/tmp/opt/") )
		{
			// ɾ�����ļ�
			//$command = "export LANG=C; /usr/bin/sudo /bin/rm -rf {$web_dir}";
			//exec($command);
			
			// �����ļ�
			$command = "export LANG=C; /usr/bin/sudo /bin/cp -ar /tmp/opt/vstor/* /opt/vstor/* -f";
			exec($command);
			
			// ɾ���ϴ����ļ���ѹ����ļ�
			$command = "export LANG=C; /usr/bin/sudo /bin/rm -rf /tmp/opt/vstor {$update_file}";
			exec($command);
			
			// �޸�web�ļ�������
			$command = "export LANG=C; /usr/bin/sudo /bin/chown vstor.vstor /opt/vstor/ -R";
			exec($command);
			
			$message = $update_web_ok_str[$lang];
		}
		else
		{
			$message = $update_web_failed_str[$lang];
		}
		
		// ɾ���ϴ����ļ�
		$command = "export LANG=C; /usr/bin/sudo /bin/rm -rf {$update_file}";
		exec($command);
	}
	else
	{
		$message = $update_web_failed_str[$lang];
	}
}

?>
<form name="update_web_form" id="update_web_form" enctype="multipart/form-data" action="update.php" method="post">
<table align="center" width="70%" border="0" cellpadding="6">
  <tr>
    <td class="title"><?php print $update_web_str[$lang];?></td>
  </tr>
  <tr>
    <td class="field_data2">
    <?php print $update_tip_str[$lang];?>
    <br/><p>
    <!-- <input type="hidden" name="MAX_FILE_SIZE" value="83886080" /> -->
	<input type="file" name="web_update_file" size="49%" />
	<br/><p>
	<input type="submit" name="update_submit" onClick="return upload_confirm('<?php print $confirm_upload_str[$lang];?>');" value="<?php print $update_web_str[$lang];?>" />
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