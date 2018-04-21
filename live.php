<!--<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\" <html xmlns="http://www.w3.org/1999/xhtml">-->
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>MVP平台-视频直播</title>
<link rel="shortcut icon" type="image/x-icon" href="images/mingding-v3.gif" />

<link rel="stylesheet" href="style/conn.css" type="text/css">
<link rel="stylesheet" href="style/doc.css" type="text/css">
<!--<script defer type="text/javascript" src="js/pngfix.js"></script>-->
<script type="text/javascript" src="js/ajax_function.js"></script>
<script type="text/javascript" src="js/function.js"></script>
<script type="text/javascript" src="js/basic.js"></script>
<script type="text/javascript" src="js/live.js"></script>
<?php 
header("Content-Type: text/html; charset=utf-8");
//print_r($_POST);
//print("<br>");
$cam_count = 0;
$cam_name_list = array();
$cam_id_list = array();
$cam_lxcx_list = array();
$cam_sfkk_list = array();
$cam_download_list = array();
$str_current_camid = 0;
$hd_camname = "";
$hd_camid = "";
$hd_lxcx="";
$hd_sfkk = "";
$hd_download = "";
if(isset($_POST['hd_camname']))
{
    $hd_camname = $_POST['hd_camname'];
    //print($hd_camname . "<br>");
    $cam_name_list = explode(";", $hd_camname);
    $cam_count = count($cam_name_list);
}

if(isset($_POST['hd_camid']))
{
    $hd_camid = $_POST['hd_camid'];
    //print($hd_camid . "<br>");
    $cam_id_list = explode(";", $hd_camid);
}

if(isset($_POST['hd_lxcx']))
{
	$hd_lxcx = $_POST['hd_lxcx'];
	//print($hd_lxcx . "<br>");
	$cam_lxcx_list = explode(";", $hd_lxcx);
}
else
{
	$cam_lxcx_list[0] = 1;
}

if(isset($_POST['hd_sfkk']))
{
    $hd_sfkk = $_POST['hd_sfkk'];
    //print($hd_sfkk . "<br>");
    $cam_sfkk_list = explode(";", $hd_sfkk);
}
if(isset($_POST['hd_download']))
{
    $hd_download = $_POST['hd_download'];
    //print($hd_download . "<br>");
    $cam_download_list = explode(";", $hd_download);
}
if(isset($_GET['camid']))
{
    $str_camid = $_GET['camid'];
    //print($str_camid . "<br>");
}
?>
<script type="text/javascript">
function onMyload()
{
	// 设置表单数据用于数据传输、共享
    document.getElementById("hd_camname").value = "<?php print $hd_camname ?>";
    document.getElementById("hd_camid").value = "<?php print $hd_camid ?>";
    document.getElementById("hd_lxcx").value = "<?php print $hd_lxcx ?>";
    document.getElementById("hd_sfkk").value = "<?php print $hd_sfkk ?>";
    document.getElementById("hd_download").value = "<?php print $hd_download ?>";

	// 继续加载live.js中其他业务
	onload();
}
// 鼠标按下img时处理函数，更替src图片路径
function DoOnMDown(obj, src)
{
	obj.src="images/" + src;
}
// 鼠标抬起img时处理函数，更替src图片路径
function DoOnMUp(obj, src)
{
	obj.src="images/" + src;
}
</script>
</head>

<body  onLoad="onMyload()" onResize="onWebResize()">
<table>
  <tr>
    <td align="left" valign="middle">
        <span class="popup_title" id="span_cam_info">MVP直播：</span>
    </td>
    <td>
      	<select class="cam_list" id="camera_list" onChange="ChangeLiveCam();"></select>
    </td>
    <td><span class="status_switch" id="span_switch_status"></span></td>
<?php 
	if($cam_lxcx_list[0] == 0)
	{
		print "<!--<br/>";
	}
?>
    <td>
    	<img src="images/toright_16.png" />
    	<a class="lt_link_font" onClick="GotoVodWeb();" title="点击进入点播页面" id="a_goto_vod" href="#">查看录像</a>
    </td>
