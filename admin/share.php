<?php

/**
 * ECSHOP 订单管理
 * ============================================================================
 * 版权所有 2005-2010 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: yehuaixiao $
 * $Id: order.php 17219 2011-01-27 10:49:19Z yehuaixiao $
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'includes/lib_order.php');
require_once(ROOT_PATH . 'includes/lib_goods.php');



if ($_REQUEST['act'] == 'share_list')
{
//     /* 检查权限 */
    admin_priv('order_view');

    /* 模板赋值 */
    $smarty->assign('ur_here', '晒单列表');
    

    $smarty->assign('status_list', $_LANG['cs']);   // 订单状态

   
   
   
    $smarty->assign('full_page',        1);

    $order_list = share_list();
    $smarty->assign('order_list',   $order_list['orders']);
    $smarty->assign('filter',       $order_list['filter']);
    $smarty->assign('record_count', $order_list['record_count']);
    $smarty->assign('page_count',   $order_list['page_count']);
    $smarty->assign('sort_order_time', '<img src="images/sort_desc.gif">');

    /* 显示模板 */
    assign_query_info();
    $smarty->display('share_list.htm');
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    /* 检查权限 */
    admin_priv('order_view');

    $order_list = share_list();

    $smarty->assign('order_list',   $order_list['orders']);
    $smarty->assign('filter',       $order_list['filter']);
    $smarty->assign('record_count', $order_list['record_count']);
    $smarty->assign('page_count',   $order_list['page_count']);
    $sort_flag  = sort_flag($order_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);
    make_json_result($smarty->fetch('share_list.htm'), '', array('filter' => $order_list['filter'], 'page_count' => $order_list['page_count']));
}

/*------------------------------------------------------ */
//-- 订单详情页面
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'oper')
{
	$id = isset($_GET['id'])?intval($_GET['id']):0;
	
	$order_id = isset($_GET['order_id'])?intval($_GET['order_id']):0;
	
	$user_id = $db->getOne("select uid from ".$ecs->table('share')." where id = ".$id) ;
	
	if($id > 0&&$order_id>0&&$user_id>0)
	{
		$credit = $db->getOne("select value from ".$ecs->table('shop_config')." where code = 'share_points'") ;
		
		$sql = 'update '.$ecs->table('share').' set status = '.$_GET['status'].' , integrate = '.$credit.'  where id = '.$id ;
		 
		$links[]    = array('text' =>'晒单列表', 'href' => 'share.php?act=share_list');
		 
		if($db->query($sql))
		{
			$sql = 'update '.$ecs->table('order_info').' set is_share = '.$_GET['status'].' where order_id = '.$order_id ;
			
			$db->query($sql);
			
			
			if($_GET['status'] == 2)
			{
				
					
				$sql = "UPDATE ".$ecs->table("users")." SET pay_points = pay_points + $credit , rank_points = rank_points+ $credit  WHERE user_id =".$user_id;
			
				$db->query($sql);
					
				//添加账户积风流程记录
				$sql = "INSERT INTO " . $ecs->table("account_log") . "(user_id, change_time, rank_points,pay_points,change_desc, change_type)" . " VALUES ($user_id, '". gmtime() ."',$credit , $credit, '晒单通过', '99')";
					
				$db->query($sql);
					
			}
			else if($_GET['status'] == 1)
			{
				
			
				$sql = "UPDATE ".$ecs->table("users")." SET pay_points = pay_points - $credit , rank_points = rank_points- $credit  WHERE user_id =".$user_id;
					
				$db->query($sql);
				
				$credit = 0- $credit;
				//添加账户积风流程记录
				$sql = "INSERT INTO " . $ecs->table("account_log") . "(user_id, change_time, rank_points,pay_points,change_desc, change_type)" . " VALUES ($user_id, '". gmtime() ."',$credit , $credit, '晒单重置', '99')";
			
				$db->query($sql);
			}
			
			/* 清除缓存 */
			clear_cache_files();
			sys_msg('操作成功!',0,$links);
		}
		else
		{
			sys_msg('操作失败!',1,$links);
		}
		 
	}
	else
	{
		ecs_header("Location: share.php?act=share_list\n");
	}
	
}

