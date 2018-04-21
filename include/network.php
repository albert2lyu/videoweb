<?php

/*
 *说明：
 *	系统环境：RedHat Enterprise 5
 *	网络环境：bonding.ko模块静态编译进kernel中。若以模块方式加载，需要手动在/etc/rc.syslocal文件中添加
 *			"insmod /lib/modules/2.6.18-prep/kernel/drivers/net/bonding/bonding.ko"；
 *
 */

define('FILE_RESOLV', "/etc/resolv.conf");
define('FILE_NETWORK', "/etc/sysconfig/network");
define('FILE_HOSTS', "/etc/hosts");
define('CMD_ETHTOOL', "export LANG=C; /usr/bin/sudo /sbin/ethtool 2>&1 ");
define('CMD_IFCONFIG', "export LANG=C;/usr/bin/sudo /sbin/ifconfig 2>&1 ");
define('CMD_IFUP', "export LANG=C; /usr/bin/sudo /sbin/ifup 2>&1 ");
define('CMD_IFDOWN', "export LANG=C; /usr/bin/sudo /sbin/ifdown 2>&1 "); 
define('CMD_HOSTNAME', "export LANG=C; /usr/bin/sudo /bin/hostname 2>&1 ");
define('DIR_IFCONFIG', "/etc/sysconfig/network-scripts/"/*"/etc/sysconfig/network-devices/"*/);
define('DEVICE_PREFIX',"ifcfg-"/*"ifconfig\."*/);
define('NETMASK_FIELD',"NETMASK"/*PREFIX*/);
define('FILE_MODPROBE', "/etc/modprobe.conf");

