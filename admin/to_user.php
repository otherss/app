<?php

/**
 * ECSHOP 客户留言
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: user_msg.php 17217 2011-01-19 06:29:08Z liubo $
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
/* 权限判断 */
admin_priv('feedback_priv');
/*初始化数据交换对象 */
$exc = new exchange($ecs->table("to_user"), $db, 'id', 'content');

/*------------------------------------------------------ */
//-- 发送留言
/*------------------------------------------------------ */
if ($_REQUEST['act']=='add')
{
	
   
    /* 获取关于订单所有信息 */
    $sql = "SELECT user_id, user_name  FROM " . $ecs->table('users');
          

    $user_list = $db->getAll($sql);
   

    assign_query_info();
    $smarty->assign('ur_here',     '添加管理员留言');
    $smarty->assign('user_list',     $user_list);
//     $smarty->assign('action_link',  array('text'=>$_LANG['order_detail'], 'href'=>'order.php?act=info&order_id=' . $order_id));
    $smarty->assign('action_link',  array('text' => '管理员留言列表', 'href'=>'to_user.php?act=list_all'));
    $smarty->assign('admin_name',  $_SESSION['admin_name']);
    $smarty->display('to_user_info.htm');
}

if ($_REQUEST['act']=='insert')
{
    $sql = "INSERT INTO " . $ecs->table('to_user') . "(admin_id,user_id,content,add_time)"
            ." VALUES (".$_SESSION['admin_id'].", ".$_POST['user_id'].", '".$_POST['content']. "',".time().")";

   
    $db->query($sql);
	
    $link[] = array('text' => '返回管理员留言列表', 'href' => 'to_user.php?act=list_all');
    sys_msg('发布留言成功！',0, $link);
    
  
    exit;
}




/*------------------------------------------------------ */
//-- 列出所有留言
/*------------------------------------------------------ */
if ($_REQUEST['act']=='list_all')
{
	
    assign_query_info();

    $msg_list = to_user_list();
//     var_dump($msg_list);
    $smarty->assign('action_link',  array('text' => '添加留言', 'href'=>'to_user.php?act=add'));
    $smarty->assign('msg_list',     $msg_list['msg_list']);
    $smarty->assign('filter',       $msg_list['filter']);
    $smarty->assign('record_count', $msg_list['record_count']);
    $smarty->assign('page_count',   $msg_list['page_count']);
    $smarty->assign('full_page',    1);
    $smarty->assign('sort_msg_id', '<img src="images/sort_desc.gif">');

    $smarty->assign('ur_here',      $_LANG['11_to_user']);
    $smarty->assign('full_page',    1);
    $smarty->display('to_user_list.htm');
}

/*------------------------------------------------------ */
//-- ajax显示留言列表
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    $msg_list = to_user_list();

    $smarty->assign('msg_list',     $msg_list['msg_list']);
    $smarty->assign('filter',       $msg_list['filter']);
    $smarty->assign('record_count', $msg_list['record_count']);
    $smarty->assign('page_count',   $msg_list['page_count']);

    $sort_flag  = sort_flag($msg_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('to_user_list.htm'), '', array('filter' => $msg_list['filter'], 'page_count' => $msg_list['page_count']));
}
/*------------------------------------------------------ */
//-- ajax 删除留言
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'remove')
{
    $msg_id = intval($_REQUEST['id']);

    /* 检查权限 */
    check_authz_json('feedback_priv');

   
    if ($exc->drop($msg_id))
    {

        $url = 'to_user.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
        ecs_header("Location: $url\n");
        exit;
    }
    else
    {
        make_json_error($GLOBALS['db']->error());
    }
}

/*------------------------------------------------------ */
//-- 批量操作删除、允许显示、禁止显示用户评论
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'batch')
{
    admin_priv('feedback_priv');
    $action = isset($_POST['sel_action']) ? trim($_POST['sel_action']) : 'def';

    if (isset($_POST['checkboxes']))
    {
        switch ($action)
        {
           case 'remove':
                $db->query("DELETE FROM " . $ecs->table('to_user') . " WHERE " . db_create_in($_POST['checkboxes'], 'id'));
               
                break;
           default :
               break;
        }

        clear_cache_files();
        $action = ($action == 'remove') ? 'remove' : 'edit';
      

        $link[] = array('text' => '返回管理员留言列表', 'href' => 'to_user.php?act=list_all');
        sys_msg(sprintf('移除成功！', count($_POST['checkboxes'])), 0, $link);
    }
    else
    {
        /* 提示信息 */
        $link[] = array('text' => '返回管理员留言列表', 'href' => 'to_user.php?act=list_all');
        sys_msg($_LANG['no_select_comment'], 0, $link);
    }
}


