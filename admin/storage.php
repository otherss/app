<?php 
/**
 * ECSHOP 仓库管理程序
 * ============================================================================
 * * 版权所有 2013-2014 广州维赛网络科技有限公司，并保留所有权利。
 * 网站地址: http://wwww.weisai.net；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: hungry $
 * $Id: storage.php 17217 2014 hungry$
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
//仓库列表

if ($_REQUEST['act'] == 'list')
{
	
	
	$pzd_list = get_storage_list();  
    
$smarty->assign('pzd_list',  $pzd_list['pzd_list']);  
$smarty->assign('filter',       $pzd_list['filter']);  
$smarty->assign('record_count', $pzd_list['record_count']);  
$smarty->assign('page_count',   $pzd_list['page_count']);  
	
	$action_link = array('href' => 'storage.php?act=add', 'text' => '添加仓库');
	$smarty->assign('action_link',  $action_link);
	
	$smarty->assign('full_page',    1);
	$smarty->display('storage_list.htm');
}
elseif ($_REQUEST['act'] == 'query'){//分页代码
//获取信息列表
$pzd_list = get_storage_list();
$smarty->assign('pzd_list',  $pzd_list['pzd_list']);  
$smarty->assign('filter',       $pzd_list['filter']);  
$smarty->assign('record_count', $pzd_list['record_count']);  
$smarty->assign('page_count',   $pzd_list['page_count']);  

//跳转页面
make_json_result($smarty->fetch('storage_list.htm'), '',
array('filter' => $pzd_list['filter'], 'page_count' => $pzd_list['page_count']));
}
elseif ($_REQUEST['act'] == 'add')
{
	$smarty->assign('countries',        get_regions());
	$smarty->assign('full_page',    1);
	$smarty->assign('action',        'insert');
	$smarty->display('storage_info.htm');
}
elseif ($_REQUEST['act'] == 'edit')
{
	$id=isset($_GET['id'])?intval($_GET['id']):0;
	$sql="SELECT * FROM ".$ecs->table('storage')." WHERE id='".$id."'";
	$row = $db->getRow($sql);
	
	if($row['regions']!="")
	{
		$regions_arr = '('.$row['regions'].')';
		$sql = "SELECT region_id, region_name ".
				"FROM ".$ecs->table('region'). " WHERE  region_id in $regions_arr";
		$res = $db->query($sql);
		while ($arr = $db->fetchRow($res))
		{
			$regions[$arr['region_id']] = $arr['region_name'];
		}
	}
	else 
	{
		$regions = array();
	}
	
	
	
	
	$smarty->assign('action',        'update');
	$smarty->assign('sid',    $id);
	$smarty->assign('store',    $row);
	$smarty->assign('regions',          $regions);
	$smarty->assign('countries',        get_regions());
	$smarty->assign('full_page',    1);

	$smarty->display('storage_info.htm');
}
elseif ($_REQUEST['act'] == 'insert')
{
	
	$store_name=isset($_POST['store_name'])?$_POST['store_name']:'';
	$store_desc=isset($_POST['store_desc'])?$_POST['store_desc']:'';
	$store_status=isset($_POST['store_status'])?$_POST['store_status']:'';
	
	$regions=isset($_POST['regions'])?implode(',',$_POST['regions']):array();
	if($store_name==='')
	{
		sys_msg('仓库名称不能为空', 1);
	}
	
	$sql="INSERT INTO ".$ecs->table('storage').
	"(store_name,store_desc,store_status,regions) VALUES('$store_name','$store_desc','$store_status','$regions')";
	$res=$db->query($sql);
	
	$id=$db->insert_id();
	
	$sql = "  select *  FROM " . $GLOBALS['ecs']->table('goods');
		$res = $GLOBALS['db']->getAll($sql);
	
		foreach ($res as $k => $v)
			{
				$ss = " insert into ". $GLOBALS['ecs']->table('storage_goods')." (storage_id, goods_id, number) values ( ".$id.",".$v['goods_id'].",0 )";
				$GLOBALS['db']->query($ss);
			}
		
		$lnk[] = array('text' => $_LANG['back_list'], 'href'=>'storage.php?act=list');
		$lnk[] = array('text' => $_LANG['add_continue'], 'href'=>'storage.php?act=add');
		sys_msg('添加仓库成功', 1, $lnk);
       
}
elseif($_REQUEST['act'] == 'del')
{
	$id=isset($_GET['id'])?intval($_GET['id']):0;
	$smarty->assign('full_page',    1);
   $sql="DELETE FROM ".$ecs->table('storage')." WHERE id='".$id."'";
   $res=$db->query($sql);
   
   
   $lnk[] = array('text' => $_LANG['back_list'], 'href'=>'storage.php?act=list');
  
   sys_msg('删除仓库成功', 1, $lnk);
   
}
elseif($_REQUEST['act'] == 'empty')
{
	
	$id=isset($_GET['id'])?intval($_GET['id']):0;
	$smarty->assign('full_page',    1);
	
	$sql="SELECT goods_num,goods_id FROM ".$ecs->table('goods_store')." WHERE id='".$id."'";
	$good=$db->getRow($sql);

	
	$sql="DELETE FROM ".$ecs->table('goods_store')." WHERE id='".$id."'";
	$res=$db->query($sql);
	 //更新商品总数
	$sql="UPDATE ".$ecs->table('goods')."SET goods_number=goods_number-".$good['goods_num']." WHERE goods_id='".$good['goods_id']."'";
	$res=$db->query($sql);
	$lnk[] = array('text' => $_LANG['back_list'], 'href'=>'goods.php?act=list');

	sys_msg('清空仓库成功', 1, $lnk);
	 
}
elseif($_REQUEST['act'] == 'update')
{
	$id=isset($_POST['id'])?intval($_POST['id']):0;
	$store_name=isset($_POST['store_name'])?$_POST['store_name']:'';
	$store_desc=isset($_POST['store_desc'])?$_POST['store_desc']:'';
	$store_status=isset($_POST['store_status'])?$_POST['store_status']:'';
	
	$regions=isset($_POST['regions'])?implode(',',$_POST['regions']):array();
	
	if($store_name==='')
	{
		sys_msg('仓库名称不能为空', 1);
	}
	
	$sql='UPDATE '.$ecs->table('storage')."SET store_name='$store_name',store_desc='$store_desc',store_status='$store_status',regions='$regions' WHERE id='".$id."'";
	
	
	$res=$db->query($sql);
	
	
	$lnk[] = array('text' => '返回', 'href'=>'storage.php?act=list');
	
	sys_msg('修改仓库成功', 1, $lnk);
}
elseif($_REQUEST['act'] == 'goods')
{
	$id=isset($_GET['goods_id'])?intval($_GET['goods_id']):0;
	
	//添加商品
	if($_POST['flag']=='add_goods')
	{
		$num=isset($_POST['goods_num'])?intval($_POST['goods_num']):0;
		$store=isset($_POST['storage_list'])?intval($_POST['storage_list']):0;
		if($store==0)
		{
			sys_msg('请选择仓库');
		}
		
		if($num==0)
		{
			sys_msg('请填写数量');
		}
		$sql="SELECT id FROM ".$ecs->table('goods_store')." WHERE store_id='".$store."' AND goods_id='".$id."'";
		$res=intval($db->getOne($sql));
		
		if($res>0)
		{
			$sql="UPDATE ".$ecs->table('goods_store')."SET goods_num='$num' WHERE id='".$res."'";
		   
			
		}
		else 
		{
			$sql="INSERT INTO ".$ecs->table('goods_store').
			"(store_id,goods_id,goods_num) VALUES('$store','$id','$num')";
		}
			
	    $res=$db->query($sql);
		//更新商品总数
	    $sql="SELECT goods_num FROM ".$ecs->table('goods_store')." WHERE goods_id='".$id."'";
	    $total_temp=$db->getAll($sql);
	    $total_num=0;
	   
	    foreach($total_temp as $k=>$v)
	    {
	    	$total_num+=$v['goods_num'];
	    }
	   
	    if($total_num>0)
	    {
	    	$sql="UPDATE ".$ecs->table('goods')."SET goods_number='$total_num' WHERE goods_id='".$id."'";
	    	$res=$db->query($sql);
	    }
	}
	//获取商品货仓列表
	$sql="SELECT b.store_name,a.goods_num,a.id FROM ".$ecs->table('goods_store')." as a,".$ecs->table('storage')." as b WHERE a.store_id=b.id AND a.goods_id='".$id."'";
	$store_list=$db->getAll($sql);
	$smarty->assign('store_list', $store_list);
	/* 多货仓--hungry*/
	$storagelist=storage_list();
	$smarty->assign('storagelist', $storagelist);
	$smarty->assign('full_page',    1);
	$smarty->display('storage_goods.htm');
}
/**
 * 获取仓库列表--hungry
 *
 * @param
 *
 * @return array
 */
function storage_list()
{

	/* 获活动数据 */
	$sql = "SELECT store_status,store_name,id FROM " . $GLOBALS['ecs']->table('storage');

	$row = $GLOBALS['db']->getAll($sql);

	return $row;
}
//获取列表
function get_storage_list()
{
	$sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('storage');
	$filter['record_count'] = $GLOBALS['db']->getOne($sql);
	$filter = page_and_size($filter);
	/* 获活动数据 */
	$sql = "SELECT store_status,store_name,id FROM " . $GLOBALS['ecs']->table('storage')." LIMIT ". $filter['start'] .", " . $filter['page_size'];
	$filter['keywords'] = stripslashes($filter['keywords']);
	set_filter($filter, $sql);
	$row = $GLOBALS['db']->getAll($sql);
	$arr = array("pzd_list" => $row, "filter" => $filter, "page_count" => $filter['page_count'], "record_count" => $filter['record_count']);
			return $arr;
}