<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>测试视频</title>
<link href="./style/doc.css" rel="stylesheet" type="text/css">
<script type="text/javascript">
function do_core()
{
	document.getElementById('span_tip').innerText = "正在导出调试信息，请等待约1分钟...";
	alert("导出调试信息！");
	var doc = document.form_core; 
    doc.submit(); 
}

</script>
</head>

<body style="text-align:center;">

<span style="font:Verdana, Geneva, sans-serif; font-weight:bolder; color:#000;">MVP</span>
<hr/>
<div id="box" align="center">
<table width="400" border="2" cellpadding="0" align="center" cellspacing="0" >
<tr>
	<td height="100" colspan="3" align="center" valign="middle" style="font:Verdana, Geneva, sans-serif; font-weight:bolder; color:#906;">
导出MVP服务调试信息
	</td>
</tr>

<tr>
    
<?php
/*
 * 表单处理部分
 */
if(isset($_POST['hd_core']))
{
	print "<td align='left'>";
	//print "<br>开始导出mvp服务进程调试信息<br>";
	//print "正在导出...<br>";
	exec("export LANG=C; /usr/bin/sudo ./mvpcore", $output, $retval);
	if($retval != 0)
	{
		print "导出失败！<br><br>";
		print "错误信息:<br>";
		foreach( $line as $output)
		{
			print $line . "<br>";
		}
	}
	else
	{
		print "导出成功！<br>";
	}

	print "</td>";
}
else
{
	print "
			<td align='center'>
			    <input type='submit' id='core_btn'  onClick='return do_core();' value='导出MVP服务调试信息'/>
			</td>
		  ";
}
?>
</tr>
<tr>
	<td>
    	<span id="span_tip"></span>
    </td>
</tr>
</table>
<form name="form_core" id="form_core"action="core.php" method=POST>
    <input type="hidden" id="hd_core" name="hd_core" value="1"/>
</form>
</div>
</body>
</html>
