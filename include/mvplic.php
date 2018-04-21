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
	 * ˵��������������Ȩ���ļ�
	* ��������
	* ���أ��ɹ��������ɵ��ļ���·�������򷵻�FALSE
	*/
	function GetSourceFile()
	{
		// ��ȡ��ǰ����Ŀ¼
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
	 * ˵����ͨ���ļ�Ӧ����Ȩ
	 * ������$file�������ļ�·��
	 * ���أ��ɹ�����TRUE�����򷵻�FALSE
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