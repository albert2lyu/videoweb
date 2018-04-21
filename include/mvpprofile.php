<?php

/*
 * 说明：修改mvpServer.ini文件，对mvp进行配置
 * 
 * 
 */

require_once("function.php");

define('FILE_MVPSERVER_INI', "/opt/MVP64/Properties.cfg");
define('FILE_STARTMVP', "/opt/MVP64/start");
define('CMD_STARTMVP', "export LANG=C; /usr/bin/sudo /opt/MVP64/start ");
define('CMD_PIDOF', "export LANG=C; /usr/bin/sudo /sbin/pidof ");
define('NAME_MVPSERVER', "mvp");

/*
 * 说明：获取MVP服务器模式
 * 参数：$mode：模式值
 * 	#设置服务器模式,位与方式
	#SVR_MODE_STORAGE = 1,			//存储服务器
	#SVR_MODE_VOD = 2,				//点播服务器
	#SVR_MODE_TRANSMIT = 4,			//转发服务器
	#SVR_MODE_DOWNLOAD = 8,			//下载服务器
	#SVR_MODE_MANAGER = 128,		//管理服务器
 * 返回：含有五个元素的数组，每个元素的值为0或1，表示是否为此服务器模式（顺序如上），
 * 		 失败返回FALSE
 * array("storage"=>1, 
		 "vod"=>1, 
		 "transmit"=>1, 
		 "download"=>1, 
		 "manager"=>1
		);
 */
function GetMVPServerMode($mode)
{
	$server_mode = $mode & 0x8F; // 1000 1111
	$server_array = array();
	$storage = 0;
	$vod = 0;
	$transmit = 0;
	$download = 0;
	$manager = 0;

	if($server_mode >= 128)
	{
		$manager = 1;
		$server_mode -= 128;
	}
	if($server_mode >= 8)
	{
		$download = 1;
		$server_mode -= 8;
	}
	if($server_mode >= 4)
	{
		$transmit = 1;
		$server_mode -= 4;
	}
	if($server_mode >= 2)
	{
		$vod = 1;
		$server_mode -= 2;
	}
	if($server_mode == 1)
	{
		$storage = 1;
	}
	
	$server_array =  array(  "storage"=>$storage, 
							 "vod"=>$vod, 
							 "transmit"=>$transmit, 
							 "download"=>$download, 
							 "manager"=>$manager
							);

	return $server_array;
}
/*
 * 说明：NVR Service是否启动
 * 参数：无
 * 返回：启动返回TRUE，否则返回FALSE
 */
function IsMVPRunning()
{
	exec(CMD_PIDOF . NAME_MVPSERVER, $output, $retval);
	if($retval == 0)
	{
		return TRUE;
	}
	else
	{
		return FALSE;
	}
}

/*
 * 说明：启动NVR Service
 * 参数：无
 * 返回：成功返回TRUE，否则返回FALSE
 */
function StartMVPServer()
{
	if( !is_executable(FILE_STARTMVP) )
	{
		return FALSE;
	}
	if( IsMVPRunning() === TRUE )
	{
		return TRUE;
	}
	
	exec(CMD_STARTMVP . " start");
	return TRUE;
}

/*
 * 说明：启动NVR Service
 * 参数：无
 * 返回：成功返回TRUE，否则返回FALSE
 */
function StopMVPServer()
{
	if( !is_executable(FILE_STARTMVP) )
		return FALSE;
	if( ! IsMVPRunning() )
		return TRUE;

	exec(CMD_STARTMVP . " stop");
	return TRUE;
}

/*
 * 说明：重启NVR Service
 * 参数：无
 * 返回：成功返回TRUE，否则返回FALSE
 */
function RestartMVPServer()
{
	if( !is_executable(FILE_STARTMVP) )
		return FALSE;
	
	$bRunning = IsMVPRunning();
	if( $bRunning == TRUE )
	{
		exec(CMD_STARTMVP . " stop");
		exec(CMD_STARTMVP . " start");
	}
	else
	{
		exec(CMD_STARTMVP . " start");
	}
	
	return TRUE;
}

class MVPProfile
{
	// 配置文件的所有行
	private $all_lines = array(); 
	// 配置文件的非空行、非注释行
	private $lines = array(
	/*	行号=>内容
		1=>"1111==2222",
		...
	*/
	);	  
	
	function __construct()
	{
		if( ! is_writable(FILE_MVPSERVER_INI) )
		{
			SetFileMode(FILE_MVPSERVER_INI, 'w');
		}
		$this->ReadMVPProfile();
	}
	
	/*
	 * 说明：获取某字段的值
	 * 参数：$field：字段名称
	 * 返回：成功返回相应的字段值，失败返回FALSE（使用===判断FALSE）
	 */
	function GetFieldValue($field)
	{
		foreach($this->lines as $line_no=>$line_str)
		{
			if(preg_match("/{$field}\s*=\s*/", $line_str))
			{
				if( preg_match("/{$field}\s*=\s*([^\n]*)/", $line_str, $match) )
				{
					if( isset($match[1]))
					{
						$value = $match[1];
						return $value;
					}
				}
			}
		}
		
		return FALSE;
	}
	
	/*
	 * 说明：设置某字段的值
	 * 参数：$field：字段名称
	 * 		 $value：字段值
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	function SetFieldValue($field, $value)
	{
		foreach($this->lines as $line_no=>$line_str)
		{
			if(preg_match("/{$field}\s*=\s*/i", $line_str))
			{
				$str = preg_replace("/({$field}\s*=\s*)([^\n]*)/i", "\${1}{$value}", $line_str);
				$this->all_lines[$line_no] = $str;
				
				return TRUE;
			}
		}
		
		return FALSE;
	}
	
	/*
	 * 说明：保存修改后的mvp配置文件,修改字段值后必须调用此接口才能保存修改
	 * 参数：无
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	function Save()
	{
		$fp = fopen(FILE_MVPSERVER_INI, 'wt');
		if($fp === FALSE)
			return FALSE;
		
		foreach($this->all_lines as $line)
		{
			$line .= "\n";
			//$line .= chr(10);
			fwrite($fp, $line);
		}
		fflush($fp);
		fclose($fp);
	}
	
	////////////////////////////////////////////////
	// private
	
	private function ReadMVPProfile()
	{
		$file_buffer = array();
		$file_buffer = rfts(FILE_MVPSERVER_INI);
		if( $file_buffer === FALSE )
		{
			return FALSE;
		}
		
		$this->all_lines = explode("\n", $file_buffer);
		// 剔除空行及注释行
		$index = 0;
		foreach($this->all_lines as $line)
		{
			if(
				 preg_match("/^#.*$/", trim($line)) //注释行
				 ||
				 preg_match("/^\s*$/", trim($line)) // 空行
			  )
			{
				$index++;
				continue;
			}
			$this->lines[$index] = & $this->all_lines[$index];
			$index++;
		}
	}
}

?>