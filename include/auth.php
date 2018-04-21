<?php
require_once("function.php");

if (isset($GLOBALS["authenticated"]))
	unset($GLOBALS["authenticated"]);

if (isset($GLOBALS["authuser"]))
	unset($GLOBALS["authuser"]);


function check_auth($username, $password)
{
	$bPassed = FALSE;
	$account_file_path = "./account/" . $username;
	if(file_exists($account_file_path))
	{
		$account_file_handle = fopen($account_file_path, 'r');
		if($account_file_handle)
		{
	        $buffer = fgets($account_file_handle, 33);
	
	        $password_md5 = md5($password);
	        if(strncmp($password_md5, $buffer, 32) == 0)
	        {
	            $bPassed = TRUE;
	        }
	
	        fclose($account_file_handle);
		}
	}

	return $bPassed;
}

function check_authenticated($username, $password)
{
	if (check_auth($username, $password)==TRUE)
	{
		if ($username == "admin")
		{
			$GLOBALS["authenticated"] = 1;
			$GLOBALS["authuser"] = $username;
		}
		else
		{
			$GLOBALS["authenticated"] = 0;
			$GLOBALS["userauthenticated"] = 1;
			$GLOBALS["authuser"] = $username;
		}
	
		return TRUE;
	}
	else
	{
		$GLOBALS["authenticated"] = 0;
		$GLOBALS["authuser"] = "";
		
		return FALSE;
	}
}
/*
 *�޸�����
 *����ֵ0-�ɹ���1-���������2-�������������벻ͬ ,-1-ʧ��
 */
function change_password($username,$old_password, $new_password, $confirm_password)
{
	$old_pass = FALSE;
	$account_file_path = "./account/" . $username;
	// ��ȡ������
	if(file_exists($account_file_path))
	{
		SetFileMode($account_file_path, 'w');
		$account_file_handle = fopen($account_file_path, 'r');
		if($account_file_handle != FALSE)
		{
			    $buffer = fgets($account_file_handle, 33);
		
		        $password_md5 = md5($old_password);
		        if(strncmp($password_md5, $buffer, 32) == 0)
		        {
		            $old_pass = TRUE;
		        }
		        else
		        {
		        	fclose($account_file_handle);
		        	return 1;
		        }
		        fclose($account_file_handle);
		}
		else
		{
			return -1;
		}
	}
	else
	{
		return -1;
	}
	
	//�Ƚ���������������Ƿ���ͬ
	if($new_password != $confirm_password)
	{
		return 2;
	}
	
	
	//�޸��ʻ��ļ�
	if(file_exists($account_file_path))
	{
    	$account_file_handle = fopen($account_file_path, 'w');
		if($account_file_handle != FALSE)
		{
	        $password_md5 = md5($new_password);
			fwrite($account_file_handle, $password_md5);
	        fclose($account_file_handle);
		}
		else
		{
			return -1;
		}
	}
	else
	{
		return -1;
	}
	
	return 0;
}
?>
