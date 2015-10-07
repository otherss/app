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

if(isset($_SESSION['user_id'])&&$_SESSION['user_id']>0)
{
	$user_id = $_SESSION['user_id'];
}
else
{
	$re['msg'] = "请先登录";
	die($json->encode($re));
}

$act = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';
else if($act == 'do_comment')
   
	$rank = isset($_REQUEST['comment_rank'])?$_REQUEST['comment_rank']:0;
	$content = isset($_REQUEST['content'])?trim($_REQUEST['content']):0;
	if($content == ''){
		$re['msg'] = "评论内容不能为空";
		$re['error'] = 1;
	}
		$re['error'] = 1;
		$re['error'] = 1;
			if($result>0){
				$re['msg'] = "操作成功，将继续评论订单中".$result."件商品。";
			}
			
				$re['msg'] = "订单商品全部评论成功";
				$re['error'] = 0;
			
			}
	}

}
else if($act == 'comment_list')
	/* 读评论信息 */
	
	
	$page = !empty($_GET['page']) ? intval($_GET['page']) : 1;
	$comment = array();
	
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
		
		$comment = get_users_comment($_SESSION['user_id'],$page_num,$page_num * ($page - 1));
	$re['count'] = count($comment);

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
	


	$sql = 'SELECT p1.*  , p3.goods_name , p3.goods_price , p3.goods_number , p4.original_img FROM ' . $GLOBALS['ecs']->table('comment') .' as p1 '.
			'left join '. $GLOBALS['ecs']->table('order_goods') .' as p3 on p1.goods_id = p3.goods_id  '.
	" WHERE p1.user_id = '$user_id'  AND p1.parent_id = 0".

	' ORDER BY p1.add_time DESC';
	
// 	$res = $GLOBALS['db']->query($sql);



	$arr = array();

	$ids = '';

	while ($row = $GLOBALS['db']->fetchRow($res))

	{

		$ids .= $ids ? ",$row[comment_id]" : $row['comment_id'];
		
		$temp['comment_id']       = $row['comment_id'];
		
		$temp['goods_name'] = $row['goods_name'];

		$temp['content']  = str_replace('\r\n', '<br />', htmlspecialchars($row['content']));

		$temp['content']  = nl2br(str_replace('\n', '<br />', $temp['content']));

		$temp['comment_rank']     = $row['comment_rank'];

		$temp['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);

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



	
	$back = $GLOBALS['db']->getRow($sql);
	
	$sql = "INSERT INTO " .$GLOBALS['ecs']->table('comment') .
	
	"(content, rec_id,comment_rank, add_time, ip_address, status, user_id ,goods_id) VALUES " .
	
	"('" .$content."', '".$rec_id."','0', ".gmtime().", '".real_ip()."', '0',  '".$_SESSION['user_id']."' , $goods_id)";
	
	$result = $GLOBALS['db']->query($sql);
		
		" SET is_comment = 1 where rec_id =  ".$rec_id ;
		
		$GLOBALS['db']->query($sql);
			" SET is_comment = 1 where order_id =  ".$order_id ;
			
			
			$GLOBALS['db']->query($sql);
?>