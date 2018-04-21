<?php
/*
 *说明：
 *	系统环境：RedHat Enterprise 5
 *
 *	类名称：Cpu
 *	作用：获取CPU的物理颗数、核数、cache大小、bogomips大小、名称、频率。
 *
 *	created by 王大典, 2009-11-02 12:45
 */

define('FILE_CPUINFO', "/proc/cpuinfo");

Class Cpu
{
////////////////////////
// 变量
////////////////////////
	// cpu颗数
	private $cpuCount;
	
	// 多颗cpu信息列表，列表数目为$cpuCount个
	private $cpu_list=array(
	/*
		array(
		 	"id"=>0,
			"cores"=>array(
				array(
					"id"=>0,         // id,和上边的id意义相同
					"name"=>"",		 // 名称
					"processor"=>0,  // 处理器号码
					"speed"=>"0GHZ", // 频率
					"cache"=>"0KB",	 // cache大小
					"bogomips"=>0, 	 // bogomips大小（伪测速）
				),
				...
			),
			...
		),
		...
	*/
	);

	// 获取cpu信息是否成功，即调用函数GetCpuInfo()的结果返回
	private $bGetCpuInfoOk = FALSE;
	
////////////////////////
// public接口
////////////////////////
	// 构造函数
	function __construct()
	{
		$this->bGetCpuInfoOk = $this->GetCpuInfo();
	}

	// 获取cpu颗数
	function GetCpuCount()
	{
		if( ! $this->bGetCpuInfoOk )
			return FALSE;
		
		return $this->cpuCount;
	}
	
	// 获取cpu列表
	function GetCpuList()
	{
		if( ! $this->bGetCpuInfoOk )
			return FALSE;
		
		return $this->cpu_list;
	}
	
	// 获取cpu的ID
	function GetCpuID($cpuid)
	{
		if ( ! $this->isCpuIdOk($cpuid) || ! $this->bGetCpuInfoOk )
			return FALSE;
		
		return $cpuid;
	}
	// 获取cpu的名称
	function GetCpuName($cpuid)
	{
		if ( ! $this->isCpuIdOk($cpuid) || ! $this->bGetCpuInfoOk )
			return FALSE;
		 return $this->cpu_list[$cpuid]['cores'][0]['name'];
	}
	// 获取cpu的核数。根据ID，后同
	function GetCpuCoreCount($cpuid)
	{
		if ( ! $this->isCpuIdOk($cpuid) || ! $this->bGetCpuInfoOk )
			return FALSE;
		
		return count($this->cpu_list[$cpuid]['cores']);
	}
	// 获取cpu速度
	function GetCpuSpeed($cpuid)
	{
		if ( ! $this->isCpuIdOk($cpuid) || ! $this->bGetCpuInfoOk )
			return FALSE;
		return $this->cpu_list[$cpuid]['cores'][0]['speed'];
	}
	// 获取cpu的cache大小
	function GetCpuCache($cpuid)
	{
		if ( ! $this->isCpuIdOk($cpuid) || ! $this->bGetCpuInfoOk )
			return FALSE;
		return $this->cpu_list[$cpuid]['cores'][0]['cache'];
	}
	// 获取cpu的bogomips大小
	function GetCpuBogomips($cpuid)
	{
		if ( ! $this->isCpuIdOk($cpuid) || ! $this->bGetCpuInfoOk )
			return FALSE;
		$bogomips = 0;
		foreach( $this->cpu_list[$cpuid]['cores'] as $core )
		{
			$bogomips += $core['bogomips'];
		}
		return $bogomips;
	}

////////////////////////
// private接口
////////////////////////
	//获取cpu所有信息
	private function GetCpuInfo()
	{
		$file_buffer = rfts( FILE_CPUINFO );
	
		if ( $file_buffer != FALSE )
		{
			$lines = explode("\n", $file_buffer);
			$id = array();
			$name = array();
			$processor = array();
			$speed = array();
			$cache =array();
			$bogomips = array();
			
			$ar_buf = array();
			
			foreach( $lines as $line )
			{
				$arrBuff = preg_split('/\s+:\s+/', trim($line));
				if( count( $arrBuff ) == 2 )
				{
					$key = $arrBuff[0];
					$value = $arrBuff[1];
					switch ($key)
					{
						case 'physical id':
							$id[] = $value;
							break;
						case 'processor':
							$processor[] = $value;
							break;
						case 'model name':
							$name[] = $value;
							break;
						case 'cpu MHz':
							$speed[] = format_speed(1000 * sprintf('%.2f', $value));
							break;
						case 'cache size':
							$cache[] = format_bytesize($value*1024);
							break;
						case 'bogomips':
							$bogomips[] = $value;
							break;
		 			}//switch ($key)
				}//if( count( $arrBuff ) == 2 )
			}//foreach( $lines as $line )
			
			$cores_count = count($processor);
			$core = array();
			for($i=0; $i<$cores_count; $i++)
			{
				// id
				if( isset($id[$i]) )
					$core['id']=$id[$i];
				else
					$core['id']=$i;
				// name
				if( isset($name[$i]) )
					$core['name']=$name[$i];
				else
					$core['name']="N.A.";
				// processor
				//if( isset($processor[$i]) )
					$core['processor']=$processor[$i];
				//else
				// $core['processor']=0;
				
				// speed
				if ( isset($speed[$i]) )
					$core['speed']=$speed[$i];
				else
					$core['speed']=0;
				// cache
				if ( isset($cache[$i]) )
					$core['cache']=$cache[$i];
				else
					$core['cache']=0;
				// bogomips
				if ( isset($bogomips[$i]) )
					$core['bogomips']=$bogomips[$i];
				else
					$core['bogomips']=0;
				
				// 列入类对象数组
				$this->cpu_list[ $core['id'] ]['id'] = $core['id'];
				$this->cpu_list[ $core['id'] ]['cores'][] = $core;
				$core = array();
			}
			
		}//if ( $file_buffer != FALSE )
		else
		{
			return FALSE;
		}
		$this->cpuCount = count($this->cpu_list);

		return TRUE;
	}
	
	// 判断cpuid的合法性
	private function isCpuIdOk($cpuid)
	{
		/*if( is_int($cpuid) == TRUE )
		{
			if( $cpuid < 0 || $cpuid >= $this->cpuCount )
				return FALSE;
		}
		else
		{
			return FALSE;
		}*/
		return TRUE;
	}

} 

?>