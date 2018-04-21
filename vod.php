<!--<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\" <html xmlns="http://www.w3.org/1999/xhtml">-->
<html >
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>MVP平台-录像点播</title>
<link rel="shortcut icon" type="image/x-icon" href="images/mingding-v3.gif" />

<link rel="stylesheet" href="style/conn.css" type="text/css">
<link rel="stylesheet" href="style/doc.css" type="text/css">

<!--<script defer type="text/javascript" src="js/pngfix.js"></script>-->
<script type="text/javascript" src="js/ajax_function.js"></script>
<script type="text/javascript" src="js/calendar_cn.js"></script>
<script type="text/javascript" src="js/function.js"></script>
<script type="text/javascript" src="js/basic.js"></script>
<script type="text/javascript" src="js/vod.js"></script>
<?php 
header("Content-Type: text/html; charset=utf-8");
//print_r($_POST);
//print("<br>");
$cam_count = 0;
$cam_name_list = array();
$cam_id_list = array();
$cam_sfkk_list = array();
$cam_download_list = array();
$str_current_camid = 0;
$hd_camname = "";
$hd_camid = "";
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
    document.getElementById("hd_sfkk").value = "<?php print $hd_sfkk ?>";
    document.getElementById("hd_download").value = "<?php print $hd_download ?>";

	// 继续加载vod.js中其他业务
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
	   <span class="popup_title" id="span_cam_info">MVP视频点播：</span>
	</td>
	<td>
	   <select class="cam_list" id="camera_list" onChange="ChangeVodCam();"></select>
	</td>
    <td><span class="status_switch" id="span_switch_status"></span></td>
    <td>
        <img src="images/toright_16.png" />
        <a class="lt_link_font" onClick="GotoLiveWeb();" title="点击进入直播页面" id="a_goto_live" href="#">查看直播</a>
    </td>
  </tr>
