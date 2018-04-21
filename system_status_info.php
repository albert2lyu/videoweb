<?php
require_once("./include/ajax_authenticated.php");
require_once("./include/function.php");
require_once("./include/network.php");
require_once("./include/memory.php");
require_once("./include/cpu.php");
require_once("./include/usage.php");
require_once("./include/timezone.php");
require_once("./include/log.php");
require_once("./include/data.php");

$lang=load_lang();

//ϵͳ����
$sys_vital=array(
	"ϵͳ����",
	"System Vital"
);

//Ӳ����Ϣ
$hw_info=array(
	"Ӳ����Ϣ",
	"Hardware Information"
);

// ���ص��ļ�ϵͳ״̬
$mounted_fs=array(
	"���ص��ļ�ϵͳ��Ϣ",
	"Disk Mounted Information"
);

$hostname_str=array(
	"��������",
	"Host name"
);
$listen_ip_str=array(
	"����IP��ַ",
	"Listening IP"
);
$kernel_version_str=array(
	"�ں˰汾",
	"Kernel Version"
);
$distribute_name_str=array(
	"��������",
	"Distributed Name"
);
$distribute_version_str=array(
	"���а汾",
	"Distributed Version"
);
$system_date_str=array(
	"ϵͳʱ��",
	"System Date"
);
$system_uptime_str=array(
	"����ʱ��",
	"Uptime"
);
$user_count_str=array(
	"�û���",
	"User(s)"
);
$load_average_str=array(
	"ϵͳƽ������",
	"System Load Average"
);
$error_unknown=array(
	"δ֪",
	"Unknown"
);
// cpu��Ϣ
$cpu_information=array(
	"CPU��Ϣ",
	"CPU Information"
);
$cpu_id_str=array(
	"���",
	"ID"
);
$cpu_name_str=array(
	"����",
	"Name"
);
$cpu_core_count_str=array(
	"����",
	"Core(s)"
);
$cpu_speed_str=array(
	"Ƶ��",
	"Speed"
);
$cpu_cache_size_str=array(
	"Cache��С",
	"Cache size"
);
$cpu_bogomips_str=array(
	"BogoMIPS",
	"BogoMIPS"
);
$cpu_usage=array(
	"CPUʹ��",
	"Usage"
);

// �ڴ�ʹ��״̬
$mem_information=array(
	"�ڴ���Ϣ",
	"Memory Information"
);

$memory_type_str=array(
	"�ڴ�����",
	"Memory Type"
);
$memory_usage_str=array(
	"ʹ�ðٷֱ�",
	"Usage"
);
$memory_free_str=array(
	"δʹ��",
	"Free"
);
$memory_used_str=array(
	"��ʹ��",
	"Used"
);
$memory_total_str=array(
	"�ܴ�С",
	"Total"
);
$physical_memory_str=array(
	"�����ڴ�",
	"Physical"
);
$swap_memory_str=array(
	"�����ڴ�",
	"Swap"
);
// ����ʹ��״̬
$network_usage=array(
	"����ʹ��״̬",
	"Network Usage State"
);
$net_dev_name_str=array(
	"�豸",
	"Device"
);
$net_dev_bandwidth_str=array(
	"����",
	"Bandwidth"
);
$net_dev_receive_str=array(
	"��������",
	"Recevie Rate"
);
$net_dev_transmit_str=array(
	"��������",
	"Transmit Rate"
);
$receive_str=array(
	"����",
	"Receive"
);
$send_str=array(
	"����",
	"Transmit"
);
$connect_str=array(
	"����",
	"Connected"
);
$error_drop_str=array(
	"����/����",	
	"Error/Drop"
);

$yes_str=array(
	"��",
	"yes"
);
$no_str=array(
	"��",
	"no"
);

