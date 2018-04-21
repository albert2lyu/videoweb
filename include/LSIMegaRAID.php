<?php
/*
 * ˵����1�� ��ȡ������LSI MegaRAID�洢���ƿ��Ĺ��ܲ��������ȡ�洢���ͺš�����RAID��
 *          ɾ��raid����ȡ������Ϣ��
 * 
 * ������֧��   ��LSI MegaRAID 9260ϵ�п��ƿ�
 * �ײ������� storcli�����װ����װ��Ĭ����/opt/MegaRAID/storcli/storcli64��
 *           �����ֶ�����storcli���ln -sfv /opt/MegaRAID/storcli/storcli64 /usr/bin/storcli��
 * 		
 * Created by ����䣬2017-11-26
 */

require_once("function.php");

$lang=load_lang();

$controller_prefix_str=array(
	"������",
	"Controller"
);
$io_cached_str=array(
        "����I/O",
        "Cached I/O"
);
$io_direct_str=array(
        "ֱ��I/O",
        "Direct I/O"
);
$read_ra_str=array(
        "Ԥ��",
        "Read-Ahead"
);
$read_nora_str=array(
        "��Ԥ��",
        "No Read-Ahead"
);
$write_wt_str=array(
        "ͨ��д",
        "Write-Through"
);
$write_wb_str=array(
        "��д",
        "Write-Back"
);
$write_awb_str=array(
        "���ǻ�д",
        "Always Write-Back"
);

define('STORCLI', "export LANG=C; /usr/bin/sudo /usr/bin/storcli");

// ������Ϣ
define("CLI",  "storcli: ");
define("C_ERR_RUN_FAIL", CLI . "command run failure");
define("C_ERR_NO_OUTPUT",  CLI . "no output");
define("C_ERR_PARSE_FAIL", CLI . "parse output failure");


/*
 * LSI RAID��ͨ�ú���
 */
class CLSIMegaRAIDFunc
{
	/*
	 * ˵������ȡstorcli ����ִ�к����json�������
	 * ������$cmd : ��Ҫִ�е�storcli����ز�����������ַ���
	 *       $msg������������Ϣ���ɹ�ʱ�˱���Ϊ�գ�
	 *       $status�� ����ִ�е�״̬������storcli���ص�Command Status����TRUE-����ִ�еĲ����ɹ���FALSE-����ִ�еĲ���ʧ��
	 * ���أ��������ؽ���json����������,���򷵻�FALSE����������ͨ��$msg���أ�
	 */
	function GetStorcliRet($cmd, &$msg, &$status)
	{
		$msg = "";
		$status = FALSE;
		$Arr_Json = array();
	
		$output = shell_exec($cmd);
		if( $output === NULL)
		{
			$msg = C_ERR_NO_OUTPUT;
			return FALSE;
		}
	
		$Arr_Json = json_decode($output, true);
		if($Arr_Json == NULL)
		{
			$msg = C_ERR_PARSE_FAIL;
			return FALSE;
		}
		
		if( !isset($Arr_Json["Controllers"][0]["Command Status"]["Status"]) )
		{
			$msg = C_ERR_RUN_FAIL;
			return FALSE;
		}
		
		if(Trim($Arr_Json["Controllers"][0]["Command Status"]["Status"]) == "Success")
		{
			//$msg = $Arr_Json["Controllers"][0]["Command Status"]["Description"];
			$status = TRUE;
		}
		else
		{
		    $msg = $Arr_Json["Controllers"][0]["Command Status"]["Description"];
		}
	
		
		return $Arr_Json;
	}
	
	/*
	 * ˵�������ÿ�����ʱ��
	 * ������$cid��������ID
	 *       $time��ʱ�䣬��ʽ"20171218 12:38:20"����"systemtime"������ϵͳʱ�����á�
	 * 		 $msg�����ڷ��ش���������Ϣ����
	 * ���أ���������TRUE�����򷵻�FALSE����������ͨ��$msg���أ�
	 */
	function SetCtlTime($cid, $time="systemtime", &$msg="")
	{
	    $msg = "";
        $time = strtolower(trim($time));
	    $command = STORCLI . " " . "/c" . $cid . " set time=" . $time . " J";
	    /*�����ʾ����
	     {
        "Controllers":[
        {
        	"Command Status" : {
        		"Controller" : 0,
        		"Status" : "Success",
        		"Description" : "None"
        	},
        	"Response Data" : {
        		"Controller Properties" : [
        			{
        				"Ctrl_Prop" : "systemtime",
        				"Value" : "2017/12/16 12:53:09"
        			}
        		]
        	}
        }
        ]
        }
	    */
	    
	    $Arr_Json = $this->GetStorcliRet($command, $msg, $status);
	    if(  $Arr_Json === FALSE || $status === FALSE)
	    {
	        return FALSE;
	    }
	    
	    return TRUE;
	}
	
	/*
	 * ˵������ȡ������ʱ��
	 * ������$cid��������ID
	 * 		 $msg�����ڷ��ش���������Ϣ����
	 * ���أ���������ʱ��"",���򷵻�FALSE����������ͨ��$msg���أ�
	 */
	function GetCtlTime($cid, &$msg="")
	{
	    $msg = "";
	    $command = STORCLI . " " . "/c" . $cid . " show time J";
	    /* �����ʾ����
	     [root@mvp web]# storcli /c0 show time J
            {
            "Controllers":[
            {
            	"Command Status" : {
            		"Controller" : 0,
            		"Status" : "Success",
            		"Description" : "None"
            	},
            	"Response Data" : {
            		"Controller Properties" : [
            			{
            				"Ctrl_Prop" : "Time",
            				"Value" : "2017/12/15 23:01:27"
            			}
            		]
            	}
            }
            ]
            }
	     */
	    $status = FALSE;
	    $Arr_Json = $this->GetStorcliRet($command, $msg, $status);
	    if(  $Arr_Json === FALSE || $status === FALSE )
	    {
	        return FALSE;
	    }
	    $ctl_time = $Arr_Json["Controllers"][0]["Response Data"]["Controller Properties"][0]["Value"];
	    $rettime = str_replace("/", "-", trim($ctl_time));
	    return $rettime;
	}
	
	/*
	 * ˵�������ÿ�������VD����
	 * ������$cid��������ID
	 *       $vid��VD ID
	 *       $property�������õ���������
	 *       $value�������õ�����ֵ
	 * 		 $msg�����ڷ��ش���������Ϣ���ɹ�Ϊ��""��
	 * ���أ���������TRUE�����򷵻�FALSE����������ͨ��$msg���أ�
	 */
	function SetVdProperty($cid, $vid, $property, $value, &$msg="")
	{
	    $msg = "";
	    $command = STORCLI . " " . "/c" . $cid . "/v" . $vid . " set " . $property . "=" . $value . " J";
	    $status = FALSE;
	    $Arr_Json = $this->GetStorcliRet($command, $msg, $status);
	    //print $command . "<br>";
	    //print_r($Arr_Json);print "<br>";
	    if(  $Arr_Json === FALSE || $status === FALSE)
	    {
	        return FALSE;
	    }
	    return TRUE;
	}
	

	/*
	 * ˵��������RAID
	 * ������$cid�� ������ID
	 *       $type: raid���ͣ���"raid0"  "raid5"...
	 *       $name: raid������
	 *       $drives: �����Ĵ�����Ϣ����"4:8,4:9" (enclosureID:SlotID����ʹ��","�ֿ�)
	 *       $io: io���ԣ�����"direct"����"cached"
	 *       $write: д���ԣ�����"wt"/"wb"/"awb"
	 *       $read�������ԣ�����"nora"/"ra"
	 *       $strip��strip��С������8, 16, 32, 64, 128, 256, 512, 1024����λKB��
	 *       $pdsperarray��ÿ��������ĸ�������������raid10/50/60
	 *       $msg�����ڷ��ش���������Ϣ���ɹ�Ϊ��""��
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
	 */
	function AddVD($cid, $type, $name, $drives, $io="direct", $write="wb", $read="ra", $strip="256", $pdsperarray=0, &$msg="")
	{
	    $msg = "";
	    $name = trim($name);
	    if($name == "")
	    {
	        $name = "vd" . time();
	    }
	
	    $command = STORCLI . " /c" . $cid . " add vd " . $type . " name=" . $name .
	           " drives=" . $drives . " " . $io . " " . $write . " " . $read .
	           " strip=" . $strip;
	    if($pdsperarray > 0)
	    {
	        $command .= " pdperarray=" . $pdsperarray;
	    }
	    $command .= " J";
        //print $command."<br>";
        
	    $status = FALSE;
	    $Arr_Json = $this->GetStorcliRet($command, $msg, $status);
	    if(  $Arr_Json === FALSE || $status === FALSE)
	    {
	        return FALSE;
	    }
	    return TRUE;
	}
	
