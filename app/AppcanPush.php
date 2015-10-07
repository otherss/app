<?php
/*
create by Roy
QQ:381281
有需要制作APP与插件的，可以与我QQ联系。
嗯，我只是工作之余挣点外快~
*/
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

	header("Content-Type: text/html; charset=utf-8");
	session_start();
	$appcan_cookies = $_SESSION["appcan_cookies"];
	$appcan_username = "lit520@qq.com";	//推送平台的账号。
	$appcan_password = "116232";		//推送平台的密码。
	$appcan_appId = "11420244";			//应用的ID号。
	$appcan_token = "2cbe8c22-1b2b-a136-0f3b-447eec1f0017";			//应用的ID号。

	
// 	LoginAppcan();
	
// 	echo "aaa".$appcan_cookies;
// 	exit;

	if($_GET["op"] === "GetAndroidUserList"){//GetAndroidUserList;
		echo GetAndroidUserList();
	}
	else if($_GET["op"] === "GetIosUserList"){//GetIosUserList;
		echo GetIosUserList();
	}
	else if($_GET["op"] === "pushAll"){//GetIosUserList;	//最后一个参数为空是JSON字符串，我没写。
		echo pushAllDevice($_POST["type"],$_POST["title"],$_POST["content"],"''");
	}
	else if($_GET["op"] === "pushSingel"){//GetIosUserList;	//最后一个参数为空是JSON字符串，我没写。
		echo pushSingelDevice($_POST["type"],$_POST["title"],$_POST["content"],$_POST["token"],"''");
	}
	exit();






	/**
	 * 获取安卓在线用户列表。
	 */
	function LoginAppcan(){
		global $appcan_cookies ;
		
		if($appcan_cookies){
			$loginTime = $_SESSION["login_time"];
			if(ceil((time()-$loginTime))<=600){
				return;
			}
		}
		
		global $appcan_username;	//推送平台的账号。
		global $appcan_password;		//推送平台的密码。
		global $appcan_appId ;			//应用的ID号。
		$postData = array();
		$postData["service"] = "http://newpush.appcan.cn/msg/listUserPage1?platform=1&appId=".$appcan_appId."&search=&pageNo=&pageSize=&next=&pre=";
		$postData["errorUrl"] ="http://newpush.appcan.cn/msg/listUserPage1?platform=1&appId=".$appcan_appId."&search=&pageNo=&pageSize=&next=&pre=";
		$postData["callback"] ="parent.doLoginCallback";
		$postData["lt"] ="e1s1";
		$postData["username"] =$appcan_username;
		$postData["password"] =$appcan_password;
		$postData["_eventId"] ="submit";
		$postData["isAjax"] ="true";

		$header = array();
		$header["Accept"] = "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8";
		$header["Accept-Encoding"] = "deflate";
		$header["Accept-Language"] = "zh-CN,zh;q=0.8";
		$header["Origin"] = "http://www.appcan.cn";
		$header["Referer"] = "http://www.appcan.cn/";
		$header["Content-Type"] = "application/x-www-form-urlencoded";
		$header["User-Agent"] = "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.125 Safari/537.36";
		$header["Cache-Control"] = "max-age=0";
		$header["Cookie"] = "appcan_uuid=c7b767c0-0b04-54b6-084b-f656966e83a2; pgv_pvi=7974164035; JSESSIONID=167792B667F635ADB042C746E2C42C05";
		
		//print_r($header);
		$url = "http://newsso.appcan.cn/login?service=http%3A%2F%2Fdashboard.appcan.cn%2Flogin";

		$requestBase = new RequestBase();
		$ret = $requestBase->exec($url, $postData, RequestBase::METHOD_POST,array(CURLOPT_HTTPHEADER => $header,CURLOPT_HEADER => true));
		
		if(strpos($ret,"Set-Cookie: ")){
			$appcan_cookies = substr($ret,strpos($ret,"Set-Cookie: ")+strlen("Set-Cookie: "));
			$appcan_cookies = substr($appcan_cookies,0 , strpos($appcan_cookies,";"));
			//echo "{\"status\":1,\"cookies\":\"".$appcan_cookies."\",\"url\":\"".$url."\"}";
			$_SESSION["appcan_cookies"] = $appcan_cookies;
			$_SESSION["login_time"] = time();
			//echo "".$appcan_cookies."\n";
		}

		

		return $ret;
		//return $result;
	}

	/**
	 * 获取安卓在线用户列表。
	 */
	function GetAndroidUserList(){
		return GetUserListByPlatForm(1);
	}
	
	/**
	 * 获取苹果在线用户列表。
	 */
	function GetIosUserList(){
		return GetUserListByPlatForm(0);
	}


	/**
	 * 获取推送在线的用户列表。
	 * 
	 * @param intPlatForm
	 *            客户端类型，0为这IOS，1为安卓。
	 */
	function GetUserListByPlatForm($intPlatForm,$pageSize,$pageNo){
		LoginAppcan();
		global $appcan_cookies ;
		global $appcan_appId ;
		//获取列表所需访问的URL，intPlatForm是参数。
		$url = "http://newpush.appcan.cn/msg/listUserPage1?"
			."platform=".$intPlatForm
				."&appId=".$appcan_appId
					."&search=&pageNo=".$pageNo
						."&pageSize=".$pageSize."&next=&pre=";
		try {
			$header = array();
			$header["Origin"] = "http://www.appcan.cn";
			$header["Referer"] = "http://newpush.appcan.cn/msg/index?id=30570&appId=".$appcan_appId."&appName=%E6%95%B0%E5%AD%97%E7%A6%81%E8%BF%9D";
			$header["User-Agent"] = "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.125 Safari/537.36";
			$header["Cookie"] = $appcan_cookies ;
			$header["X-Requested-With"] = "XMLHttpRequest";
			$header["Content-Type"] = "application/x-www-form-urlencoded";
			
			$requestBase = new RequestBase();
			$ret = $requestBase->exec($url, array(), RequestBase::METHOD_GET,array(CURLOPT_HTTPHEADER => $header,CURLOPT_HEADER => false));
			
			return $ret;
		}
		catch(Exception $e)
		{
			echo $e->getMessage();
		}
	}

	/**
	 * 向所有人推送消息   POST
	 * 
	 * @param $intDevice
	 *            推送终端的类型。
	 * @param $strTitle
	 *            推送给终端的标题。
	 * @param $strContent
	 *            推送给终端的内容。
	 * @param $json
	 *            要推送的JSON字符串，注意是字符串，在客户端接收到的就是：{"msgName":"标题","json":'.$json.'}
	 * @return 发送是否成功。
	 */
	function pushAllDevice($intDevice, $strTitle ,$strContent,$json){
		LoginAppcan();
		global $appcan_cookies ;
		global $appcan_appId ;
		global $appcan_token ;
		try {
			$postData = array();
			$postData["appId"] = $appcan_appId;
			$postData["authtoken"] = $appcan_token;
			$postData["platforms"] = $intDevice;		//推送终端的类型。
			$postData["msgName"] = $strTitle;
			$postData["title"] = $strContent;
			$postData["body"] = '{"msgName":"'.$strTitle.'","json":'.$json.'}';
			$postData["badgeNum"] = "";
			$postData["pushTime"] = "";
			$postData["save"] = "72";			//消息保存时间。
			$postData["keepHours"] = "1";
			$postData["userScope"] = "1";
			
			
			$header = array();
			$header["Origin"] = "http://www.appcan.cn";
			$header["Referer"] = "http://newpush.appcan.cn/msg/index?id=118228&appId=".$appcan_appId;
			$header["User-Agent"] = "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.125 Safari/537.36";
			$header["Cookie"] = $appcan_cookies ;
			$header["X-Requested-With"] = "XMLHttpRequest";
			$header["Content-Type"] = "application/x-www-form-urlencoded; charset=UTF-8";
			//System.out.println(cookies;
			$header["Cookie"] = $appcan_cookies ;


			$url = "http://newpush.appcan.cn/msg/pushMsg";


			$requestBase = new RequestBase();
			$ret = $requestBase->exec($url, $postData, RequestBase::METHOD_POST,array(CURLOPT_HTTPHEADER => $header,CURLOPT_HEADER => false));
			
			return $ret;
		}
		catch(Exception $e)
		{
			echo $e->getMessage();
		}
	}



	//echo pushSingelDevice(1,"信德国际APP","欢迎您的使用~~~","4d668de33dfe09474cc2a384069bd2fd");
	/**
	 * 向所有人推送消息   POST
	 * 
	 * @param $intDevice
	 *            推送终端的类型。
	 * @param $strTitle
	 *            推送给终端的标题。
	 * @param $strContent
	 *            推送给终端的内容。
	 * @param $strToken
	 *            指定token推送给他。
	 * @param $json
	 *            要推送的JSON字符串，注意是字符串，在客户端接收到的就是：{"msgName":"标题","json":'.$json.'}
	 * @return 发送是否成功。
	 */
	function pushSingelDevice($intDevice, $strTitle ,$strContent ,$strToken,$json){
		LoginAppcan();
		global $appcan_cookies ;
		global $appcan_appId ;
		try {
			$postData = array();
			$postData["appId"] = $appcan_appId;
			$postData["platforms"] = $intDevice;		//推送终端的类型。
			$postData["msgName"] = $strTitle;
			$postData["title"] = $strContent;
			$postData["body"] = '{"msgName":"'.$strTitle.'","json":'.$json.'}';
			$postData["badgeNum"] = "";
			$postData["pushTime"] = "";
			$postData["save"] = "72";			//消息保存时间。
			$postData["keepHours"] = "1";
			$postData["userScope"] = "1";
			$postData["title"] = $strContent;
			$postData["softToken"] = $strToken;
			$postData["checkbox"] = "checkbox";
			$postData["badgeNum"] = "";
			
			$header = array();
			$header["Origin"] = "http://www.appcan.cn";
			$header["Referer"] = "http://newpush.appcan.cn/msg/index?id=30570&appId=".$appcan_appId."&appName=%E6%95%B0%E5%AD%97%E7%A6%81%E8%BF%9D";
			$header["User-Agent"] = "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.125 Safari/537.36";
			$header["Cookie"] = $appcan_cookies ;
			$header["X-Requested-With"] = "XMLHttpRequest";
			$header["Content-Type"] = "application/x-www-form-urlencoded; charset=UTF-8";
			//System.out.println(cookies;
			$header["Cookie"] = $appcan_cookies ;


			$url = "http://newpush.appcan.cn/msg/pushMsg";


			$requestBase = new RequestBase();
			$ret = $requestBase->exec($url, $postData, RequestBase::METHOD_POST,array(CURLOPT_HTTPHEADER => $header,CURLOPT_HEADER => false));
			
			return $ret;
		}
		catch(Exception $e)
		{
			echo $e->getMessage();
		}
	}