// ���̹�����Ϣ
$disk_str=array(
	"����",
	"Disk"
);
$mount_path_str=array(
	"����·��",	
	"Mounted on"
);
$file_system_str=array(
	"�ļ�ϵͳ",
	"Filesystem"
);
$disk_usage_str=array(
	"ʹ�ðٷֱ�",
	"Usage"
);
$disk_free_str=array(
	"δʹ��",	
	"Free"
);
$disk_used_str=array(
	"��ʹ��",
	"Used"
);
$disk_total_str=array(
	"�ܴ�С",
	"Total"
);
$disk_fs_str=array(
	"�ļ�ϵͳ",
	"Filesystem"
);
$disk_stat_str = array(
	"�洢����״̬",
	"Storage Disk State"
);
$disk_name_str =array(
	"����",
	"Disk"
);
$disk_read_str = array(
	"������",
	"Read Rate"
);
$disk_write_str = array(
	"д����",
	"Write Rate"
);
$no_disk_str=array(
	"û�к��ʵĴ���",
	"no proper disks"
);
?>

<?php 
define('CMD_UNAME', "export LANG=C; /bin/uname ");
define('CMD_DF', "export LANG=C; /bin/df ");

$usage = new Usage();
$usage_stat = $usage->GetUsageStat();

$network = new Network();
$hostname = $network->GetHostname();
$listen_ip = $_SERVER['SERVER_ADDR'];
// �ں˰汾
exec(CMD_UNAME . "-r", $output, $ret);
$output = implode(" ", $output);
if($ret == 0)
	$kernel_version = $output;
else
	$kernel_version = $error_unknown[$lang];

	
// �����ʼ
ob_start();

// ϵͳ��Ϣ
$timezone = new Timezone();
$bUtcEnabled = FALSE;
$valueTimezone = "";
$timezone->GetTimezone($valueTimezone, $bUtcEnabled);
date_default_timezone_set($valueTimezone);
$date_time = date("Y-m-d H:i:s");
$uptime_info = get_uptime_info();

print "
<table width=\"100%\" border=\"0\" cellpadding=\"6\">
  <tr>
    <td width=\"68%\" valign=\"top\">
	<table width=\"100%\" border=\"0\" cellpadding=\"0\" class=\"status\">
      <tr>
        <td class=\"title\" colspan=\"2\">$sys_vital[$lang]</td>
      </tr>
      <tr>
        <td class=\"bolder\" width=\"44%\">$hostname_str[$lang]</td>
        <td>$hostname</td>
      </tr>
      <tr>
        <td class=\"bolder\">$listen_ip_str[$lang]</td>
        <td>$listen_ip</td>
      </tr>
      <tr>
        <td class=\"bolder\">$kernel_version_str[$lang]</td>
        <td>$kernel_version</td>
      </tr>
      <tr>
        <td class=\"bolder\">$distribute_name_str[$lang]</td>
        <td>{$GLOBALS["PRODUCT_NAME"]}</td>
      </tr>
      <tr>
        <td class=\"bolder\">$distribute_version_str[$lang]</td>
        <td>{$GLOBALS["vstor_version"][$GLOBALS["VSTORWEB_LANG"]]}</td>
      </tr>
      <tr>
        <td class=\"bolder\">{$system_date_str[$lang]}</td>
        <td>{$date_time}</td>
      </tr>
      ";
if($uptime_info !== FALSE)
{
	print "
      <tr>
        <td class=\"bolder\">{$system_uptime_str[$lang]}</td>
        <td>{$uptime_info['uptime']}</td>
      </tr>
      <tr>
        <td class=\"bolder\">{$user_count_str[$lang]}</td>
        <td>{$uptime_info['user']}</td>
      </tr>
      <tr>
        <td class=\"bolder\">{$load_average_str[$lang]}</td>
        <td>{$uptime_info['load_average']}</td>
      </tr>
	";
}
print "
    </table>
";
print "</td>
	   <td></td>
	</tr>
";

print "
	<tr>
	<td valign=\"top\" colspan=\"2\">