	/*
	 * ˵����ɾ���Ѵ���RAID
	 * ������$cid�� ������ID
	 *       $vid��RAID id
	 *       $msg�����ڷ��ش���������Ϣ���ɹ�Ϊ��""��
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
	 */
	function DeleteVD($cid, $vid, &$msg="")
	{
	    $msg = "";
	    $szVDID = "/c" . $cid . "/v" . $vid;
	    $command = STORCLI. " " . $szVDID . " del force J";
	    $status = FALSE;
	    $Arr_Json = $this->GetStorcliRet($command, $msg, $status);
	    if(  $Arr_Json === FALSE || $status === FALSE)
	    {
	        return FALSE;
	    }
	    return TRUE;
	}
	
	/*
	 * ˵������ȡ���̳�ʼ������
	 * ������$cid�� ������ID
	 *       $eid��enclosure ID
	 * 		 $sid��slot id
	 *       $msg�����ڷ��ش���������Ϣ���ɹ�Ϊ��""��
	 * ���أ��ɹ����س�ʼ�����ȣ�"50%"����ʧ�ܷ���FALSE
	 */
	function GetPdInitProgress($cid, $eid, $sid, &$msg="")
	{
	    $msg = "";
	    $szPDID = "/c" . $cid . "/e" . $eid . "/s" . $sid;
	     
	    $command = STORCLI . " " . $szPDID . " show initialization J";
	    /* �������ʾ��
        [root@mvp web]# storcli /c0/e4/s14 show initialization J
        {
        "Controllers":[
        {
        	"Command Status" : {
        		"Controller" : 0,
        		"Status" : "Success",
        		"Description" : "Show Drive Initialization Status Succeeded."
        	},
        	"Response Data" : [
        		{
        			"Drive-ID" : "/c0/e4/s14",
        			"Progress%" : 4,
        			"Status" : "In progress",
        			"Estimated Time Left" : "-"
        		}
        	]
        }
        ]
        }
                û�н��г�ʼ���ķ���ʾ����
        [root@mvp web]# storcli /c0/e4/s14 show initialization J
        {
        "Controllers":[
        {
        	"Command Status" : {
        		"Controller" : 0,
        		"Status" : "Success",
        		"Description" : "Show Drive Initialization Status Succeeded."
        	},
        	"Response Data" : [
        		{
        			"Drive-ID" : "/c0/e4/s14",
        			"Progress%" : "-",
        			"Status" : "Not in progress",
        			"Estimated Time Left" : "-"
        		}
        	]
        }
        ]
        }
	     */
	    $status = FALSE;
	    $Arr_Json = $this->GetStorcliRet($command, $msg, $status);
	    if(  $Arr_Json === FALSE || $status == FALSE)
	    {
	        return FALSE;
	    }
	    
	    $progress = "0%";
	    $Arr_RetInfo = $Arr_Json["Controllers"][0]["Response Data"][0];
	    if($Arr_RetInfo["Status"] == "In progress")
	    {
	        $progress = intval(trim($Arr_RetInfo["Progress%"])) . "%";
	    }
	    else if($Arr_RetInfo["Status"] == "Not in progress")
	    {
	        $msg = $Arr_RetInfo["Status"];
	        return FALSE;
	    }
	    else 
	    {
	        return FALSE;
	    }
	    return $progress;
	}
	
	/*
	 * ˵������ʼ���̳�ʼ��
	 * ������$cid�� ������ID
	 *       $eid��enclosure ID
	 * 		 $sid��slot id
	 *       $msg�����ڷ��ش���������Ϣ���ɹ�Ϊ��""��
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
	 */
	function StartPdInitialization($cid, $eid, $sid, &$msg="")
	{
	    $msg = "";
	    $szPDID = "/c" . $cid . "/e" . $eid . "/s" . $sid;
	     
	    $command = STORCLI . " " . $szPDID . " start initialization J";
	    /* �������ʾ��
        [root@mvp web]# storcli /c0/e4/s14 start initialization J
        {
        "Controllers":[
        {
        	"Command Status" : {
        		"Controller" : 0,
        		"Status" : "Success",
        		"Description" : "Start Drive Initialization Succeeded."
        	}
        }
        ]
        }
	     */
	    $status = FALSE;
	    $Arr_Json = $this->GetStorcliRet($command, $msg, $status);
	    if(  $Arr_Json === FALSE || $status === FALSE)
	    {
	        return FALSE;
	    }
	     
	    return TRUE;
	}
	

	/*
	 * ˵����ֹͣ���̵ĳ�ʼ��
	 * ������$cid�� ������ID
	 *       $eid��enclosure ID
	 * 		 $sid��slot id
	 *       $msg�����ڷ��ش���������Ϣ���ɹ�Ϊ��""��
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
	 */
	function StopPdInitialization($cid, $eid, $sid, &$msg="")
	{
	    $msg = "";
	    $szPDID = "/c" . $cid . "/e" . $eid . "/s" . $sid;
	
	    $command = STORCLI. " " . $szPDID . " stop initialization J";
	    /* �������ʾ��
	     [root@mvp web]# storcli /c0/e4/s14 set good force J
	     {
	     "Controllers":[
	     {
	     "Command Status" : {
	     "Controller" : 0,
	     "Status" : "Success",
	     "Description" : "Set Drive Good Succeeded."
	     }
	     }
	     ]
	     }
	     */
	    $status = FALSE;
	    $Arr_Json = $this->GetStorcliRet($command, $msg, $status);
	    if(  $Arr_Json === FALSE || $status === FALSE)
	    {
	        return FALSE;
	    }
	
	    return TRUE;
	}
	
	/*
	 * ˵������unconfigure bad �Ĵ��̸�Ϊunconfigure good
	 * ������$cid�� ������ID
	 *       $eid��enclosure ID
	 * 		 $sid��slot id
	 *       $init: TRUE-�޸ĳɹ���ʼ��ʼ�����̣�FALSE=�޸ĳɹ��󲻳�ʼ������
	 *       $msg�����ڷ��ش���������Ϣ���ɹ�Ϊ��""��
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
	 */
	function SetPdUbadToUgood($cid, $eid, $sid, $init, &$msg="")
	{
	    $msg = "";
	    $szPDID = "/c" . $cid . "/e" . $eid . "/s" . $sid;
	    
	    $command = STORCLI. " " . $szPDID . " set good force J";
	    //print $command . "<br>";
	    /* �������ʾ��
	    [root@mvp web]# storcli /c0/e4/s14 set good force J
        {
        "Controllers":[
        {
        	"Command Status" : {
        		"Controller" : 0,
        		"Status" : "Success",
        		"Description" : "Set Drive Good Succeeded."
        	}
        }
        ]
        }
	    */
	    $status = FALSE;
	    $Arr_Json = $this->GetStorcliRet($command, $msg, $status);
	    if(  $Arr_Json === FALSE || $status === FALSE)
	    {
	        return FALSE;
	    }
	    
	    // ������󣬳�ʼ�����̣���������Ծ��޷�ʹ��
	    if($init === TRUE)
	    {
	        $this->StartPdInitialization($cid, $eid, $sid, $msg);
	    }
	    
	    return TRUE;
	}
	
	/*
	 * ˵��������hotspare
	 * ������$cid�� ������ID
	 *       $eid��enclosure ID
	 * 		 $sid��slot id
	 * 		 $vids��raid��ID���飨Ϊָ����RAID�鴴��hotspare����Ϊ������ʱ����ȫ��hotspare
	 *       $msg�����ڷ��ش���������Ϣ���ɹ�Ϊ��""��
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
	 */
	function AddHotSpare($cid, $eid, $sid, $vids=array(), &$msg="")
	{
	    $msg = "";
	    $cid = intval($cid);
	    $szPDID = "/c" . $cid . "/e" . $eid . "/s" . $sid;
	    $command = "";
	    if(count($vids) == 0)
	    {
	        $command = STORCLI. " " . $szPDID . " add hotsparedrive J";
	    }
	    else
	    {
	        $dgs = array();
	        $objCtrlList = new CLSIMegaRAIDList();
	        $objCtrl = $objCtrlList->GetCtlObj($cid);
	        foreach($vids as $vid)
	        {
	            foreach ($objCtrl->VDs as $vd)
	            {
	                if($vd["VD"] == intval($vid))
	                {
	                    $dgs[] = $vd["DG"];
	                    break;
	                }
	            }
	        }
	        if(count($dgs) != 0)
	        {
	            $str_dgs = implode(",", $dgs);
	            $command = STORCLI. " " . $szPDID . " add hotsparedrive dgs=" . $str_dgs . " J";
	        }
	        else
	        {
	            $command = STORCLI. " " . $szPDID . " add hotsparedrive J";
	        }
	    }
	
	    $status = FALSE;
	    $Arr_Json = $this->GetStorcliRet($command, $msg, $status);
	    if(  $Arr_Json === FALSE || $status === FALSE)
	    {
	        return FALSE;
	    }
	    return TRUE;
	}
	
