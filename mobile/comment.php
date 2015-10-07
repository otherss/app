<?php

/**
 * ECSHOP WAP评论页
 * ============================================================================
 * 版权所有 2005-2011 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: testyang $
 * $Id: comment.php 15013 2008-10-23 09:31:42Z testyang $
*/

define('IN_ECS', true);
define('ECS_ADMIN', true);

include_once(dirname(__FILE__) . '/includes/init.php');
include_once(ROOT_PATH . 'includes/lib_order.php');
// error_reporting(0);
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
// $user_id = $_SESSION['user_id']=9;
if(isset($_SESSION['user_id'])&&$_SESSION['user_id']>0)
{
	$user_id = $_SESSION['user_id'];
}
else
{
	$re['msg'] = "请先登录";
	die($json->encode($re));
}

$act = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';if($act == 'flow_comment'){	$order_id = isset($_REQUEST['order_id'])   ? intval($_REQUEST['order_id']) : 0;		$flow_comment = flow_comment($order_id);			$re['error'] = 0;		$re['data'] = $flow_comment;		die($json->encode($re));	}
else if($act == 'do_comment'){
   
	$rank = isset($_REQUEST['comment_rank'])?$_REQUEST['comment_rank']:0;	
	$content = isset($_REQUEST['content'])?trim($_REQUEST['content']):0;		$goods_id =isset($_REQUEST['goods_id'])?$_REQUEST['goods_id']:0;		$status =isset($_REQUEST['status'])?intval($_REQUEST['status']):0;		$rec_id =isset($_REQUEST['rec_id'])?intval($_REQUEST['rec_id']):0;		$order_id =isset($_REQUEST['order_id'])?intval($_REQUEST['order_id']):0;	
	if($content == ''){
		$re['msg'] = "评论内容不能为空";
		$re['error'] = 1;
	}		elseif($rec_id==0 || $goods_id ==0 || $user_id==0)	{		$re['msg'] = "操作异常";
		$re['error'] = 1;	}		elseif(check_comment($rec_id)==0)	{		$re['msg'] = "不能重复评论";
		$re['error'] = 1;	}	else	{					$result = do_comment($content,$rec_id,$goods_id,$order_id);						
			if($result>0){	
				$re['msg'] = "操作成功，将继续评论订单中".$result."件商品。";				$re['error'] = 2;	
			}			elseif($result==-1){
			
				$re['msg'] = "订单商品全部评论成功";
				$re['error'] = 0;
			
			}			else 			{				$re['msg'] = "操作失败";			}
	}								die($json->encode($re));

}
else if($act == 'comment_list'){	
	/* 读评论信息 */	$record_count = $db->getOne("SELECT COUNT(*) FROM " .$ecs->table('comment'). " WHERE user_id = {$_SESSION['user_id']}");
	
	
	$page = !empty($_GET['page']) ? intval($_GET['page']) : 1;	
	$comment = array();
		if ($record_count > 0)	{
		$page_num = '5';
		
		
		$pages = ceil($record_count / $page_num);
		
		if ($page <= 0)
		
		{
		
			$page = 1;
		
		}
		
		if ($pages == 0)
		
		{
		
			$pages = 1;
		
		}
		
		$comment = get_users_comment($_SESSION['user_id'],$page_num,$page_num * ($page - 1));				}	
	$re['count'] = count($comment);	$re['page'] = $page;	$re['pages'] = $pages;	$re['data'] = $comment;	$re['msg'] = "操作成功";	$re['error'] = 0;		die($json->encode($re));	}

/**

* 查询评论内容

*

* @access  public

* @params  integer     $id

* @params  integer     $type

* @params  integer     $page

* @return  array

*/

function get_users_comment($user_id,$num=5,$start=0)

