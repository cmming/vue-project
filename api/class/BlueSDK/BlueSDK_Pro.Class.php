<?php
///////////////////////////////////////////////////
//17晃开放平台用户认证，授权开放接口库
//bull 2014-07-03 
/////////////////////////////////////////////////
class BlueSDK_Pro
{
	/////////////////////////////////////////////////
	//常量不能要修改
	/////////////////////////////////////////////////
	//平台内部加密密钥
	private $_sdk_secret_key = '';

	//接口地址
	private $_BLUESDK_USERGATE_URL = "http://api.lgshouyou.com/UserGate.php?";	
	private $_BLUESDK_USERSTAT_URL = "http://api.lgshouyou.com/UserStat.php?";	
	private $_BLUESDK_GET_CODE_URL = "http://api.lgshouyou.com/vcode/vcode.php?";
	private $_BLUESDK_GET_SMS_URL = "http://api.lgshouyou.com/SMSGate.php?";	

	/////////////////////////////////////////////////
	//平台分配给CP的配置信息,需要配置
	/////////////////////////////////////////////////
	private $_cp_app_id = "";		//晃游平台分配给CP的应用编号
	private $_cp_app_key = "";		//晃游平台分配给CP的应用标识
	private $_cp_app_secret = "";	//晃游平台分配给CP的应用的通信息密钥
	///////////////////////////////////////////////
	
	//用户令牌
	private $_user_token = "";
	//用户数据编号
	private $_user_uid = 0;
	//用户昵称
	private $_user_unick = "";
	//用户帐号[可能为空]
	private $_user_uname = "";
	//用户证件号码
	private $_user_cardid = "";
	//用户电话码[可能为空]
	private $_user_phone = "";
	//用户QQ号码[可能为空]
	private $_user_qq = "";
	//用户积分[可能为空]
	private $_user_score = 0;
	//用户邮寄地址[可能为空]
	private $_user_addr = "";
	//用户E-mail[可能为空]
	private $_user_email = "";

