<?php

/**
 * ECSHOP 商品分类
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: category.php 17217 2011-01-19 06:29:08Z liubo $
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}

/*------------------------------------------------------ */
//-- INPUT
/*------------------------------------------------------ */


if($_REQUEST['act'] == 'praise')
{
	include('includes/cls_json.php');

    $json   = new JSON;
    $res    = array('err_msg' => '', 'result' => '', 'qty' => 1);

    $ower = $_REQUEST['uid'];
    $share_id = $_REQUEST['share_id'];
    
    $res['status'] = 0;
    
    $praiser = $_SESSION['user_id']?$_SESSION['user_id']:0;
 
    if($share_id&&$ower)
    {
    	if(!$praiser)
    	{
    		$sql = 'select count(*) from '.$ecs->table("praise").' where share_id ='.$share_id.' and ip = "'.real_ip() .'" and ower = '.$ower;
    		$sql2 = 'insert into '.$ecs->table("praise").' (share_id ,ower,ip,date) values ('.$share_id.','.$ower.',"'.real_ip().'",'.time().') ';
    		
    	}
    	else 
    	{
    		
	    	$sql = 'select count(*) from '.$ecs->table("praise").' where share_id ='.$share_id.' and praiser = '.$praiser .' and ower = '.$ower;
	    	$sql2 = 'insert into '.$ecs->table("praise").' (share_id ,ower,praiser ,ip,date) values ('.$share_id.','.$ower.','.$praiser.',"'.real_ip().'",'.time().') ';
    	}
    	if($db->getOne($sql))
    	{
    		$res['status'] = 2;  //已点赞
    	}
    	else
    	{
    		
    		if($db->query($sql2))
    		{
    			$sql = 'update '.$ecs->table("share").' set praise_nums = praise_nums+1 where id = '.$share_id;
    			$db->query($sql);
    			$res['status'] = 1;
    		}
    	}
    }
    die($json->encode($res));
}




/* 初始化分页信息 */
$page = isset($_REQUEST['page'])   && intval($_REQUEST['page'])  > 0 ? intval($_REQUEST['page'])  : 1;
$size = isset($_CFG['page_size'])  && intval($_CFG['page_size']) > 0 ? intval($_CFG['page_size']) : 10;
$gid = isset($_REQUEST['gid'])? intval($_REQUEST['gid']) : 0;
$uid = isset($_REQUEST['uid'])? intval($_REQUEST['uid']) : 0;
/* 排序、显示方式以及类型 */

$default_sort_order_method = $_CFG['sort_order_method'] == '0' ? 'DESC' : 'ASC';
$default_sort_order_type   = $_CFG['sort_order_type'] == '0' ? 'gid' : ($_CFG['sort_order_type'] == '1' ? 'add_time' : 'praise_nums');

$sort  = (isset($_REQUEST['sort'])  && in_array(trim(strtolower($_REQUEST['sort'])), array('gid', 'add_time', 'praise_nums'))) ? trim($_REQUEST['sort'])  : $default_sort_order_type;
$order = (isset($_REQUEST['order']) && in_array(trim(strtoupper($_REQUEST['order'])), array('ASC', 'DESC')))                              ? trim($_REQUEST['order']) : $default_sort_order_method;

// setcookie('ECS[display]', $display, gmtime() + 86400 * 7);
/*------------------------------------------------------ */
//-- PROCESSOR
/*------------------------------------------------------ */

/* 页面的缓存ID */
$cache_id = sprintf('%X', crc32('-' . $display .'-' . $gid .'-' . $uid . '-' . $sort  .'-' . $order  .'-' . $page . '-' . $size . '-' . $_SESSION['user_rank'] . '-' .
    $_CFG['lang']));

if (!$smarty->is_cached('share.dwt', $cache_id))
{
   
    $smarty->assign('data_dir',    DATA_DIR);
    
    $smarty->assign('gid',    $gid);
    
    $smarty->assign('uid',    $uid);
   
   
    
    $count = get_share_count($gid,$uid);
    
    $size = 8;
    
    $max_page = ($count> 0) ? ceil($count / $size) : 1;
    
    if ($page > $max_page)
    {
        $page = $max_page;
    }
    
    
    
    $sharelist = get_share_list( $size, $page, $sort, $order,$gid,$uid);
  	
    $smarty->assign('goods_list',       $sharelist);
   
    $smarty->assign('navigator_list', get_navigator());

    assign_share_pager('share', $gid,$uid, $count, $size, $sort, $order, $page, '', $brand, $price_min, $price_max, $display, $filter_attr_str); // 分页
    
}

$smarty->display('share.dwt', $cache_id);

/*------------------------------------------------------ */
//-- PRIVATE FUNCTION
/*------------------------------------------------------ */



/**
 * 获得分类下的商品
 *
 * @access  public
 * @param   string  $children
 * @return  array
 */
