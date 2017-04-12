<?php
//功能操作类
//wl 2016-09-13

class multiPorts{

	//日志级别	
	const LOG_LEVL_NO = 0;					//无日志
	const LOG_LEVL_ERROR = 0x01;			//错误日志
	const LOG_LEVL_WARNING = 0x02;			//警告日志
	const LOG_LEVL_DEBUG = 0x04;			//调式日志

	//日志文件保存路径
	private $_logPath = "";
	//日志文件名称
	private $_logFileName = 'default_log.log';
	//日志级别开关
	private $_logLevel = 0;
	//日志会话索引
	private $_log_sess = "";
	//日志条数索引
	private $_log_index = 0;

	//错误码
	private $_error_code = "";
	//错误描述
	private $_error_des = "";

	//获得错误码
	public function get_error_code()
	{
		return $this->_error_code;
	}
	//获得错误描述
	public function get_error_des()
	{
		return $this->_error_des;
	}

	//获取菜单数组键值
	private function get_menu_keys($menu_index_arr)
	{
		$ret = array();
		foreach($menu_index_arr as $index => $child)
		{
			$ret[]=$index;
			if(!empty($child))
			{
				$tmp_arr = $this->get_menu_keys($child);
				$ret = array_merge ($ret, $tmp_arr);
			}
		}
		return $ret;
	}

	//判断键值是否存在
	private function get_valid_menu_keys(&$menu_index_arr,$right_list,&$right_result)
	{
		foreach($menu_index_arr as $index => $child)
		{
			//权限键值存在，则保留
			if(in_array($index,$right_list))
			{
				if(isset($child['childMenu']) && is_array($child['childMenu']) && !empty($child['childMenu']))
				{
					$t_child = array();
					$this->get_valid_menu_keys($child['childMenu'],$right_list,$t_child);
					$child['childMenu'] = $t_child;
				}

				$right_result[] = $child;
			}
		}
	}

	//权限转换
	private function trans_menu_data($type,$menu_index_arr,$parent_menu_info=array())
	{
		$trans_right = array();
		//取出所有权限键名
		$right_list = $this->get_menu_keys($menu_index_arr);		

		$filename = ALL_MENU_PATH.'menu_data.php';
		//判断权限菜单是否存在
		if(file_exists($filename))
		{
			$all_right = include($filename);
			if(!empty($all_right[$type]))
			{
				$this->get_valid_menu_keys($all_right[$type],$right_list,$trans_right);

			}
		}
		return $trans_right;
	}