";
// ��������
print "<table width=\"100%\" border=\"0\" cellpadding=\"0\" class=\"status\">\n";
print "<tr>\n";
print "  <td colspan=\"7\" class=\"title\">$disk_stat_str[$lang]</td>\n";
print "  </tr>\n";
print "<tr  class=\"bolder\">\n";
print "  <td>{$disk_name_str[$lang]}</td>\n";
print "  <td>{$disk_total_str[$lang]}</td>\n";
print "  <td>{$disk_fs_str[$lang]}</td>\n";
print "  <td>{$disk_free_str[$lang]}</td>\n";
print "  <td>{$disk_usage_str[$lang]}</td>\n";
print "  <td>{$disk_read_str[$lang]}</td>\n";
print "  <td>{$disk_write_str[$lang]}</td>\n";
print "</tr>";
if( isset($usage_stat['disk']) )
{
	foreach( $usage_stat['disk'] as $entry )
	{
		print "<tr>";
		// �豸����
		print "<td>\n";
		print $entry['name'];
		print "</td>\n";
		// ��С
		print "<td width=\"15%\">\n";
		print $entry['size'];
		print "</td>\n";
		// �ļ�ϵͳ
		print "<td width=\"13%\">\n";
		print $entry['fs'];
		print "</td>\n";
		// ʣ��
		print "<td width=\"15%\">\n";
		print $entry['free'];
		print "</td>\n";
		// ʹ����
		print "<td width=\"20%\">\n";
		print create_percent_bar($entry['usage']);
		print "</td>\n";
		// ���ٶ�
		print "<td width=\"13%\">\n";
		print $entry['read'];
		print "</td>\n";
		// д�ٶ�
		print "<td width=\"14%\">\n";
		print $entry['write'];
		print "</td>\n";
		
		print "</tr>";
	}
}

print "</table>";
print "
	</tr>
";

// ����ʹ����Ϣ
print "<tr>";
print "<td colspan=\"2\" valign=\"top\">\n";
$list_nic = $network->ListNICs();
print "<table width=\"100%\" border=\"0\" cellpadding=\"0\" class=\"status\">\n";
print "<tr>\n";
print "  <td colspan=\"8\" class=\"title\">$network_usage[$lang]</td>\n";
print "  </tr>\n";
print "<tr  class=\"bolder\">\n";
print "  <td>{$net_dev_name_str[$lang]}</td>\n";
print "  <td>{$net_dev_bandwidth_str[$lang]}</td>\n";
print "  <td>{$receive_str[$lang]}</td>\n";
print "  <td>{$send_str[$lang]}</td>\n";
print "  <td>{$connect_str[$lang]}</td>\n";
print "  <td>{$net_dev_receive_str[$lang]}</td>\n";
print "  <td>{$net_dev_transmit_str[$lang]}</td>\n";
print "  <td>{$error_drop_str[$lang]}</td>\n";
print "</tr>";
foreach($list_nic as $nic_entry)
{
	$dev_nic = new NetworkCard($nic_entry);
	print "<tr>\n";
	
	// �豸����
	print "<td>\n";
	print $dev_nic->GetDevice();
	print "</td>\n";
	// �ٶ�
	print "<td width=\"12%\">\n";
	print $dev_nic->GetSpeed();
	print "</td>\n";
	// ����
	print "<td width=\"12%\">\n";
	print $dev_nic->GetRxBytes();
	print "</td>\n";
	// ����
	print "<td width=\"12%\">\n";
	print $dev_nic->GetTxBytes();
	print "</td>\n";
	// �������
	print "<td width=\"10%\">\n";
	print $dev_nic->IsConnected() ? $yes_str[$lang] : $no_str[$lang];
	print "</td>\n";
	// ����
	print "<td width=\"16%\">\n";
	print $usage->GetNetDevReceive($dev_nic->GetDevice());
	print "</td>\n";
	print "<td width=\"16%\">\n";
	print $usage->GetNetDevTransmit($dev_nic->GetDevice());
	print "</td>\n";
	// ����/����
	print "<td>\n";
	print ($dev_nic->GetTxErrors() + $dev_nic->GetRxErrors()) . "/"
		  . ($dev_nic->GetTxDropped() + $dev_nic->GetRxDropped());
	print "</td>\n";
	
	print "</tr>\n";
}

