<?php

/**
 * ECSHOP 商品分类管理程序
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

//我的抽奖
if($_REQUEST['act'] == 'index')
{
// 	$wx_id=$_GET["wx_id"]?intval($_GET["wx_id"]):'';
// 	$type=$_GET["type"]?intval($_GET["type"]):'';
	
	
	$sql="SELECT id,title,status,createtime,keyword FROM".$ecs->table('lottery') ."where 1";
	$lottery=$db->getAll($sql);
	foreach($lottery as $k=>$v)
	{
		 
	
		$lottery[$k]['createtime']=date('Y-m-d ',$lottery[$k]['createtime']);
		$sql="SELECT count(*) FROM".$ecs->table('lottery_record')."WHERE lid=".$v[id]." AND islottery=1";
		$lottery[$k]['lucknums']=$db->getOne($sql);
	}
	$smarty->assign('action_link',  array("href"=>"lottery.php?act=add_lottery","text"=>"添加新的活动"));
// 	$smarty->assign('action_link',  array("href"=>"lottery.php?act=add_lottery","text"=>"添加新的活动"));
	$smarty->assign('lottery',  $lottery);
// 	$smarty->assign('wx_id',  $wx_id);
// 	$smarty->assign('type',  $type);
	$smarty->display('lottery_list.htm');
}

else if($_REQUEST['act'] == 'edit_lottery')
{
	$lottery_id=$_REQUEST["id"];
// 	$wx_id=$_GET["wx_id"]?intval($_GET["wx_id"]):'';
// 	$type=$_GET["type"]?intval($_GET["type"]):'';
	$sql="SELECT * FROM".$ecs->table('lottery')."WHERE id=".$lottery_id;
	$lottery_detail=$db->getAll($sql);
	$lottery_detail=$lottery_detail[0];
// 	if($type=='4')
// 	{
// 		$title='幸福大转盘';
// 	}
// 	else if($type=='5')
// 	{
// 		$title='优惠券';
// 	}
// 	else if($type=='6')
// 	{
// 		$title='刮刮乐';
// 	}
// 	else
// 	{
// 		die('参数错误');
// 	}
	$title='幸福大转盘';
	$smarty->assign('title', $title);
// 	$smarty->assign('type', $type);
	$lottery_detail["statdate"]=date("Y-m-d",$lottery_detail["statdate"]);
	$lottery_detail["enddate"]=date("Y-m-d",$lottery_detail["enddate"]);
	$smarty->assign('action_link', array('text' => '返回前一页', 'href' => 'lottery.php?act=index'));
	$smarty->assign('lottery_detail',  $lottery_detail);

// 	$wx_id=$_REQUEST["wx_id"];


	
	$smarty->assign('flag', 'edit_lottery_detail');
// 	$smarty->assign('wx_id', $wx_id);
	$smarty->display('lottery_detail.htm');

}
else if($_REQUEST['act'] == 'add_lottery')
{

// 	$wx_id=$_GET["wx_id"]?intval($_GET["wx_id"]):'';
// 	$type=$_GET["type"]?intval($_GET["type"]):'';
// if($type=='4')
// {
// 	$title='幸福大转盘';
// }
// else if($type=='5')
// {
// 	$title='优惠券';
// }
// else if($type=='6')
// {
// 	$title='刮刮乐';
// }
// else
// {
// 	die('参数错误');
// }
	$title='幸福大转盘';
	$smarty->assign('action_link', array('text' => '返回前一页', 'href' => 'lottery.php?act=index'));
// $smarty->assign('type', $type);
    $smarty->assign('title', $title);
	$smarty->assign('flag', 'add_lottery_detail');
// 	$smarty->assign('wx_id', $wx_id);
	$smarty->display('lottery_detail.htm');
}
else if($_REQUEST['act'] == 'del_lottery')
{
	$lottery_id=$_REQUEST["id"];
	$sql="DELETE FROM".$ecs->table('lottery')."WHERE id=".$lottery_id;

	if(!$db->query($sql))
	{
		echo "error";
	}
	else 
	{
		echo "success";
	}
	
	exit;
}
else if ($_REQUEST['act'] == 'send_price')
{
	$result=array('error'=>1,'content'=>'');
	$id=$_POST['id']?$_POST['id']:'';
	$sql = "UPDATE " . $ecs->table('lottery_record') . " SET sendstutas ='1',sendtime=".$_SERVER['REQUEST_TIME']." WHERE id ='$id'";
	$res= $db->query($sql);
	if($res)
	{
		$result['error']=0;
		die(json_encode($result));
	}

}
elseif($_REQUEST['act'] == 'lottery_record')
{
	
// 	$type=$_GET['type']?$_GET['type']:'';
	$lid=$_GET['lid'];
	$url='lottery.php?act=send_price';
	$smarty->assign('action_link', array('text' => '返回前一页', 'href' => 'lottery.php?act=index'));
	$page_size='10';
	$page=$_GET['page']?$_GET['page']:'';
	if($page <= 1 || $page == '') $page = 1;
	$sql="SELECT count(*) FROM".$ecs->table('lottery_record')."WHERE islottery=1 AND lid='$lid' ";
	$record_nums=$db->getOne($sql);
	$page_count = ceil($record_nums/$page_size); 
	$select_limit = $page_size;
	$select_from = ($page - 1) * $page_size.',';
	$pre_page = ($page == 1)? 1 : $page - 1;
	$next_page= ($page == $page_count)? $page_count : $page + 1 ;
	$smarty->assign('total_nums',  $record_nums);
	$uri = 'lottery.php?act=lottery_record&lid='.$lid;
	$smarty->assign('first_page',  $uri.'&page=1');
	$smarty->assign('pre_page', $uri.'&page='. $pre_page);
	$smarty->assign('next_page',  $uri.'&page='.$next_page);
	$smarty->assign('last_page',  $uri.'&page='.$page_count);
	$smarty->assign('cur_page',  $page);
	$smarty->assign('total_page',  $page_count);
	//中奖记录
	$sql="SELECT id,wecha_name,sn,time,sendtime,prize,phone,sendstutas,wecha_id FROM".$ecs->table('lottery_record')."WHERE islottery=1 AND lid='$lid' limit $select_from $select_limit ";
	$luckyrecord=$db->getAll($sql);
	foreach($luckyrecord as $k=>$v)
	{
			
	
		$luckyrecord[$k]['time']=date('Y-m-d ',$luckyrecord[$k]['time']);
		$luckyrecord[$k]['sendtime']=date('Y-m-d ',$luckyrecord[$k]['sendtime']);
			
	}
	$smarty->assign('send_url',  $url);
	$smarty->assign('luckyrecord',  $luckyrecord);
	$smarty->display('lottery_record.htm');
	
}
elseif($_REQUEST['act'] == 'edit_lottery_detail')
{
// 	$msg_id=$_REQUEST["id"];
// 	$wxuser_id=$_POST["wx_id"]?intval($_POST["wx_id"]):'';
	$statdate=strtotime($_REQUEST["statdate"]);
	$enddate=strtotime($_REQUEST["enddate"]);
// 	$sql = "SELECT keyword FROM".$ecs->table('lottery')."WHERE id = '$msg_id'";
// 	$old_keyword=$db->getOne($sql);
	
	
// 	if($old_keyword!=$_POST['keyword'])
// 	{
// 		$sql = "SELECT count(*) FROM".$ecs->table('lottery')."WHERE keyword = '$_POST[keyword]' AND wxuser_id=$wxuser_id";
// 		$check_key=$db->getOne($sql);
// 		if($check_key>0)
// 		{
// 			sys_msg('已存在相同的关键词', 1);
// 		}
// 		else
// 		{
// 			$sql = "UPDATE " . $ecs->table('keys') . " SET keyword ='$_POST[keyword]' WHERE type=1 AND msg_id ='$msg_id'";
// 			$res= $db->query($sql);
// 		}
// 	}
	
	$data=" daynums = ".$_REQUEST["daynums"]." ,keyword='".$_REQUEST["keyword"]."', title='".$_REQUEST["title"]."', txt='".$_REQUEST["txt"]."', sttxt='".$_REQUEST["sttxt"]."', allpeople=".$_REQUEST["allpeople"];
	$data.=", statdate=".$statdate.", enddate=".$enddate.", info='".$_REQUEST["info"]."', aginfo='".$_REQUEST["aginfo"]."', canrqnums=".$_REQUEST["canrqnums"];
	$data.=", endtite='".$_REQUEST["endtitle"]."', endinfo='".$_REQUEST["endinfo"]."', fist='".$_REQUEST["fist"]."', fistnums=".$_REQUEST["fistnums"];
	$data.=", second='".$_REQUEST["second"]."', secondnums=".$_REQUEST["secondnums"].", third='".$_REQUEST["third"]."', thirdnums=".$_REQUEST["thirdnums"].",status=".$_REQUEST["status"];
	$sql="update ".$ecs->table("lottery")." set ".$data." where id=".$_REQUEST["id"];
	
	$link[0]['text'] = '返回';
	$link[0]['href'] = "lottery.php?act=index";
	
	if($_REQUEST["status"] == 1)
	{
		$sql2="update ".$ecs->table("lottery")." set status = 0 ";
		
		if(!$db->query($sql2))
		{
			// 		$db->query($sql2);
			sys_msg('操作失败',0, $link);
		}
	}
	else 
	{
		$sql2="select count(*) from ".$ecs->table("lottery")." where status =  1 and  id != ".$_REQUEST["id"];
		
		
		if(!$db->getOne($sql2))
		{
	// 		$db->query($sql2);
			sys_msg('请至少保证一个活动处于开启状态',0, $link);
		}
	}
// 	$sql2="update ".$ecs->table("keys")."set keyword='".$_REQUEST["keyword"]."' where msg_id=".$_REQUEST["id"];
	// 	if(!$db->query($sql))
		// 	{
		// 		echo "error";
		// 	}
		// 	else
			// 	{
			// 		echo "success";
			// 	}

			// 	exit;

	clear_cache_files(); // 清除相关的缓存文件
	
	if($db->query($sql))
	{
// 		$db->query($sql2);
		sys_msg('更新活动成功',0, $link);
	}
	else
	{
		sys_msg('更新活动失败',1, $link);
	}

}
else if($_REQUEST['act'] == 'add_lottery_detail')
{
	$wxuser_id=$_POST['wx_id']?intval($_POST['wx_id']):'';
// 	$type=$_POST['type']?intval($_POST['type']):'';
	$statdate=strtotime($_REQUEST["statdate"]);
	$enddate=strtotime($_REQUEST["enddate"]);
// 	$sql = "SELECT count(*) FROM".$ecs->table('keys')."WHERE keyword = '$_POST[keyword]' AND wxuser_id=$wxuser_id";
// 	$check_key=$db->getOne($sql);
// 	if($check_key>0)
// 	{
// 		sys_msg('已存在相同的关键词', 1);
// 	}
	$field="(createtime,wxuser_id,keyword,title,txt,sttxt,statdate,enddate,info,aginfo,endtite,endinfo,fist,fistnums,second,secondnums,third,thirdnums,allpeople,canrqnums,daynums,status,type)";
	$val="('".$_SERVER['REQUEST_TIME']."','".$wxuser_id."','".$_REQUEST["keyword"]."','".$_REQUEST["title"]."','".$_REQUEST["txt"]."','".$_REQUEST["sttxt"]."','".$statdate."','".$enddate."','".$_REQUEST["info"]."','".$_REQUEST["aginfo"];
	$val.= "','".$_REQUEST["endtitle"]."','".$_REQUEST["endinfo"]."','".$_REQUEST["fist"]."','".$_REQUEST["fistnums"]."','".$_REQUEST["second"]."','".$_REQUEST["secondnums"]."','".$_REQUEST["third"]."','".$_REQUEST["thirdnums"]."','".$_REQUEST["allpeople"]."','".$_REQUEST["canrqnums"]."','".$_REQUEST["daynums"]."','".$_REQUEST["status"]."',0)";
	$sql="INSERT INTO ".$ecs->table("lottery").$field."VALUES".$val;
	
	// 	if(!$db->query($sql))
		// 	{
		//  		echo "error";
		// 	}
		// 	else
			// 	{
			// 		echo "success";
			// 	}
			// 	exit;
			
	if($_REQUEST["status"] == 1)
	{
		$sql2="update ".$ecs->table("lottery")." set status = 0 ";
	
		if(!$db->query($sql2))
		{
			// 		$db->query($sql2);
			sys_msg('操作失败',0, $link);
		}
	}
	else
	{
		$sql2="select count(*) from ".$ecs->table("lottery")." where status =  1 ";
	
	
		if(!$db->getOne($sql2))
		{
			// 		$db->query($sql2);
			sys_msg('请至少保证一个活动处于开启状态',0, $link);
		}
	}
	
	
	clear_cache_files(); // 清除相关的缓存文件
	$link[0]['text'] = '返回';
	$link[0]['href'] = 'lottery.php?act=index&type='.$type.'&wx_id='.$wxuser_id;
	
	if($db->query($sql))
	{
// 		$id= $db->insert_id();
// 		$sql2="INSERT INTO ".$ecs->table("keys")."(msg_id,keyword,wxuser_id,type)  VALUES (".$id.",'".$_REQUEST["keyword"]."',".$wxuser_id.",'$type')";
		
// 		$db->query($sql2);
		sys_msg('添加活动成功',0, $link);
	}
	else
	{
		sys_msg('添加活动失败',1, $link);
	}




}
?>