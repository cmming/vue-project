<?php
////////////////////////////////////////////////////////
// 功能：CP接收晃游平台支付异步通知处理类Demo
// 版本：1.0
// 日期：2014-07-11 
// 说明：
//		 以下代码只是为了方便CP测试而提供的样例代码，CP可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码
//		 该代码仅供学习和研究晃游平台SDK接口使用，只是提供一个参考。
//		其中deal_cp_app_logic()函数为商户业务逻辑处理函数
// 调式工具：	LOG_FLAG 为日志开关　
//				write_log()为文本日志输出函数
//				$_logPath 为日志文件保存全路径[包含文件名]
////////////////////////////////////////////////////////
class BlueSDK_Processor
{
	const PT_WXPAY_NATIVE = 102;		//微信扫码支付
	const PT_ALIPAY_F2F = 105;			//支付宝扫码支付

	private $_QUERY_ORDER_URL = "http://api.lgshouyou.com/QueryOrder.php?";	//订单校验地址
	private $_GET_ORDER_URL = "http://api.lgshouyou.com/GetOrder.php?";		//请求订单生成地址
	//const QUERY_ORDER_URL = "http://192.168.1.4:8080/CoreSDK/QueryOrder.php?";
	//const QUERY_ORDER_URL = "http://192.168.1.250:8080/blue/api/QueryOrder.php?";	//订单校验地址

	const LOG_FLAG = false;							//日志开关
	private $_logPath = 'BlueSDK_Pay_Recv.log';		//日志路径

	///////////////////////////////////////////////
	private $_cp_app_id = "";		//晃游平台分配给CP的应用编号
	private $_cp_app_key = "";		//晃游平台分配给CP的应用标识
	private $_cp_app_secret = "";	//晃游平台分配给CP的应用的通信息密钥	
	private $_sdk_secret_key = '';	//平台内部加密密钥
	///////////////////////////////////////////////

	public $_pay_user_id = "";		//晃游平台付费用户的数字id
	public $_pay_type = "";			//晃游平台扣费方式标识
	public $_order_id = "";			//晃游平台生成的定单编号
	public $_amount = 0;			//所购买商品金额, 以分为单位.  
	public $_qrcode = "";			//订单图源二维码数据地址


	public $_cp_oid = "";			//CP方订单编号
	public $_cp_product_id = "";	//CP方商品编号
	public $_cp_uid = "";			//CP方用户数字id
	public $_cp_uname = "";			//CP方用户帐号
	public $_cp_unick = "";			//CP方用户昵称或角色或称[如果填写则有]
	public $_cp_svrid = "";			//CP方游或应用的服，区编号[如果填写则有]
	public $_ext = "";				//用户自定义参数，透传参数,如有回调，则原样返回

	private $_data = "";			//收到的订单数据，订单校验用

	//错误码
	public $_error_code = "";
	//错误描述
	public $_error_des = "";

	public function __construct($cp_app_id,$cp_app_key,$cp_app_secret)
	{
		$this->_cp_app_id = $cp_app_id;
		$this->_cp_app_key = $cp_app_key;
		$this->_cp_app_secret = $cp_app_secret;
		if(defined('SDK_SECRET_KEY'))
		{
			$this->_sdk_secret_key = SDK_SECRET_KEY;
		}
		if(BLUE_TEST_FLAG==true)
		{
			$this->_QUERY_ORDER_URL = BLUE_TEST_URL."QueryOrder.php?";
			$this->_GET_ORDER_URL = BLUE_TEST_URL."GetOrder.php?";
		}
	}

    public function setLogPath($logPath)
    {
        $this->_logPath = $logPath;
    }