	/*
	 * ˵����ɾ��hotspare
	 * ������$strSID:���̱�ţ���ʽ"/c0/e4/s9"
	 *       $msg�����ڷ��ش���������Ϣ���ɹ�Ϊ��""��
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
	 */
	function DeleteHotSpareEx($strSID, &$msg="")
	{
	    $msg = "";
	    $command = STORCLI. " " . $strSID . " delete hotsparedrive J";
	    $status = FALSE;
	    $Arr_Json = $this->GetStorcliRet($command, $msg, $status);
	    if(  $Arr_Json === FALSE)
	    {
	        return FALSE;
	    }
	    return TRUE;
	}
	
	/*
	 * ˵����ɾ��hotspare
	 * ������$cid�� ������ID
	 *       $eid��enclosure ID
	 * 		 $sid��slot id
	 *       $msg�����ڷ��ش���������Ϣ���ɹ�Ϊ��""��
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
	 */
	function DeleteHotSpare($cid, $eid, $sid, &$msg="")
	{
	    $msg = "";
	    $szPDID = "/c" . $cid . "/e" . $eid . "/s" . $sid;
	    return $this->DeleteHotSpareEx($szPDID, $msg);
	}
	
	/*
	 * ˵������λ����
	 * ������$szPDID�����̱�ʶ����/c0/e4/s8
	 * 		 $time����λ���̶�����
	 * 		 $msg�����ڷ��ش���������Ϣ����
	 * ���أ���������TRUE��$msgΪ��""��,���򷵻�FALSE����������ͨ��$msg���أ�
	 */
	function LocatePD($szPDID, $time=20, &$msg="")
	{
		$msg = "";
		$command = STORCLI . " " . $szPDID . " start locate J";
		
		// ��λ����
		$status = FALSE;
		$Arr_Json = $this->GetStorcliRet($command, $msg, $status);
		if(  $Arr_Json === FALSE || $status === FALSE)
		{
			return FALSE;
		}
		
		if($time <= 0 || $time >= 600) $time=20;
		
		// ��ʱ��ȡ����λ����̨����������ú󲻵ȴ�ֱ�ӷ���
		$command = "/bin/sleep " . $time . " && " . STORCLI . " " . $szPDID . " stop locate J";
		shell_exec($command);
		return TRUE;
	}
}

class CLSIMegaRAIDList
{
	private $m_szLastErrorInfo;
	private $m_objCtls;
	private $m_objF;
	
	function __construct()
	{
		$this->m_szLastErrorInfo = "";
		$this->m_objCtls = array();
		$this->m_objF = new CLSIMegaRAIDFunc();
		$this->GetCtlInfoList();
	}
	
	/*
	 * ˵������ȡ���µĴ�����Ϣ
	 * ��������
	 * ���أ�������Ϣ��û�д�����Ϣ����FALSE
	 */
	function GetLastErrorInfo()
	{
		if( $this->m_szLastErrorInfo == "" )
		{
			return FALSE;
		}
		$error_info = $this->m_szLastErrorInfo;
		$this->m_szLastErrorInfo = "";
		return $error_info;
	}
	
	/*
	 * ˵������ȡ�������б�
	 * ��������
	 * ���أ��ɹ�����CLSIMegaRAID�������飬ʧ�ܷ���NULL
	 */
	function GetCtlList()
	{
		return $this->m_objCtls;
	}

	/*
	 * ˵������ȡ�ƶ�ID�Ŀ�����CLSIMegaRAID����
	 * ������$ctl_id��������ID
	 * ���أ��ɹ�����CLSIMegaRAID����ʧ�ܷ���NULL
	 */
	function GetCtlObj($ctl_id)
	{
		if(count($this->m_objCtls) <= 0)
		{
			return NULL;
		}
		foreach($this->m_objCtls as $ctl)
		{
			if($ctl->ID == intval($ctl_id))
			{
				return $ctl;
			}
		}
		return NULL;
	}
	
// --------------------------------------------˽��
	
	/*
	 * ˵������ȡ�������б�
	 * ��������
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
	 */
	private function GetCtlInfoList()
	{
		$this->m_szLastErrorInfo = "";
		$Controller_Count = 0;
		$Command = STORCLI . " show J";
		$Arr_Json = array();
	
		/*
		 �������ʾ����JSON����
			[root@mvp ~]# storcli show j
			{
			"Controllers":[
			{
			"Command Status" : {
			"Status Code" : 0,
			"Status" : "Success",
			"Description" : "None"
			},
			"Response Data" : {
			"Number of Controllers" : 1,
			"Host Name" : "mvp",
			"Operating System " : "Linux2.6.18-308.el5",
			"System Overview" : [
			{
			"Ctl" : 0,
			"Model" : "LSIMegaRAIDSAS9261-8i",
			"Ports" : 8,
			"PDs" : 5,
			"DGs" : 1,
			"DNOpt" : 0,
			"VDs" : 1,
			"VNOpt" : 0,
			"BBU" : "Msng",
			"sPR" : "On",
			"DS" : "1&2",
			"EHS" : "Y",
			"ASOs" : 2,
			"Hlth" : "Opt"
			}
			]
			}
			}
			]
			}
		*/
		$status = FALSE;
		$Arr_Json = $this->m_objF->GetStorcliRet($Command, $this->m_szLastErrorInfo, $status);
		if($Arr_Json === FALSE)
		{
			return FALSE;
		}
		
		// �����ɹ�
		//��ȡ����������
		$Controller_Count = intval($Arr_Json["Controllers"][0]["Response Data"]["Number of Controllers"]);
		if($Controller_Count <= 0)
		{
			return FALSE;
		}
	
		$Controllers = $Arr_Json["Controllers"][0]["Response Data"]["System Overview"];
		for($i=0; $i<$Controller_Count; $i++)
		{
			$Controller = array();
			/*
			 array(
				 "Ctl"=>"0", //������������
				 "Model"=>"LSIMegaRAIDSAS9261-8i",//�������ͺ�
				 "Ports"=>"8", //���������̽ӿ���
				 "PDs"=>"4", //���������
				 "DGs"=>"1", //��������
				 "VDs"=>"1", //���������
				 "BBU"=>"msng", //bbu״̬
				 "Hlth"=>"Opt", //������״̬
			);
			*/
			$Controller['Ctl'] = intval($Controllers[$i]['Ctl']);
			$Controller['Model'] = trim($Controllers[$i]['Model']);
			$Controller['Ports'] = trim($Controllers[$i]['Ports']);
			$Controller['PDs'] = intval($Controllers[$i]['PDs']);
			$Controller['DGs'] = intval($Controllers[$i]['DGs']);
			$Controller['VDs'] = intval($Controllers[$i]['VDs']);
			$Controller['BBU'] = trim($Controllers[$i]['BBU']);
			$Controller['Hlth'] = trim($Controllers[$i]['Hlth']);
			
			$ctl = new CLSIMegaRAID($Controller["Ctl"], $Controller);
			$this->m_objCtls[] = $ctl;
		}
	
		return TRUE;
	}

}



class CLSIMegaRAID
{
	// -----------------------------------------------��Ա��������
	// ��ʼ���Ƿ�ɹ�
	public $OK;
	// ���ƿ���Ϣ
	public $INFO;
	// ���ƿ�ID
	public $ID;
	// ������Ϣ�б�
	public $PDs;
	// VD(raid����Ϣ�б�)
	public $VDs;
	// �ȱ�����ϢHotSpare
	public $HSs;
	// ���������ȱ�����RAID�Ĵ���
	public $UPDs;
	
	//��¼���µĴ�����Ϣ
	private $m_szLastErrorInfo;
	private $m_objF;
	
	// -----------------------------------------------���г�Ա��������

	function __construct()
	{
		$this->m_szLastErrorInfo = "";
		$this->ID = -1;
		$this->INFO = array();
		$this->OK = FALSE;
		$this->m_objF = new CLSIMegaRAIDFunc();
		
		$argsList = func_get_args();
		$argsCnt = func_num_args();
		//�ж�Test���Ƿ���__constructxx����,����������Ϊ$f
		if(method_exists($this,$f='__construct' . $argsCnt)){
			//������xx������ʹ��call_user_func_array(arr1,arr2)����������,�ú����Ĳ���Ϊ�������飬ǰ�������Ϊ����˭($this)��ʲô($f)��������һ������Ϊ����
			call_user_func_array(array($this,$f),$argsList);
		}
	}
	
