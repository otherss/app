<?php



define('IN_ECS', true);

define('ECS_ADMIN', true);



require(dirname(__FILE__) . '/includes/init.php');


// error_reporting(E_ALL^E_NOTICE);
// error_reporting(NULL); ini_set('display_errors','Off');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');

include_once(ROOT_PATH . 'includes/lib_transaction.php');

$user_id =  isset($_REQUEST['user_id'])   ? intval($_REQUEST['user_id']) : 0;

$address_id =  isset($_REQUEST['address_id'])   ? intval($_REQUEST['address_id']) : 0;

$user_key = isset($_REQUEST['user_key']) ? trim($_REQUEST['user_key']) : '';


$act = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : '';

$user_name = $user->check_user_app($user_id, $user_key);

if ($user_name!=-1)
{

	$user->set_session($user_name);

	$user->set_cookie($user_name);
	
}

include('includes/cls_json.php');

$json   = new JSON;

$re = array(
		"error"=>1,
		"msg"=>"操作失败",
		"data"=>array()
);


if($_SESSION['user_id']>0)
{
	$user_id = $_SESSION['user_id'];
}
else 
{
	$re['msg'] = "请先登录";
	die($json->encode($re));
}




if ($act == 'edit_address')

{

	include_once('includes/lib_transaction.php');

	$consignee = get_consignee_one($_SESSION['user_id'],$address_id);

	$district_list = get_regions(3, 88);

	$re['error'] = 0;

	$re['data']['district_list'] = $district_list;

	
	foreach ($district_list as $k => $v)
	{
		if($consignee['district'] ==$v['region_id'])
		{
			$consignee['district_name'] = $v['region_name'];
		}
	}

	

	$re['data']['consignee'] = $consignee;

}

else if ($act == 'get_default_address')

{
	
	include_once('includes/lib_transaction.php');
	
	
	$address_id = $_REQUEST['id']?intval($_REQUEST['id']):0;
	
	$consignee = get_default_consignee($_SESSION['user_id'],$address_id);
	
	if(!$consignee)
	{
		$re['error'] = 2;
		
	}
	else 
	{
		$district_list = get_regions(3, 88);
		
		$re['error'] = 0;
		
		foreach ($district_list as $k => $v)
		{
			if($consignee['district'] ==$v['region_id'])
			{
				$consignee['district_name'] = $v['region_name'];
			}
		}
		
		$re['data']['consignee'] = $consignee;
	}
	
	

}
else if ($act == 'address_list')

{

	include_once('includes/lib_transaction.php');

	$consignee_list = get_consignee_list($_SESSION['user_id']);
	
	$district_list = get_regions(3, 88);
	
	$re['error'] = 0;
	
	$re['data']['district_list'] = $district_list;

	foreach ($consignee_list as $region_id => $consignee)
	{
		foreach ($district_list as $k => $v)
		{
				if($consignee['district'] ==$v['region_id'])
				{
					$consignee_list[$region_id]['district_name'] = $v['region_name'];
				}
		}

	}
	
	$re['data']['consignee_list'] = $consignee_list;

}

/*增加收获地址*/

elseif ($act == 'add_edit_address'){



	global $db;

	include_once('includes/lib_transaction.php');

	$gourl = empty($_REQUEST['gourl'])  ? '' : trim($_REQUEST['gourl']);

	if(empty($_REQUEST['district']))
	{
		
		$re['msg'] = "配送片区不能为空。";
		die($json->encode($re));
	}

	elseif(empty($_REQUEST['address']))

	{
		$re['msg'] = "收货地址不可为空。";
		die($json->encode($re));

	}

	elseif(empty($_REQUEST['consignee']))

	{
		$re['msg'] = "收货人姓名不可为空。";
		die($json->encode($re));

	}

	elseif(empty($_REQUEST['tel']))

	{
		$re['msg'] = "联系电话不可为空。";
		die($json->encode($re));

	}

	
	$consignee = array(

			'user_id'		=> $_SESSION['user_id'],

			'address_id'    => empty($_REQUEST['address_id']) ? 0  : intval($_REQUEST['address_id']),

			'consignee'     => empty($_REQUEST['consignee'])  ? '' : trim($_REQUEST['consignee']),

			'country'       => 1,

			'province'      => 6,

			'city'          => 88,

			'district'      => empty($_REQUEST['district'])   ? '' : $_REQUEST['district'],

			'address'       => empty($_REQUEST['address'])    ? '' : $_REQUEST['address'],

			'tel'           => empty($_REQUEST['tel'])        ? '' : make_semiangle(trim($_REQUEST['tel']))

	);

	
	
	$result = update_address($consignee);

	if($result){

		$re['error'] = 0;
		$re['msg'] = "保存收货地址成功。";
		$re['data']['address_id'] =$result;
	}

	else{
		
		$re['error'] = 1;
		$re['msg'] = "保存收货地址失败。";
		

	}
	
	

}

