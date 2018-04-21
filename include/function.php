<?php
require_once("file.php");
require_once("network.php");

define('CN_LANG', 0);
define('EN_LANG', 1);
define('DATE', "export LANG=C; /usr/bin/sudo /bin/date");

$lang = load_lang();

/*��*/
$days=array(
	"��",
	"days"
);
$day=array(
	"��",
	"day"
);
/*��*/
$min=array(
	"��",
	"min"
);

// ���¼�����������
/*
 * ��������load_lang
 * ��������
 * ����ֵ���������ͣ�0���ģ�1Ӣ��
 * created by �����, 2009-10-09
 */
function load_lang()
{
	$lang_type = CN_LANG;
	
	if( isset($_SESSION['g_Language']) )
	{
		$lang_type = $_SESSION['g_Language'];
	}
	else
	{
		$file=new File("./config/vstorweb.conf");
		if( $file->Load() )
		{
			$needle = "LANG=";
			while (!$file->EOF())
			{
				if (preg_match("/^" . $needle . "[^ ][^ ]*/i", $file->GetLine(), $match))
				{
					$lang_type = trim(preg_replace("/" . $needle . "/i", "", $match[0]));
					break;
				}
			}
		}
	}
	
	if ( $lang_type!=CN_LANG && $lang_type!=EN_LANG )
	{
		$lang_type = CN_LANG;
	}
		
	return $lang_type;
}
/*
 * ˵������ȡ��Ʒ����
 * ��������
 * ����: �ɹ��������ƣ����򷵻�FALSE
 */
function get_product_name()
{
	$name="";
	$file=new File("./config/vstorweb.conf");
	if( $file->Load() )
	{
		$needle = "LOCAL_PRODUCT_IS=";
		while (!$file->EOF())
		{
			if (preg_match("/^" . $needle . "([^\r\n]*)/i", $file->GetLine(), $match))
			{
				$name=$match[1];
				break;
			}
		}
	}
	if($name == "")
	{
		return FALSE;
	}
	return $name;
	//return "���������ܴ洢���";
}

/*
 * ˵�����Ƿ���ʾMVP������
 * ��������
 * ���أ���ʾ����TRUE�����򷵻�FALSE
 */
function is_show_vismgr()
{
	$show=0;
	$file=new File("./config/vstorweb.conf");
	if( $file->Load() )
	{
		$needle = "VISMGR_SHOW=";
		while (!$file->EOF())
		{
			if (preg_match("/^" . $needle . "[^ ][^ ]*/i", $file->GetLine(), $match))
			{
				$show=trim(preg_replace("/^" . $needle . "/i", "", $match[0]));
				break;
			}
		}
	}
	
	if( $show == 0 )
	{
		return TRUE;
	}
	else
	{
		return FALSE;
	}
}

/*
 * ˵�����Ƿ���ʾRAID������
 * ��������
 * ���أ���ʾ����TRUE�����򷵻�FALSE
 */
function is_show_raidmgr()
{
	$show=0;
	$file=new File("./config/vstorweb.conf");
	if( $file->Load() )
	{
		$needle = "RAIDMGR_SHOW=";
		while (!$file->EOF()){
			if (preg_match("/^" . $needle . "[^ ][^ ]*/i", $file->GetLine(), $match)){
				$show=trim(preg_replace("/^" . $needle . "/i", "", $match[0]));
			}
		}
	}
	if( $show == 0 )
	{
		return TRUE;
	}
	else
	{
		return FALSE;
	}
}

/*
 * ˵������ӡ��Ϣ
 * ������$message����Ϣ����
 * created by , 2011-11-10
 */
function print_msg_block($message)
{
	print "<div class=\"result_tip\">";
	print $message;
	print "</div>";
}

/*
 * ˵�����Զ���ʽ���ֽ�����ΪB/KB/MB/GB
 */