elseif ($_REQUEST['act'] == 'info')
{
	$id = isset($_GET['id'])?intval($_GET['id']):0;
	
	
	if($id > 0)
	{
		$smarty->assign('full_page',        1);
		$sql = 'select * from '.$ecs->table('share').' where id = '.$id ;
		$share = $db->getRow($sql);
		$smarty->assign('ur_here', '晒单详情');
		$smarty->assign('action_link', array('text' =>'晒单列表', 'href' => 'share.php?act=share_list'));
		$smarty->assign('share', $share);
		$smarty->display('share_info.htm');
		
		
		 
	}
	else
	{
		ecs_header("Location: share.php?act=share_list\n");
	}
	
}
elseif ($_REQUEST['act'] == 'del')
{
    $id = isset($_GET['id'])?intval($_GET['id']):0;
    
  $order_id =  isset($_GET['order_id'])?intval($_GET['order_id']):0;
    
    if($id > 0 && $order_id >0)
    {
    	
    	$sql = 'delete from '.$ecs->table('share').' where id = '.$id ;
    	
    	$sql2 = 'update  '.$ecs->table('order_info').'  set is_share = 0  where order_id = '.$order_id ;
    	
    	$links[]    = array('text' =>'晒单列表', 'href' => 'share.php?act=share_list');
    	
    	
    	if($db->query($sql)&&$db->query($sql2))
    	{
    		/* 清除缓存 */
    		clear_cache_files();
    		sys_msg('删除成功!',0,$links);
    	}
    	else 
    	{
    		sys_msg('操作失败!',1,$links);
    	}
    	
    }
    else 
    {
    	ecs_header("Location: share.php?act=share_list\n");
    }
}

else if ($_POST['act'] == 'batch')
{
	admin_priv('comment_priv');
	$action = isset($_POST['sel_action']) ? trim($_POST['sel_action']) : 'deny';
	
	$sql = 'select order_id from '. $ecs->table('share') . " WHERE " . db_create_in($_POST['checkboxes'], 'id');
	
	$order_ids = $db->getCol($sql);
	
	
	
	
	if (isset($_POST['checkboxes']))
	{
		switch ($action)
		{
			case 'remove':
// 				$db->query("UPDATE " . $ecs->table('share') . " SET status = 0  WHERE " . db_create_in($_POST['checkboxes'], 'id'));
				$db->query("DELETE FROM " . $ecs->table('share') . " WHERE " . db_create_in($_POST['checkboxes'], 'id'));	
				$db->query('update '.$ecs->table('order_info').' set is_share = 0 ,share_id = 0 where '.db_create_in($order_ids, 'order_id'));
				break;

			case 'allow' :
				$db->query("UPDATE " . $ecs->table('share') . " SET status = 2 ,check_time = ".gmtime()." WHERE " . db_create_in($_POST['checkboxes'], 'id'));
				$db->query('update '.$ecs->table('order_info').' set is_share = 2 where '.db_create_in($order_ids, 'order_id'));
				break;
			case 'reset' :
				$db->query("UPDATE " . $ecs->table('share') . " SET status = 1 ,check_time = ".gmtime()." WHERE " . db_create_in($_POST['checkboxes'], 'id'));
				$db->query('update '.$ecs->table('order_info').' set is_share = 1 where '.db_create_in($order_ids, 'order_id'));
				break;

			case 'deny' :
				$db->query("UPDATE " . $ecs->table('share') . " SET status = 3 ,check_time = ".gmtime()." WHERE " . db_create_in($_POST['checkboxes'], 'id'));
				$db->query('update '.$ecs->table('order_info').' set is_share = 3 where '.db_create_in($order_ids, 'order_id'));
				break;

			default :
				break;
		}
		echo 'aaa';
		clear_cache_files();
		$action = ($action == 'remove') ? 'remove' : 'edit';
		admin_log('', $action, 'adminlog');

		$link[] = array('text' => '返回晒单列表', 'href' => 'share.php?act=share_list');
		sys_msg(sprintf('执行成功', count($_POST['checkboxes'])), 0, $link);
	}
	else
	{
		/* 提示信息 */
		$link[] = array('text' => '返回晒单列表', 'href' => 'share.php.php?act=share_list');
		sys_msg('无选中记录', 0, $link);
	}
}