	/////////////////////////////////////////////////
	//功　　能：写日志
	//入口参数：$msg	- 日志内容
	//返 回 值：无
	//说　　明：
	/////////////////////////////////////////////////
	public function write_log($msg)
	{
		if(self::LOG_FLAG)
		{
			$now = strftime('[%Y-%m-%d %H:%i:%s]',time());
			$msg = $now.$msg;

			$isNewFile = !file_exists($this->_logPath);
			$fp = fopen($this->_logPath, 'a');
			if($fp)
			{
				if (flock($fp, LOCK_EX)) {
					fwrite($fp, $msg . "\n");
					flock($fp, LOCK_UN);
				}
				fclose($fp);
			}
		}
	}	

	///////////////////////////////////////////////
	//功　	能：输入回显结果
	//入口参数：$result - true:内部处理成功 false:内部处理失败
	//说　　明：
	///////////////////////////////////////////////
	public function echo_result($result)
	{
		if($result)
		{
			//处理成功，按照第三方平台要求输出
			echo "success";
		}else{
			//处理失败，按照第三方平台要求输出,也可以有选择的输出或不输出
			echo "fail";
		}
	}

	///////////////////////////////////////////////
	//功　	能：获取输出回显结果
	//入口参数：$result - true:内部处理成功 false:内部处理失败
	//说　　明：
	///////////////////////////////////////////////
	public function get_echo_result($result)
	{
		if($result)
		{
			return "success";
		}else{
			return "fail";
		}
	}

	/////////////////////////////////////////////////
	//功　　能：生成签名
	//入口参数：$params		- 待签名字段数组
	//			$appSecret	- 签名私钥
	//返 回 值：签名后的md5码
	/////////////////////////////////////////////////
	private function getSign($params, $appSecret)
    {
		unset($params['sign']);

        $processedParams = array();
        foreach ($params as $k => $v) {
            if (empty($v)) {
                continue;
            }

            $processedParams[$k] = $v;
        }
        ksort($processedParams);
        $signStr = join('#', $processedParams) . '#' . $appSecret;
		$this->write_log("签名字符串:".$signStr);
        return md5($signStr);
    }

	///////////////////////////////////////////////
	//功　	能：请求生成订单
	//入口参数：$pay_type - 支付类型
	//			$user_id - 用户在平台方的数字编号
	//			$amount- 支付金额【单位分】
	//			$product_id - 物品编号
	//			$cp_uid - 用户在cp方的数字id
	//			$cp_uname - 用户在cp方的帐号
	//			$product_name - 物品名称或描述
	//			$cp_oid - cp方订单编号
	//			$cp_unick - 用户在cp方昵称
	//			$cp_svrid - 用户充值游戏区服编号
	//			$notify_url - 支付成功通知地址
	//			$ext - 透传参数
	//返 回 值：true	- 数据合法，解析成功 
	//			false	- 数据非法，解析失败
	//说　　明：已完成支付逻辑的订单，再次调用此接口时，将返回不合法
	///////////////////////////////////////////////
	public function get_order($pay_type,
								$user_id,
								$amount,
								$product_id,
								$cp_uid,
								$cp_uname,
								$product_name="",
								$cp_oid="",
								$cp_unick="",
								$cp_svrid="",
								$notify_url="",
								$ext="")
	{
		$ret = false;
		$t_data['act'] = "getoid_blue_web";
		$t_data['cp_appkey'] = $this->_cp_app_key;
		$t_data['pay_type'] = $pay_type;
		$t_data['user_id'] = $user_id;
		$t_data['amount'] = $amount;
		$t_data['product_id'] = $product_id;
		$t_data['cp_uid'] = $cp_uid;
		$t_data['cp_uname'] = $cp_uname;
		$t_data['product_name'] = $product_name;
		$t_data['cp_oid'] = $cp_oid;
		$t_data['cp_unick'] = $cp_unick;
		$t_data['cp_svrid'] = $cp_svrid;
		$t_data['notify_url'] = $notify_url;
		$t_data['ext'] = $ext;
		$t_data['sign'] = $this->getSign($t_data,$this->_cp_app_secret.$this->_sdk_secret_key);
		$params = http_build_query($t_data);
		$result = @file_get_contents($this->_GET_ORDER_URL.$params);
		if(!empty($result))
		{
			$order_info = json_decode($result,true);
			if(!is_null($order_info))
			{
				if(strcmp($order_info['err_code'],"1") == 0)
				{
					$this->_pay_user_id = $user_id;
					$this->_pay_type = $pay_type;
					$this->_amount = $amount;
					$this->_order_id = $order_info['oid'];
					$this->_qrcode = $order_info['qrcode'];
					$ret = true;
				}else{
					$this->_error_code = $order_info['err_code'];
					$this->_error_des = $order_info['err_msg'];
				}
			}
		}
		return $ret;
	}

