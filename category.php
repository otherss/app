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

/*------------------------------------------------------ */
//-- INPUT
/*------------------------------------------------------ */
$cat_id = 0;
/* 获得请求的分类 ID */
if (isset($_REQUEST['cat_id']))
{
    $cat_id = intval($_REQUEST['cat_id']);
}
elseif (isset($_REQUEST['category']))
{
    $cat_id = intval($_REQUEST['category']);
}

// $cat_list = "";
// /* 获得请求的分类 ID */
// if (isset($_REQUEST['cat_list']))
// {
// 	$cat_list = intval($_REQUEST['cat_list']);
// }

// else
// {
//     /* 如果分类ID为0，则返回首页 */
//     ecs_header("Location: ./\n");

//     exit;
// }


/* 初始化分页信息 */
$page = isset($_REQUEST['page'])   && intval($_REQUEST['page'])  > 0 ? intval($_REQUEST['page'])  : 1;


$size = 6;


$price_max = isset($_REQUEST['price_max']) && intval($_REQUEST['price_max']) > 0 ? intval($_REQUEST['price_max']) : 0;
$price_min = isset($_REQUEST['price_min']) && intval($_REQUEST['price_min']) > 0 ? intval($_REQUEST['price_min']) : 0;

$price  = !empty($_REQUEST['price']) ? $_REQUEST['price'] : '';
$cat_list  = !empty($_REQUEST['cat_list']) ? $_REQUEST['cat_list'] : '';
$keyword  = !empty($_REQUEST['keyword']) ? $_REQUEST['keyword'] : '';

$get_sort  = !empty($_REQUEST['sort']) ? $_REQUEST['sort'] : 'goods_id';

if ($get_sort == 'shop_price')

{

	$order_rule = ' g.shop_price DESC, g.sort_order';

}
else
{

	$order_rule = '  g.sort_order DESC , g.last_update DESC ';

}

    /* 如果页面没有被缓存则重新获取页面的内容 */

	if($cat_list!='')
	{
		$children = get_children_by_list($cat_list);
		
		$cat_id = 100;
		
// 		echo $children;
	}
	elseif($cat_id>0)
	{
		
	    $children = get_children($cat_id);
	}

//     $cat = get_cat_info($cat_id);   // 获得分类的相关信息

//     if (empty($cat))
//     {
//        echo "empty";
//        exit;
//     }
  


  

//     $smarty->assign('categories',       get_categories_tree($cat_id)); // 分类树
 
//     $smarty->assign('top_goods',        get_top10());                  // 销售排行
 
//     $smarty->assign('promotion_info', get_promotion_info());



//     $smarty->assign('best_goods',      get_category_recommend_goods('best', $children, $brand, $price_min, $price_max, $ext));
//     $smarty->assign('promotion_goods', get_category_recommend_goods('promote', $children, $brand, $price_min, $price_max, $ext));
//     $smarty->assign('hot_goods',       get_category_recommend_goods('hot', $children, $brand, $price_min, $price_max, $ext));

//     $count = get_cagtegory_goods_count($children, $brand, $price_min, $price_max, $ext);
//     $max_page = ($count> 0) ? ceil($count / $size) : 1;
//     if ($page > $max_page)
//     {
//         $page = $max_page;
//     }
  
    $goodslist = array();
    
    $ext = price_range($price);
    
    $goodslist = category_get_goods($children, $brand, $price_min, $price_max, $ext, $size, $page, $order_rule,'',$keyword,$cat_id);

    
    include('includes/cls_json.php');
    
    $json   = new JSON;
    
    $count = count($goodslist);
    
    $res    = array('err_msg' => 'ok', 'data' => $goodslist,'count'=>$count);
    
    die($json->encode($res));
    


?>
