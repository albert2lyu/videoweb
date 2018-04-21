// JavaScript Document
var g_svr_ip="";
var g_svr_ip_ex="";
var g_svr_port=0;
var g_svr_port_ex=0;
var g_usr_name="";
var g_usr_name_ex="";
var g_usr_pass="";
var g_usr_pass_ex="";
var g_web_user="";
var g_web_user_ok=false;
var g_root = new Array();
var g_group_list = new Array();
var g_camera_list = new Array();
var g_is_getcamera_ok = false;
var g_sel_camlist = new Array();
var g_bPlaying = false;
var g_scanState = 0;// 0-停止 1-启动 2-暂停 3-继续
var g_cam_search_index=0;
var g_scan_list = new Array();
var g_cur_camlist = new Array(); // 保存当前执行的巡检中的摄像机
var g_cur_cam_index=0;// 当前播放相机的在g_cur_camlist中的索引
var g_cur_cam_time=0;//当前相机已切换时间（用作对比巡检时间间隔）
var g_b_scan_first=true; //巡检启动第一次执行
var g_scanOpType = 0; // 巡检操作类型 1-新增  2-删除  3-更新 其他-未知
var d = new dTree('d', true, true, OnClickCheckCB, ClickedCheckCB, OnDbClickNodeCB);
// 是否已登陆
var g_b_login = false;