	//错误码
	private $_error_code = "";
	//错误描述
	private $_error_des = "";
	


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
			$this->_BLUESDK_USERGATE_URL = BLUE_TEST_URL."UserGate.php?";
			$this->_BLUESDK_USERSTAT_URL = BLUE_TEST_URL."UserStat.php?";
			$this->_BLUESDK_GET_CODE_URL = BLUE_TEST_URL."vcode/vcode.php?";
			$this->_BLUESDK_GET_SMS_URL = BLUE_TEST_URL."SMSGate.php?";
		}
	}

	///////////////////////////////////////////////
	//功　	能：获取用户数字id
	//入口参数：
	//返 回 值：用户数字id
	//说　　明：
	///////////////////////////////////////////////
	public function get_user_uid()
	{
		return $this->_user_uid;
	}

	///////////////////////////////////////////////
	//功　	能：获取用户昵称
	//入口参数：
	//返 回 值：用户昵称
	//说　　明：
	///////////////////////////////////////////////
	public function get_user_unick()
	{
		return $this->_user_unick;
	}

	///////////////////////////////////////////////
	//功　	能：获取用户帐号
	//入口参数：
	//返 回 值：用户昵称
	//说　　明：
	///////////////////////////////////////////////
	public function get_user_uname()
	{
		return $this->_user_uname;
	}

	///////////////////////////////////////////////
	//功　	能：获取用户令牌
	//入口参数：
	//返 回 值：用户令牌
	//说　　明：
	///////////////////////////////////////////////
	public function get_user_token()
	{
		return $this->_user_token;
	}

	///////////////////////////////////////////////
	//功　	能：获取用户证件号码
	//入口参数：
	//返 回 值：户证件号码
	//说　　明：
	///////////////////////////////////////////////
	public function get_user_cardid()
	{
		return $this->_user_cardid;
	}


	///////////////////////////////////////////////
	//功　	能：获取用户电话码
	//入口参数：
	//返 回 值：用户电话码
	//说　　明：
	///////////////////////////////////////////////
	public function get_user_phone()
	{
		return $this->_user_phone;
	}

	///////////////////////////////////////////////
	//功　	能：获取用户QQ号码
	//入口参数：
	//返 回 值：用户QQ号码
	//说　　明：
	///////////////////////////////////////////////
	public function get_user_qq()
	{
		return $this->_user_qq;
	}

	///////////////////////////////////////////////
	//功　	能：获取用户邮寄地址
	//入口参数：
	//返 回 值：用户邮寄地址
	//说　　明：
	///////////////////////////////////////////////
	public function get_user_addr()
	{
		return $this->_user_addr;
	}

	///////////////////////////////////////////////
	//功　	能：获取用户E-mail
	//入口参数：
	//返 回 值：用户E-mail
	//说　　明：
	///////////////////////////////////////////////
	public function get_user_email()
	{
		return $this->_user_email;
	}

	///////////////////////////////////////////////
	//功　	能：获取用户积分
	//入口参数：
	//返 回 值：用户积分
	//说　　明：
	///////////////////////////////////////////////
	public function get_user_score()
	{
		return $this->_user_score;
	}

	///////////////////////////////////////////////
	//功　	能：获取错误码
	//入口参数：
	//返 回 值：最后一次错误码
	//说　　明：
	///////////////////////////////////////////////
	public function get_error_code()
	{
		return $this->_error_code;
	}
	///////////////////////////////////////////////
	//功　	能：获取错误描述
	//入口参数：
	//返 回 值：最后一次错误码对应描述
	//说　　明：
	///////////////////////////////////////////////
	public function get_error_des()
	{
		return $this->_error_des;
	}
	///////////////////////////////////////////////
	//功　	能：清除错误信息
	//入口参数：
	//返 回 值：
	//说　　明：
	///////////////////////////////////////////////
	public function clear_error_info()
	{
		$this->_error_code="";
		$this->_error_des="";
	}

	///////////////////////////////////////////////
	//功　	能：通过令牌获取用户信息
	//入口参数：$token	- 令牌
	//返 回 值：true	- 数据合法
	//			false	- 数据非法
	//说　　明：验证token是否有效，若有效，则能获取用户信息
	///////////////////////////////////////////////
	public function get_user_info_by_token($token)
	{
		$ret = false;
		$t_data['act'] = "auth_blue_web";
		$t_data['cp_appkey'] = $this->_cp_app_key;
		$t_data['user_token'] = $token;
		$t_data['sign'] = $this->getSign($t_data,$this->_cp_app_secret.$this->_sdk_secret_key);
		$params = http_build_query($t_data);
		$result = @file_get_contents($this->_BLUESDK_USERGATE_URL.$params);
		if(!empty($result))
		{
			$user_info = json_decode($result,true);
			if(!is_null($user_info))
			{
				if(strcmp($user_info['err_code'],"1") == 0)
				{
					$this->_user_token = $token;
					$this->_user_uid = $user_info['user_id'];
					$this->_user_unick = $user_info['user_nick'];
					$this->_user_uname = $user_info['user_name'];
					$ret = true;
				}else{
					$this->_error_code = $user_info['err_code'];
					$this->_error_des = $user_info['err_msg'];
				}
			}
		}
		return $ret;
	}

	///////////////////////////////////////////////
	//功　	能：通过令牌获取用户详细信息
	//入口参数：$token	- 令牌
	//返 回 值：true	- 获取成功
	//			false	- 获取失败
	//说　　明：
	///////////////////////////////////////////////
	public function get_user_detail_info_by_token($token)
	{
		$ret = false;
		$t_data['act'] = "query_uinfo_blue_web";
		$t_data['cp_appkey'] = $this->_cp_app_key;
		$t_data['user_token'] = $token;
		$t_data['sign'] = $this->getSign($t_data,$this->_cp_app_secret.$this->_sdk_secret_key);
		$params = http_build_query($t_data);
		$result = @file_get_contents($this->_BLUESDK_USERGATE_URL.$params);
		$this->_writeLog($result);
		if(!empty($result))
		{
			$user_info = json_decode($result,true);
			if(!is_null($user_info))
			{
				if(strcmp($user_info['err_code'],"1") == 0)
				{
					
					//有详细信息过来
					if(!empty($user_info['data']))
					{
						$this->_user_token = $token;
						$this->_user_uid = isset($user_info['data']['uid'])?$user_info['data']['uid']:0;
						$this->_user_unick = isset($user_info['data']['nick'])?$user_info['data']['nick']:"";
						$this->_user_phone = isset($user_info['data']['phone'])?$user_info['data']['phone']:"";
						$this->_user_email = isset($user_info['data']['email'])?$user_info['data']['email']:"";
						$this->_user_qq = isset($user_info['data']['qq'])?$user_info['data']['qq']:"";
						$this->_user_addr = isset($user_info['data']['addr'])?$user_info['data']['addr']:"";
						$this->_user_cardid = isset($user_info['data']['cardid'])?$user_info['data']['cardid']:"";
						
						$ret = true;
					}else{
						$this->_writeLog("err_code:".$user_info['err_code']." err_msg:".$user_info['err_msg']);
					}
				}
			}
		}else{
			echo "tt".$result;
		}
		return $ret;
	}

	///////////////////////////////////////////////
	//功　	能：通过令牌修改用户详细信息
	//入口参数：$token	- 令牌
	//			$data_arr - 需要修改的数据数据，数组键名为允许修改的字段，如 nick，phone，email
	//返 回 值：true	- 修改成功
	//			false	- 修改失败
	//说　　明：
	///////////////////////////////////////////////
	public function modify_user_detail_info_by_token($token,$data_arr)
	{
		$ret = false;
		$t_data['act'] = "modify_uinfo_blue_web";
		$t_data['cp_appkey'] = $this->_cp_app_key;
		$t_data['user_token'] = $token;
		$t_data['data'] = json_encode($data_arr);
		$t_data['sign'] = $this->getSign($t_data,$this->_cp_app_secret.$this->_sdk_secret_key);
		$params = http_build_query($t_data);
		$result = @file_get_contents($this->_BLUESDK_USERGATE_URL.$params);
		$this->_writeLog($result);
		if(!empty($result))
		{
			$user_info = json_decode($result,true);
			if(!is_null($user_info))
			{
				if(strcmp($user_info['err_code'],"1") == 0)
				{
					$ret = true;
				}else{
					$this->_writeLog("err_code:".$user_info['err_code']." err_msg:".$user_info['err_msg']);
				}
			}
		}
		return $ret;
	}

	///////////////////////////////////////////////
	//功　	能：用户重置密码
	//入口参数：$user_uname - 用户名
	//			$user_pwd - 用户密码[明文]
	//			$vkey - 验证码识别码
	//			$vcode - 验证码
	//返 回 值：true	- 修改成功
	//			false	- 修改失败
	//说　　明：
	///////////////////////////////////////////////
	public function reset_user_pwd($user_uname,$user_pwd,$vkey,$vcode)
	{
		$ret = false;
		$t_data['act'] = "reset_pwd_blue_web";
		$t_data['cp_appkey'] = $this->_cp_app_key;
		$t_data['user_name'] = $user_uname;
		$t_data['user_pwd'] = md5($user_pwd);
		$t_data['dev_id'] = "WEB_CP_".$this->getip_str();
		$t_data['vcode'] = $vcode;
		$t_data['vkey'] = $vkey;
		$t_data['sign'] = $this->getSign($t_data,$this->_cp_app_secret.$this->_sdk_secret_key);
		$params = http_build_query($t_data);
		$result = @file_get_contents($this->_BLUESDK_USERGATE_URL.$params);
		$this->_writeLog("reset_user_pwd result:$result");
		if(!empty($result))
		{
			$user_info = json_decode($result,true);
			if(!is_null($user_info))
			{
				if(strcmp($user_info['err_code'],"1") == 0)
				{					
					$ret = true;
				}else{
					$this->_error_code = $user_info['err_code'];
					$this->_error_des = $user_info['err_msg'];
				}
			}
		}
		return $ret;
	}

	///////////////////////////////////////////////
	//功　	能：通过令牌获取用户财富信息
	//入口参数：$token	- 令牌
	//返 回 值：true	- 获取成功
	//			false	- 获取失败
	//说　　明：
	///////////////////////////////////////////////
	public function get_user_wealth_info_by_token($token)
	{
		$ret = false;
		$t_data['act'] = "query_wealth_blue_web";
		$t_data['cp_appkey'] = $this->_cp_app_key;
		$t_data['user_token'] = $token;
		$t_data['sign'] = $this->getSign($t_data,$this->_cp_app_secret.$this->_sdk_secret_key);
		$params = http_build_query($t_data);
		$result = @file_get_contents($this->_BLUESDK_USERGATE_URL.$params);
		$this->_writeLog($result);
		if(!empty($result))
		{
			$user_info = json_decode($result,true);
			if(!is_null($user_info))
			{
				if(strcmp($user_info['err_code'],"1") == 0)
				{
					if(!empty($user_info['data']))
					{
						$this->_user_score =  isset($user_info['data']['score'])?$user_info['data']['score']:0;
						$ret = true;
					}else{
						$this->_writeLog("err_code:".$user_info['err_code']." err_msg:".$user_info['err_msg']);
					}
				}else{
					$this->_writeLog("err_code:".$user_info['err_code']." err_msg:".$user_info['err_msg']);
				}
			}
		}
		return $ret;
	}

	///////////////////////////////////////////////
	//功　	能：通过令牌修改用户财富信息
	//入口参数：$uid	- 用户数字编号
	//			$reason - 修改原因/动作
	//			$oid	- 财富变更请求在请求方的唯一订单号，最大长度32
	//			$score  - 积分变动量【正负表示增减】
	//			$diamond  - 钻石变动量【正负表示增减】	
	//返 回 值：true	- 修改成功
	//			false	- 修改失败
	//说　　明：
	///////////////////////////////////////////////
	public function modify_user_wealth_info_by_uid($uid,$reason,$oid,$score,$diamond=0)
	{
		$ret = false;
		$t_data['act'] = "modify_wealth_blue_web";
		$t_data['cp_appkey'] = $this->_cp_app_key;
		$t_data['user_id'] = $uid;
		$t_data['reason'] = $reason;
		$t_data['oid'] = $oid;
		$tmp_arr['score'] = $score;
		$tmp_arr['diamond'] = $diamond;
		$t_data['data'] = json_encode($tmp_arr);
		$t_data['sign'] = $this->getSign($t_data,$this->_cp_app_secret.$this->_sdk_secret_key);
		$params = http_build_query($t_data);
		$result = @file_get_contents($this->_BLUESDK_USERGATE_URL.$params);
		$this->_writeLog($result);
		if(!empty($result))
		{
			$user_info = json_decode($result,true);
			if(!is_null($user_info))
			{
				if(strcmp($user_info['err_code'],"1") == 0)
				{
					$ret = true;
				}else{
					$this->_writeLog("err_code:".$user_info['err_code']." err_msg:".$user_info['err_msg']);
				}
			}
		}
		return $ret;
	}

	///////////////////////////////////////////////
	//功　	能：生成验证码
	//出口参数：$vkey - 验证码识别码
	//			$vcode_img　- 验证码图像信息
	//返 回 值：true	- 获取成功
	//			false	- 获取失败
	//说　　明：$vkey在需要验证码的地方一并需要，
	//			建议要使用该功能开启session来保存该值
	///////////////////////////////////////////////
	public function get_vcode(&$vkey,&$vcode_img)
	{
		$ret = false;
		$t_data['cp_appkey'] = $this->_cp_app_key;
		$params = http_build_query($t_data);
		$result = @file_get_contents($this->_BLUESDK_GET_CODE_URL.$params);		
		//$this->_writeLog("get_vcode result=$result");
		if(!empty($result))
		{
			$vcode_info = json_decode($result,true);
			if(!is_null($vcode_info))
			{
				$vkey = $vcode_info['key'];
				$vcode_img = urldecode($vcode_info['src']);
				$ret = true;
			}else{
				$this->_error_code = $vcode_info['err_code'];
				$this->_error_des = $vcode_info['err_msg'];
				$this->_writeLog("get_vcode error err_code:".$vcode_info['err_code']." err_msg:".$vcode_info['err_msg']);
			}
		}
		return $ret;
	}

	///////////////////////////////////////////////
	//功　	能：生成手机注册验证码请求地址
	//入口参数：$phone	- 用户手机号码
	//出口参数：$vkey - 验证码识别码
	//			$ltimes - 该用户还可申获取验证码的次数
	//返 回 值：true	- 获取成功
	//			false	- 获取失败
	//说　　明：$vkey在需要验证码的地方一并需要，
	//			建议要使用该功能开启session来保存该值
	///////////////////////////////////////////////
	public function get_reg_sms($phone,&$vkey,&$ltimes)
	{
		return $this->send_vcode("reg_sms_blue_web",$phone,$vkey,$ltimes);
	}
	
	///////////////////////////////////////////////
	//功　	能：发送用户信息修改验证码
	//入口参数：$phone	- 用户手机号码
	//出口参数：$vkey - 验证码识别码
	//			$ltimes - 该用户还可申获取验证码的次数
	//返 回 值：true	- 获取成功
	//			false	- 获取失败
	//说　　明：$vkey在需要验证码的地方一并需要，
	//			建议要使用该功能开启session来保存该值
	///////////////////////////////////////////////
	public function get_com_sms($phone,&$vkey,&$ltimes)
	{
		 return $this->send_vcode("get_com_sms_blue_web",$phone,$vkey,$ltimes);
	}
	///////////////////////////////////////////////
	//功　	能：请求发送验证码【通用】
	//入口参数：$phone	- 用户手机号码 或邮箱地址
	//出口参数：$vkey - 验证码识别码
	//			$ltimes - 该用户还可申获取验证码的次数
	//返 回 值：true	- 获取成功
	//			false	- 获取失败
	//说　　明：$vkey在需要验证码的地方一并需要，
	//			建议要使用该功能开启session来保存该值
	///////////////////////////////////////////////
	private function send_vcode($act,$phone,&$vkey,&$ltimes)
	{
		$ret = false;
		$t_data['act'] = $act;
		$t_data['cp_appkey'] = $this->_cp_app_key;
		$t_data['cip'] = self::ipton(self::getip_str());
		$t_data['phone'] = $phone;
		$t_data['sign'] = $this->getSign($t_data,$this->_cp_app_secret.$this->_sdk_secret_key);
		$params = http_build_query($t_data);
		$result = @file_get_contents($this->_BLUESDK_GET_SMS_URL.$params);	
		$this->_writeLog("send_vcode result:$result");
		if(!empty($result))
		{
			$vcode_info = json_decode($result,true);
			if(!is_null($vcode_info))
			{
				if(strcmp($vcode_info['err_code'],"1") == 0)
				{
					$vkey = $vcode_info['key'];
					$ltimes = $vcode_info['ltimes'];
					$ret = true;
				}else{
					$this->_error_code = $vcode_info['err_code'];
					$this->_error_des = $vcode_info['err_msg'];
					$this->_writeLog("err_code:".$vcode_info['err_code']." err_msg:".$vcode_info['err_msg']);
				}
			}else{
				$this->_writeLog("send_vcode error");
			}
		}
		return $ret;
	}

	///////////////////////////////////////////////
	//功　	能：验证并使用[手机注册验证码]
	//入口参数：$phone	- 用户手机号码
	//出口参数：$vkey - 验证码识别码
	//			$vcode - 验证码
	//返 回 值：true	- 验证并使用成功
	//			false	- 验证使用失败
	//说　　明：$vkey在需要验证码的地方一并需要，
	//			建议要使用该功能开启session来保存该值
	///////////////////////////////////////////////
	public function check_use_sms($phone,$vkey,$vcode)
	{
		return $this->send_vcode("check_sms_blue_web",$phone,$vkey,$vcode);		
	}

	///////////////////////////////////////////////
	//功　	能：验证并使用[通用验证码]
	//入口参数：$phone	- 用户手机号码
	//出口参数：$vkey - 验证码识别码
	//			$ltimes - 该用户还可申获取验证码的次数
	//返 回 值：true	- 获取成功
	//			false	- 获取失败
	//说　　明：$vkey在需要验证码的地方一并需要，
	//			建议要使用该功能开启session来保存该值
	///////////////////////////////////////////////
	public function check_use_com_sms($phone,$vkey,$vcode)
	{
		 return $this->check_vcode("check_com_sms_blue_web",$phone,$vkey,$vcode);
	}


	///////////////////////////////////////////////
	//功　	能：验证并使用验证码[手机或邮箱验证码]
	//入口参数：$phone	- 用户手机号码
	//出口参数：$vkey - 验证码识别码
	//			$vcode - 验证码
	//返 回 值：true	- 验证并使用成功
	//			false	- 验证使用失败
	//说　　明：$vkey在需要验证码的地方一并需要，
	//			建议要使用该功能开启session来保存该值
	///////////////////////////////////////////////
	private function check_vcode($act,$phone,$vkey,$vcode)
	{
		$ret = false;
		$t_data['act'] = $act;
		$t_data['cp_appkey'] = $this->_cp_app_key;
		$t_data['phone'] = $phone;
		$t_data['vcode'] = $vcode;
		$t_data['vkey'] = $vkey;
		$t_data['sign'] = $this->getSign($t_data,$this->_cp_app_secret.$this->_sdk_secret_key);
		$params = http_build_query($t_data);
		$result = @file_get_contents($this->_BLUESDK_GET_SMS_URL.$params);	
		$this->_writeLog("check_vcode result:$result");
		if(!empty($result))
		{
			$vcode_info = json_decode($result,true);
			if(!is_null($vcode_info))
			{
				if(strcmp($vcode_info['err_code'],"1") == 0)
				{
					$ret = true;
				}else{
					$this->_error_code = $vcode_info['err_code'];
					$this->_error_des = $vcode_info['err_msg'];
					$this->_writeLog("err_code:".$vcode_info['err_code']." err_msg:".$vcode_info['err_msg']);
				}
			}else{
				$this->_writeLog("check_vcode error");
			}
		}
		return $ret;
	}

	///////////////////////////////////////////////
	//功　	能：用户注册
	//入口参数：$user_uname - 用户名
	//			$user_pwd - 用户密码[明文]
	//			$vkey - 验证码识别码
	//			$vcode - 验证码
	//返 回 值：true	- 注册成功
	//			false	- 注册失败
	//说　　明：注册成功后，会自动登陆，可以取用户信息了
	///////////////////////////////////////////////
	public function user_registry($user_uname,$user_pwd,$vkey,$vcode)
	{
		$ret = false;
		$t_data['act'] = "registry_blue_web";
		$t_data['cp_appkey'] = $this->_cp_app_key;
		$t_data['user_name'] = $user_uname;
		$t_data['user_pwd'] = md5($user_pwd);
		$t_data['dev_id'] = "WEB_CP_".$this->getip_str();
		$t_data['vcode'] = $vcode;
		$t_data['vkey'] = $vkey;
		$t_data['sign'] = $this->getSign($t_data,$this->_cp_app_secret.$this->_sdk_secret_key);
		$params = http_build_query($t_data);
		$result = @file_get_contents($this->_BLUESDK_USERGATE_URL.$params);
		$this->_writeLog("user_registry result:$result");
		if(!empty($result))
		{
			$user_info = json_decode($result,true);
			if(!is_null($user_info))
			{
				if(strcmp($user_info['err_code'],"1") == 0)
				{
					$this->_user_token = $user_info['user_token'];
					$this->_user_uid = $user_info['user_id'];
					$this->_user_unick = $user_info['user_nick'];
					$this->_user_uname = $user_uname;
					$ret = true;
				}else{
					$this->_error_code = $user_info['err_code'];
					$this->_error_des = $user_info['err_msg'];
				}
			}
		}
		return $ret;
	}

	///////////////////////////////////////////////
	//功　	能：用户登陆
	//入口参数：$user_uname - 用户名
	//			$user_pwd - 用户密码[明文]
	//			$vkey - 验证码识别码
	//			$vcode- 验证码
	//返 回 值：true	- 登陆成功
	//			false	- 登陆失败
	//说　　明：
	///////////////////////////////////////////////
	public function user_login($user_uname,$user_pwd,$vkey,$vcode)
	{
		$ret = false;
		$t_data['act'] = "login_blue_web";
		$t_data['cp_appkey'] = $this->_cp_app_key;
		$t_data['user_name'] = $user_uname;
		$t_data['user_pwd'] = md5($user_pwd);
		$t_data['dev_id'] = "WEB_CP_".$this->getip_str();
		$t_data['vcode'] = $vcode;
		$t_data['vkey'] = $vkey;
		$t_data['sign'] = $this->getSign($t_data,$this->_cp_app_secret.$this->_sdk_secret_key);
		$params = http_build_query($t_data);
		$result = @file_get_contents($this->_BLUESDK_USERGATE_URL.$params);
		if(!empty($result))
		{
			$user_info = json_decode($result,true);
			if(!is_null($user_info))
			{
				if(strcmp($user_info['err_code'],"1") == 0)
				{
					$this->_user_token = $user_info['user_token'];
					$this->_user_uid = $user_info['user_id'];
					$this->_user_unick = $user_info['user_nick'];
					$this->_user_uname = $user_uname;
					$ret = true;
				}else{
					$this->_error_code = $user_info['err_code'];
					$this->_error_des = $user_info['err_msg'];
				}
			}
		}
		return $ret;
	}

	///////////////////////////////////////////////
	//功　	能：用户注销退出
	//入口参数：$token	- 令牌
	//返 回 值：true	- 注销成功
	//			false	- 注销失败
	//说　　明：建议使用登陆接口的CP，在用户退出时调用注销接口
	///////////////////////////////////////////////
	public function user_logout($token)
	{
		$ret = false;
		$t_data['act'] = "logout_blue_web";
		$t_data['cp_appkey'] = $this->_cp_app_key;
		$t_data['user_token'] = $token;		
		$t_data['sign'] = $this->getSign($t_data,$this->_cp_app_secret.$this->_sdk_secret_key);
		$params = http_build_query($t_data);
		$result = @file_get_contents($this->_BLUESDK_USERGATE_URL.$params);
		if(!empty($result))
		{
			$user_info = json_decode($result,true);
			if(!is_null($user_info))
			{
				if(strcmp($user_info['err_code'],"1") == 0)
				{
					$ret = true;
				}else{
					$this->_error_code = $user_info['err_code'];
					$this->_error_des = $user_info['err_msg'];
				}
			}
		}
		return $ret;
	}

	///////////////////////////////////////////////
	//功　	能：检测帐号是否存在
	//入口参数：$user_uname - 用户名
	//返 回 值：true	- 帐号存在
	//			false	- 帐号不存在
	//说　　明：
	///////////////////////////////////////////////
	public function check_uname_exist($user_uname)
	{
		$ret = false;
		$t_data['act'] = "check_uname_blue_web";
		$t_data['user_name'] = $user_uname;
		$t_data['cp_appkey'] = $this->_cp_app_key;
		$t_data['sign'] = $this->getSign($t_data,$this->_cp_app_secret.$this->_sdk_secret_key);
		$params = http_build_query($t_data);
		$result = @file_get_contents($this->_BLUESDK_USERGATE_URL.$params);
		if(!empty($result))
		{
			$user_info = json_decode($result,true);
			if(!is_null($user_info))
			{
				if(strcmp($user_info['err_code'],"1") == 0)
				{
					if($user_info['exist'] == 1)
					{
						$ret = true;
					}
				}else{
					$this->_error_code = $user_info['err_code'];
					$this->_error_des = $user_info['err_msg'];
				}
			}
		}
		return $ret;
	}

	///////////////////////////////////////////////
	//功　	能：获取用户行为数据
	//入口参数：$req_data	- 用户自定义数据
	//出口参数：$respone_data - 用户自定义返回数据结果[仅当调用成功时才有值]
	//返 回 值：true	- 获取成功
	//			false	- 获取失败
	//说　　明：建议使用登陆接口的CP，在用户退出时调用注销接口
	///////////////////////////////////////////////
	public function get_user_stat_data($req_data,&$respone_data)
	{
		$ret = false;
		$t_data['act'] = "get_stat_data_blue_web";
		$t_data['cp_appkey'] = $this->_cp_app_key;
		$t_data['data'] = $req_data;		
		$t_data['sign'] = $this->getSign($t_data,$this->_cp_app_secret.$this->_sdk_secret_key);
		$params = http_build_query($t_data);
		$result = @file_get_contents($this->_BLUESDK_USERSTAT_URL.$params);
		if(!empty($result))
		{
			$user_info = json_decode($result,true);
			if(!is_null($user_info))
			{
				if(strcmp($user_info['err_code'],"1") == 0)
				{
					$respone_data = $user_info['data'];
					$ret = true;
				}else{
					$this->_error_code = $user_info['err_code'];
					$this->_error_des = $user_info['err_msg'];					
				}
			}
		}
		return $ret;
	}

	/////////////////////////////////////////////////
	//功　　能：生成签名
	//入口参数：$params		- 待签名字段数组
	//			$appSecret	- 签名私钥
	//返 回 值：签名后的md5码
	/////////////////////////////////////////////////
	private function getSign($params, $appSecret)
    {
        $processedParams = array();
        foreach ($params as $k => $v) {
            if (empty($v)) {
                continue;
            }

            $processedParams[$k] = $v;
        }
        ksort($processedParams);
        $signStr = join('#', $processedParams) . '#' . $appSecret;
        return md5($signStr);
    }

	/////////////////////////////////////////////////
	//功　　能：写log
	//入口参数：$log - 日志内容
	//返 回 值：
	//说    明：可按需自行修改格式或功能
	/////////////////////////////////////////////////
    private function _writeLog($log) {
        if (BLUE_TEST_FLAG) {
            echo $log . "<br />";
			/*
			$fp = fopen("sms_log.log","a+");
			if($fp)
			{
				fwrite($fp,$log);
				fclose($fp);
			}*/
        }
    }

	/////////////////////////////////////////////////
	//功　　能：获取当前访问IP
	//入口参数：
	//返 回 值：[xxx.xxx.xxx.xxx]格式IP地址
	/////////////////////////////////////////////////
	public static function getip_str() 
	{
		if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && ($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			 return $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		elseif(isset($_SERVER['HTTP_CLIENT_IP']) && ($_SERVER['HTTP_CLIENT_IP'])) {
			 return $_SERVER['HTTP_CLIENT_IP'];
		}
		else{
			 return $_SERVER['REMOTE_ADDR'];
		}
	}

	/////////////////////////////////////////////////
	//功　　能：将IP转换为数字
	//入口参数：
	//返 回 值：无符号整型数
	/////////////////////////////////////////////////
	public static function ipton($ip){
		$ip_arr=explode('.',$ip);//分隔ip段
		$ipstr="";
		foreach ($ip_arr as $value)
		{
			$iphex=dechex($value);
			if(strlen($iphex)<2){
				$iphex='0'.$iphex;
			}
			$ipstr.=$iphex;
		}
		return hexdec($ipstr);
	}
}
?>
