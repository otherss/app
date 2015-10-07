<?php

define('IN_ECS', true);
define('ECS_ADMIN', true);

require(dirname(__FILE__) . '/includes/init.php');
error_reporting(E_ALL^E_NOTICE^E_WARNING);$region_id = $_GET['region_id'];$goods_id = $_GET['goods_id'];
$number = get_store($goods_id,$region_id);if(!$number){	$number = 0;}// $linked_goods = get_linked_goods($goods_id);$data = array(msg=>"",data=>"",error=>0);$data['data']['number'] = $number;include('includes/cls_json.php');



$json   = new JSON;
die($json->encode($data));function get_store($goods_id,$region_id){		if(!$goods_id || !$region_id)	{		return 0;	}	// 				".$GLOBALS['ecs']->table("region")." as p2 ,		$sql = "select number  from ".$GLOBALS['ecs']->table("storage_goods")." as p1 ,				".$GLOBALS['ecs']->table("storage")." as p2 			where p2.regions like '%".$region_id."%' and p1.goods_id = ".$goods_id." and p1.storage_id = p2.id  ";
		return $GLOBALS['db']->getOne($sql);}

?>