	function __construct2($id, $info)
	{
		$this->ID = $id;
		$this->INFO = $info;
		
		$this->OK = $this->Init();
	}
	
	/*
	 * ˵������ȡ���ƿ�������Ϣ
	 * ��������
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
	 */
	function Init()
	{
	    $this->m_szLastErrorInfo = "";
	    $this->PDs = array();
	    $this->VDs = array();
	    $this->HSs = array();
	    $this->UPDs = array();
	
	    // ����˳���ܸ���
	    // ��ȡ���д����б�
	    if($this->GetPdList() !== TRUE)
	    {
	        return FALSE;
	    }
	    // ��ȡ���д�����RAID����Ϣ
	    if($this->GetVdList() !== TRUE)
	    {
	        return FALSE;
	    }
	    // ��ȡ���д������ȱ�����Ϣ
	    if($this->GetHsList() !== TRUE)
	    {
	        return FALSE;
	    }
	    // ��ȡ���п��õĴ�����Ϣ
	    if($this->GetUPDsList() !== TRUE)
	    {
	        return FALSE;
	    }
	    
	    return TRUE;
	}
	
	/*
	 * ˵������ȡ���µĴ�����Ϣ
	 * ��������
	 * ���أ�������Ϣ��û�д�����Ϣ����FALSE
	 */
	function GetLastErrorInfo()
	{
		if( $this->m_szLastErrorInfo == "" )
		{
			return FALSE;
		}
		$error_info = $this->m_szLastErrorInfo;
		$this->m_szLastErrorInfo = "";
		return $error_info;
	}
	
	/*
	 * ˵��������ɨ�������������ɨ��������»�ȡ��������Ϣ��
	 * ��������
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
	 */
	function RescanController()
	{
		$this->m_szLastErrorInfo = "";
		return $this->Init();
	}
	
	/*
	 * ˵������ȡ�ƶ�RAID����Ϣ
	 * ������$vid: VD ID
	 * ���أ���������RAID����Ϣ���飬���򷵻�FALSE
	 */
	function GetVdInfoByID($vid)
	{
	    foreach($this->VDs as $vd)
	    {
	        if(intval($vid) == $vd["VD"])
	        {
	            return $vd;
	        }
	    }
	    
	    return FALSE;
	}
	
	/*
	 * ˵������ȡָ��������Ϣ
	 * ������
	 *     $eid: enclosure ID
	 *     $sid: slot ID
	 * ���أ��������ش�����Ϣ���飬���򷵻�FALSE
	 */
	function GetPdInfoByID($eid, $sid)
	{
	    foreach($this->PDs as $pd)
	    {
	        if(intval($eid) == $pd["EID"]
	           &&
	           intval($sid) == $pd["Slot"])
	        {
	            return $pd;
	        }
	    }
	     
	    return FALSE;
	}
	
	/*
	 * ˵������λ����
	 * ������$eid: enclosure ID
	 * 		 $pid�� slot id
	 *       $time����λ��������
	 * ���أ��������ش���slot ID���飬���򷵻�FALSE
	 */
	function LocatePD($eid, $pid, $time)
	{
		$szPDID = "/c" . $this->ID . "/e" . $eid . "/s" . $pid;
		return $this->m_objF->LocatePD($szPDID, $time, $this->m_szLastErrorInfo);
	}
	
	/*
	 * ˵������λ����
	 * ������$szPdId:����ID��/c0/e4/s8
	 *       $time����λ��������
	 * ���أ��������ش���slot ID���飬���򷵻�FALSE
	 */
	function LocatePD2($szPdId, $time)
	{
		return $this->m_objF->LocatePD($szPdId, $time, $this->m_szLastErrorInfo);
	}
	
	/*
	 * ˵������λVD���еĴ���
	 * ������$vdid��VD ID
	 *       $time����λ��������
	 * ���أ��������ش���slot ID���飬���򷵻�FALSE
	 */
	function LocateVD($vdid, $time=20)
	{
	    if($time <= 0 || $time>=30) $time=20;
	    $pdsLocated = array();
		foreach($this->VDs as $vd)
		{
			if($vd["VD"] != intval($vdid))
			{
				continue;
			}
            $pdsLocated = $vd["PDs"];
			break;
		}
		
		// ��λVD�еĴ���
		$status = FALSE;
		foreach($pdsLocated as $pd)
		{
		    $msg = "";
		    $command = STORCLI . " " . $pd["StrID"] . " start locate J";
		    
		    // ��λ����
		    $Arr_Json = $this->GetStorcliRet($command, $msg, $status);
		    if( $Arr_Json === FALSE || $status === FALSE )
		    {
		        continue;
		    }
		}
		set_time_limit(30);
		// ��ʱ������ֹͣ��λ
		sleep($time);
		
		foreach($pdsLocated as $pd)
		{
		    $msg = "";
		    $command = STORCLI . " " . $pd["StrID"] . " stop locate J";
		    
		    // ȡ����λ����
		    $Arr_Json = $this->GetStorcliRet($command, $msg, $status);
		    if(  $Arr_Json === FALSE || $status === FALSE)
		    {
		        continue;
		    }
		}
		return TRUE;
	}


// ----------------------------------------------------˽�г�Ա��������

	/*
	 * ˵������ȡ������Ϣ
	 * ��������
	 * ���أ��ɹ�����TRUE��ʧ�ܷ���FALSE
	 */
	private function GetPdList()
	{
		$this->m_szLastErrorInfo = "";
	
		// ��ȡ�����������ӵĴ��̸���
		if($this->INFO["PDs"] <= 0)
		{
			return TRUE;
		}
		// ��ȡ�������µ�enclosure�б�
		$listEnc = $this->GetEncsInfo();
		if($listEnc === FALSE)
		{
			return FALSE;
		}
	
		// ����enclosure���λ�ȡ������Ϣ
		foreach ($listEnc as $enc)
		{
			if(intval($enc["PD"]) <= 0)
			{
				continue;
			}
			$pdIds = $this->GetPdSlotIdListOfEnc($enc["EID"]);
			if($pdIds === FALSE)
			{
				return FALSE;
			}
			foreach($pdIds as $pdID)
			{
				$pd = array();
				$pd = $this->GetPdInfo($enc["EID"], $pdID);
				if($pd === FALSE)
				{
					return FALSE;
				}
				$this->PDs[] = $pd;
			}
		}
	
		return TRUE;
	}
	
	/*
	 * ˵������ȡ���ƿ��ϵ�raid���б�
	 * ��������
	 * ���أ���������TRUE�����򷵻�FALSE
	 */
	private function GetVdList()
	{
		$this->m_szLastErrorInfo = "";
		$listVdId = $this->GetVdIdList();
		if( $listVdId === FALSE)
		{
			return FALSE;
		}
	
		foreach($listVdId as $vd_id)
		{
			$vdInfo = array();
			$vdInfo = $this->GetVdInfo($vd_id);
			if($vdInfo !== FALSE)
			{
				$this->VDs[] = $vdInfo;
			}
		}
		// ���´��̵��ȱ���Ϣ
		$this->UpdateDhsInfoOfPd();
		return TRUE;
	}
	
	/*
	 * ˵������ȡ���ƿ��ϵ��ȱ����б�
	 * ��������
	 * ���أ���������TRUE�����򷵻�FALSE
	 */
	private function GetHsList()
	{
	    //"HSType"=>1, // �ȱ��������ͣ�0-���ȱ��̣�1-ȫ�֣�2-ָ����ĳ����ĳЩ������
	    //"DHS Array"=>array(0,1,2) // ָ���������еĴ�����ID���ȱ���ȫ���ȱ����߷��ȱ�ʱΪ������
	    foreach($this->PDs as $pd)
	    {
	        if($pd["HSType"] == 0)
	        {
	            continue;
	        }
	        $this->HSs[] = $pd;
	    }
	    return TRUE;
	}
	
	/*
	 * ˵������ȡ���ƿ��ϵĿ��õĴ���
	 * ��������
	 * ���أ���������TRUE�����򷵻�FALSE
	 */
	private function GetUPDsList()
	{
        foreach($this->PDs as $pd)
	    {
	        if($pd["State"] == "UGood" && // ����״̬����
	           $pd["VDID"] == -1 && // û����raid
	           $pd["HSType"] == 0 &&  // û�����ȱ�
	           $pd["IsInit"] == 0 // û�����ڳ�ʼ��
	          )
	        {
	            $this->UPDs[] = $pd;
	        }

	    }
	    return TRUE;
	}
	
