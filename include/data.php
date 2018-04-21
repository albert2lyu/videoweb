<?php
require_once("function.php");

// 语言种类0-中文，1-英文
$VSTORWEB_LANG=load_lang();
$PRODUCT_NAME=get_product_name();
$RAID_TYPE=get_raid_type();
/* 系统标题 */
$vstor_title=array(
// 0 中文 （下同）
	"网络视频存储",
// 1 English （下同）
	"NVR"
);

$vstor_version=array(
	"版本 2.2",
	"version 2.2"
);

$exit_link=array(
	"退出",
	"Logout"
);
$log_link=array(
	"日志",
	"Log"
);
$status_link=array(
	"状态",
	"Status"
);
$shutdown_link=array( 
	"关机",
	"Shutdown"
);
$help_link=array(
	"帮助",
	"Help"
);

/*工具栏的按钮选择*/
/*
 * 和$mainview_data中的数组序列一致。
 */
define('STATUS_SEL',  0); // 状态
define('SYSTEM_SEL',  1); // 系统
define('VIS_SEL',     2); // VIS
define('RAID_SEL',    3); // 卷管理
define('VOLUME_SEL',  4); // RAID
define('ACCOUNT_SEL', 5); // 帐户


/*主框架界面数据*/

/*
 * 注：如修改中文数据，需相应的修改英文数据，以保持一致。
 */

// 存储RAID卡型号不同进入不同的导航链接(默认3ware-9750卡)
//1-3ware 9750-8i
//2-LSIMegaRAIDSAS9261-8i
$rd_drive = "raid.php?redirect=drive_target.php";// 磁盘信息链接
$rd_unit = "raid.php?redirect=unit_target.php";//raid组信息链接
$rd_maintenance = "raid.php?redirect=maintenance_target.php";//RAID组管理链接
$rd_controller = "raid.php?redirect=controller_target.php";//控制器管理链接
switch($RAID_TYPE)
{
	case 1:
		$rd_drive = "raid.php?redirect=drive_target.php";
		$rd_unit = "raid.php?redirect=unit_target.php";
		$rd_maintenance = "raid.php?redirect=maintenance_target.php";
		$rd_controller = "raid.php?redirect=controller_target.php";
		break;
	case 2:
		$rd_drive = "raid.php?redirect=drive_target2_index.php";
		$rd_unit = "raid.php?redirect=unit_target2_index.php";
		$rd_maintenance = "raid.php?redirect=maintenance_target2_index.php";
		$rd_controller = "raid.php?redirect=controller_target2_index.php";
		break;
	default:
		break;
}