function format_bytesize ($intBytes, $intDecplaces = 2) {
	//$strSpacer = '&nbsp;';
	$strSpacer = ' ';
	if ($intDecplaces <= 0)
	{
		$format_str  = "%d";
	}
	else
	{
		$format_str  = "%." . $intDecplaces . "f";
	}
	
	if( $intBytes > 1099511627776 )
	{
		$strResult = sprintf( $format_str, $intBytes / 1099511627776 );
		$strResult .= $strSpacer . "TB";
	}
	else if( $intBytes > 1073741824 ) 
	{
		$strResult = sprintf( $format_str, $intBytes / 1073741824 );
		$strResult .= $strSpacer . "GB";
	} 
	else if( $intBytes > 1048576 ) 
	{
		$strResult = sprintf( $format_str, $intBytes / 1048576);
		$strResult .= $strSpacer . "MB";
	} 
	else if( $intBytes > 1024)
	{
		$strResult = sprintf( $format_str, $intBytes / 1024);
		$strResult .= $strSpacer . "KB";
	}
	else
	{
		$strResult = sprintf( $format_str, $intBytes );
		$strResult .= $strSpacer . "B";
	}
	
	return $strResult;
}

/*
 * ˵������ʽ���ֽ�Ϊָ���ĵ�λ
 * ������$intBytes�������ֽ���
 * 		 $strUnit: GB,MB,KB,B�е�һ������λ
 * 		 $intDecplaces: ��ȷ�ȣ�С������λ��
 * ���أ���ʽ������ַ���
 * 
 * created by auto, 2009-11-01
 */
function format_bytesize_to_unit ($intBytes, $strUnit="MB"/*TB,GB,MB,KB,B*/, $intDecplaces = 2) {
	//$strSpacer = '&nbsp;';
	$strSpacer = ' ';
	if ($intDecplaces <= 0)
	{
		$format_str  = "%d";
	}
	else
	{
		$format_str  = "%." . $intDecplaces . "f";
	}
	
	if( $strUnit == "TB" )
	{
		$strResult = sprintf( $format_str, $intBytes / 1099511627776 );
	}
	else if( $strUnit == "GB" )
	{
		$strResult = sprintf( $format_str, $intBytes / 1073741824 );
	} 
	else if( $strUnit == "MB" )
	{
		$strResult = sprintf( $format_str, $intBytes / 1048576);
	} 
	else if( $strUnit == "KB" )
	{
		$strResult = sprintf( $format_str, $intBytes / 1024);
	}
	else
	{
		$strResult = sprintf( $format_str, $intBytes );
		$strUnit = "B";
	}
	
	$strResult .= $strSpacer . $strUnit;
	
	return $strResult;
}

/*
 * ˵������ʽ��hzƵ�� 
 */
function format_speed( $intHz ) {
	$strResult = "";
	
	if( $intHz < 1000 ) 
	{
		$strResult = $intHz . " Hz";
	} 
	else if($intHz < 1000000)
	{
		$strResult = round( $intHz / 1000, 2 ) . " MHz";
	}
	else
	{
		$strResult = round( $intHz / 1000 /1000, 2 ) . " GHz";
	}
	
	return $strResult;
}

/*
 * ˵������ȡ�ļ�Ϊ�ַ��������з�\n����
 * ���أ��ɹ�-�����ļ����ݣ��ַ�����ʽ����ʧ��-FALSE;
 */
function rfts( $strFileName, $intLines = 0, $intBytes = 4096) {
	global $error;
	$strFile = "";
	$intCurLine = 1;
  
	if( file_exists( $strFileName ) )
	{
		if( $fd = fopen( $strFileName, 'r' ) ) 
		{
			while( !feof( $fd ) ) 
			{
				$strFile .= fgets( $fd, $intBytes );
				if( $intLines <= $intCurLine && $intLines != 0 ) 
				{
					break;
				} 
				else 
				{
					$intCurLine++;
				}
			}
			fclose( $fd );
		} 
		else 
		{
			return FALSE;
		}
	} 
	else
	{
		return FALSE;
	}
	
	return $strFile;
}

/*
 * ˵���������ٷֱȽ���������ʾ
 * ������$value:�ٷֱȽ���������ֵ�ַ������硰60%��
 * 		 $warning:�Ƿ��о��棬��$value����һ����ֵʱ��Ĭ��90����ʹ�ú�ɫ��ʾ�ٷֱȽ����� 
 * ���أ�html��ӡ����
 * created by �����, 2009-11-01
 */
