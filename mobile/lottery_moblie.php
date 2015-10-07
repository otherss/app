<?php
define('IN_ECS', true);

define('ECS_ADMIN', true);

require (dirname ( __FILE__ ) . '/includes/init.php');



if ($_REQUEST ['act'] == 'index') {
	
	$agent = $_SERVER ['HTTP_USER_AGENT'];
	/*
	 * if(!strpos($agent,"MicroMessenger")) { echo '此功能只能在微信浏览器中使用';exit; }
	 */
	
	$wxuser_id = 0;
// 	$wecha_id = $_SESSION['user_name'];
	$wecha_id = $_SESSION['user_name'];
	$id = $_GET ['id'];
	
	
	echo $_SESSION['user_id'];
	if($_SESSION['user_id'] == 0)
	{
		
		$userAgent = $_SERVER['HTTP_USER_AGENT'];
		if(strpos($userAgent,"iPhone") || strpos($userAgent,"iPad") || strpos($userAgent,"iPod") || strpos($userAgent,"iOS")){
			//iPhone
			echo "<script>alert('只有商城会员才能参与抽奖活动，请登录！');window.location.href='user.php' </script>";
		}else if(strpos($userAgent,"Android")){
			//Android
			echo "<script>alert('只有商城会员才能参与抽奖活动，请登录！');window.location.href='user.php'</script>";
		}else{
			//电脑
// 			echo "<script>alert('只有商城会员才能参与抽奖活动，请登录！');window.location.href='user.php'</script>";
// 			exit;
			show_message('只有商城会员才能进行抽奖活动', '马上登录', 'user.php', 'error');
		}
		
		
	}
	
	
	
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
	
	}
	// $redata = M('Lottery_record');
	// $where = array('wxuser_id'=>$wxuser_id,'wecha_id'=>$wecha_id,'lid'=>$id);
	// $record = $redata->where($where)->find();
	$sql = "select * from " . $ecs->table ( 'Lottery_record' ) . "where wxuser_id ='$wxuser_id' and wecha_id='$wecha_id' and lid= $id";
	$record = $db->getRow ( $sql );
	if ($record == Null) {
		// $redata->add($where);
		// $record = $redata->where($where)->find();
		
		$sql = "insert into " . $ecs->table ( 'Lottery_record' ) . " (wxuser_id,wecha_id,lid) values ('$wxuser_id','$wecha_id',$id)";
		$db->query ( $sql );
		
		$sql = "select * from " . $ecs->table ( 'Lottery_record' ) . "where wxuser_id ='$wxuser_id' and wecha_id='$wecha_id' and lid= $id";
		$record = $db->getRow ( $sql );
	}
	
	
// 	echo '<pre>';
	
	// $Lottery = M('Lottery')->where(array('id'=>$id,'wxuser_id'=>$wxuser_id,'type'=>1,'status'=>1))->find();
	
	$sql = "select * from " . $ecs->table ( 'Lottery' ) . "where id=$id and wxuser_id ='$wxuser_id'";
	$Lottery = $db->getRow ( $sql );
	
// 	echo '<pre>';
// 	var_dump($Lottery);
	// 1.活动过期,显示结束
	if(!$Lottery)
	{
		show_message('该活动不存在或未开始', '返回上一页');
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
	// 1. 中过奖金
	if ($record ['islottery'] == 1) {
		$data ['end'] = 5;
		$data ['sn'] = $record ['sn'];
		$data ['uname'] = $record ['wecha_name'];
		$data ['prize'] = $record ['prize'];
		$data ['tel'] = $record ['phone'];
		
		$data ['islottery'] = $record ['islottery'];
	}
	
	$click = $Lottery['click']+1;
	$upsql="update " . $ecs->table ( 'Lottery' ) . " set click='".$click."' where id='".$_GET["id"]."'";
	$db->query ( $upsql );
	
	$jsql="select count(*) from " . $ecs->table ( 'Lottery_record' ) . " where lid='".$_GET["id"]."'";
	$jnum= $db->getOne($jsql );
	// 		$jnum=mysql_num_rows($jquery);
	$jupsql="update " . $ecs->table ( 'Lottery' ) . " set joinnum='".$jnum."' where id='".$_GET["id"]."'";
	$db->query ( $jupsql );
	
// 	echo '<pre>';
// 	var_dump($record);
	$data ['On'] = 1;
	$data ['wxuser_id'] = $wxuser_id;
	$data ['wecha_id'] = $record ['wecha_id'];
	$data ['lid'] = $record ['lid'];
	$data ['rid'] = $record ['id'];
	

	$data ['usenums'] = $record ['usenums'];
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
	// var_dump($data);exit();
// 	echo '<pre>';
// 	var_dump($data);
	$smarty->display ('Lottery_index.html');
} 

elseif ($_REQUEST ['act'] == 'getajax') {
	
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
	$sql = "select * from " . $ecs->table ( 'Lottery' ) . "where id=$id and wxuser_id ='$wxuser_id' and type=4 and status= 1";
	$Lottery = $db->getRow ( $sql );
	
	if ($record ['usenums'] >= $Lottery ['canrqnums']) {
		$norun = 2;
		$usenums = $record ['usenums'];
		$canrqnums = $Lottery ['canrqnums'];
		echo '{
				
				"norun":' . $norun . ',
				"usenums":"' . $usenums . '",
				"canrqnums":"' . $canrqnums . '",
				"id":"' . $id . '",
				"wxuser_id":"' . $wxuser_id . '",
				"type":"' . $type . '",
				"status":"' . $status . '",
				"sql":"'.$sql.'"
			}';
		exit ();
	} else { // 每次请求先增加 使用次数 usenums
	         
		// M('Lottery_record')->where(array('id'=>$rid))->setInc('usenums');
		
		
		
		// $record = M('Lottery_record')->where(array('id'=>$rid))->find();
		$sql = "select * from " . $ecs->table ( 'Lottery_record' ) . "where id=$rid ";
		$record = $db->getRow ( $sql );
		
		$prizetype = get_prize ( $id );
		if ($prizetype >= 1 && $prizetype <= 6) {
			$sn = uniqid ();
			$prize_name=$prizetype."等级";
			$sql = "update " . $ecs->table ( 'Lottery_record' ) . " set usenums=usenums+1,sn='$sn',time='$_SERVER[REQUEST_TIME]',prize='$prize_name',islottery=1  where id=$rid ";
			$db->query ( $sql );
			echo '{"success":1,"sn":"' . $sn . '","prizetype":"' . $prizetype . '","usenums":"' . $record ['usenums'] . '"}';
		} else {
			echo '{"success":0,"prizetype":"","usenums":"' . $record ['usenums'] . '"}';
		}
		exit ();
	}
} 

elseif ($_REQUEST ['act'] == 'add') 

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
				$sql = "update " . $ecs->table ( 'Lottery' ) . " set fistlucknums=fistlucknums+1   where id=$id ";
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
					$sql = "update " . $ecs->table ( 'Lottery' ) . " set secondlucknums=secondlucknums+1   where id=$id ";
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
					$sql = "update " . $ecs->table ( 'Lottery' ) . " set thirdlucknums=thirdlucknums+1   where id=$id ";
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
					$sql = "update " . $ecs->table ( 'Lottery' ) . " set fourlucknums=fourlucknums+1   where id=$id ";
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
					$sql = "update " . $ecs->table ( 'Lottery' ) . " set fivelucknums=fivelucknums+1   where id=$id ";
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
					$sql = "update " . $ecs->table ( 'Lottery' ) . " set sixlucknums=sixlucknums+1   where id=$id ";
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


