<?php
/*
 * 说明：
 * 		1、设置、获取当前计算机的时区/UTC
 * 		3、获取可用的时区列表
 * 
 * created by 王大典, 2009-12-17
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
	 * 说明：获取时区列表
	 * 参数：无
	 * 返回：成功则返回列表，否则返回FALSE
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
	 * 说明：获取当前计算机设置的时区
	 * 参数：$zone：时区值，输出参数用于返回
	 * 		 $utc: 是否启用了UTC（TRUE启用/FALSE未启用），输出参数用于返回
	 * 返回：成功则返回TRUE；失败返回FALSE
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
	 * 说明：设置时区
	 * 参数：$timezone:需要设置的时区的值，如"Asia/Shanghai"
	 * 		 $utc：TRUE/FALSE，启用或关闭UTC设置
	 * 返回：成功返回TRUE，否则返回FALSE
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
			// 写入文件
			fputs($fp, $zoneline);
			fputs($fp, $utcline);
			fputs($fp, "ARC=false\n");
			fflush($fp);
			fclose($fp);
			// 创建链接
			$command = "export LANG=C; /usr/bin/sudo /bin/ln -sfv /usr/share/zoneinfo/{$timezone} /etc/localtime";
			exec($command);
			// 写入硬件时钟
			$command = "export LANG=C; /usr/bin/sudo /sbin/hwclock -w";
			exec($command);
		}
		
		return TRUE;
	}
	
	/*
	 * 说明：设置默认的时区、utc
	 * 参数：无
	 * 返回：成功返回TRUE，否则返回FALSE
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
		
		// 排序
		$this->bListZoneOk = TRUE;
		sort($this->zone_list);
	}
}

?>