//$value = 10%
function create_percent_bar($value="0%", $warning=TRUE)
{
	$percent = $value;
	$up_limit = 90; // �ٷֱȳ���90%���ֱ���ɫ����ɫ
	$mid_limit = 70;// 60%-90%���棬��ɫ
	
	
	$value = explode("%", $value);
	$value = $value[0];
	if($value < 0)
		$value = 0;
	else if($value > 100)
		$value = 100;

	// �ٷֱȽ���������ʾ��ɫ����
	$color="#0ef424";
	if( $warning )
	{
		if( $value >= $up_limit) // >= 90%
		{
			$color = "#ee200a";
		}
		else if( $value>=$mid_limit && $value<$up_limit ) // 60% ~ 90%
		{
			$color = "#FFFA00";
		}
	}
	
	// ���ÿ��Ʊ��Ŀ����ʵ�ְٷֱȽ���������ʾЧ��
	// �������Ƕ�����Ϊ�˸��õ��ֿܷ����ƽ������ĸ߶Ⱥ�����ĸ߶�
	$buffer =  "<table cellspacing=\"0\" cellpadding=\"0\"><tr><td>";
	$buffer .= "<table><tr><td width=\"" . $value . "px\" height=\"10px\" style=\"background:" . $color . ";text-algin=center;\"></td><tr></table>";
	$buffer .= "</td><td style=\"font-size:13px;\">" . $value . "%</td></tr></table>";

/*	//ʹ��ѭ��������ӡ1���ؿ�ȵ�ͼƬ
	$buffer = "<img src=\"./images/1percent.gif\" />";
	for($i=0; $i<$value; $i++)
	{
		$buffer .= "<img src=\"./images/1percent.gif\" />";
	}
*/
	return $buffer;
}

/*
 * ˵�������������߼��������ơ��߼��������Ƿ�Ϸ���
 * 		 �Ϸ�������Ч�ľ��������ַ���A-Z a-z 0-9 _ ���������ո���ҳ����˵����
 * ������$name: �߼�������߼��������
 * ���أ��Ϸ�����TRUE�����򷵻�FALSE
 * 
 * created by �����, 2009-11-09
 */
function IsLvmNameOk($name)
{
	$retval = preg_match("/\W/", $name, $match);
	if($retval != 0)
	{
		return FALSE;
	}
	
	return TRUE;
}

/*
 * ˵������ȡĳĿ¼�Ƿ����ڱ�ʹ��
 * ������$dir��Ŀ¼
 * ���أ����ڱ�ʹ�÷���TRUE�����򷵻�FALSE
 * created by �����, 2009-11-09
 */
function IsDirUsing($dir)
{
	if( !is_dir($dir) )
	{
		return -1;
	}
	
	exec("export LANG=C;/usr/bin/sudo /sbin/fuser -m " . $dir, $output, $retval);
	if($retval == 0)
	{
		return TRUE;
	}
	return FALSE;
}

/*
 * ˵���������ļ������˿ɶ�/д/ִ��
 * ������ $file���ļ�·��
 * 		 $mode�������ļ���ģʽ��'r'����'w'д��'x'ִ�У������'rw'��'wx'��
 * 		 $set��TRUE--�����ļ���$mode���ԣ�Ĭ�ϣ���FALSE--ȡ���ļ���$mode����
 * ���أ��ɹ�����TRUE�����򷵻�FALSE
 * created by �����, 2009-11-09
 */

function SetFileMode($file, $mode, $set=TRUE)
{
	if( ! preg_match("/^[rwx]{1}[rwx]?[rwx]?/", $mode) )
	{
		return FALSE;
	}

	$command = "export LANG=C; /usr/bin/sudo /bin/chmod a";
	$command .= $set ? "+" : "-";
	if(strstr($mode, 'r') !== FALSE)
	{
		$command .= "r";
	}
	if(strstr($mode, 'w') !== FALSE)
	{
		$command .= "w";
	}
	if(strstr($mode, 'x') !== FALSE)
	{
		$command .= "x";
	}
	$command .= " " . $file;
	
	exec($command, $output ,$retval);
	if($retval != 0)
	{
		return FALSE;
	}
	
	return TRUE;
}

