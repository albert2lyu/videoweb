<?php
require_once("function.php");
require_once("network.php");
require_once("file.php");

/*
 * 说明：
 * 		1、设置、获取ha信息，仅支持双机热备
 * 		2、判断ha是否已启用
 * 		3、启动、停止heartbeat服务
 * 
 * careated by 王大典, 2009-12-22
 */

define('FILE_HA_CF', "/etc/ha.d/ha.cf");
define('FILE_HARESOURCES', "/etc/ha.d/haresources");
define('FILE_AUTHKEYS', "/etc/ha.d/authkeys");

class VisHa
{
	function __construct()
	{

	}
	
	/*
	 * 说明：判断HA是否已启用
	 * 参数：无
	 * 返回：已启用返回TRUE，否则返回FALSE
	 */
	function IsVisHaEnabled()
	{
		$command = "export LANG=C; /usr/bin/sudo /sbin/pidof heartbeat";
		exec($command, $output, $retval);
		if($retval == 0)
		{
			return TRUE;
		}
		
		return FALSE;
	}
	
	/*
	 * 说明：获取VISHA信息
	 * 参数：无
	 * 返回：获取成功则返回信息列表，否则返回FALSE
	 		信息列表：
	 		array(
	 			"active"=>array(
	 				"hostname"=>"VIS230",
	 				"ipaddr"=>"192.168.58.230",
	 				"bcast"=>"eth0"
	 			),
	 			
	 			"standby"=>array(
	 				"hostname"=>"VIS231",
	 				"ipaddr"=>"192.168.58.231",
	 				"bcast"=>""
	 			),
	 			
	 			"cluster"=>array(
	 				"node"=>"VIS230",
	 				"ipaddr"=>"",
	 				"prefix"=>"24",
	 				"bcast"=>"192.168.58.255",
	 				"resources"=>"visserver"
	 			)
	 		)
	 */
	function GetVisHaInfo()
	{
		if( ! $this->IsVisHaEnabled() )
		{
			return FALSE;
		}
		
		$visha_info = array();
		
		// 获取节点信息
		$act_node_name = "";
		$std_node_name = "";
		$bcast_dev = "";
		$this->GetHacfInfo($act_node_name, $std_node_name, $bcast_dev);
		$visha_info['active']['hostname'] = $act_node_name;
		$visha_info['standby']['hostname'] = $std_node_name;
		
		// 获取节点IP
		$act_node_ip = $this->GetIpByHostname($act_node_name);
		$std_node_ip = $this->GetIpByHostname($std_node_name);
		$visha_info['active']['ipaddr'] = $act_node_ip;
		$visha_info['standby']['ipaddr'] = $std_node_ip;
		
		// 判断本机是active还是standby
		$network = new Network();
		$hostname = $network->GetHostname();
		if($hostname == $act_node_name)
		{
			$visha_info['active']['bcast'] = $bcast_dev;
			$visha_info['standby']['bcast'] = "";
		}
		else if($hostname == $std_node_name)
		{
			$visha_info['active']['bcast'] = "";
			$visha_info['standby']['bcast'] = $bcast_dev;
		}
		else
		{
			return FALSE;
		}
		
		// 获取集群信息
		$clusterinfo = $this->GetHaresourcesInfo($act_node_name);
		if($clusterinfo === FALSE)
		{
			return FALSE;
		}
		
		$visha_info['cluster']['node'] = $act_node_name;
		$visha_info['cluster']['ipaddr'] = $clusterinfo['ipaddr'];
		$visha_info['cluster']['prefix'] = $clusterinfo['prefix'];
		$visha_info['cluster']['bcast'] = $clusterinfo['bcast'];
		$visha_info['cluster']['resources'] = "visserver";

		return $visha_info;		
	}
	
