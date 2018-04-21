<?php
require("./include/ajax_authenticated.php");
require_once("./include/data.php");
require_once("./include/function.php");

$lang=load_lang();

/*ʱ��*/
$sys_time=array(
	"<b>ʱ��:</b>",
	"<b>Time:</b>"
);
/*����*/
$up_time=array(
	"<b>����:</b>",
	"<b>Up:</b>"
);
/*�û���*/
$user_count=array(
	"<b>�û�:</b>",
	"<b>User:</b>"
);
/*ƽ������*/
$load_avg=array(
	"<b>ϵͳƽ������:</b>",
	"<b>System Load Average:</b>"
);


$uptime_info = get_uptime_info();
/* 
array(
	"time"=>"10:22:15",
	"uptime"=>"3 ��",
	"user"=>5,
	"load_average"=>"0.00, 0.10, 0.10"
)
*/
if( $uptime_info !== FALSE )
{
	//print $system_time_value . " " . $up_time_value . " " . $user_count_value . "<br/>" . $load_average_value;
	print $sys_time[$lang] . $uptime_info['time'] . " "
	      . $up_time[$lang] . $uptime_info['uptime'] . " "
	      . $user_count[$lang] . $uptime_info['user'] . "<br/>"
		  . $load_avg[$lang] . $uptime_info['load_average'];
}
?>
