var g_cam_id=0;
var g_cam_name="";
var g_svr_ip="";
var g_svr_ip_ex="";
var g_svr_port=0;
var g_svr_port_ex=0;
var g_usr_name="";
var g_usr_name_ex="";
var g_usr_pass="";
var g_usr_pass_ex="";
var g_bPtzControl=0; //是否可控PTZ，0-不可控1-可控
var g_download_level=0;//下载权限级别，-1表示不能下载
var g_vod_sel=new Array();
var g_vod_playing = false;
var g_stop_vod = false;
var g_last_vod_type=0;//VOD播放控制: 1-正常播放 2-暂停  3-倒放 4-单帧正播 5-单帧倒播 其他
var g_vod_speed=1.0;
var g_window_width=0;
var g_window_heigh=0;
var g_is_download = false;
var g_is_stop_download = false;

// 外部url传入的get值：vod开始、结束时间
var g_vod_stime = "";// vod开始时间
var g_vod_etime = "";// vod结束时间
var g_b_start_vod_query = false; // 是否自动查询录像，当外部传入vod开始结束时间时变为true

//外部传入的用户信息，用于记录日志，20180320
var g_log_userid=""; // 用户ID
var g_log_username="";// 用户名称
var g_log_clientip="";//用户IP
var g_log_groupid="";//用户部门ID
var g_log_groupname="";//用户部门名称
var g_b_log = false; 

// 获取的VOD历史视频数据
/*
结果如下：
g_vodlist={
	[0]={start="2015-10-20 11:40:37", end="2015-10-20 12:40:37"},
	[1]={start="2015-10-20 14:40:37", end="2015-10-20 15:40:37"},
	...
}
*/
var g_vodlist = new Array();

// 相机列表数据
var g_arr_camname = new Array();
var g_arr_camid = new Array();
var g_arr_camsfkk = new Array();
var g_arr_camdownload = new Array();
// 是否已登陆
var g_b_login = false;


function GotoLiveWeb()
{
	if(g_is_download == true)
	{
		alert("请等待下载任务结束！");
		return;
	}
	if(g_vod_playing == true)
	{
		//alert("请先停止录像播放！");
		document.getElementById("a_goto_live").innerHTML = "正在停止录像播放，请稍等...";
		document.getElementById("a_goto_live").onclick = "";
		EndVod();
		setTimeout('GotoLiveWeb()', 100); 
		return;
	}
	
	var page = "";
	if(g_b_log)
    {
	    page = 'live.php?' + 'camid=' + g_cam_id + '&userId=' + g_log_userid + '&userName=' + g_log_username + 
               '&ClientIp=' + g_log_clientip + '&groupId=' + g_log_groupid + '&groupName=' + g_log_groupname;
    }
	else
	{
	    page = 'live.php?' + 'camid=' + g_cam_id;
	    // + '&camname=' + g_cam_name + '&sfkk=' + g_bPtzControl + '&download=' + g_download_level;
	}
	
	var liveWin;
	liveWin = window.open (
			 page,
			 "_self",
			 'resizable=yes,menubar=no,location=no,status=no,scrollbars=no'
	);
    var doc = document.form_camdata; 
    doc.target = liveWin.name; 
    doc.action = page; 
    doc.submit(); 
    liveWin.focus();
}

