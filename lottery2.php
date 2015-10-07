<?php
define ( 'IN_ECS', true );


$userAgent = $_SERVER['HTTP_USER_AGENT'];
if(strpos($userAgent,"iPhone") || strpos($userAgent,"iPad") || strpos($userAgent,"iPod") || strpos($userAgent,"iOS")){
	
	define('ECS_ADMIN', true);
	require (dirname ( __FILE__ ).'/mobile/includes/init.php');
	
}else if(strpos($userAgent,"Android")){
	
	define('ECS_ADMIN', true);
	require (dirname ( __FILE__ ).'/mobile/includes/init.php');
	
}else{
	
	require (dirname ( __FILE__ ) . '/includes/init.php');
}

error_reporting(0);

$action = isset($_REQUEST['act'])?$_REQUEST['act']:'index';

/* 未登录处理 */
if (empty($_SESSION['user_id']))
{
   
            if (!empty($_SERVER['QUERY_STRING']))
            {
                $back_act = 'lottery2.php?' . strip_tags($_SERVER['QUERY_STRING']);
            }
            $action = 'login';
       
}

if ($action == 'login')
{
	
// 	PC登陆
	if(is_PC())
	{
		if (empty($back_act))
		{
			if (empty($back_act) && isset($GLOBALS['_SERVER']['HTTP_REFERER']))
			{
				$back_act = strpos($GLOBALS['_SERVER']['HTTP_REFERER'], 'user.php') ? './index.php' : $GLOBALS['_SERVER']['HTTP_REFERER'];
			}
			else
			{
				$back_act = 'lottery2.php';
			}
		
		}
		
		
		$captcha = intval($_CFG['captcha']);
		if (($captcha & CAPTCHA_LOGIN) && (!($captcha & CAPTCHA_LOGIN_FAIL) || (($captcha & CAPTCHA_LOGIN_FAIL) && $_SESSION['login_fail'] > 2)) && gd_version() > 0)
		{
			$GLOBALS['smarty']->assign('enabled_captcha', 1);
			$GLOBALS['smarty']->assign('rand', mt_rand());
		}
		
		$smarty->assign('back_act', $back_act);
		$smarty->assign('action', $action);
		$smarty->display('user_passport.dwt');
	}
	// 	手机登陆
	else 
	{
		
		$smarty->assign('gourl', "/lottery2.php");
		$smarty->assign('login_faild', 0);
		$smarty->assign('from_lottery', 1);

		$smarty->display('login.dwt');
		exit;
		
	}
	
	
}


