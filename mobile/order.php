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

 * $Author: liuhui $

 * $Id: order.php 15013 2008-10-23 09:31:42Z liuhui $

*/



define('IN_ECS', true);

define('ECS_ADMIN', true);



require(dirname(__FILE__) . '/includes/init.php');

require(ROOT_PATH . 'includes/lib_order.php');

require(ROOT_PATH . 'includes/lib_clips.php');

require(ROOT_PATH . 'includes/lib_payment.php');

error_reporting(E_ALL^E_NOTICE^E_WARNING);

/* 载入语言文件 */

require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/common.php');




$user_id =  isset($_REQUEST['user_id'])   ? intval($_REQUEST['user_id']) : 0;

$user_key = isset($_REQUEST['user_key']) ? trim($_REQUEST['user_key']) : '';





include('includes/cls_json.php');


$json   = new JSON;

$re = array(
		"error"=>1,
		"msg"=>"",
		"data"=>array()
);

unset($_SESSION['user_id']);

$user_name = $user->check_user_app($user_id, $user_key);

if ($user_name!=-1)
{

	$re['error'] = 0;

	$user->set_session($user_name);

	$user->set_cookie($user_name);



}


// echo $_SESSION['user_id'];

if(isset($_SESSION['user_id'])&&$_SESSION['user_id']>0)
{
	$user_id = $_SESSION['user_id'];
}
else
{
	$re['msg'] = "请先登录";
	die($json->encode($re));
}


$carts = $_REQUEST['cart'];

if($_REQUEST['act'] == 'order_lise')

{



		$best_time = empty($_POST['best_time'])  ? '' : compile_str($_POST['best_time']);
		
		$address_id = empty($_POST['address_id'])  ? '' : 0;

	$consignee = get_consignee($_SESSION['user_id'],$address_id);

	if (empty($carts['cart']))

	{
		
		$result['error'] =1;
		$result['msg'] ="没有被选中的商品。";
		echo $json->encode($result);
	
		exit;

	}
	
	$cart_goods = my_cart_goods($carts['cart']);


	//$order = flow_order_info();

	$total = order_fee($order, $cart_goods);


	$user_info = user_info($_SESSION['user_id']);
	
	
	$res['data']['total'] = $total;
	
	$res['data']['consignee'] = $consignee;
	
	$res['data']['goods'] = $cart_goods;
	
	$res['msg'] = "订单确认";
	
	echo $json->encode($res);
	
	// 	exit;

	exit;

}

elseif ($_REQUEST['act'] == 'change_surplus')

{

    /*------------------------------------------------------ */

    //-- 改变余额

    /*------------------------------------------------------ */

    include_once('includes/cls_json.php');



    $surplus   = floatval($_GET['surplus']);

    $user_info = user_info($_SESSION['user_id']);



    if ($user_info['user_money'] + $user_info['credit_line'] < $surplus)

    {

        $result['error'] = '您的购物车中没有商品！';

    }

    else

    {

        /* 取得购物类型 */

        $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;



        /* 取得购物流程设置 */

        $smarty->assign('config', $_CFG);



        /* 获得收货人信息 */

        $consignee = get_consignee($_SESSION['user_id']);



        /* 对商品信息赋值 */

        $cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计



        if (empty($cart_goods))

        {

            $result['error'] = '您的购物车中没有商品！';

        }

        else

        {

            /* 取得订单信息 */

            $order = flow_order_info();

            $order['surplus'] = $surplus;



            /* 计算订单的费用 */

            $total = order_fee($order, $cart_goods, $consignee);

            $smarty->assign('total', $total);



            /* 团购标志 */

            if ($flow_type == CART_GROUP_BUY_GOODS)

            {

                $smarty->assign('is_group_buy', 1);

            }



            $result['content'] = $smarty->fetch('order_total.dwt');

        }

    }



    $json = new JSON();

    die($json->encode($result));

}


elseif ($_REQUEST['act'] == 'change_integral')

