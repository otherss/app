<?php



define('IN_ECS', true);

define('ECS_ADMIN', true);



require(dirname(__FILE__) . '/includes/init.php');


// error_reporting(E_ALL^E_NOTICE);
error_reporting(NULL); ini_set('display_errors','Off');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');

include_once(ROOT_PATH . 'includes/lib_transaction.php');

include_once(ROOT_PATH . 'includes/lib_passport.php');

$user_id =  isset($_REQUEST['user_id'])   ? intval($_REQUEST['user_id']) : 0;

$user_key = isset($_REQUEST['user_key']) ? trim($_REQUEST['user_key']) : '';


$user_name = $user->check_user_app($user_id, $user_key);

if ($user_name!=-1)
{

	$re['error'] = 0;

	$user->set_session($user_name);

	$user->set_cookie($user_name);
	
}

include('includes/cls_json.php');

$json   = new JSON;

$re = array(
		"error"=>1,
		"msg"=>"信息填写有误",
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

	



    $old_password = isset($_REQUEST['old']) ? trim($_REQUEST['old']) : null;

    $new_password = isset($_REQUEST['password']) ? trim($_REQUEST['password']) : '';

    $repassword = isset($_REQUEST['repassword']) ? trim($_REQUEST['repassword']) : '';

  

    $code         = isset($_REQUEST['code']) ? trim($_REQUEST['code'])  : '';


    if (strlen($new_password) ==0 || strlen($repassword) ==0 )
    
    {
    
    	$re['msg'] = "密码不能为空";
    	die($json->encode($re));
    
    }
    elseif (strlen($new_password) < 6 || strlen($repassword) < 6 )

    {

   $re['msg'] = "密码长度不能小于6个字符";
   die($json->encode($re));

    } elseif (md5($new_password)<>md5($repassword)){

     $re['msg'] = "确认密码输入有误";
     die($json->encode($re));

	}



    $user_info = $user->get_profile_by_id($user_id); //论坛记录



    if ($user_info &&  $_SESSION['user_id']>0 && $_SESSION['user_id'] == $user_id && $user->check_user($_SESSION['user_name'], $old_password))

    {

		

        if ($user->edit_user(array('username'=> (empty($code) ? $_SESSION['user_name'] : $user_info['user_name']), 'old_password'=>$old_password, 'password'=>$new_password), empty($code) ? 0 : 1))

        {

			$sql="UPDATE ".$ecs->table('users'). "SET `ec_salt`='0' WHERE user_id= '".$user_id."'";

			$db->query($sql);

            $user->logout();
			
            $re['error'] = 0;
            
// 			$user->logout();

// 			$Loaction = 'user.php';

// 			ecs_header("Location: $Loaction\n");

        }

        else

        {

            $re['msg'] = "密码输入有误";

        }

    }

    else

    {

        $re['msg'] = "旧密码输入有误";

    }

	








die($json->encode($re));

?>