<?php
define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');


include('includes/cls_json.php');

$json   = new JSON;



// die($json->encode($res));

$ad = get_advlist_position_name('wap首页banner轮播',0,3);

$region = get_regions_options(3,88);

$data = array('error'=>0,'msg'=>'','data'=>array('ad'=>$ad,'region'=>$region));

// echo $region;

die($json->encode($data));


?>