print "</table>\n";
print "    </td>
  </tr>
";

// CPU��Ϣ
print "
  <tr>
	<td colspan=\"2\" valign=\"top\">";


print "
    <table width=\"100%\" border=\"0\" cellpadding=\"0\" class=\"status\">
      <tr>
        <td colspan=\"7\" class=\"title\">$cpu_information[$lang]</td>
        </tr>
      <tr  class=\"bolder\">
        <td>{$cpu_id_str[$lang]}</td>
        <td>{$cpu_name_str[$lang]}</td>
        <td>{$cpu_core_count_str[$lang]}</td>
        <td>{$cpu_speed_str[$lang]}</td>
        <td>{$cpu_cache_size_str[$lang]}</td>
        <td>{$cpu_bogomips_str[$lang]}</td>
        <td width=\"140px\">{$cpu_usage[$lang]}</td>
      </tr>
";
$cpu = new Cpu();
$cpu_list = $cpu->GetCpuList();
foreach($cpu_list as $entry)
{
	print "<tr>";
	print "<td>" . $entry['id'] . "</td>";
	print "<td>" . $entry['cores'][0]['name'] . "</td>";
	print "<td>" . count($entry['cores']) . "</td>";
	print "<td>" . $entry['cores'][0]['speed'] . "</td>";
	print "<td>" . $entry['cores'][0]['cache'] . "</td>";
	print "<td>" . $cpu->GetCpuBogomips($entry['id']) . "</td>";
	print "<td>" . create_percent_bar($usage->GetCpuUsage($entry['id'])) . "</td>";
	print "</tr>";
}

print "
    </table>
";
print "
	</td>
  </tr>
";

// �ڴ���Ϣ
print "
  <tr>
	<td colspan=\"2\" valign=\"top\">";

$memory = new Memory();
$phy_total = $memory->GetTotalPhysicalMemory(); // KB
$phy_free = $memory->GetFreePhysicalMemory();
$phy_used = $memory->GetUsedPhysicalMemory();
$phy_usage = $phy_total==0 ? "0%" : sprintf("%d%%", $phy_used / $phy_total * 100);

$phy_total_str = format_bytesize_to_unit($phy_total*1024);
$phy_free_str  = format_bytesize_to_unit($phy_free*1024);
$phy_used_str  = format_bytesize_to_unit($phy_used*1024);
$phy_suage_grad_str = create_percent_bar($phy_usage);

$swap_total = $memory->GetTotalSwap(); // KB
$swap_free = $memory->GetFreeSwap();
$swap_used = $memory->GetUsedSwap();
$swap_usage = $swap_total==0 ? "0%" : sprintf("%d%%", $swap_used / $swap_total * 100);

$swap_total_str = format_bytesize_to_unit($swap_total*1024);
$swap_free_str  = format_bytesize_to_unit($swap_free*1024);
$swap_used_str  = format_bytesize_to_unit($swap_used*1024);
$swap_usage_grad_str = create_percent_bar($swap_usage);
print "
    <table width=\"100%\" border=\"0\" cellpadding=\"0\" class=\"status\">
      <tr>
        <td colspan=\"5\" class=\"title\">$mem_information[$lang]</td>
        </tr>
      <tr  class=\"bolder\">
        <td>$memory_type_str[$lang]</td>
        <td width=\"140px\">$memory_usage_str[$lang]</td>
        <td>$memory_free_str[$lang]</td>
        <td>$memory_used_str[$lang]</td>
        <td>$memory_total_str[$lang]</td>
      </tr>
      <tr>
        <td>$physical_memory_str[$lang]</td>
        <td>" . $phy_suage_grad_str . "</td>
        <td>$phy_free_str</td>
        <td>$phy_used_str</td>
        <td>$phy_total_str</td>
      </tr>
      <tr>
        <td>$swap_memory_str[$lang]</td>
        <td>" . $swap_usage_grad_str . "</td>
        <td>$swap_free_str</td>
        <td>$swap_used_str</td>
        <td>$swap_total_str</td>
      </tr>
    </table>