if($action == 'info') {

	$smarty->display("lottery_tips.dwt");
}
else if ($action == 'index') {
	
	
	/*
	 * if(!strpos($agent,"MicroMessenger")) { echo '此功能只能在微信浏览器中使用';exit; }
	 */
	
	$wxuser_id = 0;
// 	$wecha_id = $_SESSION['user_name'];
	$wecha_id = $_SESSION['user_name'];
	
	$user_id = $_SESSION['user_id'];
	
	
	$sql="select id from ".$ecs->table("lottery")." where status =  1 limit 1";
	
	$lottery_id = $db->getOne($sql);
	
// 	$smarty->assign('lottery_id', $lottery_id);
	
	$id = $lottery_id;
	
	
	
// 	if(!$_SESSION['user_id'])
// 	{
		
// 		$userAgent = $_SERVER['HTTP_USER_AGENT'];
// 		if(strpos($userAgent,"iPhone") || strpos($userAgent,"iPad") || strpos($userAgent,"iPod") || strpos($userAgent,"iOS")){
// 			//iPhone
// 			echo "<script>alert('只有商城会员才能参与抽奖活动，请登录！');window.location.href='mobile/user.php' </script>";
// 		}else if(strpos($userAgent,"Android")){
// 			//Android
// 			echo "<script>alert('只有商城会员才能参与抽奖活动，请登录！');window.location.href='mobile/user.php'</script>";
// 		}else{
// 			//电脑
// // 			echo "<script>alert('只有商城会员才能参与抽奖活动，请登录！');window.location.href='user.php'</script>";
// // 			exit;
// 			show_message('只有商城会员才能进行抽奖活动', '马上登录', 'user.php', 'error');
// 		}
		
		
// 	}
	
	
	/* 
	if(!$id)
	{
		$userAgent = $_SERVER['HTTP_USER_AGENT'];
		if(strpos($userAgent,"iPhone") || strpos($userAgent,"iPad") || strpos($userAgent,"iPod") || strpos($userAgent,"iOS")){
			//iPhone
			echo "<script>window.location.href='index.php' </script>";
		}else if(strpos($userAgent,"Android")){
			//Android
			echo "<script>window.location.href='index.php'</script>";
		}else{
			//电脑
			show_message('非法操作', '返回上一页');
		}
	
	} */
	
	
	
	
	
	// 	{
	// 1.活动过期,显示结束
	if(!$id)
	{
// 		show_message('该活动不存在或未开始', '返回上一页');
		$data ['end'] = 1;
		$data ['endinfo'] = '该活动不存在或已结束';
		$smarty->assign ( 'Dazpan', $data );
		$smarty->display ('Lottery_index.html');
		exit ();
		
	}
	else 
	{
		$sql = "select * from " . $ecs->table ( 'lottery' ) . " where id=$id and wxuser_id ='$wxuser_id' and status = 1";
		$Lottery = $db->getRow ( $sql );
		
		if(!$Lottery)
		{
			// 		show_message('该活动不存在或未开始', '返回上一页');
			$data ['end'] = 1;
			$data ['endinfo'] = '该活动不存在或已结束';
			$smarty->assign ( 'Dazpan', $data );
			$smarty->display ('Lottery_index.html');
			exit ();
		
		}
		
	}
	
	$sql = "select * from " . $ecs->table ( 'lottery_record' ) . "where wxuser_id ='$wxuser_id' and wecha_id='$wecha_id' and lid= $id";
	$record = $db->getRow ( $sql );
	
	
	
	if ($record == Null) {
		// $redata->add($where);
		// $record = $redata->where($where)->find();
	
		$sql = "insert into " . $ecs->table ( 'lottery_record' ) . " (wxuser_id,wecha_id,lid) values ('$wxuser_id','$wecha_id',$id)";
		$db->query ( $sql );
	
		$sql = "select * from " . $ecs->table ( 'lottery_record' ) . "where wxuser_id ='$wxuser_id' and wecha_id='$wecha_id' and lid= $id";
		$record = $db->getRow ( $sql );
	}
	
	
	
	// 4.显示奖项,说明,时间
	if ($Lottery ['enddate'] < time ()) {
		$data ['end'] = 1;
		$data ['endinfo'] = $Lottery ['endinfo'];
		$smarty->assign ( 'Dazpan', $data );
		$smarty->display ('Lottery_index.html');
		exit ();
	}
	
	if ($Lottery ['status'] == 0) {
		$data ['end'] = 1;
		$data ['endinfo'] = $Lottery ['endinfo'];
		$smarty->assign ( 'Dazpan', $data );
		$smarty->display ('Lottery_index.html');
		exit ();
	}
	
// 	if ($record ['daynums'] >= $Lottery ['daynums']) {
// 		$data ['end'] = 1;
// 		$data ['endinfo'] = '今天的抽奖次数已用完~';
// 		$smarty->assign ( 'Dazpan', $data );
// 		$smarty->display ('Lottery_index.html');
// 		exit ();
// 	}
	
	// 1. 中过奖金
	if ($record ['islottery'] == 1) {
		$data ['end'] = 5;
		$data ['sn'] = $record ['sn'];
		$data ['uname'] = $record ['wecha_name'];
		$data ['prize'] = $record ['prize'];
		$data ['tel'] = $record ['phone'];
		
		$data ['islottery'] = $record ['islottery'];
	}
	
	if($record['daytime'] - strtotime(date("Y-m-d")) < 0 )
	{
		$sql = " update ". $ecs->table ( 'lottery_record' ) . " set daytime = ".strtotime(date("Y-m-d H:i:s"))." ,daynums = 0  where wxuser_id ='$wxuser_id' and wecha_id='$wecha_id' and lid= $id";
		
		$record['daynums'] = 0;
		
		$db->query($sql);
	}
	
// 	echo $record['daytime'] - strtotime(date("Y-m-d"));
	
// 	echo date("Y-m-d");
	
	include_once(ROOT_PATH .'includes/lib_clips.php');
	
	if ($rank = get_rank_info())
	{
		$smarty->assign('rank_name', $rank['rank_name']);
		
		if (!empty($rank['next_rank_name']))
		{
			$smarty->assign('next_rank_name', $rank['next_rank_name']);
			$smarty->assign('next_rank', $rank['next_rank']);
		}
	}
	
	$info = get_user_default($user_id);
	$smarty->assign('info',        get_user_default($user_id));
	
	$can_play = floor($info['pay_points']/LOTTERY_CREDIT);
	$smarty->assign('can_play', $can_play);
// 	var_dump(get_user_default($user_id));
// 	var_dump(get_rank_info());
	$click = $Lottery['click']+1;
	$upsql="update " . $ecs->table ( 'lottery' ) . " set click='".$click."' where id='".$_GET["id"]."'";
	$db->query ( $upsql );
	
	$jsql="select count(*) from " . $ecs->table ( 'lottery_record' ) . " where lid='".$_GET["id"]."'";
	$jnum= $db->getOne($jsql );
	// 		$jnum=mysql_num_rows($jquery);
	$jupsql="update " . $ecs->table ( 'lottery' ) . " set joinnum='".$jnum."' where id='".$_GET["id"]."'";
	$db->query ( $jupsql );
	
// 	echo '<pre>';
// 	var_dump($record);
	$data ['On'] = 1;
	$data ['wxuser_id'] = $wxuser_id;
	$data ['wecha_id'] = $record ['wecha_id'];
	$data ['lid'] = $record ['lid'];
	$data ['rid'] = $record ['id'];
	

	$data ['usenums'] = $record ['usenums'];
	$data ['daynums'] = $record ['daynums'];
	$data ['daycnums'] = $Lottery ['daynums'];
	$data ['canrqnums'] = $Lottery ['canrqnums'];
	$data ['fist'] = $Lottery ['fist'];
	$data ['second'] = $Lottery ['second'];
	$data ['third'] = $Lottery ['third'];
	$data ['four'] = $Lottery ['four'];
	$data ['five'] = $Lottery ['five'];
	$data ['six'] = $Lottery ['six'];
	$data ['fistnums'] = $Lottery ['fistnums'];
	$data ['secondnums'] = $Lottery ['secondnums'];
	$data ['thirdnums'] = $Lottery ['thirdnums'];
	$data ['fournums'] = $Lottery ['fournums'];
	$data ['fivenums'] = $Lottery ['fivenums'];
	$data ['sixnums'] = $Lottery ['sixnums'];
	$data ['info'] = $Lottery ['info'];
	$data ['txt'] = $Lottery ['txt'];
	$data ['sttxt'] = $Lottery ['sttxt'];
	$data ['title'] = $Lottery ['title'];
	$data ['statdate'] = date('Y年m月j日',$Lottery ['statdate']);
	$data ['enddate'] = date('Y年m月j日',$Lottery ['enddate']);
	
	$smarty->assign ( 'Dazpan', $data );
	$smarty->assign ( 'need_credit', LOTTERY_CREDIT );
	// var_dump($data);exit();
// 	echo '<pre>';
// 	var_dump($data);
	$smarty->display ('Lottery_index.html');
} 