function Login()
{
	var info='<\?xml version="1.0"\?><doc><members>';
	info += '<ServerIP>' + g_svr_ip + '</ServerIP>';       //IP
	info += '<ServerPort>' + g_svr_port + '</ServerPort>'; //端口号
	info += '<UserName>' + g_usr_name + '</UserName>';     //用户名
	info += '<Password>' + g_usr_pass + '</Password>';     //密码
	info += '</members></doc>';
	var ret = vodplayer.Logon(1, info);
	if(ret != 1)
	{
		//alert("登录视频服务器[" + g_svr_ip + "]，失败!");
		// 尝试另一个地址信息
		var infoEx='<\?xml version="1.0"\?><doc><members>';
		infoEx += '<ServerIP>' + g_svr_ip_ex + '</ServerIP>';      //IP
		infoEx += '<ServerPort>' + g_svr_port_ex + '</ServerPort>';    //端口号
		infoEx += '<UserName>' + g_usr_name_ex + '</UserName>';           //用户名
		infoEx += '<Password>' + g_usr_pass_ex + '</Password>';          //密码
		infoEx += '</members></doc>';
		var retEx = vodplayer.Logon(1, infoEx);
		if(retEx != 1)
		{
			alert("登录视频服务器[" + g_svr_ip_ex + "]，失败!");
		}
		else
		{
			g_b_login = true;
		}
		return retEx;
	}
	else
	{
		g_b_login = true;
	}
	return ret;
}
function GetConfigInfo()
{
	var dom;
	dom = new ActiveXObject("Microsoft.XMLDOM");
	dom.async = false;                           
	dom.load("config.xml");
	if(dom)
	{
		var node;
		var n_config = dom.getElementsByTagName('config/ServerConfigMember');
		var n_enable = dom.getElementsByTagName('config/ServerConfigMember/Enabled');
		var n_svrip = dom.getElementsByTagName('config/ServerConfigMember/SvrIP');
		var n_port = dom.getElementsByTagName('config/ServerConfigMember/SvrPort');
		var n_username = dom.getElementsByTagName('config/ServerConfigMember/User');
		var n_userpass = dom.getElementsByTagName('config/ServerConfigMember/Pass');
		
		var n_svrip_ex = dom.getElementsByTagName('config/ServerConfigMember/SvrIPEx');
		var n_port_ex = dom.getElementsByTagName('config/ServerConfigMember/SvrPortEx');
		var n_username_ex = dom.getElementsByTagName('config/ServerConfigMember/UserEx');
		var n_userpass_ex = dom.getElementsByTagName('config/ServerConfigMember/PassEx');
		
		if(n_config.length<=0)
		{
			return false;
		}
		for(var i=0; i<n_config.length;i++)
		{
			var enabled = parseInt(n_enable[i].childNodes[0].text);
			if(enabled==0)
			{
				continue;
			}
			g_svr_ip   = n_svrip[i].childNodes[0].text;
			g_svr_port = parseInt(n_port[i].childNodes[0].text);
			g_usr_name = n_username[i].childNodes[0].text;
			g_usr_pass = n_userpass[i].childNodes[0].text;
			
			g_svr_ip_ex   = n_svrip_ex[i].childNodes[0].text;
			g_svr_port_ex = parseInt(n_port_ex[i].childNodes[0].text);
			g_usr_name_ex = n_username_ex[i].childNodes[0].text;
			g_usr_pass_ex = n_userpass_ex[i].childNodes[0].text;
			return true;
		}
	}
	return false;
}
function SetCamList()
{
	var obj_camlist = document.getElementById("camera_list");
	var obj_hd_camname = document.getElementById("hd_camname");
	var obj_hd_camid = document.getElementById("hd_camid");
	var obj_hd_sfkk = document.getElementById("hd_sfkk");
	var obj_hd_download = document.getElementById("hd_download");
	if(obj_hd_camid.value=="" || obj_hd_camname.value=="")
	{
		// 未收到POST数据，关闭窗口（防止有些浏览器弹出两个窗体，其中一个没有收到POST数据）
		//alert("NO POST, CLOSED!!!");
		//window.opener=null;
		//window.close();
	}
	g_arr_camname = obj_hd_camname.value.split(";");
	g_arr_camid = obj_hd_camid.value.split(";");
	g_arr_camsfkk = obj_hd_sfkk.value.split(";");
	g_arr_camdownload = obj_hd_download.value.split(";");
	if(g_arr_camname.length != g_arr_camid.length || g_arr_camname.length==0)
	{
		alert("未获取到有效的相机列表！");
		return false;
	}
	
	if(g_cam_id == 0)
	{
		g_cam_id = g_arr_camid[0];
		g_cam_name = g_arr_camname[0];
		g_bPtzControl = parseInt(g_arr_camsfkk[0]);
		g_download_level = parseInt(g_arr_camdownload[0]);
	}

	for(var i=0; i<g_arr_camname.length;i++)
	{
		var v_camid = parseInt(g_arr_camid[i]);
		var oOption=document.createElement("option");
		oOption.setAttribute("value", g_arr_camid[i]);
		oOption.setAttribute("title", g_arr_camname[i]);
		var oText=document.createTextNode("[" + g_arr_camid[i] + "] " + g_arr_camname[i]);//[1001] 摄像机名称
		oOption.appendChild(oText);
		obj_camlist.appendChild(oOption);

		if(v_camid == g_cam_id)
		{
			obj_camlist.options[i].selected=true;
			g_cam_name = g_arr_camname[i];
			g_bPtzControl = parseInt(g_arr_camsfkk[i]);
			g_download_level = parseInt(g_arr_camdownload[i]);
		}
	}
	return true;
}
function onload()
{
	// 隐藏检索结果界面
	document.getElementById("div_vod_result1").style.display="none";
	document.getElementById("div_vod_result2").style.display="none";
	vodplayer.InitOcxType(2);
	var args = new Array();
	args = getArgs();
	g_cam_id = parseInt(args['camid']); // 相机ID
	if(isNaN(g_cam_id))
	{
		g_cam_id = 0;
	}
	
	//if(args['starttime'] == undefined || args['endtime'] == undefined)
	if(!args['starttime'] || !args['endtime'])
    {
	    g_vod_stime = "";
	    g_vod_etime = "";
    }
	else
	{
	    g_vod_stime = args['starttime']; // VOD开始时间
	    g_vod_etime = args['endtime']; // VOD结束时间
	    g_b_start_vod_query = true; //生效自动检索录像
	}
    if(!args['userId'] || !args['userName'] || !args['ClientIp'] || 
       !args['groupId'] || !args['groupName'])
    {
        g_log_userid = "";
        g_log_username = "";
        g_log_clientip = "";
        g_log_groupid = "";
        g_log_groupname = "";
        g_b_log = false;
    }
    else
    {
        g_log_userid = args['userId'];
        g_log_username = args['userName'];
        g_log_clientip = args['ClientIp'];
        g_log_groupid = args['groupId'];
        g_log_groupname = args['groupName'];
        g_b_log = true;
    }
	
	// 设置相机下拉列表
	if ( SetCamList() == false )
	{
		return false;
	}

	//document.getElementById("span_cam_info").innerHTML = "MVP视频点播：[" + g_cam_id + "]" + g_cam_name;

	if( GetConfigInfo() != true)
	{
		alert("加载配置文件失败，无法连接服务器!");
		return false;
	}
	//window.resizeTo(810, 572);
	//g_window_width = window.outerWidth;
	//g_window_heigh = window.outerHeight;
	g_window_width = 810;
	g_window_heigh = 572;
	//alert("width:"+g_window_width + "  height:"+g_window_heigh);
	onWebResize();
	SetVodStartTime();
	SetVodEndTime();
	// 等待控件加载完毕在执行接口调用
	setTimeout("Login()", 100);
	
	// 自动检索录像
	if(g_b_start_vod_query == true)
    {
	    setTimeout("StartVodQurey(false)", 600);
    }
}