{

	/* 取得评论列表 */
	


	$sql = 'SELECT p1.*  , p3.goods_name , p3.goods_price , p3.goods_number , p4.original_img FROM ' . $GLOBALS['ecs']->table('comment') .' as p1 '.			//'left join '. $GLOBALS['ecs']->table('users') .' as p2 on p1.user_id = p2.user_id '.
			'left join '. $GLOBALS['ecs']->table('order_goods') .' as p3 on p1.goods_id = p3.goods_id  '.			'left join '. $GLOBALS['ecs']->table('goods') .' as p4 on p1.goods_id = p4.goods_id  '.
	" WHERE p1.user_id = '$user_id'  AND p1.parent_id = 0".

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
		
		$temp['goods_name'] = $row['goods_name'];				$temp['goods_price'] = $row['goods_price'];				$temp['goods_number'] = $row['goods_number'];				$temp['original_img'] = $row['original_img'];

		$temp['content']  = str_replace('\r\n', '<br />', htmlspecialchars($row['content']));

		$temp['content']  = nl2br(str_replace('\n', '<br />', $temp['content']));

		$temp['comment_rank']     = $row['comment_rank'];

		$temp['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);				$arr[] = $temp;

	}

	/* 取得已有回复的评论 */

// 	if ($ids)

// 	{

// 		$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('comment') .

// 		" WHERE parent_id IN( $ids )";

// 		$res = $GLOBALS['db']->query($sql);

// 		while ($row = $GLOBALS['db']->fetch_array($res))

// 		{

// 			$arr[$row['parent_id']]['re_content']  = nl2br(str_replace('\n', '<br />', htmlspecialchars($row['content'])));

// 			$arr[$row['parent_id']]['re_add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);

// 			$arr[$row['parent_id']]['re_email']    = $row['email'];

// 			$arr[$row['parent_id']]['re_username'] = $row['user_name'];

// 		}

// 	}

	

	



	return $arr;

}

function flow_comment($order_id){	 if($order_id==0)	 {	 	return array();	 }	 else	 {	 	 $sql = " select  p2.original_img,p1.goods_name,p1.goods_id,p1.rec_id,p1.goods_price from  ".$GLOBALS['ecs']->table('order_goods')." as p1 ".					" left join".$GLOBALS['ecs']->table('goods')." as p2 on p1.goods_id = p2.goods_id  ".	 	 		" where p1.is_comment = 0 and p1.order_id =  ".$order_id." limit 1 ";	 		 	 	 	 	 	 return $GLOBALS['db']->getRow($sql);	 }}
function check_comment($rec_id){		$sql = " SELECT is_comment from ".$GLOBALS['ecs']->table('order_goods') ." WHERE rec_id=".$rec_id;
	
	$back = $GLOBALS['db']->getRow($sql);		if(!$back || $back['is_comment'] ==1)	{		return 0;	}		return 1;	}function do_comment($content,$rec_id,$goods_id,$order_id){	/* 保存评论内容 */
	
	$sql = "INSERT INTO " .$GLOBALS['ecs']->table('comment') .
	
	"(content, rec_id,comment_rank, add_time, ip_address, status, user_id ,goods_id) VALUES " .
	
	"('" .$content."', '".$rec_id."','0', ".gmtime().", '".real_ip()."', '0',  '".$_SESSION['user_id']."' , $goods_id)";
	
	$result = $GLOBALS['db']->query($sql);// 	$result = 1;	if($result)	{		$sql = "UPDATE " .$GLOBALS['ecs']->table('order_goods') .
		
		" SET is_comment = 1 where rec_id =  ".$rec_id ;
		
		$GLOBALS['db']->query($sql);				$sql = " SELECT COUNT(*) as num from ".$GLOBALS['ecs']->table('order_goods') ." WHERE order_id=".$order_id." AND is_comment = 0 ";				$num = $GLOBALS['db']->getOne($sql);						//若所有商品已评论，则将订单状态置为已评论		if($num >0)		{			return $num;		}		else		{									$sql = "UPDATE " .$GLOBALS['ecs']->table('order_info') .			
			" SET is_comment = 1 where order_id =  ".$order_id ;
			
			
			$GLOBALS['db']->query($sql);						return -1;		}					}		return $result;}
?>