/*
 * ˵��������Ŀ¼
 * ������$dir��Ŀ¼
 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
 * CREATED BY �����, 2009-12-07
 */
function CreateDir($dir)
{
	$command = "export LANG=C; /usr/bin/sudo /bin/mkdir -p " . $dir;
	exec($command, $output, $retval);
	if($retval != 0)
		return FALSE;
	
	$command = "export LANG=C; /usr/bin/sudo /bin/chmod a+rw " . $dir . " -R";
	exec($command);
	return TRUE;
}

/*
 * ˵���������ļ�
 * ������$file���ļ�
 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
 * CREATED BY �����, 2009-12-07
 */
function CreateFile($file)
{
	$command = "export LANG=C; /usr/bin/sudo /bin/touch " . $file;
	exec($command, $output, $retval);
	if($retval != 0)
	{
		return FALSE;
	}
	
	return TRUE;
}

/*
 * ˵����ɾ���ļ�
 * ������$file���ļ�
 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
 * CREATED BY �����, 2009-12-07
 */
function RemoveFile($file)
{
	if(! file_exists($file) )
	{
		return TRUE;
	}
	
	$command = "export LANG=C; /usr/bin/sudo /bin/rm -rf " . $file;
	exec($command, $output, $retval);
	if($retval != 0)
	{
		return FALSE;
	}
	
	return TRUE;
}

/*
 * ˵�����ж�IP�ĺϷ���
 * ������$ip��ip��ַ����192.168.58.230
 * ���أ��Ϸ�IP�򷵻�TRUE�����򷵻�FALSE
 */
function IsIpOk($ip)
{
	$ipaddr = trim($ip);
	//  1.2.3.4              000.000.000.000
	if( strlen($ipaddr)<7 || strlen($ipaddr)>15
	 || 
	 ! preg_match("/^([0-9]+\.){3}[0-9]+$/", $ipaddr) )
	{
		return FALSE;
	}
	
	$ip_fields = explode(".", $ipaddr);
	if( count($ip_fields) == 4 )
	{
		foreach($ip_fields as $field)
		{
			if( $field<0 || $field>255)
			{
				return FALSE;
			}
		}
	}
	else
	{
		return FALSE;
	}

	return TRUE;
}

/*
 * ˵�����ж��Ƿ�������ϰ���MVP�������������ʹ���ִ��
 * ��������
 * ���أ���������򷵻�TRUE�����򷵻�FALSE
 * CREATED BY �����, 2009-12-07
 */
function IsMVPExisted()
{
	$mvp_exe_file = "/opt/MVP64/mvp";
	if( file_exists($mvp_exe_file) )
	{
		SetFileMode($mvp_exe_file, 'wx');
		return TRUE;
	}
	
	return FALSE;
}

/*
 * ˵���������ļ�
 * ���ñ�����ǰ�������κ�http����������͵������ļ���������������������ļ�����ȷ��
 */