function onWebResize()
{
	var obj_vodplayer = document.getElementById("vodplayer");
	//var width = window.innerWidth;
	//var heigh = window.innerHeight;
	var width = document.body.clientWidth;
	var heigh = document.body.clientHeight;
	/////////////////////////////////////////////////
	// edited 2015-11-21
	var w = width - 274;
	var h = heigh - 80;
	if(w<240)
	{
		w = 240;
	}
	if(h<180)
	{
		h = 180;
	}
	obj_vodplayer.style.width = w;
	obj_vodplayer.style.height = h;
	
	return;
	/////////////////////////////////////////////////
	var w = width - g_window_width;
	var h = heigh - g_window_heigh;
	
	if( w<=20 || h<=20)
	{
		//return;
	}
	var obj_w = w + 480;
	var obj_h = h + 360;
	if(obj_w<360)
	{
		obj_w = 360;
	}
	else if(obj_w>1024)
	{
		//obj_w=1024;
	}
	
	if(obj_h<270)
	{
		obj_h = 270;
	}
	else if(obj_h>768)
	{
		//obj_h=768;
	}
	
	obj_vodplayer.style.width = obj_w;
	obj_vodplayer.style.height = obj_h;
}
function ChangeVodCam()
{
	var obj_camlist = document.getElementById("camera_list");
	var obj_switch_status = document.getElementById("span_switch_status");
	
	// 关闭下载
	if(g_is_download == true)
	{
		alert("请等待下载任务结束！");
		// 重新选择之前的项目
		for(var i=0; i<obj_camlist.options.length; i++)
		{
			if(parseInt(obj_camlist.options[i].value) == g_cam_id)
			{
				obj_camlist.options[i].selected=true;
				break;
			}
		}
		// 控制select失去焦点
		obj_camlist.blur();
		return;
	}
	// 关闭之前的点播
	if(g_vod_playing == true)
	{
		//alert("请先停止录像播放！");
		obj_switch_status.innerHTML = "正在切换中...";
		EndVod();
		setTimeout('ChangeVodCam()', 100); 
		return;
	}

	var index_camlist = obj_camlist.selectedIndex;
	if(obj_camlist.selectedIndex < 0)
	{
		index_camlist = 0;
	}

	g_cam_id = parseInt(g_arr_camid[index_camlist]);
	g_cam_name = g_arr_camname[index_camlist];
	g_bPtzControl  = g_arr_camsfkk[index_camlist]; 
	g_download_level = g_arr_camdownload[index_camlist];
	onWebResize();
	obj_switch_status.innerHTML = "";
	//
	ClearOldVodQureyResult();
	SetVodStartTime();
	SetVodEndTime();
}
function SetLocalTime(type)
{
	var obj_time;
	if(type=="start")
	{
		obj_time = document.getElementById("start_t");
	}
	else if( type=="end")
	{
		obj_time = document.getElementById("end_t");
	}
	else
	{
		return false;
	}
    var today = new Date();
    var yearNow = today.getFullYear();
    var monthNow = today.getMonth()+1;
    var dateNow = today.getDate();
    var hourNow = today.getHours();
    var minNow = today.getMinutes();
    var secNow = today.getSeconds();

    if(monthNow<10) monthNow = "0" + monthNow;
    if(dateNow<10) dateNow = "0" + dateNow;
    if(hourNow<10) hourNow = "0" + hourNow;
    if(minNow<10) minNow = "0" + minNow;
    if(secNow<10) secNow = "0" + secNow;

    var time_str = yearNow + "-" + monthNow + "-" + dateNow + " "
    			   + hourNow + ":" + minNow + ":" + secNow;

    obj_time.value = time_str;
    return true;
}
// 设置VOD开始时间（网页加载时）
function SetVodEndTime()
{
    var str_etime = "";
    if(g_vod_etime == "")
    {
        var today = new Date();
        var yearNow = today.getFullYear();
        var monthNow = today.getMonth()+1;
        var dateNow = today.getDate();
        var hourNow = today.getHours();
        var minNow = today.getMinutes();
        var secNow = today.getSeconds();
    
        if(monthNow<10) monthNow = "0" + monthNow;
        if(dateNow<10) dateNow = "0" + dateNow;
        if(hourNow<10) hourNow = "0" + hourNow;
        if(minNow<10) minNow = "0" + minNow;
        if(secNow<10) secNow = "0" + secNow;
    
        str_etime = yearNow + "-" + monthNow + "-" + dateNow + " "
        			   + hourNow + ":" + minNow + ":" + secNow;
    }
    else
    {
        str_etime = g_vod_etime;
    }

	obj_etime = document.getElementById("end_t");
	obj_etime.value = str_etime;
    return true;
}
// 设置VOD开始时间（网页加载时）
function SetVodStartTime()
{
    var str_stime = "";
    if(g_vod_stime == "")
    {
        var today = new Date();
    	var OneHourAgo = today.getTime() - 3600*1000;
    	var tmpToday = new Date(OneHourAgo);
        var year = tmpToday.getFullYear();
        var month = tmpToday.getMonth()+1;
        var date = tmpToday.getDate();
        var hour = tmpToday.getHours();
        var min = tmpToday.getMinutes();
        var sec = tmpToday.getSeconds();
    
        if(month<10) month = "0" + month;
        if(date<10) date = "0" + date;
        if(hour<10) hour = "0" + hour;
        if(min<10) min = "0" + min;
        if(sec<10) sec = "0" + sec;
    
        str_stime = year + "-" + month + "-" + date + " "
        			   + hour + ":" + min + ":" + sec;
    }
    else
    {
        str_stime = g_vod_stime;
    }
	obj_stime = document.getElementById("start_t");
	obj_stime.value = str_stime;
    return true;
}
function ClearOldVodQureyResult()
{
	//删除之前的检索结果，并隐藏结果控件
	var obj_vodrlt=document.getElementById("vod_result_list");
    var i;
    for(i=obj_vodrlt.options.length-1;i>=0;i--){
       var oOption=obj_vodrlt.options[i];
       obj_vodrlt.removeChild(oOption);
    }

	var obj_span_vod_ret=document.getElementById("span_vod_ret");
	obj_span_vod_ret.innerHTML="";
	document.getElementById("div_vod_result1").style.display="none";
	document.getElementById("div_vod_result2").style.display="none";
	//清空之前的检索结果
	g_vodlist = [];
	g_vod_sel = [];
}
function StartVodQurey(bShowMsg)
{
	if(g_b_login != true)
	{
		if(bShowMsg)
		    alert("未登录视频服务器！")
		return;
	}
	// 如果正在下载，则直接返回
	if( g_is_download == true )
	{
	    if(bShowMsg)
	        alert("请等待下载结束！");
		return;
	}
	//删除之前的检索结果，并隐藏结果控件
	ClearOldVodQureyResult();

	obj_stime = document.getElementById("start_t");
	obj_etime = document.getElementById("end_t");
	//alert("开始录像检索:\n开始: " + obj_stime.value + "\n结束: " + obj_etime.value);
	var info;
	info = '<\?xml version="1.0"\?><doc><members>';
	info += '<cameraid>' + g_cam_id + '</cameraid>';
	info += '<startTime>' + obj_stime.value + '</startTime>';
	info += '<stopTime>' + obj_etime.value + '</stopTime>';
	info += '<queryCondition>' + 0 + '</queryCondition>';  //0为IPSAN  1为设备  2为PCNVR
	//info += '<LogonHandle>' + logonHandle.value + '</LogonHandle>';//登陆成功后返回的句柄。
	info += '</members></doc>';
	//alert(info);
	// vod 检索状态提示
	var obj_span_vodsearch = document.getElementById("span_vodsearch");
	obj_span_vodsearch.innerHTML = "正在检索中...<img src='images/loading.gif'>";
	obj_span_vodsearch.style.display = "";
	
	var ret = vodplayer.GetVodList(1, info);
	return ret;
}
function OnGetVodHistory(xmlinfo)
{
	// vod 检索状态提示
	var obj_span_vodsearch = document.getElementById("span_vodsearch");
	obj_span_vodsearch.innerHTML = "";
	obj_span_vodsearch.style.display = "none";
	
	var obj_span_vod_ret=document.getElementById("span_vod_ret");
	obj_span_vod_ret.innerHTML="";
	var obj_vodrlt=document.getElementById("vod_result_list");
	//alert(xmlinfo);
	var vod_list_count=0;
	var dom;
	dom = new ActiveXObject("Microsoft.XMLDOM");   //实例化dom对象
	dom.async = false;                           
	dom.loadXML(xmlinfo);                       //加载xml文件
	
	if(dom)
	{
		var node;
		var n_member = dom.getElementsByTagName('doc/members/member');
		var n_camid = dom.getElementsByTagName('doc/members/member/cameraid');
		var n_stime = dom.getElementsByTagName('doc/members/member/StartTime');
		var n_etime = dom.getElementsByTagName('doc/members/member/EndTime');
		if(n_member.length<=0)
		{
			document.getElementById("div_vod_result1").style.display="block";
			document.getElementById("div_vod_result2").style.display="none";
			return false;
		}
		for(var i=0; i<n_member.length;i++)
		{
			var v_camid = parseInt(n_camid[i].childNodes[0].text);
			if(v_camid != g_cam_id)
			{
				continue;
			}
			var v_stime = n_stime[i].childNodes[0].text;
			var v_etime = n_etime[i].childNodes[0].text;
			var vod_list_item = v_stime + " 至 " + v_etime;
			var oOption=document.createElement("option");
			oOption.setAttribute("value", i);
			oOption.setAttribute("title", vod_list_item);
			var oText=document.createTextNode(vod_list_item);
			oOption.appendChild(oText);
			obj_vodrlt.appendChild(oOption);
			vod_list_count++;
			g_vodlist.push({start:v_stime,end:v_etime});
		}
	}
	obj_vodrlt.options[0].selected=true;
	g_vod_sel.push(0);
	obj_span_vod_ret.innerHTML="检索到 " + vod_list_count + " 条结果。";
	
	document.getElementById("div_vod_result1").style.display="none";
	if(g_download_level != -1)
	{
		document.getElementById("div_download").style.display="block";
	}
	else
	{
		document.getElementById("div_download").style.display="none";
	}
	document.getElementById("div_vod_result2").style.display="block";
	return true;
}
/*
让select控件只能同时选择一个子项
*/
function SelectOne()
{
	var obj_vodrlt=document.getElementById("vod_result_list");

	for(var j=0; j<g_vod_sel.length; j++)
	{
		obj_vodrlt.options[g_vod_sel[j]].selected=false;
	}
	g_vod_sel = [];
	g_vod_sel.push(obj_vodrlt.selectedIndex);
	//alert(obj_vodrlt.options[obj_vodrlt.selectedIndex].index);
	//alert(obj_vodrlt.options[obj_vodrlt.selectedIndex].text);
	//alert(obj_vodrlt.options[obj_vodrlt.selectedIndex].value);
}
function SetVodStatus()
{
	var obj_vodstatus = document.getElementById("span_vod_status");
	if(g_vod_playing==false)
	{
		//obj_vodstatus.style.display="none";
		obj_vodstatus.innerHTML = "已停止";	
		return;
	}
	else
	{
		var strVodSpeed;
		if(g_vod_speed==(1.0/2.0))
		{
			strVodSpeed = "1/2x";
		}
		else if(g_vod_speed==(1.0/4.0))
		{
			strVodSpeed = "1/4x";
		}
		else if(g_vod_speed==(1.0/8.0))
		{
			strVodSpeed = "1/8x";
		}
		else if(g_vod_speed==(1.0/16.0))
		{
			strVodSpeed = "1/16x";
		}
		else if(g_vod_speed==(1.0/32.0))
		{
			strVodSpeed = "1/32x";
		}
		else
		{
			strVodSpeed = g_vod_speed + "x";
		}
		
		var strVodStatus;
		//VOD播放控制: 1-正常播放 2-暂停  3-倒放 4-单帧正播 5-单帧倒播 其他-停止
		if(g_last_vod_type == 1)
		{
			strVodStatus = "正播";
		}
		else if(g_last_vod_type == 2)
		{
			strVodStatus = "暂停";
		}
		else if(g_last_vod_type == 3)
		{
			strVodStatus = "倒放";
			strVodSpeed = "-" + strVodSpeed;
		}
		else if(g_last_vod_type == 4)
		{
			strVodStatus = "单帧正播";
			strVodSpeed = "";
		}
		else if(g_last_vod_type == 5)
		{
			strVodStatus = "单帧倒播";
			strVodSpeed = "";
		}
		else
		{
			strVodStatus = "停止";
			strVodSpeed = "";
		}
		obj_vodstatus.style.display = "";
		if(g_stop_vod == false)
		{
			obj_vodstatus.innerHTML = strVodStatus + "  " + strVodSpeed;
		}
		setTimeout("SetVodStatus()", 500);
	}
}

