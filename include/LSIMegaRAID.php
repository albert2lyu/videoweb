<?php
/*
 * 说明：1、 获取、设置LSI MegaRAID存储控制卡的功能参数，如获取存储卡型号、建立RAID、
 *          删除raid、获取磁盘信息等
 * 
 * 控制器支持   ：LSI MegaRAID 9260系列控制卡
 * 底层依赖： storcli命令（安装包安装后默认在/opt/MegaRAID/storcli/storcli64，
 *           需用手动建立storcli命令：ln -sfv /opt/MegaRAID/storcli/storcli64 /usr/bin/storcli）
 * 		
 * Created by 王大典，2017-11-26
 */

require_once("function.php");

$lang=load_lang();

$controller_prefix_str=array(
	"控制器",
	"Controller"
);
$io_cached_str=array(
        "缓冲I/O",
        "Cached I/O"
);
$io_direct_str=array(
        "直接I/O",
        "Direct I/O"
);
$read_ra_str=array(
        "预读",
        "Read-Ahead"
);
$read_nora_str=array(
        "非预读",
        "No Read-Ahead"
);
$write_wt_str=array(
        "通过写",
        "Write-Through"
);
$write_wb_str=array(
        "回写",
        "Write-Back"
);
$write_awb_str=array(
        "总是回写",
        "Always Write-Back"
);

define('STORCLI', "export LANG=C; /usr/bin/sudo /usr/bin/storcli");

// 错误信息
define("CLI",  "storcli: ");
define("C_ERR_RUN_FAIL", CLI . "command run failure");
define("C_ERR_NO_OUTPUT",  CLI . "no output");
define("C_ERR_PARSE_FAIL", CLI . "parse output failure");


/*
 * LSI RAID卡通用函数
 */
