<?php
session_start();
// ҳ���л���ajax�İ������ļ�
if(isset($_SESSION['g_bLogin']))
{
	if($_SESSION['g_bLogin'] == TRUE)
	{
	}
	else
	{
		exit;
	}
}
else
{
	exit;
}
?>