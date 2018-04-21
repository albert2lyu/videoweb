<?php
require("./include/authenticated.php");
require_once("./include/function.php");
require_once("./include/LSIMegaRAID.php");

ob_start();
$msg = "";
if(isset($_GET['cid']) && isset($_GET['eid']) && isset($_GET['sid']))
{
	$cid = intval($_GET['cid']);
	$eid = intval($_GET['eid']);
	$sid = intval($_GET['sid']);
	$szPDID = "/c" . $cid . "/e" . $eid . "/s" . $sid;
	$obj = new CLSIMegaRAIDFunc();
	$obj->LocatePD($szPDID, 30, $msg);
}
else
{
	// ?
}

$output_content = $msg;
ob_end_clean();
print($output_content);
?>