</table>
<table cellspacing="0" width="100%" border="0">
  <tr>
  	<td align="left" valign="top">
    <table  width="100%" align="left" border="0">
      <tr>
    	<td align="left" valign="top">
          <OBJECT
            id=vodplayer
            classid="clsid:{97037AFC-8D9C-47D6-8D4E-1CF7B96C7A7D}"
            codebase="BocomSDK.ocx#version=1,0,0,0"
            width=400
            height=300
            align=center
            hspace=0
            vspace=0
          ></OBJECT>
        </td>
      </tr>
      <tr><td height="5"></td></tr>
      <tr>
        <td valign="top" align="left">
        <a title="播放录像" href="javascript: void(0);" id="vod_play" onClick="OpenVod(1);"><img src="images/vodplay.png" onMouseDown="DoOnMDown(this, 'vodplay2.png');" onMouseUp="DoOnMDown(this, 'vodplay.png')"/></a>
        <a title="暂停播放录像" href="javascript: void(0);" id="vod_pause" onClick="VodPlayCtrl(2);"><img src="images/vodpause.png" onMouseDown="DoOnMDown(this, 'vodpause2.png');" onMouseUp="DoOnMDown(this, 'vodpause.png')"/></a>
        <a title="倒播录像" href="javascript: void(0);" id="vod_playback" onClick="VodPlayCtrl(3);"><img src="images/vodplayback.png" onMouseDown="DoOnMDown(this, 'vodplayback2.png');" onMouseUp="DoOnMDown(this, 'vodplayback.png')"/></a>
        <a title="停止播放录像" href="javascript: void(0);" id="vod_stop" onClick="EndVod();"><img src="images/vodstop.png" onMouseDown="DoOnMDown(this, 'vodstop2.png');" onMouseUp="DoOnMDown(this, 'vodstop.png')"/></a>
        <a title="慢速播放录像" href="javascript: void(0);" id="vod_playslow" onClick="SetPlaybackSpeed('slow');"><img src="images/vodplayslow.png" onMouseDown="DoOnMDown(this, 'vodplayslow2.png');" onMouseUp="DoOnMDown(this, 'vodplayslow.png')"/></a>
        <a title="快速播放录像" href="javascript: void(0);" id="vod_playfast" onClick="SetPlaybackSpeed('fast');"><img src="images/vodplayfast.png" onMouseDown="DoOnMDown(this, 'vodplayfast2.png');" onMouseUp="DoOnMDown(this, 'vodplayfast.png')"/></a>
        <a title="单帧倒播" href="javascript: void(0);" id="vod_playframeback" onClick="VodPlayCtrl(5);"><img src="images/vodplayframeback.png" onMouseDown="DoOnMDown(this, 'vodplayframeback2.png');" onMouseUp="DoOnMDown(this, 'vodplayframeback.png')"/></a>
        <a title="单帧正播" href="javascript: void(0);" id="vod_playframe" onClick="VodPlayCtrl(4);"><img src="images/vodplayframe.png" onMouseDown="DoOnMDown(this, 'vodplayframe2.png');" onMouseUp="DoOnMDown(this, 'vodplayframe.png')"/></a>
        <span id="span_vod_status" class="tip_text" style="display:none"></span>
        </td>  
      </tr> 
    </table>
    </td>
    <td align="left" valign="top" width="274">
      <table cellspacing="2" border="0">
        <tr>
          <td colspan="3">
            <img src="images/menu_bar.gif"><span class="popup_title">录像检索</span>
          </td>
        </tr>
        <tr>
          <td class="field_title">开始时间</td>
	  	  <td>
			<input type="text" title="设置录像检索开始时间" size="20" value="" id="start_t" onClick="SelectDate(this,'yyyy-MM-dd hh:mm:ss',0,0)" readonly />
          </td>
          <td>
          <a title="设置为本机当前时间" href="javascript: void(0);" id="btn_setlocaltime1" onClick="SetLocalTime('start');"><img src="images/localtime.png" onMouseDown="DoOnMDown(this, 'localtime2.png');" onMouseUp="DoOnMDown(this, 'localtime.png')"/></a>
		  </td>
        </tr>
        <tr>
          <td class="field_title">结束时间</td>
	  	  <td>
			<input type="text" title="设置录像检索结束时间" size="20" value="" id="end_t" onClick="SelectDate(this,'yyyy-MM-dd hh:mm:ss',0,0)" readonly />
          </td>
          <td>
            <a title="设置为本机当前时间" href="javascript: void(0);" id="btn_setlocaltime1" onClick="SetLocalTime('end');"><img src="images/localtime.png" onMouseDown="DoOnMDown(this, 'localtime2.png');" onMouseUp="DoOnMDown(this, 'localtime.png')"/></a>
		  </td>
        </tr>
        <tr>
          <td class="field_title">开始检索</td>
          <td colspan="2">
            &nbsp;&nbsp;<a title="开始录像检索" href="javascript: void(0);" id="vod_query" onClick="StartVodQurey(true);"><img src="images/vodsearch2.png" onMouseDown="DoOnMDown(this, 'vodsearch2_1.png');" onMouseUp="DoOnMDown(this, 'vodsearch2.png')"/></a>
            <span id="span_vodsearch" class="tip_text" style="display:none"></span>
          </td>
        </tr>
        <tr><td height="8" colspan="3"></td></tr>
      </table>
      <div id="div_vod_result1">
      <table cellpadding="2" width="100%">
        <tr>
          <td>
          <img src="images/menu_bar.gif"><span class="popup_title">检索结果</span>
          </td>
        </tr>
        <tr><td height="5"></td></tr>
        <tr>
          <td align="center">
          	<img src="images/log_warning.gif"><span style="font-size:14px; color:#F00">未检索到录像<br>检索录像时间跨度尽量不要超过24小时</span>
            <!--<img src="images/log_warning.gif"><span style="font-size:14px; color:#F00">未检索到录</span>-->
          </td>
        </tr>
        </table>
      </div>
      <div id="div_vod_result2">
      <table cellpadding="2">
        <tr>
          <td colspan="2">
          <img src="images/menu_bar.gif"><span class="popup_title">检索结果</span>
          </td>
        </tr>
        <tr><td height="5" colspan="2"></td></tr>
        <tr>
          <td colspan="2">
          	<select id="vod_result_list" size="8" multiple="true" onChange="SelectOne()" onDblClick="OpenVod(2);">
            </select>
            <br/>
            <img src="images/log_info.gif"><span id="span_vod_ret" style="font-size:12px; color:#000"></span>
          </td>
        </tr>
        <tr>
          <td height="8" colspan="2"></td>
        </tr>
      </table>
      <div id="div_download">
      <table cellpadding="2">
        <tr>
          <td colspan="2"><img src="images/menu_bar.gif"><span class="popup_title">下载设置</span></td>
        </tr>
        <tr><td height="5" colspan="2"></td></tr>
         <tr>
          <td class="field_title">视频格式</td>
          <td class="field_title">
         	 <input type="checkbox" id="chk_toavi" checked="checked" onClick="DoSetToAvi();" /><label id="lbl_toavi" for="chk_toavi">avi格式</label>
          </td>
        </tr>
        <tr><td height="5" colspan="2"></td></tr>
        <tr>
          <td class="field_title">录像分段</td>
          <td class="field_title">
         	 <input type="checkbox" checked="checked" id="chk_splitvod" onClick="DoSetSplitVodInfo();"/><label id="lbl_splitvod" for="chk_splitvod">每段
             <input type="text" id="txt_splitmin" value="2" size="4" maxlength="3" style="ime-mode:Disabled;" onBlur="CheckSplitTime();">分钟[0-200]</label>
          </td>
        </tr>
        <tr><td height="8" colspan="2"></td></tr>
        <tr>
          <td class="field_title">下载操作</td>
          <td>
          <a title="下载检索结果中选择的时间段录像" href="javascript: void(0);" id="vod_download" onClick="StartDownload();"><img src="images/voddownload.png" onMouseDown="DoOnMDown(this, 'voddownload2.png');" onMouseUp="DoOnMDown(this, 'voddownload.png')"/></a>
            &nbsp;
          <a title="停止录像下载" href="javascript: void(0);" id="vod_downloadstop" onClick="StopDownload();"><img src="images/voddownloadstop.png" onMouseDown="DoOnMDown(this, 'voddownloadstop2.png');" onMouseUp="DoOnMDown(this, 'voddownloadstop.png')"/></a>
          </td>
        </tr>
        <tr>
          <td class="field_title" colspan="2">
         	 <span id="span_download_rate" style="display:none"></span>
          </td>
        </tr>
      </table>
      </div>
      </div>
    </td>
  </tr>
