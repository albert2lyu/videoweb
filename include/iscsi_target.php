<?php
require_once("lvm.php");
require_once("function.php");

/*
 * 说明：
 * 1、获取共享的iscsi-target（名称、lun列表（lun id、路径、传输方式）、连接状态（（会话ID、initiator名称、连接IP）））
 * 2、新建iscsi-target
 * 3、删除iscsi-target
 * 4、为iscsi-target映射或取消映射逻辑卷
 * 5、启动、停止、重启iscsi-target服务
 * 6、获取某一iscsi-target的连接状态
 * 7、获取某一iscsi-target的lun
 * 8、获取可以作为iscsi-target lun的逻辑卷列表
 * 
 * created by 王大典, 2009-11-11
 */

define('FILE_IETD_CONF', "/etc/ietd.conf");
define('FILE_IET_SESSION', "/proc/net/iet/session");
define('FILE_IET_VOLUME', "/proc/net/iet/volume");
define('CMD_ISCSI_TARGET', "export LANG=C; /usr/bin/sudo /sbin/service iscsi-target ");

define('TYPE_BLOCKIO', "blockio");
define('TYPE_FILEIO', "fileio");
define('IOMODE', "IOMode=wt");
define('NAME_PREFIX', "iqn.sikeyuan.cn:nvr.");

class Iscsi_target
{
	// iscsi-target列表
	private $it_list = array(
/*		array(
			"name"=>"iqn.test",
			"lun"=>array(
				array(
					"id"=>0,
					"name"=>"lv0",
					"path"=>"/dev/vg0/lv0",
					"size"=>"100G",
					"type"=>"blockio"
				),
				array(
					"id"=>1,
					"name"=>"lv1",
					"path"=>"/dev/vg0/lv1",
					"size"=>"100G",
					"type"=>"blockio"
				),
				...
			),
			"session"=>array(
				array(
					"sid"=>"12345679",
					"initiator"=>"iqn.123",
					"ip"=>"192.168.58.43"
				),
				...
			)
			
		),
		...
		
*/
	);
	
	function __construct()
	{
		//$this->GetItList();
	}
	
	/*
	 * 说明：获取已共享的iscsi-target列表
	 * 参数：无
	 * 返回：成功返回iscsi-target列表，失败返回FALSE
	 */
	function GetItList()
	{
		if( $this->ListIt() )
		{
			return $this->it_list;
		}
		else
		{
			return FALSE;
		}
	}
	
	/*
	 * 说明：创建新的iscsi-target
	 * 参数：$it_name: iscsi-target的名称
	 * 返回：成功返回TRUE,否则返回FALSE
	 */
	function Create($it_name)
	{
		if( ! IsLvmNameOk($it_name) )
		{
			return FALSE;
		}
		$it_name = NAME_PREFIX . $it_name;

		if( $this->ListIt() === FALSE )
		{
			return FALSE;
		}

		// 防止重复名称
		foreach( $this->it_list as $entry)
		{
			if($entry['name'] == $it_name )
			{
				return FALSE;
			}
		}
		
		$it = array();
		$it['name'] = $it_name;
		$this->it_list[] = $it;

		$this->ModifyConfigFile();
		$this->Restart();
		
		return TRUE;		
	}
	
	/*
	 * 说明：为iscsi-target映射逻辑卷
	 * 参数：$it_name：iscsi-target名称
	 * 		 $lun：需要映射的lun
			array(
				"name"=>"lv0",
				"path"=>"/dev/vg0/lv0",
				"size"=>"1024 MB",
				"vg"=>"vg0"
			)
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	function Map($it_name, $lun)
	{
		/*if( $this->ListIt() === FALSE )
			return FALSE;*/
		$index = 0;
		$new_lun = array();
		if( !is_array($lun) && isset($lun['path']) && isset($lun['size']) )
		{
			return FALSE;
		}
		
		$new_lun['path'] = $lun['path'];
		$new_lun['size'] = $lun['size'];
		$new_lun['type'] = TYPE_BLOCKIO;

		foreach( $this->it_list as $it )
		{
			if($it['name'] == $it_name)
			{
				if( isset( $it['lun']) )
				{
					// 设置ID
					//$new_lun['id'] = count($it['lun']);
					$id_list = array();
					foreach( $it['lun'] as $entry )
					{
						$id_list[] = $entry['id'];
					}
					for($i=0; true; $i++)
					{
						if( ! in_array($i, $id_list) )
						{
							$new_lun['id'] = $i;
							break;
						}
					}
				}
				else
				{
					$new_lun['id'] = 0;
				}
				$this->it_list[$index]['lun'][] = $new_lun;
				break;
			}
			$index++;
		}
		
