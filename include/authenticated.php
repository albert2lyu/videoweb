<?php
/*
 * 验证时候已经登录
 */
session_start();
$bLogin = FALSE;
if( isset($_SESSION['g_bLogin']) )
{
	if($_SESSION['g_bLogin'] === TRUE)
	{
		$bLogin = TRUE;
	}
}

// 尚未登录，返回登陆界面 ，并记录请求的web页面用于登录后重定向
if( $bLogin === FALSE )
{
	$_SESSION['ReqAddrBeforeLogin'] = $_SERVER["REQUEST_URI"];
	header("Location: login.php");
}

?>

