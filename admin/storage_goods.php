<?php

/**
 * ECSHOP 商品管理程序
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
require_once(ROOT_PATH . '/' . ADMIN_PATH . '/includes/lib_goods.php');
include_once(ROOT_PATH . '/includes/cls_image.php');
$image = new cls_image($_CFG['bgcolor']);
$exc = new exchange($ecs->table('storage_goods'), $db, 'id',"");

/*------------------------------------------------------ */
//-- 商品列表，商品回收站
/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'list' || $_REQUEST['act'] == 'trash')
{
    admin_priv('goods_manage');

    $cat_id = empty($_REQUEST['cat_id']) ? 0 : intval($_REQUEST['cat_id']);
    $goods_id = empty($_REQUEST['goods_id']) ? 0 : intval($_REQUEST['goods_id']);
    $storage_id = empty($_REQUEST['storage_id']) ? 0 : intval($_REQUEST['storage_id']);
    /* 模板赋值 */
//     $goods_ur = array('' => $_LANG['01_goods_list'], 'virtual_card'=>$_LANG['50_virtual_card_list']);
//     $ur_here = ($_REQUEST['act'] == 'list') ? $goods_ur[$code] : $_LANG['11_goods_trash'];
    $smarty->assign('ur_here', $ur_here);
    $smarty->assign('storage_id', $storage_id);
  
//     $smarty->assign('cat_list',     cat_list(0, $cat_id));
//     $smarty->assign('brand_list',   get_brand_list());
//     $smarty->assign('intro_list',   get_intro_list());
    $smarty->assign('lang',         $_LANG);
    $smarty->assign('list_type',    $_REQUEST['act'] == 'list' ? 'goods' : 'trash');
  

    $smarty->assign('cat_list',     cat_list(0, $cat_id));
    $goods_list = storage_goods_list2($_REQUEST['act'] == 'list' ? 0 : 1, ($_REQUEST['act'] == 'list') ? (($code == '') ? 1 : 0) : -1);
    $smarty->assign('goods_list',   $goods_list['goods']);
    $smarty->assign('filter',       $goods_list['filter']);
    $smarty->assign('record_count', $goods_list['record_count']);
    $smarty->assign('page_count',   $goods_list['page_count']);
    $smarty->assign('full_page',    1);
	
    /* 排序标记 */
    $sort_flag  = sort_flag($goods_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);
    $smarty->assign("storage_list", storage_list());
    $smarty->assign('storage_goods_list',     storage_goods_list());
    
 
    /* 显示商品列表页面 */
    assign_query_info();
   
    $smarty->display("storage_goods_list.htm");
}


elseif ($_REQUEST['act'] == 'query')
{
	$is_delete = empty($_REQUEST['is_delete']) ? 0 : intval($_REQUEST['is_delete']);
	
	$cat_id = empty($_REQUEST['cat_id']) ? 0 : intval($_REQUEST['cat_id']);
	$goods_id = empty($_REQUEST['goods_id']) ? 0 : intval($_REQUEST['goods_id']);
	$storage_id = empty($_REQUEST['storage_id']) ? 0 : intval($_REQUEST['storage_id']);
	
	$goods_list = storage_goods_list2($is_delete, ($code=='') ? 1 : 0);


	$smarty->assign('goods_list',   $goods_list['goods']);
	$smarty->assign('filter',       $goods_list['filter']);
	
	$smarty->assign('record_count', $goods_list['record_count']);
	$smarty->assign('page_count',   $goods_list['page_count']);
	
	

	/* 排序标记 */
	$sort_flag  = sort_flag($goods_list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);

	/* 获取商品类型存在规格的类型 */
	

	$tpl = 'storage_goods_list.htm';

	make_json_result($smarty->fetch($tpl), '',
	array('filter' => $goods_list['filter'], 'page_count' => $goods_list['page_count'],'select'=>storage_goods_list()));
}


/*------------------------------------------------------ */
//-- 修改商品排序
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_add_number')
{
	check_authz_json('goods_manage');

	$id       = intval($_POST['id']);
	$number    = intval($_POST['val']);

	if ($exc->edit("number =number+ '$number'", $id))
	{
		clear_cache_files();
		make_json_result($number,"进库成功",array("type"=>"add","id"=>$id));
	}
}

elseif ($_REQUEST['act'] == 'edit_cut_number')
{
	check_authz_json('goods_manage');

	$id       = intval($_POST['id']);
	$number    = intval($_POST['val']);
	
	$sql = "SELECT number FROM " .$GLOBALS['ecs']->table('storage_goods')
			
					."WHERE id = ".$id;
	
	$old = $GLOBALS['db']->getOne($sql);
	
	if($old<$number)
	{
		clear_cache_files();
	
		make_json_response(0,"","大于现有库存量",array("type"=>"cut","id"=>$id));
	}
	
	elseif ($exc->edit("number =number- '$number'", $id))
	{
		clear_cache_files();
		make_json_result($number,"出库成功",array("type"=>"cut","id"=>$id));
	}
}

function update_goods_stock($goods_id, $value)
{
    if ($goods_id)
    {
        /* $res = $goods_number - $old_product_number + $product_number; */
        $sql = "UPDATE " . $GLOBALS['ecs']->table('goods') . "
                SET goods_number = goods_number + $value,
                    last_update = '". gmtime() ."'
                WHERE goods_id = '$goods_id'";
        $result = $GLOBALS['db']->query($sql);

        /* 清除缓存 */
        clear_cache_files();

        return $result;
    }
    else
    {
        return false;
    }
}


