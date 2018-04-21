<?php

/*
 * 说明：修改visServer.ini文件，对vis进行配置
 * 
 * created by 王大典，2009-11-24
 */

require_once("function.php");

define('FILE_VISSERVER_INI', "/opt/library/visServer.ini");
define('FILE_STARTVIS', "/opt/startvis");
define('CMD_STARTVIS', "export LANG=C; /usr/bin/sudo /opt/startvis ");
define('CMD_PIDOF', "export LANG=C; /usr/bin/sudo /sbin/pidof ");
define('NAME_VISSERVER', "visServer");

/*
 * 说明：获取VIS服务器模式
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
function GetVisServerMode($mode)
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
 * 说明：VIS server是否启动
 * 参数：无
 * 返回：启动返回TRUE，否则返回FALSE
 */
function IsVisRunning()
{
	exec(CMD_PIDOF . NAME_VISSERVER, $output, $retval);
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
 * 说明：启动VIS server
 * 参数：无
 * 返回：成功返回TRUE，否则返回FALSE
 */
function StartVisServer()
{
	if( !is_executable(FILE_STARTVIS) )
	{
		return FALSE;
	}
	if( IsVisRunning() === TRUE )
	{
		return TRUE;
	}
	
	exec(CMD_STARTVIS . " start");
	return TRUE;
}

/*
 * 说明：启动VIS server
 * 参数：无
 * 返回：成功返回TRUE，否则返回FALSE
 */
function StopVisServer()
{
	if( !is_executable(FILE_STARTVIS) )
		return FALSE;
	if( ! IsVisRunning() )
		return TRUE;

	exec(CMD_STARTVIS . " stop");
	return TRUE;
}

/*
 * 说明：重启VIS server
 * 参数：无
 * 返回：成功返回TRUE，否则返回FALSE
 */
function RestartVisServer()
{
	if( !is_executable(FILE_STARTVIS) )
		return FALSE;
	
	$bRunning = IsVisRunning();
	if( $bRunning == TRUE )
	{
		exec(CMD_STARTVIS . " stop");
		exec(CMD_STARTVIS . " start");
	}
	else
	{
		exec(CMD_STARTVIS . " start");
	}
	
	return TRUE;
}

class VisProfile
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
		if( ! is_writable(FILE_VISSERVER_INI) )
		{
			SetFileMode(FILE_VISSERVER_INI, 'w');
		}
		$this->ReadVisProfile();
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
	 * 说明：保存修改后的vis配置文件,修改字段值后必须调用此接口才能保存修改
	 * 参数：无
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	function Save()
	{
		$fp = fopen(FILE_VISSERVER_INI, 'wt');
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
	
	private function ReadVisProfile()
	{
		$file_buffer = array();
		$file_buffer = rfts(FILE_VISSERVER_INI);
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