	//获得我的权限菜单
	public function get_my_menu()
	{
		$ret = array();
		$admin_id = isset($_SESSION['HTC_LOGIN_DATA']['ad_id'])?$_SESSION['HTC_LOGIN_DATA']['ad_id']:'';
		$admin_tid = isset($_SESSION['HTC_LOGIN_DATA']['ad_tid'])?$_SESSION['HTC_LOGIN_DATA']['ad_tid']:'';
		$admin_type = (empty($admin_tid))?"admin":"merchant";
		if($admin_id)
		{
			$filename = ADMIN_RIGTHS_PATH.$admin_id.'.php';
			//判断权限菜单是否存在
			if(file_exists($filename))
			{
				$right_index = include($filename);
				
				//根据权限索引，转换成需要的模式
				$ret = $this->trans_menu_data($admin_type,$right_index);


				$this->_error_code = Core_Exception::CODE_DEAL_OK;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_FILE_NOT_EXISTS;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_FILE_NOT_EXISTS);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_USER_NOT_LOGIN;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_USER_NOT_LOGIN);
		}
		return $ret;
	}
	//获得所有菜单
	public function get_menu()
	{
		$ret = array();
		$filename = ALL_MENU_PATH.'menu_data.php';
		//判断权限菜单是否存在
		if(file_exists($filename))
		{
			$ret = include($filename);
			$this->_error_code = Core_Exception::CODE_DEAL_OK;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_FILE_NOT_EXISTS;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_FILE_NOT_EXISTS);
		}
		return $ret;
	}
	//获得不同类型管理员菜单
	public function gey_type_Menu($ad_type)
	{
		$data_arr = array();
		if($ad_type)
		{
			$filename = ALL_MENU_PATH.'menu_data.php';
			//判断权限菜单是否存在
			if(file_exists($filename))
			{
				$menu_arr = include($filename);
				$type_key = '';
				switch($ad_type)
				{
					case 1:
					$type_key = 'admin';
					break;
					case 2:
					$type_key = 'merchant';
					break;
				}
				$arr = $menu_arr[$type_key];
				if(!empty($arr))
				{
					foreach($arr as $menu_id=>$menu_info)
					{
						$tmp_arr = array();
						$tmp_arr['type'] = $menu_info['type'];
						$tmp_arr['menu_ext'] = '';
						//有二级菜单
						if(isset($menu_info['childMenu']) && !empty($menu_info['childMenu']))
						{
							foreach($menu_info['childMenu'] as $child_menu_id=>$num_arr)
							{
								if(!empty($num_arr))
								{
									$tmp_arr['menu_ext'] = $menu_id;
									$tmp_arr['menu_id'] = $child_menu_id; 
									$tmp_arr['menuText'] = $num_arr['menuText'];
									$tmp_arr['url'] = $num_arr['url'];
									if(isset($num_arr['default']))
										$tmp_arr['default'] = true;

									$data_arr[] = $tmp_arr;
								}
							}
						}
						else
						{
							$tmp_arr['menu_id'] = $menu_id; 
							$tmp_arr['menuText'] = $menu_info['menuText'];
							$tmp_arr['url'] = $menu_info['url'];
							if(isset($menu_info['default']))
								$tmp_arr['default'] = true;

							$data_arr[] = $tmp_arr;
						}
					}
				}
				$this->_error_code = Core_Exception::CODE_DEAL_OK;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_FILE_NOT_EXISTS;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_FILE_NOT_EXISTS);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_BAD_PARAM;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_BAD_PARAM);
		}
		return $data_arr;
	}
	//创建菜单
	public function create_menu($menu_data)
	{
		$ret = false;
		//判断是否有权限操作本接口
		if(check_admin())
		{
			if(!empty($menu_data))
			{
				$num = 1;
				$data_arr = array();
				//将传入的菜单数据按类型整理
				foreach($menu_data as $num_arr)
				{
					//每一条菜单数据必须包含菜单所属管理员类型(ad_type),菜单唯一ID(menu_id),菜单名称(menu_text),菜单地址(url),是否将此菜单设置为登录首页(index_flag 1 是 0 不是)，菜单额外属性（menu_ext 为空代表一级菜单，非空代表父级菜单ID和菜单名称如（45454:aaaaa）,以后可扩展为3级菜单例如14545_646256）
					if(isset($num_arr['ad_type']) && isset($num_arr['menu_id']) && isset($num_arr['menu_text']) && isset($num_arr['url']) && isset($num_arr['menu_ext']) && isset($num_arr['index_flag']) && !empty($num_arr['ad_type']) && !empty($num_arr['menu_id']) && !empty($num_arr['menu_text']))
					{
						$ad_type = '';
						switch($num_arr['ad_type'])
						{
							case 1:
							$ad_type = 'admin';
							break;
							case 2:
							$ad_type = 'merchant';
							break;
							break;
							default :
							break;
						}
						if(!empty($ad_type))
						{
							//若是一级菜单
							if(empty($num_arr['menu_ext']))
							{
								$menu_id = (string)$num_arr['menu_id'];
								//$data_arr[$ad_type][$menu_id]['type'] = $num;
								$data_arr[$ad_type][$menu_id]['type'] = $num_arr['menu_ext'];
								$data_arr[$ad_type][$menu_id]['menuText'] = $num_arr['menu_text'];
								$data_arr[$ad_type][$menu_id]['url'] = $num_arr['url'];
								//var_dump($data_arr[$ad_type][$num_arr['menu_id']]['menuText']);
								if($num_arr['index_flag'])
									$data_arr[$ad_type][$menu_id]['default'] = true;
								$num++;
							}
							else //二级菜单
							{
								$menu_id = (string)$num_arr['menu_id'];

								$ext_arr = explode(':',$num_arr['menu_ext']);
								
								if(!isset($data_arr[$ad_type][$ext_arr[0]]['type']))
								{
									//$data_arr[$ad_type][$ext_arr[0]]['type'] = $num;
									$data_arr[$ad_type][$menu_id]['type'] = $num_arr['menu_ext'];
									$num++;
								}
								
								$data_arr[$ad_type][$ext_arr[0]]['menuText'] = $ext_arr[1];
								$data_arr[$ad_type][$ext_arr[0]]['childMenu'][$menu_id]['menuText'] = $num_arr['menu_text'];
								$data_arr[$ad_type][$ext_arr[0]]['childMenu'][$menu_id]['url'] = $num_arr['url'];
								//var_dump($data_arr[$ad_type][$ext_arr[0]]['childMenu'][$num_arr['menu_id']]['menuText']);
								if($num_arr['index_flag'])
									$data_arr[$ad_type][$ext_arr[0]]['childMenu'][$menu_id]['default'] = true;
							}
						}
					}
					else
					{
						$this->_error_code = Core_Exception::CODE_BAD_PARAM;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_BAD_PARAM);
						Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
					}
				}
				$str = "<?php\r\n return ".var_export($data_arr,true).";\r\n?>";
				//将菜单写入文件
				$filename = ALL_MENU_PATH.'menu_data.php';
				$ret = file_put_contents($filename,$str);
				if($ret)
				{
					$this->_error_code = Core_Exception::CODE_DEAL_OK;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_FORMDATA_BAD;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_FORMDATA_BAD);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_NO_POWER;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_NO_POWER);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $ret;
	}
	//创建管理员权限菜单
	public function create_admin_Menu($ad_id,$menu_data)
	{
		$ret = false;
		//判断是否有权限操作本接口
		if(check_admin())
		{
			if(!empty($menu_data))
			{
				$data_arr = array();
				//将传入的菜单数据按类型整理
				foreach($menu_data as $num_arr)
				{
					//每一条菜单数据必须包含菜单所属,菜单唯一ID(menu_id),菜单额外属性（menu_ext 为空代表一级菜单，非空代表父级菜单ID（45454）,以后可扩展为3级菜单例如14545_646256）
					if(isset($num_arr['menu_id']) && isset($num_arr['menu_ext']) && !empty($num_arr['menu_id']))
					{
						//若是一级菜单
						if(empty($num_arr['menu_ext']))
						{
							$data_arr[$num_arr['menu_id']] = array();
						}
						else //二级菜单
						{
							$data_arr[$num_arr['menu_ext']][$num_arr['menu_id']] = array();
						}
					}
					else
					{
						$this->_error_code = Core_Exception::CODE_BAD_PARAM;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_BAD_PARAM);
						Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
					}
				}
				$str = "<?php\r\n return ".var_export($data_arr,true).";\r\n?>";

				//将菜单写入文件
				$filename = ADMIN_RIGTHS_PATH.$ad_id.'.php';
				//$filename = ADMIN_RIGTHS_PATH.'test.php';
				$ret = file_put_contents($filename,$str);
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_FORMDATA_BAD;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_FORMDATA_BAD);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_NO_POWER;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_NO_POWER);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $ret;
	}
	//获得所有菜单中某个管理员已有的权限菜单
	public function get_my_power_menu($data)
	{
		$data_arr = array();
		//判断是否有权限操作本接口
		if(check_admin())
		{
			if(isset($data['ad_id']) && !empty($data['ad_id']) && isset($data['ad_type']) && !empty($data['ad_type']))
			{
				$admin = new classAdmin();
				if($data['ad_type'] == 2)
				{
					$ad_id = $admin->get_adminid_by_tidandtype($data['ad_id'],$data['ad_type']);					
				}else{
					$ad_id = $data['ad_id'];
				}
				//获得该类型管理员的所有权限菜单
				$menu_type_arr = $this->gey_type_Menu($data['ad_type']);
				//获得该管理员的所有权限菜单
				$admin_menuid_arr = array();
				$filename = ADMIN_RIGTHS_PATH.$ad_id.'.php';
				if(file_exists($filename))
				{
					$admin_menu = include($filename);
					//取出所有权限键名
					$admin_menuid_arr = $this->get_menu_keys($admin_menu);
				}
				if(!empty($menu_type_arr))
				{
					foreach($menu_type_arr as $num_arr)
					{
						$tmp_arr = array();
						$tmp_arr['type'] = $num_arr['type'];
						$tmp_arr['menu_ext'] = $num_arr['menu_ext'];
						$tmp_arr['menu_id'] = $num_arr['menu_id']; 
						$tmp_arr['menuText'] = $num_arr['menuText'];
						$tmp_arr['url'] = $num_arr['url'];
						if(isset($num_arr['default']))
							$tmp_arr['default'] = true;
						if(in_array($num_arr['menu_id'],$admin_menuid_arr))
							$tmp_arr['checked'] = true;
						else
							$tmp_arr['checked'] = false;
						$data_arr[] = $tmp_arr;
					}
				}
				else
				{
					$this->_error_code = Core_Exception::CODE_ERROR_ADMIN_TYPE;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_ERROR_ADMIN_TYPE);
					Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_BAD_PARAM;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_BAD_PARAM);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_NO_POWER;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_NO_POWER);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $data_arr;
	}
	//商户申请提现
	public function merchant_apply_money($data_arr)
	{
		$ret = false;
		//判断是不是商户
		if(check_merchant())
		{
			$mid = isset($_SESSION['HTC_LOGIN_DATA']['ad_tid'])?$_SESSION['HTC_LOGIN_DATA']['ad_tid']:'';
			if($mid&&isset($data_arr['money']))
			{
				$admin = new classAdmin();
				//判断商户是否有提现账户
				$merchant_info = $admin->get_merchant_info_byid($mid);
				$account = isset($merchant_info['account'])?$merchant_info['account']:'';
				if($account)
				{
					$flag = false;

					$data_operation = new data_operation();
					
					//获得商户的提现要求
					$demands_info = $data_operation->get_merchant_demands($mid);
					
					if(!empty($demands_info))
					{
						//获得商户最近一次的提现记录
						$bill_record = $data_operation->get_merchant_bill_record_last($mid);
						
						if(!empty($bill_record))
						{
							$day = floor((time() - $bill_record['ctime'])/86400);
							//超过指定额度且两次提现时间间隔达到知道值才可以提现
							if(($data_arr['money']>=$demands_info['money'])&&($day>=$demands_info['time']))
								$flag = true;
						}
						else
						{
							//超过指定额度才可以提现
							if($data_arr['money']>=$demands_info['money'])
								$flag = true;
						}
					}
					else
					{
						$this->_error_code = Core_Exception::CODE_MERCHANT_DEMANDS_FAIL;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_MERCHANT_DEMANDS_FAIL);
						Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
					}

					if($flag)
					{
						//判断是否已在提现，每个商户不允许同时提现
						if($data_operation->check_mercahnt_balance($mid))
						{
							//锁定提现状态，不允许同一帐号同时多人提现
							if($data_operation->update_mercahnt_balance_docker($mid,$mid))
							{
								$data_arr['mid'] = $mid;
								//提现
								if($data_operation->add_bill_pay_record($data_arr))
								{
									//清除锁定状态
									$data_operation->update_mercahnt_balance_docker($mid,0);

									$this->_error_code = Core_Exception::CODE_DEAL_OK;
									$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
									$ret = true;
								}
								else
								{
									$this->_error_code = $data_operation->get_error_code();
									$this->_error_des = $data_operation->get_error_des();
									Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_WARNING);
									//清除锁定状态
									$data_operation->update_mercahnt_balance_docker($mid,0);
								}
							}
							else
							{
								$this->_error_code = Core_Exception::CODE_MERCHANT_DOCKER_FAIL;
								$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_MERCHANT_DOCKER_FAIL);
								Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
							}
						}
						else
						{
							$this->_error_code = Core_Exception::CODE_MERCHANT_NOT_BILL;
							$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_MERCHANT_NOT_BILL);
							Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
						}
					}
					else
					{
						$this->_error_code = Core_Exception::CODE_NOT_REACH_DEMANDS;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_NOT_REACH_DEMANDS);
						Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_WARNING);
					}
				}
				else
				{
					$this->_error_code = Core_Exception::CODE_MERCHANT_NOT_ACCOUNT;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_MERCHANT_NOT_ACCOUNT);
					Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_FORMDATA_BAD;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_FORMDATA_BAD);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_NO_POWER;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_NO_POWER);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $ret;
	}
	//生成激活码
	public function create_activity_code()
	{
		$ret = false;
		//判断是不是管理员
		if(check_admin())
		{
			$ret = true;
			$this->_error_code = Core_Exception::CODE_DEAL_OK;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
			//调用函数生成激活码，每次生成1000个
			for($i=1;$i<=CREATE_ACTIVITY_CODE_NUMS;$i++)
			{
				$code = create_activity_code($i);
				//将激活码写入激活表
				$data_operation = new data_operation();
				$data_operation->insert_activity_code($code);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_NO_POWER;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_NO_POWER);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $ret;
	}
}
?>