	/*
	 * ˵������ȡ���ƿ��ϵ��ƶ�ID��raid��Ϣ
	 * ������$vd_id��raid���ID
	 * ���أ���������raid��Ϣ���飬���򷵻�FALSE
	 array(
	 "Ctl"=>0, // ������ID
	 "DG"=>0, // DG ID
	 "VD"=>0, // VD ID
	 "TYPE"=>"RAID5", // RAID����
	 "Name"=>"vd0_test", //RAID������
	 "Size"=>12.10 TB, // ��С
	 "Access"=>"RW", // ����Ȩ��
	 "Cache"=>"RWTC", // cache����ԭʼ�ַ�����ϵͳ���أ�
	 "io"=>array( // IO����
	           "Type"=>"direct",
	           "Text"=>"ֱ��IO"
	       ),
	  "wrcache"=>array( // д�����
	           "Type"=>"awb",
	           "Text"=>"���ǻ�д"
	             ),
	 "rdcache"=>array( // ��ȡ����
	           "Type"=>"ra",
	           "Text"=>"Ԥ��"
	           ),
	 "State"=>"Optl", // ״̬
	 "Strip"=>"512 KB", //strip��С
	 "Cdate"=>"2017-12-10", // ��������
	 "Ctime"=>"10:10:10 PM", // ����ʱ��
	 "IsOK"=>1, //��ȡVD��Ϣ�Ƿ���ȷ��0-ʧ�ܣ�1-��ȷ
	 "ErrMsg"=>"", // ��ȡVD��Ϣʱ������صĴ�����Ϣ������Ϊ��""
	 "PDs"=>array(
			 array(
				 "StrID"=>"/c0/e4/s8", // ���̱�ʶ
				 "Ctl"=>0, // ������ID
				 "EID"=>4, // enclosure ID
				 "EID:Slt"=>"4:8", // ����EID��slotID
				 "State"=>"Onln", //����״̬
				 "Slot"=>8, // ��ۺ�
				 "DG"=>0, // ����������ţ���-����ʾ�������κδ�����
				 "Size"=>"3.637 TB", // ��������
				 "Intf"=>"SATA", //���̽ӿ�����
				 "Model"=>"HUC109060CSS600", // �ͺ�
				 "Temperature"=>" 24C (75.20 F)", // �¶�
				 "S.M.A.R.T alert flagged by drive"=>"No", //SMART����
				 "Manufacturer"=>"ATA", // ����
				 "WWN"=>"5000c500932fe089", //ȫ��Ψһ��
				 "Firmware Revision"=>"TN02", //�̼��汾
				 "Link Speed"=>"6.0Gb/s"
			 ),
			 ...
	 	  ) // ���������б�
	 )
	 */
	private function GetVdInfo($vd_id)
	{
		$this->m_szLastErrorInfo = "";
		$vdInfo = array();
		$szVdId = "/c" . $this->ID . "/v" . $vd_id; // /c0/v0
		$command = STORCLI . " " . $szVdId . " show all J";// storcli /c0/v0 show all J
		$status = FALSE;
		$Arr_Json = $this->m_objF->GetStorcliRet($command, $this->m_szLastErrorInfo, $status);
		if($Arr_Json === FALSE)
		{
			return FALSE;
		}
		/* �������ʾ����
		   [root@shizong bin]# storcli /c0/v0 show all J
            {
            "Controllers":[
            {
            	"Command Status" : {
            		"Controller" : 0,
            		"Status" : "Success",
            		"Description" : "None"
            	},
            	"Response Data" : {
            		"/c0/v0" : [
            			{
            				"DG/VD" : "0/0",
            				"TYPE" : "RAID1",
            				"State" : "Optl",
            				"Access" : "RW",
            				"Consist" : "Yes",
            				"Cache" : "RWTD",
            				"Cac" : "-",
            				"sCC" : "ON",
            				"Size" : "558.406 GB",
            				"Name" : ""
            			}
            		],
            		"PDs for VD 0" : [
            			{
            				"EID:Slt" : "252:0",
            				"DID" : 2,
            				"State" : "Onln",
            				"DG" : 0,
            				"Size" : "558.406 GB",
            				"Intf" : "SAS",
            				"Med" : "HDD",
            				"SED" : "N",
            				"PI" : "N",
            				"SeSz" : "512B",
            				"Model" : "HUC109060CSS600 ",
            				"Sp" : "U",
            				"Type" : "-"
            			},
            			{
            				"EID:Slt" : "252:1",
            				"DID" : 1,
            				"State" : "Onln",
            				"DG" : 0,
            				"Size" : "558.406 GB",
            				"Intf" : "SAS",
            				"Med" : "HDD",
            				"SED" : "N",
            				"PI" : "N",
            				"SeSz" : "512B",
            				"Model" : "HUC109060CSS600 ",
            				"Sp" : "U",
            				"Type" : "-"
            			}
            		],
            		"VD0 Properties" : {
            			"Strip Size" : "256 KB",
            			"Number of Blocks" : 1171062784,
            			"VD has Emulated PD" : "No",
            			"Span Depth" : 1,
            			"Number of Drives Per Span" : 2,
            			"Write Cache(initial setting)" : "WriteThrough",
            			"Disk Cache Policy" : "Disk's Default",
            			"Encryption" : "None",
            			"Data Protection" : "Disabled",
            			"Active Operations" : "None",
            			"Exposed to OS" : "Yes",
            			"Creation Date" : "08-12-2017",
            			"Creation Time" : "10:13:28 AM",
            			"Emulation type" : "default",
            			"Is LD Ready for OS Requests" : "Yes",
            			"SCSI NAA Id" : "6e8611f0000a3f1021bd2248d478ff83"
            		}
            	}
            }
            ]
            }
		*/
		
		if( !isset($Arr_Json["Controllers"][0]["Response Data"]) )
		{
		    return FALSE;
		}

		$Arr_VdInfo = $Arr_Json["Controllers"][0]["Response Data"];
		$idx_vd = $szVdId; // "/c0/v0"
		// ��ȡraid�������Ϣ
		$vdInfo["Ctl"] = $this->ID;
		$arr_dgvd = explode("/", $Arr_VdInfo[$idx_vd][0]["DG/VD"]);
		$vdInfo["DG"] = intval($arr_dgvd[0]);
		$vdInfo["VD"] = $vd_id;
		$vdInfo["TYPE"] = $Arr_VdInfo[$idx_vd][0]["TYPE"];
		$vdInfo["Name"] = $Arr_VdInfo[$idx_vd][0]["Name"];
		$vdInfo["Size"] = $Arr_VdInfo[$idx_vd][0]["Size"];
		$vdInfo["Access"] = $Arr_VdInfo[$idx_vd][0]["Access"];
		$vdInfo["Cache"] = $Arr_VdInfo[$idx_vd][0]["Cache"];
		//IO����
		$vdInfo["io"] = $this->GetPolicyInfo("io", $vdInfo["Cache"]);
		// ��ȡ����
		$vdInfo["rdcache"] = $this->GetPolicyInfo("rdcache", $vdInfo["Cache"]);
		// д�����
		$vdInfo["wrcache"] = $this->GetPolicyInfo("wrcache", $vdInfo["Cache"]);
		$vdInfo["State"] = $Arr_VdInfo[$idx_vd][0]["State"];
		
		// ���������б�
		// raid�����������б�, ͨ��VD��DG�ʹ���PD��DG��ͬ�жϣ����������л�ȡ��Ϊ�˱����ȡVDʧ��ʱ�޷���ȡ�����б�
		foreach($this->PDs as &$pd)
		{
		    if($pd["DG"] == $vdInfo["DG"])
		    {
		        // ���´��̵�VD��Ϣ
		        $pd["VDID"] = $vdInfo["VD"];
				$pd["VDName"] = $vdInfo["Name"];
		        // ���뵽VD���еĴ����б�
		        $vdInfo["PDs"][] = &$pd;
		    }
		}
		
		
		// ��ȡ״̬�ɹ��������ȡ��ϸ��Ϣ
		if($status === TRUE)
		{
		    $vdInfo["IsOK"] = 1;
		    $vdInfo["ErrMsg"] = "";
		    
    		$idx_properties = "VD" . $vd_id . " Properties"; //"VD0 Properties"
    		
    		$vdInfo["Strip"] = $Arr_VdInfo[$idx_properties]["Strip Size"];
    		$szCdate = $Arr_VdInfo[$idx_properties]["Creation Date"];
    		//�ӡ���-��-�ꡱתΪ����-��-�ա�
    		$arr_date = explode("-", $szCdate);
    		krsort($arr_date);
    		$vdInfo["Cdate"] = implode("-", $arr_date);
    		$vdInfo["Ctime"] = $Arr_VdInfo[$idx_properties]["Creation Time"];
		}
		else 
		{
		    $vdInfo["IsOK"] = 0;
		    $vdInfo["ErrMsg"] = $Arr_Json["Controllers"][0]["Command Status"]["Detailed Status"][0]["ErrMsg"];
		    
		    $vdInfo["Strip"] = "";
		    $vdInfo["Cdate"] = "";
		    $vdInfo["Ctime"] = "";
		    
		}
	
		return $vdInfo;
	}
	
