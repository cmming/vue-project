<?php
///////////////////////////////////////////////////
//17�ο���ƽ̨�û���֤����Ȩ���Žӿڿ�
//bull 2014-07-03 
/////////////////////////////////////////////////
class BlueSDK_Pro
{
	/////////////////////////////////////////////////
	//��������Ҫ�޸�
	/////////////////////////////////////////////////
	//ƽ̨�ڲ�������Կ
	private $_sdk_secret_key = '';

	//�ӿڵ�ַ
	private $_BLUESDK_USERGATE_URL = "http://api.lgshouyou.com/UserGate.php?";	
	private $_BLUESDK_USERSTAT_URL = "http://api.lgshouyou.com/UserStat.php?";	
	private $_BLUESDK_GET_CODE_URL = "http://api.lgshouyou.com/vcode/vcode.php?";
	private $_BLUESDK_GET_SMS_URL = "http://api.lgshouyou.com/SMSGate.php?";	

	/////////////////////////////////////////////////
	//ƽ̨�����CP��������Ϣ,��Ҫ����
	/////////////////////////////////////////////////
	private $_cp_app_id = "";		//����ƽ̨�����CP��Ӧ�ñ��
	private $_cp_app_key = "";		//����ƽ̨�����CP��Ӧ�ñ�ʶ
	private $_cp_app_secret = "";	//����ƽ̨�����CP��Ӧ�õ�ͨ��Ϣ��Կ
	///////////////////////////////////////////////
	
	//�û�����
	private $_user_token = "";
	//�û����ݱ��
	private $_user_uid = 0;
	//�û��ǳ�
	private $_user_unick = "";
	//�û��ʺ�[����Ϊ��]
	private $_user_uname = "";
	//�û�֤������
	private $_user_cardid = "";
	//�û��绰��[����Ϊ��]
	private $_user_phone = "";
	//�û�QQ����[����Ϊ��]
	private $_user_qq = "";
	//�û�����[����Ϊ��]
	private $_user_score = 0;
	//�û��ʼĵ�ַ[����Ϊ��]
	private $_user_addr = "";
	//�û�E-mail[����Ϊ��]
	private $_user_email = "";

	//������
	private $_error_code = "";
	//��������
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
	//����	�ܣ���ȡ�û�����id
	//��ڲ�����
	//�� �� ֵ���û�����id
	//˵��������
	///////////////////////////////////////////////
	public function get_user_uid()
	{
		return $this->_user_uid;
	}

	///////////////////////////////////////////////
	//����	�ܣ���ȡ�û��ǳ�
	//��ڲ�����
	//�� �� ֵ���û��ǳ�
	//˵��������
	///////////////////////////////////////////////
	public function get_user_unick()
	{
		return $this->_user_unick;
	}

	///////////////////////////////////////////////
	//����	�ܣ���ȡ�û��ʺ�
	//��ڲ�����
	//�� �� ֵ���û��ǳ�
	//˵��������
	///////////////////////////////////////////////
	public function get_user_uname()
	{
		return $this->_user_uname;
	}

	///////////////////////////////////////////////
	//����	�ܣ���ȡ�û�����
	//��ڲ�����
	//�� �� ֵ���û�����
	//˵��������
	///////////////////////////////////////////////
	public function get_user_token()
	{
		return $this->_user_token;
	}

	///////////////////////////////////////////////
	//����	�ܣ���ȡ�û�֤������
	//��ڲ�����
	//�� �� ֵ����֤������
	//˵��������
	///////////////////////////////////////////////
	public function get_user_cardid()
	{
		return $this->_user_cardid;
	}


	///////////////////////////////////////////////
	//����	�ܣ���ȡ�û��绰��
	//��ڲ�����
	//�� �� ֵ���û��绰��
	//˵��������
	///////////////////////////////////////////////
	public function get_user_phone()
	{
		return $this->_user_phone;
	}

	///////////////////////////////////////////////
	//����	�ܣ���ȡ�û�QQ����
	//��ڲ�����
	//�� �� ֵ���û�QQ����
	//˵��������
	///////////////////////////////////////////////
	public function get_user_qq()
	{
		return $this->_user_qq;
	}

	///////////////////////////////////////////////
	//����	�ܣ���ȡ�û��ʼĵ�ַ
	//��ڲ�����
	//�� �� ֵ���û��ʼĵ�ַ
	//˵��������
	///////////////////////////////////////////////
	public function get_user_addr()
	{
		return $this->_user_addr;
	}

	///////////////////////////////////////////////
	//����	�ܣ���ȡ�û�E-mail
	//��ڲ�����
	//�� �� ֵ���û�E-mail
	//˵��������
	///////////////////////////////////////////////
	public function get_user_email()
	{
		return $this->_user_email;
	}

	///////////////////////////////////////////////
	//����	�ܣ���ȡ�û�����
	//��ڲ�����
	//�� �� ֵ���û�����
	//˵��������
	///////////////////////////////////////////////
	public function get_user_score()
	{
		return $this->_user_score;
	}