function DownloadFile($fileName, $fancyName = '', $forceDownload = true, $speedLimit = 0, $contentType = '') 
{ 
    if (!is_readable($fileName)) 
    { 
        header("HTTP/1.1 404 Not Found"); 
        return FALSE; 
    } 
 
    $fileStat = stat($fileName); 
    $lastModified = $fileStat['mtime']; 

    $md5 = md5($fileStat['mtime'] .'='. $fileStat['ino'] .'='. $fileStat['size']); 
    $etag = '"' . $md5 . '-' . crc32($md5) . '"'; 
  
    header('Last-Modified: ' . gmdate("D, d M Y H:i:s", $lastModified) . ' GMT'); 
    header("ETag: $etag"); 
  
    if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $lastModified) 
    { 
        header("HTTP/1.1 304 Not Modified"); 
        return FALSE; 
    } 
  
    if (isset($_SERVER['HTTP_IF_UNMODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_UNMODIFIED_SINCE']) < $lastModified) 
    { 
        header("HTTP/1.1 304 Not Modified"); 
        return FALSE; 
    } 
  
    if (isset($_SERVER['HTTP_IF_NONE_MATCH']) &&  $_SERVER['HTTP_IF_NONE_MATCH'] == $etag) 
    { 
        header("HTTP/1.1 304 Not Modified"); 
        return FALSE; 
    } 

    if ($fancyName == '') 
    { 
        $fancyName = basename($fileName); 
    } 
  
    if ($contentType == '') 
    { 
        $contentType = 'application/octet-stream'; 
    } 
  
    $fileSize = $fileStat['size']; 
  
    $contentLength = $fileSize; 
    $isPartial = FALSE; 
  
    if (isset($_SERVER['HTTP_RANGE'])) 
    { 
        if (preg_match('/^bytes=(\d*)-(\d*)$/', $_SERVER['HTTP_RANGE'], $matches)) 
        { 
            $startPos = $matches[1]; 
            $endPos = $matches[2]; 
  
            if ($startPos == '' && $endPos == '') 
            { 
            	header("HTTP/1.1 404 Not Found"); 
                return FALSE; 
            } 
  
            if ($startPos == '') 
            { 
                $startPos = $fileSize - $endPos; 
                $endPos = $fileSize - 1; 
            } 
            else if ($endPos == '') 
            { 
                $endPos = $fileSize - 1; 
            } 
  
            $startPos = $startPos < 0 ? 0 : $startPos; 
            $endPos = $endPos > $fileSize - 1 ? $fileSize - 1 : $endPos; 
  
            $length = $endPos - $startPos + 1; 
  
            if ($length < 0) 
            { 
            	header("HTTP/1.1 404 Not Found"); 
                return FALSE; 
            } 
  
            $contentLength = $length; 
            $isPartial = FALSE; 
        } 
    } 
  
    // send headers 
    if ($isPartial) 
    { 
        header('HTTP/1.1 206 Partial Content'); 
        header("Content-Range: bytes $startPos-$endPos/$fileSize"); 
  
    } 
    else 
    { 
        header("HTTP/1.1 200 OK"); 
        $startPos = 0; 
        $endPos = $contentLength - 1; 
    }
    
  	header("Pragma: public");
	header("Expires: 0");
    header('Cache-Control: public, must-revalidate, max-age=0, post-check=0, pre-check=0'); 
    header('Accept-Ranges: bytes'); 
    header('Content-type: ' . $contentType); 
    if ($forceDownload) 
    { 
    	header("Content-Type: application/force-download");
        header('Content-Disposition: attachment; filename="' . $fancyName. '"'); 
        header("Content-Type: application/download");
    } 
  
    header("Content-Transfer-Encoding: binary"); 
    header('Content-Length: ' . $contentLength); 
  
    $bufferSize = 2048; 
  
    if ($speedLimit != 0) 
    { 
        $packetTime = floor($bufferSize * 1000000 / $speedLimit); 
    } 
    
    /*
     * ɾ��output�������е����ݣ���ֹ���͵������ļ�����ȷ��
     */ 
    ob_clean();
  
    $bytesSent = 0; 
    $fp = fopen($fileName, "rb"); 
    fseek($fp, $startPos);
    
    while ($bytesSent < $contentLength && !feof($fp) && connection_status() == 0 ) 
    { 
        if ($speedLimit != 0) 
        { 
            list($usec, $sec) = explode(" ", microtime()); 
            $outputTimeStart = ((float)$usec + (float)$sec); 
        } 
  
        $readBufferSize = $contentLength - $bytesSent < $bufferSize ? $contentLength - $bytesSent : $bufferSize; 
        $buffer = fread($fp, $readBufferSize); 
  
        echo $buffer; 
  
        ob_flush(); 
        flush(); 
  
        $bytesSent += $readBufferSize; 
  
        if ($speedLimit != 0) 
        { 
            list($usec, $sec) = explode(" ", microtime()); 
            $outputTimeEnd = ((float)$usec + (float)$sec); 
  
            $useTime = ((float) $outputTimeEnd - (float) $outputTimeStart) * 1000000; 
            $sleepTime = round($packetTime - $useTime); 
            if ($sleepTime > 0) 
            { 
                usleep($sleepTime); 
            } 
        } 
    }
    //exit();
    return TRUE; 
} 