/**
 *  获取订单列表信息
 *
 * @access  public
 * @param
 *
 * @return void
 */
function share_list()
{
    $result = get_filter();
    if ($result === false)
    {
        /* 过滤信息 */
        $filter['order_sn'] = empty($_REQUEST['order_sn']) ? '' : trim($_REQUEST['order_sn']);
        
        $filter['status'] = isset($_REQUEST['status']) ? intval($_REQUEST['status']) : -1;
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'add_time' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		
       
        $where = 'WHERE 1 ';
        
        if ($filter['order_sn'])
        {
            $where .= " AND o.order_sn LIKE '%" . mysql_like_quote($filter['order_sn']) . "%'";
        }
        if ($filter['status'] != -1&&$filter['status'] != 0)
        {
        	$where .= " AND status = '$filter[status]'";
        }
        else
        {
        	$where .= " AND status != 0";
        }
		
        
        
        /* 分页大小 */
        $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);

        if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0)
        {
            $filter['page_size'] = intval($_REQUEST['page_size']);
        }
        elseif (isset($_COOKIE['ECSCP']['page_size']) && intval($_COOKIE['ECSCP']['page_size']) > 0)
        {
            $filter['page_size'] = intval($_COOKIE['ECSCP']['page_size']);
        }
        else
        {
            $filter['page_size'] = 15;
        }

        $where .= " AND s.order_id = o.order_id AND s.gid = g.goods_id and o.is_share != 0 ";
        
        $sql = "SELECT COUNT(*) from ".$GLOBALS['ecs']->table('share')."as s ,".$GLOBALS['ecs']->table('order_info')." as o ,".$GLOBALS['ecs']->table('goods')." as g ".$where;
        
		
        
        $filter['record_count']   = $GLOBALS['db']->getOne($sql);
        $filter['page_count']     = $filter['record_count'] > 0 ? ceil($filter['record_count'] / $filter['page_size']) : 1;
        
        /* 查询 */
        $sql = "SELECT s.*,o.order_sn,g.goods_name  from ".$GLOBALS['ecs']->table('share')."as s ,".$GLOBALS['ecs']->table('order_info')." as o ,".$GLOBALS['ecs']->table('goods')." as g $where  ORDER BY $filter[sort_by] $filter[sort_order] ".
                " LIMIT " . ($filter['page'] - 1) * $filter['page_size'] . ",$filter[page_size]";
        
        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }

    $row = $GLOBALS['db']->getAll($sql);

    /* 格式话数据 */
    foreach ($row AS $key => $value)
    {
       
        $row[$key]['short_order_time'] = local_date('m-d H:i', $value['add_time']);
        switch ($value['status'])
        {
        	case 1:
        		$row[$key]['oper'] = '<a href="share.php?act=oper&id='.$row[$key]['id'].'&status=2&order_id='.$row[$key]['order_id'].'">通过</a>  <a href="share.php?act=oper&id='.$row[$key]['id'].'&status=3&order_id='.$row[$key]['order_id'].'">不通过</a>';
        		$row[$key]['status'] = '未审核';
        		break;
        	case 2:
        		$row[$key]['oper'] = '<a href="share.php?act=oper&id='.$row[$key]['id'].'&status=1&order_id='.$row[$key]['order_id'].'">重置</a>';
        		$row[$key]['status'] = '审核已通过';
        		break;
        	case 3:
        		$row[$key]['oper'] = '<a href="share.php?act=oper&id='.$row[$key]['id'].'&status=1&order_id='.$row[$key]['order_id'].'">重置</a>';
        		$row[$key]['status'] = '审核未通过';
        		break;
        }
       
    }
    $arr = array('orders' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}


?>