Class Network {
	var $devices = array();
	var $bonds = array();
	var $vinterfaces = array();
	var $vlans = array();

	function Network() {
		//Network constructor
		$this->GetNICs();
		$this->GetVIFs();
		$this->GetBonds();
		$this->GetVLANs();
		return 0;
	}

	/* list_type：
	 * 		0-列出所有，用于显示所有设备
	 * 		1-滤掉slave，用于显示需要设置设备IP等信息的设备
	 * 		2-滤掉bond及slave，用于显示可以做绑定的设备
	 */
	function ListDevices($list_type=0) {
		$list = array_merge($this->ListBonds(), $this->ListNICs()/*, $this->ListVIFs(), $this->ListVLANs()*/);
		sort($list);
		// added by wangdd 2009-10-27
		$ret_list = array();
		$list_filtered = array();

		if($list_type == 0)
		{
			$ret_list = $list;
		}
		else if($list_type == 1)
		{
			foreach($list as $entry)
			{
				$eth = new NetworkCard($entry);
				if( !$eth->IsSlave() )
					$list_filtered[] = $entry;
			}
			$ret_list = $list_filtered;
		}
		else if($list_type == 2)
		{
			foreach($list as $entry)
			{
				$eth = new NetworkCard($entry);
				if( !$eth->IsSlave() && !$eth->IsMaster())
					$list_filtered[] = $entry;
			}
			$ret_list = $list_filtered;
		}
		
		return $ret_list;
		
	}

	function ListNICs() {
		return $this->devices;
	}

	function ListBonds() {
		return $this->bonds;
	}

	function ListVIFs() {
		return $this->vinterfaces;
	}

	function ListVLANs() {
		return $this->vlans;
	}

	function GetNICs() {
/*		exec("/bin/dmesg | /bin/grep \"eth\"",$output);*/
		exec("/bin/cat /proc/net/dev | /bin/grep \"eth\"", $output);
		foreach ($output as $line){
			if (preg_match("/eth[0-9][0-9]*/i", $line, $match))
				$this->devices[] = $match[0];
		}
		
		$this->devices = array_unique($this->devices);
		sort($this->devices);
	}

	function GetVIFs() {
		$devlist = array();
		$d = dir(DIR_IFCONFIG);
		$needle = DEVICE_PREFIX . "eth[0-9]+:";
		while ($entry = $d->read()){
			if (preg_match("/^" . $needle . "/i", $entry))
				$devlist[] = preg_replace("/" . DEVICE_PREFIX . "/","",$entry);
		}
		//limit Virtual Devices to real interfaces with following code
		if (empty($this->devices)) $this->GetNICs();
		foreach ($this->devices as $device){
			foreach($devlist as $dev){
				if (preg_match("/" . $device . ":[0-9]/", $dev, $match)){
					$this->vinterfaces[] = $match[0];
				}
			}
		}
	}

	function GetVLANs() {
		$devlist = array();
		$d = dir(DIR_IFCONFIG);
		$needle = DEVICE_PREFIX . "eth[0-9]+\.";
		while ($entry = $d->read()){
			if (preg_match("/^" . $needle . "/i", $entry))
				$devlist[] = preg_replace("/" . DEVICE_PREFIX . "/","",$entry);
		}
		//limit VLANS to real interfaces with following code
		if (empty($this->devices)) $this->GetNICs();
		foreach ($this->devices as $device){
			foreach($devlist as $dev){
				if (preg_match("/" . $device . "\.[0-9]/", $dev, $match)){
					$this->vlans[] = $match[0];
				}
			}
		}
	}

	function GetBonds(){
		$d = dir(DIR_IFCONFIG);
		$needle = DEVICE_PREFIX . "bond";
		while ($entry = $d->read()){
			if (preg_match("/^" . $needle . "/i", $entry))
				$this->bonds[] = preg_replace("/" . DEVICE_PREFIX . "/","",$entry);
		}
		
		sort($this->bonds);	
	}

	function GetDNS() {
		$file = new File(FILE_RESOLV);
		if(!$file->Load())
			return FALSE; //couldn't open file

		$entries = array("", "");
		$needle = "nameserver ";
		$index = 0;
		while (!$file->EOF()){
			$line = $file->GetLine();
			if (preg_match("/$needle/i", $line)){
				$entries[$index] = trim(preg_replace("/$needle/i", "", $line));
				if($entries[$index]==NULL)
					$entries[$index]="";
				$index++;
			}
		}
		return $entries;
	}

	function IsDHCP() {
		foreach ($this->ListNICs() as $device){
			$eth = new NetworkCard($device);
			if ($eth->IsDHCP())
				return TRUE;
		}
		return FALSE;
	}

	function SetDNS($dns1, $dns2) {
		$file = new File(FILE_RESOLV);

		$file->AddLine("search " . $this->GetHostname());
		if ($dns1)
			$file->AddLine("nameserver " . $dns1);
		if ($dns2)
			$file->AddLine("nameserver " . $dns2);
		$file->Save();
	}

	function GetHostname() {
		exec(CMD_HOSTNAME, $output, $retval);
		$output = implode(" ", $output);
		if ($retval)
			$error = $output; //capturuing error for future development
		return $output;
	}

	function GetDomain() {
		exec(CMD_HOSTNAME . " -d", $output, $retval);
		/*
		$output = implode(" ", $output);
		if ($retval)
			$error = $output; //capturuing error for future development
		return $output;
		*/
		if( $retval != 0 )
			return "";
		$hostname = $output[0];
		return $hostname;
	}


	function GetGatewayDevice() {
		$file = new File(FILE_NETWORK);
		if(!$file->Load())
			return FALSE; //couldn't open file

		$needle = "gatewaydev=";
		while (!$file->EOF()){
			if (preg_match("/" . $needle . "[^ ][^ ]*/i", $file->GetLine(), $match)){
				return trim(preg_replace("/" . $needle . "/i", "", $match[0]));
			}
		}
	}


	function SetGatewayDevice($device) {
		$file = new File(FILE_NETWORK);
		$file->Load();
		$file->EditLine("GATEWAYDEV=", "GATEWAYDEV=" . $device);
		$file->Save();
	}

	function GetGateway() {
		$file = new File(FILE_NETWORK);
		if(!$file->Load())
			return FALSE; //couldn't open file

		$needle = "gateway=";
		while (!$file->EOF()){
			if (preg_match("/" . $needle . "[^ ][^ ]*/i", $file->GetLine(), $match)){
				return trim(preg_replace("/" . $needle . "/i", "", $match[0]));
			}
		}
		return "";
	}

	function SetGateway($gateway) {
		// 取消gateway
		if($gateway == "")
		{
			$file = new File(FILE_NETWORK);
			$file->Load();
			$file->DeleteLine("^GATEWAY=");
			$file->Save();
			return TRUE;
		}
		
		// 设置gateway
		if( !IsIpOk($gateway) )
		{
			return FALSE;
		}
		$file = new File(FILE_NETWORK);
		$file->Load();
		$file->EditLine("GATEWAY=", "GATEWAY=" . $gateway);
		$file->Save();
	}

	function SetHostname($hostname) {
		$hostname_split = split("\.", $hostname);
		$file = new File(FILE_RESOLV);
		$file->Load();
		$file->EditLine("search ", "search " . $hostname);
		$file->Save();

		$file = new File(FILE_NETWORK);
		$file->Load();
		$file->EditLine("HOSTNAME=", "HOSTNAME=" . $hostname);
		$file->Save();

		$file = new File(FILE_HOSTS);
		//$file->AddLine("# Do not remove the following line, or various programs");
		//$file->AddLine("# that require network functionality will fail.");
		//$file->AddLine("127.0.0.1		" . $hostname . " " . $hostname_split[0] . " localhost.localdomain localhost");
		$line = "127.0.0.1  " . $hostname . " " . $hostname_split[0] . " localhost.localdomain localhost";
		$file->Load();
		$file->EditLine("^127\.0\.0\.1", $line);
		$file->Save();
		
		exec("export LANG=C; /usr/bin/sudo /bin/hostname " . $hostname);
	}
/*
	function UpdatePrompt(){
		exec("/etc/init.d/openfiler-appliance restart > /dev/null 2> /dev/null");
	}
*/
	function Restart() {
		exec("export LANG=C; /usr/bin/sudo /sbin/service network restart", $output, $retval);
		if ($retval) //error occured
			$error = implode(" ", $output); //future
		return $retval;
	}

	function IsValidIP($ip) { 
		if (preg_match("/^[0-9]{1,3}(.[0-9]{1,3}){3}$/",$ip)) {
			foreach(explode(".", $ip) as $octet) {
				if ($octet<1 || $octet>255)
					return FALSE;
			}
		}
		else
			return FALSE;
		return TRUE;
	}

	//this function is to generate all the possible netmasks
	function GenerateNetmasks() {
		$values = array();
		$intmask = 0;
		for ($i = 31; $i >= 0; $i--) {
			$values[] = (long2ip($intmask));
			$intmask += intval(pow(2, $i));
		}
		$values[] = long2ip(pow(2, 32) - 1);
		return $values;
	}
}