<?php 
	if($cam_lxcx_list[0] == 0)
	{
		print "--><br/>";
	}
?>
  </tr>
</table>
<table cellspacing="0"  border="0">
<tr>
  <td align="left"  valign="top">
    <table width="100%"  align="left"  border="0">
      <tr>
        <td valign="top">
        <OBJECT
            id="player1"
            classid="clsid:{97037AFC-8D9C-47D6-8D4E-1CF7B96C7A7D}" 
            codebase="BocomSDK.ocx#version=1,0,0,0" 
            width=400 
            height=300
            align=center 
            hspace=0 
            vspace=0
         >
         </OBJECT>
        </td>
      </tr>
      <tr><td height="3"></td></tr>
      <tr>
        <td align="left" valign="top">
        <!--
        <img src="images/quanping_32.png" id="btn_fullscreen" title="全屏播放" onClick="DoFullscreen();" onMouseOver="imgTransparent(this);" onMouseOut="imgNoTransparent(this);"/>
        <img src="images/zhuapai_32.png" id="btn_capture" title="抓拍" onClick="DoCapture();"  onMouseOver="imgTransparent(this);" onMouseOut="imgNoTransparent(this);"/>
        <img src="images/luxiang_32.png" id="btn_localrecord" title="本地录像" onClick="DoStartLocalRecord();"  onMouseOver="imgTransparent(this);" onMouseOut="imgNoTransparent(this);"/>
        <img src="images/tingzhiluxiang_32.png" id="btn_stoplr" title="停止本地录像" onClick="DoStopLocalRecord();"  onMouseOver="imgTransparent(this);" onMouseOut="imgNoTransparent(this);"/>
        -->
        </td>
      </tr>
    </table>
  </td>
  <td align="left" valign="top" rowspan="2" width="150">
    <div id="ptz_div">
    <table border="0" cellspacing="3">
      <tr>
        <td colspan="3">
        <img src="images/menu_bar.gif"><span class="popup_title">云台控制</span>
        </td>
      </tr>
      <tr><td height="5"></td></tr>
      <tr>
        <td class="tdptzbtn">
          <a title="左上" href="javascript: void(0);" id="btn_leftup" onMouseDown="StartPTZ('left');StartPTZ('up');" onMouseUp="StopPTZ('leftup');"><img src="images/leftup2_32.png" onMouseDown="DoOnMDown(this, 'leftup_32.png');" onMouseUp="DoOnMDown(this, 'leftup2_32.png')"/></a>
         </td>
        <td class="tdptzbtn">
        <a title="上" href="javascript: void(0);" id="btn_up" onMouseDown="StartPTZ('up');" onMouseUp="StopPTZ('up');"><img src="images/up2_32.png" onMouseDown="DoOnMDown(this, 'up_32.png');" onMouseUp="DoOnMDown(this, 'up2_32.png')"/></a>
        </td>
        <td class="tdptzbtn">
        <a title="右上" href="javascript: void(0);" id="btn_rightup" onMouseDown="StartPTZ('right');StartPTZ('up');" onMouseUp="StopPTZ('rightup');"><img src="images/rightup2_32.png" onMouseDown="DoOnMDown(this, 'rightup_32.png');" onMouseUp="DoOnMDown(this, 'rightup2_32.png')"/></a>
        </td>
      </tr>
      <tr>
        <td class="tdptzbtn">
        <a title="左" href="javascript: void(0);" id="btn_left" onMouseDown="StartPTZ('left');" onMouseUp="StopPTZ('left');"><img src="images/left2_32.png" onMouseDown="DoOnMDown(this, 'left_32.png');" onMouseUp="DoOnMDown(this, 'left2_32.png')"/></a>
        </td>
        <td class="tdptzbtn">
        <a title="停止" href="javascript: void(0);" id="btn_stop" onClick="StopPTZ('stop');"><img src="images/stop2.png" onMouseDown="DoOnMDown(this, 'stop.png');" onMouseUp="DoOnMDown(this, 'stop2.png')"/></a>
        </td>
        <td class="tdptzbtn">
        <a title="右" href="javascript: void(0);" id="btn_right" onMouseDown="StartPTZ('right');" onMouseUp="StopPTZ('right');"><img src="images/right2_32.png" onMouseDown="DoOnMDown(this, 'right_32.png')" onMouseUp="DoOnMUp(this, 'right2_32.png')"/></a>
        </td>
      </tr>
      <tr>
        <td class="tdptzbtn">
        <a title="左下" href="javascript: void(0);" id="btn_leftdown" onMouseDown="StartPTZ('left');StartPTZ('down');" onMouseUp="StopPTZ('leftdown');"><img src="images/leftdown2_32.png" onMouseDown="DoOnMDown(this, 'leftdown_32.png');" onMouseUp="DoOnMDown(this, 'leftdown2_32.png')"/></a>
        </td>
        <td class="tdptzbtn">
        <a title="下" href="javascript: void(0);" id="btn_down" onMouseDown="StartPTZ('down');" onMouseUp="StopPTZ('down');"><img src="images/down2_32.png" onMouseDown="DoOnMDown(this, 'down_32.png');" onMouseUp="DoOnMDown(this, 'down2_32.png')"/></a>
         </td>
        <td class="tdptzbtn">
        <a title="右下" href="javascript: void(0);" id="btn_rightdown" onMouseDown="StartPTZ('right');StartPTZ('down');" onMouseUp="StopPTZ('rightdown');"><img src="images/rightdown2_32.png" onMouseDown="DoOnMDown(this, 'rightdown_32.png');" onMouseUp="DoOnMDown(this, 'rightdown2_32.png')"/></a>
        </td>
      </tr>
      <tr><td colspan="3" height="5"></td></tr>
      <tr>
        <td colspan="3" class="field_title">
            速度
          <a title="递减" href="javascript: void(0);" id="decrease_speed" onMouseDown="StartSetPtzSpeed(1);" onMouseUp="StopSetPtzSpeed();"><img src="images/decrease2.png" onMouseDown="DoOnMDown(this, 'decrease.png');" onMouseUp="DoOnMDown(this, 'decrease2.png')"/></a>
          <input type="text" size="4" maxlength="3" value="150" id="ptzspeed" style="ime-mode:Disabled;" onBlur="CheckPtzSpeed();"/>
          <a title="递增" href="javascript: void(0);" id="increase_speed" onMouseDown="StartSetPtzSpeed(2);" onMouseUp="StopSetPtzSpeed();"><img src="images/increase2.png" onMouseDown="DoOnMDown(this, 'increase.png');" onMouseUp="DoOnMDown(this, 'increase2.png')"/></a>
        </td>
      </tr>
    </table>

    <table  cellspacing="3" border="0">
    <tr><td colspan="3" height="10"></td></tr>
     <tr>
       <td colspan="3">
         <img src="images/menu_bar.gif"><span class="popup_title">镜头操作</span>
       </td>
     </tr>
     <tr>
       <td class="field_title">缩放</td>
       <td>
       <a title="放大" href="javascript: void(0);" id="btn_zoomin" onMouseDown="StartPTZ('zoomin');" onMouseUp="StopPTZ('zoomin');"><img src="images/PTZ-zoomin.png" onMouseDown="DoOnMDown(this, 'PTZ-zoomin2.png');" onMouseUp="DoOnMDown(this, 'PTZ-zoomin.png')"/></a>
       </td>
       <td>
       <a title="缩小" href="javascript: void(0);" id="btn_zoomout" onMouseDown="StartPTZ('zoomout');" onMouseUp="StopPTZ('zoomout');"><img src="images/PTZ-zoomout.png" onMouseDown="DoOnMDown(this, 'PTZ-zoomout2.png');" onMouseUp="DoOnMDown(this, 'PTZ-zoomout.png')"/></a>
       </td>
     </tr>