{

    /*------------------------------------------------------ */

    //-- 改变积风

    /*------------------------------------------------------ */

    include_once('includes/cls_json.php');



    $points    = floatval($_GET['points']);

    $user_info = user_info($_SESSION['user_id']);



    /* 取得订单信息 */

    $order = flow_order_info();



    $flow_points = flow_available_points();  // 该订单允许使用的积风

    $user_points = $user_info['pay_points']; // 用户的积风总数



    if ($points > $user_points)

    {

        $result['error'] = '您使用的积风不能超过您现有的积风。';

    }

    elseif ($points > $flow_points)

    {

        $result['error'] = sprintf("您使用的积风不能超过%d", $flow_points);

    }

    else

    {

        /* 取得购物类型 */

        $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;



        $order['integral'] = $points;



        /* 获得收货人信息 */

        $consignee = get_consignee($_SESSION['user_id']);



        /* 对商品信息赋值 */

        $cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计



        if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type))

        {

            $result['error'] = '您的购物车中没有商品！';

        }

        else

        {

            /* 计算订单的费用 */

            $total = order_fee($order, $cart_goods, $consignee);

            $smarty->assign('total',  $total);

            $smarty->assign('config', $_CFG);



            /* 团购标志 */

            if ($flow_type == CART_GROUP_BUY_GOODS)

            {

                $smarty->assign('is_group_buy', 1);

            }



            $result['content'] = $smarty->fetch('order_total.dwt');

            $result['error'] = '';

        }

    }



    $json = new JSON();

    die($json->encode($result));

}



elseif($_REQUEST['act'] = 'done')