	///////////////////////////////////////////////
	//����	�ܣ���ȡ������
	//��ڲ�����
	//�� �� ֵ�����һ�δ�����
	//˵��������
	///////////////////////////////////////////////
	public function get_error_code()
	{
		return $this->_error_code;
	}
	///////////////////////////////////////////////
	//����	�ܣ���ȡ��������
	//��ڲ�����
	//�� �� ֵ�����һ�δ������Ӧ����
	//˵��������
	///////////////////////////////////////////////
	public function get_error_des()
	{
		return $this->_error_des;
	}
	///////////////////////////////////////////////
	//����	�ܣ����������Ϣ
	//��ڲ�����
	//�� �� ֵ��
	//˵��������
	///////////////////////////////////////////////
	public function clear_error_info()
	{
		$this->_error_code="";
		$this->_error_des="";
	}

	///////////////////////////////////////////////
	//����	�ܣ�ͨ�����ƻ�ȡ�û���Ϣ
	//��ڲ�����$token	- ����
	//�� �� ֵ��true	- ���ݺϷ�
	//			false	- ���ݷǷ�
	//˵����������֤token�Ƿ���Ч������Ч�����ܻ�ȡ�û���Ϣ
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
	//����	�ܣ�ͨ�����ƻ�ȡ�û���ϸ��Ϣ
	//��ڲ�����$token	- ����
	//�� �� ֵ��true	- ��ȡ�ɹ�
	//			false	- ��ȡʧ��
	//˵��������
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
					
					//����ϸ��Ϣ����
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
	//����	�ܣ�ͨ�������޸��û���ϸ��Ϣ
	//��ڲ�����$token	- ����
	//			$data_arr - ��Ҫ�޸ĵ��������ݣ��������Ϊ�����޸ĵ��ֶΣ��� nick��phone��email
	//�� �� ֵ��true	- �޸ĳɹ�
	//			false	- �޸�ʧ��
	//˵��������
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
	//����	�ܣ��û���������
	//��ڲ�����$user_uname - �û���
	//			$user_pwd - �û�����[����]
	//			$vkey - ��֤��ʶ����
	//			$vcode - ��֤��
	//�� �� ֵ��true	- �޸ĳɹ�
	//			false	- �޸�ʧ��
	//˵��������
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
	//����	�ܣ�ͨ�����ƻ�ȡ�û��Ƹ���Ϣ
	//��ڲ�����$token	- ����
	//�� �� ֵ��true	- ��ȡ�ɹ�
	//			false	- ��ȡʧ��
	//˵��������
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
	//����	�ܣ�ͨ�������޸��û��Ƹ���Ϣ
	//��ڲ�����$uid	- �û����ֱ��
	//			$reason - �޸�ԭ��/����
	//			$oid	- �Ƹ�������������󷽵�Ψһ�����ţ���󳤶�32
	//			$score  - ���ֱ䶯����������ʾ������
	//			$diamond  - ��ʯ�䶯����������ʾ������	
	//�� �� ֵ��true	- �޸ĳɹ�
	//			false	- �޸�ʧ��
	//˵��������
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
	//����	�ܣ�������֤��
	//���ڲ�����$vkey - ��֤��ʶ����
	//			$vcode_img��- ��֤��ͼ����Ϣ
	//�� �� ֵ��true	- ��ȡ�ɹ�
	//			false	- ��ȡʧ��
	//˵��������$vkey����Ҫ��֤��ĵط�һ����Ҫ��
	//			����Ҫʹ�øù��ܿ���session�������ֵ
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
	//����	�ܣ������ֻ�ע����֤�������ַ
	//��ڲ�����$phone	- �û��ֻ�����
	//���ڲ�����$vkey - ��֤��ʶ����
	//			$ltimes - ���û��������ȡ��֤��Ĵ���
	//�� �� ֵ��true	- ��ȡ�ɹ�
	//			false	- ��ȡʧ��
	//˵��������$vkey����Ҫ��֤��ĵط�һ����Ҫ��
	//			����Ҫʹ�øù��ܿ���session�������ֵ
	///////////////////////////////////////////////
	public function get_reg_sms($phone,&$vkey,&$ltimes)
	{
		return $this->send_vcode("reg_sms_blue_web",$phone,$vkey,$ltimes);
	}
	
