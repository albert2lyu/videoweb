<?php
/*
 * 说明：配置NFS服务端
 * 		1、通过NFS共享指定的目录
 * 		2、取消某一目录的NFS共享
 * 
 * created by 王大典, 2009-12-01
 */
require_once("./include/function.php");

define('FILE_EXPORTS', "/etc/exports");
define('CMD_EXPORTFS', "export LANG=C; /usr/bin/sudo /usr/sbin/exportfs ");
define('CMD_NFS_START',  "export LANG=C; /usr/bin/sudo /sbin/service start");
define('CMD_NFS_STOP',  "export LANG=C; /usr/bin/sudo /sbin/service nfs stop");

/*
 * 说明：判断nfs服务是否已经启动
 * 参数：无
 * 返回：启动返回TRUE，否则返回FALSE
 */
function IsNfsSrvRunning()
{
	exec("export LANG=C; /usr/bin/sudo /sbin/pidof nfsd", $output, $retval);
	if($retval != 0)
	{
		return FALSE;
	}
	return TRUE;
}


/*
 * 说明：启动nfs服务
 * 参数：无
 * 返回：成功返回TRUE，否则返回FALSE
 */
function StartNfsSrv()
{
	exec("export LANG=C; /usr/bin/sudo /sbin/service nfs start", $output, $retval);
	if($retval != 0)
	{
		return FALSE;
	}
	if( ! IsNfsSrvRunning() )
	{
		return FALSE;
	}
	return TRUE;
}

/*
 * 说明：停止nfs服务
 * 参数：无
 * 返回：成功返回TRUE，否则返回FALSE
 */
function StopNfsSrv()
{
	exec("export LANG=C; /usr/bin/sudo /sbin/service nfs stop", $output, $retval);
	if($retval != 0)
	{
		return FALSE;
	}
	if( IsNfsSrvRunning() )
	{
		return FALSE;
	}
	return TRUE;
}

/*
 * 说明：重启nfs服务
 * 参数：无
 * 返回：成功返回TRUE，否则返回FALSE
 */
function RestartNfsSrv()
{
	exec("export LANG=C; /usr/bin/sudo /sbin/service nfs restart", $output, $retval);
	if($retval != 0)
	{
		return FALSE;
	}
	if( ! IsNfsSrvRunning() )
	{
		return FALSE;
	}
	return TRUE;
}


class NfsServer
{
		
	function __construct()
	{
		SetFileMode(FILE_EXPORTS, "w");
		/*
		 * 启动nfs
		 */
		if(IsNfsSrvRunning() === FALSE)
		{
			StartNfsSrv();
		}
	}
	
	/*
	 * 说明：共享指定目录
	 * 参数： $sharedir：需要共享的目录
	 * 		 $hosts：共享目标的网络或主机，形式类似192.168.58.0/24
	 * 		 $opt: 共享参数
	 * 返回：成功返回列表，失败返回FALSE
	 */
	function Share($sharedir, $hosts, $opt)
	{
		if( !is_dir($sharedir) )
		{
			return FALSE;
		}
		
		$buffer = $sharedir . " " . $hosts . "(" . $opt . ")\n";
		$fp = fopen(FILE_EXPORTS, 'at');
		if($fp === FALSE)
		{
			return FALSE;
		}
		fputs($fp, $buffer);
		fflush($fp);
		fclose($fp);
		exec(CMD_EXPORTFS . "-rv");
		
		return TRUE;
	}
	
	/*
	 * 说明：取消共享指定目录
	 * 参数： $sharedir：需要共享的目录
	 * 		 $hosts：共享目标的网络或主机
	 * 返回：成功返回列表，失败返回FALSE
	 */
	function Unshare($sharedir)
	{
		if( !is_dir($sharedir) )
		{
			return FALSE;
		}
		$file_buffer = rfts(FILE_EXPORTS);
		if($file_buffer === FALSE)
		{
			return FALSE;
		}
		$lines_modify = array();
		$lines = explode("\n", $file_buffer);
		// 剔除最后的空元素，防止造成写入文件后多出一空行
		array_pop($lines);
		foreach($lines as $line)
		{
			if(preg_match("|^{$sharedir}|i", trim($line)))
			{
				continue;
			}
			$lines_modify[] = $line . "\n";
		}

		$fp = fopen(FILE_EXPORTS, 'wt');
		if($fp === FALSE)
		{
			return FALSE;
		}
		foreach($lines_modify as $line)
		{
			fputs($fp, $line);
		}
		fflush($fp);
		fclose($fp);
		exec(CMD_EXPORTFS . "-rv");
		
		return TRUE;
	}
	
	/*
	 * 说明：判断目录是否已经被NFS共享
	 * 参数：$dir：目录
	 * 返回：已共享返回TRUE，否则返回FALSE
	 */
	function IsShared($dir)
	{
		if( ! is_dir($dir) )
		{
			return FALSE;
		}
		$file_buffer = rfts(FILE_EXPORTS);
		if($file_buffer === FALSE)
		{
			return FALSE;
		}

		$lines = explode("\n", $file_buffer);
		foreach($lines as $line)
		{
			if( preg_match("|^{$dir}\s+|i", trim($line)) )
			{
				return TRUE;
			}
		}
		
		return FALSE;
	}
	
}

?>