/*
 * ˵�����ж�Ŀ¼�Ƿ��Ѿ���Ϊmysql�洢�ļ������ݿ��ı���Ŀ¼��
 * ������$bk_dir��Ŀ¼
 * ���أ��ɹ�TRUE������FALSE
 */
function IsMysqlStorageFileBkDir($bk_dir)
{
	$root_cron = "/var/spool/cron/root";
	SetFileMode("/var/spool", "rw");
	SetFileMode("/var/spool/cron", "rw");
	SetFileMode($root_cron, "rw");
	// 0 10 * * * /usr/bin/mysqldump -uadmin -padmin --opt MVP3 StorageFile_tbl>/mnt/192.168.51.14/14945000/StorageFile_tbl.txt
	
	$file = new File($root_cron);
	if( ! $file->Load() )
	{
		return FALSE;
	}
	$lines = array();
	$dir_list = array();
	while( TRUE )
	{
		if( ( $line = $file->GetLine() ) === FALSE )
		{
			break;
		}
		if( preg_match("|MVP3\s+StorageFile_tbl\s*>\s*(.*)|i", trim($line), $match) )
		{
			$buffer = $match[1];
			$dir = substr($buffer, 0, strrpos($buffer, "/"));
			$dir_list[] = $dir;
		}
		else
		{
			continue;
		}
	}
	if( count($dir_list) === 0 )
	{
		return FALSE;
	}
	
	if( $bk_dir[strlen($bk_dir)-1] == "/" )
	{
		$bk_dir = substr($bk_dir, 0, strlen($bk_dir)-1);
	}
	if( in_array($bk_dir, $dir_list) )
	{
		return TRUE;
	}
	return FALSE;	
}

/*
 * ˵��������mysql�洢�ļ������ݿ����
 * ������$dir������Ŀ¼
 * ���أ��ɹ�TRUE������FALSE
 */
function SetMysqlStorageFileTableBk($dir)
{
	$root_cron = "/var/spool/cron/root";
	SetFileMode("/var/spool", "rw");
	SetFileMode("/var/spool/cron", "rw");
	SetFileMode($root_cron, "rw");
	$filename = "/StorageFile_tbl.txt";
	$line = "*/30 * * * * /usr/bin/mysqldump -uadmin -padmin --opt MVP3 StorageFile_tbl > " . $dir;
	$line_match = "\*\/30 \* \* \* \* /usr/bin/mysqldump -uadmin -padmin --opt MVP3 StorageFile_tbl > " . $dir;
	$file = new File($root_cron);
	if($file->Load() === FALSE)
	{
		return FALSE;
	}
	if( $file->FindLine($line_match) === FALSE )
	{
		$file->AddLine($line . $filename);
	}
	$file->Save();
	$cmd = "export LANG=C; /usr/bin/sudo /usr/bin/crontab -u root " . $root_cron;
	exec($cmd, $output, $retval);
	return TRUE;	
}

function UnsetMysqlStorageFileTableBk($dir)
{
	$root_cron = "/var/spool/cron/root";
	SetFileMode("/var/spool", "rw");
	SetFileMode("/var/spool/cron", "rw");
	SetFileMode($root_cron, "rw");
	$filename = "/StorageFile_tbl.txt"; 
	$line = "*/30 * * * * /usr/bin/mysqldump -uadmin -padmin --opt MVP3 StorageFile_tbl > " . $dir;
	$line_match = "\*\/30 \* \* \* \* /usr/bin/mysqldump -uadmin -padmin --opt MVP3 StorageFile_tbl > " . $dir;
	$file = new File($root_cron);
	if($file->Load() === FALSE)
	{
		return FALSE;
	}
	if( $file->FindLine($line_match) !== FALSE )
	{
		$file->DeleteLine($line_match, TRUE);
	}
	$file->Save();
	$cmd = "export LANG=C; /usr/bin/sudo /usr/bin/crontab -u root " . $root_cron;
	exec($cmd, $output, $retval);
	return TRUE;	
}

