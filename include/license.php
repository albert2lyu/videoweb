<?php
require_once ("function.php");
/*
 * ˵���� License���� 1������License Source�ļ�source.info
 *                  2����Ȩlic.info�ļ�
 * created by ����䣬2012-11-10
 */
//
define ( 'GENSOURCE', "/opt/MVP/GenSource" );
define ( 'DIR_LICTMP', "/tmp/lic_tmp/" );
define ( 'DIR_MVP', "/opt/MVP/");
class License {
	function __construct() {
		SetFileMode ( GENSOURCE, 'x' );
	}
	
	/*
	 * ˵����������Ȩ�����ļ� 
	 * ��������
	 *  ���أ��ɹ��������ɵ��ļ���·�������򷵻�FALSE
	 */ 
	function GetReqSourceFile() {
		CreateDir ( DIR_LICTMP );
		$ipaddr = $_SERVER ['SERVER_ADDR'];
		$filename = "source_" . $ipaddr . "_" . strftime ( "%Y%m%d%H%M%S", time () ) . ".info";
		$tmpfile = DIR_LICTMP . $filename;
		$sourcefile= DIR_LICTMP . "source.info";
		exec ( "cd " . DIR_LICTMP . ";" . GENSOURCE, $output, $retval );
		if ($retval != 0 || file_exists ( $sourcefile ) === FALSE) {
			return FALSE;
		}
		// ������
		exec( "export LANG=C; /usr/bin/sudo mv -f " . $sourcefile . " " . $tmpfile, $output, $retval);
		if ($retval != 0 || file_exists ( $tmpfile ) === FALSE) {
			return FALSE;
		}
		
		return $tmpfile;
	}
	
	/*
	 * ˵��:Ӧ����Ȩ�ļ�
	 *  ������$file���ļ�·�� 
	 *  ���أ��ɹ�����TRUE�����򷵻�FALSE
	 */
	function UpdateLicFile($file) {
		if (file_exists ( $file ) === FALSE) {
			return FALSE;
		}
		exec ( "export LANG=C; /usr/bin/sudo mv -f " . $file . " " . DIR_MVP, $output, $retval );
		if ($retval != 0) {
			return FALSE;
		}
		
		return TRUE;
	}
	
	// //////////////////////////////////////////////////
	// private
}

?>