function storage_list()
{
    $type = 'storage_list';
    
    
    
    $where.= get_admin_storage($type);
    
	/* 获活动数据 */
	$sql = "SELECT store_status,store_name,id FROM " . $GLOBALS['ecs']->table('storage')." where store_status = 1 ".$where;

	$res = $GLOBALS['db']->getAll($sql);
	
	$storage_list = array();
	foreach ($res AS $row)
	{
		$storage_list[$row['id']] = addslashes($row['store_name']);
	}
	
	return $storage_list;
	

}


function storage_goods_list()
{
	$cat_id           = empty($_REQUEST['cat_id']) ? 0 : intval($_REQUEST['cat_id']);
	
	$where = " where g.is_delete = 0 and g.is_on_sale =1 ";
	
	$where.= $cat_id> 0 ? " AND " . get_children($cat_id) : '';
	
	
	
	/* 获活动数据 */
	$sql = "SELECT goods_id,goods_name FROM " . $GLOBALS['ecs']->table('goods')." as g ".$where;
	
	
	
	$res = $GLOBALS['db']->getAll($sql);

	
	$selected = $_REQUEST['goods_id']>0?$_REQUEST['goods_id']:0;
		
		$select = '<option value=-1 selected="ture">';
		$select .= htmlspecialchars("请选择商品", ENT_QUOTES) . '</option>';
		foreach ($res AS $var)
		{
			$select .= '<option value="' . $var['goods_id'] . '" ';
			$select .= ($selected == $var['goods_id']) ? "selected='ture'" : '';
			$select .= '>';
		
			$select .= htmlspecialchars(addslashes($var['goods_name']), ENT_QUOTES) . '</option>';
		}
		
		return $select;
	


}



/**
 * 获得商品列表
 *
 * @access  public
 * @params  integer $isdelete
 * @params  integer $real_goods
 * @params  integer $conditions
 * @return  array
 */
function storage_goods_list2($is_delete, $real_goods=1, $conditions = '')
{
	/* 过滤条件 */
	$param_str = '-' . $is_delete . '-' . $real_goods;
	$result = get_filter($param_str);
	if ($result === false)
	{
// 		$day = getdate();
// 		$today = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);
        
		$filter['cat_id']           = empty($_REQUEST['cat_id']) ? 0 : intval($_REQUEST['cat_id']);
		$filter['goods_id']           = empty($_REQUEST['goods_id']) ? 0 : intval($_REQUEST['goods_id']);
		$filter['storage_id']           = empty($_REQUEST['storage_id']) ? 0 : intval($_REQUEST['storage_id']);
		$filter['keyword']          = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
		
		if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1)
		{
			$filter['keyword'] = json_str_iconv($filter['keyword']);
		}
		$filter['sort_by']          = empty($_REQUEST['sort_by']) ? 'goods_id' : trim($_REQUEST['sort_by']);
		$filter['sort_order']       = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		

		$where = $filter['cat_id'] > 0 ? " AND " . get_children($filter['cat_id']) : '';

	
		/* 库存警告 */
	

		/* 关键字 */
		if (!empty($filter['keyword']))
		{
// 			$where .= " AND (g.goods_sn LIKE '%" . mysql_like_quote($filter['keyword']) . "%' OR g.goods_name LIKE '%" . mysql_like_quote($filter['keyword']) . "%')";
			$where .= " AND ( g.goods_name LIKE '%" . mysql_like_quote($filter['keyword']) . "%')";
		}

		if ($filter['goods_id'] > 0)
		{
			$where .= " AND sg.goods_id=".$filter['goods_id'];
		}
		
		if ($filter['storage_id'] > 0)
		{
			$where .= " AND sg.storage_id=".$filter['storage_id'];
		}
		
	
	
		$where .= " AND (g.is_on_sale = 1)";
		
		$type = 'storage_goods_list';
		
		$where.= get_admin_storage($type);
	

		$where .= $conditions;

		/* 记录总数 */
		$sql = "SELECT COUNT(*) FROM " .$GLOBALS['ecs']->table('storage_goods'). " AS sg ,"
					.$GLOBALS['ecs']->table('goods'). " AS g ,"
					.$GLOBALS['ecs']->table('storage'). " AS s "		
					."WHERE s.store_status = 1 AND sg.goods_id = g.goods_id AND s.id = sg.storage_id   AND  g.is_delete='$is_delete' $where";
		//echo $sql;
		
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);

		/* 分页大小 */
		$filter = page_and_size($filter);
		
		$sql = "SELECT sg.id , s.store_name, g.goods_id, g.goods_name , sg.number " .
				"FROM " .$GLOBALS['ecs']->table('storage_goods'). " AS sg ,".
				$GLOBALS['ecs']->table('storage'). " AS s ,".
				 $GLOBALS['ecs']->table('goods') . " AS g ".
				"WHERE sg.goods_id = g.goods_id AND  sg.storage_id = s.id  AND  s.store_status = 1 AND "
				."g.is_delete='$is_delete' $where" .
				" ORDER BY $filter[sort_by] $filter[sort_order] ".
				" LIMIT " . $filter['start'] . ",$filter[page_size]";
		
		$filter['keyword'] = stripslashes($filter['keyword']);
		set_filter($filter, $sql, $param_str);
	}
	else
	{
		$sql    = $result['sql'];
		$filter = $result['filter'];
	}
	
// 	$sql = "  select *  FROM " . $GLOBALS['ecs']->table('goods');
// 	$res = $GLOBALS['db']->getAll($sql);
	
// 	foreach ($res as $k => $v)
// 	{
// 		$ss = " insert into ". $GLOBALS['ecs']->table('storage_goods')." (storage_id, goods_id, number) values ( 1,".$v['goods_id'].",100 )";
// 		$GLOBALS['db']->query($ss);
// 	}
	
	
	$row = $GLOBALS['db']->getAll($sql);

	
	
	return array('goods' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

?>