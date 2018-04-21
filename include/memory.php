<?php
/*
 *˵����
 *	ϵͳ������RedHat Enterprise 5
 *
 *	�����ƣ�Memory
 *	���ã���ȡ�����ڴ桢�����ڴ棨�����������ܴ�С��ʹ�ô�С��Ϣ(��λ��KB)��
 *
 *	created by �����, 2009-10-31 16:30
 */

define('FILE_MEMINFO', "/proc/meminfo");
define('CMD_FREE',"export LANG=C; /usr/bin/free ");

Class Memory
{
	// �����ڴ�
	private $physical_total;
	private $physical_used;
	private $physical_free;
	// ������
	private $swap_total;
	private $swap_used;
	private $swap_free;
	
	// ���캯��
	function Memory()
	{
	}
	
	//��ȡmemory��Ϣ
	private function ObtainMemoryInfo()
	{
		exec(CMD_FREE, $output, $retval);
		/* $output:
			             total       used       free     shared    buffers     cached
			Mem:       3368400     381432    2986968          0      77176     217768
			-/+ buffers/cache:      86488    3281912
			Swap:      5365700          0    5365700
			
			��λ��ΪKB
		*/
		$match = array("","","","","","","");
		foreach($output as $line)
		{
			$line = trim($line);
			//�����ڴ�
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
			// ��������
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
	
	// �����ڴ棺�ܴ�С��ʣ���С��ʹ�ô�С
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
	
	// �����ڴ棨�����������ܴ�С��ʣ���С��ʹ�ô�С
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