/*------------------------------------------------------ */
//-- 回复留言
/*------------------------------------------------------ */
elseif ($_REQUEST['act']=='view')
{
    $smarty->assign('send_fail',   !empty($_REQUEST['send_ok']));
    $smarty->assign('msg',         get_feedback_detail(intval($_REQUEST['id'])));
    $smarty->assign('ur_here',     $_LANG['reply']);
    $smarty->assign('action_link', array('text' => $_LANG['08_unreply_msg'], 'href'=>'user_msg.php?act=list_all'));

    assign_query_info();
    $smarty->display('msg_info.htm');
}
elseif ($_REQUEST['act']=='action')
{
    if (empty($_REQUEST['parent_id']))
    {
        $sql = "INSERT INTO ".$ecs->table('feedback')." (msg_title, msg_time, user_id, user_name , ".
                    "user_email, parent_id, msg_content) ".
                "VALUES ('reply', '".gmtime()."', '".$_SESSION['admin_id']."', ".
                    "'".$_SESSION['admin_name']."', '".$_POST['user_email']."', ".
                    "'".$_REQUEST['msg_id']."', '".$_POST['msg_content']."') ";
        $db->query($sql);
    }
    else
    {
        $sql = "UPDATE ".$ecs->table('feedback')." SET user_email = '".$_POST['user_email']."', msg_content='".$_POST['msg_content']."', msg_time = '".gmtime()."' WHERE msg_id = '".$_REQUEST['parent_id']."'";
        $db->query($sql);
    }

    /* 邮件通知处理流程 */
    if (!empty($_POST['send_email_notice']) or isset($_POST['remail']))
    {
        //获取邮件中的必要内容
        $sql = 'SELECT user_name, user_email, msg_title, msg_content ' .
               'FROM ' .$ecs->table('feedback') .
               " WHERE msg_id ='$_REQUEST[msg_id]'";
        $message_info = $db->getRow($sql);

        /* 设置留言回复模板所需要的内容信息 */
        $template    = get_mail_template('user_message');
        $message_content = $message_info['msg_title'] . "\r\n" . $message_info['msg_content'];

        $smarty->assign('user_name',   $message_info['user_name']);
        $smarty->assign('message_note', $_POST['msg_content']);
        $smarty->assign('message_content', $message_content);
        $smarty->assign('shop_name',   "<a href='".$ecs->url()."'>" . $_CFG['shop_name'] . '</a>');
        $smarty->assign('send_date',   date('Y-m-d'));

        $content = $smarty->fetch('str:' . $template['template_content']);

        /* 发送邮件 */
        if (send_mail($message_info['user_name'], $message_info['user_email'], $template['template_subject'], $content, $template['is_html']))
        {
            $send_ok = 0;
        }
        else
        {
            $send_ok = 1;
        }
    }

    ecs_header("Location: ?act=view&id=".$_REQUEST['msg_id']."&send_ok=$send_ok\n");
    exit;

}



/**
 *
 *
 * @access  public
 * @param
 *
 * @return void
 */
function to_user_list()
{
    /* 过滤条件 */
    $filter['keywords']   = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
    if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1)
    {
        $filter['keywords'] = json_str_iconv($filter['keywords']);
    }
    
  
    $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'f.id' : trim($_REQUEST['sort_by']);
    $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

    $where = '';
    if ($filter['keywords'])
    {
        $where .= " AND f.content LIKE '%" . mysql_like_quote($filter['keywords']) . "%' ";
    }
   

    $sql = "SELECT count(*) FROM " .$GLOBALS['ecs']->table('to_user'). " AS f" .
           " WHERE 1=1 " . $where ;
    $filter['record_count'] = $GLOBALS['db']->getOne($sql);

    /* 分页大小 */
    $filter = page_and_size($filter);

    $sql = "SELECT f.id, a.user_name as admin_name, f.content,u.user_name,  f.add_time " .
            "FROM " . $GLOBALS['ecs']->table('to_user') . " AS f  ,".
            $GLOBALS['ecs']->table('users') . " AS u , ".
            $GLOBALS['ecs']->table('admin_user') . " AS a  ".
            "WHERE f.user_id = u.user_id and f.admin_id = a.user_id ". $where .
            "ORDER by $filter[sort_by] $filter[sort_order] ".
            "LIMIT " . $filter['start'] . ', ' . $filter['page_size'];

//     echo $sql;
//     exit;
    $msg_list = $GLOBALS['db']->getAll($sql);
   
   
    foreach ($msg_list as $k => $v)
    {
    	
    	$msg_list[$k]['add_time'] = date("Y-m-d H:i:s",$v['add_time']);
    	
    }
    $filter['keywords'] = stripslashes($filter['keywords']);
    $arr = array('msg_list' => $msg_list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}

/**
 * 获得留言的详细信息
 *
 * @param   integer $id
 *
 * @return  array
 */
function get_feedback_detail($id)
{
    global $ecs, $db, $_CFG;

    $sql = "SELECT T1.*, T2.msg_id AS reply_id, T2.user_name  AS reply_name, u.email AS reply_email, ".
                "T2.msg_content AS reply_content , T2.msg_time AS reply_time, T2.user_name AS reply_name ".
            "FROM ".$ecs->table('feedback'). " AS T1 ".
            "LEFT JOIN " .$ecs->table('admin_user'). " AS u ON u.user_id='" .$_SESSION['admin_id']. "' ".
            "LEFT JOIN ".$ecs->table('feedback'). " AS T2 ON T2.parent_id=T1.msg_id ".
            "WHERE T1.msg_id = '$id'";
    $msg = $db->GetRow($sql);

    if ($msg)
    {
        $msg['msg_time']   = local_date($_CFG['time_format'], $msg['msg_time']);
        $msg['reply_time'] = local_date($_CFG['time_format'], $msg['reply_time']);
    }

    return $msg;
}

?>