$mainview_data=array(
	/////////////////////////////////
	// 0 中文
	/////////////////////////////////
	array(
		// 0
		array(
			"title"=>" 状态",
			"name"=>"状态",
			"link"=>"./status.php",
			"icon"=>array(
				"status_l.gif",
				"status_d.gif"
			),
			"font"=>array(
				"tools_link_l",
				"tools_link_d"
			),
			/*
			 * 注："subtitle"和"href"相对应，按照顺序、一对一的关系。增、删必须同步。下同
			 */			
			"subtitle"=>array(
				"系统信息",
				//"进程信息",
				//"设备自检"
			),
			"img"=>"status_img",
			"href"=>array(
				"status.php?redirect=sys_status_target.php",
				//"status.php?redirect=process_status_target.php",
				//"status.php?redirect=checkout_target.php"
			)			
		),
		// 1
		array(
			"title"=>" 系统管理",
			"name"=>"系统",
			"link"=>"./system.php",
			"icon"=>array(
				"system_l.gif",
				"system_d.gif"
			),
			"font"=>array(
				"tools_link_l",
				"tools_link_d"
			),
			"subtitle"=>array(
				"网络设置",
				"存储路径设置",
				"时钟设置",
				"关机/重启",
				"Ping测试",
				//"终端控制台",
				"语言",
				"日志",
				"帮助",
				"升级",
				"服务管理",
				"重置数据",
			),
			"img"=>"system_img",
			"href"=>array(
				"system.php?redirect=network_target.php",
				"system.php?redirect=storage_target.php",
				"system.php?redirect=clock_target.php",
				"system.php?redirect=shutdown_target.php",
				"system.php?redirect=ping_target.php",
				//"system.php?redirect=shell_target.php",
				"system.php?redirect=lang_target.php",
				"system.php?redirect=log_target.php",
				"system.php?redirect=help_target.php", 
				"system.php?redirect=mvpupdate_target.php",
				"system.php?redirect=mvpmanage_target.php",	
				"system.php?redirect=reset_target.php",
			)			
		),
		// 2
		array(
			"title"=>" MVP管理",
			"name"=>"MVP",
			"link"=>"./vis.php",
			"icon"=>array(
				"vis_l.gif",
				"vis_d.gif"
			),
			"font"=>array(
				"tools_link_l",
				"tools_link_d"
			),
			/*
			 * 注：如果在此subtitle下添加或删除项，影响了“配置存储服务器”、“配置VOD服务器”的原有序号，
			 * 	       需要到view.php的print_manager（213行）函数中做相应的修改。
			 */
			"subtitle"=>array(
				"设置VIS服务",       //0
				"VIS管理",      	    //1
				"查看VIS版本",       //2
				"配置存储服务器",    //3
				"配置VOD服务器",     //4
				"UsbKey授权管理",    //5
				"配置VIS热备",       //6
				"更新VIS系统"        //7
			),
			"img"=>"vis_img",
			"href"=>array(
				"vis.php?redirect=visconfig_target.php",
				"vis.php?redirect=vismanage_target.php",
				"vis.php?redirect=visversion_target.php",
				"vis.php?redirect=storage_target.php",
				"vis.php?redirect=vod_target.php",
				"vis.php?redirect=usbkey_target.php",
				"vis.php?redirect=ha_target.php",
				"vis.php?redirect=visupdate_target.php"
			)
		),
		// 3
		array(
			"title"=>" RAID管理",
			"name"=>"RAID管理",
			"link"=>"./raid.php",
			"icon"=>array(
				"raidmgr_l.gif",
				"raidmgr_d.gif"
			),
			"font"=>array(
				"tools_link_l",
				"tools_link_d"
			),
			"subtitle"=>array(
				"磁盘信息",
				"RAID组信息",
				"RAID组管理",
				"控制器设置"
			),
			"img"=>"raidmgr_img",
			"href"=>array(
				$rd_drive,
				$rd_unit,
				$rd_maintenance,
				$rd_controller
			)
		),
		// 4
		array(
			"title"=>" 卷管理",
			"name"=>"卷管理",
			"link"=>"./volume.php",
			"icon"=>array(
				"volume_l.gif",
				"volume_d.gif"
			),
			"font"=>array(
				"tools_link_l",
				"tools_link_d"
			),
			"subtitle"=>array(
				"物理卷",
				"卷组",
				"逻辑卷",
				"iscsi-target"
			),
			"img"=>"volume_img",
			"href"=>array(
				"volume.php?redirect=pv_target.php",
				"volume.php?redirect=vg_target.php",
				"volume.php?redirect=lv_target.php",
				"volume.php?redirect=it_target.php"
			)
		),
		// 5
		array(
			"title"=>" 帐号",
			"name"=>"帐号",
			"link"=>"./account.php",
			"icon"=>array(
				"account_l.gif",
				"account_d.gif"
			),
			"font"=>array(
				"tools_link_l",
				"tools_link_d"
			),
			"subtitle"=>array(
				"修改密码"
			),
			"img"=>"account_img",
			"href"=>array(
				"account.php?redirect=account_target.php"
			)
		)		
	),
	
	/////////////////////////////////
	// 1 English
	/////////////////////////////////
	array(
		// 0
		array(
			"title"=>" Status",
			"name"=>"Status",
			"link"=>"./status.php",
			"icon"=>array(
				"status_l.gif",
				"status_d.gif"
			),
			"font"=>array(
				"tools_link_l",
				"tools_link_d"
			),
			"subtitle"=>array(
				"System Status",
				//"Process Status",
			),
			"img"=>"status_img",
			"href"=>array(
				"status.php?redirect=sys_status_target.php",
				//"status.php?redirect=process_status_target.php",
			)			
		),
		// 1
		array(
			"title"=>"System Manager",
			"name"=>"System",
			"link"=>"./system.php",
			"icon"=>array(
				"system_l.gif",
				"system_d.gif"
			),
			"font"=>array(
				"tools_link_l",
				"tools_link_d"
			),
			"subtitle"=>array(
				"NetWork",
				"Storage",
				"Clock",
				"Shutdown/Reboot",
				"Ping Test",
				//"Secure Console",
				"Language",
				"Log",
				"Help",
				"Upgrade",
				"Service",
				"Reset",
			),
		"img"=>"system_img",
		"href"=>array(
				"system.php?redirect=network_target.php",
				"system.php?redirect=storage_target.php",
				"system.php?redirect=clock_target.php",
				"system.php?redirect=shutdown_target.php",
				"system.php?redirect=ping_target.php",
				//"system.php?redirect=shell_target.php",
				"system.php?redirect=lang_target.php",
				"system.php?redirect=log_target.php",
				"system.php?redirect=help_target.php",
				"system.php?redirect=mvpupdate_target.php",
				"system.php?redirect=mvpmanage_target.php",
				"system.php?redirect=reset_target.php",		)
		),
		// 2
		array(
			"title"=>"NVR Manager",
			"name"=>"NVR",
			"link"=>"./vis.php",
			"icon"=>array(
				"vis_l.gif",
				"vis_d.gif"
			),
			"font"=>array(
				"tools_link_l",
				"tools_link_d"
			),
			"subtitle"=>array(
				"VIS Server",
				"VIS Management",
				"VIS Version",
				"Storage Server",
				"VOD Server",
				"UsbKey Management",
				"VIS HA Setup",
				"Update VIS"
			),
			"img"=>"vis_img",
			"href"=>array(
				"vis.php?redirect=visconfig_target.php",
				"vis.php?redirect=vismanage_target.php",
				"vis.php?redirect=visversion_target.php",
				"vis.php?redirect=storage_target.php",
				"vis.php?redirect=vod_target.php",
				"vis.php?redirect=usbkey_target.php",
				"vis.php?redirect=ha_target.php",
				"vis.php?redirect=visupdate_target.php"
			)			
		),
		// 3
		array(
			"title"=>"RAID",
			"name"=>"RAID",
			"link"=>"./raid.php",
			"icon"=>array(
				"raidmgr_l.gif",
				"raidmgr_d.gif"
			),
			"font"=>array(
				"tools_link_l",
				"tools_link_d"
			),
			"subtitle"=>array(
				"Drive Info",
				"Unit Info",
				"Maintenance",
				"Setting"
			),
			"img"=>"raidmgr_img",
			"href"=>array(
				$rd_drive,
				$rd_unit,
				$rd_maintenance,
				$rd_controller
			)
		),
		// 4
		array(
			"title"=>"Volume Manager",
			"name"=>"Volume",
			"link"=>"./volume.php",
			"icon"=>array(
				"volume_l.gif",
				"volume_d.gif"
			),
			"font"=>array(
				"tools_link_l",
				"tools_link_d"
			),
			"subtitle"=>array(
				"Physical Volume",
				"Volume Group",
				"Logic Volume",
				"Iscsi-target"
			),
			"img"=>"volume_img",
			"href"=>array(
				"volume.php?redirect=pv_target.php",
				"volume.php?redirect=vg_target.php",
				"volume.php?redirect=lv_target.php",
				"volume.php?redirect=it_target.php"
			)
		),
		// 5
		array(
			"title"=>"Account",
			"name"=>"Account",
			"link"=>"./account.php",
			"icon"=>array(
				"account_l.gif",
				"account_d.gif"
			),
			"font"=>array(
				"tools_link_l",
				"tools_link_d"
			),
			"subtitle"=>array(
				"Change Password"
			),
			"img"=>"account_img",
			"href"=>array(
				"account.php?redirect=account_target.php"
			)
		)
	)
);


/*
 * web页面底部版权信息
 */
$copyright=array(
	"Copyright&copy;MingDing<br/>保留所有权利&ensp;上海明定信息科技有限公司",
	"Copyright&copy;MingDing<br/>All rights reserved.&ensp;MingDing"
);

?>