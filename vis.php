<?php
require_once("./view.php");
require_once("./include/function.php");

if(!is_show_vismgr())
{
	exit("no access!");
}

ShowHtmlView(VIS_SEL);
?>