{

	/*------------------------------------------------------ */

	//-- 完成所有订单操作，提交到数据库

	/*------------------------------------------------------ */



    include_once('includes/lib_clips.php');

//     include_once('includes/lib_payment.php');


	/* 检查购物车中是否有商品 */

// 	$sql = "SELECT COUNT(*) FROM " . $ecs->table('cart') .

// 		" WHERE session_id = '" . SESS_ID . "' " .

// 		"AND parent_id = 0 AND is_gift = 0 AND rec_type = '$flow_type'";

// 	if ($db->getOne($sql) == 0)

// 	{

//         $result['error'] = '您的购物车中没有商品！';

// 	}



	/* 检查商品库存 */

	/* 如果使用库存，且下订单时减库存，则减少库存 */

//  	if ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_PLACE)

// 	{

// 		$cart_goods_stock = get_cart_goods();

// 		$_cart_goods_stock = array();

// 		foreach ($cart_goods_stock['goods_list'] as $value)

// 		{

// 			$_cart_goods_stock[$value['rec_id']] = $value['goods_number'];

// 		}

// 		flow_cart_stock($_cart_goods_stock);

// 		unset($cart_goods_stock, $_cart_goods_stock);

// 	}
 


	$consignee = get_consignee($_SESSION['user_id'],$address_id);

	

	$order = array(

        'shipping_id'     => intval($_REQUEST['shipping']),

        'pay_id'          => intval($_REQUEST['payment']),
		
		'best_time'          => $_REQUEST['shipping_time'],

        'surplus'         => isset($_REQUEST['surplus']) ? floatval($_REQUEST['surplus']) : 0.00,

        'integral'        => isset($_REQUEST['integral']) ? intval($_REQUEST['integral']) : 0,



        'user_id'         => $_SESSION['user_id'],

        'add_time'        => gmtime(),

        'order_status'    => OS_UNCONFIRMED,

        'shipping_status' => SS_UNSHIPPED,

        'pay_status'      => PS_UNPAYED,

      

		);
	
	
	
// 	$result['data']['order'] = $order;
// 	$result['data']['cart'] =  $_GET['cart']?$_GET['cart']:0;
	
// 	var_dump($_GET['cart']);
// 	exit;
	
// 	echo $json->encode($result);
	
// 	exit;


	/* 检查积风余额是否合法 */

 	$user_id = $_SESSION['user_id'];

	if ($user_id > 0)

	{

		$user_info = user_info($user_id);



		$order['surplus'] = min($order['surplus'], $user_info['user_money'] + $user_info['credit_line']);

		if ($order['surplus'] < 0)

		{

			$order['surplus'] = 0;

		}



		// 查询用户有多少积风

		$flow_points = flow_available_points();  // 该订单允许使用的积风

		$user_points = $user_info['pay_points']; // 用户的积风总数



		$order['integral'] = min($order['integral'], $user_points, $flow_points);

		if ($order['integral'] < 0)

		{

			$order['integral'] = 0;

		}

	}

	else

	{

		$order['surplus']  = 0;

		$order['integral'] = 0;

	}






    /* 订单中的商品 */
	
	
	
	//$cart_goods = cart_goods($flow_type);
	
	
	
	
$cart_goods = my_cart_goods($carts['cart']);
// 	echo $json->encode($cart_goods);
	
// 	exit;

// 	die($cart_goods);
	
// 	$cart_goods = $_GET['cart'][0]['goods'];

	if (empty($cart_goods))

	{
		
		$result['error'] =1;
		$result['msg'] ="没有被选中的商品。";
		echo $json->encode($result);
	
		exit;

	}





	/* 收货人信息 */

	foreach ($consignee as $key => $value)

	{

		$order[$key] = addslashes($value);

	}



    /* 订单中的总额 */

    $total = order_fee($order, $cart_goods, $consignee);
	
//    
//     $order['bonus']        = $total['bonus'];

    $order['goods_amount'] = $total['goods_price'];

//     $order['discount']     = $total['discount'];

    $order['surplus']      = $total['surplus'];



	


	$order['integral_money']   = $total['integral_money'];

	$order['integral']		 = $total['integral'];


	
	if ($order['extension_code'] == 'exchange_goods')

	{

		$order['integral_money']   = 0;

		$order['integral']		 = $total['exchange_integral'];

	}



	//$order['from_ad']		  = !empty($_SESSION['from_ad']) ? $_SESSION['from_ad'] : '0';

// 	$order['referer']		  = !empty($_SESSION['referer']) ? addslashes($_SESSION['referer']) : '';

	

	$order['order_amount']  = number_format($total['amount'], 2, '.', '');

	/* 如果全部使用余额支付，检查余额是否足够 */

	if ($payment['pay_code'] == 'balance' && $order['order_amount'] > 0)

	{

		if($order['surplus'] >0) //余额支付里如果输入了一个金额

		{

			$order['order_amount'] = $order['order_amount'] + $order['surplus'];

			$order['surplus'] = 0;

		}

		if ($order['order_amount'] > ($user_info['user_money'] + $user_info['credit_line']))

		{

			$tips = '您的余额不足以支付整个订单，请选择其他支付方式';

			
			$result['error'] =1;
			$result['msg'] =$tips;
			echo $json->encode($result);
		
			exit;

		}

		else

		{

			$order['surplus'] = $order['order_amount'];

			$order['order_amount'] = 0;

		}

	}

	

	/* 如果订单金额为0（使用余额或积风或红包支付），修改订单状态为已确认、已付款 */

	if ($order['order_amount'] <= 0)

	{

		$order['order_status'] = OS_CONFIRMED;

		$order['confirm_time'] = gmtime();

		$order['pay_status']   = PS_PAYED;

		$order['pay_time']	 = gmtime();

		$order['order_amount'] = 0;

	}



	$order['integral_money']   = $total['integral_money'];

	$order['integral']		 = $total['integral'];



	if ($order['extension_code'] == 'exchange_goods')

	{

		$order['integral_money']   = 0;

		$order['integral']		 = $total['exchange_integral'];

	}


	$order['shipping_name'] = get_shipping_name($order['shipping_id']);
	
	$order['pay_name'] = get_payment_name($order['pay_id']);
// 	var_dump($order);
	
// 	exit;


	
	/* 插入订单表 */

	$error_no = 0;

	do

	{
	
		
		
		$order['order_sn'] = get_order_sn(); //获取新订单号
		
// 		$smarty->assign("order_sn", $order['order_sn']);
		
// 		$tips = "订单 ".$order['order_sn']." 提交成功！";
		
// 		echo $json->encode($order);
// 		exit;
		
		
		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_info'), $order, 'INSERT');
		
		
		
// 		$smarty->assign("order_id", $GLOBALS['db']->insert_id());

		$error_no = $GLOBALS['db']->errno();



		if ($error_no > 0 && $error_no != 1062)

		{

			die($GLOBALS['db']->errorMsg());

		}

	}
	
	while ($error_no == 1062); //如果是订单号重复则重新提交数据

	

	$new_order_id = $db->insert_id();

	$order['order_id'] = $new_order_id;
	
// 	$result['data']['order_id'] = $new_order_id;
	
	
// 	var_dump($result);
// 	echo $json->encode($result);
	
// 	exit;

    /* 插入订单商品 */
	
	
	
	foreach($cart_goods as $k => $v)
	{
// 		$temp = $v['goods'];
// 		$temp['goods_number'] =  $v['goods_num'];
// 		$temp['goods_price'] =  $v['goods']['shop_price'];
		$temp = $v;
		unset($temp['goods_desc']);
// 		unset($temp['is_real']);
		unset($temp['original_img']);
		unset($temp['goods_img']);
		unset($temp['goods_thumb']);
		unset($temp['shop_price']);
		
// 		echo $json->encode($temp);
		
// 		exit;
		
		$temp['order_id'] =$new_order_id;
		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_goods'), $temp, 'INSERT');
	}
	
	
	
//     $sql = "INSERT INTO " . $ecs->table('order_goods') . "( " .

//                 "order_id, goods_id, goods_name, goods_sn, product_id, goods_number, market_price, ".

//                 "goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, goods_attr_id) ".

//             " SELECT '$new_order_id', goods_id, goods_name, goods_sn, product_id, goods_number, market_price, ".

//                 "goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, goods_attr_id".

//             " FROM " .$ecs->table('cart') .

//             " WHERE session_id = '".SESS_ID."' AND rec_type = '$flow_type'";

//     $db->query($sql);

	

	/* 处理余额、积风、红包 */

	if ($order['user_id'] > 0 && $order['surplus'] > 0)

	{

		log_account_change($order['user_id'], $order['surplus'] * (-1), 0, 0, 0, sprintf('支付订单 %s', $order['order_sn']));

	}

	if ($order['user_id'] > 0 && $order['integral'] > 0)

	{

		log_account_change($order['user_id'], 0, 0, 0, $order['integral'] * (-1), sprintf('支付订单 %s', $order['order_sn']));

	}

   
	/* 如果使用库存，且下订单时减库存，则减少库存 */

	if ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_PLACE)

	{

		change_order_goods_storage($order['order_id'], true, SDT_PLACE);

	}





	/* 清空购物车 */