	/*
	 * 说明：设置ha.cf信息
	 * 参数：
	 * 		$act_ip: 活跃节点的IP
	 * 		$act_name: 活跃节点主机名称
	 * 		$std_ip: 备用节点IP
	 * 		$std_name: 备用节点主机名称
	 * 		$bcastdev: 本机使用的网络设备
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	function SetHacfInfo($act_ip, $act_name, $std_ip, $std_name, $bcastdev)
	{
		if( !IsIpOk($act_ip) || !IsIpOk($std_ip) )
		{
			return FALSE;
		}
		
		// 使文件可写
		SetFileMode(FILE_HA_CF, "w");
		SetFileMode("/etc/hosts", "w");
		
		// 写入/etc/hosts文件
		$file = new File("/etc/hosts");
		if( !$file->Load() )
		{
			return FALSE;
		}
		$file->EditLine("^(([0-9]+\.){3}[0-9]+)\s+{$act_name}$", $act_ip . " " . $act_name);
		$file->EditLine("^(([0-9]+\.){3}[0-9]+)\s+{$std_name}$", $std_ip . " " . $std_name);
		$file->Save();
		
		// 写入ha.cf文件
		$fp = fopen(FILE_HA_CF, 'wt');
		if( $fp === FALSE )
		{
			return FALSE;
		}
		$buffer  = "debugfile /var/log/ha-debug\n";
		$buffer .= "logfile /var/log/ha-log\n";
		$buffer .= "logfacility  local4\n";
		$buffer .= "keepalive 2\n";
		$buffer .= "deadtime 30\n";
		$buffer .= "warntime 10\n";
		$buffer .= "initdead 120\n";
		$buffer .= "udpport 694\n";
		$buffer .= "bcast {$bcastdev}\n";
		$buffer .= "auto_failback on\n";
		$buffer .= "node {$act_name}\n";
		$buffer .= "node {$std_name}\n";
		$buffer .= "respawn root /usr/lib/heartbeat/ipfail\n";
		fputs($fp, $buffer);
		fflush($fp);
		fclose($fp);
		
		return TRUE;
	}
	
	/*
	 * 说明：设置haresources信息
	 * 参数：
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	function SetHaResourcesInfo($act_name, $ip, $prefix, $bcast, $resources="visserver")
	{
		if( !IsIpOk($ip) )
		{
			return FALSE;
		}
		
		$fp = fopen(FILE_HARESOURCES, 'wt');
		if( $fp === FALSE )
		{
			return FALSE;
		}

		$buffer = $act_name . " " . $ip . "/" . $prefix . "/" . $bcast . " " . $resources . "\n";
		fputs($fp, $buffer);
		fflush($fp);
		fclose($fp);
		
		return TRUE;
	}
	
	/*
	 * 说明：启用HA
	 * 参数：无
	 * 返回：成功返回TRUE,失败返回FALSE
	 */
	function EnableVisHa()
	{
		$command = "export LANG=C; /usr/bin/sudo /sbin/service heartbeat start";
		exec($command);
		
		if( $this->IsVisHaEnabled() )
		{
			$command = "export LANG=C; /usr/bin/sudo /sbin/chkconfig --add heartbeat";
			exec($command);
			$command = "export LANG=C; /usr/bin/sudo /sbin/chkconfig --level 3 heartbeat on";
			exec($command);
			
			return TRUE;
		}
		
		return FALSE;
	}
	
	/*
	 * 说明：停用HA
	 * 参数：无
	 * 返回：成功返回TRUE,失败返回FALSE
	 */
	function DisableVisHa()
	{
		$command = "export LANG=C; /usr/bin/sudo /sbin/service heartbeat stop";
		exec($command);
		
		if( ! $this->IsVisHaEnabled() )
		{
			$command = "export LANG=C; /usr/bin/sudo /sbin/chkconfig --del heartbeat";
			exec($command);

			return TRUE;
		}
		
		return FALSE;
	}
	
	
	/////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////
	// private
	
