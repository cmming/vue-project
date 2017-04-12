<?php
//管理员操作类
//wl 2016-09-13

class classAdmin{

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

	//mysql数据库对象
	protected $mysql=null;	
	//redis数据库对象
	protected $redis=null;

	//用户数据编号
	private $_user_uid = 0;
	//用户昵称
	private $_user_unick = "";
	//用户帐号
	private $_user_uname = "";
	//用户类行
	private $_user_type = "";
	//用户电话
	private $_user_phone = "";
	//非平台管理员ID
	private $_user_tid = "";
	//用户状态
	private $_user_state = "";
	//错误码
	private $_error_code = "";
	//错误描述
	private $_error_des = "";

	//初始化mysql数据库对象
	private function init_mysql_obj()
	{
		$ret = false;
		if($this->mysql == null)
		{
			$this->mysql = new Core_DBOper;
			//创建对象失败
			if($this->mysql == null)
			{
				//抛出异常，告知用户同时写入日志
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,"创建数据库对象失败",Core_Logger::LOG_LEVL_ERROR);
				throw new Core_Exception(Core_Exception::CODE_FAILED_CREATE_OBJ);
			}
			else
			{
				if($this->mysql->DB_Connect_CenterDB() == false)
				{
					Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,"连接数据库失败",Core_Logger::LOG_LEVL_ERROR);
					//抛出异常，告知用户同时写入日志
					throw new Core_Exception(Core_Exception::CODE_DB_CONNECT_ERROR);
				}
				else
					$ret = true;
			}
		}
		else
			$ret = true;

		return $ret;
	}
	//获得管理员ＩＤ
	public function get_adminid()
	{
		return $this->_user_uid;
	}
	//获得管理员帐号
	public function get_admin_uname()
	{
		return $this->_user_uname;
	}
	//获得管理员昵称
	public function get_admin_nick()
	{
		return $this->_user_unick;
	}
	//获得管理员类型
	public function get_admin_type()
	{
		return $this->_user_type;
	}
	//获得管理员角色
	public function get_admin_phone()
	{
		return $this->_user_phone;
	}
	//获得管理员角色ID 
	public function get_admin_TID()
	{
		return $this->_user_tid;
	}
	//获得管理员状态
	public function get_admin_state()
	{
		return $this->_user_state;
	}
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
	//验证登录
	public function login_confirm($uname,$pwd,$ad_type)
	{
		$result=false;
		if($this->init_mysql_obj())
		{
			$uname = mysql_real_escape_string($uname);
			$pwd=mysql_real_escape_string($pwd);
			$sql = 'select * from t_admin where ad_uname="'.$uname.'"';
			$result = $this->mysql->DB_Query($sql);
			if($result)
			{
				if($res_arr = $this->mysql->DB_fetch_array($result))
				{
					if($res_arr['ad_pwd']!=$pwd)
					{
						$this->_error_code = Core_Exception::CODE_FAIL_PWD;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_FAIL_PWD);
					}
					else if($res_arr['enable']<0)
					{
						$this->_error_code = Core_Exception::CODE_USER_DISABLED;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_USER_DISABLED);
					}
					else if($res_arr['enable']==0)
					{
						$this->_error_code = Core_Exception::CODE_UNAME_NO_ACTIVATE;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_UNAME_NO_ACTIVATE);
					}
					else if($res_arr['ad_type']!=$ad_type)
					{
						$this->_error_code = Core_Exception::CODE_ERROR_ADMIN_TYPE;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_ERROR_ADMIN_TYPE);
					}
					else
					{
						$result=true;
						$this->_error_code = Core_Exception::CODE_DEAL_OK;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
						//赋值
						$this->_user_uid = $res_arr['ad_id'];
						$this->_user_uname = $res_arr['ad_uname'];
						$this->_user_unick = $res_arr['ad_nick'];
						$this->_user_type = $res_arr['ad_type'];
						$this->_user_tid = $res_arr['ad_tid'];
						$this->_user_phone = $res_arr['phone'];
						$this->_user_state = $res_arr['enable'];
						//将数据存入redis数据库
						/*$key = $this->make_key_by_aid($res_arr['ad_id']);
						$data_arr = array('ad_id'=>$res_arr['ad_id'],'ad_uname'=>$res_arr['ad_uname'],'ad_nick'=>$res_arr['ad_nick'],'ad_type'=>$res_arr['ad_type'],'ad_tid'=>$res_arr['ad_tid'],'ad_role'=>$res_arr['ad_role'],'enable'=>$res_arr['enable']);
						$this->save_data_to_redis_hash($key,$data_arr);*/
						$_SESSION['HTC_LOGIN_DATA']['ad_id'] = $res_arr['ad_id'];
						$_SESSION['HTC_LOGIN_DATA']['ad_uname'] = $res_arr['ad_uname'];
						$_SESSION['HTC_LOGIN_DATA']['ad_nick'] = $res_arr['ad_nick'];
						$_SESSION['HTC_LOGIN_DATA']['ad_type'] = $res_arr['ad_type'];
						$_SESSION['HTC_LOGIN_DATA']['ad_tid'] = $res_arr['ad_tid'];
						$_SESSION['HTC_LOGIN_DATA']['phone'] = $res_arr['phone'];
					}
				}
				else
				{
					$this->_error_code = Core_Exception::CODE_USER_NOT_EXIST;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_USER_NOT_EXIST);
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
		}
		return $result;
	}
	//判断登录名是否存在
	public function check_admin_by_name($uname)
	{
		$result=false;
		if($this->init_mysql_obj())
		{
			$uname = mysql_real_escape_string($uname);
			$sql = 'select * from t_admin where ad_uname="'.$uname.'"';
			$result = $this->mysql->DB_Query_Res_And_Count($sql);
			if($result>0)
			{
				$result = true;
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_USER_NOT_EXIST;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_USER_NOT_EXIST);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
		}
		return $result;
	}
	// 判断商户是否有套餐
	public function check_package_by_mid($mid)
	{
		$result=false;
		if($this->init_mysql_obj())
		{
			$mid = mysql_real_escape_string($mid);
			$sql = 'select * from t_package where mid="'.$mid.'"';
			$result = $this->mysql->DB_Query_Res_And_Count($sql);
			if($result>0)
			{
				$result = true;
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
		}
		return $result;
	}
	//根据admin_id生成redis的key
	private static function make_key_by_aid($admin_id)
	{
		return "adminId:".$admin_id.":#";
	}
	//增加管理员
	public function add_admin($formdata)
	{
		$ret = false;
		if($this->init_mysql_obj())
		{
			if(isset($formdata['ad_uname'])&&isset($formdata['ad_nick'])&&isset($formdata['ad_pwd'])&&isset($formdata['ad_role'])&&isset($formdata['phone'])&&isset($formdata['menu_data']))
			{
				$menu_data = $formdata['menu_data'];
				unset($formdata['menu_data']);
				foreach($formdata as $key=>$val)
				{
					$key = mysql_real_escape_string($key);
					$val = mysql_real_escape_string($val);
					$formdata[$key] = $val;
				}
				//判断登录名是否重复
				if(!$this->check_admin_by_name($formdata['ad_uname']))
				{
					$ad_type = isset($formdata['ad_type'])?$formdata['ad_type']:1;
					$ad_tid = isset($formdata['ad_tid'])?$formdata['ad_tid']:0;
					$enable = 1; //默认可用
					$sql = 'insert into t_admin(ad_uname,ad_nick,ad_pwd,ad_type,ad_role,ad_tid,phone,enable) values("'.$formdata['ad_uname'].'","'.$formdata['ad_nick'].'","'.$formdata['ad_pwd'].'","'.$ad_type.'","'.$formdata['ad_role'].'","'.$ad_tid.'","'.$formdata['phone'].'","'.$enable.'")';
					if($this->mysql->DB_Query($sql))
					{
						$ad_id = $this->mysql->DB_insert_id();
						//写入管理员权限菜单
						$multiPorts = new multiPorts();
						$multiPorts->create_admin_Menu($ad_id,$menu_data);

						//操作成功写入操作记录表
						$data_arr = array('aid'=>$_SESSION['HTC_LOGIN_DATA']['ad_id'],'time'=>time(),'t_name'=>'t_admin','act'=>'add','data'=>json_encode($formdata));
						$data_oper = new data_operation();
						if($data_oper->insert_into_admin_log($data_arr))
						{
							$this->_error_code = Core_Exception::CODE_DEAL_OK;
							$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
							$ret = true;
						}
						else
						{
							$this->_error_code = Core_Exception::CODE_ADMIN_LOG_ERROR;
							$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_ADMIN_LOG_ERROR);
							Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
						}
					}
					else
					{
						$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
						Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
					}
				}
				else
				{
					$this->_error_code = Core_Exception::CODE_UNAME_EXISTS;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_UNAME_EXISTS);
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
		return $ret;
	}
	//增加商户
	public function add_merchant($formdata)
	{
		$ret = false;
		if($this->init_mysql_obj())
		{
			if(isset($formdata['uname'])&&isset($formdata['name'])&&isset($formdata['shopowner'])&&isset($formdata['sphone'])&&isset($formdata['mpnone'])&&isset($formdata['mpnone2'])&&isset($formdata['addr'])&&isset($formdata['area'])&&isset($formdata['staffs'])&&isset($formdata['equip'])&&isset($formdata['remarks'])&&isset($formdata['menu_data']))
			{
				$menu_data = $formdata['menu_data'];
				unset($formdata['menu_data']);
				foreach($formdata as $key=>$val)
				{
					$key = mysql_real_escape_string($key);
					$val = mysql_real_escape_string($val);
					$formdata[$key] = $val;
				}
				//判断商户名是否重复
				if(!$this->check_admin_by_name($formdata['uname']))
				{
					$pwd = md5('123456');
					$sql = 'insert into t_merchant(uname,name,shopowner,sphone,mpnone,mpnone2,addr,area,staffs,equip,remarks) values("'.$formdata['uname'].'","'.$formdata['name'].'","'.$formdata['shopowner'].'","'.$formdata['sphone'].'","'.$formdata['mpnone'].'","'.$formdata['mpnone2'].'","'.$formdata['addr'].'","'.$formdata['area'].'","'.$formdata['staffs'].'","'.$formdata['equip'].'","'.$formdata['remarks'].'")';
					if($this->mysql->DB_Query($sql))
					{
						$ad_tid = $this->mysql->DB_insert_id();		

						$admin_data_arr = array('ad_uname'=>$formdata['uname'],'ad_nick'=>$formdata['name'],'ad_pwd'=>$pwd,'ad_type'=>2,'ad_role'=>'店长','ad_tid'=>$ad_tid,'phone'=>$formdata['sphone'],'enable'=>1,'menu_data'=>$menu_data);
						//将结果存入管理员表
						if($this->add_admin($admin_data_arr))
						{
							//操作成功写入操作记录表
							$data_arr = array('aid'=>$_SESSION['HTC_LOGIN_DATA']['ad_id'],'time'=>time(),'t_name'=>'t_merchant','act'=>'add','data'=>json_encode($formdata));
							$data_oper = new data_operation();
							if($data_oper->insert_into_admin_log($data_arr))
							{
								$this->_error_code = Core_Exception::CODE_DEAL_OK;
								$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
								$ret = true;
							}
							else
							{
								$this->_error_code = Core_Exception::CODE_ADMIN_LOG_ERROR;
								$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_ADMIN_LOG_ERROR);
								Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
							}
						}
						else
						{
							$this->_error_code = Core_Exception::CODE_ADD_ADMIN_FAIL;
							$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_ADD_ADMIN_FAIL);
						}
					}
					else
					{
						$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
						Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
					}
				}
				else
				{
					$this->_error_code = Core_Exception::CODE_UNAME_EXISTS;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_UNAME_EXISTS);
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
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $ret;
	}
	//修改平台管理员
	public function update_admin($ad_id,$formdata)
	{
		$ret = false;
		if($this->init_mysql_obj())
		{
			if(!empty($formdata)&&isset($formdata['menu_data']))
			{
				$menu_data = $formdata['menu_data'];
				unset($formdata['menu_data']);
				$update_arr = array('ad_nick','ad_pwd','ad_role','phone','enable');
				foreach($formdata as $key=>$val)
				{
					$key = mysql_real_escape_string($key);
					$val = mysql_real_escape_string($val);

					if(in_array($key,$update_arr))
					{
						$formdata[$key] = $val;
					}
					else
						unset($formdata[$key]);
				}
				$sql = 'update t_admin set ';
				$i=0;
				$up_num = count($formdata)-1;
				foreach($formdata as $key=>$val)
				{
					$sql .= $key.'="'.$val.'"';
					if($i!=$up_num)
						$sql.=',';
					$i++;
				}
				$sql .= ' where ad_id='.$ad_id;
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_DEBUG);
				if($this->mysql->DB_Query_Affected_Rows($sql)>=0)
				{
					//修改管理员权限菜单
					$multiPorts = new multiPorts();
					if(!$multiPorts->create_admin_Menu($ad_id,$menu_data))
					{
						$this->_error_code = Core_Exception::CODE_CREATE_ADMIN_MENU_FAIL;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_CREATE_ADMIN_MENU_FAIL);
						Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
					}
					//操作成功写入操作记录表
					$data_arr = array('aid'=>$_SESSION['HTC_LOGIN_DATA']['ad_id'],'time'=>time(),'t_name'=>'t_admin','act'=>'update','data'=>json_encode(array('id'=>$ad_id,'data'=>$formdata)));
					$data_oper = new data_operation();
					if($data_oper->insert_into_admin_log($data_arr))
					{
						$this->_error_code = Core_Exception::CODE_DEAL_OK;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
						$ret = true;
					}
					else
					{
						$this->_error_code = Core_Exception::CODE_ADMIN_LOG_ERROR;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_ADMIN_LOG_ERROR);
						Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
					}
					
				}
				else
				{
					$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
					Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
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
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $ret;
	}
	//修改商户
	public function update_merchant($id,$formdata)
	{
		$ret = false;
		if($this->init_mysql_obj())
		{
			if(!empty($formdata)&&isset($formdata['menu_data']))
			{  
				$ad_pwd = '';
				$menu_data = $formdata['menu_data'];
				unset($formdata['menu_data']);

				$update_arr = array('name','shopowner','sphone','mpnone','mpnone2','addr','area','staffs','equip','remarks');
				foreach($formdata as $key=>$val)
				{
					$key = mysql_real_escape_string($key);
					$val = mysql_real_escape_string($val);

					if(in_array($key,$update_arr))
						$formdata[$key] = $val;
					else if($key == 'ad_pwd')
					{
						$ad_pwd = $val;
						unset($formdata[$key]);
					}
					else
						unset($formdata[$key]);
				}
				
				$sql = 'update t_merchant set ';
				$i=0;
				$up_num = count($formdata)-1;
				foreach($formdata as $key=>$val)
				{
					$sql .= $key.'="'.$val.'"';
					if($i!=$up_num)
						$sql.=',';
					$i++;
				}
				$sql .= ' where id='.$id;

				if($this->mysql->DB_Query_Affected_Rows($sql)>=0)
				{	

					//根据商户ID和类型获得管理员ID
					$ad_id = $this->get_adminid_by_tidandtype($id,2);
					$update_arr = array('menu_data'=>$menu_data);
					if(isset($formdata['name']))
						$update_arr['ad_nick'] = $formdata['name'];
					if($ad_pwd)
						$update_arr['ad_pwd'] = $ad_pwd;
					if(isset($formdata['sphone']))
						$update_arr['phone'] = $formdata['sphone'];
					Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,'ad_id=='.$ad_id,Core_Logger::LOG_LEVL_DEBUG);
					//Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,'update_arr=='.json_encode($update_arr),Core_Logger::LOG_LEVL_DEBUG);
					//商户修改成功要跟新管理员表中的相关信息
					if($this->update_admin($ad_id,$update_arr))
					{
						//操作成功写入操作记录表
						$data_arr = array('aid'=>$_SESSION['HTC_LOGIN_DATA']['ad_id'],'time'=>time(),'t_name'=>'t_merchant','act'=>'update','data'=>json_encode(array('id'=>$id,'data'=>$formdata)));
						$data_oper = new data_operation();
						if($data_oper->insert_into_admin_log($data_arr))
						{
							$this->_error_code = Core_Exception::CODE_DEAL_OK;
							$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
							$ret = true;
						}
						else
						{
							$this->_error_code = Core_Exception::CODE_ADMIN_LOG_ERROR;
							$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_ADMIN_LOG_ERROR);
							Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
						}
					}
				}
				else
				{
					$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
					Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
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
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $ret;
	}
	//删除商户
	public function delete_merchant($id)
	{
		$ret = false;
		if($this->init_mysql_obj())
		{
			$id = mysql_real_escape_string($id);
			$sql = 'delete from t_merchant where id='.$id;
			$num = $this->mysql->DB_Query_Affected_Rows($sql);
			if($num > 0)
			{
				//根据商户ID和管理员类型获得管理员ID
				$ad_id = $this->get_adminid_by_tidandtype($id,2);
				if($this->delete_admin($ad_id))
				{
					//操作成功写入操作记录表
					$data_arr = array('aid'=>$_SESSION['HTC_LOGIN_DATA']['ad_id'],'time'=>time(),'t_name'=>'t_merchant','act'=>'delete','data'=>json_encode(array('id'=>$id)));
					$data_oper = new data_operation();
					if($data_oper->insert_into_admin_log($data_arr))
					{
						$this->_error_code = Core_Exception::CODE_DEAL_OK;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
						$ret = true;
					}
					else
					{
						$this->_error_code = Core_Exception::CODE_ADMIN_LOG_ERROR;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_ADMIN_LOG_ERROR);
						Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
					}
				}
				else
				{
					$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
					Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $ret;
	}
	//删除管理员
	public function delete_admin($ad_id)
	{
		$ret = false;
		if($this->init_mysql_obj())
		{
			$ad_id = mysql_real_escape_string($ad_id);
			$sql = 'delete from t_admin where ad_id='.$ad_id;
			if($this->mysql->DB_Query_Affected_Rows($sql) > 0)
			{
				//操作成功写入操作记录表
				$data_arr = array('aid'=>$_SESSION['HTC_LOGIN_DATA']['ad_id'],'time'=>time(),'t_name'=>'t_admin','act'=>'delete','data'=>json_encode(array('ad_id'=>$ad_id)));
				$data_oper = new data_operation();
				if($data_oper->insert_into_admin_log($data_arr))
				{
					$this->_error_code = Core_Exception::CODE_DEAL_OK;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
					$ret = true;
				}
				else
				{
					$this->_error_code = Core_Exception::CODE_ADMIN_LOG_ERROR;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_ADMIN_LOG_ERROR);
					Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $ret;
	}
	//根据商户ID和管理员类型获得管理员ID
	public function get_adminid_by_tidandtype($ad_tid,$ad_type)
	{
		$ad_id = '';
		if($this->init_mysql_obj())
		{
			$ad_tid = mysql_real_escape_string($ad_tid);
			$ad_type = mysql_real_escape_string($ad_type);
			$sql = 'select ad_id from t_admin where ad_tid='.$ad_tid.' and ad_type='.$ad_type;
			if($result = $this->mysql->do_Query($sql))
			{
				$row = $this->mysql->DB_fetch_array($result);
				if(!empty($row))
				{
					$this->_error_code = Core_Exception::CODE_DEAL_OK;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
					$ad_id = $row['ad_id'];
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $ad_id;
	}
	//搜所平台管理员
	public function search_admin($search_arr)
	{
		//判断是否有权限操作本接口
		if(check_admin())
		{
			$data_arr = array();
			if($this->init_mysql_obj())
			{
				$ad_nick = isset($search_arr['ad_nick'])?mysql_real_escape_string($search_arr['ad_nick']):'';

				$page = isset($search_arr['page'])?$search_arr['page']:1;
				$bnums = ($page-1)*PAGE_NUMS;

				$sql = 'select * from t_admin where 1=1 ';
				if($ad_nick)
					$sql .= 'and ad_nick like "%'.$ad_nick.'%" ';

				$allnums = $this->mysql->DB_Query_Res_And_Count($sql);
				$allpage = ceil($allnums/PAGE_NUMS);

				$sql .= 'limit '.$bnums.','.PAGE_NUMS;

				if($result = $this->mysql->do_Query($sql))
				{
					while($row = $this->mysql->DB_fetch_array($result))
					{	
						$tmp_arr = array();
						$tmp_arr['ad_id'] = $row['ad_id'];
						$tmp_arr['ad_uname'] = $row['ad_uname'];
						$tmp_arr['ad_nick'] = $row['ad_nick'];
						$tmp_arr['ad_pwd'] = $row['ad_pwd'];
						$tmp_arr['ad_type'] = $row['ad_type'];
						$tmp_arr['ad_role'] = $row['ad_role'];
						$tmp_arr['ad_tid'] = $row['ad_tid'];
						$tmp_arr['dtime'] = $row['dtime'];
						$tmp_arr['phone'] = $row['phone'];
						$tmp_arr['enable'] = $row['enable'];
						$data_arr['data'][] = $tmp_arr;
					}
					$data_arr['allpage'] = $allpage;
					$this->_error_code = Core_Exception::CODE_DEAL_OK;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
				}
				else
				{
					$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
					Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
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
	//搜索商户信息
	public function search_merchant($search_arr)
	{
		//判断是否有权限操作本接口
		if(check_admin())
		{
			$data_arr = array();
			if($this->init_mysql_obj())
			{
				$name = isset($search_arr['name'])?mysql_real_escape_string($search_arr['name']):'';
				$id = isset($search_arr['id'])?mysql_real_escape_string($search_arr['id']):'';
				$mint = isset($search_arr['mint'])?mysql_real_escape_string($search_arr['mint']):''; //本月最低营业额
				$maxt = isset($search_arr['maxt'])?mysql_real_escape_string($search_arr['maxt']):''; //本月最高营业额
				$page = isset($search_arr['page'])?mysql_real_escape_string($search_arr['page']):1;
				$bnums = ($page-1)*PAGE_NUMS;
				$enums = $bnums + PAGE_NUMS - 1;

				$sql = 'select * from t_merchant where 1=1 ';
				if($name)
					$sql .= 'and name like "%'.$name.'%" ';
				if($id)
					$sql .= 'and id='.$id.' ';

				$allnums = 0;
				if($result = $this->mysql->do_Query($sql))
				{
					while($row = $this->mysql->DB_fetch_array($result))
					{
						$save_flag = true;
						//根据条件获得营业额
						$data_oper = new data_operation();
						//本月营业额
						$con_arr = array('btime'=>date('Ym01',time()),'etime'=>date('Ymd'),'mid'=>$row['id']);
						$mtumover = $data_oper->get_tumover_by_con_day($con_arr);
						//总营业额
						$con_arr = array('mid'=>$row['id']);
						$atumover = $data_oper->get_tumover_by_con_day($con_arr);
						if($mint && ($mtumover<$mint))
							$save_flag = false;
						if($maxt && ($mtumover>$maxt))
							$save_flag = false;

						if($save_flag)
						{
							if(($allnums>=$bnums) && ($allnums<=$enums))
							{
								$tmp_arr = array();
								$tmp_arr['id'] = $row['id'];
								$tmp_arr['uname'] = $row['uname'];
								$tmp_arr['name'] = $row['name'];
								$tmp_arr['ctime'] = $row['ctime'];
								$tmp_arr['shopowner'] = $row['shopowner'];
								$tmp_arr['sphone'] = $row['sphone'];
								$tmp_arr['mpnone'] = $row['mpnone'];
								$tmp_arr['mpnone2'] = $row['mpnone2'];
								$tmp_arr['addr'] = $row['addr'];
								$tmp_arr['area'] = $row['area'];
								$tmp_arr['staffs'] = $row['staffs'];
								$tmp_arr['equip'] = $row['equip'];
								$tmp_arr['remarks'] = $row['remarks'];
								$tmp_arr['mtumover'] = $mtumover;
								$tmp_arr['atumover'] = $atumover;
								$data_arr['data'][] = $tmp_arr;
							}
							$allnums++;
						}
					}
					$allpage = ceil($allnums/PAGE_NUMS);
					$data_arr['allpage'] = $allpage;
					$this->_error_code = Core_Exception::CODE_DEAL_OK;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
				}
				else
				{
					$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
					Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
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
	//根据商户ID获得商户信息
	public function get_merchant_info_byid($mid)
	{
		$data_arr = array();
		if($this->init_mysql_obj())
		{
			$mid = mysql_real_escape_string($mid);
			$sql = 'select * from t_merchant where id='.$mid;
			if($result = $this->mysql->do_Query($sql))
			{
				$row = $this->mysql->DB_fetch_array($result,MYSQL_ASSOC);
				if(!empty($row))
				{
					$this->_error_code = Core_Exception::CODE_DEAL_OK;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
					$data_arr = $row;
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $data_arr;
	}
	//修改管理员密码
	public function update_admin_pwd($updateid,$formdata)
	{
		$ret = false;
		if($this->init_mysql_obj())
		{
			if(isset($formdata['new_pwd'])&&isset($formdata['old_pwd']))
			{
				$updateid = mysql_real_escape_string($updateid);
				$formdata['old_pwd'] = mysql_real_escape_string($formdata['old_pwd']);
				$formdata['new_pwd'] = mysql_real_escape_string($formdata['new_pwd']);
				$sql = 'select * from t_admin where ad_id="'.$updateid.'"';
				if($result = $this->mysql->do_Query($sql))
				{
					if($row = $this->mysql->DB_fetch_array($result,MYSQL_ASSOC))
					{
						if(strcmp($formdata['old_pwd'],$row['ad_pwd']) == 0)
						{
							//跟新密码
							$sql = 'update t_admin set ad_pwd="'.$formdata['new_pwd'].'" where ad_id='.$updateid;
							if($this->mysql->DB_Query_Affected_Rows($sql) >= 0)
							{
								$this->_error_code = Core_Exception::CODE_DEAL_OK;
								$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
								$ret = true;
							}
							else
							{
								$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
								$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
								Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
							}
						}
						else
						{
							$this->_error_code = Core_Exception::CODE_FAIL_PWD;
							$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_FAIL_PWD);
							Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
						}
					}
					else
					{
						$this->_error_code = Core_Exception::CODE_USER_NOT_EXIST;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_USER_NOT_EXIST);
						Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
					}
				}
				else
				{
					$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
					Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
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
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $ret;
	}
	//修改管理员状态
	public function update_admin_state($updateid,$formdata)
	{
		$ret = false;
		//判断是否有权限操作本接口
		if(check_admin())
		{
			if($this->init_mysql_obj())
			{
				if(isset($formdata['state']))
				{
					$updateid = mysql_real_escape_string($updateid);
					$formdata['state'] = mysql_real_escape_string($formdata['state']);

					$enable = ($formdata['state']==0)?1:0;
					//跟新密码
					$sql = 'update t_admin set enable="'.$enable.'" where ad_id='.$updateid;
					if($this->mysql->DB_Query_Affected_Rows($sql) >= 0)
					{
						$this->_error_code = Core_Exception::CODE_DEAL_OK;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
						$ret = true;
					}
					else
					{
						$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
						Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
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
				$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $ret;
	}
	//修改商户
	public function update_merchant_info($id,$formdata)
	{
		$ret = false;
		//判断是否有权限操作本接口
		if(check_merchant())
		{
			if($this->init_mysql_obj())
			{
				if(!empty($formdata))
				{  
					$update_arr = array('name','shopowner','sphone','mpnone','mpnone2','addr','area','staffs','equip','remarks');
					foreach($formdata as $key=>$val)
					{
						$key = mysql_real_escape_string($key);
						$val = mysql_real_escape_string($val);

						if(in_array($key,$update_arr))
						{
							$formdata[$key] = $val;
						}
						else
							unset($formdata[$key]);
					}
					$sql = 'update t_merchant set ';
					$i=0;
					$up_num = count($formdata)-1;
					foreach($formdata as $key=>$val)
					{
						$sql .= $key.'="'.$val.'"';
						if($i!=$up_num)
							$sql.=',';
						$i++;
					}
					$sql .= ' where id='.$id;
					if($this->mysql->DB_Query_Affected_Rows($sql)>=0)
					{
						//操作成功写入操作记录表
						$data_arr = array('aid'=>$_SESSION['HTC_LOGIN_DATA']['ad_id'],'time'=>time(),'t_name'=>'t_merchant','act'=>'update','data'=>json_encode(array('id'=>$id,'data'=>$formdata)));
						$data_oper = new data_operation();
						if($data_oper->insert_into_admin_log($data_arr))
						{
							$this->_error_code = Core_Exception::CODE_DEAL_OK;
							$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
							$ret = true;
						}
						else
						{
							$this->_error_code = Core_Exception::CODE_ADMIN_LOG_ERROR;
							$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_ADMIN_LOG_ERROR);
							Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
						}
					}
					else
					{
						$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
						Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
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
				$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
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
	//跟新商户的账户
	public function update_merchant_account($id,$formdata)
	{
		$ret = false;
		if($this->init_mysql_obj())
		{
			$id = mysql_real_escape_string($id);
			$account = mysql_real_escape_string($formdata['account']);
			$sql = 'update t_merchant set account="'.$account.'" where id="'.$id.'"';
			
			if($this->mysql->DB_Query_Affected_Rows($sql)>0)
			{
				$this->_error_code = Core_Exception::CODE_DEAL_OK;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
				$ret = true;
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
				Core_Logger::getInstance()->writeLog(get_called_class()."::".__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(get_called_class()."::".__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $ret;
	}
	//根据管理员ID获得管理员信息
	public function get_admin_info_byid($ad_id)
	{
		$data_arr = array();
		if($this->init_mysql_obj())
		{
			$ad_id = mysql_real_escape_string($ad_id);
			$sql = 'select * from t_admin where ad_id='.$ad_id;
			if($result = $this->mysql->do_Query($sql))
			{
				$row = $this->mysql->DB_fetch_array($result,MYSQL_ASSOC);
				if(!empty($row))
				{
					$this->_error_code = Core_Exception::CODE_DEAL_OK;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
					$data_arr = $row;
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $data_arr;
	}
	// cm 上传用户信息 UPLOAD_FILE_PATH
	public function update_admin_logo($updateid)
	{
		$ret = false;
		if (!file_exists(UPLOAD_FILE_PATH.$updateid))
		{ 
			// 没有，就创建
			mkdir (UPLOAD_FILE_PATH.$updateid);
		} 
		if ( !empty( $_FILES ) ) {
			$tempPath = $_FILES[ 'file' ][ 'tmp_name' ];
			$uploadPath = UPLOAD_FILE_PATH.$updateid. DIRECTORY_SEPARATOR . $_FILES[ 'file' ][ 'name' ];
			move_uploaded_file($tempPath,$uploadPath);
			$this->_error_code = Core_Exception::CODE_DEAL_OK;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
			$ret = true;

		} else {

			$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
			Core_Logger::getInstance()->writeLog(get_called_class()."::".__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);

		}
		
	}

}
global $Admin_ID;
$Admin_ID=isset($_SESSION['HTC_LOGIN_DATA']['ad_id'])?$_SESSION['HTC_LOGIN_DATA']['ad_id']:'';
?>