// 	clear_cart($flow_type);

	/* 清除缓存，否则买了商品，但是前台页面读取缓存，商品数量不减少 */

	clear_all_files();



	if(!empty($order['shipping_name']))

	{

		$order['shipping_name']=trim(stripcslashes($order['shipping_name']));

	}

	/* 取得支付信息，生成支付代码 */

	if ($order['order_amount'] > 0)

	{

		$order['log_id'] = insert_pay_log($new_order_id, $order['order_amount'], PAY_ORDER);


	} 

// 	unset($_SESSION['flow_consignee']); // 清除session中保存的收货人信息
	
// 	unset($_SESSION['flow_order']);
	
// 	unset($_SESSION['direct_shopping']);
	
	
	$result['error'] = 0;
	$result['msg'] = '订单提交成功！';
	$result['data']['amount'] = $order['order_amount'];
	$result['data']['title'] = "订单名称";
	$result['data']['desc'] = "订单描述";
	$result['data']['order_sn'] = $order['order_sn'];
	$result['data']['log_id'] = $order['log_id'];
// 	$result['data']['order'] = $order;
// 	$order['order_sn']
// 	$result['data']['order_sn'] = "订单描述";
	echo $json->encode($result);
	
	exit;

	/* 订单信息 */

// 	$smarty->assign('order', $order);

// 	$smarty->assign('total', $total);

// 	$smarty->assign('goods_list', $cart_goods);

	
}





function flow_available_points()

{

	$sql = "SELECT SUM(g.integral * c.goods_number) ".

			"FROM " . $GLOBALS['ecs']->table('cart') . " AS c, " . $GLOBALS['ecs']->table('goods') . " AS g " .

			"WHERE c.session_id = '" . SESS_ID . "' AND c.goods_id = g.goods_id AND c.is_gift = 0 AND g.integral > 0 " .

			"AND c.rec_type = '" . CART_GENERAL_GOODS . "'";



	$val = intval($GLOBALS['db']->getOne($sql));



	return integral_of_value($val);

}



/**

 * 检查订单中商品库存

 *

 * @access  public

 * @param   array   $arr

 *

 * @return  void

 */

