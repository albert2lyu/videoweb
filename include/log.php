<?php
require_once("file.php");
require_once("function.php");
require_once("timezone.php");

/*
 * 说明：vstor web的log管理
 * 	1、记录LOG
 *  2、清空LOG
 *  3、读取LOG
 *  created by 王大典，2010-01-12
 */

define('FILE_LOG', "/opt/vstor/web/log/vstor.log");
define('FILE_LOG_CN', "/opt/vstor/web/log/vstor_cn.log");
define('FILE_LOG_EN', "/opt/vstor/web/log/vstor_en.log");
define('MAX_LOG_COUNT', 10000);
define('REMAIN_LOG_COUNT', 8000);
define('LOG_SEPARATOR', '&&&');

// 模块定义
define('MOD_SYSTEM', "System");
define('MOD_NETWORK', "Network");
define('MOD_CLOCK', "Clock");
define('MOD_VIS', "VIS");
define('MOD_MVP', "NVR");
define('MOD_VOLUME', "Volume");
define('MOD_ACCOUNT', "Account");
define('MOD_RAID', "RAID");

// 日志类型定义
define('LOG_ERROR', "error");
define('LOG_WARN', "warning");
define('LOG_INFOS', "information");
define('LOG_OTHER', "other");

Class Log
{
	function __construct()
	{
		SetFileMode(FILE_LOG_CN, 'wr');
		SetFileMode(FILE_LOG_EN, 'wr');
		$this->CheckLogPolicy(EN_LANG);
		$this->CheckLogPolicy(CN_LANG);
	}
	
	/*
	 * 说明：记录LOG
	 * 参数：$module: 模块名称-MOD_SYSTEM, MOD_NETWORK, MOD_CLOCK, MOD_VIS, MOD_VOLUME, MOD_ACCOUNT
	 * 		 $log: LOG信息
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	function VstorWebLog($level, $module, $log, $lang=EN_LANG)
	{
		// 获取当前系统时间，并格式化为“yyyy-mm-dd hh:mm:ss”
		$bUtcEnabled = FALSE;
		$valueTimezone = "";
		$timezone = new Timezone();
		$timezone->GetTimezone($valueTimezone, $bUtcEnabled);
		date_default_timezone_set($valueTimezone);
		$log_time = date("Y-m-d H:i:s");
		
		// 获取客户端IP
		if( isset($_SERVER['REMOTE_ADDR']) )
		{
			$log_remoteip = $_SERVER['REMOTE_ADDR'];
		}
		else
		{
			$log_remoteip = "-";
		}
		
		$log_buffer = $level . LOG_SEPARATOR . $log_time . LOG_SEPARATOR . $log_remoteip . LOG_SEPARATOR
					  . $module . LOG_SEPARATOR . $log . "\n";
		
		// 写入文件
		if( $lang == CN_LANG )
		{
			$fp = fopen(FILE_LOG_CN, 'at');
		}
		else if( $lang == EN_LANG )
		{
			$fp = fopen(FILE_LOG_EN, 'at');
		}
		else
		{
			return FALSE;
		}

		if($fp === FALSE)
		{
			return FALSE;
		}
		if(flock($fp, LOCK_EX))
		{
			fwrite($fp, $log_buffer);
			flock($fp, LOCK_UN);
		}
/*
		else
		{
			fwrite($fp, $log_buffer);
		}
*/
		fclose($fp);
		
		return TRUE;
	}
	
	/*
	 * 说明：清空日志
	 * 参数：无
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	function ClearLog()
	{
		$fpcn = fopen(FILE_LOG_CN, 'wt');
		$fpen = fopen(FILE_LOG_EN, 'wt');
		if($fpcn === FALSE && $fpen === FALSE)
		{
			return FALSE;
		}
		fclose($fpcn);
		fclose($fpen);
		
		return TRUE;
	}

	/*
	 * 说明：获取日志
	 * 参数：无
	 * 返回：成功则返回日志详细列表(按照最新的时间顺序)，如下
			array(
				array(
					"level"=>"information",
					"time"=>"2010-10-10 10:10:10",
					"remote_ip"=>"192.168.58.43",
					"module"=>"System",
					"log"=>"Hello, World!"
				),
				...
			)，
			失败返回FALSE
	 */
	function GetLog($web_lang=EN_LANG)
	{
		$log_list = array(
		/*
			array(
				"level"=>"information",
				"time"=>"2010-10-10 10:10:10",
				"remote_ip"=>"192.168.58.43",
				"module"=>"System",
				"log"=>"Hello, World!"
			),
			...
		*/
		);
		
		if( $web_lang == CN_LANG )
		{
			$fp = fopen(FILE_LOG_CN, 'rt');
		}
		else if( $web_lang == EN_LANG )
		{
			$fp = fopen(FILE_LOG_EN, 'rt');
		}
		else
		{
			return FALSE;
		}
		
		if($fp === FALSE)
		{
			return FALSE;
		}
		if(flock($fp, LOCK_SH))
		{
			while( !feof($fp) )
			{
				$line = trim(fgets($fp));
				$line_arr = explode(LOG_SEPARATOR, $line);
				if( count($line_arr) >= 5 )
				{
					$entry = array();
					$entry['level'] = $line_arr[0];
					$entry['time'] = $line_arr[1];
					$entry['remote_ip'] = $line_arr[2];
					$entry['module'] = $line_arr[3];
					$entry['log'] = $line_arr[4];
				
					$log_list[] = $entry;
				}
				else 
				{
					continue;
				}
			}
			
			flock($fp, LOCK_UN);
		}

		fclose($fp);
		
		return array_reverse($log_list);
	}
	
	/////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////
	// private
	
	/*
	 * 说明：维护log记录
	 */
	private function CheckLogPolicy($web_lang=EN_LANG)
	{
		$log_list = $this->GetLog($web_lang);
		$log_count = count($log_list);
		if( $log_list === FALSE 
			|| 
			$log_count < MAX_LOG_COUNT /*如果log记录数小于限定值则不作处理*/
		)
		{
			return FALSE;
		}
		
		// log记录数大于限定值，做部分删除处理
		$log_list = array_slice($log_list, 0, REMAIN_LOG_COUNT);
		// 将$log_list倒序，重新写入文件
		$log_list = array_reverse($log_list);
		
		if( $web_lang == CN_LANG )
		{
			$fp = fopen(FILE_LOG_CN, 'wt');
		}
		else if( $web_lang == EN_LANG )
		{
			$fp = fopen(FILE_LOG_EN, 'wt');
		}
		else
		{
			return FALSE;
		}

		if($fp === FALSE)
		{
			return FALSE;
		}
		if(flock($fp, LOCK_EX))
		{
			foreach( $log_list as $entry )
			{
				$log_line_buffer = $entry['level'] . LOG_SEPARATOR . $entry['time'] . LOG_SEPARATOR
								   . $entry['remote_ip'] . LOG_SEPARATOR . $entry['module']
								   . LOG_SEPARATOR . $entry['log'] . "\n";
				fwrite($fp, $log_line_buffer);
			}			
			flock($fp, LOCK_UN);
		}
		fclose($fp);
		
		return TRUE;
	}	
}
?>