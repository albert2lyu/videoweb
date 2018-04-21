<?php
/*
 * ˵����NFS �ͻ�������
 * 		1����ʾ������������nfs�����б�����Ŀ¼������������
 * 		2�����ء�ж��Ŀ¼
 * 		3����ʾ�ѹ��ص�NFS��nfs������IP�������ص�Ŀ¼�������ڱ���Ŀ¼��
 * 		        		        ����ѡ��ܴ�С��ʣ���С��ʹ���ʣ�
 * 		4���жϴ˹���Ŀ¼�Ƿ��Ѿ�����
 * ע��nfs�ͻ���ֻ������portmap���񼴿ɺ�nfs���������ӡ�
 * 
 * created by �����, 2009-11-30		
 */

define('CMD_SHOWMOUNT', "export LANG=C; /usr/bin/sudo /usr/sbin/showmount ");
define('CMD_MOUNT_NFS', "export LANG=C; /usr/bin/sudo /bin/mount -t nfs ");
define('CMD_UMOUNT_NFS', "export LANG=C; /usr/bin/sudo /bin/umount -f -t nfs ");
define('CMD_MOUNT', "export LANG=C; /usr/bin/sudo /bin/mount ");
define('FILE_FSTAB', "/etc/fstab");
// ����NFSʱ��ѡ������
define('MOUNT_NFS_OPT', "async,tcp,hard,bg,retrans=0,timeo=1,rsize=32768");

/*
 * ˵�����ж�portmap�����Ƿ��Ѿ�����
 * ��������
 * ���أ���������TRUE�����򷵻�FALSE
 */
function IsPortmapSrvRunning()
{
	exec("export LANG=C; /usr/bin/sudo /sbin/pidof portmap", $output, $retval);
	if($retval != 0)
	{
		return FALSE;
	}
	return TRUE;
}

/*
 * ˵��������portmap����
 * ��������
 * ���أ��ɹ�����TRUE�����򷵻�FALSE
 */
function StartPortmapSrv()
{
	exec("export LANG=C; /usr/bin/sudo /sbin/service portmap start", $output, $retval);
	if($retval != 0)
	{
		return FALSE;
	}
	if( ! IsPortmapSrvRunning() )
	{
		return FALSE;
	}
	return TRUE;
}

/*
 * ˵����ֹͣportmap����
 * ��������
 * ���أ��ɹ�����TRUE�����򷵻�FALSE
 */
function StopPortmapSrv()
{
	exec("export LANG=C; /usr/bin/sudo /sbin/service portmap stop", $output, $retval);
	if($retval != 0)
	{
		return FALSE;
	}
	if( IsPortmapSrvRunning() )
	{
		return FALSE;
	}
	return TRUE;
}

/*
 * ˵��������portmap����
 * ��������
 * ���أ��ɹ�����TRUE�����򷵻�FALSE
 */
function RestartPortmapSrv()
{
	exec("export LANG=C; /usr/bin/sudo /sbin/service portmap restart", $output, $retval);
	if($retval != 0)
	{
		return FALSE;
	}
	if( ! IsPortmapSrvRunning() )
	{
		return FALSE;
	}
	return TRUE;
}

class NfsClient
{
	private $nfs_server_ip = "";
	private $nfs_share_lists = array(
	/*
		array(
			"sharedir"=>"/mnt/test1",
			"hosts"=>"192.168.58.0/24"
		),
		...
	*/
	);
	private $mounted_nfs_lists = array(
	/*
		 array(
			"server"=>"192.168.58.230",
			"sharedir"=>"/mnt/test1",
			"mountdir"=>"/mnt/test1",
			"mountopt"=>"timeo=3,udp,soft",
			"total"=>"100G",
			"free"=>"50G",
			"usage"=>"50%"			
		 ),
		 ...	 
	 */
	);
	
	function __construct()
	{
		/*
		 * ����portmap
		 */
		if( IsPortmapSrvRunning() === FALSE )
		{
			StartPortmapSrv();
		}
	}
	
	/*
	 * ˵�����г�NFS�������Ĺ���Ŀ¼
	 * ��������
	 * ���أ�$nfs_share_lists�б�ʧ�ܷ���FALSE
	 */
	function GetShareLists($ip)
	{
		if( $this->ListShare($ip) === FALSE )
		{
			return FALSE;
		}
		
		return $this->nfs_share_lists;
	}
	
	/*
	 * ˵��������nfs����Ŀ¼
	 * ������$sharedir��nfs�����������Ŀ¼
	 * 		 $mountdir����Ҫ���ص��ı���Ŀ¼
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
	 */
	function Mount($sharedir, $mountdir, $ip)
	{
		if( ! is_dir($mountdir) )
		{
			$command = "export LANG=C; /usr/bin/sudo /bin/mkdir -p " . $mountdir;
			exec($command, $output, $retval);
			if( $retval != 0 )
			{
				return FALSE;
			}
		}
		
		$command = CMD_MOUNT_NFS . "-o " . MOUNT_NFS_OPT . " " . $ip . ":" . $sharedir . " " . $mountdir;
		exec($command, $output, $retval);
		if( $retval != 0 )
		{
			return FALSE;
		}
		
		//���ÿ����Զ�����
		$fp = fopen(FILE_FSTAB, 'at');
		if($fp === FALSE)
		{
			return FALSE;
		}
		$buffer = $ip . ":" . $sharedir . " " . $mountdir . " nfs " . MOUNT_NFS_OPT. " defaults 0 0\n";
		fputs($fp, $buffer);
		fflush($fp);
		fclose($fp);
		
		return TRUE;
	}

