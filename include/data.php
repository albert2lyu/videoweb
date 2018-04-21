<?php
require_once("function.php");

// ��������0-���ģ�1-Ӣ��
$VSTORWEB_LANG=load_lang();
$PRODUCT_NAME=get_product_name();
$RAID_TYPE=get_raid_type();
/* ϵͳ���� */
$vstor_title=array(
// 0 ���� ����ͬ��
	"������Ƶ�洢",
// 1 English ����ͬ��
	"NVR"
);

$vstor_version=array(
	"�汾 2.2",
	"version 2.2"
);

$exit_link=array(
	"�˳�",
	"Logout"
);
$log_link=array(
	"��־",
	"Log"
);
$status_link=array(
	"״̬",
	"Status"
);
$shutdown_link=array( 
	"�ػ�",
	"Shutdown"
);
$help_link=array(
	"����",
	"Help"
);

/*�������İ�ťѡ��*/
/*
 * ��$mainview_data�е���������һ�¡�
 */
define('STATUS_SEL',  0); // ״̬
define('SYSTEM_SEL',  1); // ϵͳ
define('VIS_SEL',     2); // VIS
define('RAID_SEL',    3); // �����
define('VOLUME_SEL',  4); // RAID
define('ACCOUNT_SEL', 5); // �ʻ�


/*����ܽ�������*/

/*
 * ע�����޸��������ݣ�����Ӧ���޸�Ӣ�����ݣ��Ա���һ�¡�
 */

// �洢RAID���ͺŲ�ͬ���벻ͬ�ĵ�������(Ĭ��3ware-9750��)
//1-3ware 9750-8i
//2-LSIMegaRAIDSAS9261-8i
$rd_drive = "raid.php?redirect=drive_target.php";// ������Ϣ����
$rd_unit = "raid.php?redirect=unit_target.php";//raid����Ϣ����
$rd_maintenance = "raid.php?redirect=maintenance_target.php";//RAID���������
$rd_controller = "raid.php?redirect=controller_target.php";//��������������
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
	// 0 ����
	/////////////////////////////////
	array(
		// 0
		array(
			"title"=>" ״̬",
			"name"=>"״̬",
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
			 * ע��"subtitle"��"href"���Ӧ������˳��һ��һ�Ĺ�ϵ������ɾ����ͬ������ͬ
			 */			
			"subtitle"=>array(
				"ϵͳ��Ϣ",
				//"������Ϣ",
				//"�豸�Լ�"
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
			"title"=>" ϵͳ����",
			"name"=>"ϵͳ",
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
				"��������",
				"�洢·������",
				"ʱ������",
				"�ػ�/����",
				"Ping����",
				//"�ն˿���̨",
				"����",
				"��־",
				"����",
				"����",
				"�������",
				"��������",
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
			"title"=>" MVP����",
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
			 * ע������ڴ�subtitle����ӻ�ɾ���Ӱ���ˡ����ô洢����������������VOD����������ԭ����ţ�
			 * 	       ��Ҫ��view.php��print_manager��213�У�����������Ӧ���޸ġ�
			 */
			"subtitle"=>array(
				"����VIS����",       //0
				"VIS����",      	    //1
				"�鿴VIS�汾",       //2
				"���ô洢������",    //3
				"����VOD������",     //4
				"UsbKey��Ȩ����",    //5
				"����VIS�ȱ�",       //6
				"����VISϵͳ"        //7
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
			"title"=>" RAID����",
			"name"=>"RAID����",
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
				"������Ϣ",
				"RAID����Ϣ",
				"RAID�����",
				"����������"
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
			"title"=>" �����",
			"name"=>"�����",
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
				"�����",
				"����",
				"�߼���",
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
			"title"=>" �ʺ�",
			"name"=>"�ʺ�",
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
				"�޸�����"
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
 * webҳ��ײ���Ȩ��Ϣ
 */
$copyright=array(
	"Copyright&copy;MingDing<br/>��������Ȩ��&ensp;�Ϻ�������Ϣ�Ƽ����޹�˾",
	"Copyright&copy;MingDing<br/>All rights reserved.&ensp;MingDing"
);

?>