<?php 
	if($cam_lxcx_list[0] == 0)
	{
		print "<!--<br/>";
	}
?>
     <tr>
       <td class="field_title">焦距</td>
       <td>
       <a title="远焦" href="javascript: void(0);" id="btn_far" onMouseDown="StartPTZ('focusfar');" onMouseUp="StopPTZ('focusfar');"><img src="images/PTZ-far.png" onMouseDown="DoOnMDown(this, 'PTZ-far2.png');" onMouseUp="DoOnMDown(this, 'PTZ-far.png')"/></a>
       </td>
       <td>
       <a title="近焦" href="javascript: void(0);" id="btn_near" onMouseDown="StartPTZ('focusnear');" onMouseUp="StopPTZ('focusnear');"><img src="images/PTZ-near.png" onMouseDown="DoOnMDown(this, 'PTZ-near2.png');" onMouseUp="DoOnMDown(this, 'PTZ-near.png')"/></a>
       </td>
     </tr>
     <tr>
       <td class="field_title">光圈</td>
       <td>
       <a title="光圈大" href="javascript: void(0);" id="btn_lbig" onMouseDown="StartPTZ('irisopen');" onMouseUp="StopPTZ('irisopen');"><img src="images/PTZ-lbig.png" onMouseDown="DoOnMDown(this, 'PTZ-lbig2.png');" onMouseUp="DoOnMDown(this, 'PTZ-lbig.png')"/></a>
       </td>
       <td>
       <a title="光圈小" href="javascript: void(0);" id="btn_lsmall" onMouseDown="StartPTZ('irisclose');" onMouseUp="StopPTZ('irisclose');"><img src="images/PTZ-lsmall.png" onMouseDown="DoOnMDown(this, 'PTZ-lsmall2.png');" onMouseUp="DoOnMDown(this, 'PTZ-lsmall.png')"/></a>
       </td>
      </tr>
