<?php



/**

 * ECSHOP 商品分类页

 * ============================================================================

 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。

 * 网站地址: http://www.ecshop.com；

 * ----------------------------------------------------------------------------

 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和

 * 使用；不允许对程序代码以任何形式任何目的的再发布。

 * ============================================================================

 * $Author: testyang $

 * $Id: category.php 15013 2008-10-23 09:31:42Z testyang $

*/



define('IN_ECS', true);

define('ECS_ADMIN', true);



require(dirname(__FILE__) . '/includes/init.php');

$share_id = isset($_REQUEST['id'])  ?$_REQUEST['id'] : 0;

$action = isset($_REQUEST['act'])  ?$_REQUEST['act'] : '';

if ($_SESSION['user_id'] > 0)

{

	$smarty->assign('user_name', $_SESSION['user_name']);

}


/* 处理晒单评论 */
if($action == 'share_reply')
{
	require(ROOT_PATH . 'includes/cls_json.php');
	
	$content = isset($_REQUEST['content'])  ?$_REQUEST['content'] : '';
	
	$share_id = isset($_REQUEST['id'])  ?$_REQUEST['id'] : 0;
	
	$uname =$_SESSION['user_name'];
	
	
	$json   = new JSON;
	$result = array('error' => 0, 'message' => '', 'content' => '');
	
// 	/* 检查验证码 */
// 	include_once('/includes/cls_captcha.php');
	
// 	$validator = new captcha('/data/captcha');
	
	
// 	if (!$validator->check_word($c))
// 	{
// 		$result['error']   = 1;
// 		$result['message'] = '验证码不正确';
// 	}
	
	
	/* 没有验证码时，用时间来限制机器人发帖或恶意发评论 */
	if (!isset($_SESSION['send_time']))
	{
		$_SESSION['send_time'] = 0;
	}
	
	$cur_time = gmtime();
	if (($cur_time - $_SESSION['send_time']) < 30) // 小于30秒禁止发评论
	{
		$result['error']   = 1;
		$result['message'] = '评论过于频繁,请稍后再评论。';
	}
	else if (!$uname)
		{
			$result['error']   = 2;
			$result['message'] = '只有登录用户才可进行评论。';
		}
	else
	{
		
		add_share_comment($share_id,$uname,$content);
		$_SESSION['send_time'] = $cur_time;
		
	}
	
	echo $json->encode($result);
	exit;
}
/* 点赞 */
else if($action == 'praise')
{
	include('includes/cls_json.php');

	$json   = new JSON;
	$res    = array('err_msg' => '', 'result' => '', 'qty' => 1);

	$ower = $_REQUEST['uid'];
	$share_id = $_REQUEST['share_id'];

	$res['status'] = 0;
	
// 	echo $_SESSION['user_id'];
	$praiser = $_SESSION['user_id']?$_SESSION['user_id']:0;

	 
	 
	

	//     if(!$praiser)
		//     {
	 
		//     	$res['status'] = 4;  //未登录
		//     }

	if($share_id&&$ower)
	{
// 		echo $praiser;
		if($praiser!=0)
		{
			$sql = 'select count(*) from '.$ecs->table("praise").' where share_id ='.$share_id.' and ip = "'.real_ip() .'" and ower = '.$ower;
			$sql2 = 'insert into '.$ecs->table("praise").' (share_id ,ower,ip,date) values ('.$share_id.','.$ower.',"'.real_ip().'",'.gmtime().') ';

		}
		else
		{

			$sql = 'select count(*) from '.$ecs->table("praise").' where share_id ='.$share_id.' and praiser = '.$praiser .' and ower = '.$ower;
			$sql2 = 'insert into '.$ecs->table("praise").' (share_id ,ower,praiser ,ip,date) values ('.$share_id.','.$ower.','.$praiser.',"'.real_ip().'",'.gmtime().') ';

		}
		 
// 		echo $sql;
		
		if($db->getOne($sql))
		{
			$res['status'] = 2;  //已点赞
			$res['sql'] = $sql; 
		}
		else
		{

			if($db->query($sql2))
			{
				$sql = 'update '.$ecs->table("share").' set praise_nums = praise_nums+1 where id = '.$share_id;
				$db->query($sql);
				$res['status'] = 1;
				$res['sql'] = $sql; 
			}
		}
		 
		 
		 
		 
	}
	 
	die($json->encode($res));
}