function OpenVod(type)
{
	if(type==1)
	{
		if(g_vod_playing == true)
		{
			if(g_last_vod_type !=2)
			{
				VodPlayCtrl(1);	
				if(g_vod_speed!=1.0)
				{
					//alert(g_vod_speed);
					g_vod_speed=1.0;
					SetPlaybackSpeed("normal");
					return true;
				}
				return true;
			}
			else
			{
				VodPlayCtrl(1);	
				/*
				if(g_vod_speed<1.0)
				{
					//alert("slow: " + g_vod_speed);
					SetPlaybackSpeed("slow");
					return true;
				}
				else if(g_vod_speed>1.0)
				{
					//alert("fast: " + g_vod_speed);
					SetPlaybackSpeed("fast");
					return true;
				}
				*/
				return true;
			}
		}

	}
	else if(type==2)
	{
		//
	}
	//获取需要播放的录像时间点
	var obj_vodrlt=document.getElementById("vod_result_list");
	var index_vod = obj_vodrlt.selectedIndex;
	if(obj_vodrlt.selectedIndex<0)
	{
		index_vod = 0;
	}
	//alert(g_vodlist.length);
	if(g_vodlist.length<=0)
	{
		alert("请先检索录像，再选择播放录像");
		return -1;
	}
	var stime=g_vodlist[index_vod].start;
	var etime=g_vodlist[index_vod].end;
	var info;
	info = '<\?xml version="1.0"\?><doc><members>';
	info += '<cameraid>'+ g_cam_id +'</cameraid>';
	info += '<starttime>' + stime + '</starttime>';
	info += '<stoptime>' + etime + '</stoptime>';  
	info += '<playindex>' + 1 + '</playindex>';
	info += '<filename>' + ' ' + '</filename>';//查询出来的录像路径
	info += '</members></doc>';	
	//alert(info);
	g_last_vod_type = 1;
	var ret = vodplayer.OpenVod(1, info);
	return ret;
}
function OnNoteVodStart(info)
{
	if(info != "0")
	{
		alert("请求点播录像失败！");
	}
	g_vod_playing = true;
	g_stop_vod = false;
	g_vod_speed = 1.0;
	SetVodStatus();
	VodPlayCtrl(1);
	return;
}
/* VOD播放控制
 * flag: 控制指令
		 1-正常播放
		 2-暂停
		 3-倒放
		 4-单帧正播
		 5-单帧倒播
		 其他-停止
 */
