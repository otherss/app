<?php 

/**

* ECSHOP 用户中心

* ============================================================================

* * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。

* 网站地址: http://www.ecshop.com；

* ----------------------------------------------------------------------------

* 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和

* 使用；不允许对程序代码以任何形式任何目的的再发布。

* ============================================================================

* $Author: liubo $

* $Id: user.php 16643 2009-09-08 07:02:13Z liubo $

*/



define('IN_ECS', true);

define('ECS_ADMIN', true);

error_reporting(0);

require(dirname(__FILE__) . '/includes/init.php');


include('includes/cls_json.php');


$json   = new JSON;



	$re = array(
			"error"=>1,
			"msg"=>"账号密码错误",
			"data"=>array()
	);

	$user_name = !empty($_REQUEST['username']) ? $_REQUEST['username'] : '';

	$pwd = !empty($_REQUEST['pwd']) ? $_REQUEST['pwd'] : '';

	$gourl = !empty($_REQUEST['gourl']) ? $_REQUEST['gourl'] : '';


	if (!empty($user_name)&&!empty($pwd))
	{

		if ($user->check_user($user_name, $pwd) > 0)
		{
				
			$re['error'] = 0;
				
			$user->set_session($user_name);

			$user->set_cookie($user_name);
				
// 			$re['data']['username'] = $_REQUEST ['username'];
// 			$re['data']['user_id'] = $_SESSION ['user_id'] ;
			
			$re['data'] = user_info( $_SESSION ['user_id']);
			$birthday = explode("-", $re['data']['birthday']);
			$re['data']['user_key'] = $_SESSION ['user_id'].rand(100, 999);
			$re['data']['year'] = $birthday[0];
			$re['data']['month'] = $birthday[1];
			$re['data']['day'] = $birthday[2];
			update_user_info($re['data']['user_key']);

			if($gourl){

				$re['data']['gourl'] = $gourl;

			}

		}

	}

	die($json->encode($re));


?>