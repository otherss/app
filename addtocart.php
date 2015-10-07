<?php

/**
 * ECSHOP 购物流程
 * ============================================================================
 * 版权所有 2005-2010 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: douqinghua $
 * $Id: flow.php 17218 2011-01-24 04:10:41Z douqinghua $
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');



/* AJAX购物车 */

	include_once('includes/cls_json.php');
	$result = array('error' => 0, 'msg' => '', 'data' => '');
	$json = new JSON;
	
	$number = $_GET['number'];
	$goods_id = $_GET['id'];
	
	if ($GLOBALS['_CFG']['use_storage'] == 1)
	{
		$goods_number = $GLOBALS['db']->getOne("select goods_number from ".$GLOBALS['ecs']->table('goods')." where goods_id='$goods_id'");
		if($number>$goods_number)
		{
			$result['error'] = '1';
			$result['msg'] ='对不起,您选择的数量超出库存您最多可购买'.$goods_number."件";
			$result['data']['goods_number']=$goods_number;
			die($json->encode($result));
		}
	}
	
	$goods = cart_goods_info($goods_id);
	
	$linked_goods = get_linked_goods($goods_id);
	$goods['linked_goods'] = $linked_goods;
	
	if(!$goods)
	{
		$result['error'] = '1';
		$result['msg'] ="无此商品记录";
	}
	
	$result['data']['goods'] = $goods;
	
	die($json->encode($result));


	
	
	function cart_goods_info($goods_id)
	{
	
	
		$time = gmtime();
// 		$sql = 'SELECT g.goods_id,g.goods_name,goods_sn,shop_price,market_price,goods_desc,goods_thumb,goods_img,original_img,is_real ' .
		$sql = 'SELECT g.goods_id,g.goods_name,goods_sn,shop_price,market_price,original_img,is_real ' .
		
				'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' .

				"WHERE g.goods_id = '$goods_id' AND g.is_delete = 0 " ;
				
		$row = $GLOBALS['db']->getRow($sql);
	
	
		if ($row !== false)
		{
			
	
			/* 获得商品的销售价格 */
// 			$row['m_price']        = $row['market_price'];
	
// 			$row['s_price'] = $row['shop_price'];
	
// 			$row['r_price'] = $row['rank_price'];
	
// 			$row['market_price']        = price_format($row['market_price']);
	
// 			$row['shop_price'] = price_format($row['shop_price']);
	
		
	
	
	
// 			/* 修正促销价格 */
// 			if ($row['promote_price'] > 0)
// 			{
// 				$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
// 			}
// 			else
// 			{
// 				$promote_price = 0;
// 			}
	
		
			
	
// 			$row['promote_price_org'] =  $promote_price;
// 			$row['promote_price'] =  price_format($promote_price);
	
	
			
	
// 			/* 促销时间倒计时 */
// 			$time = gmtime();
// 			if ($time >= $row['promote_start_date'] && $time <= $row['promote_end_date'])
// 			{
// 				$row['gmt_end_time']  = $row['promote_end_date'];
// 			}
// 			else
// 			{
// 				$row['gmt_end_time'] = 0;
// 			}
	
			/* 是否显示商品库存数量 */
			$row['goods_number']  = ($GLOBALS['_CFG']['use_storage'] == 1) ? $row['goods_number'] : '';
	
		
			
			/* 修正商品图片 */
			$row['goods_img']   = get_image_path($goods_id, $row['goods_img']);
			$row['goods_thumb'] = get_image_path($goods_id, $row['goods_thumb'], true);
			$row['original_img']   = get_image_path($goods_id, $row['original_img']);
			return $row;
		}
		else
		{
			return array();
		}
	}
	
	

	/**
	 * 获得指定商品的关联商品
	 *
	 * @access  public
	 * @param   integer     $goods_id
	 * @return  array
	 */
	function get_linked_goods($goods_id)
	{
		// 	$sql = 'SELECT g.goods_id, g.goods_name, g.goods_thumb,g.original_img, g.goods_img, g.shop_price  ' .
		$sql = 'SELECT g.goods_id, g.goods_name, g.original_img,  g.shop_price  ' .
				'FROM ' . $GLOBALS['ecs']->table('link_goods') . ' AS lgg ' .
				'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.goods_id = lgg.link_goods_id ' .
				"WHERE lgg.goods_id = '$goods_id' AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 limit 4";
	
		$res = $GLOBALS['db']->query($sql);
		// 	return ;
		// 	echo "aaa";
		if($res==false)
		{
			return array();
		}
		$arr = array();
		while ($row = $GLOBALS['db']->fetchRow($res))
		{
			$temp = array();
			$temp['goods_id']     = $row['goods_id'];
			$temp['goods_name']   = sub_str($row['goods_name'],15);
			// 		$arr[$row['goods_id']]['short_name']   = $GLOBALS['_CFG']['goods_name_length'] > 0 ?
			// 		sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
// 			$temp['goods_thumb']  = get_image_path($row['goods_id'], $row['goods_thumb'], true);
			$temp['original_img']    = get_image_path($row['goods_id'], $row['original_img']);
			$temp['market_price'] = price_format($row['market_price']);
			$temp['shop_price']   = price_format($row['shop_price']);
		
	
			$arr[] = $temp;
		}
	
		return $arr;
	}
	

?>