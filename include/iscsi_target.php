<?php
require_once("lvm.php");
require_once("function.php");

/*
 * ˵����
 * 1����ȡ�����iscsi-target�����ơ�lun�б�lun id��·�������䷽ʽ��������״̬�����ỰID��initiator���ơ�����IP������
 * 2���½�iscsi-target
 * 3��ɾ��iscsi-target
 * 4��Ϊiscsi-targetӳ���ȡ��ӳ���߼���
 * 5��������ֹͣ������iscsi-target����
 * 6����ȡĳһiscsi-target������״̬
 * 7����ȡĳһiscsi-target��lun
 * 8����ȡ������Ϊiscsi-target lun���߼����б�
 * 
 * created by �����, 2009-11-11
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
	// iscsi-target�б�
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
	 * ˵������ȡ�ѹ����iscsi-target�б�
	 * ��������
	 * ���أ��ɹ�����iscsi-target�б�ʧ�ܷ���FALSE
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
	 * ˵���������µ�iscsi-target
	 * ������$it_name: iscsi-target������
	 * ���أ��ɹ�����TRUE,���򷵻�FALSE
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

		// ��ֹ�ظ�����
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
	 * ˵����Ϊiscsi-targetӳ���߼���
	 * ������$it_name��iscsi-target����
	 * 		 $lun����Ҫӳ���lun
			array(
				"name"=>"lv0",
				"path"=>"/dev/vg0/lv0",
				"size"=>"1024 MB",
				"vg"=>"vg0"
			)
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
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
					// ����ID
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
	 * ˵����Ϊiscsi-targetȡ��ӳ��һ��lun
	 * ������$it_name��iscsi-target����
	 * 		 $lun_id����Ҫȡ����lun��id
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
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
	 * ˵����ɾ��iscsi-target
	 * ������$it_name��iscsi-target����
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
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
	 * ˵������ȡĳһiscsi-target��lun�б�
	 * ������$it_name��iscsi-target������
	 * ���أ��ɹ�����lun�б���������
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
	 * 		ʧ�ܷ���FALSE 
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
	 * ˵������ȡĳһISCSI-TARGET������״̬
	 * ������$it_name��iscsi-target����
	 * ���أ��ɹ���������״̬��session�б�����
			array(
				array(
					"sid"=>"12345679",
					"initiator"=>"iqn.123",
					"ip"=>"192.168.58.43"
				),
				...
			)
	 *  	 ʧ�ܷ���FALSE;
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
	 * ˵������ȡ�ѱ�ӳ����߼����б�
	 * ��������
	 * ���أ��ɹ������б�ʧ�ܷ���FALSE
	 * 
	 * 		�����б�ṹ��
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
	 * ˵������ȡ��û�б�ӳ����߼����б�
	 * ��������
	 * ���أ��ɹ������߼����б�ʧ�ܷ���FALSE
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
			// ����Ѿ����ػ�������Ϊ���������޳�
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
						// ����Ѿ�ӳ�䣬���޳�
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
	 * ˵�����жϴ�iscsi-target�Ƿ��Ѿ�ӳ����LUN
	 * ������$it_name��iscsi-target����
	 * ���أ��Ѿ�ӳ�䷵��TRUE�����򷵻�FALSE
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
		// ɾ�����м�ע����
		$index = 0;
		foreach($lines as $line)
		{
			if(
				 preg_match("/^#.*$/", trim($line)) //ע����
				 ||
				 preg_match("/^\s*$/", trim($line)) // ����
			)
			{
				array_splice($lines, $index, 1);
				//����ɾ��һ��Ԫ�أ����Ե�ǰ������$index��ָ����һ��Ԫ��
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
			//�ҵ�iscsi-target����ƥ����
			if( preg_match("/Target\ /i", $line) )
			{
				$it = array();
				$it['name'] = preg_replace("/Target\ /i", "", $line, 1);
				$line = next($lines);
				while(TRUE)//��ȡissci-target��������
				{
					$line = current($lines);
					$lun_entry = array();
					$match = array();
					if($line === FALSE)
					{
						break;
					}
					$line = trim($line);
					// ��ȡ������һ��iscsi-target���˳���ǰiscsi-target��ƥ�����
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
				
				// ��ȡ��it������״̬
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
	 * ˵������ȡĳiscsi-target������״̬
	 * ������$it_name��iscsi-target������
	 * ���أ��ɹ���������״̬���飨��ʶ��sid��initiator���ơ����ӵ�IP��
	 * ����array(
				array(
					"sid"=>"12345679",
					"initiator"=>"iqn.123",
					"ipaddr"=>"192.168.58.43"
				),
				...
			)
			ʧ�ܷ���FALSE
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
		
		// ɾ�����м�ע����
		$index = 0;
		foreach($lines as $line)
		{
			if(
				 preg_match("/^#.*$/", trim($line)) //ע����
				 ||
				 preg_match("/^\s*$/", trim($line)) // ����
			)
			{
				array_splice($lines, $index, 1);
				//����ɾ��һ��Ԫ�أ����Ե�ǰ������$index��ָ����һ��Ԫ��
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
				// �ҵ���һ��iscsi-target session���˳�
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
					// �ҵ���һ��iscsi-target session���˳�
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
	 * ˵����ֹͣiscsi-target����
	 * ��������
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
	 */
	private function Stop()
	{
		exec(CMD_ISCSI_TARGET . "stop", $output, $retval);
		return $retval==0 ? TRUE : FALSE;
	}
	
	/*
	 * ˵��������iscsi-target����
	 * ��������
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
	 */
	private function Start()
	{
		exec(CMD_ISCSI_TARGET . "start", $output, $retval);
		// ˯һ���ӣ��ȴ�֮ǰ��������������
		sleep(1);
		return $retval==0 ? TRUE : FALSE;
	}
	
	/*
	 * ˵��������iscsi-target����
	 * ��������
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
	 */
	private function Restart()
	{
		exec(CMD_ISCSI_TARGET . "restart", $output, $retval);
		// ˯һ���ӣ��ȴ�֮ǰ��������������
		sleep(1);
		return $retval==0 ? TRUE : FALSE;
	}
	
	/*
	 * ˵��������������д��/etc/ietd.conf�ļ�
	 * ��������
	 * ���أ��ɹ�����TRUE�����򷵻�FALSE
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

