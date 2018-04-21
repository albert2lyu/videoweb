<?php

/*
 * 说明：获取CPU使用率、网卡流速、本地磁盘读写速率
 * 
 * 
 * created by 王大典, 2009-11-13
 */

require_once("cpu.php");
require_once("disk.php");
require_once("network.php");

define('FILE_STAT', "/proc/stat");
define('FILE_NET_DEV', "/proc/net/dev");
define('FILE_DISKSTATS', "/proc/diskstats");

class Usage
{
	private $usage_stat = array(
	/*
		"cpu"=>array(
			array(
				"id"=>0, //id: physical id（一颗cpu，不针对核）
				"usage"=>"90%"
			),
			...
		),
		"net"=>array(
			array(
				"device"=>"eth0",
				"receive"=>"10MB/s",
				"transmit"=>"1MB/s"
			),
			...
		),
		"disk"=>array(
			array(
				"name"=>"磁盘0",
				"device"=>"/dev/sda",
				"size"=>"1024 GB",
				"read"=>"10MB/s",
				"write"=>"20MB/s",
				"free"=>"100G",
				"fs"=>"xfs",
				"usage"=>"10%"
			),
			...
		)
	*/
	);
	private $cpu_list = array();
	private $disk_list = array();
	private $net_dev_list = array();
	
	function __construct()
	{
		$network = new NetWork();
		$this->net_dev_list = $network->ListNICs();
		$cpu = new Cpu();
		$this->cpu_list = $cpu->GetCpuList();
		$this->disk_list = GetDiskList();
	}
	
	/*
	 * 说明：获取使用率
	 * 参数：无
	 * 返回：成功返回$usage_stat结构的数组，失败返回FALSE
	 */
	function GetUsageStat()
	{
		$this->usage_stat = array();
		
		if($this->ComputeUsageStat() === FALSE)
		{
			return FALSE;
		}
		
		return $this->usage_stat;
	}
	
	/*
	 * 说明：获取网卡的接收速率
	 */
	function GetNetDevReceive($nic)
	{
		foreach($this->usage_stat['net'] as $entry)
		{
			if($entry['device'] == $nic)
			{
				return $entry['receive'];
			}
		}
		
		return FALSE;
	}
	
	/*
	 * 说明：获取网卡的发送速率
	 */
	function GetNetDevTransmit($nic)
	{
		foreach($this->usage_stat['net'] as $entry)
		{
			if($entry['device'] == $nic)
			{
				return $entry['transmit'];
			}
		}
		
		return FALSE;
	}
	/*
	 * 说明：获取磁盘个数
	 */
	function GetDiskCount()
	{
		return count($this->usage_stat['disk']);
	}
	
	/*
	 * 说明：获取磁盘的大小
	 */
	function GetDiskSize($disk)
	{
		foreach($this->usage_stat['disk'] as $entry)
		{
			if($entry['device'] == $disk)
			{
				return $entry['size'];
			}
		}
		return FALSE;
	}
	
	/*
	 * 说明：获取磁盘的读速率
	 */
	function GetDiskRead($disk)
	{
		foreach($this->usage_stat['disk'] as $entry)
		{
			if($entry['device'] == $disk)
			{
				return $entry['read'];
			}
		}
		return FALSE;
	}

	/*
	 * 说明：获取磁盘的写速率
	 */
	function GetDiskWrite($disk)
	{
		foreach($this->usage_stat['disk'] as $entry)
		{
			if($entry['device'] == $disk)
			{
				return $entry['write'];
			}
		}
		return FALSE;
	}
	
	/*
	 * 说明：获取cpu的使用率
	 */
	function GetCpuUsage($cpuid)
	{
		foreach($this->usage_stat['cpu'] as $entry)
		{
			if($entry['id'] == $cpuid)
			{
				return $entry['usage'];
			}
		}
		return FALSE;
	}
	//////////////////////////////////////////////////////////////
	// private
	
