<?php 
require_once("./view.php");
require_once("./include/function.php");

if(!is_show_raidmgr())
{
	exit("no access!");
}

ShowHtmlView(RAID_SEL);
?>