class RequestBase{

	//get请求方式
	const METHOD_GET  = 'get';
	//post请求方式
	const METHOD_POST = 'post';

	/**
	 * 发起一个get或post请求
	 * @param $url 请求的url
	 * @param int $method 请求方式
	 * @param array $params 请求参数
	 * @param array $extra_conf curl配置, 高级需求可以用, 如
	 * $extra_conf = array(
	 *    CURLOPT_HEADER => true,
	 *    CURLOPT_RETURNTRANSFER = false
	 * )
	 * @return bool|mixed 成功返回数据，失败返回false
	 * @throws Exception
	 */
	public static function exec($url,  $params = array(), $method = self::METHOD_GET, $extra_conf = array())
	{
		$params = is_array($params)? http_build_query($params): $params;
		//如果是get请求，直接将参数附在url后面
		if($method == self::METHOD_GET)
		{
			$url .= (strpos($url, '?') === false ? '?':'&') . $params;
		}

		//默认配置
		$curl_conf = array(
				CURLOPT_URL => $url,  //请求url
				CURLOPT_HEADER => false,  //不输出头信息
				CURLOPT_RETURNTRANSFER => true, //不输出返回数据
				CURLOPT_CONNECTTIMEOUT => 5 // 连接超时时间
		);

		//配置post请求额外需要的配置项
		if($method == self::METHOD_POST)
		{
			//使用post方式
			$curl_conf[CURLOPT_POST] = true;
			//post参数
			$curl_conf[CURLOPT_POSTFIELDS] = $params;
		}

		//添加额外的配置
		foreach($extra_conf as $k => $v)
		{
			$curl_conf[$k] = $v;
		}

		$data = false;
		try
		{
			//初始化一个curl句柄
			$curl_handle = curl_init();
			//设置curl的配置项
			curl_setopt_array($curl_handle, $curl_conf);
			//发起请求
			$data = curl_exec($curl_handle);
			if($data === false)
			{
				throw new Exception('CURL ERROR: ' . curl_error($curl_handle));
			}
		}
		catch(Exception $e)
		{
			echo $e->getMessage();
		}
		curl_close($curl_handle);

		return $data;
	}
}
?>