	/*
	 * ˵�������ݻ�ȡ��VD Cache��Ϣ����
	 * ������$policy����Ҫ��ȡ�Ĳ������ͣ�����IO����"io", ��ȡ����"rdcache", д�����"wrcache"
	 * 		 $value����Ҫ��������Ϣ��VD�е�Cache��Ϣ������"RWTC"
	 * ���أ��������ؽ�����Ϣ�����飬���򷵻�FALSE
	 *       array(
	 *         "Type"=>"cached", // ��������(����RAIDʱ�õ���ֵ)
	 *         "Text"=>"����I/O", // �������������������������ͷ�������
	 *       )
	 */
	private function GetPolicyInfo($policy, $value)
	{
	    global $io_cached_str, $io_direct_str;
	    global $read_ra_str, $read_nora_str;
	    global $write_wt_str, $write_wb_str, $write_awb_str;
	    global $lang;
	    $policy = strtolower( trim($policy) );
	    $value =strtolower( trim($value) );
	    $retArr = array();
	    $tmpStr = "";
	    switch($policy)
	    {
	        case "io":
	            $tmpStr = substr($value, -1);
	            if($tmpStr == "c")
	            {
	                $retArr["Type"] = "cached";
	                $retArr["Text"] = $io_cached_str[$lang];
	            }
	            else if($tmpStr == "d")
	            {
	                $retArr["Type"] = "direct";
	                $retArr["Text"] = $io_direct_str[$lang];
	            }
	            else
	            {
	                $retArr = FALSE;
	            }
	            break;
	        case "rdcache":
	            $tmpStr = substr($value, 0, 1);
	            if($tmpStr == "r")
	            {
	                $retArr["Type"] = "ra";
	                $retArr["Text"] = $read_ra_str[$lang];
	            }
	            else if($tmpStr == "n")
	            {
	                $retArr["Type"] = "nora";
	                $retArr["Text"] = $read_nora_str[$lang];
	            }
	            else
	            {
	                $retArr = FALSE;
	            }
	            break;
	        case "wrcache": // if˳���ܸ�
	            if(strstr($value, "awb") !== FALSE)
	            {
	                $retArr["Type"] = "awb";
	                $retArr["Text"] = $write_awb_str[$lang];
	            }
	            else if(strstr($value, "wb") !== FALSE)
	            {
	                $retArr["Type"] = "wb";
	                $retArr["Text"] = $write_wb_str[$lang];
	            }
	            else if(strstr($value, "wt") !== FALSE)
	            {
	                $retArr["Type"] = "wt";
	                $retArr["Text"] = $write_wt_str[$lang];
	            }
	            else
	            {
	                $retArr = FALSE;
	            }
	            break;
	        default:
	            $retArr = FALSE;
	            break;
	    }
	    
	    return $retArr;
	}
	