	/*
	 * 说明：获取ha.cf配置的节点信息
	 */
	private function GetHacfInfo(&$act_node, &$std_node, &$bcastdev)
	{
		$act_node = $std_node = $bcastdev = "";
		
		// 读取ha.cf文件
		$file_buffer = rfts(FILE_HA_CF);
		if( $file_buffer === FALSE )
		{
			return FALSE;
		}
		$lines = explode("\n", $file_buffer);
		
		// 删除空行及注释行
		$index = 0;
		foreach($lines as $line)
		{
			if(
				 preg_match("/^#.*$/", trim($line)) //注释行
				 ||
				 preg_match("/^\s*$/", trim($line)) // 空行
			)
			{
				array_splice($lines, $index, 1);
				//由于删除一个元素，所以当前的索引$index已指向下一个元素
				continue;
			}
			$index++;
		}
		// 获取节点信息
		$node_arr = array();
		foreach($lines as $line)
		{
			if( preg_match("/^node\s+(.*)/i", trim($line), $match) )
			{
				$node_arr[] = $match[1];
			}
			if( preg_match("/^bcast\s+([^\s]*)/i", trim($line), $match) )
			{
				$bcastdev = $match[1];
			}
		}
		
		$act_node = $node_arr[0];
		$std_node = $node_arr[1];
		
		return TRUE;
	}
	
	/*
	 * 说明：通过主机名称获取IP（必须是事先在/etc/hosts文件中配置过的）
	 */
	private function GetIpByHostname($hostname)
	{
		$file_buffer = rfts("/etc/hosts");
		if( $file_buffer === FALSE )
		{
			return FALSE;
		}
		$lines = explode("\n", $file_buffer);
		
		// 删除空行及注释行
		$index = 0;
		foreach($lines as $line)
		{
			if(
				 preg_match("/^#.*$/", trim($line)) //注释行
				 ||
				 preg_match("/^\s*$/", trim($line)) // 空行
			)
			{
				array_splice($lines, $index, 1);
				//由于删除一个元素，所以当前的索引$index已指向下一个元素
				continue;
			}
			$index++;
		}
		
		foreach($lines as $line)
		{
			if( preg_match("|^(([0-9]+\.){3}[0-9]+)\s+{$hostname}$|i", trim($line), $match) )
			{
				return $match[1];
			}
		}
		
		return FALSE;
	}
	
	/*
	 * 说明：获取haresources信息
	 */
	private function GetHaresourcesInfo($act_node_name)
	{
		$file_buffer = rfts(FILE_HARESOURCES);
		if( $file_buffer === FALSE )
		{
			return FALSE;
		}
		$lines = explode("\n", $file_buffer);
		
		// 删除空行及注释行
		$index = 0;
		foreach($lines as $line)
		{
			if(
				 preg_match("/^#.*$/", trim($line)) //注释行
				 ||
				 preg_match("/^\s*$/", trim($line)) // 空行
			)
			{
				array_splice($lines, $index, 1);
				//由于删除一个元素，所以当前的索引$index已指向下一个元素
				continue;
			}
			$index++;
		}
		
		$resourceinfo = array();
		foreach($lines as $line)
		{
			if( preg_match("|^{$act_node_name}\s+(.*)\s+visserver|i", trim($line), $match) )
			{
				$str = $match[1];
				$str_arr = explode("/", $str);
				$ip = array_shift($str_arr);
				$prefix = "";
				$dev = "";
				$broadcast = "";
				foreach($str_arr as $entry)
				{
					// prefix
					if( preg_match("/^[0-9]+$/i", trim($entry)) )
					{
						$prefix = trim($entry);
					}
					// dev
					else if( preg_match("/^([eth|bond][0-9]+)\s+/i", trim($entry), $dev_match) )
					{
						$dev = $dev_match[1];
					}
					// broadcast
					else if( preg_match("/^([0-9]+\.){3}[0-9]+$/i", trim($entry)) )
					{
						$broadcast = trim($entry);
					}
				}
				$resourceinfo = array(
					"ipaddr"=>$ip,
					"prefix"=>$prefix,
					"dev"=>$dev,
					"bcast"=>$broadcast
				);
				return $resourceinfo;
			}
		}
		
		return FALSE;
	}
}

?>