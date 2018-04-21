<!--<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\" <html xmlns="http://www.w3.org/1999/xhtml">-->
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>MVP平台-摄像机巡检</title>
<link rel="shortcut icon" type="image/x-icon" href="images/mingding-v3.gif" />

<link rel="stylesheet" href="style/conn.css" type="text/css">
<link rel="stylesheet" href="style/doc.css" type="text/css">
<link rel="stylesheet" href="style/dtree.css" type="text/css" />
<!--<script defer type="text/javascript" src="js/pngfix.js"></script>
<script type="text/javascript" src="js/zDrag.js"></script>
<script type="text/javascript" src="js/zDialog.js"></script>-->
<script defer type="text/javascript" src="js/function.js"></script>
<script defer type="text/javascript" src="js/basic.js"></script>
<script defer type="text/javascript" src="js/dtree.js"></script>
<script defer type="text/javascript" src="js/scan.js"></script>

<script type="text/javascript">

function onMyload()
{
	// 前期处理
	
	// 后续处理
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
<?php 
/*
<input type="hidden" name="f_scan_cam" id="f_camlist" value="" />
<input type="hidden" name="f_scan_name" id="f_scan_name" value="" />
<input type="hidden" name="f_scan_id" id="f_scan_id" value="" />
<input type="hidden" name="f_scan_loop" id="f_scan_loop" value="" />
<input type="hidden" name="f_scan_time" id="f_scan_time" value="" />
<input type="hidden" name="f_scan_type" id="f_scan_type" value="" />
 */
header("Content-Type: text/html; charset=utf-8");
$str_scan_camids = "";
$arr_scan_camid = array();
$str_scan_name = "";
$scan_id = 0;
$scan_time = 0;
$scan_type = 0;
$scan_loop = 0;
$str_scan_server = "";
$str_scan_user = "";
$bPost = false;

// 巡检下属相机列表
if(isset($_POST['f_scan_cam']))
{
    $str_scan_camids = $_POST['f_scan_cam'];
    $arr_scan_camid = explode(";", $str_scan_camids);
    $bPost = true;
}
else
{
    $bPost = false;
}
// 巡检名称
if(isset($_POST['f_scan_name']))
{
    $str_scan_name = $_POST['f_scan_name'];
    $bPost = true;
}
else
{
    $bPost = false;
}
// 巡检ID
if(isset($_POST['f_scan_id']))
{
    $scan_id = $_POST['f_scan_id'];
    $bPost = true;
}
else
{
    $bPost = false;
}
// 巡检的循环次数
if(isset($_POST['f_scan_loop']))
{
    $scan_loop = $_POST['f_scan_loop'];
    $bPost = true;
}
else
{
    $bPost = false;
}
// 巡检的相机执行间隔时间
if(isset($_POST['f_scan_time']))
{
    $scan_time = $_POST['f_scan_time'];
    $bPost = true;
}
else
{
    $bPost = false;
}
// 巡检操作类型
if(isset($_POST['f_scan_type']))
{
    $scan_type = $_POST['f_scan_type'];
    $bPost = true;
}
else
{
    $bPost = false;
}
// 操作用户名称
if(isset($_POST['f_scan_user']))
{
    $str_scan_user = $_POST['f_scan_user'];
    $bPost = true;
}
else
{
    $bPost = false;
}
// mvp服务器地址
if(isset($_POST['f_scan_svr']))
{
    $str_scan_server = $_POST['f_scan_svr'];
    $bPost = true;
}
else
{
    $bPost = false;
}

if($bPost)
{
    // 获取时间作为ID
    $time_id = time();
    // 处理巡检设置 1-新增  2-删除  3-更新
    $file = "scan\\".$str_scan_user.".xml";
    $dom = new DOMDocument("1.0", "UTF-8");
    
    // 加载当前用户的巡检配置文件
    if( ! file_exists($file) )
    {
        //header("Content-Type:text/plain");
        $root= $dom->createElement("config");
        $dom->appendChild($root);
        $dom->save($file);
    }
    if( ! $dom->load($file) )
    {
        $root= $dom->createElement("config");
        $dom->appendChild($root);
        $dom->save($file);
        $dom->load($file);
    }
    $dom->formatOutput = true;
    $root = $dom->getElementsByTagName("config")->item(0);
    // 先找到获取相关节点
    $scan_nodes =$root->getElementsByTagName("ScanSetting");
    $bFindScanNode = false;
    for($i=0; $i<$scan_nodes->length; $i++)
    {
        $scan_node = $scan_nodes->item($i);
        $mvpserver = $scan_node->getElementsByTagName("MvpServerAddr");
        // 匹配到服务器地址
        if($mvpserver->item(0)->nodeValue == $str_scan_server)
        {
            $bFindScanNode = true;
            break;
        }
    }
    if($bFindScanNode)
    {
        // 先检查有无此ID或者名称的节点
        $bFindScanItem = false;
        $scanitems = $scan_node->getElementsByTagName("ScanItem");
        for($i=0; $i<$scanitems->length; $i++)
        {
            $scanitem = $scanitems->item($i);
            $scanID = $scanitem->getElementsByTagName("ID");
            $scanName = $scanitem->getElementsByTagName("szName");
            // 匹配到服务器地址
            if($scanID->item(0)->nodeValue == $scan_id ||
                $scanName->item(0)->nodeValue == $str_scan_name)
            {
                $bFindScanItem = true;
                break;
            }
        }           
        // 如果查找到此节点，先删除
        if($bFindScanItem)
        {
            $scanitem->parentNode->removeChild($scanitem);
        }
        if ($scan_type == 1 || $scan_type == 3) // 新增  更新
        {
            $newitem = $dom->createElement("ScanItem");
            $newname = $dom->createElement("szName", $str_scan_name);
            $newitem->appendChild($newname);
            $new_scan_id = ($scan_type == 1) ? $time_id : $scan_id;
            $newid = $dom->createElement("ID", $new_scan_id);
            $newitem->appendChild($newid);
            $newinterval = $dom->createElement("Interval", $scan_time);
            $newitem->appendChild($newinterval);
            $newloop = $dom->createElement("LoopCount", $scan_loop);
            $newitem->appendChild($newloop);
            $newscancams = $dom->createElement("ScanCameras");
            $newitem->appendChild($newscancams);
            for($i=0; $i<count($arr_scan_camid); $i++)
            {
                $newscancam = $dom->createElement("ScanCamera");
                $newscancamid = $dom->createElement("CameraId", $arr_scan_camid[$i]);
                $newscancam->appendChild($newscancamid);
                $newscancams->appendChild($newscancam);
            }
            $scan_node->appendChild($newitem);
        }
        if($scan_type == 2)
        {
            //如果此节点下无巡检数据，则删除节点
            $scanitems = $scan_node->getElementsByTagName("ScanItem");
            if($scanitems->length <= 0)
            {
                $scan_node->parentNode->removeChild($scan_node);
            }
        }
    }
    else 
    {
        if ($scan_type == 1 || $scan_type == 3) // 新增  更新
        {
            $newnode = $dom->createElement("ScanSetting");
            $newserver = $dom->createElement("MvpServerAddr", $str_scan_server);
            $newnode->appendChild($newserver);
            
            $newitem = $dom->createElement("ScanItem");
            $newnode->appendChild($newitem);
            $newname = $dom->createElement("szName", $str_scan_name);
            $newitem->appendChild($newname);
            $newid = $dom->createElement("ID", $time_id);
            $newitem->appendChild($newid);
            $newinterval = $dom->createElement("Interval", $scan_time);
            $newitem->appendChild($newinterval);
            $newloop = $dom->createElement("LoopCount", $scan_loop);
            $newitem->appendChild($newloop);
            $newscancams = $dom->createElement("ScanCameras");
            $newitem->appendChild($newscancams);
            for($i=0; $i<count($arr_scan_camid); $i++)
            {
                $newscancam = $dom->createElement("ScanCamera");
                $newscancamid = $dom->createElement("CameraId", $arr_scan_camid[$i]);
                $newscancam->appendChild($newscancamid);
                $newscancams->appendChild($newscancam);
            }
            $root->appendChild($newnode);
            $dom->appendChild($root);
        }
    }
    $dom->formatOutput = true;
    $dom->save($file);
}
?>
</head>
<body  onLoad="onMyload()" onResize="onWebResize()" style="background:#FFF;">
<table width="100%" height="100%" border="0">
  <tr>
    <td align="left" valign="top" width="280">
	  <script type='text/javascript' src="js/dtree.js"></script>
	  <script type='text/javascript' src="js/scan.js"></script>
      <script type="text/javascript">
	    setTimeout('ShowCameraListLoop()', 1000);
      </script>
      <div class="dtree" style="width:280px; overflow:auto;" id="div_cameralist">
        <img src="images/loading_20.gif">正在加载摄像机列表...
      </div>
    <div id="div_camtools" style="width:280px; overflow:auto;">
    <table width="100%">
    <tr>
    <td width="100%">
    <a class="openclose" href="javascript: void(0);" id="a_openclose" title="展开全部列表" onClick="DoOpenClose(1);"><img src="images/dtree/folderopen.gif"></a>&nbsp;
    <a class="openclose" href="javascript: void(0);" id="a_nodeselect" title="选择全部" onClick="DoNodeAllSelect(1);"><img src="images/dtree/check.gif"></a>&nbsp;&nbsp;
    
    <input type="text" id="txt_searchcam" style="width:120px; height:24px; font-size:12px;" onChange="CamSearchChanged();"/>
    <a class="openclose" href="javascript: void(0);" id="a_camsearch" title="搜索摄像机" onClick="DoCamSearch();"><img src="images/cam_search.png" onMouseDown="DoOnMDown(this, 'cam_search2.png');" onMouseUp="DoOnMDown(this, 'cam_search.png')"></a>
    </td>
    </tr>
    <tr><td height="2" style="background:#808080"></td></tr>
    <tr><td height="5"></td></tr>
    </table>
    </div>
    <div id="div_seleced_cameralist" class="show_selected_cam" style="width:280px; overflow:auto;">
      <script type="text/javascript">
	    dd = new dTree('dd', false, false);
		dd.add(0, -1, "已选择的摄像机 [共 0 个]", "", "javascript: void(0);");
		document.write(dd);
      </script>
    </div>
    </td>
    <td width="2" style="background:#808080">
    <td>
    <td align="left" valign="top">
      <table width="100%" border="0">
        <tr>
          <!-- 顶部信息栏区域 --> 
          <td height="30"  align="left" valign="middle" >
          <span class="popup_title">视频巡检方案：</span>
          <select class="scan_list" id="scan_list" onChange="ChangeSelectedScan();">
          <option value="0">--------------------请选择一个巡检方案--------------------</option>
          </select>
          &nbsp;&nbsp;&nbsp;
          <a class="openclose" href="javascript: void(0);" id="a_updatescan" title="编辑当前选择的巡检方案" onClick="UpdateScan(3);"><img src="images/update_scan.gif" onMouseDown="DoOnMDown(this, 'update_scan2.png');" onMouseUp="DoOnMDown(this, 'update_scan.gif')"/>更新</a>
          &nbsp;
          <a class="openclose" href="javascript: void(0);" id="a_addscan" title="增加新的的巡检方案" onClick="UpdateScan(1);"><img src="images/add_scan.gif" onMouseDown="DoOnMDown(this, 'add_scan2.png');" onMouseUp="DoOnMDown(this, 'add_scan.gif')"/>新增</a>
          &nbsp;
          <a class="openclose" href="javascript: void(0);" id="a_delscan" title="删除当前选择的巡检方案" onClick="UpdateScan(2);"><img src="images/del_scan.gif" onMouseDown="DoOnMDown(this, 'del_scan2.png');" onMouseUp="DoOnMDown(this, 'del_scan.gif')"/>删除</a>
          &nbsp;
          </td>
        </tr>
        <tr>
          <!-- 视频播放区域 --> 
          <td align="left" valign="top">
            <OBJECT
            id="scan_player"
            classid="clsid:{97037AFC-8D9C-47D6-8D4E-1CF7B96C7A7D}" 
            codebase="BocomSDK.ocx#version=1,0,0,0" 
            width=100% 
            height=300
            align=center 
            hspace=0 
            vspace=0
            >
            </OBJECT>
          </td>
        </tr>
        <tr>
          <!-- 控制操作栏区域 --> 
          <td height="30" align="left" valign="middle">
          <table width="100%" border="0">
            <tr>
            <td width="100">
            <span class="popup_title">巡检控制：</span>
            </td>
            <td width="140">
            <input type="button" id="btn_operate" value="启 动"  onClick="DoScan(1);" />
            &nbsp;&nbsp;
            <input type="button" id="btn_stop" value="停 止" onClick="DoScan(0);"/>
            </td>
            <td  width="140">
            &nbsp;<span class="tip_text2" id="span_cur_cam_tip"></span>
            </td>
            <td>
            &nbsp;<span class="tip_text2" id="span_cur_cam"></span>
            </td>
            </tr>
          </table>
          </td>
        </tr>
        <tr>
          <td  height="2" style="background:#808080"></td>
        </tr> 
        <tr>
          <!-- 底部设置栏区域 --> 
          <td  height="120" align="left" valign="top"  class="field_title">
          	<span class="popup_title">巡检方案设置：</span>
            <span class="tip_text2" id="span_scan_op_status"></span>
            <br/><br/>
            <table width="100%" border="0"><tr>
            <td width="100px" class="field_title">
            <label >巡检方案名称：</label>
            </td>
            <td width="240px" class="field_title">
            <input type="text" style="display:none;"size="32" maxlength="24" id="txt_scan_name" onKeyUp="return CheckScanName(this);" onBlur="CheckScanName2(this)" value="">
            <label id="label_scan_name"></label>
            </td>
            <td width="200px" class="field_title">
            <label>摄像机轮巡间隔：</label>
            <select style="width:70px;" id="sel_scan_time">
            <option value="10">10秒</option>
            <option value="15">15秒</option>
            <option value="20">20秒</option>
            <option value="25">25秒</option>
            <option value="30">30秒</option>
            <option value="60">1分钟</option>
            <option value="120">2分钟</option>
            <option value="180">3分钟</option>
            <option value="240">4分钟</option>
            <option value="300">5分钟</option>
            </select>
            </td>
            <td class="field_title">
            <a class="openclose" href="javascript: void(0);" style="display:none;" id="a_confirmscan" title="保存巡检方案" onClick="SaveScan();"><img src="images/confirm_scan.gif" onMouseDown="DoOnMDown(this, 'confirm_scan2.png');" onMouseUp="DoOnMDown(this, 'confirm_scan.gif')"/>保存</a>
            &nbsp;
            <a class="openclose" href="javascript: void(0);" style="display:none;" id="a_cancelscan" title="取消" onClick="CancelScan();"><img src="images/cancel_scan.gif" onMouseDown="DoOnMDown(this, 'cancel_scan2.png');" onMouseUp="DoOnMDown(this, 'cancel_scan.gif')"/>取消</a>
            </td>
          </tr></table>
          </td>
        </tr>
      </table>
	</td>
  </tr>
</table>
<form  name="form_scandata" id="form_scandata" action="scan.php" method=POST>
<input type="hidden" name="f_scan_cam" id="f_scan_cam" value="" />
<input type="hidden" name="f_scan_id" id="f_scan_id" value="" />
<input type="hidden" name="f_scan_name" id="f_scan_name" value="" />
<input type="hidden" name="f_scan_loop" id="f_scan_loop" value="" />
<input type="hidden" name="f_scan_time" id="f_scan_time" value="" />
<input type="hidden" name="f_scan_type" id="f_scan_type" value="" />
<input type="hidden" name="f_scan_svr" id="f_scan_svr" value="" />
<input type="hidden" name="f_scan_user" id="f_scan_user" value="" />
</form>

<script language="javascript" type="text/javascript">
window.onunload=function(event){
	//alert("logout event");
	var ret = scan_player.Logout(1);
	//alert( "logout! ret=" + ret);
}
</script>


</body>
</html>