Class NetworkCard extends Network{

	var $device;
	var $ifconfig;
	var $ethtool;

	function NetworkCard($dev){
		$this->device=$dev;
		exec(CMD_IFCONFIG . $this->device, $this->ifconfig);
	}

	function GetDevice(){
		return $this->device;
	}

	function IsEnabled() {
		exec(CMD_IFCONFIG, $output);
		foreach($output as $line){
			if (preg_match("/^" . $this->device ."/", $line))
				return TRUE;
		}
		return FALSE;
	}

	function IsConnected() {
		$ph = popen(CMD_ETHTOOL . $this->device, "r");
		while (!feof($ph)){
			if (preg_match("/detected: [yesno]*/i", fgets($ph, 4096), $match)){
				if (preg_match("/yes/i", $match[0]))
					return TRUE;
			}
		}
		return FALSE;
	}

	function GetSpeed() {
		$ph = popen(CMD_ETHTOOL . $this->device, "r");
		$speed = "";
		$needle = "speed: ";
		while (!feof($ph)){
			if (preg_match("/" . $needle . "[^ ][^ ]*/i", fgets($ph, 4096), $match)){
				$speed = trim(preg_replace("/" . $needle . "/i", "", $match[0]));
				if ( preg_match("/unknown/i", $speed) )// 未连接
					$speed = "0 Mb/s";
				return $speed;
			}
		}
		return FALSE;
	}

	function IsSlave() {
		$file = new File(DIR_IFCONFIG . DEVICE_PREFIX . $this->device);
		if(!$file->Load())
			return FALSE;  //can't open file, exit

		while (!$file->EOF()){
			$line = split("=", $file->GetLine());
			if ((trim(strtoupper($line[0]))=="SLAVE") && (trim(strtoupper($line[1]))=="YES"))
				return TRUE;
		}
		return FALSE;
	}

	function IsVirtual() {
		$needle = "eth[0-9]+:";
		if (preg_match("/^" . $needle . "/i", $this->GetDevice()))
			return TRUE;
		return FALSE;
	}

	function IsVLAN() {
		$needle = "eth[0-9]+\.[0-9]";
		if (preg_match("/^" . $needle . "/i", $this->GetDevice()))
			return TRUE;
		return FALSE;
	}

	function IsMaster() {
		$needle = "bond[0-9]";
		if (preg_match("/^" . $needle . "/i", $this->GetDevice()))
			return TRUE;
		return FALSE;
	}

	function GetMaster() {
		$file = new File(DIR_IFCONFIG . DEVICE_PREFIX . $this->device);
		if(!$file->Load())
			return FALSE; //couldn't open file

		$needle = "master=";
		while (!$file->EOF()){
			if (preg_match("/" . $needle . "[^ ][^ ]*/i", $file->GetLine(), $match)){
				return trim(preg_replace("/" . $needle . "/i", "", $match[0]));
			}
		}
		return FALSE;
	}

	function GetIP() {
		foreach($this->ifconfig as $line){
			if (preg_match("/inet addr:[0-9\.]*/i", $line, $match)){
				$match = split(":", $match[0]);
				return $match[1];
			}
		}
		return FALSE;
	}
	// added by wangdd, 2009-10-26
	function SetIP($ipaddr)
	{
		if( !IsIpOk($ipaddr) )
		{
			return FALSE;
		}
		$file = new File(DIR_IFCONFIG . DEVICE_PREFIX . $this->device);
		if( ! $file->Load() )
		{
			return  FALSE;
		}
		$file->EditLine("^IPADDR=", "IPADDR=" . $ipaddr);
		$file->EditLine("^BOOTPROTO=", "BOOTPROTO=static");
		$file->EditLine("^ONBOOT=", "ONBOOT=yes");
		$file->Save();
		return TRUE;
	}

	function GetMask() {
		foreach($this->ifconfig as $line){
			if (preg_match("/mask:[0-9\.]*/i", $line, $match)){
				$match = split(":", $match[0]);
				return $match[1];
			}
		}
		return FALSE;
	}
	
	// added by wangdd, 2009-10-26
	function SetMask($netmask)
	{
		$file = new File(DIR_IFCONFIG . DEVICE_PREFIX . $this->device);
		if( ! $file->Load() )
		{
			return  FALSE;
		}
		$file->EditLine("^NETMASK=", "NETMASK=" . $netmask);
		$file->Save();
		return TRUE;
	}
	
	// added by wangdd，2010-07-07
	function RemoveIP()
	{
		$file = new File(DIR_IFCONFIG . DEVICE_PREFIX . $this->device);
		if( ! $file->Load() )
		{
			return  FALSE;
		}
		$file->DeleteLine("^IPADDR=");
		$file->DeleteLine("^NETMASK=");
		$file->DeleteLine("^BROADCAST=");
		$file->Save();
		return TRUE;
	}
	
	// added by wangdd, 2009-12-21 
	function GetBroadcast()
	{
		$file = new File(DIR_IFCONFIG . DEVICE_PREFIX . $this->device);
		if( ! $file->Load() )
			return  FALSE;
		$line = $file->FindLine("^BROADCAST=");
		if( $line === FALSE )
			return FALSE;
		
		if( preg_match("|^BROADCAST=(.*)|i", trim($line), $match) )
		{
			return $match[1];
		}
		
		return FALSE;
	}
	
	function SetBroadcast($ip, $broadcast=FALSE)
	{
		if( ! IsIpOk($ip) )
			return FALSE;
		
		$broadcast_ip = "";
		if( $broadcast !== FALSE && IsIpOk($broadcast) )
		{
			$broadcast_ip = $broadcast_ip;
		}
		else
		{
			$broadcast_ip = substr($ip, 0, (strrpos($ip, ".")+1)) . "255";
		}

		$file = new File(DIR_IFCONFIG . DEVICE_PREFIX . $this->device);
		if( ! $file->Load() )
			return  FALSE;
		$file->EditLine("^BROADCAST=", "BROADCAST=" . $broadcast_ip);
		$file->Save();
		return TRUE;
	}

	function GetMTU() {
		foreach($this->ifconfig as $line){
			if (preg_match("/mtu:[0-9]*/i", $line, $match)){
				$match = split(":", $match[0]);
				return $match[1];
			}
		}
		return FALSE;
	}

	function GetMAC() {
		foreach($this->ifconfig as $line){
			if (preg_match("/hwaddr [0-9A-F:]*/i", $line, $match)){
				$match = split(" ", $match[0]);
				return $match[1];
			}
		}
		return FALSE;
	}

	function GetTxBytes() {
		foreach($this->ifconfig as $line){
			if (preg_match("/TX bytes:[^(]*\ /i", $line, $match)){
				$match = split(":", trim($match[0]));
				return format_bytesize($match[1]);
			}
		}
		return FALSE;
	}

	function GetRxBytes() {
		foreach($this->ifconfig as $line){
			if (preg_match("/RX bytes:[^(]*\ /i", $line, $match)){
				$match = split(":", trim($match[0]));
				return format_bytesize($match[1]);
			}
		}
		return FALSE;
	}
	
	function GetTxErrors()
	{
		// TX packets:10891476 errors:0 dropped:0 overruns:0 frame:0
		foreach($this->ifconfig as $line){
			if (preg_match("/TX bytes:[^(]*\ (errors:[^(]*)\ /i", $line, $match)){
				$match = split(":", trim($match[1]));
				return $match[1];
			}
		}
		return FALSE;
	}
	
	function GetTxDropped()
	{
		// TX packets:10891476 errors:0 dropped:0 overruns:0 frame:0
		foreach($this->ifconfig as $line){
			if (preg_match("/TX bytes:[^(]*\ (dropped:[^(]*)\ /i", $line, $match)){
				$match = split(":", trim($match[1]));
				return $match[1];
			}
		}
		return FALSE;
	}
	
	function GetRxErrors()
	{
		// RX packets:10891476 errors:0 dropped:0 overruns:0 frame:0
		foreach($this->ifconfig as $line){
			if (preg_match("/RX bytes:[^(]*\ (errors:[^(]*)\ /i", $line, $match)){
				$match = split(":", trim($match[1]));
				return $match[1];
			}
		}
		return FALSE;
	}
	
	function GetRxDropped()
	{
		// RX packets:10891476 errors:0 dropped:0 overruns:0 frame:0
		foreach($this->ifconfig as $line){
			if (preg_match("/RX bytes:[^(]*\ (dropped:[^(]*)\ /i", $line, $match)){
				$match = split(":", trim($match[1]));
				return $match[1];
			}
		}
		return FALSE;
	}

	function GetGateway() {
		$file = new File(DIR_IFCONFIG . DEVICE_PREFIX . $this->device);
		if(!$file->Load())
			return FALSE; //couldn't open file

		$needle = "gateway=";
		while (!$file->EOF()){
			if (preg_match("/" . $needle . "[^ ][^ ]*/i", $file->GetLine(), $match)){
				return trim(preg_replace("/" . $needle . "/i", "", $match[0]));
			}
		}
		return FALSE;
	}

	function RemoveSlave(){
		exec("/sbin/ifenslave -d " . $this->GetMaster() . " " . $this->GetDevice(), $output, $retval);

		$file = new File(DIR_IFCONFIG . DEVICE_PREFIX . $this->GetDevice());
		$file->Load();
		$file->DeleteLine("master", FALSE);
		$file->DeleteLine("slave", FALSE);
		$file->Save();
	}

	function AddVIF(){
		$highest = 1;
		$this->getVIFs();
		foreach ($this->ListVIFs() as $vinterface){
			if (preg_match("/" . $this->device . ":([0-9])/", $vinterface, $match))
				if ($highest <= $match[1])
					$highest=$match[1] + 1;
		}
		//create a new empty configuration file
		$file = new File(DIR_IFCONFIG . DEVICE_PREFIX . $this->GetDevice() . ":" . $highest);
		$file->Save();
	}

	function AddVLAN($id){

		//create a new empty configuration file
		$file = new File(DIR_IFCONFIG . DEVICE_PREFIX . $this->GetDevice() . "." . $id);
		$file->Save();
	}

	function Save($dhcp, $ip, $netmask, $mtu) {
		//first remove from any bond
		if ($this->IsSlave())
			$this->RemoveSlave();

		$file = new File(DIR_IFCONFIG . DEVICE_PREFIX . $this->device);
		$file->AddLine("DEVICE=" . $this->device);
		if ($this->IsVLAN())
			$file->AddLine("VLAN=yes");
		if ($mtu)
			$file->AddLine("MTU=" . $mtu);
		$file->AddLine("USERCTL=no");
		$file->AddLine("ONBOOT=yes");
	
		$file->Save();
	}

	function Start() {
		exec(CMD_IFUP . $this->device, $output, $retval);
		if ($retval) //error occured
			$error = implode(" ", $output); //future
		$this->UpdatePrompt();
		return $retval;
	}

	function Stop() {
		exec(CMD_IFDOWN . $this->device, $output, $retval);
		if ($retval) //error occured
			$error = implode(" ", $output); //future
		return $retval;
	}

	function Remove() {
		$this->Stop();
		$file = new File(DIR_IFCONFIG . DEVICE_PREFIX . $this->device);
		$file->Delete();
	}

	function Restart() {
		$this->Stop();
		$this->Start();
	}
}

