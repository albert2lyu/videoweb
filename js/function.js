
/*
 * 说明：判断IP是否正确
 * 
 * created by 王大典, 2009-12-24
 */
function IsIpOk(ipaddr)
{
	// 判断长度
	var iplen = ipaddr.length;
	if( iplen<7/*1.1.1.1*/ || iplen>15/*100.100.100.100*/ )
	{
		return false;
	}
	
	// 判断有效字符
	var valid_char = "0123456789.";
	for(var i=0; i<iplen; i++)
	{
		var chr = ipaddr.charAt(i);
		if( valid_char.indexOf(chr) == -1 )
		{
			return false;
		}
	}
	
	// 判断每个字段的大小
	var field_arr = new Array();
	field_arr = ipaddr.split(".");
	if( field_arr.length != 4 )
	{
		return false;
	}
	
	// 不能是0.0.0.0
	if( field_arr[0]==0 && field_arr[1]==0 && field_arr[2]==0 && field_arr[3]==0 )
	{
		return false;
	}
	// 非127.0.0.1
	if( field_arr[0]==127 && field_arr[1]==0 && field_arr[2]==0 && field_arr[3]==1 )
	{
		return false;
	}
	// 非255.255.255.255
	if( field_arr[0]==255 && field_arr[1]==255 && field_arr[2]==255 && field_arr[3]==255 )
	{
		return false;
	}
	
	for(var i=0; i<field_arr.length; i++)
	{
		if( field_arr[i] > 255 || field_arr[i]=="")
		{
			return false;
		}
	}
	
	return true;
}

/*
 * 检查输入的Hostname名称是否正确(HOSTNAME)
 */
function IsHostnameOk(name)
{
	// 判断长度
	var namelen = name.length;
	if( namelen==0 || namelen>16 )
	{
		return false;
	}
	
	// 判断有效字符
	var valid_char = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_.-";
	for(var i=0; i<namelen; i++)
	{
		var chr = name.charAt(i);
		if( valid_char.indexOf(chr) == -1 )
		{
			return false;
		}
	}
	
	return true;
}

/*
 * 检查输入的LVM名称是否正确(HOSTNAME)
 */
function IsLvmNameOk(name)
{
	// 判断长度
	var namelen = name.length;
	if( namelen==0 || namelen>16 )
	{
		return false;
	}
	
	// 判断有效字符
	var valid_char = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_";
	for(var i=0; i<namelen; i++)
	{
		var chr = name.charAt(i);
		if( valid_char.indexOf(chr) == -1 )
		{
			return false;
		}
	}
	
	return true;
}

function IsUnitNameOk(name)
{
	// 判断长度
	var namelen = name.length;
	if( namelen>15 || namelen==0)
	{
		return false;
	}
	
	// 判断有效字符
	var valid_char = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_-";
	for(var i=0; i<namelen; i++)
	{
		var chr = name.charAt(i);
		if( valid_char.indexOf(chr) == -1 )
		{
			return false;
		}
	}
	
	return true;
}