		$this->ModifyConfigFile();		
		$this->Restart();
		
		return TRUE;
	}
	
	/*
	 * 说明：为iscsi-target取消映射一个lun
	 * 参数：$it_name：iscsi-target名称
	 * 		 $lun_id：需要取消的lun的id
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	function Unmap($it_name, $lun_id)
	{
		/*if( $this->ListIt() === FALSE )
			return FALSE;*/
		$list_index = 0;
		foreach( $this->it_list as $it )
		{
			if($it['name'] == $it_name )
			{
				$index = 0;
				foreach($it['lun'] as $entry)
				{
					if( $entry['id'] == $lun_id )
					{
						array_splice($this->it_list[$list_index]['lun'], $index, 1);
						break;
					}
					$index++;
				}
			}
			$list_index++;
		}
		
		$this->ModifyConfigFile();
		$this->Restart();
		return TRUE;
	}
	
	/*
	 * 说明：删除iscsi-target
	 * 参数：$it_name：iscsi-target名称
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	function Remove($it_name)
	{
		/*if( $this->ListIt() === FALSE )
			return FALSE;*/
		$index = 0;
		foreach( $this->it_list as $it )
		{
			if( $it['name'] == $it_name )
			{
				array_splice($this->it_list, $index, 1);
				break;
			}
			$index++;
		}
		
		$this->ModifyConfigFile();
		$this->Restart();
		return TRUE;
	}
	
	/*
	 * 说明：获取某一iscsi-target的lun列表
	 * 参数：$it_name：iscsi-target的名称
	 * 返回：成功返回lun列表数组类似
			array(
				array(
					"id"=>0,
					"name"=>"lv0",
					"path"=>"/dev/vg0/lv0",
					"size"=>"100G",
					"type"=>"blockio"
				),
				array(
					"id"=>1,
					"path"=>"/dev/vg0/lv1",
					"name"=>"lv1",
					"size"=>"100G",
					"type"=>"blockio"
				),
				...
			)
	 * 		失败返回FALSE 
	 */
	function GetItLunList($it_name)
	{
		if( $this->ListIt() === FALSE )
		{
			return FALSE;
		}
		
		$lun_list = array();		
		foreach($this->it_list as $it)
		{
			if( $it['name'] == $it_name )
			{
				$lun_list = isset($it['lun']) ? $it['lun'] : array();				
				break;
			}
		}
		
		return $lun_list;
	}

	/*
	 * 说明：获取某一ISCSI-TARGET的连接状态
	 * 参数：$it_name：iscsi-target名称
	 * 返回：成功返回连接状态的session列表，类似
			array(
				array(
					"sid"=>"12345679",
					"initiator"=>"iqn.123",
					"ip"=>"192.168.58.43"
				),
				...
			)
	 *  	 失败返回FALSE;
	 */
	function GetItSession($it_name)
	{
		if( $this->ListIt() === FALSE )
		{
			return FALSE;
		}
			
		$session_list = array();
		foreach($this->it_list as $it)
		{
			if( $it['name'] == $it_name )
			{
				$lun_list = isset($it['session']) ? $it['session'] : array();				
				break;
			}
		}
		
		return $session_list;
	}
	
	/*
	 * 说明：获取已被映射的逻辑卷列表
	 * 参数：无
	 * 返回：成功返回列表，失败返回FALSE
	 * 
	 * 		返回列表结构：
	  		array(
	  			array(
				"name"=>"lv0",
				"path"=>"/dev/vg0/lv0",
				"size"=>"1024 MB",
				"vg"=>"vg0",
	  			"it"=>"iqn.123"
	  			),
	  			...
	  		)
	 */
	function GetMappedLvList()
	{
		if( $this->ListIt() === FALSE )
		{
			return FALSE;
		}

		$lun_list =	array(
		/*
		array(
			"name"=>"lv0",
			"path"=>"/dev/vg0/lv0",
			"size"=>"1024 MB",
			"vg"=>"vg0",
	  		"it"=>"iqn.123"
	  		),
	  		...
		*/
		);		
		
		foreach($this->it_list as $it)
		{
			if( ! isset($it['lun']) )
			{
				continue;
			}
			foreach( $it['lun'] as $entry )
			{
				$lun = array();
				$lun['name'] = substr( strrchr($entry['path'], "/"), 1 );
				$lun['path'] = $entry['path'];
				$lun['size'] = $entry['size'];
				$tmp_arr = explode("/", $entry['path']);
				$lun['vg']   = $tmp_arr[2];
				$lun['it']   = $it['name'];
				
				$lun_list[] = $lun;
			}
		}
		
		return $lun_list;
	}
	
	/*
	 * 说明：获取还没有被映射的逻辑卷列表
	 * 参数：无
	 * 返回：成功返回逻辑卷列表，失败返回FALSE
	 */
	function GetUnmappedLvList()
	{
		$logicVolume = new LogicVolume();
		$lv_list = $logicVolume->GetLvList();
		if($lv_list === FALSE)
		{
			return FALSE;
		}

		if( $this->ListIt() === FALSE )
		{
			return FALSE;
		}
		
		$index = 0;
		foreach($lv_list as $lv)
		{
			// 如果已经挂载或者已作为交换区，剔除
			$mapper_dev = $logicVolume->GetLvMapperDev($lv['path']);
			if( IsDiskMounted($mapper_dev) || IsDiskSwapon($mapper_dev) )
			{
				array_splice($lv_list, $index, 1);
				continue;
			}
			
			foreach($this->it_list as $it)
			{
				if( isset($it['lun']) )
				{
					foreach( $it['lun'] as $lun )
					{
						// 如果已经映射，则剔除
						if( $lun['path'] == $lv['path'] )
						{
							array_splice($lv_list, $index, 1);
							continue 3;
						}
					}
				}
			}
			$index++;
		}
		
		return $lv_list;
	}
	
	/*
	 * 说明：判断此iscsi-target是否已经映射了LUN
	 * 参数：$it_name：iscsi-target名称
	 * 返回：已经映射返回TRUE，否则返回FALSE
	 */
	function IsLunMapped($it_name)
	{
		$lun_list = $this->GetItLunList($it_name);
		if($lun_list === FALSE || count($lun_list)==0)
		{
			return FALSE;
		}
		
		return TRUE;
	}
		
	/////////////////////////////////////////////////////
	// private
	
	private function ListIt()
	{
		$this->it_list = array();
		
		$logicVolume = new LogicVolume();
		
		$file_buffer = rfts(FILE_IETD_CONF);
		if( $file_buffer === FALSE )
		{
			return FALSE;
		}
		$lines = explode("\n", $file_buffer);
		// 删除空行及注释行
		$index = 0;
		foreach($lines as $line)
		{
			if(
				 preg_match("/^#.*$/", trim($line)) //注释行
				 ||
				 preg_match("/^\s*$/", trim($line)) // 空行
			)
			{
				array_splice($lines, $index, 1);
				//由于删除一个元素，所以当前的索引$index已指向下一个元素
				continue;
			}
			$index++;
		}
		
		reset($lines);
		while(TRUE)
		{
			$line = current($lines);
			if($line === FALSE)
			{
				break;
			}
			//找到iscsi-target名称匹配行
			if( preg_match("/Target\ /i", $line) )
			{
				$it = array();
				$it['name'] = preg_replace("/Target\ /i", "", $line, 1);
				$line = next($lines);
				while(TRUE)//获取issci-target的配置行
				{
					$line = current($lines);
					$lun_entry = array();
					$match = array();
					if($line === FALSE)
					{
						break;
					}
					$line = trim($line);
					// 获取到了下一个iscsi-target则退出当前iscsi-target的匹配操作
					if( preg_match("/Target\ /i", $line) )
					{
						break;
					}
					if( preg_match("/Lun\s([0-9]+)\s/i", $line) )
					{
						// lun id
						if( preg_match("/Lun\s([0-9]+)\s/i", $line, $match) )
						{
							$lun_entry['id'] = $match[1];
						}
						//path
						if( preg_match("/Path=([^,]*)/i", $line, $match) )
						{
							$lun_entry['path'] = $match[1];
							$lun_entry['name'] = substr( strrchr($lun_entry['path'], "/"), 1 );
							$lun_entry['size'] = $logicVolume->GetLvSizeByPath($lun_entry['path']);
						}
						// type
						if( preg_match("/Type=([^,]*)/i", $line, $match) )
						{
							$lun_entry['type'] = $match[1];
						}
						$it['lun'][] = $lun_entry;
					}//if( preg_match("/Lun\s([0-9]+)\s/i", $line) )
					next($lines);
				}//while(TRUE)
				
				// 获取此it的连接状态
				$session = $this->ListItSession($it['name']);
				if($session !== FALSE && count($session)>0)
				{
					$it['session'] = $session;
				}
					
				$this->it_list[] = $it;
			}
			else
			{
				next($lines);
			}//if( preg_match("/Target\ /i", $line) ) else
		}//while(TRUE)
		
		return TRUE;
	}
	
	/*
	 * 说明：获取某iscsi-target的连接状态
	 * 参数：$it_name：iscsi-target的名称
	 * 返回：成功返回连接状态数组（标识符sid、initiator名称、连接的IP）
	 * 类似array(
				array(
					"sid"=>"12345679",
					"initiator"=>"iqn.123",
					"ipaddr"=>"192.168.58.43"
				),
				...
			)
			失败返回FALSE
	 */
	private function ListItSession($it_name)
	{
		$session = array(
/*
			array(
				"sid"=>"12345679",
				"initiator"=>"iqn.123",
				"ip"=>"192.168.58.43"
			),
			...
*/
		);
		
		$file_buffer = rfts(FILE_IET_SESSION);
		if( $file_buffer === FALSE )
		{
			return FALSE;
		}
		$lines = explode("\n", $file_buffer);
		
		// 删除空行及注释行
		$index = 0;
		foreach($lines as $line)
		{
			if(
				 preg_match("/^#.*$/", trim($line)) //注释行
				 ||
				 preg_match("/^\s*$/", trim($line)) // 空行
			)
			{
				array_splice($lines, $index, 1);
				//由于删除一个元素，所以当前的索引$index已指向下一个元素
				continue;
			}
			$index++;
		}
		
		reset($lines);		
		while(TRUE)
		{
			$line = current($lines);
			if($line === FALSE)
			{
				break;
			}
			if( preg_match("/$it_name/i", $line) )
			{
				$line = next($lines);
				if($line === FALSE)
					break;
				// 找到下一个iscsi-target session则退出
				if( preg_match("/tid:([0-9]+)/i", trim($line)) )
				{
					break;
				}
				while(TRUE)
				{
					$line = current($lines);
					$match = array();
					$sub_session = array();
					if($line === FALSE)
					{
						break;
					}
					$line = trim($line);
					// 找到下一个iscsi-target session则退出
					if( preg_match("/tid:([0-9]+)/i", $line) )
					{
						break;
					}
					preg_match("/sid:([^\s]+)\sinitiator:([^\s]*)$/i", $line, $match);
					$sub_session['sid'] = $match[1];
					$sub_session['initiator']= $match[2];
					$line = next($lines);
					preg_match("/ip:([^\s]*)/i", $line, $match);
					$sub_session['ip'] = $match[1];
					
					$session[] = $sub_session;
					
					next($lines);
				}//while(TRUE)
				
				break;
			}//if( preg_match("/$it_name/i", $line) )
			next($lines);
		}//while(TRUE)
		
		return $session;
	}
	
	/*
	 * 说明：停止iscsi-target服务
	 * 参数：无
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	private function Stop()
	{
		exec(CMD_ISCSI_TARGET . "stop", $output, $retval);
		return $retval==0 ? TRUE : FALSE;
	}
	
	/*
	 * 说明：启动iscsi-target服务
	 * 参数：无
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	private function Start()
	{
		exec(CMD_ISCSI_TARGET . "start", $output, $retval);
		// 睡一秒钟，等待之前的连接重新连接
		sleep(1);
		return $retval==0 ? TRUE : FALSE;
	}
	
	/*
	 * 说明：重启iscsi-target服务
	 * 参数：无
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	private function Restart()
	{
		exec(CMD_ISCSI_TARGET . "restart", $output, $retval);
		// 睡一秒钟，等待之前的连接重新连接
		sleep(1);
		return $retval==0 ? TRUE : FALSE;
	}
	
	/*
	 * 说明：将配置数据写入/etc/ietd.conf文件
	 * 参数：无
	 * 返回：成功返回TRUE，否则返回FALSE
	 */
	private function ModifyConfigFile()
	{
		$fd = fopen(FILE_IETD_CONF, 'w');
		if( $fd === FALSE )
		{
			return FALSE;
		}
		
		foreach( $this->it_list as $entry )
		{
			$buffer = "";

			$buffer .= "Target " . $entry['name'] . "\n";
			if( isset($entry['lun']) )
			{
				foreach($entry['lun'] as $lun)
				{
					$buffer .= "\tLun " . $lun['id'] . " Path=" . $lun['path'] . ",Type=" . $lun['type'] . ",IOMode=wt\n";
				}
			}
			$buffer .= "\n";
			fputs($fd, $buffer);
		}
		fflush($fd);
		fclose($fd);
		
		return TRUE;
	}
	
}

?>

