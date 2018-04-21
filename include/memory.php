<?php
/*
 *说明：
 *	系统环境：RedHat Enterprise 5
 *
 *	类名称：Memory
 *	作用：获取物理内存、虚拟内存（交换区）的总大小、使用大小信息(单位：KB)。
 *
 *	created by 王大典, 2009-10-31 16:30
 */

define('FILE_MEMINFO', "/proc/meminfo");
define('CMD_FREE',"export LANG=C; /usr/bin/free ");

Class Memory
{
	// 物理内存
	private $physical_total;
	private $physical_used;
	private $physical_free;
	// 交换区
	private $swap_total;
	private $swap_used;
	private $swap_free;
	
	// 构造函数
	function Memory()
	{
	}
	
	//获取memory信息
	private function ObtainMemoryInfo()
	{
		exec(CMD_FREE, $output, $retval);
		/* $output:
			             total       used       free     shared    buffers     cached
			Mem:       3368400     381432    2986968          0      77176     217768
			-/+ buffers/cache:      86488    3281912
			Swap:      5365700          0    5365700
			
			单位均为KB
		*/
		$match = array("","","","","","","");
		foreach($output as $line)
		{
			$line = trim($line);
			//物理内存
			if( preg_match("/Mem:/", $line) )
			{
				preg_match_all("/[0-9]+/", $line, $match);
				if(count($match) > 0)
				{
					$this->physical_total = $match[0][0];
					$this->physical_used  = $match[0][1];
					$this->physical_free  = $match[0][2];					
				}
			}
			// 交换分区
			else if(preg_match("/Swap:/", $line))
			{
				preg_match_all("/[0-9]+/", $line, $match);
				if(count($match) > 0)
				{
					$this->swap_total = $match[0][0];
					$this->swap_used  = $match[0][1];
					$this->swap_free  = $match[0][2];					
				}
			}
			else
			{
				// nothing
			}
		}
		
	}
	
	// 物理内存：总大小、剩余大小、使用大小
	function GetTotalPhysicalMemory()
	{
		$this->ObtainMemoryInfo();
		return $this->physical_total;
	}
	
	function GetFreePhysicalMemory()
	{
		$this->ObtainMemoryInfo();
		return $this->physical_free;
	}
	
	function GetUsedPhysicalMemory()
	{
		$this->ObtainMemoryInfo();
		return $this->physical_used;
	}
	
	// 虚拟内存（交换区）：总大小、剩余大小、使用大小
	function GetTotalSwap()
	{
		$this->ObtainMemoryInfo();
		return $this->swap_total;
	}
	
	function GetFreeSwap()
	{
		$this->ObtainMemoryInfo();
		return $this->swap_free;
	}

	function GetUsedSwap()
	{
		$this->ObtainMemoryInfo();
		return $this->swap_used;
	}
}
?>