function VodPlayCtrl(flag)
{
	if(flag>5 || flag<=0)
	{
		EndVod();
		return;
	}
	g_last_vod_type = flag;
	var info = '<\?xml version="1.0"\?><doc><members>';
	info += '<playindex>' + 1 + '</playindex>';
	info += '<ctrtype>' + flag + '</ctrtype>';	
	info += '<playparam>' + '1' + '</playparam>';
	info += '</members></doc>';
	var ret = vodplayer.PlayCtrl("bocom", info);
	return ret;
}
function EndVod()
{
	g_stop_vod = true;
	if(g_vod_playing == false)
	{
		return;
	}
	
	var info = '<\?xml version="1.0"\?><doc><members>';
	info += '<PlayIndex>' + 1 + '</PlayIndex>';
	info += '</members></doc>';
	//alert(info);
	var ret = vodplayer.CloseVod(1, info);
	document.getElementById("span_vod_status").innerHTML="正在停止..."
	return ret;
}
function OnNoteVodEnd(info)
{
	g_vod_playing = false;
	g_last_vod_type = 0;
}
function SetPlaybackSpeed(flag)
{
	obj_vodstatus = document.getElementById("span_vod_status");
	if(flag=="fast")
	{
		g_vod_speed*=2;
		if(g_vod_speed>128)
		{
			g_vod_speed=128;
		}
	}
	else if(flag=="slow")
	{
		g_vod_speed/=2.0;
		if(g_vod_speed<(1.0/32.0))
		{
			g_vod_speed=(1.0/32.0);
		}
	}
	else
	{
		g_vod_speed=1.0;
	}
	var info = '<\?xml version="1.0"\?><doc><members>';
	info += '<playindex>' + 1 + '</playindex>';
	info += '<speed>' + g_vod_speed + '</speed>';
	info += '<vodtype>' + g_last_vod_type + '</vodtype>';
	info += '</members></doc>';
	//alert(info);
	var ret = vodplayer.SetPlaybackSpeed('bocom', info);
}
function OnGetVodPos(info)
{
	return;
	var vodpos_time=parseInt(info);
	var timeObj = new Date(vodpos_time*1000);
    var year = timeObj.getFullYear();
    var month = timeObj.getMonth()+1;
    var date = timeObj.getDate();
    var hour = timeObj.getHours();
    var min = timeObj.getMinutes();
    var sec = timeObj.getSeconds();

    if(month<10) month = "0" + month;
    if(date<10) date = "0" + date;
    if(hour<10) hour = "0" + hour;
    if(min<10) min = "0" + min;
    if(sec<10) sec = "0" + sec;

    var str_vod_pos = year + "-" + month + "-" + date + " "
    			   + hour + ":" + min + ":" + sec;
	
    return true;
}

