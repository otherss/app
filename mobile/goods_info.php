<?php

/**
 * ECSHOP 商品页
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: testyang $
 * $Id: goods.php 15013 2008-10-23 09:31:42Z testyang $
*/

define('IN_ECS', true);
define('ECS_ADMIN', true);

require(dirname(__FILE__) . '/includes/init.php');
error_reporting(E_ALL^E_NOTICE^E_WARNING);if(empty($_GET['id'])){	$goods_id= $db->getOne("select goods_id from ".$ecs->table('goods')." where is_on_sale = 1 and is_delete = 0 order by sort_order");}else{	$goods_id = $_GET['id'];}
$user_id = $_GET['user_id']>0?$_GET['user_id']:0;


$goods_info = get_goods_info($goods_id);$sql = " select count(*) from   ".$GLOBALS['ecs']->table('collect_goods') . ' AS g ' .                       "WHERE g.goods_id = '$goods_id' AND g.user_id = '$user_id' " ;$goods_info['collect'] = $GLOBALS['db']->GetOne($sql); // $linked_goods = get_linked_goods($goods_id);$goods_info['month_nums'] = encode_output(ec_buysum($goods_id));
$goods_info['goods_name'] = encode_output($goods_info['goods_name']);$goods_info['goods_desc'] = encode_output($goods_info['goods_desc']);// $goods_info['goods_desc'] = encode_output($goods_info['goods_desc']);$goods_info['goods_desc'] = preg_replace("/<a[^>]*>(.*)<\/a>/isU",'${1}',$goods_info['goods_desc']);$goods_info['goods_desc'] = htmlspecialchars_decode(str_replace('/images','http://'.$_SERVER['HTTP_HOST']."/images",$goods_info['goods_desc']));// echo $goods_info['goods_desc'] ;// exit;
$goods_info['promote_price'] = encode_output($goods_info['promote_price']);
$goods_info['market_price'] = encode_output($goods_info['market_price']);
$goods_info['shop_price'] = encode_output($goods_info['shop_price']);
$goods_info['shop_price_formated'] = encode_output($goods_info['shop_price_formated']);
$goods_info['goods_number'] = encode_output($goods_info['goods_number']);$comment = get_goods_comment($goods_id);$goods_info['comment'] = $comment;$goods_info['comment_nums'] =count($comment); $properties = get_goods_properties($goods_id);  // 获得商品的规格和属性$goods_info['properties'] = $properties;

$goods_info['gallery'] = get_goods_gallery($goods_id);//         $smarty->assign('properties',          $properties['pro']);      // $comment = get_goods_comment($goods_id);// $goods_info['linked_goods'] = $linked_goods;
// $smarty->assign('goods_info', $goods_info);include('includes/cls_json.php');



$json   = new JSON;
// die($json->encode($goods_info));die($_REQUEST['callback']."(".$json->encode($goods_info).")");$collectnums = $db->getOne("select count(*) from".$ecs->table('collect_goods')." where goods_id = ".$goods_id);
// $smarty->assign('related_goods',		 get_linked_goods($goods_id));
//         $properties = get_goods_properties($goods_id);  // 获得商品的规格和属性
//         $smarty->assign('properties',          $properties['pro']);                              // 商品属性

$linked_goods = get_linked_goods($goods_id);



$properties = get_goods_properties($goods_id);  // 获得商品的规格和属性


$goods_gallery = get_goods_gallery($goods_id);
$smarty->assign('now_time',  gmtime()); // 当前系统时间
function is_collect($user_id,$goods_id){	$sql = "select count(*) from ".$GLOBALS['ecs']->table("collect_goods")."  where user_id = ".$user_id." and goods_id = ".$goods_id;
	return $GLOBALS['db']->getOne($sql);}
function get_goods_comment($goods_id,$num=100,$start=0)

{

	/* 取得评论列表 */



	$sql = 'SELECT p1.* , p2.user_name  FROM ' . $GLOBALS['ecs']->table('comment') .' as p1 '.
			'left join '. $GLOBALS['ecs']->table('users') .' as p2 on p1.user_id = p2.user_id '.
// 	'left join '. $GLOBALS['ecs']->table('order_goods') .' as p3 on p1.goods_id = p3.goods_id  '.
// 	'left join '. $GLOBALS['ecs']->table('goods') .' as p4 on p1.goods_id = p4.goods_id  '.
	" WHERE p1.goods_id = '$goods_id'  AND p1.parent_id = 0  and p1.status =1 ".

	' ORDER BY p1.add_time DESC';

	$res = $GLOBALS['db']->SelectLimit($sql, $num, $start);

	// 	$res = $GLOBALS['db']->query($sql);



	$arr = array();

	$ids = '';

	while ($row = $GLOBALS['db']->fetchRow($res))

	{

		$ids .= $ids ? ",$row[comment_id]" : $row['comment_id'];

		$temp = array();

		$temp['comment_id']       = $row['comment_id'];
				$temp['user_name'] = $row['user_name'];		
// 		$temp['goods_name'] = $row['goods_name'];

// 		$temp['goods_price'] = $row['goods_price'];

// 		$temp['goods_number'] = $row['goods_number'];

// 		$temp['original_img'] = $row['original_img'];

		$temp['content']  = str_replace('\r\n', '<br />', htmlspecialchars($row['content']));

		$temp['content']  = nl2br(str_replace('\n', '<br />', $temp['content']));

		$temp['comment_rank']     = $row['comment_rank'];

		$temp['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);

		$arr[] = $temp;

	}

	return $arr;

}

?>