	/*
	 * ˵������ȡ���ƿ��ϵ��ƶ�Enclosure���ƶ�������Ϣ
	 * ������$enc_id��enclosure��ID
	 * 		 $slot_id�����̲��ID
	 * ���أ��������ش�����Ϣ�����飬���򷵻�FALSE
	 array
	 (
	 "StrID"=>"/c0/e4/s8", // ���̱�ʶ
	 "Ctl"=>0, // ������ID
	 "EID"=>4, // enclosure ID
	 "EID:Slt"=>"4:8", // ����EID��slotID
	 "State"=>"Onln", //����״̬
	 "Slot"=>8, // ��ۺ�
	 "DG"=>0, // ����������ţ���-����ʾ�������κδ�����
	 "Size"=>"3.637 TB", // ��������
	 "Intf"=>"SATA", //���̽ӿ�����
	 "Model"=>"HUC109060CSS600", // �ͺ�
	 "Temperature"=>" 24C (75.20 F)", // �¶�
	 "S.M.A.R.T alert flagged by drive"=>"No", //SMART����
	 "Manufacturer"=>"ATA", // ����
	 "WWN"=>"5000c500932fe089", //ȫ��Ψһ��
	 "Firmware Revision"=>"TN02", //�̼��汾
	 "Link Speed"=>"6.0Gb/s",
	 "VDID"=>0, // ��������RAID����Ϣ��û����Ϊ-1
	 "VDName"=>"r123", // ��������RAID�����ƣ�û����Ϊ��""
	 "HSType"=>1, // �ȱ��������ͣ�0-���ȱ��̣�1-ȫ�֣�2-ָ����ĳ����ĳЩ������
	 "DHS Array"=>array(0,1,2), // ָ���������еĴ�����ID���ȱ���ȫ���ȱ����߷��ȱ�ʱΪ������
	 "IsInit"=>1, // �Ƿ����ڳ�ʼ��,0-û���ڳ�ʼ����1-���ڳ�ʼ��
	 "Init Progress"=>"25%", // ��ʼ������
	 "IsOK"=>1, // ��ȡ������Ϣ�ɹ����0-ʧ�ܣ�1-�ɹ�
	 "ErrMsg"=>"", // ������Ϣ
	 )
	 */
	private function GetPdInfo($enc_id, $slot_id)
	{
		$this->m_szLastErrorInfo = "";
		$pdInfo = array();
		$szDriveId = "/c" . $this->ID . "/e" . $enc_id . "/s" . $slot_id; // "/c0/e4/s8"
		$command = STORCLI . " " . $szDriveId . " show all J";
		$status = FALSE;
		$Arr_Json = $this->m_objF->GetStorcliRet($command, $this->m_szLastErrorInfo, $status);
		if($Arr_Json === FALSE)
		{
			return FALSE;
		}
		/* �������ʾ����
		   [root@mvp web]# storcli /c0/e4/s14 show all J
            {
            "Controllers":[
            {
            	"Command Status" : {
            		"Controller" : 0,
            		"Status" : "Success",
            		"Description" : "Show Drive Information Succeeded."
            	},
            	"Response Data" : {
            		"Drive /c0/e4/s14" : [
            			{
            				"EID:Slt" : "4:14",
            				"DID" : 5,
            				"State" : "GHS",
            				"DG" : "-",
            				"Size" : "5.456 TB",
            				"Intf" : "SATA",
            				"Med" : "HDD",
            				"SED" : "N",
            				"PI" : "N",
            				"SeSz" : "512B",
            				"Model" : "ST6000NM0115-1YZ110",
            				"Sp" : "U",
            				"Type" : "-"
            			}
            		],
            		"Drive /c0/e4/s14 - Detailed Information" : {
            			"Drive /c0/e4/s14 State" : {
            				"Shield Counter" : 0,
            				"Media Error Count" : 0,
            				"Other Error Count" : 0,
            				"BBM Error Count" : 0,
            				"Drive Temperature" : " 21C (69.80 F)",
            				"Predictive Failure Count" : 0,
            				"S.M.A.R.T alert flagged by drive" : "No"
            			},
            			"Drive /c0/e4/s14 Device attributes" : {
            				"SN" : "            ZAD2JFJ5",
            				"Manufacturer Id" : "ATA     ",
            				"Model Number" : "ST6000NM0115-1YZ110",
            				"NAND Vendor" : "NA",
            				"WWN" : "5000c500a5175c60",
            				"Firmware Revision" : "SN02    ",
            				"Raw size" : "5.458 TB [0x2baa0f4b0 Sectors]",
            				"Coerced size" : "5.456 TB [0x2ba7de800 Sectors]",
            				"Non Coerced size" : "5.457 TB [0x2ba90f4b0 Sectors]",
            				"Device Speed" : "6.0Gb/s",
            				"Link Speed" : "6.0Gb/s",
            				"NCQ setting" : "N/A",
            				"Write cache" : "N/A",
            				"Logical Sector Size" : "512B",
            				"Physical Sector Size" : "4 KB",
            				"Connector Name" : ""
            			},
            			"Drive /c0/e4/s14 Policies/Settings" : {
            				"Enclosure position" : 0,
            				"Connected Port Number" : "0(path0) ",
            				"Sequence Number" : 27,
            				"Commissioned Spare" : "No",
            				"Emergency Spare" : "No",
            				"Last Predictive Failure Event Sequence Number" : 0,
            				"Successful diagnostics completion on" : "N/A",
            				"SED Capable" : "No",
            				"SED Enabled" : "No",
            				"Secured" : "No",
            				"Cryptographic Erase Capable" : "No",
            				"Locked" : "No",
            				"Needs EKM Attention" : "No",
            				"PI Eligible" : "No",
            				"Certified" : "No",
            				"Wide Port Capable" : "No",
            				"Port Information" : [
            					{
            						"Port" : 0,
            						"Status" : "Active",
            						"Linkspeed" : "6.0Gb/s",
            						"SAS address" : "0x5001c450006b11b2"
            					}
            				]
            			},
            			"Inquiry Data" : "5a 0c ff 3f 37 c8 10 00 00 00 00 00 3f 00 00 00 00 00 00 00 20 20 20 20 20 20 20 20 20 20 20 20 41 5a 32 44 46 4a 35 4a 00 00 00 00 00 00 4e 53 32 30 20 20 20 20 54 53 30 36 30 30 4d 4e 31 30 35 31 31 2d 5a 59 31 31 20 30 20 20 20 20 20 20 20 20 20 20 20 20 20 20 20 20 20 20 20 20 10 80 00 40 00 2f 00 40 00 02 00 02 07 00 ff 3f 10 00 3f 00 10 fc fb 00 10 5c ff ff ff 0f 00 00 07 00 "
            		},
            		// ȫ���ȱ�
            		"Drive /c0/e4/s14 Hot Spare Information" : {
            			"Type" : "Global Hot Spare",
            			"Enclosure Affinity" : "No",
            			"Revertible" : "Yes"
            		}
            		// ָ���ȱ�
            		"Drive /c0/e4/s14 Hot Spare Information" : {
            			"Type" : "Dedicated Hot Spare",
            			"Enclosure Affinity" : "No",
            			"Revertible" : "Yes",
            			"Dedicated Array(s)" : "0, 1"
            		}
            	}
            }
            ]
            }
		*/
		
		// ��������
		$pdInfo["StrID"] = $szDriveId;
		$pdInfo["Ctl"] = $this->ID;
		$pdInfo["EID"] = $enc_id;
		$pdInfo["Slot"] = $slot_id;
		$pdInfo["EID:Slt"] = $enc_id . ":" . $slot_id;
		
		// show all ʧ�ܣ����ִ��show��ȡ������Ϣ
		if( ! isset($Arr_Json["Controllers"][0]["Response Data"]) )
		{
		    $command = STORCLI . " " . $szDriveId . " show J";
		    $status = FALSE;
		    $Arr_Json = $this->m_objF->GetStorcliRet($command, $this->m_szLastErrorInfo, $status);
		    if($Arr_Json === FALSE)
		    {
		        return FALSE;
		    }
		    /*
		    [root@mvp web]# storcli /c0/e4/s3 show J
		    {
		        "Controllers":[
		        {
		            "Command Status" : {
		            "Controller" : 0,
		            "Status" : "Failure",
		            "Description" : "Show Drive Information Failed.",
		            "Detailed Status" : [
		            {
		                "Drive" : "/c0/e4/s3",
		                "Status" : "Failure",
		                "ErrCd" : 45,
		                "ErrMsg" : "-"
		            }
		            ]
		        },
		        "Response Data" : {
		        "Drive Information" : [
		        {
		            "EID:Slt" : "4:3",
		            "DID" : 10,
		            "State" : "Failed",
		            "DG" : 0,
		            "Size" : "2.727 TB",
		            "Intf" : "SATA",
		            "Med" : "HDD",
		            "SED" : "N",
		            "PI" : "N",
		            "SeSz" : "512B",
		            "Model" : "-",
		            "Sp" : "U",
		            "Type" : "-"
		        }
		        ]
		        }
		        }
		        ]
		    }
		    */
		    if( ! isset($Arr_Json["Controllers"][0]["Response Data"]["Drive Information"][0]) )
		    {
		        return FALSE;
		    }
		    
		    $arrDriveInfo = $Arr_Json["Controllers"][0]["Response Data"]["Drive Information"][0];
		    
		    $pdInfo["State"] = trim($arrDriveInfo["State"]);
			$dg_id_str = trim($arrDriveInfo["DG"]);
    		if($dg_id_str == "-")
    		{
    		    $pdInfo["DG"] = -1;
    		}
    		else
    		{
    		  $pdInfo["DG"] = intval($dg_id_str);
    		}
		    $pdInfo["Size"] = trim($arrDriveInfo["DG"]);
		    $pdInfo["Intf"] = trim($arrDriveInfo["Intf"]);
		    $pdInfo["Model"] = trim($arrDriveInfo["Model"]);
		    
		    // ���������ÿ�
		    $pdInfo["Temperature"] = "";
		    $pdInfo["S.M.A.R.T alert flagged by drive"] = "";
		    $pdInfo["Manufacturer"] = "";
		    $pdInfo["WWN"] = "";
		    $pdInfo["SN"] = "";
		    $pdInfo["Firmware Revision"] = "";
		    $pdInfo["Link Speed"] = "";
		    $pdInfo["VDID"] = -1; 
		    $pdInfo["VDName"] = "";
		    $pdInfo["HSType"] = 0;
		    $pdInfo["DHS Array"] = array();
		    $pdInfo["IsInit"] = 0;
		    $pdInfo["Init Progress"] = ""; 
		    
		    $pdInfo["IsOK"] = 0;
		    $detailStatus = $Arr_Json["Controllers"][0]["Command Status"]["Detailed Status"][0];
		    $pdInfo["ErrMsg"] = $detailStatus["ErrMsg"] . ", Error Code: " . $detailStatus["ErrCd"];
		    
		    return $pdInfo;
		}
		
		$Arr_PdInfo = $Arr_Json["Controllers"][0]["Response Data"];
	
		$idx_drive = "Drive " . $szDriveId; // "Drive /c0/e252/s0"
		$idx_detail = "Drive " . $szDriveId . " - Detailed Information"; // "Drive /c0/e252/s0 - Detailed Information"
		$idx_d_state = "Drive " . $szDriveId . " State"; // "Drive /c0/e252/s0 State"
		$idx_d_attr = "Drive " . $szDriveId . " Device attributes"; // "Drive /c0/e252/s0 Device attributes"
		$idx_d_set = "Drive " . $szDriveId . " Policies/Settings"; // "Drive /c0/e252/s0 Policies/Settings"
		$idx_hs_info = "Drive " . $szDriveId . " Hot Spare Information"; // "Drive /c0/e4/s14 Hot Spare Information"
		$Arr_DriveDetail = $Arr_PdInfo[$idx_detail];
		// ��ȡ��������ֵ
		$pdInfo["State"] = trim($Arr_PdInfo[$idx_drive][0]["State"], " ");
		//DG id
		$dg_id_str = trim($Arr_PdInfo[$idx_drive][0]["DG"], " ");
		if($dg_id_str == "-")
		{
		    $pdInfo["DG"] = -1;
		}
		else
		{
		  $pdInfo["DG"] = intval($dg_id_str);
		}
		$pdInfo["Size"] =  trim($Arr_PdInfo[$idx_drive][0]["Size"], " ");
		$pdInfo["Intf"] =  trim($Arr_PdInfo[$idx_drive][0]["Intf"], " ");
		$pdInfo["Model"] =  trim($Arr_PdInfo[$idx_drive][0]["Model"], " ");
		// �ӷ��ص��¶��ַ�����ȡ�����϶�����
		$temperature_str = trim($Arr_DriveDetail[$idx_d_state]["Drive Temperature"]);// "21C (69.80 F)"
		if(preg_match("|^([0-9]*)C|", $temperature_str, $match))
		{
		    $pdInfo["Temperature"] = $match[1] . "��";
		}
		else 
		{
		    $pdInfo["Temperature"] = $temperature_str;
		}
		
		$pdInfo["S.M.A.R.T alert flagged by drive"] = trim($Arr_DriveDetail[$idx_d_state]["S.M.A.R.T alert flagged by drive"], " ");
		$pdInfo["Manufacturer"] = trim($Arr_DriveDetail[$idx_d_attr]["Manufacturer Id"], " ");
		$pdInfo["WWN"] = trim($Arr_DriveDetail[$idx_d_attr]["WWN"], " ");
		$pdInfo["SN"] = trim($Arr_DriveDetail[$idx_d_attr]["SN"], " ");
		$pdInfo["Firmware Revision"] = trim($Arr_DriveDetail[$idx_d_attr]["Firmware Revision"], " ");
		$pdInfo["Link Speed"] = trim($Arr_DriveDetail[$idx_d_attr]["Link Speed"], " ");
		$pdInfo["VDID"] = -1; // �ݲ���ȡ�Ȼ�ȡ��VD�����
		$pdInfo["VDName"] = ""; // �ݲ���ȡ�Ȼ�ȡ��VD�����
		// �ȱ���Ϣ
		$pdInfo["HSType"] = 0;
		$pdInfo["DHS Array"] = array();
		if( isset($Arr_PdInfo[$idx_hs_info]) ) // ����Ϊ�ȱ���
		{
		    $hs_type = trim($Arr_PdInfo[$idx_hs_info]["Type"]);
		    if($hs_type == "Dedicated Hot Spare") // ָ����Ӳ������ȱ�
		    {
		        $pdInfo["HSType"] = 2;
		        // "Dedicated Array(s)" : "0, 1"
		        $str_dgs = $Arr_PdInfo[$idx_hs_info]["Dedicated Array(s)"];
		        $arr_dgs = explode(",", $str_dgs);
		        
		        foreach($arr_dgs as $dg)
		        {
		            // ��ʹ��DG�������ţ�����ȡ���raid������ΪRAID��ID
		            $pdInfo["DHS Array"][] = intval(trim($dg));
		        }
		    }
		    else if($hs_type == "Global Hot Spare") // ȫ���ȱ�
		    {
		        $pdInfo["HSType"] = 1;
		    }
		}
		
		// ���̳�ʼ��״̬��Ϣ
		$pdInfo["IsInit"] = 0;
		$pdInfo["Init Progress"] = "";
		if($pdInfo["State"] == "UGood")
		{
    		$progress = $this->m_objF->GetPdInitProgress($this->ID, $enc_id, $slot_id, $this->m_szLastErrorInfo);
    		if($progress !== FALSE)
    		{
    		    $pdInfo["IsInit"] = 1;
    		    $pdInfo["Init Progress"] = $progress;
    		}
		}
		
		return $pdInfo;
	}
	
