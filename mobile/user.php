<?php

/**
 * ECSHOP 用户中心
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: user.php 16643 2009-09-08 07:02:13Z liubo $
*/

define('IN_ECS', true);
define('ECS_ADMIN', true);
error_reporting(NULL); ini_set('display_errors','Off');
require(dirname(__FILE__) . '/includes/init.php');

require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');

$act = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';
$user_id =  isset($_REQUEST['user_id'])   ? intval($_REQUEST['user_id']) : 0;

$user_key = isset($_REQUEST['user_key']) ? trim($_REQUEST['user_key']) : '';


include('includes/cls_json.php');


$json   = new JSON;$re = array(
		"error"=>1,
		"msg"=>"",
		"data"=>array()
);unset($_SESSION['user_id']);$user_name = $user->check_user_app($user_id, $user_key);

if ($user_name!=-1)
{

	$re['error'] = 0;

	$user->set_session($user_name);

	$user->set_cookie($user_name);
		
}// echo $_SESSION['user_id'];if(isset($_SESSION['user_id'])&&$_SESSION['user_id']>0)
{
	$user_id = $_SESSION['user_id'];
}
else
{
	$re['msg'] = "请先登录";
	die($json->encode($re));
}
// $user_id = $_SESSION['user_id'];// $user_id = 9;// $_SESSION['user_id'] = 9;



/* 用户登陆 */
if ($act == 'do_login')
{	$re['msg'] = "账号密码错误";
		
	$user_name = !empty($_REQUEST['username']) ? $_REQUEST['username'] : '';
	$pwd = !empty($_REQUEST['pwd']) ? $_REQUEST['pwd'] : '';
	$gourl = !empty($_REQUEST['gourl']) ? $_REQUEST['gourl'] : '';	
	if (!empty($user_name)&&!empty($pwd)){
		if ($user->check_user($user_name, $pwd) > 0)
		{						$re['error'] = 0;			
			$user->set_session($user_name);
			$user->set_cookie($user_name);						$re['data']['username'] = $_REQUEST ['username'];
			$re['data']['user_id'] = $_REQUEST ['user_id'] ;
			$re['data']['user_key'] = $_REQUEST ['user_id'].rand(100, 999);
				
			update_user_info($re['data']['user_key']);
			if($gourl){
				$re['data']['gourl'] = $gourl;
			}
		}
	}		die($json->encode($re));	
// 	$smarty->assign('login_faild', $login_faild);
// 	$smarty->display('login.dwt');
// 	exit;
}
elseif ($act == 'order_list')
{	
	$flag = isset($_GET['flag'])?$_GET['flag']:"";		$where =" and 1=1 ";		if($flag == "comment-order")
	{
		$where = " and shipping_status =2  and is_comment =0 ";
		
	}
	else if($flag == "send-order")
	{
		$where = " and pay_status =2 and shipping_status !=1 and shipping_status !=2 ";
	}
	else if($flag == "confirm-order")
	{
		$where = " and pay_status=2 and shipping_status =1 ";
	}
	else if($flag == "pay-order")
	{
		$where = " and pay_status=0 and order_status!=2 ";
	}
	else
	{
	
	}		// 		// 	if($flag == "confirm-order")// 	{// 		$where =" and shipping_status = 2";			// 	}				$record_count = $db->getOne("SELECT COUNT(*) FROM " .$ecs->table('order_info'). " WHERE user_id = {$_SESSION['user_id']}".$where);
			$orders = array();	
	if ($record_count > 0){
		include_once(ROOT_PATH . 'includes/lib_transaction.php');
		$page_num = '5';
		$page = !empty($_GET['page']) ? intval($_GET['page']) : 1;
		$pages = ceil($record_count / $page_num);
		if ($page <= 0)
		{
			$page = 1;
		}
		if ($pages == 0)
		{
			$pages = 1;
		}
		$orders = get_user_orders($_SESSION['user_id'], $page_num, $page_num * ($page - 1),'wap',$where);				
		if (!empty($orders))
		{
			foreach ($orders as $key => $val)
			{
				$orders[$key]['total_fee'] = encode_output($val['total_fee']);
			}
		}
	
		$re['data']['order_list'] = $orders;
		$re['data']['pages'] = $pages;				$re['data']['page'] = $page;				
	}		$re['data']['count'] = count($orders);		$re['error'] = 0;	
	die($json->encode($re));
}
/* 订单详情 */
elseif($act=='order_detail'){
	$id= isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
	include_once(ROOT_PATH . 'includes/lib_transaction.php');
	include_once(ROOT_PATH . 'includes/lib_payment.php');
	include_once(ROOT_PATH . 'includes/lib_order.php');
	include_once(ROOT_PATH . 'includes/lib_clips.php');
	/* 订单详情 */
	$order = get_order_detail($id, $_SESSION['user_id']);
	if ($order === false)
	{
		$re['error'] = 1;				$re['msg'] = "该订单不存在";			die($json->encode($re));
	}
	require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');
	/* 订单商品 */
	$goods_list = order_goods2($id);
	if (empty($goods_list))
	{
		$re['error'] = 1;
		
		$re['msg'] = "无效订单";
		
		die($json->encode($re));
	}
// 	foreach ($goods_list AS $key => $value)
// 	{
// 		$goods_list[$key]['market_price'] = price_format($value['market_price'], false);
// 		$goods_list[$key]['goods_price']  = price_format($value['goods_price'], false);
// 		$goods_list[$key]['subtotal']	 = price_format($value['subtotal'], false);
// 	}

	/* 订单 支付 配送 状态语言项 */
// 	$order['order_status'] = $_LANG['os'][$order['order_status']];
// 	$order['pay_status'] = $_LANG['ps'][$order['pay_status']];
// 	$order['shipping_status'] = $_LANG['ss'][$order['shipping_status']];		$re['data']['goods_list'] = $goods_list;		$re['data']['order'] = $order;		$re['error'] = 0;	
	die($json->encode($re));
	
}
/* 取消订单 */
elseif ($act == 'cancel_order')
{
	include_once(ROOT_PATH . 'includes/lib_transaction.php');
	include_once(ROOT_PATH . 'includes/lib_order.php');

	$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
	if (cancel_order($order_id, $_SESSION['user_id']))
	{
		ecs_header("Location: user.php?act=order_list\n");
		exit;
	}
}

/* 确认收货 */
elseif ($act == 'affirm_received')
{
	include_once(ROOT_PATH . 'includes/lib_transaction.php');

	$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
	$_LANG['buyer'] = '买家';
	if (affirm_received($order_id, $_SESSION['user_id']))
	{
		$re['error'] = 0;		$re['msg'] ="操作成功";
	}
	die($json->encode($re));
}elseif ($act == 'user_info')
{	$re['error'] = 0;	$re['data'] = user_info( $_SESSION ['user_id']);		die($json->encode($re));}
?>