function GetConfigInfo()
{
	var dom;
	dom = new ActiveXObject("Microsoft.XMLDOM");
	dom.async = false;                           
	dom.load("config.xml");
	if(dom)
	{
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
function Login()
{
	var info='<\?xml version="1.0"\?><doc><members>';
	info += '<ServerIP>' + g_svr_ip + '</ServerIP>'; 
	info += '<ServerPort>' + g_svr_port + '</ServerPort>';    
	info += '<UserName>' + g_usr_name + '</UserName>';
	info += '<Password>' + g_usr_pass + '</Password>';
	info += '</members></doc>';
	var ret = scan_player.Logon(1, info);
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
		var retEx = scan_player.Logon(1, infoEx);
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
	//alert("login ret=" + ret);
	return ret;
}
function onload()
{
	scan_player.InitOcxType(1);
	var args = new Array();
	args = getArgs();
	g_web_user = args['user'];
	//alert(g_web_user);
	if(g_web_user=='' || g_web_user==undefined || g_web_user==null )
	{
		g_web_user_ok = false;
		//alert("无效用户！");
		//return;
	}
	else
	{
		g_web_user_ok = true;
	}
	if( GetConfigInfo() != true)
	{
		alert("加载配置文件失败，无法连接服务器!");
		return false;
	}
	// 获取已配置的巡检
	var ret = GetScanInfo();
	if(ret)
	{
		SetScanListItems();
	}
	
	onWebResize();
	
	// 等待控件加载完毕在执行接口调用
	setTimeout("Login()", 100);
	
	// 获取摄像机
	setTimeout("ToGetCameraListInfo()", 500);
}
function GetScanInfo()
{
	g_scan_list = [];
	if(g_web_user_ok == false)
		return;
	var scanfile = "scan\\" + g_web_user + ".xml";
	var dom;
	dom = new ActiveXObject("Microsoft.XMLDOM");
	dom.async = false;                           
	dom.load(scanfile);
	if(dom)
	{
		var n_scans = dom.getElementsByTagName('ScanSetting');
		var n_server, n_scan, n_scanitems, n_scanitem, n_cams;
		if(n_scans.length <= 0) 
		{
			return false;
		}
		var i = 0;
		for(i=0; i<n_scans.length; i++)
		{
			n_server = n_scans[i].getElementsByTagName('MvpServerAddr');
			//alert(n_server[0].childNodes[0].text);
			if(n_server[0].childNodes[0].text == g_svr_ip)
			{
				break;
			}
		}
		if(i>=n_scans.length)
		{
			//未找到相应的配置
			return false;
		}
		n_scan = n_scans[i];
		n_scanitems = n_scan.getElementsByTagName('ScanItem');
		if(n_scanitems.length <= 0)
		{
			return false;
		}
		for(i=0; i<n_scanitems.length; i++)
		{
			n_scanitem = n_scanitems[i];
			g_scan_list[i] = new Array();
			g_scan_list[i].name = unescape(n_scanitem.getElementsByTagName('szName')[0].childNodes[0].text);
			g_scan_list[i].id = n_scanitem.getElementsByTagName('ID')[0].childNodes[0].text;
			g_scan_list[i].interval = n_scanitem.getElementsByTagName('Interval')[0].childNodes[0].text;
			g_scan_list[i].loop = n_scanitem.getElementsByTagName('LoopCount')[0].childNodes[0].text;
			//alert(g_scan_list[i].name+"\n"+g_scan_list[i].interval+"\n"+g_scan_list[i].loop);
			g_scan_list[i].cams = new Array();
			n_cams = n_scanitem.getElementsByTagName('ScanCameras/ScanCamera/CameraId');
			if(n_cams.length > 0)
			{
				for(var n=0; n<n_cams.length; n++)
				{
					g_scan_list[i].cams.push(n_cams[n].childNodes[0].text);
					//alert(g_scan_list[i].cams[n]);
				}
			}
		}

		return true;
	}
	return false;
}
function SetScanListItems()
{
	if(g_scan_list.length <= 0)
	{
		return;
	}
	var obj_scanlist=document.getElementById("scan_list");
	for(var i=0; i<g_scan_list.length; i++)
	{
		var oOption=document.createElement("option");
		oOption.setAttribute("value", g_scan_list[i].id);
		oOption.setAttribute("title", g_scan_list[i].name);
		var oText=document.createTextNode(g_scan_list[i].name);
		oOption.appendChild(oText);
		obj_scanlist.appendChild(oOption);
	}
}
function ParseCameraXML(xmlInfo)
{
	var dom;
	dom = new ActiveXObject("Microsoft.XMLDOM");
	dom.async = false;                           
	dom.loadXML(xmlInfo);
	if(dom)
	{
		// 根节点(仅有一个)
		var n_root = dom.getElementsByTagName('CameraInfo/Root');
		if(n_root.length<=0)
		{
			return false;
		}
		var n_root_id = dom.getElementsByTagName('CameraInfo/Root/ID');
		var n_root_pid = dom.getElementsByTagName('CameraInfo/Root/PID');
		var n_root_name = dom.getElementsByTagName('CameraInfo/Root/Name');
		var n_root_cam = dom.getElementsByTagName('CameraInfo/Root/CamCount');
		
		var v_root_id = n_root_id[0].childNodes[0].text;
		var v_root_pid = n_root_pid[0].childNodes[0].text;
		var v_root_name = n_root_name[0].childNodes[0].text;
		var v_root_cam = n_root_cam[0].childNodes[0].text;
		g_root.push({id:v_root_id, pid:v_root_pid, name:v_root_name, cam:v_root_cam});
		//alert(g_root[0].id + "\n" + g_root[0].pid + "\n" + g_root[0].name);
		// 组信息
		var n_group = dom.getElementsByTagName('CameraInfo/Group');
		var n_group_id = dom.getElementsByTagName('CameraInfo/Group/ID');
		var n_group_pid = dom.getElementsByTagName('CameraInfo/Group/PID');
		var n_group_name = dom.getElementsByTagName('CameraInfo/Group/Name');
		var n_group_cam = dom.getElementsByTagName('CameraInfo/Group/CamCount');
		if(n_group.length > 0)
		{
			for(var i=0; i<n_group.length;i++)
			{
				var v_group_id = n_group_id[i].childNodes[0].text;
				var v_group_pid = n_group_pid[i].childNodes[0].text;
				var v_group_name = n_group_name[i].childNodes[0].text;
				var v_group_cam = n_group_cam[i].childNodes[0].text;
				g_group_list.push({id:v_group_id, pid:v_group_pid, name:v_group_name, cam:v_group_cam});
				//if(v_group_id==23)
					//alert(g_group_list[i].id + "\n" + g_group_list[i].pid + "\n" + g_group_list[i].name);
			}
		}
		
		// 相机信息
		var n_camera = dom.getElementsByTagName('CameraInfo/Camera');
		var n_camera_id = dom.getElementsByTagName('CameraInfo/Camera/ID');
		var n_camera_gid = dom.getElementsByTagName('CameraInfo/Camera/GID');
		var n_camera_name = dom.getElementsByTagName('CameraInfo/Camera/Name');
		if(n_camera.length>0)
		{
			for(var i=0; i<n_camera.length;i++)
			{
				var v_camera_id = n_camera_id[i].childNodes[0].text;
				var v_camera_gid = n_camera_gid[i].childNodes[0].text;
				var v_camera_name = n_camera_name[i].childNodes[0].text;
				g_camera_list.push({id:v_camera_id, gid:v_camera_gid, name:v_camera_name});
				//if(v_camera_id==5131)
					//alert(g_camera_list[i].id + "\n" + g_camera_list[i].gid + "\n" + g_camera_list[i].name);
			}
		}
		// 返回成功
		return true;
	}
	return false;
}
function ToGetCameraListInfo()
{
	if(g_b_login != true)
	{
		setTimeout("ToGetCameraListInfo()", 200);
	}
	else
	{
		setTimeout("GetCameraListInfo()", 500);
	}
}
function GetCameraListInfo()
{
	var info = scan_player.GetCameraResourceInfo();
	//alert(info);
	/*
	// 写入文件，测试代码
	var fso = new ActiveXObject("Scripting.FileSystemObject");
	var f1 = fso.CreateTextFile("C:\\cam.xml",true);
	f1.Write(info);
	f1.Close();
	*/
	g_is_getcamera_ok = false;
	g_root = [];
	g_group_list = [];
	g_camera_list = [];
	g_sel_camlist = [];
	// 解析xml
	var ret = ParseCameraXML(info);
	if(ret==false)
	{
		alert("解析相机列表信息失败！");
		return false;
	}
	g_is_getcamera_ok = true;
	return true;
}
function ShowCameraList()
{
	if(g_is_getcamera_ok == false)
	{
		return false;
	}
	
	// 根节点
	var separator = ';';
	var str = "G" + separator + g_root[0].id + separator + g_root[0].name;
	var title = "摄像机组：" + g_root[0].name + "\n摄像机数：" + g_root[0].cam;
	d.add(g_root[0].id, -1, g_root[0].name+" ("+g_root[0].cam+")", str, "", title);
	// 组信息
	for(var i=0; i<g_group_list.length; i++)
	{
		str = "G" + separator  + g_group_list[i].id + separator + g_group_list[i].name;
		title = "摄像机组：" + g_group_list[i].name + "\n摄像机数：" + g_group_list[i].cam;
		d.add(g_group_list[i].id, g_group_list[i].pid, g_group_list[i].name, str, "", title);
		//alert(g_group_list[i].id+"\n"+g_group_list[i].pid+"\n"+g_group_list[i].name+"\n"+"javascript: void(0);"+"\n"+title);
	}
	// 相机信息
	for(var i=0; i<g_camera_list.length; i++)
	{
		str = "C" + separator  + g_camera_list[i].id + separator + g_camera_list[i].name;
		title = "摄像机名称：" + g_camera_list[i].name + "\n摄像机编号：" + g_camera_list[i].id;
		d.add(g_camera_list[i].id, g_camera_list[i].gid, "["+g_camera_list[i].id+"]"+g_camera_list[i].name, str, "", title);
	}
	
	document.getElementById("div_cameralist").innerHTML = d;
	return true;
}
function ShowCameraListLoop()
{
	var ret = ShowCameraList();
	if(ret != true)
	{
		setTimeout("ShowCameraListLoop()",100);
	}
	
	// 打开第1级目录
	for(var i=0; i<g_group_list.length; i++)
	{
		if(g_group_list[i].pid==0)
		{
			d.openTo(g_group_list[i].id, false, false);
		}
	}
	return;
}
// 点击checkbox时回调函数
function OnClickCheckCB(node, checked)
{
	//alert("click:\n" +node.id + "\n" + node.name+ "\n" +node.str);return;
	var str_arr = new Array();
	str_arr = node.str.split(';');
	if(str_arr[0] != "C")
	{
		// 如果是目录节点，则直接返回
		return true;
	}
	var bFind = false;
	var index = 0;
	for(var i=0; i<g_sel_camlist.length; i++)
	{
		if(g_sel_camlist[i].id == node.id)
		{
			bFind = true;
			index = i;
			break;
		}
	}
	if(checked == true && bFind == false)
	{
		g_sel_camlist.push({id:node.id, pid:node.pid, name:node.name, title:node.title});
		return true;
	}
	if(checked == false && bFind == true)
	{
		// 删除取消选择的项
		g_sel_camlist.splice(index, 1);
		return true;
	}
	return false;
}
// 点击checkbox后回调函数
function ClickedCheckCB()
{
	//alert("checkbox clicked");	
	ShowSelectedCameraList();
	// 判断是否有节点被选择，以更改界面元素
	var ret = d.IsNodeChecked();
	var obj_ck = document.getElementById("a_nodeselect");
	if(ret)
	{
		obj_ck.innerHTML = "<img src='images/dtree/nocheck.gif'>";
		obj_ck.setAttribute("title", "取消选择");
		obj_ck.onclick = function(){DoNodeAllSelect(2);};
	}
	else
	{
		obj_ck.innerHTML = "<img src='images/dtree/check.gif'>";
		obj_ck.setAttribute("title", "选择全部");
		obj_ck.onclick = function(){DoNodeAllSelect(1);};
	}
}
// 显示已选择的相机列表
function ShowSelectedCameraList()
{
	var obj_div = document.getElementById("div_seleced_cameralist");
	/*
	if(g_sel_camlist.length <= 0)
	{
		obj_div.innerHTML = "<img src='images/dtree/base.gif'><span class='tip_text'>已选择的摄像机 [共 0 个]</span>";
		return;
	}
	*/
	dd = new dTree('dd', false, false);
	dd.add(0, -1, "已选择的摄像机 [共 " + g_sel_camlist.length + " 个]", "", "");
	for(var i=0; i<g_sel_camlist.length; i++)
	{
		dd.add(g_sel_camlist[i].id, 0, g_sel_camlist[i].name, "", "", g_sel_camlist[i].title);
	}
	//alert(show_str);
	obj_div.innerHTML = dd;
}
// 双击相机节点后的回调函数
function OnDbClickNodeCB(node)
{
	//alert("click:\n" +node.id + "\n" + node.name+ "\n" +node.str);
	var str_arr = new Array();
	str_arr = node.str.split(';');
	if(str_arr[0] != "C")
	{
		// 如果是目录节点，则直接返回
		return;
	}

	if(g_cur_camlist.length > 0)
	{
		if(!confirm("当前正在执行巡检任务，是否停止巡检？"))
		{
			return;
		}
		DoScan(0);
	}	
	if(g_bPlaying)
	{
		CloseVideo(node.id);
	}
	document.getElementById("span_cur_cam_tip").innerHTML = "当前播放摄像机:";
	document.getElementById("span_cur_cam").innerHTML = node.name;
	setTimeout('OpenVideo('+node.id+')', 300);
}
function OpenVideo(camid)
{
	//alert(camid);
	if(camid<=0)
	{
		alert("请求实时视频失败：无效的相机编号！");
		return false;
	}
	var info = '<\?xml version="1.0"\?><doc><members>';
	info += '<cameraid>' + camid + '</cameraid>';
	info += '<recvtype>2</recvtype>';
	info += '</members></doc>';
	var ret = scan_player.OpenVideo("bocom", 0, info);
	if(ret != 1)
	{
		alert("请求实时视频直播失败!");
		return ret;
	}
	g_bPlaying = true;
	return true;
}
function CloseVideo(camid)
{
	scan_player.CloseVideo(0);
	g_bPlaying = false;
}
function ChangeSelectedScan()
{
	var obj_scanlist = document.getElementById("scan_list");
	/*
	strMsg = "即将切换巡检，是否继续？";
	if( ! confirm(strMsg) )
	{
		return;
	}
	*/
	
	// 取消之前选择的所有相机，并折叠
	d.checkNode(-1, false);
	d.closeAll();
	
	// 设置操作按钮显示
	//document.getElementById("btn_operate").value = "启 动";
	//document.getElementById("btn_operate").onclick = function(){DoScan(1);};
	// 控制select失去焦点
	obj_scanlist.blur();
	
	var index_scanlist = obj_scanlist.selectedIndex;
	if(obj_scanlist.selectedIndex <=0)
	{
		index_scanlist = 0;
		obj_scanlist.options[0].text = "--------------------请选择一个巡检方案--------------------";
		DoScanSelected(0);
		return;
	}
	var id = obj_scanlist.options[index_scanlist].value;
	obj_scanlist.options[0].text = "---------------------取消选择巡检方案---------------------";
    DoScanSelected(id);
}
function DoScanSelected(id)
{
	var obj_select = document.getElementById("sel_scan_time");	
	if(id == 0)
	{
		document.getElementById("txt_scan_name").value = "";
		document.getElementById("label_scan_name").innerHTML = "";
		obj_select.selectedIndex = 0;
		
		return;
	}
	
	for(var i=0; i<g_scan_list.length; i++)
	{
		if(g_scan_list[i].id == id)
		{
			// 设置名称显示
			document.getElementById("txt_scan_name").value = g_scan_list[i].name;
			document.getElementById("label_scan_name").innerHTML = g_scan_list[i].name;
			// 设置时间间隔选择
			for(var n=0; n<obj_select.options.length; n++)
			{
				if(obj_select.options[n].value == g_scan_list[i].interval)
				{
					obj_select.options[n].selected = true;
					break;
				}
			}
			// 设置相机选择
			for(var n=0; n<g_scan_list[i].cams.length; n++)
			{
				var id = g_scan_list[i].cams[n];
				d.checkNode(id, true);
				d.openTo(id, false, false);
			}
			d.openTo(g_scan_list[i].cams[0], true, false);
			return;
		}
	}

}
function DoScanLoop()
{
	//0-停止 1-启动 2-暂停 3-继续
	if(g_scanState == 0)
	{
		return;
	}
	var obj_scan_time = document.getElementById("sel_scan_time");
	var interval = obj_scan_time.options[obj_scan_time.selectedIndex].value;
	var rest_time=0;
	if(g_scanState == 1 || g_scanState == 3)
	{
		if(g_b_scan_first == true)
		{
			g_b_scan_first = false;
			CloseVideo(0);
			OpenVideo(g_cur_camlist[0].id);
			g_cur_cam_index = 0;
			g_cur_cam_time = 1;
		}
		else
		{
			if(g_cur_cam_time>=interval)
			{
				if(g_sel_camlist.length == 1)
				{
					// 仅有一个相机，不做巡检
					
				}
				else
				{
					// 播放下一个摄像机
					if(++g_cur_cam_index >= g_cur_camlist.length)
					{
						g_cur_cam_index=0;
					}
					CloseVideo(g_cur_camlist[g_cur_cam_index].id);
					OpenVideo(g_cur_camlist[g_cur_cam_index].id);
					g_cur_cam_time = 1;
				}
			}
			else
			{
				g_cur_cam_time += 1;
			}
		}
		
		if(g_sel_camlist.length == 1)
		{
			// 仅有一个相机，不做巡检
			rest_time = "";
		}
		else
		{
			rest_time = "["+(interval - g_cur_cam_time + 1) + "秒]";
		}
	}
	else if(g_scanState == 2)
	{
		g_cur_cam_time = 1;
		rest_time = "";
	}
	document.getElementById("span_cur_cam_tip").innerHTML = "当前轮巡摄像机:";
	document.getElementById("span_cur_cam").innerHTML = g_cur_camlist[g_cur_cam_index].name + " " + rest_time;
	setTimeout('DoScanLoop()', 1000);
}

function DoScan(type)
{
	//0-停止 1-启动 2-暂停 3-继续
	var obj_op = document.getElementById("btn_operate");
	
	if(type == 1)
	{
		g_cur_camlist = []; 
		for(var i=0; i<g_sel_camlist.length; i++)
		{
			g_cur_camlist.push({id:g_sel_camlist[i].id, name:g_sel_camlist[i].name});
		}
	}
	
	if(g_cur_camlist.length <= 0 && type != 0)
	{
		alert("请先选择一个或者多个摄像机");
		return;
	}
	g_scanState = type;// 0-停止 1-启动 2-暂停 3-继续
	if(type == 1)
	{
		DoScanLoop();
		obj_op.value = "暂 停";
		obj_op.onclick = function(){DoScan(2);};
	}
	else if(type == 2)
	{
		obj_op.value = "继 续";
		obj_op.onclick = function(){DoScan(3);};	
	}
	else if(type ==3)
	{
		obj_op.value = "暂 停";
		obj_op.onclick = function(){DoScan(2);};	
	}
	else if(type == 0)
	{
		CloseVideo(0);
		obj_op.value = "启 动";
		obj_op.onclick = function(){DoScan(1);};
		
		g_cur_camlist = [];
		g_cur_cam_index = 0;
		g_cur_cam_time = 0;
		g_b_scan_first = true;
		document.getElementById("span_cur_cam_tip").innerHTML = "";
		document.getElementById("span_cur_cam").innerHTML = "";
	}
	else
	{
		return false;
	}
}
function DoNodeAllSelect(type)
{
	// 1-选择全部  2-取消选择全部
	var obj_ck = document.getElementById("a_nodeselect");
	if(type == 1)
	{
		d.checkNode(-1, true);
		DoOpenClose(1);
		obj_ck.innerHTML = "<img src='images/dtree/nocheck.gif'>";
		obj_ck.setAttribute("title", "取消选择");
		obj_ck.onclick = function(){DoNodeAllSelect(2);};
	}
	else if(type == 2)
	{
		DoOpenClose(2);
		d.checkNode(-1, false);
		obj_ck.innerHTML = "<img src='images/dtree/check.gif'>";
		obj_ck.setAttribute("title", "选择全部");
		obj_ck.onclick = function(){DoNodeAllSelect(1);};
	}
}
function DoOpenClose(type)
{
	// 1-展开全部  2-折叠全部
	var obj_a = document.getElementById("a_openclose");
	if(type == 1)
	{
		d.openAll();
		obj_a.innerHTML = "<img src='images/dtree/folder.gif'>";
		obj_a.setAttribute("title", "折叠全部列表");
		obj_a.onclick = function(){DoOpenClose(2);};
	}
	else if(type == 2)
	{
		d.closeAll();
		obj_a.innerHTML = "<img src='images/dtree/folderopen.gif'>";
		obj_a.setAttribute("title", "展开全部列表");
		obj_a.onclick = function(){DoOpenClose(1);};
	}
}
function CamSearchChanged()
{
	g_cam_search_index = 0;
}
function DoCamSearch()
{
	var str_search = document.getElementById("txt_searchcam").value;
	// 去除左右的空格
	//str_search = str_search.Trim();
	if(str_search == '')
	{
		alert("请输入查询内容！");
		document.getElementById("txt_searchcam").focus();
		return;
	}
	
	var str, str_esp;
	var ret;
	var str_search_esp;
	/*var Regx = /^[A-Za-z0-9\s]*$/;
	if (Regx.test(str_search))
	{
		// 只包含数字、字母、空格，无需转换
		str_search_esp = str_search;
	}
	else
	{
		str_search_esp = escape(str_search)
	}*/
	str_search_esp = str_search;
	// 开始查询,先从上次查找到的位置进行检索
	var i=g_cam_search_index;
	var len = g_camera_list.length;
	for( ; ; )
	{
		str = g_camera_list[i].id + g_camera_list[i].name;
		/*
		if (Regx.test(str))
		{
			// 只包含数字、字母、空格，无需转换
			str_esp = str;
		}
		else
		{
			str_esp = escape(str);
		}
		*/
		str_esp = str;
		ret = str_esp.indexOf(str_search_esp);
		if(ret != -1)
		{
			// 先折叠所有列表，方便查看
			d.closeAll();
			//alert(str_esp + "\n" + str_search_esp);
			d.openTo(g_camera_list[i].id, true, false);
			g_cam_search_index = i + 1;
			if(g_cam_search_index>=g_camera_list.length)
			{
				g_cam_search_index = 0;
			}
			return;
		}
		else
		{
			i++;
			if(i >= len && g_cam_search_index == 0)
			{
				break;
			}
			else if(i >= len && g_cam_search_index != 0)
			{
				len = g_cam_search_index;
				g_cam_search_index = 0;
				i = g_cam_search_index;
			}
		}
	}
	alert("未找到匹配摄像机！");
	document.getElementById("txt_searchcam").focus();
	document.getElementById("txt_searchcam").select();
	return;
}
function CheckScanNameOk(name)
{
	// 检查名称是否合法
	
	return true;
}
function CheckScanName(obj)
{
	var name = obj.value.Trim();
	
	var bOk = CheckScanNameOk(name);
	if(bOk == false)
	{
		alert("巡检方案的名称不合法！");
		obj.focus();
		obj.select();
		return false;
	}
	return true;
}
function CheckScanName2(obj)
{
	var name = obj.value;
	// 去除左右两边的空格
	name = name.Trim();
	obj.value = name;
	
	CheckScanName(obj)
}
function UpdateScan(type)
{
	if(g_web_user_ok == false)
	{
		alert("您无权限操作");
		return;
	}
	// 1-新增  2-删除  3-更新
	var obj_scanname = document.getElementById("txt_scan_name");
	var obj_scanlist = document.getElementById("scan_list");
	var index_scanlist = obj_scanlist.selectedIndex;
	if(obj_scanlist.selectedIndex <= 0 && type != 1)
	{
		alert("请先选择一个巡检方案，再进行操作！");
		return;
	}
	// 禁用下拉选择巡检方案列表，防止保存方案时出错
	obj_scanlist.disabled = true;
	
	// 显示确认、取消按钮
	if(type == 1 || type == 3)
	{
		document.getElementById("a_confirmscan").style.display = "inline";
		document.getElementById("a_cancelscan").style.display = "inline";
		obj_scanname.style.display = "inline";
		document.getElementById("label_scan_name").style.display="none";
		
		document.getElementById("a_updatescan").style.display="none";
		document.getElementById("a_addscan").style.display="none";
		document.getElementById("a_delscan").style.display="none";
	}
	
	if(type == 1) // 新增
	{
		g_scanOpType = 1;
		document.getElementById("span_scan_op_status").innerHTML="正在设置新增的巡检方案";
		obj_scanname.value = "请输入新的巡检方案名称";
		obj_scanname.focus();
		obj_scanname.select();
		
		return;
	}
	else if(type == 3) // 更新
	{
		g_scanOpType = 3;
		document.getElementById("span_scan_op_status").innerHTML= "正在更新巡检方案  " + obj_scanname.value;
		obj_scanname.focus();
		obj_scanname.select();
		
		return;
	}
	else if(type == 2) // 删除
	{
		g_scanOpType = 2;
		document.getElementById("f_scan_name").value = escape(obj_scanlist.options[index_scanlist].text);
		document.getElementById("f_scan_id").value = obj_scanlist.options[index_scanlist].value;
		document.getElementById("f_scan_cam").value = "0";// 删除操作，可填任意值
		document.getElementById("f_scan_loop").value = 0;// 删除操作，可填任意值
		document.getElementById("f_scan_time").value = 0;// 删除操作，可填任意值
		document.getElementById("f_scan_type").value = 2;
		document.getElementById("f_scan_user").value = g_web_user;
		document.getElementById("f_scan_svr").value = g_svr_ip;
		var strMsg = "删除巡检方案："+ obj_scanname.value + "\n将会停止当前的巡检工作，是否继续？";
		if( confirm(strMsg) )
		{
			document.getElementById("form_scandata").action="scan.php?user=" + g_web_user;
			document.getElementById("form_scandata").submit();
		}
		else
		{
			// 取消删除，使能巡检方案下拉菜单
			obj_scanlist.disabled = false;
		}
		return;
	}
	/*

	*/
	return;
}
function SaveScan()
{
	// 1-新增  2-删除  3-更新
	if(g_scanOpType != 1 && g_scanOpType != 3)
	{
		return;
	}
	// 必须选择一个或多个摄像机
	if(g_sel_camlist.length <= 0)
	{
		alert("请先选择一个或者多个摄像机");
		return;
	}
	var obj_scanname = document.getElementById("txt_scan_name");
	if(obj_scanname.value == '')
	{
		alert("请输入一个有效的巡检名称");
		obj_scanname.focus();
		obj_scanname.select();
		return;
	}
	// 如果新增巡检方案，检查名称是否重复
	if(g_scanOpType == 1)
	{
		for(var i=0; i<g_scan_list.length; i++)
		{
			if(g_scan_list[i].name == obj_scanname.value)
			{
				alert("巡检方案名称存在重复，请使用不同的名称！");
				obj_scanname.focus();
				obj_scanname.select();
				return;	
			}
		}
	}
	// 生成提示字符串
	var strMsg;
	switch(g_scanOpType)
	{
		case 1:
			strMsg = "新增巡检方案："+ obj_scanname.value;
			break
		case 3:
			strMsg = "更新巡检方案："+ obj_scanname.value;
			break;
		default:
			alert("无效的巡检方案设置！");
			return;
	}
	strMsg += "\n将会停止当前的巡检工作，是否继续？";

	var obj_scan_time = document.getElementById("sel_scan_time");
	var index_scantime = obj_scan_time.selectedIndex;
	// 设置相机列表
	var cam_list = "";
	for(var i=0; i<g_sel_camlist.length; i++)
	{
		// 最后一个元素，不加分隔符
		if((i+1)>=g_sel_camlist.length) 
			cam_list += g_sel_camlist[i].id;
		else
			cam_list += g_sel_camlist[i].id + ";";
	}
	document.getElementById("f_scan_cam").value = cam_list;
	document.getElementById("f_scan_name").value = escape(obj_scanname.value);
	if(g_scanOpType == 1) // 新增加点由服务器生成ID
	{
		document.getElementById("f_scan_id").value = 0;
	}
	else
	{
		var obj_scanlist = document.getElementById("scan_list");
		var index_scanlist = obj_scanlist.selectedIndex;
		document.getElementById("f_scan_id").value = obj_scanlist.options[index_scanlist].value;
	}
	document.getElementById("f_scan_loop").value = 0;
	document.getElementById("f_scan_time").value = obj_scan_time.options[index_scantime].value;
	document.getElementById("f_scan_type").value = g_scanOpType;
	document.getElementById("f_scan_user").value = g_web_user;
	document.getElementById("f_scan_svr").value = g_svr_ip;
	if( confirm(strMsg) )
	{
		document.getElementById("form_scandata").action="scan.php?user=" + g_web_user;
		document.getElementById("form_scandata").submit();
	}
}
function CancelScan()
{
	if( ! confirm("取消当前巡检方案的编辑？") )
	{
		return;
	}
	var obj_scanname = document.getElementById("txt_scan_name");
	var obj_scanlist = document.getElementById("scan_list");
	var index_scanlist = obj_scanlist.selectedIndex;
	// 取消操作，使能巡检方案下拉菜单
	obj_scanlist.disabled = false;
	obj_scanname.style.display = "none";
	document.getElementById("label_scan_name").style.display="inline";
	document.getElementById("a_confirmscan").style.display = "none";
	document.getElementById("a_cancelscan").style.display = "none";
	
	document.getElementById("a_updatescan").style.display="inline";
	document.getElementById("a_addscan").style.display="inline";
	document.getElementById("a_delscan").style.display="inline";
	
	document.getElementById("span_scan_op_status").innerHTML="";
	if(index_scanlist>0)
	{
		obj_scanname.value = obj_scanlist.options[index_scanlist].text;
	}
	else
	{
		obj_scanname.value = "";
	}

}
function onWebResize()
{
	var obj_vodplayer = document.getElementById("scan_player");
	var width = document.body.clientWidth;
	var heigh = document.body.clientHeight;

	var h = heigh - 30/*顶部信息栏区域高度*/ - 30/*控制操作栏区域高度*/ - 120/*底部设置区域高度*/ - 25/*额外边界*/;
    if(h<0)
    {
        h=0;
    }
	obj_vodplayer.style.width = "100%";
	obj_vodplayer.style.height = h;
	document.getElementById("div_cameralist").style.height = heigh*0.60;
	document.getElementById("div_seleced_cameralist").style.height = heigh*0.35-25;
	return;
}