	/*
	 * ˵�������´�����Ϣ�е�VD��Ϣ
	 * ������$enc_id��enclosure ID
	 * 	    $slot_id��slot id
	 * ���أ���
	 */
	private function UpdateDhsInfoOfPd()
	{
	    foreach($this->PDs as &$pd)
	    {
	        if($pd["HSType"] == 0 || $pd["HSType"] == 1) // ���ȱ�����ȫ���ȱ�
	        {
	            continue;
	        }
	        $arrVDID = array();
	        foreach($pd["DHS Array"] as $dgid)
	        {
	            foreach($this->VDs as $vd)
	            {
	                if($vd["DG"] == $dgid)
	                {
	                    $arrVDID[] = $vd["VD"];
	                }
	            }
	        }
	        $pd["DHS Array"] = $arrVDID;
	    }
	}
	
	/*
	 * ˵������ȡ���ƿ��ϵ�raid��ID�б�
	 * ��������
	 * ���أ���������raid��ID�����飬���򷵻�FALSE
	 *       array(0,1,2)
	 */
	private function GetVdIdList()
	{
		$this->m_szLastErrorInfo = "";
		$listVdID = array();
		$command = STORCLI . " /c" . $this->ID . "/vall show J";// storcli /c0/vall show J
		$status = FALSE;
		$Arr_Json = $this->m_objF->GetStorcliRet($command, $this->m_szLastErrorInfo, $status);
		if($Arr_Json === FALSE)
		{
			return FALSE;
		}
		/* �������ʾ����
		{
			"Controllers":[
			{
				"Command Status" : {
				"Controller" : 0,
				"Status" : "Success",
				"Description" : "None"
				},
				"Response Data" : {
				"Virtual Drives" : [
				{
					"DG/VD" : "0/0",
					"TYPE" : "RAID5",
					"State" : "Optl",
					"Access" : "RW",
					"Consist" : "No",
					"Cache" : "RWTC",
					"Cac" : "-",
					"sCC" : "ON",
					"Size" : "7.275 TB",
					"Name" : "r5_test"
				},
				{
					"DG/VD" : "1/1",
					"TYPE" : "RAID0",
					"State" : "Optl",
					"Access" : "RW",
					"Consist" : "Yes",
					"Cache" : "NRWTD",
					"Cac" : "-",
					"sCC" : "ON",
					"Size" : "7.275 TB",
					"Name" : "r0_1314"
				}
				]
				}
			}
			]
		}
		*/
		// δ�ҵ��Ѵ�����VD
		if( ! isset($Arr_Json["Controllers"][0]["Response Data"]) )
		{
			return $listVdID;
		}
		$Arr_VdInfo = $Arr_Json["Controllers"][0]["Response Data"]["Virtual Drives"];
		foreach ($Arr_VdInfo as $vdinfo)
		{
			$arr_id = explode("/", $vdinfo["DG/VD"]); // "DG/VD" : "0/0"
			$listVdID[] = intval($arr_id[1]);
		}
		return $listVdID;
	}
	
	/*
	 * ˵������ȡ���������ƶ�Enclosure�Ĵ����б�
	 * ������ $enc_id: enclosure ID
	 *       $msg������������Ϣ����������������˱���Ϊ�գ�
	 * ���أ��������ش���slot ID���飬���򷵻�FALSE
	 */
	private function GetPdSlotIdListOfEnc($enc_id)
	{
		$listPdSlotId = array();
		$command = STORCLI . " /c" . $this->ID . "/e" . $enc_id . "/sall show J";
		$status = FALSE;
		$Arr_Json = $this->m_objF->GetStorcliRet($command, $this->m_szLastErrorInfo, $status);
		if($Arr_Json === FALSE)
		{
			return FALSE;
		}
		
		if( !isset($Arr_Json["Controllers"][0]["Response Data"]["Drive Information"]) )
		{
		    return FALSE;
		}
		
		$Arr_PDs = $Arr_Json["Controllers"][0]["Response Data"]["Drive Information"];
		foreach($Arr_PDs as $drive)
		{
			$str_array = explode(":", $drive["EID:Slt"]); //"EID:Slt"=>"4:8"
			$listPdSlotId[] = intval($str_array[1]);
		}
		return $listPdSlotId;
	}

	/*
	 * ˵������ȡ�������µ�enclosure�б�
	 * ��������
	 * ���أ���������enclosure�����б����򷵻�FALSE
	 */
	private function GetEncsInfo()
	{
		$listEnc = array();
		/*
		array
		(
			(
				"EID"=>1, // enclosure ID
				"PD"=>4   // drive count
			),
			(
				"EID"=>2,
				"PD"=>6
			)
		)
		*/
		$msg = "";
		$Command = STORCLI . " /c" . $this->ID ."/eall show J";
		$Arr_Json = array();
		/*
		 �������ʾ����JSON����
		[root@mvp ~]# storcli /c0/eall show J
		{
		"Controllers":[
		{
			"Command Status" : {
				"Controller" : 0,
				"Status" : "Success",
				"Description" : "None"
			},
			"Response Data" : {
				"Properties" : [
					{
						"EID" : 4,
						"State" : "OK",
						"Slots" : 16,
						"PD" : 5,
						"PS" : 1,
						"Fans" : 4,
						"TSs" : 3,
						"Alms" : 0,
						"SIM" : 0,
						"Port#" : "Port 0 - 3 x4",
						"ProdID" : "80H10331605A0",
						"VendorSpecific" : "x36-0.7.3.1"
					},
					{
						"EID" : 252,
						"State" : "OK",
						"Slots" : 8,
						"PD" : 0,
						"PS" : 0,
						"Fans" : 0,
						"TSs" : 0,
						"Alms" : 0,
						"SIM" : 1,
						"Port#" : "Internal",
						"ProdID" : "SGPIO",
						"VendorSpecific" : " "
					}
				]
			}
		}
		]
		}
		*/
		$status = FALSE;
		$Arr_Json = $this->m_objF->GetStorcliRet($Command, $this->m_szLastErrorInfo, $status);
		if($Arr_Json === FALSE)
		{
			return FALSE;
		}
		if( !isset($Arr_Json["Controllers"][0]["Response Data"]["Properties"]) )
		{
		    return FALSE;
		}
		$Arr_Enc = $Arr_Json["Controllers"][0]["Response Data"]["Properties"];
		for($i=0; $i<count($Arr_Enc); $i++)
		{
			$enc = array();
			$enc["EID"] = intval($Arr_Enc[$i]["EID"]);
			$enc["PD"] = intval($Arr_Enc[$i]["PD"]);
			
			$listEnc[] = $enc;
		}
		
		return $listEnc;
	}
	

	
}

?>


