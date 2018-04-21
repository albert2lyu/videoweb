<?php
/*
 * ˵����
 * 		1�����á���ȡ��ǰ�������ʱ��/UTC
 * 		3����ȡ���õ�ʱ���б�
 * 
 * created by �����, 2009-12-17
 */

define('FILE_ZONETAB', "/usr/share/zoneinfo/zone.tab");
define('FILE_CLOCK', "/etc/sysconfig/clock");
define('FILE_LOCALTIME', "/etc/localtime");
define('ZONE_DEFAULT', "Asia/Shanghai");

class Timezone
{
	private $zone_list = array(
	/*
		 "Asia/Hong_Kong",
		 "Asia/Shanghai"
		 ...
	*/
	);	
	private $bListZoneOk = FALSE;
	
	function __construct()
	{
		$this->ListTimezone();
	}
	
	/*
	 * ˵������ȡʱ���б�
	 * ��������
	 * ���أ��ɹ��򷵻��б����򷵻�FALSE
	 */
	function GetTimezoneList()
	{
		if( $this->bListZoneOk !== TRUE )
		{
			if( $this->ListTimezone() === FALSE )
			{
				return FALSE;
			}
		}
		
		return $this->zone_list;
	}
	
	/*
	 * ˵������ȡ��ǰ��������õ�ʱ��
	 * ������$zone��ʱ��ֵ������������ڷ���
	 * 		 $utc: �Ƿ�������UTC��TRUE����/FALSEδ���ã�������������ڷ���
	 * ���أ��ɹ��򷵻�TRUE��ʧ�ܷ���FALSE
	 */
	function GetTimezone(&$zone, &$utc)
	{
		$zone = FALSE;
		$utc = FALSE;
		$cp = fopen(FILE_CLOCK, "rt");
		if( $cp )
		{
			while ( $line = fgets($cp) )
			{
				$line = trim($line);

				if ( strncmp($line, "UTC=true", 8) == 0 )
				{
					$utc = TRUE;
				}
				else if (strncmp($line, "ZONE=\"", 6) == 0)
				{
					$zone = substr($line, 6, strlen($line) - 7);
				}
			}
			fclose($cp);
		}

		if(!$zone)
		{
			if( is_link(FILE_LOCALTIME) )
			{
				$zone = readlink(FILE_LOCALTIME);
			}

			if ( ! $zone )
			{
				$zone = ZONE_DEFAULT;
			}
			else
			{
				$zone = strstr($zone, "zoneinfo/");
				$zone = substr($zone, 9, strlen($zone) - 9);
			}
		}
		
		return TRUE;
	}
	
	/*
	 * ˵��������ʱ��
	 * ������$timezone:��Ҫ���õ�ʱ����ֵ����"Asia/Shanghai"
	 * 		 $utc��TRUE/FALSE�����û�ر�UTC����
	 * ���أ��ɹ�����TRUE�����򷵻�FALSE
	 */
	function SetTimezone($timezone, $utc)
	{
		$zonelist = $this->GetTimezoneList();
		if( $zonelist === FALSE || !in_array($timezone, $zonelist) )
		{
			return FALSE;
		}
		$zoneline = "ZONE=\"{$timezone}\"\n";
		if($utc == TRUE)
		{
			$utcline = "UTC=true\n";
		}
		else
		{
			$utcline = "UTC=false\n";
		}
		
		SetFileMode(FILE_CLOCK, 'w');
		$fp = fopen(FILE_CLOCK, 'wt');
		if( $fp )
		{
			// д���ļ�
			fputs($fp, $zoneline);
			fputs($fp, $utcline);
			fputs($fp, "ARC=false\n");
			fflush($fp);
			fclose($fp);
			// ��������
			$command = "export LANG=C; /usr/bin/sudo /bin/ln -sfv /usr/share/zoneinfo/{$timezone} /etc/localtime";
			exec($command);
			// д��Ӳ��ʱ��
			$command = "export LANG=C; /usr/bin/sudo /sbin/hwclock -w";
			exec($command);
		}
		
		return TRUE;
	}
	
	/*
	 * ˵��������Ĭ�ϵ�ʱ����utc
	 * ��������
	 * ���أ��ɹ�����TRUE�����򷵻�FALSE
	 */
	function SetDefaultTimezone()
	{
		return $this->SetTimezone(ZONE_DEFAULT, FALSE);
	}
	
	///////////////////////////////////
	///////////////////////////////////
	// private
	
	private function ListTimezone()
	{
		//	VC      +1309-06114     America/St_Vincent
		//	VE      +1030-06656     America/Caracas
		//	VG      +1827-06437     America/Tortola
		if( ! file_exists(FILE_ZONETAB) )
		{
			return FALSE;
		}
		
		$fp = fopen(FILE_ZONETAB, 'rt');
		if($fp === FALSE)
		{
			return FALSE;
		}
		
		while( ! feof($fp) )
		{
			$line = trim( fgets($fp) );
			if( preg_match("/^#/", $line) || $line=="" )
			{
				continue;
			}
			$items = preg_split("/\s+/", $line);
			if( isset($items[2]) )
			{
				$this->zone_list[] = $items[2];
			}
		}
		fclose($fp);
		
		// ����
		$this->bListZoneOk = TRUE;
		sort($this->zone_list);
	}
}

?>