</table>

<!-- 提交给vod.php的表单 -->
<form name="form_camdata" id="form_camdata" action="vod.php" method=POST>
    <input type="hidden" id="hd_camname" name="hd_camname" value=""/>
    <input type="hidden" id="hd_camid" name="hd_camid" value=""/>
    <input type="hidden" id="hd_sfkk" name="hd_sfkk" value=""/>
    <input type="hidden" id="hd_download" name="hd_download" value=""/>
</form>

<script language="javascript" for="vodplayer" event="OnGetVodList(sType,info)">	
	//document.write(info);
	OnGetVodHistory(info);
</script>
<script language="javascript" for="vodplayer" event="OnNoteVodStart(info)">	
	//document.write(info);
	OnNoteVodStart(info);
</script>
<script language="javascript" for="vodplayer" event="OnGetVodPos(info)">
	//document.write(info);
	OnGetVodPos(info);
</script>
<script language="javascript" for="vodplayer" event="OnDownloadStart(info)">
	//document.write(info);
	OnNoteDownloadStart(info);
</script>
<script language="javascript" for="vodplayer" event="OnDownloadEnd(info)">
	//document.write(info);
	OnNoteDownloadEnd(info);
</script>
<script language="javascript" for="vodplayer" event="OnNoteVodEnd(info)">
	//document.write(info);
	OnNoteVodEnd(info);
</script>
<script language="javascript" type="text/javascript">
window.onunload=function(event){
	var ret = vodplayer.Logout(1);
	//alert( "vod: logout! ret=" + ret);
}
</script>

</body>
</html>