class CLSIMegaRAIDFunc
{
	/*
	 * 说明：获取storcli 命令执行后解析json后的数组
	 * 参数：$cmd : 需要执行的storcli及相关参数组成命令字符串
	 *       $msg：错误描述信息（成功时此变量为空）
	 *       $status： 命令执行的状态（根据storcli返回的Command Status），TRUE-命令执行的操作成功，FALSE-命令执行的操作失败
	 * 返回：正常返回解析json输出后的数组,否则返回FALSE（错误描述通过$msg返回）
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
	 * 说明：设置控制器时间
	 * 参数：$cid：控制器ID
	 *       $time：时间，格式"20171218 12:38:20"或者"systemtime"【按照系统时间设置】
	 * 		 $msg：用于返回错误描述信息（）
	 * 返回：正常返回TRUE，否则返回FALSE（错误描述通过$msg返回）
	 */
	function SetCtlTime($cid, $time="systemtime", &$msg="")
	{
	    $msg = "";
        $time = strtolower(trim($time));
	    $command = STORCLI . " " . "/c" . $cid . " set time=" . $time . " J";
	    /*命令返回示例：
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
	 * 说明：获取控制器时间
	 * 参数：$cid：控制器ID
	 * 		 $msg：用于返回错误描述信息（）
	 * 返回：正常返回时间"",否则返回FALSE（错误描述通过$msg返回）
	 */
	function GetCtlTime($cid, &$msg="")
	{
	    $msg = "";
	    $command = STORCLI . " " . "/c" . $cid . " show time J";
	    /* 命令返回示例：
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
	 * 说明：设置控制器上VD名称
	 * 参数：$cid：控制器ID
	 *       $vid：VD ID
	 *       $property：待设置的属性名称
	 *       $value：待设置的属性值
	 * 		 $msg：用于返回错误描述信息（成功为空""）
	 * 返回：正常返回TRUE，否则返回FALSE（错误描述通过$msg返回）
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
	 * 说明：创建RAID
	 * 参数：$cid： 控制器ID
	 *       $type: raid类型，如"raid0"  "raid5"...
	 *       $name: raid组名称
	 *       $drives: 包含的磁盘信息，如"4:8,4:9" (enclosureID:SlotID多组使用","分开)
	 *       $io: io策略，包括"direct"或者"cached"
	 *       $write: 写策略，包括"wt"/"wb"/"awb"
	 *       $read：读策略，包括"nora"/"ra"
	 *       $strip：strip大小，包括8, 16, 32, 64, 128, 256, 512, 1024（单位KB）
	 *       $pdsperarray：每个磁盘组的个数，仅适用于raid10/50/60
	 *       $msg：用于返回错误描述信息（成功为空""）
	 * 返回：成功返回TRUE，失败返回FALSE
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
	 * 说明：删除已创建RAID
	 * 参数：$cid： 控制器ID
	 *       $vid：RAID id
	 *       $msg：用于返回错误描述信息（成功为空""）
	 * 返回：成功返回TRUE，失败返回FALSE
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
	 * 说明：获取磁盘初始化进度
	 * 参数：$cid： 控制器ID
	 *       $eid：enclosure ID
	 * 		 $sid：slot id
	 *       $msg：用于返回错误描述信息（成功为空""）
	 * 返回：成功返回初始化进度（"50%"），失败返回FALSE
	 */
	function GetPdInitProgress($cid, $eid, $sid, &$msg="")
	{
	    $msg = "";
	    $szPDID = "/c" . $cid . "/e" . $eid . "/s" . $sid;
	     
	    $command = STORCLI . " " . $szPDID . " show initialization J";
	    /* 命令输出示例
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
                没有进行初始化的返回示例：
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
	 * 说明：开始磁盘初始化
	 * 参数：$cid： 控制器ID
	 *       $eid：enclosure ID
	 * 		 $sid：slot id
	 *       $msg：用于返回错误描述信息（成功为空""）
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	function StartPdInitialization($cid, $eid, $sid, &$msg="")
	{
	    $msg = "";
	    $szPDID = "/c" . $cid . "/e" . $eid . "/s" . $sid;
	     
	    $command = STORCLI . " " . $szPDID . " start initialization J";
	    /* 命令输出示例
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
	 * 说明：停止磁盘的初始化
	 * 参数：$cid： 控制器ID
	 *       $eid：enclosure ID
	 * 		 $sid：slot id
	 *       $msg：用于返回错误描述信息（成功为空""）
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	function StopPdInitialization($cid, $eid, $sid, &$msg="")
	{
	    $msg = "";
	    $szPDID = "/c" . $cid . "/e" . $eid . "/s" . $sid;
	
	    $command = STORCLI. " " . $szPDID . " stop initialization J";
	    /* 命令输出示例
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
	 * 说明：将unconfigure bad 的磁盘改为unconfigure good
	 * 参数：$cid： 控制器ID
	 *       $eid：enclosure ID
	 * 		 $sid：slot id
	 *       $init: TRUE-修改成功后开始初始化磁盘，FALSE=修改成功后不初始化磁盘
	 *       $msg：用于返回错误描述信息（成功为空""）
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	function SetPdUbadToUgood($cid, $eid, $sid, $init, &$msg="")
	{
	    $msg = "";
	    $szPDID = "/c" . $cid . "/e" . $eid . "/s" . $sid;
	    
	    $command = STORCLI. " " . $szPDID . " set good force J";
	    //print $command . "<br>";
	    /* 命令输出示例
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
	    
	    // 设置完后，初始化磁盘，否则磁盘仍旧无法使用
	    if($init === TRUE)
	    {
	        $this->StartPdInitialization($cid, $eid, $sid, $msg);
	    }
	    
	    return TRUE;
	}
	
	/*
	 * 说明：创建hotspare
	 * 参数：$cid： 控制器ID
	 *       $eid：enclosure ID
	 * 		 $sid：slot id
	 * 		 $vids：raid组ID数组（为指定的RAID组创建hotspare），为空数组时创建全局hotspare
	 *       $msg：用于返回错误描述信息（成功为空""）
	 * 返回：成功返回TRUE，失败返回FALSE
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
	 * 说明：删除hotspare
	 * 参数：$strSID:磁盘编号，格式"/c0/e4/s9"
	 *       $msg：用于返回错误描述信息（成功为空""）
	 * 返回：成功返回TRUE，失败返回FALSE
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
	 * 说明：删除hotspare
	 * 参数：$cid： 控制器ID
	 *       $eid：enclosure ID
	 * 		 $sid：slot id
	 *       $msg：用于返回错误描述信息（成功为空""）
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	function DeleteHotSpare($cid, $eid, $sid, &$msg="")
	{
	    $msg = "";
	    $szPDID = "/c" . $cid . "/e" . $eid . "/s" . $sid;
	    return $this->DeleteHotSpareEx($szPDID, $msg);
	}
	
	/*
	 * 说明：定位磁盘
	 * 参数：$szPDID：磁盘标识，如/c0/e4/s8
	 * 		 $time：定位磁盘多少秒
	 * 		 $msg：用于返回错误描述信息（）
	 * 返回：正常返回TRUE（$msg为空""）,否则返回FALSE（错误描述通过$msg返回）
	 */
	function LocatePD($szPDID, $time=20, &$msg="")
	{
		$msg = "";
		$command = STORCLI . " " . $szPDID . " start locate J";
		
		// 定位磁盘
		$status = FALSE;
		$Arr_Json = $this->GetStorcliRet($command, $msg, $status);
		if(  $Arr_Json === FALSE || $status === FALSE)
		{
			return FALSE;
		}
		
		if($time <= 0 || $time >= 600) $time=20;
		
		// 延时后取消定位，后台运行命令，调用后不等待直接返回
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
	 * 说明：获取最新的错误信息
	 * 参数：无
	 * 返回：错误信息，没有错误信息返回FALSE
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
	 * 说明：获取控制器列表
	 * 参数：无
	 * 返回：成功返回CLSIMegaRAID对象数组，失败返回NULL
	 */
	function GetCtlList()
	{
		return $this->m_objCtls;
	}

	/*
	 * 说明：获取制定ID的控制器CLSIMegaRAID对象
	 * 参数：$ctl_id：控制器ID
	 * 返回：成功返回CLSIMegaRAID对象，失败返回NULL
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
	
// --------------------------------------------私有
	
	/*
	 * 说明：获取控制器列表
	 * 参数：无
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	private function GetCtlInfoList()
	{
		$this->m_szLastErrorInfo = "";
		$Controller_Count = 0;
		$Command = STORCLI . " show J";
		$Arr_Json = array();
	
		/*
		 命令输出示例（JSON）：
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
		
		// 解析成功
		//获取控制器个数
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
				 "Ctl"=>"0", //控制器索引号
				 "Model"=>"LSIMegaRAIDSAS9261-8i",//控制器型号
				 "Ports"=>"8", //控制器磁盘接口数
				 "PDs"=>"4", //物理磁盘数
				 "DGs"=>"1", //磁盘组数
				 "VDs"=>"1", //虚拟磁盘数
				 "BBU"=>"msng", //bbu状态
				 "Hlth"=>"Opt", //控制器状态
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
	// -----------------------------------------------成员变量部分
	// 初始化是否成功
	public $OK;
	// 控制卡信息
	public $INFO;
	// 控制卡ID
	public $ID;
	// 磁盘信息列表
	public $PDs;
	// VD(raid组信息列表)
	public $VDs;
	// 热备盘信息HotSpare
	public $HSs;
	// 可用于做热备或者RAID的磁盘
	public $UPDs;
	
	//记录最新的错误信息
	private $m_szLastErrorInfo;
	private $m_objF;
	
	// -----------------------------------------------公有成员函数部分

	function __construct()
	{
		$this->m_szLastErrorInfo = "";
		$this->ID = -1;
		$this->INFO = array();
		$this->OK = FALSE;
		$this->m_objF = new CLSIMegaRAIDFunc();
		
		$argsList = func_get_args();
		$argsCnt = func_num_args();
		//判断Test类是否有__constructxx方法,将方法名记为$f
		if(method_exists($this,$f='__construct' . $argsCnt)){
			//若存在xx方法，使用call_user_func_array(arr1,arr2)函数调用他,该函数的参数为两个数组，前面的数组为调用谁($this)的什么($f)方法，后一个数组为参数
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
	 * 说明：获取控制卡所有信息
	 * 参数：无
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	function Init()
	{
	    $this->m_szLastErrorInfo = "";
	    $this->PDs = array();
	    $this->VDs = array();
	    $this->HSs = array();
	    $this->UPDs = array();
	
	    // 调用顺序不能更改
	    // 获取所有磁盘列表
	    if($this->GetPdList() !== TRUE)
	    {
	        return FALSE;
	    }
	    // 获取所有创建的RAID组信息
	    if($this->GetVdList() !== TRUE)
	    {
	        return FALSE;
	    }
	    // 获取所有创建的热备盘信息
	    if($this->GetHsList() !== TRUE)
	    {
	        return FALSE;
	    }
	    // 获取所有可用的磁盘信息
	    if($this->GetUPDsList() !== TRUE)
	    {
	        return FALSE;
	    }
	    
	    return TRUE;
	}
	
	/*
	 * 说明：获取最新的错误信息
	 * 参数：无
	 * 返回：错误信息，没有错误信息返回FALSE
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
	 * 说明：重新扫描控制器（重新扫描后需重新获取控制器信息）
	 * 参数：无
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	function RescanController()
	{
		$this->m_szLastErrorInfo = "";
		return $this->Init();
	}
	
	/*
	 * 说明：获取制定RAID组信息
	 * 参数：$vid: VD ID
	 * 返回：正常返回RAID组信息数组，否则返回FALSE
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
	 * 说明：获取指定磁盘信息
	 * 参数：
	 *     $eid: enclosure ID
	 *     $sid: slot ID
	 * 返回：正常返回磁盘信息数组，否则返回FALSE
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
	 * 说明：定位磁盘
	 * 参数：$eid: enclosure ID
	 * 		 $pid： slot id
	 *       $time：定位磁盘秒数
	 * 返回：正常返回磁盘slot ID数组，否则返回FALSE
	 */
	function LocatePD($eid, $pid, $time)
	{
		$szPDID = "/c" . $this->ID . "/e" . $eid . "/s" . $pid;
		return $this->m_objF->LocatePD($szPDID, $time, $this->m_szLastErrorInfo);
	}
	
	/*
	 * 说明：定位磁盘
	 * 参数：$szPdId:磁盘ID：/c0/e4/s8
	 *       $time：定位磁盘秒数
	 * 返回：正常返回磁盘slot ID数组，否则返回FALSE
	 */
	function LocatePD2($szPdId, $time)
	{
		return $this->m_objF->LocatePD($szPdId, $time, $this->m_szLastErrorInfo);
	}
	
	/*
	 * 说明：定位VD组中的磁盘
	 * 参数：$vdid：VD ID
	 *       $time：定位磁盘秒数
	 * 返回：正常返回磁盘slot ID数组，否则返回FALSE
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
		
		// 定位VD中的磁盘
		$status = FALSE;
		foreach($pdsLocated as $pd)
		{
		    $msg = "";
		    $command = STORCLI . " " . $pd["StrID"] . " start locate J";
		    
		    // 定位磁盘
		    $Arr_Json = $this->GetStorcliRet($command, $msg, $status);
		    if( $Arr_Json === FALSE || $status === FALSE )
		    {
		        continue;
		    }
		}
		set_time_limit(30);
		// 延时后批量停止定位
		sleep($time);
		
		foreach($pdsLocated as $pd)
		{
		    $msg = "";
		    $command = STORCLI . " " . $pd["StrID"] . " stop locate J";
		    
		    // 取消定位磁盘
		    $Arr_Json = $this->GetStorcliRet($command, $msg, $status);
		    if(  $Arr_Json === FALSE || $status === FALSE)
		    {
		        continue;
		    }
		}
		return TRUE;
	}


// ----------------------------------------------------私有成员函数部分

	/*
	 * 说明：获取磁盘信息
	 * 参数：无
	 * 返回：成功返回TRUE，失败返回FALSE
	 */
	private function GetPdList()
	{
		$this->m_szLastErrorInfo = "";
	
		// 获取控制器下连接的磁盘个数
		if($this->INFO["PDs"] <= 0)
		{
			return TRUE;
		}
		// 获取控制器下的enclosure列表
		$listEnc = $this->GetEncsInfo();
		if($listEnc === FALSE)
		{
			return FALSE;
		}
	
		// 根据enclosure依次获取磁盘信息
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
	 * 说明：获取控制卡上的raid组列表
	 * 参数：无
	 * 返回：正常返回TRUE，否则返回FALSE
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
		// 更新磁盘的热备信息
		$this->UpdateDhsInfoOfPd();
		return TRUE;
	}
	
	/*
	 * 说明：获取控制卡上的热备盘列表
	 * 参数：无
	 * 返回：正常返回TRUE，否则返回FALSE
	 */
	private function GetHsList()
	{
	    //"HSType"=>1, // 热备磁盘类型，0-非热备盘，1-全局，2-指定给某个或某些磁盘组
	    //"DHS Array"=>array(0,1,2) // 指定给数组中的磁盘组ID做热备，全局热备或者非热备时为空数组
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
	 * 说明：获取控制卡上的可用的磁盘
	 * 参数：无
	 * 返回：正常返回TRUE，否则返回FALSE
	 */
	private function GetUPDsList()
	{
        foreach($this->PDs as $pd)
	    {
	        if($pd["State"] == "UGood" && // 磁盘状态正常
	           $pd["VDID"] == -1 && // 没有做raid
	           $pd["HSType"] == 0 &&  // 没有做热备
	           $pd["IsInit"] == 0 // 没有正在初始化
	          )
	        {
	            $this->UPDs[] = $pd;
	        }

	    }
	    return TRUE;
	}
	
	/*
	 * 说明：获取控制卡上的制定ID的raid信息
	 * 参数：$vd_id：raid组的ID
	 * 返回：正常返回raid信息数组，否则返回FALSE
	 array(
	 "Ctl"=>0, // 控制器ID
	 "DG"=>0, // DG ID
	 "VD"=>0, // VD ID
	 "TYPE"=>"RAID5", // RAID类型
	 "Name"=>"vd0_test", //RAID组名称
	 "Size"=>12.10 TB, // 大小
	 "Access"=>"RW", // 访问权限
	 "Cache"=>"RWTC", // cache类型原始字符串（系统返回）
	 "io"=>array( // IO策略
	           "Type"=>"direct",
	           "Text"=>"直接IO"
	       ),
	  "wrcache"=>array( // 写入策略
	           "Type"=>"awb",
	           "Text"=>"总是回写"
	             ),
	 "rdcache"=>array( // 读取策略
	           "Type"=>"ra",
	           "Text"=>"预读"
	           ),
	 "State"=>"Optl", // 状态
	 "Strip"=>"512 KB", //strip大小
	 "Cdate"=>"2017-12-10", // 创建日期
	 "Ctime"=>"10:10:10 PM", // 创建时间
	 "IsOK"=>1, //获取VD信息是否正确，0-失败，1-正确
	 "ErrMsg"=>"", // 获取VD信息时，命令返回的错误信息，正常为空""
	 "PDs"=>array(
			 array(
				 "StrID"=>"/c0/e4/s8", // 磁盘标识
				 "Ctl"=>0, // 控制器ID
				 "EID"=>4, // enclosure ID
				 "EID:Slt"=>"4:8", // 磁盘EID与slotID
				 "State"=>"Onln", //磁盘状态
				 "Slot"=>8, // 插槽号
				 "DG"=>0, // 所属磁盘组号，“-”表示不属于任何磁盘组
				 "Size"=>"3.637 TB", // 磁盘容量
				 "Intf"=>"SATA", //磁盘接口类型
				 "Model"=>"HUC109060CSS600", // 型号
				 "Temperature"=>" 24C (75.20 F)", // 温度
				 "S.M.A.R.T alert flagged by drive"=>"No", //SMART警报
				 "Manufacturer"=>"ATA", // 厂商
				 "WWN"=>"5000c500932fe089", //全球唯一号
				 "Firmware Revision"=>"TN02", //固件版本
				 "Link Speed"=>"6.0Gb/s"
			 ),
			 ...
	 	  ) // 所含磁盘列表
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
		/* 命令输出示例：
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
		// 获取raid组基础信息
		$vdInfo["Ctl"] = $this->ID;
		$arr_dgvd = explode("/", $Arr_VdInfo[$idx_vd][0]["DG/VD"]);
		$vdInfo["DG"] = intval($arr_dgvd[0]);
		$vdInfo["VD"] = $vd_id;
		$vdInfo["TYPE"] = $Arr_VdInfo[$idx_vd][0]["TYPE"];
		$vdInfo["Name"] = $Arr_VdInfo[$idx_vd][0]["Name"];
		$vdInfo["Size"] = $Arr_VdInfo[$idx_vd][0]["Size"];
		$vdInfo["Access"] = $Arr_VdInfo[$idx_vd][0]["Access"];
		$vdInfo["Cache"] = $Arr_VdInfo[$idx_vd][0]["Cache"];
		//IO策略
		$vdInfo["io"] = $this->GetPolicyInfo("io", $vdInfo["Cache"]);
		// 读取策略
		$vdInfo["rdcache"] = $this->GetPolicyInfo("rdcache", $vdInfo["Cache"]);
		// 写入策略
		$vdInfo["wrcache"] = $this->GetPolicyInfo("wrcache", $vdInfo["Cache"]);
		$vdInfo["State"] = $Arr_VdInfo[$idx_vd][0]["State"];
		
		// 下属磁盘列表
		// raid组下属磁盘列表, 通过VD的DG和磁盘PD的DG相同判断（不从命令中获取，为了避免获取VD失败时无法获取磁盘列表）
		foreach($this->PDs as &$pd)
		{
		    if($pd["DG"] == $vdInfo["DG"])
		    {
		        // 更新磁盘的VD信息
		        $pd["VDID"] = $vdInfo["VD"];
				$pd["VDName"] = $vdInfo["Name"];
		        // 加入到VD组中的磁盘列表
		        $vdInfo["PDs"][] = &$pd;
		    }
		}
		
		
		// 获取状态成功后继续获取详细信息
		if($status === TRUE)
		{
		    $vdInfo["IsOK"] = 1;
		    $vdInfo["ErrMsg"] = "";
		    
    		$idx_properties = "VD" . $vd_id . " Properties"; //"VD0 Properties"
    		
    		$vdInfo["Strip"] = $Arr_VdInfo[$idx_properties]["Strip Size"];
    		$szCdate = $Arr_VdInfo[$idx_properties]["Creation Date"];
    		//从“日-月-年”转为“年-月-日”
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
	 * 说明：根据获取的VD Cache信息解析
	 * 参数：$policy：需要获取的策略类型，包括IO策略"io", 读取策略"rdcache", 写入策略"wrcache"
	 * 		 $value：需要解析的信息（VD中的Cache信息），如"RWTC"
	 * 返回：正常返回解析信息的数组，否则返回FALSE
	 *       array(
	 *         "Type"=>"cached", // 策略类型(创建RAID时用到的值)
	 *         "Text"=>"缓冲I/O", // 策略文字描述，根据语言类型返回文字
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
	        case "wrcache": // if顺序不能改
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
	 * 说明：获取控制卡上的制定Enclosure的制定磁盘信息
	 * 参数：$enc_id：enclosure的ID
	 * 		 $slot_id：磁盘插槽ID
	 * 返回：正常返回磁盘信息的数组，否则返回FALSE
	 array
	 (
	 "StrID"=>"/c0/e4/s8", // 磁盘标识
	 "Ctl"=>0, // 控制器ID
	 "EID"=>4, // enclosure ID
	 "EID:Slt"=>"4:8", // 磁盘EID与slotID
	 "State"=>"Onln", //磁盘状态
	 "Slot"=>8, // 插槽号
	 "DG"=>0, // 所属磁盘组号，“-”表示不属于任何磁盘组
	 "Size"=>"3.637 TB", // 磁盘容量
	 "Intf"=>"SATA", //磁盘接口类型
	 "Model"=>"HUC109060CSS600", // 型号
	 "Temperature"=>" 24C (75.20 F)", // 温度
	 "S.M.A.R.T alert flagged by drive"=>"No", //SMART警报
	 "Manufacturer"=>"ATA", // 厂商
	 "WWN"=>"5000c500932fe089", //全球唯一号
	 "Firmware Revision"=>"TN02", //固件版本
	 "Link Speed"=>"6.0Gb/s",
	 "VDID"=>0, // 磁盘所属RAID组信息，没有则为-1
	 "VDName"=>"r123", // 磁盘所属RAID组名称，没有则为空""
	 "HSType"=>1, // 热备磁盘类型，0-非热备盘，1-全局，2-指定给某个或某些磁盘组
	 "DHS Array"=>array(0,1,2), // 指定给数组中的磁盘组ID做热备，全局热备或者非热备时为空数组
	 "IsInit"=>1, // 是否正在初始化,0-没有在初始化，1-正在初始化
	 "Init Progress"=>"25%", // 初始化进度
	 "IsOK"=>1, // 获取磁盘信息成功与否，0-失败，1-成功
	 "ErrMsg"=>"", // 错误信息
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
		/* 命令输出示例：
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
            		// 全局热备
            		"Drive /c0/e4/s14 Hot Spare Information" : {
            			"Type" : "Global Hot Spare",
            			"Enclosure Affinity" : "No",
            			"Revertible" : "Yes"
            		}
            		// 指定热备
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
		
		// 基本属性
		$pdInfo["StrID"] = $szDriveId;
		$pdInfo["Ctl"] = $this->ID;
		$pdInfo["EID"] = $enc_id;
		$pdInfo["Slot"] = $slot_id;
		$pdInfo["EID:Slt"] = $enc_id . ":" . $slot_id;
		
		// show all 失败，则仅执行show获取基本信息
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
		    
		    // 其他属性置空
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
		// 获取其他属性值
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
		// 从返回的温度字符串提取出摄氏度数字
		$temperature_str = trim($Arr_DriveDetail[$idx_d_state]["Drive Temperature"]);// "21C (69.80 F)"
		if(preg_match("|^([0-9]*)C|", $temperature_str, $match))
		{
		    $pdInfo["Temperature"] = $match[1] . "℃";
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
		$pdInfo["VDID"] = -1; // 暂不获取等获取完VD后更新
		$pdInfo["VDName"] = ""; // 暂不获取等获取完VD后更新
		// 热备信息
		$pdInfo["HSType"] = 0;
		$pdInfo["DHS Array"] = array();
		if( isset($Arr_PdInfo[$idx_hs_info]) ) // 磁盘为热备盘
		{
		    $hs_type = trim($Arr_PdInfo[$idx_hs_info]["Type"]);
		    if($hs_type == "Dedicated Hot Spare") // 指定给硬盘组的热备
		    {
		        $pdInfo["HSType"] = 2;
		        // "Dedicated Array(s)" : "0, 1"
		        $str_dgs = $Arr_PdInfo[$idx_hs_info]["Dedicated Array(s)"];
		        $arr_dgs = explode(",", $str_dgs);
		        
		        foreach($arr_dgs as $dg)
		        {
		            // 先使用DG磁盘组编号，待获取完毕raid组后更新为RAID组ID
		            $pdInfo["DHS Array"][] = intval(trim($dg));
		        }
		    }
		    else if($hs_type == "Global Hot Spare") // 全局热备
		    {
		        $pdInfo["HSType"] = 1;
		    }
		}
		
		// 磁盘初始化状态信息
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
	 * 说明：更新磁盘信息中的VD信息
	 * 参数：$enc_id：enclosure ID
	 * 	    $slot_id：slot id
	 * 返回：无
	 */
	private function UpdateDhsInfoOfPd()
	{
	    foreach($this->PDs as &$pd)
	    {
	        if($pd["HSType"] == 0 || $pd["HSType"] == 1) // 非热备或者全局热备
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
	 * 说明：获取控制卡上的raid组ID列表
	 * 参数：无
	 * 返回：正常返回raid组ID的数组，否则返回FALSE
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
		/* 命令输出示例：
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
		// 未找到已创建的VD
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
	 * 说明：获取控制器下制定Enclosure的磁盘列表
	 * 参数： $enc_id: enclosure ID
	 *       $msg：错误描述信息（如果运行正常，此变量为空）
	 * 返回：正常返回磁盘slot ID数组，否则返回FALSE
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
	 * 说明：获取控制器下的enclosure列表
	 * 参数：无
	 * 返回：正常返回enclosure数组列表，否则返回FALSE
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
		 命令输出示例（JSON）：
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


