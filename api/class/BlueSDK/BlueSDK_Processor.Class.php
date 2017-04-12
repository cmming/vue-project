<?php
////////////////////////////////////////////////////////
// ���ܣ�CP���ջ���ƽ̨֧���첽֪ͨ������Demo
// �汾��1.0
// ���ڣ�2014-07-11 
// ˵����
//		 ���´���ֻ��Ϊ�˷���CP���Զ��ṩ���������룬CP���Ը����Լ���վ����Ҫ�����ռ����ĵ���д,����һ��Ҫʹ�øô���
//		 �ô������ѧϰ���о�����ƽ̨SDK�ӿ�ʹ�ã�ֻ���ṩһ���ο���
//		����deal_cp_app_logic()����Ϊ�̻�ҵ���߼�������
// ��ʽ���ߣ�	LOG_FLAG Ϊ��־���ء�
//				write_log()Ϊ�ı���־�������
//				$_logPath Ϊ��־�ļ�����ȫ·��[�����ļ���]
////////////////////////////////////////////////////////
class BlueSDK_Processor
{
	const PT_WXPAY_NATIVE = 102;		//΢��ɨ��֧��
	const PT_ALIPAY_F2F = 105;			//֧����ɨ��֧��

	private $_QUERY_ORDER_URL = "http://api.lgshouyou.com/QueryOrder.php?";	//����У���ַ
	private $_GET_ORDER_URL = "http://api.lgshouyou.com/GetOrder.php?";		//���󶩵����ɵ�ַ
	//const QUERY_ORDER_URL = "http://192.168.1.4:8080/CoreSDK/QueryOrder.php?";
	//const QUERY_ORDER_URL = "http://192.168.1.250:8080/blue/api/QueryOrder.php?";	//����У���ַ

	const LOG_FLAG = false;							//��־����
	private $_logPath = 'BlueSDK_Pay_Recv.log';		//��־·��

	///////////////////////////////////////////////
	private $_cp_app_id = "";		//����ƽ̨�����CP��Ӧ�ñ��
	private $_cp_app_key = "";		//����ƽ̨�����CP��Ӧ�ñ�ʶ
	private $_cp_app_secret = "";	//����ƽ̨�����CP��Ӧ�õ�ͨ��Ϣ��Կ	
	private $_sdk_secret_key = '';	//ƽ̨�ڲ�������Կ
	///////////////////////////////////////////////

	public $_pay_user_id = "";		//����ƽ̨�����û�������id
	public $_pay_type = "";			//����ƽ̨�۷ѷ�ʽ��ʶ
	public $_order_id = "";			//����ƽ̨���ɵĶ������
	public $_amount = 0;			//��������Ʒ���, �Է�Ϊ��λ.  
	public $_qrcode = "";			//����ͼԴ��ά�����ݵ�ַ


	public $_cp_oid = "";			//CP���������
	public $_cp_product_id = "";	//CP����Ʒ���
	public $_cp_uid = "";			//CP���û�����id
	public $_cp_uname = "";			//CP���û��ʺ�
	public $_cp_unick = "";			//CP���û��ǳƻ��ɫ���[�����д����]
	public $_cp_svrid = "";			//CP���λ�Ӧ�õķ��������[�����д����]
	public $_ext = "";				//�û��Զ��������͸������,���лص�����ԭ������

	private $_data = "";			//�յ��Ķ������ݣ�����У����

	//������
	public $_error_code = "";
	//��������
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
	//�������ܣ�д��־
	//��ڲ�����$msg	- ��־����
	//�� �� ֵ����
	//˵��������
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
	//����	�ܣ�������Խ��
	//��ڲ�����$result - true:�ڲ�����ɹ� false:�ڲ�����ʧ��
	//˵��������
	///////////////////////////////////////////////
	public function echo_result($result)
	{
		if($result)
		{
			//����ɹ������յ�����ƽ̨Ҫ�����
			echo "success";
		}else{
			//����ʧ�ܣ����յ�����ƽ̨Ҫ�����,Ҳ������ѡ�����������
			echo "fail";
		}
	}

