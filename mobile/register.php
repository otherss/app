<?php


define('IN_ECS', true);

define('ECS_ADMIN', true);



require(dirname(__FILE__) . '/includes/init.php');


// error_reporting(E_ALL^E_NOTICE);
error_reporting(NULL); ini_set('display_errors','Off');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');

include_once(ROOT_PATH . 'includes/lib_transaction.php');

include_once(ROOT_PATH . 'includes/lib_passport.php');


include('includes/cls_json.php');

$json   = new JSON;

$re = array(
		"error"=>1,
		"msg"=>"注册失败",
		"data"=>array()
);

$username = isset($_REQUEST['username']) ? trim($_REQUEST['username']) : '';

$password = isset($_REQUEST['password']) ? trim($_REQUEST['password']) : '';

$repassword = isset($_REQUEST['repassword']) ? trim($_REQUEST['repassword']) : '';

if($password!=$repassword)
{
	$re['msg'] = "密码输入有误";
	
	die($json->encode($re));
}

$email	= isset($_REQUEST['email']) ? trim($_REQUEST['email']) : '';



$other['home_phone'] = isset($_POST['extend_field4']) ? $_POST['extend_field4'] : '';

$msg = m_register($username, $password, $email, $other);

$re['msg'] = $msg;

if ($msg ==1)

{
	
	$re['error'] = 0;
	/*把新注册用户的扩展信息插入数据库*/

	$sql = 'SELECT id FROM ' . $ecs->table('reg_fields') . ' WHERE type = 0 AND display = 1 ORDER BY dis_order, id';   //读出所有自定义扩展字段的id

	$fields_arr = $db->getAll($sql);



	$extend_field_str = '';	//生成扩展字段的内容字符串

	foreach ($fields_arr AS $val)

	{

		$extend_field_index = 'extend_field' . $val['id'];

		if(!empty($_POST[$extend_field_index]))

		{

			$temp_field_content = strlen($_POST[$extend_field_index]) > 100 ? mb_substr($_POST[$extend_field_index], 0, 99) : $_POST[$extend_field_index];

			$extend_field_str .= " ('" . $_SESSION['user_id'] . "', '" . $val['id'] . "', '" . compile_str($temp_field_content) . "'),";

		}

	}

	$extend_field_str = substr($extend_field_str, 0, -1);



	if ($extend_field_str)	  //插入注册扩展数据

	{

		$sql = 'INSERT INTO '. $ecs->table('reg_extend_info') . ' (`user_id`, `reg_field_id`, `content`) VALUES' . $extend_field_str;

		$db->query($sql);

	}



	/* 写入密码提示问题和答案 */

	if (!empty($passwd_answer) && !empty($sel_question))

	{

		$sql = 'UPDATE ' . $ecs->table('users') . " SET `passwd_question`='$sel_question', `passwd_answer`='$passwd_answer'  WHERE `user_id`='" . $_SESSION['user_id'] . "'";

		$db->query($sql);

	}



// 	$ucdata = empty($user->ucdata)? "" : $user->ucdata;

// 	$Loaction = 'index.php';

// 	ecs_header("Location: $Loaction\n");

}


die($json->encode($re));





function m_register($username, $password, $email, $other = array())

{

	/* 检查username */

	if (empty($username))

	{

		// 		echo '<script>alert("用户名必须填写！");window.location.href="user.php?act=register"; </script>';

		return "用户名必须填写！";

	}

	if (preg_match('/\'\/^\\s*$|^c:\\\\con\\\\con$|[%,\\*\\"\\s\\t\\<\\>\\&\'\\\\]/', $username))

	{

		// 		echo '<script>alert("用户名错误！");window.location.href="user.php?act=register"; </script>';

		return "用户名格式错误！";

	}



	/* 检查是否和管理员重名 */

	if (admin_registered($username))

	{

		// 		echo '<script>alert("此用户已存在！");window.location.href="user.php?act=register"; </script>';

		return "此用户已存在！";

	}



	if (!$GLOBALS['user']->add_user($username, $password, $email))

	{
		$msg = "注册失败!";
		if($GLOBALS['user']->error ==2 )
		{
			$msg = "该邮箱已被注册!";
		}
		// 		echo '<script>alert("'.$msg.'！");window.location.href="user.php?act=register"; </script>';

		//注册失败

		return $msg;

	}

	else

	{

		//注册成功



		/* 设置成登录状态 */

		$GLOBALS['user']->set_session($username);

		$GLOBALS['user']->set_cookie($username);

	}



	//定义other合法的变量数组

	$other_key_array = array('msn', 'qq', 'office_phone', 'home_phone', 'mobile_phone');

	$update_data['reg_time'] = local_strtotime(local_date('Y-m-d H:i:s'));

	if ($other)

	{

		foreach ($other as $key=>$val)

		{

			//删除非法key值

			if (!in_array($key, $other_key_array))

			{

				unset($other[$key]);

			}

			else

			{

				$other[$key] =  htmlspecialchars(trim($val)); //防止用户输入javascript代码

			}

		}

		$update_data = array_merge($update_data, $other);

	}

	$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('users'), $update_data, 'UPDATE', 'user_id = ' . $_SESSION['user_id']);



	update_user_info();	  // 更新用户信息

	//         $Loaction = 'user.php?act=user_center';

	//         ecs_header("Location: $Loaction\n");

	return 1;



}
?>