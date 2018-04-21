<?php
/*
 * ˵��������NFS�����
 * 		1��ͨ��NFS����ָ����Ŀ¼
 * 		2��ȡ��ĳһĿ¼��NFS����
 * 
 * created by �����, 2009-12-01
 */
require_once("./include/function.php");

define('FILE_EXPORTS', "/etc/exports");
define('CMD_EXPORTFS', "export LANG=C; /usr/bin/sudo /usr/sbin/exportfs ");
define('CMD_NFS_START',  "export LANG=C; /usr/bin/sudo /sbin/service start");
define('CMD_NFS_STOP',  "export LANG=C; /usr/bin/sudo /sbin/service nfs stop");

/*
 * ˵�����ж�nfs�����Ƿ��Ѿ�����
 * ��������
 * ���أ���������TRUE�����򷵻�FALSE
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
 * ˵��������nfs����
 * ��������
 * ���أ��ɹ�����TRUE�����򷵻�FALSE
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
 * ˵����ֹͣnfs����
 * ��������
 * ���أ��ɹ�����TRUE�����򷵻�FALSE
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
 * ˵��������nfs����
 * ��������
 * ���أ��ɹ�����TRUE�����򷵻�FALSE
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
		 * ����nfs
		 */
		if(IsNfsSrvRunning() === FALSE)
		{
			StartNfsSrv();
		}
	}
	
	/*
	 * ˵��������ָ��Ŀ¼
	 * ������ $sharedir����Ҫ�����Ŀ¼
	 * 		 $hosts������Ŀ����������������ʽ����192.168.58.0/24
	 * 		 $opt: �������
	 * ���أ��ɹ������б�ʧ�ܷ���FALSE
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
	 * ˵����ȡ������ָ��Ŀ¼
	 * ������ $sharedir����Ҫ�����Ŀ¼
	 * 		 $hosts������Ŀ������������
	 * ���أ��ɹ������б�ʧ�ܷ���FALSE
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
		// �޳����Ŀ�Ԫ�أ���ֹ���д���ļ�����һ����
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
	 * ˵�����ж�Ŀ¼�Ƿ��Ѿ���NFS����
	 * ������$dir��Ŀ¼
	 * ���أ��ѹ�����TRUE�����򷵻�FALSE
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