/* 晒单详情页 */
else if($action == 'info')
{
	
	$flag = isset($_REQUEST['flag'])  ?$_REQUEST['flag'] : '';
	
	$share = get_share_info($share_id);
	
	if ($share === false)
	{
		/* 如果没有找到任何记录则跳回到首页 */
		ecs_header("Location: ./\n");
		exit;
	}
	else
	{
		
		
		 
		$smarty->assign('share',   $share);
		
	
		$smarty->assign('flag',         $flag);
		 
		$smarty->assign('now_time',  gmtime());           // 当前系统时间
		$smarty->display('share_info.dwt');
		exit;
	}
}
else if($action == 'share_comments')
{
	
		$page_num = '2';
	
		$page = !empty($_GET['page']) ? intval($_GET['page']) : 1;
	
		$sql = "SELECT count(*) ".
				" FROM " . $GLOBALS['ecs']->table('share_comment') .
				" WHERE sid='$share_id' and type = 0  and status = 1 ";
		
		$num = $db->getOne($sql);
		
		$pages = ceil($num / $page_num);
	
		if ($page <= 0)
	
		{
	
			$page = 1;
	
		}
	
		if ($pages == 0)
	
		{
	
			$pages = 1;
	
		}
	
		if ($page > $pages)
	
		{
	
			$page = $pages;
	
		}
	
		$start = $page_num * ($page - 1 );
		
		$end = $page_num * $page;
		
		$comments = assign_comment_list($start,$page_num,$share_id);
		
		$smarty->assign('comments', $comments);
	
		$pagebar = get_wap_pager($num, $page_num, $page, 'share.php?act=share_comments&id='.$share_id, 'page');
	
		$smarty->assign('pagebar', $pagebar);
		
		/* 获取分页信息end */
		
		 
	
		
		$smarty->display('share_comments.dwt');
		exit;
	
}
else
{
	$get_sort  = !empty($_REQUEST['sort']) ? $_GET['sort'] : 'gid';
	
	$get_order  = !empty($_REQUEST['order']) ? $_GET['order'] : 'DESC';
	
	
	
	if ($_SESSION['user_id'] > 0)
	
	{
	
		$smarty->assign('user_name', $_SESSION['user_name']);
	
	}
	
	
		$smarty->assign('cat_name', '口碑中心');
	
		
	    if ($get_sort == 'add_time' && $get_order == 'DESC')

	    {
	
	       $order_rule = 'ORDER BY add_time DESC';
	
	    }
	
	    elseif($get_sort == 'add_time' && $get_order == 'ASC')
	
	    {
	
	       $order_rule = 'ORDER BY add_time ASC';
	
	    }
	
		elseif($get_sort == 'praise_nums' && $get_order == 'DESC')
	
		{
	
	       $order_rule = 'ORDER BY praise_nums DESC';
	
		}
	
		elseif($get_sort == 'praise_nums' && $get_order == 'ASC')
	
		{
	
	       $order_rule = 'ORDER BY praise_nums ASC';
	
		}
	
		elseif($get_sort == 'gid' && $get_order == 'DESC')
	
		{
	
	       $order_rule = 'ORDER BY gid DESC';
	
		}
	
		elseif($get_sort == 'gid' && $get_order == 'ASC')
	
		{
	
	       $order_rule = 'ORDER BY gid ASC';
	
		}
	
		else
	
		{
	
	       $order_rule = 'ORDER BY gid desc';
	
		}
	
		
	
// 		$cat_goods = assign_share_list( 0, 'wap', $order_rule);
	
// 		$num = count($cat_goods['goods']);
	
// 		if ($num > 0)
	
// 		{
	
// 			$page_num = '6';
	
// 			$page = !empty($_GET['page']) ? intval($_GET['page']) : 1;
	
// 			$pages = ceil($num / $page_num);
	
// 			if ($page <= 0)
	
// 			{
	
// 				$page = 1;
	
// 			}
	
// 			if ($pages == 0)
	
// 			{
	
// 				$pages = 1;
	
// 			}
	
// 			if ($page > $pages)
	
// 			{
	
// 				$page = $pages;
	
// 			}
	
// 			$i = 1;
	
// 			foreach ($cat_goods['goods'] as $goods_data)
	
// 			{
	
// 				if (($i > ($page_num * ($page - 1 ))) && ($i <= ($page_num * $page)))
	
// 				{
	
					
// 					$data[] = array('i' => $i  , 'id' => $goods_data['id'] , 'name' => encode_output($goods_data['name']), 'goods_img' => $goods_data['goods_img']
// 							, 'praise_nums' => $goods_data['praise_nums'], 'comment_nums' => $goods_data['comment_nums'],'uid' => $goods_data['uid']);
					
	
// 				}
	
// 				$i++;
	
// 			}
	
// 			$smarty->assign('goods_data', $data);
	
		
		$page_num = '2';
		
		$page = !empty($_GET['page']) ? intval($_GET['page']) : 1;
		
		$sql = "SELECT count(*) ".
				" FROM " . $GLOBALS['ecs']->table('share') .
				" WHERE  status = 2 ";
		
		$num = $db->getOne($sql);

		
		$pages = ceil($num / $page_num);
		
		if ($page <= 0)
		
		{
		
			$page = 1;
		
		}
		
		if ($pages == 0)
		
		{
		
			$pages = 1;
		
		}
		
		if ($page > $pages)
		
		{
		
			$page = $pages;
		
		}
		
		$start = $page_num * ($page - 1 );
		
		$end = $page_num * $page;
		
		$share_lists = assign_share_list($start,$page_num,$order_rule);
		
		$smarty->assign('share_lists', $share_lists);
		
		
		
		$pagebar = get_wap_pager($num, $page_num, $page, 'share.php?sort='.(empty($get_sort)?0:$get_sort).'&order='.(empty($get_order)?0:$get_order), 'page');

		$smarty->assign('pagebar', $pagebar);
		$smarty->assign('sort', $get_sort);
		
		$smarty->assign('order', $get_order);
		
		$smarty->assign('footer', get_footer());
		
		
		$smarty->display('share.dwt');
		}
	
	
	
		
	
		
	
	
	
		
	
	
	
	
	








