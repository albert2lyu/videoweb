
/*
 * ˵�����ж�IP�Ƿ���ȷ
 * 
 * created by �����, 2009-12-24
 */
function IsIpOk(ipaddr)
{
	// �жϳ���
	var iplen = ipaddr.length;
	if( iplen<7/*1.1.1.1*/ || iplen>15/*100.100.100.100*/ )
	{
		return false;
	}
	
	// �ж���Ч�ַ�
	var valid_char = "0123456789.";
	for(var i=0; i<iplen; i++)
	{
		var chr = ipaddr.charAt(i);
		if( valid_char.indexOf(chr) == -1 )
		{
			return false;
		}
	}
	
	// �ж�ÿ���ֶεĴ�С
	var field_arr = new Array();
	field_arr = ipaddr.split(".");
	if( field_arr.length != 4 )
	{
		return false;
	}
	
	// ������0.0.0.0
	if( field_arr[0]==0 && field_arr[1]==0 && field_arr[2]==0 && field_arr[3]==0 )
	{
		return false;
	}
	// ��127.0.0.1
	if( field_arr[0]==127 && field_arr[1]==0 && field_arr[2]==0 && field_arr[3]==1 )
	{
		return false;
	}
	// ��255.255.255.255
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
 * ��������Hostname�����Ƿ���ȷ(HOSTNAME)
 */
function IsHostnameOk(name)
{
	// �жϳ���
	var namelen = name.length;
	if( namelen==0 || namelen>16 )
	{
		return false;
	}
	
	// �ж���Ч�ַ�
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
 * ��������LVM�����Ƿ���ȷ(HOSTNAME)
 */
function IsLvmNameOk(name)
{
	// �жϳ���
	var namelen = name.length;
	if( namelen==0 || namelen>16 )
	{
		return false;
	}
	
	// �ж���Ч�ַ�
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
	// �жϳ���
	var namelen = name.length;
	if( namelen>15 || namelen==0)
	{
		return false;
	}
	
	// �ж���Ч�ַ�
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