	///////////////////////////////////////////////
	//����	�ܣ���ȡ������Խ��
	//��ڲ�����$result - true:�ڲ�����ɹ� false:�ڲ�����ʧ��
	//˵��������
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
	//�������ܣ�����ǩ��
	//��ڲ�����$params		- ��ǩ���ֶ�����
	//			$appSecret	- ǩ��˽Կ
	//�� �� ֵ��ǩ�����md5��
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
		$this->write_log("ǩ���ַ���:".$signStr);
        return md5($signStr);
    }

	///////////////////////////////////////////////
	//����	�ܣ��������ɶ���
	//��ڲ�����$pay_type - ֧������
	//			$user_id - �û���ƽ̨�������ֱ��
	//			$amount- ֧������λ�֡�
	//			$product_id - ��Ʒ���
	//			$cp_uid - �û���cp��������id
	//			$cp_uname - �û���cp�����ʺ�
	//			$product_name - ��Ʒ���ƻ�����
	//			$cp_oid - cp���������
	//			$cp_unick - �û���cp���ǳ�
	//			$cp_svrid - �û���ֵ��Ϸ�������
	//			$notify_url - ֧���ɹ�֪ͨ��ַ
	//			$ext - ͸������
	//�� �� ֵ��true	- ���ݺϷ��������ɹ� 
	//			false	- ���ݷǷ�������ʧ��
	//˵�������������֧���߼��Ķ������ٴε��ô˽ӿ�ʱ�������ز��Ϸ�
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
	//����	�ܣ���֤������֧��֪ͨ���ݺϷ��ԣ�
	//��ڲ�����$params		- ���������
	//			$secret_key	- ��֧�������Խӵļ�����Կ
	//�� �� ֵ��true	- ���ݺϷ��������ɹ� 
	//			false	- ���ݷǷ�������ʧ��
	//˵��������
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
						//������
						$this->_pay_user_id = $params['user_id'];		//����ƽ̨�����û�������id
						$this->_pay_type = $params['pay_type'];			//����ƽ̨�۷ѷ�ʽ��ʶ
						$this->_order_id = $params['order_id'];			//����ƽ̨���ɵĶ������
						$this->_amount = $params['amount'];				//��������Ʒ���, �Է�Ϊ��λ.  
						$this->_cp_product_id = $params['product_id'];	//CP����Ʒ���
						$this->_cp_uid = $params['cp_uid'];				//CP���û�����id
						$this->_cp_uname = $params['cp_uname'];			//CP���û��ʺ�
						$this->_cp_oid = isset($params['cp_oid'])?$params['cp_oid']:"";					//CP���������				
						$this->_cp_unick =  isset($params['cp_unick'])?$params['cp_unick']:"";			//CP���û��ǳƻ��ɫ���[�����д����]
						$this->_cp_svrid =  isset($params['cp_svrid'])?$params['cp_svrid']:"";			//CP���λ�Ӧ�õķ��������[�����д����]
						$this->_ext =  isset($params['ext'])?$params['ext']:"";							//�û��Զ��������͸������,���лص�����ԭ������
						$ret = true;
					}else{
						$this->write_log("�������Ϸ�");
					}					
				}else{
					$this->write_log("ǩ����ͬ");
				}
			}else{
				$this->write_log("app_key��ͬ");
			}			
		}else{
			$this->write_log("���ݲ�����");
		}

		return $ret;
	}

	///////////////////////////////////////////////
	//����	�ܣ�У�鶩���Ƿ�Ϸ�
	//��ڲ�����
	//�� �� ֵ��true	- ���ݺϷ��������ɹ� 
	//			false	- ���ݷǷ�������ʧ��
	//˵�������������֧���߼��Ķ������ٴε��ô˽ӿ�ʱ�������ز��Ϸ�
	///////////////////////////////////////////////
	public function check_order_valid()
	{
		$ret = false;
		$args = http_build_query($this->_data);
		$check_data = @file_get_contents($this->_QUERY_ORDER_URL.$args);
		$this->write_log("��֤����:".$check_data);
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
			$this->write_log("��֤����ʧ��");
		}

		return $ret;
	}
}
?>