<?php 
	if($cam_lxcx_list[0] == 0)
	{
		print "--><br/>";
	}
?>
      <!--
      <tr>
       <td class="field_title">雨刷</td>
       <td><img title="雨刷" id="btn_wipe" src="images/PTZ-wiper.png" onClick="StartPTZ('wipe');" onMouseOver="imgTransparent(this);" onMouseOut="imgNoTransparent(this);"/></td>
       <td></td>
      </tr>
      -->

    </table>
    
<?php 
	if($cam_lxcx_list[0] == 0)
	{
		print "<!--<br/>";
	}
?>

    <table cellspacing="3" border="0">
      <tr><td colspan="3" height="10"></td></tr>
      <tr>
        <td colspan="3">
          <img src="images/menu_bar.gif"><span class="popup_title">预置位控制</span>
        </td>
      </tr>
<?php 
	if($cam_lxcx_list[0] == 10)
	{
		print "--><br/>";
	}
?>
      <!--
      <tr>
        <td><img title="常用预置位 1" id="btn_p1" src="images/1.png" onClick="ChangePresetNo(1);" onMouseOver="imgTransparent(this);" onMouseOut="imgNoTransparent(this);"/></td>
        <td><img title="常用预置位 2" id="btn_p2" src="images/2.png" onClick="ChangePresetNo(2);"  onMouseOver="imgTransparent(this);" onMouseOut="imgNoTransparent(this);"/></td>
        <td><img title="常用预置位 3" id="btn_p3" src="images/3.png" onClick="ChangePresetNo(3);"  onMouseOver="imgTransparent(this);" onMouseOut="imgNoTransparent(this);"/></td>
      </tr>
      <tr>
        <td><img title="常用预置位 4" id="btn_p4" src="images/4.png" onClick="ChangePresetNo(4);"  onMouseOver="imgTransparent(this);" onMouseOut="imgNoTransparent(this);"/></td>
        <td><img title="常用预置位 5" id="btn_p5" src="images/5.png" onClick="ChangePresetNo(5);"  onMouseOver="imgTransparent(this);" onMouseOut="imgNoTransparent(this);"/></td>
        <td><img title="常用预置位 6" id="btn_p6" src="images/6.png" onClick="ChangePresetNo(6);"  onMouseOver="imgTransparent(this);" onMouseOut="imgNoTransparent(this);"/></td>
      </tr>
      <tr>
        <td><img title="常用预置位 7"id="btn_p7" src="images/7.png" onClick="ChangePresetNo(7);"  onMouseOver="imgTransparent(this);" onMouseOut="imgNoTransparent(this);"/></td>
        <td><img title="常用预置位 8" id="btn_p8" src="images/8.png" onClick="ChangePresetNo(8);" onMouseOver="imgTransparent(this);" onMouseOut="imgNoTransparent(this);" /></td>
        <td><img title="常用预置位 9" id="btn_p9" src="images/9.png" onClick="ChangePresetNo(9);"  onMouseOver="imgTransparent(this);" onMouseOut="imgNoTransparent(this);"/></td>
      </tr>
      -->