/*
 * ˵������ȡuptime��Ϣ
 * ��������
 * ���أ�������Ϣ�б�ʧ���򷵻�FALSE
 * 		�б���ʽ���£�
 * 		array(
			"time"=>"10:22:15",
			"uptime"=>"3 ��",
			"user"=>5,
			"load_average"=>"0.00, 0.10, 0.10"
  		)
 */
function get_uptime_info()
{
	$uptime_info = array();
	global $lang, $days, $day, $min;
	$buffer = array();
	$retval = 0;
	exec("export LANG=C; /usr/bin/uptime", $buffer, $retval);
	
	if( $retval == 0 )
	{
		// ��������������
		//11:10:41 up 1 days,  4:06,  1 user,  load average: 0.22, 0.08, 0.03
		//  0              ,     1  ,   2   ,      3      
		//11:10:41 up 1 min, 11 user,  load average: 0.22, 0.08, 0.03
		//  0              ,     1  ,   2     
		$load_average_value = "";
		$system_time_value = "";
		$up_time_value = "";
		$user_count_value = "";
		//ȡ��load averag
		$output = explode(",  load average: ", $buffer[0]);
		$load_average_value = $output[1];
		// ȡ��ʱ��
		$output = explode(" up ", $output[0]);
		$system_time_value = trim($output[0]);
		// ȡ������ʱ��
		$output = preg_replace("/,\ {1,}/", ";", $output[1]);
		$output = explode(";", $output);
		$count = count($output);
	
		if( $count == 3 )
		{
			$up_time_value = $output[0] . " " . $output[1];
			$user_count_value = $output[2];
		}
		else
		{
			$up_time_value = $output[0];
			$user_count_value = $output[1];
		}
	
		$up_time_value = preg_replace("/\ days/", $days[$lang], $up_time_value);
		$up_time_value = preg_replace("/\ day/", $day[$lang], $up_time_value);
		$up_time_value = preg_replace("/\ min/", $min[$lang], $up_time_value);
		$user_count_value = preg_replace("/\ users/", "", $user_count_value);
		$user_count_value = preg_replace("/\ user/",  "", $user_count_value);
		 /* array(
				"time"=>"10:22:15",
				"uptime"=>"3 ��",
				"user"=>5,
				"load_average"=>"0.00, 0.10, 0.10"
  			)*/
		$uptime_info['time'] = $system_time_value;
		$uptime_info['uptime'] = $up_time_value;
		$uptime_info['user'] = $user_count_value;
		$uptime_info['load_average'] = $load_average_value;
		
		return $uptime_info;
	}
	
	return FALSE;
}

/*
 * ˵��������html��select�ؼ���netmaskѡ���б�
 * 		����option��valueΪnetmask����<option value="255.255.255.0">255.255.255.0
 * ������$sel��Ĭ��ѡ������255.255.255.0
 * ���أ�TRUE
 * created by �����, 2009-12-22
 */
function print_netmask_of_select_bynetmask($sel="255.255.255.0")
{
	$network = new NetWork();
	// ��ȡ���������б�
	$netmask_list = $network->GenerateNetmasks();

  	foreach($netmask_list as $netmask_entry)
  	{
  		if($sel == $netmask_entry)
  		{
  			print "<option value=\"" . $netmask_entry ."\" selected>" . $netmask_entry . "\n";
  		}
  		else
  		{
  			print "<option value=\"" . $netmask_entry ."\">" . $netmask_entry . "\n";
  		}
  	}
  	return TRUE;
} 

/*
 * ˵��������html��select�ؼ���netmaskѡ���б�
 * 		����option��valueΪprefix����<option value="24">255.255.255.0
 * ������$sel��Ĭ��ѡ������255.255.255.0
 * ���أ�TRUE
 * created by �����, 2009-12-22
 */
function print_netmask_of_select_byprefix($sel="255.255.255.0")
{
	$network = new NetWork();
	// ��ȡ���������б�
	$netmask_list = $network->GenerateNetmasks();
	
	$prefix = 0;
	foreach($netmask_list as $netmask_entry)
	{
		if($sel == $netmask_entry)
		{
			print "<option value=\"" . $prefix ."\" selected>" . $netmask_entry . "\n";
		}
		else
		{
			print "<option value=\"" . $prefix ."\">" . $netmask_entry . "\n";
		}
		$prefix++;
	}
  	return TRUE;
} 