	private function ComputeUsageStat()
	{
		$sleep_sec = 2;// 休眠时间间隔
		$nic_stat_pre_arr = array();
		$nic_stat_now_arr = array();
		$disk_stat_pre_arr = array();
		$disk_stat_now_arr = array();
		$cpu_stat_pre_arr = array();
		$cpu_stat_pre_arr = array();
		
		// nic
		foreach($this->net_dev_list as $entry)
		{
			$stat = $this->GetNetDevStat($entry);
			if($stat !== FALSE)
			{
				$nic_stat_pre_arr[] = $stat;
			}
			else
			{
				return FALSE;
			}
		}
		// disk
		foreach($this->disk_list as $entry)
		{
			$stat = $this->GetDiskStat($entry['device']);
			if($stat !== FALSE)
			{
				$disk_stat_pre_arr[] = $stat;
			}
			else
			{
				return FALSE;
			}
		}
		// cpu
		foreach($this->cpu_list as $entry)
		{
			$core_list = array();
			foreach($entry['cores'] as $core)
			{
				$core_list[] = 'cpu' . $core['processor'];
			}
			$stat = $this->GetCpuStat($core_list);
			if($stat !== FALSE)
			{
				$cpu_stat_pre_arr[] = $stat;
			}
			else
			{
				return FALSE;
			}
		}
		
		
		// 休眠间隔
		sleep($sleep_sec);
		

		// nic
		foreach($this->net_dev_list as $entry)
		{
			$stat = $this->GetNetDevStat($entry);
			if($stat !== FALSE)
			{
				$nic_stat_now_arr[] = $stat;
			}
			else
			{
				return FALSE;
			}
		}
		// disk
		foreach($this->disk_list as $entry)
		{
			$stat = $this->GetDiskStat($entry['device']);
			if($stat !== FALSE)
			{
				$disk_stat_now_arr[] = $stat;
			}
			else
			{
				return FALSE;
			}
		}
		// cpu
		foreach($this->cpu_list as $entry)
		{
			$core_list = array();
			foreach($entry['cores'] as $core)
			{
				$core_list[] = 'cpu' . $core['processor'];
			}
			$stat = $this->GetCpuStat($core_list);
			if($stat !== FALSE)
			{
				$cpu_stat_now_arr[] = $stat;
			}
			else
			{
				return FALSE;
			}
		}
		
		// 计算使用率
		// nic
		$index = 0;
		$iLoop = count($nic_stat_pre_arr);
		for($i=0; $i<$iLoop; $i++)
		{
			$entry = array();
			$entry['device'] = $this->net_dev_list[$i];
			$receive = (($nic_stat_now_arr[$i]['receive'] - $nic_stat_pre_arr[$i]['receive'])
								/ 1024 / 1024 ) / $sleep_sec; // Mb/s
			$transmit = (($nic_stat_now_arr[$i]['transmit'] - $nic_stat_pre_arr[$i]['transmit'])
								/ 1024 / 1024 ) / $sleep_sec; // Mb/s
			$entry['receive'] = sprintf("%.2f MB/s", $receive);
			$entry['transmit'] = sprintf("%.2f MB/s", $transmit);
			$this->usage_stat['net'][] = $entry;
		}
		
		// disk
		$index = 0;
		$iLoop = count($disk_stat_pre_arr);
		for($i=0; $i<$iLoop; $i++)
		{
			$entry = array();
			$entry['device'] = $this->disk_list[$i]['device'];
			$read = (($disk_stat_now_arr[$i]['read'] - $disk_stat_pre_arr[$i]['read']) 
							/ 1024 / 1024 ) / $sleep_sec; // Mb/s
			$write = (($disk_stat_now_arr[$i]['write'] - $disk_stat_pre_arr[$i]['write'])
			 				/ 1024 / 1024) / $sleep_sec; // Mb/s
			$entry['read'] = sprintf("%.2f MB/s", $read);
			$entry['write'] = sprintf("%.2f MB/s", $write);
			$entry['size'] = $this->disk_list[$i]['size'];
			$entry['name'] = $this->disk_list[$i]['name'];
			// 获取磁盘使用、剩余、使用率
			$lv_diskinfo = GetDiskInfo($this->disk_list[$i]['path']);
			$entry['free']  = $lv_diskinfo['free'];
			$entry['usage'] = $lv_diskinfo['usage'];
			$entry['fs']    = $lv_diskinfo['fs'];
			 
			$this->usage_stat['disk'][] = $entry;
		}
		
		// cpu
		$index = 0;
		$iLoop = count($cpu_stat_pre_arr);
		for($i=0; $i<$iLoop; $i++)
		{
			$entry = array();
			$entry['id'] = $this->cpu_list[$i]['id'];
			$used = $cpu_stat_now_arr[$i]['used'] - $cpu_stat_pre_arr[$i]['used'];
			$total = $cpu_stat_now_arr[$i]['total'] - $cpu_stat_pre_arr[$i]['total'];
			$usage =  ($total <= 0) ? "100%" : (sprintf("%d%%", $used * 100 / $total));
			$entry['usage'] = $usage;
			
			$this->usage_stat['cpu'][] = $entry;
		}
		
		return TRUE;
	}
	
