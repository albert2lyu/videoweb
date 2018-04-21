<?php
require_once("function.php");
/*
 * 说明： UsbKey管理
 * 		 1、获取UsbKey的信息列表，类似如下：
 * 			array(              					// usbkey 
 * 
 * 					"basic"=>array(        // 基本信息
 * 						"version"=>"02",   // 授权方案版本
 * 						"product"=>"01"    // 授权于的产品
 * 					),
 * 
 * 					"auth"=>array(         // 授权信息
 * 						array(             // 一个模块的授权信息
 * 							"module"=>1,   // 模块名称
 * 							"count"=>1024  // 授权数量
 * 						),
 * 						...
 * 					)
 * 			)
 * 
 * created by 王大典，2010-02-02
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
	 * 说明：获取usbkey的信息
	 * 返回：成功返回信息列表，失败返回FALSE
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
		// 获取版本信息
		$version = 0;
		$version_str = array_shift($output);
		if( preg_match("/^Authorization Version:\s+(.*)$/i", trim($version_str), $match) )
		{
			$version = $match[1];
		}
		$mvplic_info["basic"]["version"] = $version;
		
		// 获取产品ID
		$product = -1;
		$product_str=array_shift($output);
		if( preg_match("/^Product ID:\s+(.*)$/i", trim($product_str), $match) )
		{
			$product = $match[1];
		}
		$mvplic_info["basic"]["product"] = $product;
				
		// 获取授权列表
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
	 * 说明：生成请求升级usbkey的文件
	 * 参数：无
	 * 返回：成功返回生成的文件的路径，否则返回FALSE
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
	 * 说明：通过文件应用升级usbkey
	 * 参数：$file：升级文件路径
	 * 返回：成功返回TRUE，否则返回FALSE
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