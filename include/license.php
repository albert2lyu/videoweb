<?php
require_once ("function.php");
/*
 * 说明： License管理 1、生成License Source文件source.info
 *                  2、授权lic.info文件
 * created by 王大典，2012-11-10
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
	 * 说明：生成授权请求文件 
	 * 参数：无
	 *  返回：成功返回生成的文件的路径，否则返回FALSE
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
		// 重命名
		exec( "export LANG=C; /usr/bin/sudo mv -f " . $sourcefile . " " . $tmpfile, $output, $retval);
		if ($retval != 0 || file_exists ( $tmpfile ) === FALSE) {
			return FALSE;
		}
		
		return $tmpfile;
	}
	
	/*
	 * 说明:应用授权文件
	 *  参数：$file：文件路径 
	 *  返回：成功返回TRUE，否则返回FALSE
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