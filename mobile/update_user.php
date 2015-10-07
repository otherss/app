<?php



define('IN_ECS', true);

define('ECS_ADMIN', true);



require(dirname(__FILE__) . '/includes/init.php');


// error_reporting(E_ALL^E_NOTICE);
error_reporting(NULL); ini_set('display_errors','Off');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');

include_once(ROOT_PATH . 'includes/lib_transaction.php');

$birthday = trim($_REQUEST['year']) .'-'. trim($_REQUEST['month']) .'-'.trim($_REQUEST['day']);

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

$profile  = array(

		'user_id'  => $user_id,

		'email'    => isset($_REQUEST['email']) ? trim($_REQUEST['email']) : '',
		
		'realname'    => isset($_REQUEST['realname']) ? trim($_REQUEST['realname']) : '',
		
		'sex'      => isset($_REQUEST['sex'])   ? intval($_REQUEST['sex']) : 0,

		'birthday' => $birthday

	

);



$msg = edit_profile($profile);

if ($msg==1)

{
	$re['error'] = 0;
	
}

else

{
	$re['msg'] = $msg;
}

die($json->encode($re));

?>