<?php 
	if($cam_lxcx_list[0] == 0)
	{
		print "<!--<br/>";
	}
?>
    </table>
    <table border="0" cellspacing="2">
      <tr><td colspan="3" height="5"></td></tr>
      <tr>
        <td class="field_title">预置位</td>
        <td colspan="2" class="field_title">
        <a title="递减" href="javascript: void(0);" id="decrease_preset" onMouseDown="StartSetPreset(1);" onMouseUp="StopSetPreset();"><img src="images/decrease2.png" onMouseDown="DoOnMDown(this, 'decrease.png');" onMouseUp="DoOnMDown(this, 'decrease2.png')"/></a>
        <input type="text" size="4" value="1" maxlength="3"  id="preset" style="ime-mode:Disabled;" onBlur="CheckPreset();"/>
        <a title="递增" href="javascript: void(0);" id="increase_preset" onMouseDown="StartSetPreset(2);" onMouseUp="StopSetPreset();"><img src="images/increase2.png" onMouseDown="DoOnMDown(this, 'increase.png');" onMouseUp="DoOnMDown(this, 'increase2.png')"/></a>
        </td>
      </tr>
      <tr>
        <td  class="field_title">操作</td>
        <td>
        <a title="调用预置位" href="javascript: void(0);" id="btn_getpos" onClick="DoPreset('getpos');"><img src="images/PTZ-getpos.png" onMouseDown="DoOnMDown(this, 'PTZ-getpos2.png');" onMouseUp="DoOnMDown(this, 'PTZ-getpos.png')"/></a>
        </td>
        <td>
        <a title="设置预置位" href="javascript: void(0);" id="btn_setpos" onClick="DoPreset('setpos');"><img src="images/PTZ-setpos.png" onMouseDown="DoOnMDown(this, 'PTZ-setpos2.png');" onMouseUp="DoOnMDown(this, 'PTZ-setpos.png')"/></a>
        </td>
      </tr>
      <tr>
        <td colspan="3" align="center" valign="middle">
        	<span class="tip_result" id="span_preset_tip"></span>
        </td>
      </tr>
    </table>
    </div>
  </td>
</tr>
</table>
<?php 
	if($cam_lxcx_list[0] == 0)
	{
		print "--><br/>";
	}
?>
<!-- 提交给vod.php的表单 -->
<form name="form_camdata" id="form_camdata"action="vod.php" method=POST>
    <input type="hidden" id="hd_camname" name="hd_camname" value=""/>
    <input type="hidden" id="hd_camid" name="hd_camid" value=""/>
    <input type="hidden" id="hd_lxcx" name="hd_lxcx" value="0"/>
    <input type="hidden" id="hd_sfkk" name="hd_sfkk" value=""/>
    <input type="hidden" id="hd_download" name="hd_download" value=""/>
</form>
<script language="javascript" for="player1" event="OnNoteLiveEnd(info)">
	//document.write(info);
	OnNoteLiveEndFunc(info);
</script>
<script language="javascript" for="player1" event="OnNotePtzRetEvent(info)">
	//document.write(info);
	OnNotePtzRet(info);
</script>

<script language="javascript" type="text/javascript">
window.onunload=function(event){
	//player1.CloseVideo(0);
	var ret = player1.Logout(1);
	//alert( "live: logout! ret=" + ret);
}
</script>


</body>
</html>
