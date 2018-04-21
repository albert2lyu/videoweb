<?php

/*
 * ˵��������iscsi-target
 * 		1����ȡָ��ip��iscsi-target�����б�target��IP��target���ƣ�
 * 		2��ɾ����ȡ����iscsi-target����
 * 		3������iscsi-target
 * 		4���Ͽ�����iscsi-target
 * 		5����ȡ�����ӵ�iscsi-target�б�target��IP��target���ƣ�
 * 		6���ж�ĳiscsi-target�Ƿ������� 
 * 		
 * created by �����, 2009-12-02
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
	 * ˵������ȡָ��IP�Ĺ����iscsi-target�б�ip��target���ƣ�
	 * ������$ip��IP��ַ
	 * ���أ��ɹ������б�ʧ�ܷ���FALSE
	 * 		 �б����ƣ�
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
	 * ˵����ɾ��ĳiscsi-target�ڵ���Ϣ
	 * ������ $ip��ָ��������IP
	 * 		 $it_name��ָ����iscsi-target����
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
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
	 * ˵��������ָ����iscsi-target
	 * ������ $ip��ָ��������IP
	 * 		 $it_name��ָ����iscsi-target����
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
	 */
	function LoginIt($ip, $it_name)
	{
		$command = CMD_ISCSIADM . ISCSI_NODE . "-T " . $it_name . " -p " . $ip . " -l";
		exec($command, $output, $retval);
		if($retval != 0)
		{
			return FALSE;
		}
		
		// �޸�rc.local��ӿ����Զ�����ָ�� 2010-06-30
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
	 * ˵�����Ͽ�����ָ����iscsi-target
	 * ������ $ip��ָ��������IP
	 * 		 $it_name��ָ����iscsi-target����
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
	 */
	function LogoutIt($ip, $it_name)
	{
		$command = CMD_ISCSIADM . ISCSI_NODE . "-T " . $it_name . " -p " . $ip . " -u";
		exec($command, $output, $retval);
		if($retval != 0)
		{
			return FALSE;
		}
		
		// ɾ����target��Ϣ���´����ӱ�����ȥ����Ŀ�꣬�������ӡ�
		$this->DeleteIt($ip, $it_name);
		
		// �޸�rc.localȡ�������Զ�����ָ�� 2010-06-30
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
	 * ˵������ȡ�����ӵ�iscsi-target�б�
	 * ��������
	 * ���أ��б����򷵻�FALSE
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
	 * ˵�����ж�ĳiscsi-target�Ƿ�������
	 * ������ $ip��ָ��������IP
	 * 		 $it_name��ָ����iscsi-target����
	 * ���أ������ӷ���TRUE�����򷵻�FALSE�����󷵻�-1
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