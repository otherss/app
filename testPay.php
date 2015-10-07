<?php 


// error_log("bbbb",3,'errors.log');
ini_set('display_errors', 1);//设置开启错误提示
// error_reporting('E_ALL & ~E_NOTICE ');//错误等级提示
error_reporting(E_ALL);

echo "aaa";

require(dirname(__FILE__) . '/init.php');

//  \Pingpp\Pingpp::setApiKey('sk_test_ajnPuTOmfr90nPGSSG94KGa1');
 
//  $result = \Pingpp\Charge::create(array(
//  'order_no'  => '123456789',
//  'amount'    => '100',
//  'app'       => array('id' => 'app_yDSanHqDarfP8KSC'),
//  'channel'   => 'upmp',
//  'currency'  => 'cny',
//  'client_ip' => '127.0.0.1',
//  'subject'   => '商品测试',
//  'body'      => '商品描述'
//  		));
 
 
//  echo $result;
 
//  echo phpinfo();
?>