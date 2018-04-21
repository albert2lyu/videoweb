<?php

/*
 * 说明：设置iscsi-target
 * 		1、获取指定ip的iscsi-target名称列表（target端IP，target名称）
 * 		2、删除获取到的iscsi-target名称
 * 		3、连接iscsi-target
 * 		4、断开连接iscsi-target
 * 		5、获取已连接的iscsi-target列表（target端IP，target名称）
 * 		6、判断某iscsi-target是否已连接 
 * 		
 * created by 王大典, 2009-12-02
 */
define('CMD_ISCSIADM', "export LANG=C; /usr/bin/sudo /sbin/iscsiadm ");
define('ISCSI_DISCOVERY', " -m discovery -t sendtargets ");
define('ISCSI_NODE', " -m node ");
define('ISCSI_SESSION', " -m session ");

class IscsiInitiator
{
	function __construct()
	{
		
	}
	
	/*
	 * 说明：获取指定IP的共享的iscsi-target列表（ip、target名称）
	 * 参数：$ip：IP地址
	 * 返回：成功返回列表，失败返回FALSE
	 * 		 列表类似：
				array(
				 	array(
				 		"server"=>"192.168.58.222",
				 		"target"=>"iqn.sikeyuan.cn:nvr.test"
				 	),
				 	...
				);
	 */
	function GetItList($ip)
	{
		if( empty($ip) )
		{
			return FALSE;
		}
		
		$command = CMD_ISCSIADM . ISCSI_DISCOVERY . "-p " . $ip;
		exec($command, $output, $retval);
		if( $retval != 0 )
		{
			return FALSE;
		}
		
		$it_list = array(
		/*
		 	array(
		 		"server"=>"192.168.58.222",
		 		"target"=>"iqn.sikeyuan.cn:nvr.test"
		 	),
		 	...
		 */
		);
		
		// 192.168.27.218:3260,1 iqn.sikeyuan.cn:nvr.a
		foreach($output as $line)
		{
			$items = explode(" ", trim($line));
			if( count($items) == 2 )
			{
				if( preg_match("|{$ip}|i", $items[0]) )
				{
					$it_name = $items[1];
					if( preg_match("|^iqn|i", $it_name) )
					{
						$entry['server'] = $ip;
						$entry['target'] = $it_name;
						$it_list[] = $entry;
					}
				}			
			}

		}
		
		return $it_list;
	}
	
	/*
	 * 说明：删除某iscsi-target节点信息
	 * 参数： $ip：指定服务器IP
	 * 		 $it_name：指定的iscsi-target名称
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	function DeleteIt($ip, $it_name)
	{
		//$command = CMD_ISCSIADM . ISCSI_NODE . " -T " . $it_name . " -p " . $ip . " -o delete";
		$command = CMD_ISCSIADM . ISCSI_NODE . " -T " . $it_name . " -o delete";
		exec($command, $output, $retval);
		if($retval != 0)
			return FALSE;

		return TRUE;
	}
	
	/*
	 * 说明：连接指定的iscsi-target
	 * 参数： $ip：指定服务器IP
	 * 		 $it_name：指定的iscsi-target名称
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	function LoginIt($ip, $it_name)
	{
		$command = CMD_ISCSIADM . ISCSI_NODE . "-T " . $it_name . " -p " . $ip . " -l";
		exec($command, $output, $retval);
		if($retval != 0)
		{
			return FALSE;
		}
		
		// 修改rc.local添加开机自动挂载指令 2010-06-30
		$in_str = "/sbin/iscsiadm -m node -T " . $it_name . " -p " . $ip . " -l"
		          . "; sleep 3; mount -a";
		$rclocal_file = "/etc/rc.d/rc.local";
		SetFileMode($rclocal_file, 'w');
		$file = New File($rclocal_file);
		if($file->Load() === TRUE)
		{
			if($file->FindLine($in_str) === FALSE)
			{
				$file->AddLineStart($in_str);
				$file->Save();
			}
		}
		
		return TRUE;
	}
	
	/*
	 * 说明：断开连接指定的iscsi-target
	 * 参数： $ip：指定服务器IP
	 * 		 $it_name：指定的iscsi-target名称
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	function LogoutIt($ip, $it_name)
	{
		$command = CMD_ISCSIADM . ISCSI_NODE . "-T " . $it_name . " -p " . $ip . " -u";
		exec($command, $output, $retval);
		if($retval != 0)
		{
			return FALSE;
		}
		
		// 删除此target信息，下次连接必须先去搜索目标，才能连接。
		$this->DeleteIt($ip, $it_name);
		
		// 修改rc.local取消开机自动挂载指令 2010-06-30
		$del_str = "/sbin/iscsiadm -m node -T " . $it_name . " -p " . $ip . " -l"
				   . "; sleep 3; mount -a";
		$rclocal_file = "/etc/rc.d/rc.local";
		SetFileMode($rclocal_file, 'w');
		$file = New File($rclocal_file);
		if($file->Load() === TRUE)
		{
			$file->DeleteLine($del_str);
			$file->Save();
		}
			
		return TRUE;
	}
	
	/*
	 * 说明：获取已连接的iscsi-target列表
	 * 参数：无
	 * 返回：列表，否则返回FALSE
	 */
	function GetConnectedIt()
	{
		$it_list = array(
		/*
		 	array(
		 		"server"=>"192.168.58.222",
		 		"target"=>"iqn.sikeyuan.cn:nvr.test"
		 	),
		 	...
		 */
		);

		$command = CMD_ISCSIADM . ISCSI_SESSION . "-o show";
		exec($command , $output, $retval);
		if($retval != 0)
		{
			return FALSE;
		}
		//tcp: [5] 192.168.58.222:3260,1 iqn.sikeyuan.cn:nvr.xyz		
		foreach($output as $line)
		{
			if( preg_match("|\s+([0-9\.]*):[^\s]*\s+([^\n]*)|i", trim($line), $match) )
			{
				$entry = array();
				$entry['server'] = $match[1];
				$entry['target'] = $match[2];
				$it_list[] = $entry;
			}
		}
		
		return $it_list;
	}
	
	/*
	 * 说明：判断某iscsi-target是否已连接
	 * 参数： $ip：指定服务器IP
	 * 		 $it_name：指定的iscsi-target名称
	 * 返回：已连接返回TRUE，否则返回FALSE，错误返回-1
	 */
	function IsConnected($ip, $it_name)
	{
		$it_list = $this->GetConnectedIt();
		if($it_list === FALSE)
		{
			return -1;
		}
		
		foreach($it_list as $it)
		{
			if($it['server']==$ip && $it['target']==$it_name)
			{
				return TRUE;
			}
		}
		return FALSE;
	}
	
}

?>