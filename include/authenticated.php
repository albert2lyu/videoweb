<?php
/*
 * ��֤ʱ���Ѿ���¼
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

// ��δ��¼�����ص�½���� ������¼�����webҳ�����ڵ�¼���ض���
if( $bLogin === FALSE )
{
	$_SESSION['ReqAddrBeforeLogin'] = $_SERVER["REQUEST_URI"];
	header("Location: login.php");
}

?>

