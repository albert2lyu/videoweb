<?php
require_once("function.php");
/*
 * ˵���� UsbKey����
 * 		 1����ȡUsbKey����Ϣ�б��������£�
 * 			array(              					// usbkey 
 * 
 * 					"basic"=>array(        // ������Ϣ
 * 						"version"=>"02",   // ��Ȩ�����汾
 * 						"product"=>"01"    // ��Ȩ�ڵĲ�Ʒ
 * 					),
 * 
 * 					"auth"=>array(         // ��Ȩ��Ϣ
 * 						array(             // һ��ģ�����Ȩ��Ϣ
 * 							"module"=>1,   // ģ������
 * 							"count"=>1024  // ��Ȩ����
 * 						),
 * 						...
 * 					)
 * 			)
 * 
 * created by ����䣬2010-02-02
 */

define('TOOL_TESTKEY', "/opt/TestKey");
define('DIR_KEYUPDATE', "/tmp/key_update/");
define('TOOL_UPDATEKEY', "/opt/UpdateKey");

class UsbKey
{
	function __construct()
	{
		SetFileMode(TOOL_TESTKEY, 'x');
		SetFileMode(TOOL_UPDATEKEY, 'x');
	}
	
	/*
	 * ˵������ȡusbkey����Ϣ
	 * ���أ��ɹ�������Ϣ�б�ʧ�ܷ���FALSE
	 */
	function GetUsbKeyInfo()
	{
		$mvplic_info = array();
		
		exec("export LANG=C; /usr/bin/sudo " . TOOL_TESTKEY, $output, $retval);
		if($retval != 0)
		{
			return FALSE;
		}
		/*
		   Authorization Version: 1
		   Product ID: -1
		   Authorization List:
		   12    65535
		*/
		if(count($output) < 4)
		{
			return FALSE;
		}
		// ��ȡ�汾��Ϣ
		$version = 0;
		$version_str = array_shift($output);
		if( preg_match("/^Authorization Version:\s+(.*)$/i", trim($version_str), $match) )
		{
			$version = $match[1];
		}
		$mvplic_info["basic"]["version"] = $version;
		
		// ��ȡ��ƷID
		$product = -1;
		$product_str=array_shift($output);
		if( preg_match("/^Product ID:\s+(.*)$/i", trim($product_str), $match) )
		{
			$product = $match[1];
		}
		$mvplic_info["basic"]["product"] = $product;
				
		// ��ȡ��Ȩ�б�
		array_shift($output);
		foreach($output as $line)
		{
			$entry = array();
			if( preg_match("/^([0-9]+)\s+([0-9]+)$/", trim($line), $match) )
			{
				$entry['module'] = $match[1];
				$entry['count']  = $match[2];
				$mvplic_info['auth'][] = $entry;
			}
		}
		return $mvplic_info;
	}
	/*
	 * ˵����������������usbkey���ļ�
	 * ��������
	 * ���أ��ɹ��������ɵ��ļ���·�������򷵻�FALSE
	 */
	function GetReqUpdateFile()
	{
		CreateDir(DIR_KEYUPDATE);
		$ipaddr = $_SERVER['SERVER_ADDR'];
		$filename = "keyupdate_" . $ipaddr . "_" . strftime("%Y%m%d%H%M%S", time()) . ".req";
		$tmpfile = DIR_KEYUPDATE . $filename;
		exec("export LANG=C; /usr/bin/sudo " . TOOL_UPDATEKEY . " -r " . $tmpfile, $output, $retval);
		if( $retval!=0 || file_exists($tmpfile)===FALSE )
		{
			return FALSE;
		}
		
		return $tmpfile;
	}
	
	/*
	 * ˵����ͨ���ļ�Ӧ������usbkey
	 * ������$file�������ļ�·��
	 * ���أ��ɹ�����TRUE�����򷵻�FALSE
	 */
	function UpdateUsbKey($file)
	{
		if(file_exists($file) === FALSE)
		{
			return FALSE;
		}
		exec("export LANG=C; /usr/bin/sudo " . TOOL_UPDATEKEY . " -a " . $file, $output, $retval);
		if( $retval != 0 )
		{
			return FALSE;
		}
		
		return TRUE;
	}
	
	////////////////////////////////////////////////////
	// private
		
}


?>