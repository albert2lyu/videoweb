<?php
/*
 *˵����
 *	ϵͳ������RedHat Enterprise 5
 *
 *	�����ƣ�Cpu
 *	���ã���ȡCPU�����������������cache��С��bogomips��С�����ơ�Ƶ�ʡ�
 *
 *	created by �����, 2009-11-02 12:45
 */

define('FILE_CPUINFO', "/proc/cpuinfo");

Class Cpu
{
////////////////////////
// ����
////////////////////////
	// cpu����
	private $cpuCount;
	
	// ���cpu��Ϣ�б��б���ĿΪ$cpuCount��
	private $cpu_list=array(
	/*
		array(
		 	"id"=>0,
			"cores"=>array(
				array(
					"id"=>0,         // id,���ϱߵ�id������ͬ
					"name"=>"",		 // ����
					"processor"=>0,  // ����������
					"speed"=>"0GHZ", // Ƶ��
					"cache"=>"0KB",	 // cache��С
					"bogomips"=>0, 	 // bogomips��С��α���٣�
				),
				...
			),
			...
		),
		...
	*/
	);

	// ��ȡcpu��Ϣ�Ƿ�ɹ��������ú���GetCpuInfo()�Ľ������
	private $bGetCpuInfoOk = FALSE;
	
////////////////////////
// public�ӿ�
////////////////////////
	// ���캯��
	function __construct()
	{
		$this->bGetCpuInfoOk = $this->GetCpuInfo();
	}

	// ��ȡcpu����
	function GetCpuCount()
	{
		if( ! $this->bGetCpuInfoOk )
			return FALSE;
		
		return $this->cpuCount;
	}
	
	// ��ȡcpu�б�
	function GetCpuList()
	{
		if( ! $this->bGetCpuInfoOk )
			return FALSE;
		
		return $this->cpu_list;
	}
	
	// ��ȡcpu��ID
	function GetCpuID($cpuid)
	{
		if ( ! $this->isCpuIdOk($cpuid) || ! $this->bGetCpuInfoOk )
			return FALSE;
		
		return $cpuid;
	}
	// ��ȡcpu������
	function GetCpuName($cpuid)
	{
		if ( ! $this->isCpuIdOk($cpuid) || ! $this->bGetCpuInfoOk )
			return FALSE;
		 return $this->cpu_list[$cpuid]['cores'][0]['name'];
	}
	// ��ȡcpu�ĺ���������ID����ͬ
	function GetCpuCoreCount($cpuid)
	{
		if ( ! $this->isCpuIdOk($cpuid) || ! $this->bGetCpuInfoOk )
			return FALSE;
		
		return count($this->cpu_list[$cpuid]['cores']);
	}
	// ��ȡcpu�ٶ�
	function GetCpuSpeed($cpuid)
	{
		if ( ! $this->isCpuIdOk($cpuid) || ! $this->bGetCpuInfoOk )
			return FALSE;
		return $this->cpu_list[$cpuid]['cores'][0]['speed'];
	}
	// ��ȡcpu��cache��С
	function GetCpuCache($cpuid)
	{
		if ( ! $this->isCpuIdOk($cpuid) || ! $this->bGetCpuInfoOk )
			return FALSE;
		return $this->cpu_list[$cpuid]['cores'][0]['cache'];
	}
	// ��ȡcpu��bogomips��С
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
// private�ӿ�
////////////////////////
	//��ȡcpu������Ϣ
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
				
				// �������������
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
	
	// �ж�cpuid�ĺϷ���
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