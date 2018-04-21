<?php
require_once("function.php");
require_once("network.php");
require_once("file.php");

/*
 * ˵����
 * 		1�����á���ȡha��Ϣ����֧��˫���ȱ�
 * 		2���ж�ha�Ƿ�������
 * 		3��������ֹͣheartbeat����
 * 
 * careated by �����, 2009-12-22
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
	 * ˵�����ж�HA�Ƿ�������
	 * ��������
	 * ���أ������÷���TRUE�����򷵻�FALSE
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
	 * ˵������ȡVISHA��Ϣ
	 * ��������
	 * ���أ���ȡ�ɹ��򷵻���Ϣ�б����򷵻�FALSE
	 		��Ϣ�б�
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
		
		// ��ȡ�ڵ���Ϣ
		$act_node_name = "";
		$std_node_name = "";
		$bcast_dev = "";
		$this->GetHacfInfo($act_node_name, $std_node_name, $bcast_dev);
		$visha_info['active']['hostname'] = $act_node_name;
		$visha_info['standby']['hostname'] = $std_node_name;
		
		// ��ȡ�ڵ�IP
		$act_node_ip = $this->GetIpByHostname($act_node_name);
		$std_node_ip = $this->GetIpByHostname($std_node_name);
		$visha_info['active']['ipaddr'] = $act_node_ip;
		$visha_info['standby']['ipaddr'] = $std_node_ip;
		
		// �жϱ�����active����standby
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
		
		// ��ȡ��Ⱥ��Ϣ
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
	 * ˵��������ha.cf��Ϣ
	 * ������
	 * 		$act_ip: ��Ծ�ڵ��IP
	 * 		$act_name: ��Ծ�ڵ���������
	 * 		$std_ip: ���ýڵ�IP
	 * 		$std_name: ���ýڵ���������
	 * 		$bcastdev: ����ʹ�õ������豸
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
	 */
	function SetHacfInfo($act_ip, $act_name, $std_ip, $std_name, $bcastdev)
	{
		if( !IsIpOk($act_ip) || !IsIpOk($std_ip) )
		{
			return FALSE;
		}
		
		// ʹ�ļ���д
		SetFileMode(FILE_HA_CF, "w");
		SetFileMode("/etc/hosts", "w");
		
		// д��/etc/hosts�ļ�
		$file = new File("/etc/hosts");
		if( !$file->Load() )
		{
			return FALSE;
		}
		$file->EditLine("^(([0-9]+\.){3}[0-9]+)\s+{$act_name}$", $act_ip . " " . $act_name);
		$file->EditLine("^(([0-9]+\.){3}[0-9]+)\s+{$std_name}$", $std_ip . " " . $std_name);
		$file->Save();
		
		// д��ha.cf�ļ�
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
	 * ˵��������haresources��Ϣ
	 * ������
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
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
	 * ˵��������HA
	 * ��������
	 * ���أ��ɹ�����TRUE,ʧ�ܷ���FALSE
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
	 * ˵����ͣ��HA
	 * ��������
	 * ���أ��ɹ�����TRUE,ʧ�ܷ���FALSE
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
	 * ˵������ȡha.cf���õĽڵ���Ϣ
	 */
	private function GetHacfInfo(&$act_node, &$std_node, &$bcastdev)
	{
		$act_node = $std_node = $bcastdev = "";
		
		// ��ȡha.cf�ļ�
		$file_buffer = rfts(FILE_HA_CF);
		if( $file_buffer === FALSE )
		{
			return FALSE;
		}
		$lines = explode("\n", $file_buffer);
		
		// ɾ�����м�ע����
		$index = 0;
		foreach($lines as $line)
		{
			if(
				 preg_match("/^#.*$/", trim($line)) //ע����
				 ||
				 preg_match("/^\s*$/", trim($line)) // ����
			)
			{
				array_splice($lines, $index, 1);
				//����ɾ��һ��Ԫ�أ����Ե�ǰ������$index��ָ����һ��Ԫ��
				continue;
			}
			$index++;
		}
		// ��ȡ�ڵ���Ϣ
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
	 * ˵����ͨ���������ƻ�ȡIP��������������/etc/hosts�ļ������ù��ģ�
	 */
	private function GetIpByHostname($hostname)
	{
		$file_buffer = rfts("/etc/hosts");
		if( $file_buffer === FALSE )
		{
			return FALSE;
		}
		$lines = explode("\n", $file_buffer);
		
		// ɾ�����м�ע����
		$index = 0;
		foreach($lines as $line)
		{
			if(
				 preg_match("/^#.*$/", trim($line)) //ע����
				 ||
				 preg_match("/^\s*$/", trim($line)) // ����
			)
			{
				array_splice($lines, $index, 1);
				//����ɾ��һ��Ԫ�أ����Ե�ǰ������$index��ָ����һ��Ԫ��
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
	 * ˵������ȡharesources��Ϣ
	 */
	private function GetHaresourcesInfo($act_node_name)
	{
		$file_buffer = rfts(FILE_HARESOURCES);
		if( $file_buffer === FALSE )
		{
			return FALSE;
		}
		$lines = explode("\n", $file_buffer);
		
		// ɾ�����м�ע����
		$index = 0;
		foreach($lines as $line)
		{
			if(
				 preg_match("/^#.*$/", trim($line)) //ע����
				 ||
				 preg_match("/^\s*$/", trim($line)) // ����
			)
			{
				array_splice($lines, $index, 1);
				//����ɾ��һ��Ԫ�أ����Ե�ǰ������$index��ָ����һ��Ԫ��
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