Class Bond extends NetworkCard{
	var $slaves;
	var $macaddress;

	function Bond($bond){
		$this->device = $bond;
		$this->FindSlaves();
	}

	function FindSlaves(){
		$this->GetNICs();
		foreach ($this->ListNICs() as $device){
			$slave = new NetworkCard($device);
			if ($slave->GetMaster() == $this->device){
				$this->slaves[] = $device;
			}
		}
		$this->GetVIFs();
		foreach ($this->ListVIFs() as $device){
			$slave = new NetworkCard($device);
			if ($slave->GetMaster() == $this->device){
				$this->slaves[] = $device;
			}
		}
	}

	function ListSlaves(){
		if(is_array($this->slaves))
		{
			sort($this->slaves);
			return $this->slaves;
		}
		return FALSE;
	}

	function GetSlaveStatus($device){
		$file = new File("/proc/net/bonding/" . $this->device);
		if(!$file->Load())
			return FALSE; //couldn't open file

		$needle = "Slave interface: " . $device;
		while (!$file->EOF()){
			if (preg_match("/" . $needle . "/i", $file->GetLine(), $match)){
				$temp = $file->GetLine();
				$needle = "mii status: ";
				while ($temp != ""){ //this prevents us from reading past the correct stanza
					if (preg_match("/" . $needle . "[^ ][^ ]*/i", $temp, $match)){
						return trim(preg_replace("/" . $needle . "/i", "", $match[0]));
					}
					$temp = $file->GetLine();
				}
			}
		}
		return FALSE;
	}

	function GetSlaveFailureCount($device){
		$file = new File("/proc/net/bonding/" . $this->device);
		if(!$file->Load())
			return FALSE; //couldn't open file

		$needle = "Slave interface: " . $device;
		while (!$file->EOF()){
			if (preg_match("/" . $needle . "/i", $file->GetLine(), $match)){
				$temp = $file->GetLine();
				$needle = "link failure count: ";
				while ($temp != ""){ //this prevents us from reading past the correct stanza
					if (preg_match("/" . $needle . "[^ ][^ ]*/i", $temp, $match)){
						return trim(preg_replace("/" . $needle . "/i", "", $match[0]));
					}
					$temp = $file->GetLine();
				}
			}
		}
		return FALSE;
		
	}
	
	/*
	 * 说明：获取绑定模式值，如0或1等
	 * 参数：无
	 * 返回：数值【0-6】，错误返回FALSE
	 * CREATED BY 王大典，2010-06-30 20:50
	 */
	function GetBondModeValue()
	{
		$file = new File("/etc/modprobe.conf");
		if( ! $file->Load() )
		{
			return FALSE; //couldn't open file
		}
		
		while (!$file->EOF())
		{
			$line_str = $file->GetLine();
			if ( preg_match("|options\s+{$this->device}\s+mode=([0-9])\s+|i", $line_str, $match))
			{
				return $match[1];
			}
		}
		
		return FALSE;
	}
	
	/*
	 * 说明：根据绑定模式值获取绑定模式描述，如“Balance Round-robin”
	 * 参数：无
	 * 返回：描述，错误返回FALSE
	 * CREATED BY 王大典，2010-06-30 20:50
	 */
	function GetBondModeStr()
	{
		$mode_value = $this->GetBondModeValue();
		if( $mode_value === FALSE )
		{
			return FALSE;
		}
		
		$mode_str_list = array( "Balance Round-robin", 
								"Active-Backup",
								"Balance-XOR",
								"Broadcast",
								"802.3ad",
								"Balance-tlb",
								"Balance-alb");
		
		if( $mode_value < 0 || $mode_value > count($mode_str_list) )
		{
			return FALSE;
		}
		
		return $mode_str_list[$mode_value];	
	}

	function AddSlave($slave){
		$file = new File(DIR_IFCONFIG . DEVICE_PREFIX . $slave);
		$file->AddLine("DEVICE=" . $slave);
		$file->AddLine("USERCTL=no");
		$file->AddLine("ONBOOT=yes");
		//$file->AddLine("BOOTPROTO=static");
		$file->AddLine("BOOTPROTO=none");
		$file->AddLine("SLAVE=yes");
		$file->AddLine("MASTER=" . $this->device);
		$file->Save();

		//$slave = new NetworkCard($slave);
		//$slave->Restart();
	}

	function RemoveSlave($slave){
		exec("/sbin/ifenslave -d " . $this->device . " " . $slave, $output, $retval);
		$file = new File(DIR_IFCONFIG . DEVICE_PREFIX . $slave);
		$file->Load();
		$file->DeleteLine("bootproto", FALSE);
		$file->DeleteLine("master", FALSE);
		$file->DeleteLine("slave", FALSE);
		$file->DeleteLine("userctl", FALSE);
		$file->AddLine("BOOTPROTO=static");
		$file->Save();

		$slave = new NetworkCard($slave);
		$slave->Stop();
	}

	/*
	 * mode:0-负载均衡，1-热备
	 * 
	 * 说明：仅更新配置文件，并不立刻使生效。
	 */
	function Create($ipaddr, $netmask, $mode=0 /*, $primary, $alternate, $miimon, $downdelay, $updelay*/){

		// 配置/etc/modeprobe.conf 文件
		$options = "options " . $this->device;
		$options .= " mode=" . $mode;
		$options .= " miimon=100"; /*. $miimon;*/
		$options .= " max_bonds=4";
/*		$options .= " downdelay=" . $downdelay;
		$options .= " updelay=" . $updelay;
		if ($primary != "FALSE")
			$options .= " primary=" . $primary;
		if ($alternate!="1")
			$options .= " use_carrier=0";
*/
		$file = new File(FILE_MODPROBE);
		$file->Load();
		if ($file->FindLine($this->device)){
			$file->DeleteLine($this->device, TRUE); //delete all occurances
			//may also want to run disable and remove functions
		}
		$file->AddLine("alias " . $this->device . " bonding");
		$file->AddLine($options);
		$file->Save();
		
		// 配置ifcfg-bond#文件
		$ifcfg_file = new File(DIR_IFCONFIG . DEVICE_PREFIX . $this->device);
		$ifcfg_file->AddLine("DEVICE=" . $this->device);
		$ifcfg_file->AddLine("ONBOOT=yes");
		$ifcfg_file->AddLine("BOOTPROTO=static");
		$ifcfg_file->AddLine("IPADDR=" . $ipaddr);
		$ifcfg_file->AddLine("NETMASK=" . $netmask);
		$ifcfg_file->AddLine("USERCTL=no");
		$ifcfg_file->AddLine("MTU=1500");
		$ifcfg_file->Save();
	}

	function Disable(){
		//unload module bonding which takes all bonds down
		$bondlist = array();
		$d = dir("/proc/net/bonding/");
		while ($entry = $d->read()){
			$bondlist[] = $entry;
		}

		exec("export LANG=C; /usr/bin/sudo /sbin/rmmod bonding ", $output, $retval);

		foreach ($bondlist as $bond){
			if ($bond != $this->device){
				//restart bond
				exec("/sbin/modprobe " . $bond, $output, $retval);
			}
		}
		
	}

	//delete the current bond from the system
	function Remove(){
		foreach ($this->ListSlaves() as $slave){
			$this->RemoveSlave($slave);
		}
		//删除此行，以确保取消绑定正常运行。2010-06-30
		//$this->Disable();

		$file = new File(FILE_MODPROBE);
		$file->Load();
		$file->DeleteLine($this->device, TRUE); //delete all occurances
		$file->Save();
		$file = new File(DIR_IFCONFIG . DEVICE_PREFIX . $this->device);
		$file->Delete(); //delete files
	}

}

?>
