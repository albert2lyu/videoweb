<?php
require_once("function.php");

define('DIR_MVPLIC', "/opt/MVP64/");
define('TOOL_GENSOURCE', "/opt/MVP64/GenSource");

class MvpLic
{
	function __construct()
	{
		SetFileMode(TOOL_GENSOURCE, 'x');
	}

	/*
	 * 说明：生成请求授权的文件
	* 参数：无
	* 返回：成功返回生成的文件的路径，否则返回FALSE
	*/
	function GetSourceFile()
	{
		// 获取当前工作目录
		exec("export LANG=C;/usr/bin/sudo pwd", $output, $retval);
		$cur_work_dir = $output[0];
		$ipaddr = $_SERVER['SERVER_ADDR'];
		$filename = "source_" . $ipaddr . "_" . strftime("%Y%m%d%H%M%S", time()) . ".info";
		exec("export LANG=C;/usr/bin/sudo " . TOOL_GENSOURCE, $output, $retval);
		if( $retval!=0 || file_exists($cur_work_dir . "/" . "source.info")===FALSE )
		{
			return FALSE;
		}
		exec("export LANG=C;/usr/bin/sudo /bin/mv " . $cur_work_dir . "/" . "source.info " . DIR_MVPLIC . $filename, $output, $retval);
		return DIR_MVPLIC . $filename;
	}

	/*
	 * 说明：通过文件应用授权
	 * 参数：$file：升级文件路径
	 * 返回：成功返回TRUE，否则返回FALSE
	 */
	function UpdateMvpLic($file)
	{
		if(file_exists($file) === FALSE)
		{
			return FALSE;
		}
		exec("export LANG=C; /usr/bin/sudo mv -f " . $file . " " . DIR_MVPLIC . "lic.info", $output, $retval);
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