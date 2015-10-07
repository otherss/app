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

/* 获得请求的分类 ID */
if (isset($_REQUEST['id']))
{
    $cat_id = intval($_REQUEST['id']);
}
elseif (isset($_REQUEST['category']))
{
    $cat_id = intval($_REQUEST['category']);
}



$size = 4;

 $page = 1;

	
//     $children = get_children(1);

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
    $order = "g.sort_order";
    
//     $goodslist = array();
    
//     $goodslist = category_get_goods(get_children(1), $brand, $price_min, $price_max, $ext, $size, $page, $sort, $order);

    
    include('includes/cls_json.php');
    
    $json   = new JSON;
    
//     $count = count($goodslist);
    
    $list = array();
    
    $list[] = get_category_recommend_goods('hot', $children, $brand, $price_min, $price_max, 1,4);
    $list[] = category_get_goods(get_children(1), $brand, $price_min, $price_max, $ext, $size, $page, $sort, $order,"",1);
    $list[] = category_get_goods(get_children(31), $brand, $price_min, $price_max, $ext, $size, $page, $sort, $order,"",31);
    $list[] = category_get_goods(get_children(33), $brand, $price_min, $price_max, $ext, $size, $page, $sort, $order,"",33);
    $list[] = category_get_goods(get_children(34), $brand, $price_min, $price_max, $ext, $size, $page, $sort, $order,"",34);
    
    $res    = array('err_msg' => 'ok', 'data' => $list);
    
    die($json->encode($res));
    


?>