/* 删除收货人信息*/

elseif ($act == 'drop_address')

{

	include_once('includes/lib_transaction.php');



	$consignee_id = intval($_REQUEST['id']);



	if (drop_consignee($consignee_id))

	{
		$re['error'] = 0;
		$re['msg'] ="删除收货地址成功。";

	}
	else
	{
		$re['msg'] ="删除收货地址失败。";
	}

}


elseif ($act == 'default_address')

{

	include_once('includes/lib_transaction.php');



	$consignee_id = intval($_REQUEST['id']);



	if (default_consignee($consignee_id))

	{
		$re['error'] = 0;
		$re['msg'] ="默认收货地址设置成功。";

	}
	else
	{
		$re['msg'] ="默认收货地址设置失败。";
	}

}


/* 添加收藏商品(ajax) */

elseif ($act == 'collect')

{

	

	$goods_id = $_GET['id'];


		/* 检查是否已经存在于用户的收藏夹 */

		$sql = "SELECT COUNT(*) FROM " .$GLOBALS['ecs']->table('collect_goods') .

		" WHERE user_id='$_SESSION[user_id]' AND goods_id = '$goods_id'";

		if ($GLOBALS['db']->GetOne($sql) > 0)

		{

			$re['error'] = 0;

			$re['msg'] = "该商品已移出你的收藏夹。";
		
			$db->query('DELETE FROM ' .$ecs->table('collect_goods'). " WHERE goods_id='$goods_id' AND user_id ='$user_id'" );
			
			$re['data']['type'] = "delete";
			

		}

		else

		{

			$time = gmtime();

			$sql = "INSERT INTO " .$GLOBALS['ecs']->table('collect_goods'). " (user_id, goods_id, add_time)" .

					"VALUES ('$_SESSION[user_id]', '$goods_id', '$time')";

			$re['data']['type'] = "insert";

			if ($GLOBALS['db']->query($sql) === false)

			{

				$re['error'] = 1;

				$re['msg'] = $GLOBALS['db']->errorMsg();

				

			}

			else

			{

				$re['error'] = 0;

				$re['msg'] = "该商品已经成功地加入了您的收藏夹。";

			

			}

		}

	

}


elseif ($act == 'collect_list')

{


	/* 检查是否已经存在于用户的收藏夹 */

	$sql = "SELECT p1.rec_id , p2.goods_name , p2.shop_price , p2.goods_id , p2.original_img FROM " .$GLOBALS['ecs']->table('collect_goods') ." as p1".
	" left join ".$GLOBALS['ecs']->table('goods') ." as p2 on p1.goods_id = p2.goods_id ".
	" WHERE user_id='$_SESSION[user_id]' ";
	
	$collection = $GLOBALS['db']->GetAll($sql); 
	$re['error'] = 0;
	$re['data']['collection'] = $collection;

}



elseif ($act == 'drop_collection')

{


	$collection_id = intval($_REQUEST['id']);



	if (drop_collection($collection_id))

	{
		$re['error'] = 0;
		$re['msg'] ="删除收藏成功。";

	}
	else
	{
		$re['msg'] ="删除收藏失败。";
	}

}

die($json->encode($re));

?>