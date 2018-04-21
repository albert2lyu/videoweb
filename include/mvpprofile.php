<?php

/*
 * ˵�����޸�mvpServer.ini�ļ�����mvp��������
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
 * ˵������ȡMVP������ģʽ
 * ������$mode��ģʽֵ
 * 	#���÷�����ģʽ,λ�뷽ʽ
	#SVR_MODE_STORAGE = 1,			//�洢������
	#SVR_MODE_VOD = 2,				//�㲥������
	#SVR_MODE_TRANSMIT = 4,			//ת��������
	#SVR_MODE_DOWNLOAD = 8,			//���ط�����
	#SVR_MODE_MANAGER = 128,		//���������
 * ���أ��������Ԫ�ص����飬ÿ��Ԫ�ص�ֵΪ0��1����ʾ�Ƿ�Ϊ�˷�����ģʽ��˳�����ϣ���
 * 		 ʧ�ܷ���FALSE
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
 * ˵����NVR Service�Ƿ�����
 * ��������
 * ���أ���������TRUE�����򷵻�FALSE
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
 * ˵��������NVR Service
 * ��������
 * ���أ��ɹ�����TRUE�����򷵻�FALSE
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
 * ˵��������NVR Service
 * ��������
 * ���أ��ɹ�����TRUE�����򷵻�FALSE
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
 * ˵��������NVR Service
 * ��������
 * ���أ��ɹ�����TRUE�����򷵻�FALSE
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
	// �����ļ���������
	private $all_lines = array(); 
	// �����ļ��ķǿ��С���ע����
	private $lines = array(
	/*	�к�=>����
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
	 * ˵������ȡĳ�ֶε�ֵ
	 * ������$field���ֶ�����
	 * ���أ��ɹ�������Ӧ���ֶ�ֵ��ʧ�ܷ���FALSE��ʹ��===�ж�FALSE��
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
	 * ˵��������ĳ�ֶε�ֵ
	 * ������$field���ֶ�����
	 * 		 $value���ֶ�ֵ
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
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
	 * ˵���������޸ĺ��mvp�����ļ�,�޸��ֶ�ֵ�������ô˽ӿڲ��ܱ����޸�
	 * ��������
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
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
		// �޳����м�ע����
		$index = 0;
		foreach($this->all_lines as $line)
		{
			if(
				 preg_match("/^#.*$/", trim($line)) //ע����
				 ||
				 preg_match("/^\s*$/", trim($line)) // ����
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