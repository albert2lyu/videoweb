var g_cam_id=0;
var g_todo_preset=0;
var g_cam_name="";
var g_svr_ip="";
var g_svr_ip_ex="";
var g_svr_port=0;
var g_svr_port_ex=0;
var g_usr_name="";
var g_usr_name_ex="";
var g_usr_pass="";
var g_usr_pass_ex="";
var g_window_width=0;
var g_window_heigh=0;
var g_bPtzControl=0; //是否可控PTZ，0-不可控1-可控
var g_download_level=0;//下载权限级别，-1表示不能下载
var g_bSetPtzSpeed=true;
var g_SetPtzSpeedType=1;
var g_count=0;
var g_bSetPresetNo=true;
var g_SetPresetType=1;
var g_bPlaying=false;
// 是否已登陆
var g_b_login = false;

// 外部传入的信息，用于记录日志，20180320(直播暂时不处理日志20180320)
var g_log_userid="";// 用户ID
var g_log_username="";// 用户名称
var g_log_clientip="";//用户IP
var g_log_groupid="";//用户部门ID
var g_log_groupname="";//用户部门名称
var g_b_log = false; // 是否记录日志

// 相机列表数据
var g_arr_camname = new Array();
var g_arr_camid = new Array();
var g_arr_camsfkk = new Array();
var g_arr_camdownload = new Array();

function GotoVodWeb()
{
	if(g_bPlaying == true)
	{
		document.getElementById("a_goto_vod").innerHTML = "正在停止实时视频，请稍等...";
		document.getElementById("a_goto_vod").onclick = "";
		player1.CloseVideo(0);
		
		setTimeout('GotoVodWeb()', 100);
		return;
	}
	var page = "";
    if(g_b_log)
    {
        page = 'vod.php?' + 'camid=' + g_cam_id + '&userId=' + g_log_userid + '&userName=' + g_log_username + 
               '&ClientIp=' + g_log_clientip + '&groupId=' + g_log_groupid + '&groupName=' + g_log_groupname;
    }
    else
    {
        page = 'vod.php?' + 'camid=' + g_cam_id;
        // + '&camname=' + g_cam_name + '&sfkk=' + g_bPtzControl + '&download=' + g_download_level;
    }

	var vodWin;
	vodWin = window.open (
			 page,
			 "_self",
			 'resizable=yes,menubar=no,location=no,status=no,scrollbars=no'
	);
	
    var doc = document.form_camdata; 
    doc.target = vodWin.name; 
    doc.action = page; 
    doc.submit(); 
    vodWin.focus(); 
}

