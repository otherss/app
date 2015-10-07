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
error_reporting(E_ALL^E_NOTICE^E_WARNING);



$goods_info = get_goods_info($goods_id);
$goods_info['goods_name'] = encode_output($goods_info['goods_name']);
$goods_info['promote_price'] = encode_output($goods_info['promote_price']);
$goods_info['market_price'] = encode_output($goods_info['market_price']);
$goods_info['shop_price'] = encode_output($goods_info['shop_price']);
$goods_info['shop_price_formated'] = encode_output($goods_info['shop_price_formated']);
$goods_info['goods_number'] = encode_output($goods_info['goods_number']);

$goods_info['gallery'] = get_goods_gallery($goods_id);
// $smarty->assign('goods_info', $goods_info);



$json   = new JSON;
// die($json->encode($goods_info));
// $smarty->assign('related_goods',		 get_linked_goods($goods_id));
//         $properties = get_goods_properties($goods_id);  // 获得商品的规格和属性
//         $smarty->assign('properties',          $properties['pro']);                              // 商品属性





$properties = get_goods_properties($goods_id);  // 获得商品的规格和属性


$goods_gallery = get_goods_gallery($goods_id);
$smarty->assign('now_time',  gmtime()); // 当前系统时间

	return $GLOBALS['db']->getOne($sql);
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