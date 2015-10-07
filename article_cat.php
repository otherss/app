<?php

/**
 * ECSHOP 文章分类
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: article_cat.php 17217 2011-01-19 06:29:08Z liubo $
*/


define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}

/* 清除缓存 */
clear_cache_files();

/*------------------------------------------------------ */
//-- INPUT
/*------------------------------------------------------ */

/* 获得指定的分类ID */
if (!empty($_GET['id']))
{
    $cat_id = intval($_GET['id']);
}
else
{
    ecs_header("Location: ./\n");

    exit;
}


/* 获得页面的缓存ID */
$cache_id = sprintf('%X', crc32($cat_id . '-' . $page . '-' . $_CFG['lang']));

if (!$smarty->is_cached('article_cat.dwt', $cache_id))
{
    /* 如果页面没有被缓存则重新获得页面的内容 */

    
    $article_children = get_article_children($cat_id); 
  
	
    $sql = "select article_id , title , content from ".$ecs->table("article")." as c where    ".$article_children." order by  c.cat_id ,c.sort_order, c.article_id limit 1 ";
    
    $article = $db->getRow($sql);
    
//     $article = get_article_info(38);
    
    $smarty->assign('id',               $article['article_id']);
    
    $smarty->assign('article',      $article);
    
    $smarty->assign('artciles_list',    get_cat_articles($cat_id, 1, 30 ,$keywords));
	
    if($cat_id == 14)
    {
    	$smarty->assign('title',      "帮助中心");
    }
    else if($cat_id == 3)
    {
    	$smarty->assign('title',      "才运兼收");
    }
   
}


$smarty->display('article_cat.dwt', $cache_id);


/**
 * 获得指定的文章的详细信息
 *
 * @access  private
 * @param   integer     $article_id
 * @return  array
 */
function get_article_info($article_id)
{
	/* 获得文章的信息 */
	$sql = "SELECT a.*, IFNULL(AVG(r.comment_rank), 0) AS comment_rank ".
			"FROM " .$GLOBALS['ecs']->table('article'). " AS a ".
			"LEFT JOIN " .$GLOBALS['ecs']->table('comment'). " AS r ON r.id_value = a.article_id AND comment_type = 1 ".
			"WHERE a.is_open = 1 AND a.article_id = '$article_id' GROUP BY a.article_id";
	$row = $GLOBALS['db']->getRow($sql);

	if ($row !== false)
	{
		$row['comment_rank'] = ceil($row['comment_rank']);                              // 用户评论级别取整
		$row['add_time']     = local_date($GLOBALS['_CFG']['date_format'], $row['add_time']); // 修正添加时间显示

		/* 作者信息如果为空，则用网站名称替换 */
		if (empty($row['author']) || $row['author'] == '_SHOPHELP')
		{
			$row['author'] = $GLOBALS['_CFG']['shop_name'];
		}
	}

	return $row;
}
?>