function flow_cart_stock($arr)

{

	foreach ($arr AS $key => $val)

	{

		$val = intval(make_semiangle($val));

		if ($val <= 0)

		{

			continue;

		}



		$sql = "SELECT `goods_id`, `goods_attr_id`, `extension_code` FROM" .$GLOBALS['ecs']->table('cart').

			   " WHERE rec_id='$key' AND session_id='" . SESS_ID . "'";

		$goods = $GLOBALS['db']->getRow($sql);



		$sql = "SELECT g.goods_name, g.goods_number, c.product_id ".

				"FROM " .$GLOBALS['ecs']->table('goods'). " AS g, ".

					$GLOBALS['ecs']->table('cart'). " AS c ".

				"WHERE g.goods_id = c.goods_id AND c.rec_id = '$key'";

		$row = $GLOBALS['db']->getRow($sql);



		//系统启用了库存，检查输入的商品数量是否有效

		if (intval($GLOBALS['_CFG']['use_storage']) > 0 && $goods['extension_code'] != 'package_buy')

		{

			if ($row['goods_number'] < $val)

			{

				show_message(sprintf($GLOBALS['_LANG']['stock_insufficiency'], $row['goods_name'],

				$row['goods_number'], $row['goods_number']));

				exit;

			}



			/* 是货品 */

			$row['product_id'] = trim($row['product_id']);

			if (!empty($row['product_id']))

			{

				$sql = "SELECT product_number FROM " .$GLOBALS['ecs']->table('products'). " WHERE goods_id = '" . $goods['goods_id'] . "' AND product_id = '" . $row['product_id'] . "'";

				$product_number = $GLOBALS['db']->getOne($sql);

				if ($product_number < $val)

				{

					show_message(sprintf($GLOBALS['_LANG']['stock_insufficiency'], $row['goods_name'],

					$row['goods_number'], $row['goods_number']));

					exit;

				}

			}

		}

		elseif (intval($GLOBALS['_CFG']['use_storage']) > 0 && $goods['extension_code'] == 'package_buy')

		{

			if (judge_package_stock($goods['goods_id'], $val))

			{

				show_message($GLOBALS['_LANG']['package_stock_insufficiency']);

				exit;

			}

		}

	}



}

function my_cart_goods($carts)
{
	$cart_goods = array();
	
	
	
	foreach($carts as $k => $v)
	{
// 		$temp = $v['goods'];
// 		$temp['goods_number'] =  $v['goods_num'];
// 		$temp['goods_id'] =  $v['goods']['goods_id'];
		$cart_goods[] = $v['goods']['goods_id'];
		
	}
	
	
	$where = implode(",",$cart_goods);
	
// 	echo $where;
// 	$where = "24,25";
	$sql = " select goods_id , shop_price , market_price,goods_name,goods_sn,is_real , original_img from " .$GLOBALS['ecs']->table('goods'). " WHERE goods_id in (" . $where . ") and is_on_sale =1 and is_delete = 0 ";
	
	
	$cart_goods = $GLOBALS['db']->getAll($sql);
	
	foreach($carts as $k => $v)
	{
		foreach($cart_goods as $kk => $vv)
		{
			if($vv['goods_id'] ==$v['goods']['goods_id'])
			{
				
				$cart_goods[$kk]['goods_number'] = $v['goods_num'];
				$cart_goods[$kk]['goods_price'] = $vv['shop_price'];
			}
		}
	}
	
	return $cart_goods;
	
}


	function get_shipping_name($shipping_id)
	{
		
		$shipping_name = "";
		
		if($shipping_id>0)
		{
			 $sql = " select shipping_name  from " .$GLOBALS['ecs']->table('shipping'). " WHERE shipping_id = ".$shipping_id;
	
			 $shipping_name = $GLOBALS['db']->getOne($sql);
			 
		}
		
		return $shipping_name;
	}
	
	
	function get_payment_name($pay_id)
	{
		$pay_name = "";
		
		if($pay_id>0)
		{
			$sql = " select pay_name  from " .$GLOBALS['ecs']->table('payment'). " WHERE pay_id = ".$pay_id;
		
			$pay_name = $GLOBALS['db']->getOne($sql);
		
		}
		
		return $pay_name;
	}

?>