	///////////////////////////////////////////////
	//����	�ܣ������û���Ϣ�޸���֤��
	//��ڲ�����$phone	- �û��ֻ�����
	//���ڲ�����$vkey - ��֤��ʶ����
	//			$ltimes - ���û��������ȡ��֤��Ĵ���
	//�� �� ֵ��true	- ��ȡ�ɹ�
	//			false	- ��ȡʧ��
	//˵��������$vkey����Ҫ��֤��ĵط�һ����Ҫ��
	//			����Ҫʹ�øù��ܿ���session�������ֵ
	///////////////////////////////////////////////
	public function get_com_sms($phone,&$vkey,&$ltimes)
	{
		 return $this->send_vcode("get_com_sms_blue_web",$phone,$vkey,$ltimes);
	}
	///////////////////////////////////////////////
	//����	�ܣ���������֤�롾ͨ�á�
	//��ڲ�����$phone	- �û��ֻ����� �������ַ
	//���ڲ�����$vkey - ��֤��ʶ����
	//			$ltimes - ���û��������ȡ��֤��Ĵ���
	//�� �� ֵ��true	- ��ȡ�ɹ�
	//			false	- ��ȡʧ��
	//˵��������$vkey����Ҫ��֤��ĵط�һ����Ҫ��
	//			����Ҫʹ�øù��ܿ���session�������ֵ
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
	//����	�ܣ���֤��ʹ��[�ֻ�ע����֤��]
	//��ڲ�����$phone	- �û��ֻ�����
	//���ڲ�����$vkey - ��֤��ʶ����
	//			$vcode - ��֤��
	//�� �� ֵ��true	- ��֤��ʹ�óɹ�
	//			false	- ��֤ʹ��ʧ��
	//˵��������$vkey����Ҫ��֤��ĵط�һ����Ҫ��
	//			����Ҫʹ�øù��ܿ���session�������ֵ
	///////////////////////////////////////////////
	public function check_use_sms($phone,$vkey,$vcode)
	{
		return $this->send_vcode("check_sms_blue_web",$phone,$vkey,$vcode);		
	}

	///////////////////////////////////////////////
	//����	�ܣ���֤��ʹ��[ͨ����֤��]
	//��ڲ�����$phone	- �û��ֻ�����
	//���ڲ�����$vkey - ��֤��ʶ����
	//			$ltimes - ���û��������ȡ��֤��Ĵ���
	//�� �� ֵ��true	- ��ȡ�ɹ�
	//			false	- ��ȡʧ��
	//˵��������$vkey����Ҫ��֤��ĵط�һ����Ҫ��
	//			����Ҫʹ�øù��ܿ���session�������ֵ
	///////////////////////////////////////////////
	public function check_use_com_sms($phone,$vkey,$vcode)
	{
		 return $this->check_vcode("check_com_sms_blue_web",$phone,$vkey,$vcode);
	}


	///////////////////////////////////////////////
	//����	�ܣ���֤��ʹ����֤��[�ֻ���������֤��]
	//��ڲ�����$phone	- �û��ֻ�����
	//���ڲ�����$vkey - ��֤��ʶ����
	//			$vcode - ��֤��
	//�� �� ֵ��true	- ��֤��ʹ�óɹ�
	//			false	- ��֤ʹ��ʧ��
	//˵��������$vkey����Ҫ��֤��ĵط�һ����Ҫ��
	//			����Ҫʹ�øù��ܿ���session�������ֵ
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
	//����	�ܣ��û�ע��
	//��ڲ�����$user_uname - �û���
	//			$user_pwd - �û�����[����]
	//			$vkey - ��֤��ʶ����
	//			$vcode - ��֤��
	//�� �� ֵ��true	- ע��ɹ�
	//			false	- ע��ʧ��
	//˵��������ע��ɹ��󣬻��Զ���½������ȡ�û���Ϣ��
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
	//����	�ܣ��û���½
	//��ڲ�����$user_uname - �û���
	//			$user_pwd - �û�����[����]
	//			$vkey - ��֤��ʶ����
	//			$vcode- ��֤��
	//�� �� ֵ��true	- ��½�ɹ�
	//			false	- ��½ʧ��
	//˵��������
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
	//����	�ܣ��û�ע���˳�
	//��ڲ�����$token	- ����
	//�� �� ֵ��true	- ע���ɹ�
	//			false	- ע��ʧ��
	//˵������������ʹ�õ�½�ӿڵ�CP�����û��˳�ʱ����ע���ӿ�
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
	//����	�ܣ�����ʺ��Ƿ����
	//��ڲ�����$user_uname - �û���
	//�� �� ֵ��true	- �ʺŴ���
	//			false	- �ʺŲ�����
	//˵��������
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
	//����	�ܣ���ȡ�û���Ϊ����
	//��ڲ�����$req_data	- �û��Զ�������
	//���ڲ�����$respone_data - �û��Զ��巵�����ݽ��[�������óɹ�ʱ����ֵ]
	//�� �� ֵ��true	- ��ȡ�ɹ�
	//			false	- ��ȡʧ��
	//˵������������ʹ�õ�½�ӿڵ�CP�����û��˳�ʱ����ע���ӿ�
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
	//�������ܣ�����ǩ��
	//��ڲ�����$params		- ��ǩ���ֶ�����
	//			$appSecret	- ǩ��˽Կ
	//�� �� ֵ��ǩ�����md5��
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
	//�������ܣ�дlog
	//��ڲ�����$log - ��־����
	//�� �� ֵ��
	//˵    �����ɰ��������޸ĸ�ʽ����
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
	//�������ܣ���ȡ��ǰ����IP
	//��ڲ�����
	//�� �� ֵ��[xxx.xxx.xxx.xxx]��ʽIP��ַ
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
	//�������ܣ���IPת��Ϊ����
	//��ڲ�����
	//�� �� ֵ���޷���������
	/////////////////////////////////////////////////
	public static function ipton($ip){
		$ip_arr=explode('.',$ip);//�ָ�ip��
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