";
print "
	</td>
  </tr>
";

////������Ϣ
//print "
//  <tr>
//    <td colspan=\"2\" valign=\"top\">
//";
//$output = array();
//exec(CMD_DF, $output);
///* $output:
//	Filesystem           1K-blocks      Used Available Use% Mounted on
//	/dev/hda1            128952384   6099460 116196796   5% /
//	tmpfs                   516952         0    516952   0% /dev/shm
//*/
//// �޳���һ��
//array_shift($output);
//$disk_count = count($output);
//
//$disk_array = array();
//$disk_total_array = array();
//$disk_used_array = array();
//$disk_free_array = array();
//$disk_mountpath_array = array();
//$disk_usage_array = array();
//$disk_filesystem_array = array();
//
//foreach( $output as $line )
//{
//	// ȥ����β�ո񣬲�ʹ��һ���ո��滻���еĿո��Է���ת��Ϊ���鴦��
//	$line = trim($line);
//	$line = preg_replace("/\s[\s]*/i", ";", $line);
//	$line_array = explode(";", $line);
//	
//	//��ֵ
//	$disk_array[] = $line_array[0];
//	$disk_total_array[] = format_bytesize($line_array[1] * 1024);
//	$disk_used_array[] = format_bytesize($line_array[2] * 1024);
//	$disk_free_array[] = format_bytesize($line_array[3] *1024);
//	$disk_usage_array[] = $line_array[4];
//	$disk_mountpath_array[] = $line_array[5];
//	
//	// ��ȡ�ļ�ϵͳ����
//	$command = "export LANG=C; /bin/mount | grep \"". $line_array[0] . "\"";
//	$disk_line = array();
//	exec($command, $disk_line, $retval);
//	if( $retval != 0)
//	{
//		$disk_filesystem_array[] = $error_unknown[$lang];
//	}
//	else
//	{
//		preg_match("/type\s[^\s]*/i", $disk_line[0], $match);
//		$match = explode(" ", trim($match[0]));
//		$disk_filesystem_array[] = $match[1];
//	}	
//}
//
//print "<table width=\"100%\" border=\"0\" cellpadding=\"0\" class=\"status\">
//      <tr>
//        <td colspan=\"7\" class=\"title\">{$mounted_fs[$lang]}</td>
//        </tr>
//      <tr class=\"bolder\">
//      	<td>{$disk_str[$lang]}</td>
//        <td>{$mount_path_str[$lang]}</td>
//        <td>{$file_system_str[$lang]}</td>        
//        <td>{$disk_usage_str[$lang]}</td>
//        <td>{$disk_free_str[$lang]}</td>
//        <td>{$disk_used_str[$lang]}</td>
//        <td>{$disk_total_str[$lang]}</td>
//      </tr>
//";
//for($i=0; $i<$disk_count; $i++)
//{
//	print "<tr>";
//	print "<td>" . $disk_array[$i] . "</td>";
//	print "<td>" . $disk_mountpath_array[$i] . "</td>";
//	print "<td>" . $disk_filesystem_array[$i] . "</td>";
//	print "<td>" . create_percent_bar($disk_usage_array[$i]) . "</td>";
//	print "<td>" . $disk_free_array[$i] . "</td>";
//	print "<td>" . $disk_used_array[$i] . "</td>";
//	print "<td>" . $disk_total_array[$i] . "</td>";
//	print "</tr>";
//}
//print "
//    </table>
//";
//print "
//    </td>
//  </tr>";


// ������������ύ���
$output_content = ob_get_contents();
ob_end_clean();
print($output_content);

?>