function get_share_info($id)
{

	if(!$id)
	{
		return false;
	}

	$sql = 'SELECT * FROM '.$GLOBALS['ecs']->table('share').' WHERE status > 0 and id ='.$id;

	$res = $GLOBALS['db']->getRow($sql);


	$res['add_time'] = date('Y-m-d',$res['add_time']);



	return $res;
}


function get_user_share_list($user_id,$limit,$excepte)
{

	if(!$user_id)
	{
		return false;
	}


	$sql = 'select id,uid,pic1,msg1,praise_nums,comment_nums from '.$GLOBALS['ecs']->table('share').' WHERE id != '.$excepte.' and status = 2 and uid ='.$user_id.' limit '.$limit;
		

	$res = $GLOBALS['db']->getAll($sql);


	return $res;
}

function get_share_count($gid,$uid)
{

	$where = 'WHERE 1 AND status != 0';


	if ($gid!=0)
	{
		$where .= " AND gid = $gid";
	}
	if ($uid!=0)
	{
		$where .= " AND uid = $uid";
	}

	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('share') . $where;


	return $GLOBALS['db']->getOne($sql);
}


function assign_comment_list($start,$page_size,$sid)
{

	//     $sql = 'SELECT g.goods_id, g.goods_name, g.market_price, g.shop_price AS org_price, ' .
	//                 "IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price, ".
	//                'g.promote_price, promote_start_date, promote_end_date, g.goods_brief, g.goods_thumb, g.goods_img ' .
	//             "FROM " . $GLOBALS['ecs']->table('goods') . ' AS g '.
	//             "LEFT JOIN " . $GLOBALS['ecs']->table('member_price') . " AS mp ".
	//                     "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' ".
	//             'WHERE g.is_on_sale = 1 AND g.is_alone_sale = 1 AND '.
	//                 'g.is_delete = 0 AND (' . $children . 'OR ' . get_extension_goods($children) . ') ';
	
	
	$sql = "SELECT c.*,  r.id as reply_id , r.content AS re_content, r.add_time AS re_time ".
			" FROM " . $GLOBALS['ecs']->table('share_comment') . " AS c ".
			" LEFT JOIN " . $GLOBALS['ecs']->table('share_comment') . " AS r ".
			" ON r.pid = c.id AND r.pid > 0 ".
			" WHERE c.sid='$sid' and c.type = 0  and c.status = 1 order by c.add_time desc";
	
	$res = $GLOBALS['db']->SelectLimit($sql, $page_size, $start);
	
	$comments = array();
	
	while ($row = $GLOBALS['db']->fetchRow($res))
	{
		$row['add_time'] = local_date('Y-m-d H:i:s', $row['add_time']);
		if ($row['re_time'])
		{
			$row['re_time'] = local_date('Y-m-d H:i:s', $row['re_time']);
		}
	
		$comments[] = $row;
	}
	
	
	return $comments;
}