elseif ($action == 'getajax') {
	
	
	
	$wxuser_id = 0;
	$wecha_id = $_POST ['oneid'];
	$id = $_POST ['id'];
	$rid = $_POST ['rid'];
	// $redata = M('Lottery_record');
	// $where = array('wxuser_id'=>$wxuser_id,'wecha_id'=>$wecha_id,'lid'=>$id);
	// $record = $redata->where($where)->find();
	
	$sql = "select * from " . $ecs->table ( 'lottery_record' ) . "where wxuser_id ='$wxuser_id' and wecha_id='$wecha_id' and lid= $id";
	
	$record = $db->getRow ( $sql );
	// 1. 中过奖金
	if ($record ['islottery'] == 1) {
		// $norun = 1;
		$sn = $record ['sn'];
		$uname = $record ['wecha_name'];
		$prize = $record ['prize'];
		$tel = $record ['phone'];
		$msg = "尊敬的:<font color='red'>$uname</font>,您已经中过<font color='red'> $prize</font> 了,您的领奖序列号:<font color='red'> $sn </font>请您牢记及尽快与我们联系.";
		echo '{"norun":1,"msg":"' . $msg . '"}';
		exit ();
	}
	// 2. 抽奖次数是否达到
	// $Lottery = M('Lottery')->where(array('id'=>$id,'wxuser_id'=>$wxuser_id,'type'=>1,'status'=>1))->find();
	$sql = "select * from " . $ecs->table ( 'lottery' ) . "where id=$id and wxuser_id ='$wxuser_id'  and status= 1";
	$Lottery = $db->getRow ( $sql );
	
	if ($record ['daynums'] >= $Lottery ['daynums']) {
		$norun = 2;
		$usenums = $record ['usenums'];
		$canrqnums = $Lottery ['daynums']?$Lottery ['daynums']:0;
		echo '{"norun":"' . $norun . '","usenums":"' . $record ['daynums'] . '","canrqnums":"' . $canrqnums . '","id":"' . $id . '","wxuser_id":"' . $wxuser_id . '"}';
		exit ();
	}
	
	if ($record ['usenums'] >= $Lottery ['canrqnums']) {
		$norun = 2;
		$usenums = $record ['usenums'];
		$canrqnums = $Lottery ['canrqnums']?$Lottery ['canrqnums']:0;
		echo '{"norun":"' . $norun . '","usenums":"' . $usenums . '","canrqnums":"' . $canrqnums . '","id":"' . $id . '","wxuser_id":"' . $wxuser_id . '"}';
		exit ();
	} 
	
	$user_id = $_SESSION['user_id'];
	//没有登录
	if(!$user_id)
	{
		echo '{
	
				"norun": 6
			}';
		exit ();
	}
	
	//积风不足
	$credit = LOTTERY_CREDIT;
	
	$sql = "select pay_points from ".$ecs->table("users")." where user_id = ".$user_id;
	
	$remain_credit = $db->getOne($sql);
	
	if($remain_credit < $credit)
	{
		echo '{
	
				"norun": 7,
				"credit":'.$credit.',
				"remain_credit":'.$remain_credit.'
			}';
		exit ();
	}
	//积风足够
	else
	{
		//更改账户积风
		$sql = "UPDATE ".$ecs->table("users")." SET pay_points = pay_points - $credit WHERE user_id =".$user_id;
			
		$db->query($sql);
	
		$credit = 0- $credit;
		//添加账户积风流程记录
		$sql = "INSERT INTO " . $ecs->table("account_log") . "(user_id, change_time, rank_points,pay_points,change_desc, change_type)" . " VALUES ($user_id, '". gmtime() ."',$credit , $credit, '抽奖', '101')";
	
		$db->query($sql);
	 // 每次请求先增加 使用次数 usenums
	         
		// M('Lottery_record')->where(array('id'=>$rid))->setInc('usenums');
		
		$sql = "update   " . $ecs->table ( 'lottery_record' ) . " set usenums = usenums +1 , daynums = daynums+1  where id=$rid ";
		$db->query ( $sql );
		
		// $record = M('Lottery_record')->where(array('id'=>$rid))->find();
		$sql = "select * from " . $ecs->table ( 'lottery_record' ) . "where id=$rid ";
		$record = $db->getRow ( $sql );
		
		$prizetype = get_prize ( $id );
		if ($prizetype >= 1 && $prizetype <= 6) {
			$sn = uniqid ();
			$prize_name=$prizetype."等级";
			$sql = "update " . $ecs->table ( 'lottery_record' ) . " set sn='$sn',time='$_SERVER[REQUEST_TIME]',prize='$prize_name',islottery=1  where id=$rid ";
			$db->query ( $sql );
			echo '{"success":1,"sn":"' . $sn . '","prizetype":"' . $prizetype . '","usenums":"' . $record ['usenums'] . '"}';
		} else {
			echo '{"success":0,"prizetype":"","usenums":"' . $record ['usenums'] . '"}';
		}
		exit ();
	}
} 