function GetDownloadProcess()
{
	if(vodplayer.GetDownloadStatus() == 0 || g_is_download==false || g_is_stop_download == true)
	{
		//g_is_download = false;
		//g_is_stop_download == true;
		//document.getElementById("chk_splitvod").disabled=false;
		//document.getElementById("txt_splitmin").disabled=false;
		//document.getElementById("chk_toavi").disabled=false;
		return;
	}
	if(vodplayer.GetDownloadStatus() != 0 && g_is_download == true)
	{
		var obj_download_rate=document.getElementById("span_download_rate");
		obj_download_rate.style.display="";	
		var info = '<\?xml version="1.0"\?><doc><members>';
		info += '<cameraid>' +  g_cam_id + '</cameraid>';
		info += '</members></doc>';	
		//alert(info);
		var ret = vodplayer.GetExportProgress(1, info);
		var rate=ret;
		var tip="下载进度: ";
		if(rate<=0)
		{
			obj_download_rate.innerHTML = "正在下载中，请稍等<img src='images/loading_01.gif'>";
		}
		else
		{
			if(rate>=100)
			{
				rate=100;
			}
			var show='<table cellspacing="2" cellpadding="2" style="font-size:12px;"><tr><td>'+ tip + '</td><td width=' + rate + '"px" height="10px" style="background:#66FF66;text-algin=center;"></td><td>'+ rate +"%"+'</td></tr></table>';
			
			obj_download_rate.innerHTML = show;
		}
		if(rate>=100)
		{
			obj_download_rate.innerHTML = obj_download_rate.innerHTML + "<br>下载已完成，正在处理中，请稍等<img src='images/loading.gif'>";
			return;
		}
	}
	window.setTimeout("GetDownloadProcess();", 1000);
}
function OnNoteDownloadStart(info)
{
	//alert("recv download start");
	g_is_download = true;
	g_is_stop_download = false;
	
	// 禁用下载设置
	document.getElementById("chk_splitvod").disabled=true;
	document.getElementById("txt_splitmin").disabled=true;
	document.getElementById("chk_toavi").disabled=true;
	/*
	var obj_download_rate=document.getElementById("span_download_rate");
	obj_download_rate.innerHTML = "";
	obj_download_rate.style.display="none";
	*/
	
	// 记录下载日志20180320
	// 下载成功
	LogToDKPT(1, true);
	
	// 获取下载进度
	GetDownloadProcess();
}