	private function GetNetDevStat($nic)
	{
		$nic_stat=array(
		/*
			"receive"=>20,//Byte
			"transmit"=>20
		*/
		);
		$file_buffer = rfts(FILE_NET_DEV);
		if( $file_buffer != FALSE )
		{
			$lines = explode("\n", $file_buffer);
			foreach($lines as $line)
			{
				if( preg_match("/{$nic}/i", $line) )
				{
					
					if( preg_match_all("/([0-9]+)\s+/", trim($line), $match) )
					{
						$nic_stat['receive'] = $match[1][0];
						$nic_stat['transmit'] = $match[1][8];
					}
					break;
				}				
			}//foreach($lines as $line)
		}//if( $file_buffer != FALSE )
		else
		{
			return FALSE;
		}
		
		return $nic_stat;
	}
	
	private function GetDiskStat($disk)
	{
		$disk_stat=array(
		/*
			"read"=>20,//Byte
			"write"=>20
		*/
		);
		$file_buffer = rfts(FILE_DISKSTATS);
		if( $file_buffer != FALSE )
		{
			$lines = explode("\n", $file_buffer);
			foreach($lines as $line)
			{
				if( preg_match("/\s{$disk}\s/i", $line) )
				{
					$arr = preg_split("/\s+/", trim($line));
					$disk_stat['read'] = $arr[5] * 512;
					$disk_stat['write'] = $arr[9] * 512;
					break;
				}				
			}//foreach($lines as $line)
		}//if( $file_buffer != FALSE )
		else
		{
			return FALSE;
		}
		
		return $disk_stat;
	}
	
	private function GetCpuStat($core_list)
	{
		if ( !is_array($core_list) )
			return FALSE;
		
		$cpu_stat = array(
			/*
			 "used"=>10,
			 "total"=>100
			 */
		);
		
		// 保存状态信息的数组
		$cpuStat = array(
			 "user"=>0, 
			 "nice"=>0,
			 "system"=>0,
			 "idle"=>0,
			 "iowait"=>0,
			 "irq"=>0,
			 "softirq"=>0, 
		);
		
		$file_buffer = rfts(FILE_STAT);
		if( $file_buffer != FALSE )
		{
			$lines = explode("\n", $file_buffer);
			foreach($lines as $line)
			{
				foreach($core_list as $core)
				{
					if( preg_match("/{$core}/", $line) )
					{
						if( preg_match_all("/\s+([0-9]+)/", trim($line), $match) )
						{
							$cpuStat['user']    += 	$match[1][0];
							$cpuStat['nice']    += 	$match[1][1];
							$cpuStat['system']  += 	$match[1][2];
							$cpuStat['idle']    += 	$match[1][3];
							$cpuStat['iowait']  += 	$match[1][4];
							$cpuStat['irq']     += 	$match[1][5];
							$cpuStat['softirq'] += 	$match[1][6];
						}
						break;
					}
					else
						continue;
				}
				
			}//foreach($lines as $line)
		}//if( $file_buffer != FALSE )
		else
		{
			return FALSE;
		}

		$cpu_stat['used'] = $cpuStat['user'] + $cpuStat['system'];
		$cpu_stat['total'] = $cpuStat['user'] + $cpuStat['system'] + $cpuStat['idle'] + $cpuStat['nice'];
		
		return $cpu_stat;
	}
	
}

?>