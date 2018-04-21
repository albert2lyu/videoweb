<?php
/*
 * 说明：NFS 客户端设置
 * 		1、显示给定服务器的nfs共享列表（共享目录、共享主机）
 * 		2、挂载、卸载目录
 * 		3、显示已挂载的NFS（nfs服务器IP、被挂载的目录、挂载在本机目录、
 * 		        		        挂载选项、总大小、剩余大小、使用率）
 * 		4、判断此共享目录是否已经挂载
 * 注：nfs客户端只需启动portmap服务即可和nfs服务器连接。
 * 
 * created by 王大典, 2009-11-30		
 */

define('CMD_SHOWMOUNT', "export LANG=C; /usr/bin/sudo /usr/sbin/showmount ");
define('CMD_MOUNT_NFS', "export LANG=C; /usr/bin/sudo /bin/mount -t nfs ");
define('CMD_UMOUNT_NFS', "export LANG=C; /usr/bin/sudo /bin/umount -f -t nfs ");
define('CMD_MOUNT', "export LANG=C; /usr/bin/sudo /bin/mount ");
define('FILE_FSTAB', "/etc/fstab");
// 挂载NFS时的选项配置
define('MOUNT_NFS_OPT', "async,tcp,hard,bg,retrans=0,timeo=1,rsize=32768");

/*
 * 说明：判断portmap服务是否已经启动
 * 参数：无
 * 返回：启动返回TRUE，否则返回FALSE
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
 * 说明：启动portmap服务
 * 参数：无
 * 返回：成功返回TRUE，否则返回FALSE
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
 * 说明：停止portmap服务
 * 参数：无
 * 返回：成功返回TRUE，否则返回FALSE
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
 * 说明：重启portmap服务
 * 参数：无
 * 返回：成功返回TRUE，否则返回FALSE
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
		 * 启动portmap
		 */
		if( IsPortmapSrvRunning() === FALSE )
		{
			StartPortmapSrv();
		}
	}
	
	/*
	 * 说明：列出NFS服务器的共享目录
	 * 参数：无
	 * 返回：$nfs_share_lists列表，失败返回FALSE
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
	 * 说明：挂载nfs共享目录
	 * 参数：$sharedir：nfs服务器共享的目录
	 * 		 $mountdir：需要挂载到的本地目录
	 * 返回：成功返回TRUE，失败返回FALSE
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
		
		//设置开机自动挂载
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
	 * 说明：卸载已挂载的nfs共享目录
	 * 参数：$sharedir：nfs服务器共享的目录
	 * 		 $ip：共享目录的主机IP
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	function UnMount($sharedir, $ip)
	{
		$command = CMD_UMOUNT_NFS . $ip . ":" . $sharedir;
		exec($command, $output, $retval);
		if( $retval != 0 )
		{
			return FALSE;
		}
		//取消开机自动挂载
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
	 * 说明：获取已挂载的NFS目录信息
	 * 参数：无
	 * 返回：成功返回$mounted_nfs_lists列表，失败返回FALSE
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
	 * 说明：判断此共享目录是否已经挂载
	 * 参数： $sharedir：共享目录
	 * 		 $ip：nfs服务器IP地址
	 * 返回：挂载返回TRUE，否则返回FALSE;
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
		// 剔除第一行
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
				// 获取大小
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