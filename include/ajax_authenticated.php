<?php
session_start();
// 页面中还有ajax的包含此文件
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