function Login()
{
	var info='<\?xml version="1.0"\?><doc><members>';
	info += '<ServerIP>' + g_svr_ip + '</ServerIP>';      //IP
	info += '<ServerPort>' + g_svr_port + '</ServerPort>';    //端口号
	info += '<UserName>' + g_usr_name + '</UserName>';           //用户名
	info += '<Password>' + g_usr_pass + '</Password>';          //密码
	info += '</members></doc>';
	var ret = player1.Logon(1, info);
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
		var retEx = player1.Logon(1, infoEx);
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
function ChangeLiveCam()
{
	var obj_switch_status = document.getElementById("span_switch_status");
	// 关闭之前的直播
	if(g_bPlaying == true)
	{
		obj_switch_status.innerHTML = "正在切换中...";
		player1.CloseVideo(0);
		
		setTimeout('ChangeLiveCam()', 100);
		return;
	}
	var obj_camlist = document.getElementById("camera_list");
	var index_camlist = obj_camlist.selectedIndex;
	if(obj_camlist.selectedIndex < 0)
	{
		index_camlist = 0;
	}
	// 控制select失去焦点
	obj_camlist.blur();
	
	g_cam_id = parseInt(g_arr_camid[index_camlist]);
	g_cam_name = g_arr_camname[index_camlist];
	g_bPtzControl  = g_arr_camsfkk[index_camlist]; 
	g_download_level = g_arr_camdownload[index_camlist];
	onWebResize()
	OpenVideo();
	obj_switch_status.innerHTML = "";
}
function ToOpenVideo()
{
	if(g_b_login != true)
	{
		setTimeout("ToOpenVideo()", 200);
	}
	else
	{
		setTimeout("OpenVideo()", 500);
	}
}
function OpenVideo()
{
	//alert("open");
	if(g_cam_id<=0)
	{
		alert("请求实时视频失败：无效的相机编号！");
		return -1;
	}
	//alert("g_cam_id=" + g_cam_id);
	var info = '<\?xml version="1.0"\?><doc><members>';
	info += '<cameraid>' + g_cam_id + '</cameraid>';
	info += '<recvtype>2</recvtype>';
	info += '</members></doc>';
	var ret = player1.OpenVideo("bocom", 0, info);
	if(ret != 1)
	{
		alert("请求实时视频直播失败!");
	}
	g_bPlaying = true;
	if(g_todo_preset > 0)
	{
		setTimeout("PresetWork('getpos', "+ g_todo_preset +")", 2000);
		document.getElementById("preset").value = g_todo_preset;
		g_todo_preset = 0;
	}
	return true;
}
function OnNoteLiveEndFunc(info)
{
	//alert("function aaa");
	g_bPlaying=false;
}
function CheckPtzSpeed()
{
	var obj_ptzspeed = document.getElementById("ptzspeed");
	var speed = parseInt(obj_ptzspeed.value);
	if(speed<1)
	{
		speed=1;
	}
	else if(speed>255)
	{
		speed=255;
	}
	obj_ptzspeed.value = speed;
}
function SetPtzSpeed()
{		
	if(g_bSetPtzSpeed==false)
	{
		return;
	}
	var timeout=1000;
	g_count++;
	if(g_count==1)
	{
		timeout=1000;
	}
	else if(g_count<3)
	{
		timeout = 500;
	}
	else if(g_count<6)
	{
		timeout = 200;
	}
	else if(g_count<15)
	{
		timeout = 100;
	}
	else
	{
		timeout = 50;
	}
	var obj_ptzspeed = document.getElementById("ptzspeed");
	var speed = parseInt(obj_ptzspeed.value);
	if(g_SetPtzSpeedType==1) // 递减
	{
		speed-=1;
		if(speed<1)
		{
			speed=255;
		}
	}
	else // 递增
	{
		speed+=1;
		if(speed>255)
		{
			speed=1;
		}
	}
	obj_ptzspeed.value = speed;
	if(g_bSetPtzSpeed==false)
	{
		return;
	}
	setTimeout("SetPtzSpeed()", timeout);
}
function StartSetPtzSpeed(type)
{
	g_bSetPtzSpeed = true;
	g_count=0;
	g_SetPtzSpeedType = type;
	SetPtzSpeed();
}
function StopSetPtzSpeed()
{
	g_bSetPtzSpeed = false;
	g_count=0;
}
function StartPTZ(ptz)	{
	var speed=0;
	var obj_ptzspeed = document.getElementById("ptzspeed");
	speed = parseInt(obj_ptzspeed.value);
	if(speed<=0)
		speed=1;
	else if(speed>255)
		speed=255;
	obj_ptzspeed.value = speed;
	var info = '<\?xml version="1.0"\?><doc><members>';
	info += '<PTZCode>' + ptz + '</PTZCode>';
	info += '<PtzSpeed>' + speed + '</PtzSpeed>';	//云台速度或巡航速度或巡航点停顿时间（1-255）
	info += '</members></doc>';
	//alert(info);
	var ret = player1.StartPTZ(1, info);
	//if (ret != 1) alert(player1.GetErrorMsg());
}

function StopPTZ(ptz)	{
	var info = '<\?xml version="1.0"\?><doc><members>';
	info += '<PTZCode>' + ptz + '</PTZCode>';
	info += '<PtzSpeed>0</PtzSpeed>';	//云台速度或巡航速度或巡航点停顿时间（1-255）
	info += '</members></doc>';
	//alert(info);
	var ret = player1.StopPTZ(1, info);	 	
}
function ChangePresetNo(no)
{
	document.getElementById("preset").value = no;
	return true;
}
// cmd: setpos-设置预置位，getpos-调用预置
function DoPreset(cmd)
{
	var pno=0;
	var obj_preset = document.getElementById("preset");
	pno = parseInt(obj_preset.value);
	if(pno<=0)
		pno=1;
	else if(pno>255)
		pno=255;
	obj_preset.value = pno;
	if(cmd == "setpos" && pno <= 20)
	{
		alert("前20个预置位为系统预留，请设置20至255的预置位。");
		return;
	}
	
	return PresetWork(cmd, pno);
}
// cmd: setpos-设置预置位，getpos-调用预置位
function PresetWork(cmd, pno)
{
	if(pno <= 0)
	{
		return;
	}
	var info = '<\?xml version="1.0"\?><doc><members>';
	info += '<PTZCode>' + cmd + '</PTZCode>';
	info += '<PtzSpeed>' + pno + '</PtzSpeed>';	//预置位数字
	info += '</members></doc>';
	//alert(info);
	var ret = player1.StartPTZ(1, info);
}

function CheckPreset()
{
	var obj_preset = document.getElementById("preset");
	var number = parseInt(obj_preset.value);
	if(number<1)
	{
		number=1;
	}
	else if(number>255)
	{
		number=255;
	}
	obj_preset.value = number;
}

function OnNotePtzRet(info)
{
	//alert("web\n"+info);
	if(info == "show2")
	{
		document.getElementById("span_preset_tip").innerHTML="";
		return;
	}
	var noteinfo;
	if(info == "1000")
	{
		noteinfo="调用预置位成功";
		document.getElementById("span_preset_tip").innerHTML=noteinfo;
		setTimeout("OnNotePtzRet('show2')", 3000);
		return;
	}
	else if (info == "1001")
	{
		noteinfo="调用预置位失败！";
	}
	else if (info == "2000")
	{
		noteinfo="设置预置位成功";
	}
	else if(info == "2001")
	{
		noteinfo="设置预置位失败！";
	}
	else
	{
		return;
	}
	alert(noteinfo);
	
	/*
	if(info == "show2")
	{
		document.getElementById("span_preset_tip").innerHTML="";
		
		return;
	}
	else
	{
		document.getElementById("span_preset_tip").innerHTML=info;
		
	}
	setTimeout("OnNotePtzRet('show2')", 2000);
	*/
	return true;
}
function SetPresetNo()
{		
	if(g_bSetPresetNo==false)
	{
		return;
	}
	var timeout=1000;
	g_count++;
	if(g_count==1)
	{
		timeout=1000;
	}
	else if(g_count<3)
	{
		timeout = 500;
	}
	else if(g_count<6)
	{
		timeout = 200;
	}
	else if(g_count<15)
	{
		timeout = 100;
	}
	else
	{
		timeout = 50;
	}
	var obj_preset = document.getElementById("preset");
	var number = parseInt(obj_preset.value);
	if(g_SetPresetType==1) // 递减
	{
		number-=1;
		if(number<1)
		{
			number=255;
		}
	}
	else // 递增
	{
		number+=1;
		if(number>255)
		{
			number=1;
		}
	}
	obj_preset.value = number;
	if(g_bSetPresetNo == false)
		return;
	setTimeout("SetPresetNo()", timeout);
}
function StartSetPreset(type)
{	
	g_bSetPresetNo = true;
	g_count=0;
	g_SetPresetType = type;
	SetPresetNo();
	/*
	var obj_preset = document.getElementById("preset");
	var number = parseInt(obj_preset.value);
	if(type==1) // 递减
	{
		number-=1;
		if(number<1)
			number=1;
	}
	else // 递增
	{
		number+=1;
		if(number>255)
			number=255;
	}
	obj_preset.value = number;
	*/
}

function StopSetPreset()
{
	g_bSetPresetNo = false;
	g_count=0;
}


function DoFullscreen()
{
	
	return true;
}


function DoCapture()
{
	
	return true;
}


function DoStartLocalRecord()
{
	
	return true;
}


function DoStopLocalRecord()
{
	
	return true;
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
			//alert(g_svr_ip+"\n"+ g_svr_port+"\n"+g_usr_name +"\n"+g_usr_pass);
			
			g_svr_ip_ex   = n_svrip_ex[i].childNodes[0].text;
			g_svr_port_ex = parseInt(n_port_ex[i].childNodes[0].text);
			g_usr_name_ex = n_username_ex[i].childNodes[0].text;
			g_usr_pass_ex = n_userpass_ex[i].childNodes[0].text;
			//alert(g_svr_ip_ex+"\n"+ g_svr_port_ex+"\n"+g_usr_name_ex +"\n"+g_usr_pass_ex);
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
		alert("未获取到有效的相机列表");
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
	player1.InitOcxType(1);
	var args = new Array();
	args = getArgs();
	g_cam_id = parseInt(args['camid']);
	if(isNaN(g_cam_id))
	{
		g_cam_id = 0;
	}
	g_todo_preset = parseInt(args['preset']);
	if(isNaN(g_todo_preset))
	{
		g_todo_preset = 0;
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
	
	//document.getElementById("span_cam_info").innerHTML = "MVP直播：[" + g_cam_id + "]" + g_cam_name;

	if( GetConfigInfo() != true)
	{
		alert("加载配置文件失败，无法连接服务器!");
		return false;
	}
	//window.resizeTo(750, 552);
	//g_window_width = window.outerWidth;
	//g_window_heigh = window.outerHeight;
	g_window_width = 750;
	g_window_heigh = 552;
	//alert("width:"+g_window_width + "  height:"+g_window_heigh);

	onWebResize();
	// 等待控件加载完毕在执行接口调用
	setTimeout("Login()",100);
	setTimeout("ToOpenVideo()",300);
}
function onWebResize()
{
	var obj_player1 = document.getElementById("player1");
	//var width = window.outerWidth;
	//var heigh = window.outerHeight;
	var width = document.body.clientWidth;
	var heigh = document.body.clientHeight;
	if(g_bPtzControl==0)
	{
		//控制PTZ图表显示
		document.getElementById('ptz_div').style.display="none";
		obj_player1.style.width = width-5;
		obj_player1.style.height = heigh-35;
		return;
	}
	else
	{
		document.getElementById('ptz_div').style.display="block";
	}
	/////////////////////////////////////////
	// edited 2015-11-21
	var w = width - 150;
	var h = heigh - 80;
	if(w<240)
	{
		w = 240;
	}
	if(h<180)
	{
		h = 180;
	}
	obj_player1.style.width = w;
	obj_player1.style.height = h;
	
	return;
	/////////////////////////////////////////
	var w = width - g_window_width;
	var h = heigh - g_window_heigh;
	if( w<=20 || h<=20)
	{
		//return;
	}
	var obj_w = w + 480;
	var obj_h = h + 360;
	if(obj_w<240)
	{
		obj_w = 180;
	}
	else if(obj_w>1024)
	{
		//obj_w=1024;
	}
	
	if(obj_h<180)
	{
		obj_h = 180;
	}
	else if(obj_h>768)
	{
		//obj_h=768;
	}
	//alert("width:"+obj_w + "  height:"+obj_h);
	obj_player1.style.width = obj_w;
	obj_player1.style.height = obj_h;
}