	///////////////////////////////////////////////
	//功　	能：验证第三方支付通知数据合法性，
	//入口参数：$params		- 待处理参数
	//			$secret_key	- 与支付渠道对接的加密密钥
	//返 回 值：true	- 数据合法，解析成功 
	//			false	- 数据非法，解析失败
	//说　　明：
	///////////////////////////////////////////////
	public function check_notify_data($params)
	{
		$ret = false;
		if(isset($params['pay_type']) && isset($params['cp_appkey']) && (!empty($params['cp_appkey'])) && isset($params['user_id']) && isset($params['order_id']) && isset($params['product_id']) && isset($params['cp_uid']) && isset($params['cp_uname']) && isset($params['amount']) && isset($params['sign']))
		{
			if(strcasecmp($this->_cp_app_key,$params['cp_appkey']) == 0)
			{				
				$this->_data = $params;
				$sign = $params['sign'];
				$my_sign = $this->getSign($params,$this->_cp_app_secret);
				if(strcasecmp($sign,$my_sign) == 0)
				{
					if($this->check_order_valid())
					{
						//保存结果
						$this->_pay_user_id = $params['user_id'];		//晃游平台付费用户的数字id
						$this->_pay_type = $params['pay_type'];			//晃游平台扣费方式标识
						$this->_order_id = $params['order_id'];			//晃游平台生成的定单编号
						$this->_amount = $params['amount'];				//所购买商品金额, 以分为单位.  
						$this->_cp_product_id = $params['product_id'];	//CP方商品编号
						$this->_cp_uid = $params['cp_uid'];				//CP方用户数字id
						$this->_cp_uname = $params['cp_uname'];			//CP方用户帐号
						$this->_cp_oid = isset($params['cp_oid'])?$params['cp_oid']:"";					//CP方订单编号				
						$this->_cp_unick =  isset($params['cp_unick'])?$params['cp_unick']:"";			//CP方用户昵称或角色或称[如果填写则有]
						$this->_cp_svrid =  isset($params['cp_svrid'])?$params['cp_svrid']:"";			//CP方游或应用的服，区编号[如果填写则有]
						$this->_ext =  isset($params['ext'])?$params['ext']:"";							//用户自定义参数，透传参数,如有回调，则原样返回
						$ret = true;
					}else{
						$this->write_log("订单不合法");
					}					
				}else{
					$this->write_log("签名不同");
				}
			}else{
				$this->write_log("app_key不同");
			}			
		}else{
			$this->write_log("数据不完整");
		}

		return $ret;
	}

	///////////////////////////////////////////////
	//功　	能：校验订单是否合法
	//入口参数：
	//返 回 值：true	- 数据合法，解析成功 
	//			false	- 数据非法，解析失败
	//说　　明：已完成支付逻辑的订单，再次调用此接口时，将返回不合法
	///////////////////////////////////////////////
	public function check_order_valid()
	{
		$ret = false;
		$args = http_build_query($this->_data);
		$check_data = @file_get_contents($this->_QUERY_ORDER_URL.$args);
		$this->write_log("验证订单:".$check_data);
		if(!empty($check_data))
		{
			$check_info = json_decode($check_data,true);
			if(!is_null($check_info))
			{
				if($check_info['err_code'] == 1)
				{
					$ret = true;
				}
			}
		}else{
			$this->write_log("验证订单失败");
		}

		return $ret;
	}
}
?>