/*
 * ˵�������ɴ���Ԥ����С���б�
 */
function print_ra_of_select($sel)
{
	$ra_sector = array(  256,   2048,   8192, 16384, 32768);
	$ra_size   = array("128KB", "1MB", "4MB", "8MB", "16MB");
	
	for($i=0; $i<count($ra_sector); $i++)
	{
		if($sel == $ra_sector[$i])
		{
			print "<option value=\"{$ra_sector[$i]}\" selected>{$ra_size[$i]}\n";
		}
		else
		{
			print "<option value=\"{$ra_sector[$i]}\">{$ra_size[$i]}\n";
		}
	}
	return TRUE;
}

/*
 * ˵�������ɰ�ģʽ���б�
 */
function print_bond_mode_list_of_select($sel=0)
{
	$mode_value_list = array(   0, 1, 2, 3, 4, 5, 6 );
	$mode_str_list   = array(   "Balance Round-robin", 
								"Active-Backup",
								"Balance-XOR",
								"Broadcast",
								"802.3ad",
								"Balance-tlb",
								"Balance-alb");
	
	for($i=0; $i<count($mode_value_list); $i++)
	{
		if($sel == $mode_value_list[$i])
		{
			print "<option value=\"{$mode_value_list[$i]}\" selected>{$mode_str_list[$i]}\n";
		}
		else
		{
			print "<option value=\"{$mode_value_list[$i]}\">{$mode_str_list[$i]}\n";
		}
	}
	return TRUE;
}

/*
 * ˵�����жϴ����3ware������ID������ID���˿�ID���Ƿ���ȷ
 */
function IsIdOk($id)
{
	/*
	 * idΪ8��0-255֮���ʮ������ֵ��ÿ����ֵ��һ���ո����
	 * ���磺2 3 4 255 255 255 255 255
	 */
	$id_array = array();
	$id_array = explode(" ", trim($id));
	if( count($id_array) != 8 )
	{
		return FALSE;
	}
	
	foreach( $id_array as $value )
	{
		// ������
		if( preg_match("/^[^0-9]$/", $value) )
		{
			return FALSE;
		}
		// ��ֵ�Ƿ�
		if( $value > 255 || $value < 0 )
		{
			return FALSE;
		}
	}
	
	return TRUE;
}
/*
 * ˵�����ж�raid�����Ƿ�Ϸ� a-z A-Z 0-9 _ -
 */
function IsRaidNameOk($name)
{
	$length = strlen($name);
	if( $length == 0 )
	{
		return TRUE;
	}
	if( $length > 20 )
	{
		return FALSE;
	}
	
	$retval = preg_match("|[^a-zA-Z0-9_-]|", $name);
	if($retval === 1 )
	{
		return FALSE;
	}
	return TRUE;
}

/*
 * ˵������ȡ�洢���ƿ�����
 * ��������
 * ����: �ɹ��������ͣ����򷵻�FALSE
 *       ���Ͷ��壺1-3ware 9750-8i   2-LSIMegaRAIDSAS9261-8i
 */
function get_raid_type()
{
	$raid_type = "";
	$file=new File("./config/vstorweb.conf");
	if( $file->Load() )
	{
		$needle = "RAID_TYPE=";
		while (!$file->EOF())
		{
			if (preg_match("/^" . $needle . "([^\r\n]*)/i", $file->GetLine(), $match))
			{
				$raid_type=trim($match[1]);
				break;
			}
		}
	}
	if($raid_type == "")
	{
		return FALSE;
	}
	return $raid_type;
}

/*
 * ˵������ȡϵͳʱ��
 * ��������
 * ����: �ɹ�����ʱ�䣨��ʽ"2017-12-12 10:20:39"�������򷵻�FALSE
 */
function get_sys_time()
{
    $command = DATE . " \"+%Y-%m-%d %H:%M:%S\"";
    return trim(shell_exec($command));
}
?>