	/*
	 * ˵����ж���ѹ��ص�nfs����Ŀ¼
	 * ������$sharedir��nfs�����������Ŀ¼
	 * 		 $ip������Ŀ¼������IP
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
	 */
	function UnMount($sharedir, $ip)
	{
		$command = CMD_UMOUNT_NFS . $ip . ":" . $sharedir;
		exec($command, $output, $retval);
		if( $retval != 0 )
		{
			return FALSE;
		}
		//ȡ�������Զ�����
		$file_buffer = rfts(FILE_FSTAB);
		if( $file_buffer === FALSE )
		{
			return FALSE;
		}
		
		$file_lines = array();
		$lines = explode("\n", $file_buffer);
		array_pop($lines);
		foreach($lines as $line)
		{
			if(preg_match("|{$ip}:{$sharedir}|i", trim($line)))
			{
				continue;
			}
			$file_lines[] = $line . "\n";
		}
		
		$fp = fopen(FILE_FSTAB, 'wt');
		if($fp === FALSE)
		{
			return FALSE;
		}
		foreach($file_lines as $line)
		{
			fputs($fp, $line);			
		}
		fflush($fp);
		fclose($fp);
		
		return TRUE;
	}
	
	/*
	 * ˵������ȡ�ѹ��ص�NFSĿ¼��Ϣ
	 * ��������
	 * ���أ��ɹ�����$mounted_nfs_lists�б�ʧ�ܷ���FALSE
	 */
	function GetNfsMounted()
	{
		if($this->ListNfsMounted() === FALSE)
		{
			return FALSE;
		}
		
		return $this->mounted_nfs_lists;
	}
	
	/*
	 * ˵�����жϴ˹���Ŀ¼�Ƿ��Ѿ�����
	 * ������ $sharedir������Ŀ¼
	 * 		 $ip��nfs������IP��ַ
	 * ���أ����ط���TRUE�����򷵻�FALSE;
	 */
	function IsMounted($sharedir, $ip)
	{
		if($this->ListNfsMounted() === FALSE)
		{
			return FALSE;
		}

		foreach( $this->mounted_nfs_lists as $entry )
		{
			if($entry['server']==$ip && $entry['sharedir']==$sharedir)
			{
				return TRUE;
			}
		}

		return FALSE;
	}
	
	/////////////////////////////////////////////////////////
	// private
	private function ListShare($ip)
	{
		$this->nfs_share_lists = array();
		
		$command = CMD_SHOWMOUNT . "-e " . $ip;
		exec($command, $output, $retval);
		if($retval != 0)
		{
			return FALSE;
		}
		/*
		 Export list for 192.168.58.230:
		/mnt/test           192.168.58.58/32
		/mnt/192.168.58.230 192.168.58.0/24
		*/
		// �޳���һ��
		array_shift($output);
		foreach( $output as $line)
		{
			$items = preg_split("/\s+/", trim($line));
			$entry = array();
			$entry['sharedir'] = $items[0];
			$entry['hosts'] = $items[1];
			$this->nfs_share_lists[] = $entry;
		}
	}
	
	private function ListNfsMounted()
	{
		$this->mounted_nfs_lists = array();
		
		//192.168.58.230:/mnt/192.168.58.230 on /mnt type nfs (rw,timeo=3,udp,soft,addr=192.168.58.230)
		exec(CMD_MOUNT, $output, $retval);
		if( $retval != 0 )
		{
			return FALSE;
		}
		
		foreach( $output as $line )
		{
			if( preg_match("/([0-9\.]*):([^\s]*)\s+on\s+([^\s]*)\s+type\s+nfs\s+(.*)/i", trim($line), $match) )
			{
				$entry = array(
				/*
					"server"=>"192.168.58.230",
					"sharedir"=>"/mnt/test1",
					"mountdir"=>"/mnt/test1",
					"mountopt"=>"timeo=3,udp,soft",
					"total"=>"100G",
					"free"=>"50G",
					"usage"=>"50%"			
				 */
				);
				
				$entry['server'] = $match[1];
				$entry['sharedir'] = $match[2];
				$entry['mountdir'] = $match[3];
				$mount['mountopt'] = $match[4];
				// ��ȡ��С
				/*
				 192.168.58.230:/mnt/192.168.58.230
                     								 123G   23G   95G  20% /mnt/123
				 */
				exec("export LANG=C; /bin/df -h", $df_output,  $df_retval);
				if($df_retval != 0)
				{
					$entry['total'] = "-";
					$entry['free'] = "-";
					$entry['usage'] = "100%";
				}
				else
				{
					foreach( $df_output as $df_line)
					{
						if( preg_match("|\s+([0-9%]*)\s+{$entry['mountdir']}|i", trim($df_line)) )
						{
							preg_match("|([0-9GMKB]*)\s+([0-9GMKB]*)\s+([0-9GMKB]*)\s+([0-9%]*)\s+{$entry['mountdir']}|i", trim($df_line), $match);
							$entry['total'] = isset($match[1]) ? $match[1] : "-";
							$entry['free']  = isset($match[3]) ? $match[3] : "-";
							$entry['usage'] = isset($match[4]) ? $match[4] : "-";
							break;
						}
					}//foreach( $df_output as $df_line)
				}// if($df_retval != 0) else
				
				$this->mounted_nfs_lists[] = $entry;
			}// if( preg_match("/\/\/([0-9\.]*):([^\s]*)\s+on\s+([^\s]*)\s+type\s+nfs\s+\((.*)\)/i", trim($line), $match) )
		}//foreach( $output as $line )
		
	}
	
}
?>