// 日志传入电科平台20180320
// type：操作类型，1-download
// result：操作结果，true成功，false失败
function LogToDKPT(type, result)
{
    var operationType = "";
    var operationResult = result ? "success" : "fail";
    switch(type)
    {
    case 1: // download
        operationType = "download";
        break;
    default:
        return false;
    };
    
    var url = "http://192.168.249.194:8888/tcdp/wfpt/CarmeraLogLog.htm?" + 'carmeraId=' + g_cam_id + 
              '&carmeraName=' + g_cam_name + '&userId=' + g_log_userid + 
              '&userName=' + g_log_username + '&ClientIp=' + g_log_clientip + '&groupId=' + 
              g_log_groupid + '&groupName=' + g_log_groupname + '&operationType=' + operationType + 
              '&operationResult=' + operationResult;
    loadDoc(url, logToDKPT_ajax);
    
    return true;
}

function StartDownload()
{
	if(g_download_level == -1)
	{
		//无权限
		alert("当前用户无下载权限！");
		return;
	}
	if( g_is_download == true)
	{
		alert("请等待当前下载任务结束！");
		return;
	}
	/*
	var obj_download_rate=document.getElementById("span_download_rate");
	obj_download_rate.innerHTML = "正在处理下载请求...<img src='images/loading.gif'>";
	obj_download_rate.style.display="";
	*/
	//获取选择的需要下载的时间点
	var obj_vodrlt=document.getElementById("vod_result_list");
	var index_vod = obj_vodrlt.selectedIndex
	if(obj_vodrlt.selectedIndex<0)
	{
		index_vod = 0;
	}
	var stime=g_vodlist[index_vod].start;
	var etime=g_vodlist[index_vod].end;
	//分段、AVI
	var toAvi = 0;
	var SplitTime = 0;
	if(document.getElementById("chk_toavi").checked == true)
	{
		toAvi = 1;
	}
	if(document.getElementById("chk_splitvod").checked == true)
	{
		SplitTime = parseInt(document.getElementById("txt_splitmin").value);
	}
	if(SplitTime>200)
	{
		SplitTime=200;
		document.getElementById("txt_splitmin").value=200;
	}
	if(SplitTime<0)
	{
		SplitTime=0;
		document.getElementById("txt_splitmin").value=0;
	}

	var info;
	var info = '<\?xml version="1.0"\?><doc><members>';
	info += '<PlayIndex>' + 1 + '</PlayIndex>';
	info += '<cameraid>' +  g_cam_id + '</cameraid>';
	info += '<cameraname>' +  g_cam_name + '</cameraname>';
	info += '<starttime>' + stime + '</starttime>';//开始时间
	info += '<stoptime>' + etime + '</stoptime>';//结束时间
	info += '<ToAVI>' + toAvi + '</ToAVI>';//是否转换成AVI，0-不转换，其他-转换
	info += '<SplitLen>' + SplitTime + '</SplitLen>';//切割视频成小片段文件，每个文件的分钟数（0-不切割）
	info += '</members></doc>';	
	//alert(info);
	var ret = vodplayer.StartExportRecord(1, info);
	if(ret != 1)
	{
	    // 记录下载日志20180320
	    // 下载失败
	    LogToDKPT(1, false);
	    return false;
	}
	return true;
}
function StopDownload()
{
	if(g_is_download == false)
	{
		alert("当前没有进行中的下载任务！");
		return;
	}
	if(g_is_stop_download == true)
	{
		return;
	}
	g_is_stop_download = true;
	
	var msg = "停止当前的下载任务，是否继续？";
	if( ! confirm(msg) )
	{	
		return;
	}
	
	var obj_download_rate=document.getElementById("span_download_rate");
	obj_download_rate.innerHTML = obj_download_rate.innerHTML + "<br>下载已手动结束，正在处理中，请稍等<img src='images/loading.gif'>";
	obj_download_rate.style.display="";
	
	var info = '<\?xml version="1.0"\?><doc><members>';
	info += '<cameraid>' +  g_cam_id + '</cameraid>';
	info += '</members></doc>';	
	//alert(info);
	
	var ret = vodplayer.StopExportRecord(1, info);
}
function OnNoteDownloadEnd(info)
{
	var obj_download_rate=document.getElementById("span_download_rate");
	g_is_download = false;
	if(g_is_stop_download == true)
	{
		obj_download_rate.innerHTML = "<img src='images/log_info.gif'>下载结束";
		alert("下载完成！");
	}
	else
	{
		obj_download_rate.innerHTML = "<img src='images/log_info.gif'>下载处理完成";
		alert("下载完成！");
	}

	document.getElementById("chk_splitvod").disabled=false;
	document.getElementById("txt_splitmin").disabled=false;
	document.getElementById("chk_toavi").disabled=false;
	obj_download_rate.style.display="none";
	obj_download_rate.innerHTML = "";
	g_is_stop_download = false;
	return true;
}
function CheckSplitTime()
{
	var obj_txt_splitmin = document.getElementById("txt_splitmin");
	var time = parseInt(obj_txt_splitmin.value);
	if(time<1)
		time=1;
	else if(time>200)
		time=200;
	obj_txt_splitmin.value = time;
}
function DoSetSplitVodInfo()
{
	var obj_chk_splitvod = document.getElementById("chk_splitvod");
	var obj_txt_splitmin = document.getElementById("txt_splitmin");
	if(obj_chk_splitvod.checked==true)
	{
		obj_txt_splitmin.disabled=false;
	}
	else
	{
		obj_txt_splitmin.disabled = true;
	}
	return true;
}
function DoSetToAvi()
{
	var obj_chk_toavi = document.getElementById("chk_toavi");
	var obj_lbl_toavi = document.getElementById("lbl_toavi");
	if(obj_chk_toavi.checked==true)
	{
		//obj_lbl_toavi.innerHTML = "avi格式";
	}
	else
	{
		//obj_lbl_toavi.innerHTML = "mdm格式";
	}
	return true;
}