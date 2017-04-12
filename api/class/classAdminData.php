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

	//用户名
	private $_user_uname = "";
	//获得管理员帐号
	public function get_user_uname()
	{
		return $this->_user_uname;
	}

    //增加管理员 注册
	public function add_admin($formdata)
	{
		$ret = false;
		if($this->init_mysql_obj())
		{
			if(isset($formdata['userName'])&&isset($formdata['userTel'])&&isset($formdata['userEmail'])&&isset($formdata['userPwd']))
			{
                foreach($formdata as $key=>$val)
				{
					$key = mysql_real_escape_string($key);
					$val = mysql_real_escape_string($val);
					$formdata[$key] = $val;
				}
                $sql = 'insert into t_admin(userName,userTel,userEmail,userPwd) values("'.$formdata['userName'].'","'.$formdata['userTel'].'","'.$formdata['userEmail'].'","'.$formdata['userPwd'].'")';
                // var_dump($sql);exit();
				// 发送数据请求
				if($this->mysql->DB_Query($sql)){
					$this->_error_code = Core_Exception::CODE_DEAL_OK;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
					$ret = true;
				}
				else{
					$this->_error_code = Core_Exception::CODE_ADMIN_LOG_ERROR;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_ADMIN_LOG_ERROR);
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
    //增加管理员 登录
	public function login_confirm($formdata)
	{
		$ret = false;
		if($this->init_mysql_obj())
		{
			if(isset($formdata['userName'])&&isset($formdata['userPwd']))
			{
                foreach($formdata as $key=>$val)
				{
					$key = mysql_real_escape_string($key);
					$val = mysql_real_escape_string($val);
					$formdata[$key] = $val;
				}
				// 必须有引号，防止发生错误 例如汉字的时候
                $sql = 'select * from t_admin where userName="'.$formdata['userName'].'"';
				// 发送数据请求
				if($result=$this->mysql->DB_Query($sql)){
					// 对结果集进行解析
					if($res_arr=$this->mysql->DB_fetch_array($result)){}
                	// var_dump($res_arr);exit();
					// 对结果进行判断 密码不相等
					if($formdata['userPwd']!=$res_arr['userPwd']){
						$this->_error_code = Core_Exception::CODE_FAIL_PWD;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_FAIL_PWD);
					}
					else
					{
						$ret['user_uname']=$res_arr['userName'];
						$this->_error_code = Core_Exception::CODE_DEAL_OK;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
					}
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
    //退出登录
	public function loginout($formdata)
	{
		// 清空后台该用户的session
		$this->_error_code = Core_Exception::CODE_DEAL_OK;
		$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
	}
}

?>