elseif ($action == 'add') 

{
	
	if ($_POST ['action'] == 'add') {
		$lid = $_POST ['lid'];
		$wechaid = $_POST ['wechaid'];
		$data ['sn'] = $_POST ['sncode'];
		$data ['phone'] = $_POST ['tel'];
		$data ['prize'] = $_POST ['prizetype'];
		$data ['wecha_name'] = $_POST ['wxname'];
		$data ['time'] = time ();
		$data ['islottery'] = 1;
		
		$time = time ();
		$set = "' phone='" . $_POST ['tel'] .  "',wecha_name='" . $_POST ['wxname'] ;
		// $rollback = M('Lottery_record')->where(array('lid'=> $lid,
		// 'wecha_id'=>$wechaid))->save($data);
		$sql = "update " . $ecs->table ( 'lottery_record' ) . " set  phone='".$_POST[tel]."',wecha_name=' ".$_POST[wxname]."'  where lid=$lid and wecha_id='$wechaid' ";
		
		$rollback = $db->query ( $sql );
		echo '{"success":1,"msg":"恭喜！尊敬的 ' . $data ['wecha_name'] . ',请您保持手机通畅！你的领奖序号:' . $data ['sn'] . '"}';
		exit ();
	}
}


function get_prize($id) {
	// $Lottery = M('Lottery')->where(array('id'=>$id))->find();
	global  $ecs;
	global  $db;
	$sql = "select * from " . $ecs->table ( 'lottery' ) . "where id=$id ";
	
	
	$Lottery = $db->getRow ( $sql );
	
	$firstNum = intval ( $Lottery ['fistnums'] );
	$secondNum = intval ( $Lottery ['secondnums'] );
	$thirdNum = intval ( $Lottery ['thirdnums'] );
	$fourthNum = intval ( $Lottery ['fournums'] );
	$fifthNum = intval ( $Lottery ['fivenums'] );
	$sixthNum = intval ( $Lottery ['sixnums'] );
	$multi = intval ( $Lottery ['canrqnums'] ); // 最多抽奖次数
	$prize_arr = array (
			'0' => array (
					'id' => 1,
					'prize' => '一等奖',
					'v' => $firstNum,
					'start' => 0,
					'end' => $firstNum 
			),
			'1' => array (
					'id' => 2,
					'prize' => '二等奖',
					'v' => $secondNum,
					'start' => $firstNum,
					'end' => $firstNum + $secondNum 
			),
			'2' => array (
					'id' => 3,
					'prize' => '三等奖',
					'v' => $thirdNum,
					'start' => $firstNum + $secondNum,
					'end' => $firstNum + $secondNum + $thirdNum 
			),
			'3' => array (
					'id' => 4,
					'prize' => '四等奖',
					'v' => $fourthNum,
					'start' => $firstNum + $secondNum + $thirdNum,
					'end' => $firstNum + $secondNum + $thirdNum + $fourthNum 
			),
			'4' => array (
					'id' => 5,
					'prize' => '五等奖',
					'v' => $fifthNum,
					'start' => $firstNum + $secondNum + $thirdNum + $fourthNum,
					'end' => $firstNum + $secondNum + $thirdNum + $fourthNum + $fifthNum 
			),
			'5' => array (
					'id' => 6,
					'prize' => '六等奖',
					'v' => $sixthNum,
					'start' => $firstNum + $secondNum + $thirdNum + $fourthNum + $fifthNum,
					'end' => $firstNum + $secondNum + $thirdNum + $fourthNum + $fifthNum + $sixthNum 
			),
			'6' => array (
					'id' => 7,
					'prize' => '谢谢参与',
					'v' => (intval ( $Lottery ['allpeople'] )) * $multi - ($firstNum + $secondNum + $thirdNum + $fourthNum + $fifthNum + $sixthNum),
					'start' => $firstNum + $secondNum + $thirdNum + $fourthNum + $fifthNum + $sixthNum,
					'end' => intval ( $Lottery ['allpeople'] ) * $multi 
			) 
	);
	//
	foreach ( $prize_arr as $key => $val ) {
		$arr [$val ['id']] = $val;
	}
	// -------------------------------
	// 随机抽奖[如果预计活动的人数为1为各个奖项100%中奖]
	// -------------------------------
	if ($Lottery ['allpeople'] == 1) {
		
		if ($Lottery ['fistlucknums'] <= $Lottery ['fistnums']) {
			$prizetype = 1;
		} else {
			$prizetype = 7;
		}
	} else {
		$prizetype = get_rand ( $arr, intval ( $Lottery ['allpeople'] ) * $multi );
	}
	
	// $winprize = $prize_arr[$rid-1]['prize'];
	
	switch ($prizetype) {
		case 1 :
			
			if ($Lottery ['fistlucknums'] >= $Lottery ['fistnums']) {
				$prizetype = '';
				// $winprize = '谢谢参与';
			} else {
				
				$prizetype = 1;
				// M('Lottery')->where(array('id'=>$id))->setInc('fistlucknums');
				$sql = "update " . $ecs->table ( 'lottery' ) . " set fistlucknums=fistlucknums+1   where id=$id ";
				$db->query ( $sql );
			}
			break;
		
		case 2 :
			if ($Lottery ['secondlucknums'] >= $Lottery ['secondnums']) {
				$prizetype = '';
				// $winprize = '谢谢参与';
			} else {
				// 判断是否设置了2等奖&&数量
				if (empty ( $Lottery ['second'] ) && empty ( $Lottery ['secondnums'] )) {
					$prizetype = '';
					// $winprize = '谢谢参与';
				} else { // 输出中了二等奖
					$prizetype = 2;
					// M('Lottery')->where(array('id'=>$id))->setInc('secondlucknums');
					$sql = "update " . $ecs->table ( 'lottery' ) . " set secondlucknums=secondlucknums+1   where id=$id ";
					$db->query ( $sql );
				}
			}
			break;
		
		case 3 :
			if ($Lottery ['thirdlucknums'] >= $Lottery ['thirdnums']) {
				$prizetype = '';
				// $winprize = '谢谢参与';
			} else {
				if (empty ( $Lottery ['third'] ) && empty ( $Lottery ['thirdnums'] )) {
					$prizetype = '';
					// $winprize = '谢谢参与';
				} else {
					$prizetype = 3;
					// M('Lottery')->where(array('id'=>$id))->setInc('thirdlucknums');
					$sql = "update " . $ecs->table ( 'lottery' ) . " set thirdlucknums=thirdlucknums+1   where id=$id ";
					$db->query ( $sql );
				}
			}
			break;
		
		case 4 :
			if ($Lottery ['fourlucknums'] >= $Lottery ['fournums']) {
				$prizetype = '';
				// $winprize = '谢谢参与';
			} else {
				if (empty ( $Lottery ['four'] ) && empty ( $Lottery ['fournums'] )) {
					$prizetype = '';
					// $winprize = '谢谢参与';
				} else {
					$prizetype = 4;
					// M('Lottery')->where(array('id'=>$id))->setInc('fourlucknums');
					$sql = "update " . $ecs->table ( 'lottery' ) . " set fourlucknums=fourlucknums+1   where id=$id ";
					$db->query ( $sql );
				}
			}
			break;
		
		case 5 :
			if ($Lottery ['fivelucknums'] >= $Lottery ['fivenums']) {
				$prizetype = '';
				// $winprize = '谢谢参与';
			} else {
				if (empty ( $Lottery ['five'] ) && empty ( $Lottery ['fivenums'] )) {
					$prizetype = '';
					// $winprize = '谢谢参与';
				} else {
					$prizetype = 5;
					// M('Lottery')->where(array('id'=>$id))->setInc('fivelucknums');
					$sql = "update " . $ecs->table ( 'lottery' ) . " set fivelucknums=fivelucknums+1   where id=$id ";
					$db->query ( $sql );
				}
			}
			break;
		
		case 6 :
			if ($Lottery ['sixlucknums'] >= $Lottery ['sixenums']) {
				$prizetype = '';
				// $winprize = '谢谢参与';
			} else {
				if (empty ( $Lottery ['six'] ) && empty ( $Lottery ['sixnums'] )) {
					$prizetype = '';
					// $winprize = '谢谢参与';
				} else {
					$prizetype = 6;
					// M('Lottery')->where(array('id'=>$id))->setInc('sixlucknums');
					$sql = "update " . $ecs->table ( 'lottery' ) . " set sixlucknums=sixlucknums+1   where id=$id ";
					$db->query ( $sql );
				}
			}
			break;
		
		default :
			$prizetype = '';
			// $winprize = '谢谢参与';
			
			break;
	}
	
	return $prizetype;
}

/*
 * Enter description here... @param unknown_type $proArr @param unknown_type $total 预计参与人数 @return unknown
 */
function get_rand($proArr, $total) {
	$result = 7;
	$randNum = mt_rand ( 1, $total );
	foreach ( $proArr as $k => $v ) {
		
		if ($v ['v'] > 0) { // 奖项存在或者奖项之外
			if ($randNum > $v ['start'] && $randNum <= $v ['end']) {
				$result = $k;
				break;
			}
		}
	}
	return $result;
}

function is_PC()
{
	$userAgent = $_SERVER['HTTP_USER_AGENT'];
	if(strpos($userAgent,"iPhone") || strpos($userAgent,"iPad") || strpos($userAgent,"iPod") || strpos($userAgent,"iOS")){
	
		return false;
	
	}else if(strpos($userAgent,"Android")){
	
		return false;
	
	}else{
	
		return true;
	}
}
?>