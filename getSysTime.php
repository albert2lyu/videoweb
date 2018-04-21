<?php
require("./include/authenticated.php");
require_once("./include/function.php");
require_once("./include/LSIMegaRAID.php");

ob_start();
$output = get_sys_time();
$output_content = $output;
ob_end_clean();
print($output_content);
?>