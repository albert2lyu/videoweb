<?php
require("./include/authenticated.php");
require_once("./include/function.php");
require_once("./include/LSIMegaRAID.php");

ob_start();
$msg = "";
if(isset($_GET['cid']) && isset($_GET['vid']))
{
	$VDID = intval($_GET['vid']);
	$CtlID = intval($_GET['cid']);
	$objCtlList = new CLSIMegaRAIDList();
	$objCtl = $objCtlList->GetCtlObj($CtlID);
	if($objCtl != NULL)
	{
		$objCtl->LocateVD($VDID);
	}
	$msg = $objCtlList->GetLastErrorInfo();
}
else
{
	// ?
}

$output_content = $msg;
ob_end_clean();
print($output_content);
?>