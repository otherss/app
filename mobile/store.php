<?php

define('IN_ECS', true);
define('ECS_ADMIN', true);

require(dirname(__FILE__) . '/includes/init.php');
error_reporting(E_ALL^E_NOTICE^E_WARNING);
$number = get_store($goods_id,$region_id);



$json   = new JSON;
die($json->encode($data));
	

?>