function get_share_list( $size, $page, $sort, $order,$gid,$uid)
{
    
	$where = 'WHERE 1 AND status = 2';
	
	
	if ($gid!=0)
	{
		$where .= " AND good_id = $gid";
	}
	if ($uid!=0)
	{
		$where .= " AND uid = $uid";
	}
	if ($sort == 'gid')
	{
		$sort = 'gid';
	}
	
   
    
    $sql = 'SELECT * FROM '.$GLOBALS['ecs']->table('share'). $where.' order by '.$sort.'  '.$order;
    
    
    $res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);

    $arr = array();
    
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $arr[$row['id']]['goods_id']         = $row['gid'];
        
        $arr[$row['id']]['add_time']         = date('Y-m-d',$row['add_time']);
        
        $arr[$row['id']]['user_name']        = $row['user_name'];
        
        $arr[$row['id']]['goods_thumb']      = get_image_path($row['goods_id'], $row['pic1'], true);
        
        $arr[$row['id']]['url']              = 'share_info.php?id='.$row['id'];
        
        $arr[$row['id']]['integrate']        = $row['integrate'];

        $arr[$row['id']]['praise_nums']      = $row['praise_nums'];
        
        $arr[$row['id']]['comment_nums']     = $row['comment_nums'];
        
        $arr[$row['id']]['uid']               = $row['uid'];
        
        $arr[$row['id']]['id']               = $row['id'];
        
        $arr[$row['id']]['msg']              = $row['msg'];
        
        $arr[$row['id']]['title']            = $row['title'];
    }


   
    return $arr;
}


function get_share_count($gid,$uid)
{

	$where = 'WHERE 1 AND status = 2';
	
	
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


function assign_share_pager($app, $gid,$uid, $record_count, $size, $sort, $order, $page = 1)
{
	
	$page = intval($page);
	
	if ($page < 1)
	{
		$page = 1;
	}

	$page_count = $record_count > 0 ? intval(ceil($record_count / $size)) : 1;
	
	$pager['page']         = $page;
	$pager['size']         = $size;
	$pager['sort']         = $sort;
	$pager['order']        = $order;
	$pager['record_count'] = $record_count;
	$pager['page_count']   = $page_count;
	
	
	
	$uri_args = array('gid' => $gid, 'uid' =>$uid ,'sort' => $sort, 'order' => $order);
		
	
	
	$page_prev  = ($page > 1) ? $page - 1 : 1;
	$page_next  = ($page < $page_count) ? $page + 1 : $page_count;
	
		$_pagenum = 10;     // 显示的页码
			$_offset = 2;       // 当前页偏移值
			$_from = $_to = 0;  // 开始页, 结束页
			
			
			if($_pagenum > $page_count)
			{
			$_from = 1;
			$_to = $page_count;
			
			}
			else
			{
				$_from = $page - $_offset;
				$_to = $_from + $_pagenum - 1;
				if($_from < 1)
				{
				$_to = $page + 1 - $_from;
				$_from = 1;
				if($_to - $_from < $_pagenum)
				{
				$_to = $_pagenum;
				}
				}
				elseif($_to > $page_count)
				{
				$_from = $page_count - $_pagenum + 1;
				$_to = $page_count;
				}
			}

			
			
				$pager['page_first'] = ($page - $_offset > 1 && $_pagenum < $page_count) ? build_share_uri($app, $uri_args, '', 1) : '';
				$pager['page_prev']  = ($page > 1) ? build_share_uri($app, $uri_args, '', $page_prev) : '';
				$pager['page_next']  = ($page < $page_count) ? build_share_uri($app, $uri_args, '', $page_next) : '';
				$pager['page_last']  = ($_to < $page_count) ? build_share_uri($app, $uri_args, '', $page_count) : '';
				$pager['page_kbd']  = ($_pagenum < $page_count) ? true : false;
				$pager['page_number'] = array();
				for ($i=$_from;$i<=$_to;++$i)
				{
					
				$pager['page_number'][$i] = build_share_uri($app, $uri_args, '', $i);
				
				
				
				}
					
					
				

       
        
       
        
        $GLOBALS['smarty']->assign('pager', $pager);
	}

	
	
	function build_share_uri($app, $params, $append = '', $page = 0, $keywords = '', $size = 0)
	{
		
	
		
	
		$args = array(
				'gid'   => 0,
				'uid'   => 0,
				'sort'  => '',
				'order' => '',
		);
	
		extract(array_merge($args, $params));
	
		
		
		
		
		$uri = 'share.php?';
		if (!empty($gid))
		{
			$uri .= '&amp;gid=' . $gid;
		}
		if (!empty($uid))
		{
			$uri .= '&amp;uid=' . $uid;
		}
		if (!empty($page))
		{
			$uri .= '&amp;page=' . $page;
		}
		if (!empty($sort))
		{
			$uri .= '&amp;sort=' . $sort;
		}
		if (!empty($order))
		{
			$uri .= '&amp;order=' . $order;
		}
				 
		
		
		return $uri;
	}
	
	
	
	
?>