function assign_share_list($start,$page_size,$order_rule)
{

	//     $sql = 'SELECT g.goods_id, g.goods_name, g.market_price, g.shop_price AS org_price, ' .
	//                 "IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price, ".
	//                'g.promote_price, promote_start_date, promote_end_date, g.goods_brief, g.goods_thumb, g.goods_img ' .
	//             "FROM " . $GLOBALS['ecs']->table('goods') . ' AS g '.
	//             "LEFT JOIN " . $GLOBALS['ecs']->table('member_price') . " AS mp ".
	//                     "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' ".
	//             'WHERE g.is_on_sale = 1 AND g.is_alone_sale = 1 AND '.
	//                 'g.is_delete = 0 AND (' . $children . 'OR ' . get_extension_goods($children) . ') ';


	$sql = "SELECT *  FROM " . $GLOBALS['ecs']->table('share') . 
			
			" WHERE status = 2 ".$order_rule;

	$res = $GLOBALS['db']->SelectLimit($sql, $page_size, $start);

	$comments = array();

	while ($row = $GLOBALS['db']->fetchRow($res))
	{
		$row['add_time'] = date('Y-m-d', $row['add_time']);
		

		$comments[] = $row;
	}


	return $comments;
}



/* 保存评论内容 */
function add_share_comment($sid,$uname,$content)
{
	
	$status = 1 - $GLOBALS['_CFG']['comment_check'];
	
	$sql1 = "INSERT INTO " .$GLOBALS['ecs']->table('share_comment') .
	"(type, sid,  uname, content, add_time, ip_address, status, pid) VALUES " .
	"(0, '" .$sid. "',  '$uname', '" .$content."', ".gmtime().", '".real_ip()."', '$status', '0')";
	
	
	
	/* 晒单评论数+1 */
	$sql2 = "update " .$GLOBALS['ecs']->table('share')." set comment_nums = comment_nums + 1 where id = ".$sid;
	 
	
	
	if($GLOBALS['db']->query($sql1)&&$GLOBALS['db']->query($sql2))
	{
		return true;
	}
	return false;
}
?>