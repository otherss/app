<?php

/**
 * ECSHOP 商品详情
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: goods.php 17217 2011-01-19 06:29:08Z liubo $
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}


$affiliate = unserialize($GLOBALS['_CFG']['affiliate']);
$smarty->assign('affiliate', $affiliate);

/*------------------------------------------------------ */
//-- INPUT
/*------------------------------------------------------ */

$share_id = isset($_REQUEST['id'])  ? intval($_REQUEST['id']) : 0;






/*------------------------------------------------------ */
//-- PROCESSOR
/*------------------------------------------------------ */

$cache_id = $share_id . '-' . $_SESSION['user_rank'].'-'.$_CFG['lang'];
$cache_id = sprintf('%X', crc32($cache_id));
if (!$smarty->is_cached('share_info.dwt', $cache_id))
{
    
    $smarty->assign('helps',        get_shop_help()); // 网店帮助
    
    

    
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
       
        $share_list = get_user_share_list($share['uid'],5,$share_id);
        
        $smarty->assign('share_list',   $share_list);
        
        
        $total = get_share_count(0,$share['uid']);
        
        $smarty->assign('share_count',   $total['count']);
        $smarty->assign('total',   $total['total']);
        
        
        
        $position = assign_ur_here($goods['cat_id'], $goods['goods_name']);

        /* current position */
        $smarty->assign('page_title',          $position['title']);                    // 页面标题
        $smarty->assign('ur_here',             $position['ur_here']);                  // 当前位置

        $smarty->assign('id',           $share_id);
        $smarty->assign('type',         0);
        $smarty->assign('foucs',         $_REQUEST['flag']);
       
    }
}




$smarty->assign('navigator_list', get_navigator());

$smarty->assign('now_time',  gmtime());           // 当前系统时间
$smarty->display('share_info.dwt',      $cache_id);

/*------------------------------------------------------ */
//-- PRIVATE FUNCTION
/*------------------------------------------------------ */

function get_share_info($id)
{
	
	if(!$id)
	{
		return false;
	}
	
	$sql = 'SELECT * FROM '.$GLOBALS['ecs']->table('share').' WHERE status > 0 and id ='.$id;
	
	$res = $GLOBALS['db']->getRow($sql);
	
	
		$res['add_time'] = date('Y-m-d H:i:s',$res['add_time']);
		
	
	
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

	$where = 'WHERE 1 AND status = 2';
	
	
	if ($gid!=0)
	{
		$where .= " AND gid = $gid";
	}
	if ($uid!=0)
	{
		$where .= " AND uid = $uid";
	}
	
	$sql = 'SELECT COUNT(*) as count ,sum(integrate) as total FROM ' . $GLOBALS['ecs']->table('share') . $where;
	
	
	return $GLOBALS['db']->getRow($sql);
}

?>