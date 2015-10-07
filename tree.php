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
	if (isset($_REQUEST['cat_id']))
	{
	    $parent = intval($_REQUEST['cat_id']);
	}
	else 
	{
		$parent = 0;
	}
	
	$cat_list = get_categories_tree(0);
    
    include('includes/cls_json.php');
    
    $json   = new JSON;
    
    $count = count($cat_list);
    
    $res    = array('err_msg' => 'ok', 'data' => $cat_list,'count'=>$count);
    
    die($json->encode($res));
    
 

?>
