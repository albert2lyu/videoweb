
function loadDoc(xmlhttp, url,sub_fuc)
{
	if (window.XMLHttpRequest)
	{// for Firefox, Opera, IE7, etc.
		xmlhttp = new XMLHttpRequest();
	}
	else if (window.ActiveXObject)
	{// for IE6, IE5
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	}
	
	if (xmlhttp != null)
	{
		xmlhttp.onreadystatechange = function(){
				sub_fuc(xmlhttp);
			};
		xmlhttp.open("GET",url,true);
		xmlhttp.send(null);
	}
	else
	{
		//alert("Your browser does not support XMLHTTP.");
	}
	return xmlhttp;
}
         
function load_server_time_ajax(xmlhttp)
{
	if (xmlhttp.readyState==4)
  	{// 4 = "loaded"
		if (xmlhttp.status==200)
		{// 200 = "OK"
			document.getElementById('systime_div').innerHTML=xmlhttp.responseText;
		}
	}
}

function load_ctl_time_ajax(xmlhttp)
{
	if (xmlhttp.readyState==4)
  	{// 4 = "loaded"
		if (xmlhttp.status==200)
		{// 200 = "OK"
			document.getElementById('ctltime_div').innerHTML=xmlhttp.responseText;
		}
	}
}

function locate_ajax(xmlhttp)
{
	//alert(xmlhttp.status + " :xxxx: " + text);
	if (xmlhttp.readyState==4)
  	{// 4 = "loaded"
		if (xmlhttp.status==200)
		{// 200 = "OK"
			var text = xmlhttp.responseText;
			//alert("xxxx: " + text.length);
			if(text == "")
			{
				//alert("OK!");
			}
			else
			{
				//alert("Failed: " + text);
			}
		}
	}
}

function uptime_ajax(xmlhttp)
{
	if (xmlhttp.readyState==4)
  	{// 4 = "loaded"
		if (xmlhttp.status==200)
		{// 200 = "OK"
			document.getElementById('uptime_div').innerHTML=xmlhttp.responseText;
		}
	}
}

function system_status_ajax(xmlhttp)
{
	if (xmlhttp.readyState==4)
  	{// 4 = "loaded"
		if (xmlhttp.status==200)
		{// 200 = "OK"
			document.getElementById('system_status_div').innerHTML=xmlhttp.responseText;
		}
	}
}

function process_status_ajax(xmlhttp)
{
	if (xmlhttp.readyState==4)
  	{// 4 = "loaded"
		if (xmlhttp.status==200)
		{// 200 = "OK"
			document.getElementById('process_status').innerHTML=xmlhttp.responseText;
		}
	}
}

function vis_state_ajax(xmlhttp)
{
	if (xmlhttp.readyState==4)
  	{// 4 = "loaded"
		if (xmlhttp.status==200)
		{// 200 = "OK"
			document.getElementById('vismanager').innerHTML=xmlhttp.responseText;
		}
	}
}

function mvp_state_ajax(xmlhttp)
{
	if (xmlhttp.readyState==4)
  	{// 4 = "loaded"
		if (xmlhttp.status==200)
		{// 200 = "OK"
			document.getElementById('mvpmanager').innerHTML=xmlhttp.responseText;
		}
	}
}
function ping_ajax(xmlhttp)
{
	if (xmlhttp.readyState==4)
  	{// 4 = "loaded"
		if (xmlhttp.status==200)
		{// 200 = "OK"
			document.getElementById('ping_output_td').innerHTML=xmlhttp.responseText;
		}
	}
}


