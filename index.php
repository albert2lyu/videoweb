<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>测试视频</title>
<link href="./style/doc.css" rel="stylesheet" type="text/css">
<script type="text/javascript">
function do_live()
{
	var obj_cam_id   = document.getElementById("input_cam_live_id");
	var obj_cam_preset   = document.getElementById("input_cam_preset");
	document.getElementById("hd_camid").value = obj_cam_id.value;
	document.getElementById("hd_camname").value = "摄像机-" + obj_cam_id.value;
	var page='live.php?' + 'camid=' + obj_cam_id.value + '&preset=' + obj_cam_preset.value;
	var liveWin;
	liveWin =window.open (
			 page,
			 'live_window'+obj_cam_id.value,
			 'resizable=yes,menubar=no,location=no,status=no,scrollbars=no'
	);
    var doc   = document.form_camdata; 
    doc.target = liveWin.name; 
    doc.action = page; 
    doc.submit(); 
    liveWin.focus(); 
}
function do_vod()
{
	var obj_cam_id   = document.getElementById("input_cam_vod_id");
	var page='vod.php?' + 'camid=' + obj_cam_id.value;
	document.getElementById("hd_camid").value = obj_cam_id.value;
	document.getElementById("hd_camname").value = "摄像机-" + obj_cam_id.value;
	var vodWin;
	vodWin=window.open (
			 page,
			 'vod_window'+obj_cam_id.value,
			 'resizable=yes,menubar=no,location=no,status=no,scrollbars=no'
	);
    var doc   = document.form_camdata; 
    doc.target = vodWin.name; 
    doc.action = page; 
    doc.submit(); 
    vodWin.focus(); 
}
function do_scan()
{
	var obj_scan_user   = document.getElementById("input_scan_user");
	var page='scan.php?user=' + obj_scan_user.value;
	//var page='scan.php';
	var scanWin;
	scanWin=window.open (
			 page,
			 'scan_window',
			 'resizable=yes,menubar=no,location=no,status=no,scrollbars=no'
	);
	/*
	var doc   = document.form_camdata; 
    doc.target = scanWin.name; 
    doc.action = page; 
    doc.submit(); 
    scanWin.focus();
	*/
}
function onunload()
{
	//alert("bye!");
}

</script>
</head>

<body onUnload="onunload()" style="text-align:center;">
<span>测试网页</span>
<hr/>
<div id="box" align="center">
<table width="400" border="3" cellpadding="3" align="center" cellspacing="2" >
<tr>
	<td height="100" colspan="3" align="center" valign="middle" style="font:Verdana, Geneva, sans-serif; font-weight:bolder; color:#906;">
MVP视频监控测试
	</td>
</tr>

<tr>
      <td class="field_title">直播</td>
      <td class="field_title">
        直播相机ID<input id="input_cam_live_id" type="text" value="1" size="8">
        预置位<input id="input_cam_preset" type="text" value="0" size="4">
      </td>
      <td>
        <input type="submit" id="live_btn"  onClick="return do_live();" value="  直播  "/>
      </td> 
</tr>
<tr>
    <td class="field_title">点播</td>
    <td class="field_title"> 
    	点播相机ID<input id="input_cam_vod_id" type="text" value="1" size="8">
    </td>
    <td class="field_title">
      <input type="submit" id="vod_btn"  onClick="return do_vod();" value="  点播  "/>
    </td>
</tr>
<tr>
    <td class="field_title">巡检</td>
    <td class="field_title"> 
      巡检用户名<input id="input_scan_user" type="text" value="1" size="8">
    </td>
    <td class="field_title">
      <input type="submit" id="scan_btn"  onClick="return do_scan();" value="  巡检  "/>
    </td>
  </tr>
</table>
<form name="form_camdata" id="form_camdata"action="http://192.168.1.118/php/live.php" method=POST>
    <input type="hidden" id="hd_camname" name="hd_camname" value="测试"/>
    <input type="hidden" id="hd_camid" name="hd_camid" value="0"/>
    <input type="hidden" id="hd_lxcx" name="hd_lxcx" value="0"/>
    <input type="hidden" id="hd_sfkk" name="hd_sfkk" value="1"/>
    <input type="hidden" id="hd_download" name="hd_download" value="1"/>
</form>
</div>
</body>
</html>
