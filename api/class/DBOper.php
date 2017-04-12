<?php
///////////////////////////////////////////////////
//平台数据库操作对象
//bull 2014-05-05 
///////////////////////////////////////////////////
class Core_DBOper
{
	
	private $_db_host = "";				//平台数据库主机地址
	private $_db_name = "";				//平台数据库名称
	private $_db_id = "";				//平台数据库帐号
	private $_db_pwd = "";				//平台数据库密码
	private $_db_conn = null;			//平台数据库连接
	private $_config_changed = true;	//数据库配置是否发生改变

	private $_db_errno = 0;					//数据库错误号
	private $_db_error = "";				//数据库错误描述

	//自动销毁数据库连接对象
	public function __destruct()
	{
		$this->DB_Close();
	}
	/////////////////////////////////////////////////
	//功　　能：设置数据库配置信息
	//入口参数：无
	//返 回 值：无
	//说　　明：配置信息设置后，连接数据库时将使用此配置
	/////////////////////////////////////////////////
	public function setDBConfig($host,$name,$user,$pwd)
	{
		if($this->_db_host != $host)
		{
			$this->_db_host = $host;
			$this->_config_changed = true;
		}
		if($this->_db_name != $name)
		{
			$this->_db_name = $name;
			$this->_config_changed = true;
		}
		if($this->_db_id != $user)
		{
			$this->_db_id = $user;
			$this->_config_changed = true;
		}
		if($this->_db_pwd != $pwd)
		{
			$this->_db_pwd = $pwd;
			$this->_config_changed = true;
		}
	}
	
	/////////////////////////////////////////////////
	//功　　能：获取真实数据库连接
	//入口参数：
	//返 回 值：
	//说　　明：此方法最好不用，以防数据库升级时出麻烦
	/////////////////////////////////////////////////
	public function DB_Get_Conn()
	{
		return $this->_db_conn;
	}
	
	/////////////////////////////////////////////////
	//功　　能：转义一个字符串用于 DB_query 
	//入口参数：字符串
	//返 回 值：详见mysql_real_escape_string 或mysqli_real_escape_string
	//说　　明：方便以后数据库升级，建议所有数据库执行使用此方法
	/////////////////////////////////////////////////
	public function real_escape_string($item,$conn=null)
	{
		if($conn == null)
		{
			return mysql_real_escape_string($item,$this->_db_conn);
		}else{
			return mysql_real_escape_string($item,$conn);
		}		
	}
	/////////////////////////////////////////////////
	//功　　能：连接数据库
	//入口参数：无
	//返 回 值：成功 - 返回当前数据库连接，以便外部使用 
	//			失败 - 返回false
	//说　　明：使用当前已配置的信息连接数据库
	/////////////////////////////////////////////////
	public function DB_Connect()
	{
		if($this->_db_conn)
		{
			if($this->_config_changed)
			{
				$this->_config_changed = false;
				mysql_close($this->_db_conn);
				$this->_db_conn = null;
			}			
		}
		if($this->_db_conn == null)
		{
			$this->_db_conn = mysql_connect($this->_db_host,$this->_db_id,$this->_db_pwd,1);
		}
		if($this->_db_conn)
		{
			if (mysql_select_db($this->_db_name, $this->_db_conn))
			{
				return $this->_db_conn;
			}else{
				$this->_db_errno = mysql_errno($this->_db_conn);
				$this->_db_error = mysql_error($this->_db_conn);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,"select db error host=".$this->_db_host." db=".$this->_db_name." user=".$this->_db_id." mysql_err=".mysql_error(),Core_Logger::LOG_LEVL_ERROR);
			}
		}else{
			$this->_db_errno = mysql_errno();
			$this->_db_error = mysql_error();
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,"connect to db error host=".$this->_db_host." db=".$this->_db_name." user=".$this->_db_id." mysql_err=".mysql_error(),Core_Logger::LOG_LEVL_ERROR);
		}
		return false;
	}

	/////////////////////////////////////////////////
	//功　　能：关闭数据库连接
	//入口参数：无
	//返 回 值：成功 - 返回当前数据库连接，以便外部使用 
	//			失败 - 返回false
	//说　　明：
	/////////////////////////////////////////////////
	public function DB_Close()
	{
		$ret = false;
		if($this->_db_conn!=null)
		{
			$ret = mysql_close($this->_db_conn);
			$this->_db_conn = null;
		}
		return $ret;
	}

	/////////////////////////////////////////////////
	//功　　能：获取数据库错误号
	/////////////////////////////////////////////////
	public function DB_Errno()
	{
		return $this->_db_errno;
	}

	/////////////////////////////////////////////////
	//功　　能：获取数据库错误描述
	/////////////////////////////////////////////////
	public function DB_Error()
	{
		return $this->_db_error;
	}

	/////////////////////////////////////////////////
	//功　　能：使用内置连接执行数据库语句
	//入口参数：$sql	- 需要执行的sql语句
	//返 回 值：详见mysql_query 或mysqli_query
	//说　　明：方便以后数据库升级，建议所有数据库执行使用此方法
	/////////////////////////////////////////////////
	public function DB_Query($sql)
	{
		return self::do_Query($sql,$this->_db_conn);
	}

	/////////////////////////////////////////////////
	//功　　能：使用内置连接执行数据库语句，并返回结果集中记录条数
	//入口参数：$sql	- 需要执行的sql语句
	//返 回 值：返回结果集中记录条数,-1 表示示败
	//说　　明：方便以后数据库升级，建议所有数据库执行使用此方法
	/////////////////////////////////////////////////
	public function DB_Query_Res_And_Count($sql)
	{
		$ret = -1;
		$result = self::do_Query($sql,$this->_db_conn);
		if($result)
		{
			$ret = mysql_num_rows($result);
		}
		return $ret;
	}

	/////////////////////////////////////////////////
	//功　　能：返回结果集中记录条数
	//入口参数：$res - 结果集
	//返 回 值：返回结果集中记录条数
	//说　　明：方便以后数据库升级，建议所有数据库执行使用此方法
	/////////////////////////////////////////////////
	public function DB_Res_Num_Rows($res)
	{
		return mysql_num_rows($res);
	}

	/////////////////////////////////////////////////
	//功　　能：使用内置连接执行数据库语句，并返回操作影响记录数
	//入口参数：$sql	- 需要执行的sql语句
	//返 回 值：返回结果集中记录条数,-1 表示示败
	//说　　明：方便以后数据库升级，建议所有数据库执行使用此方法
	/////////////////////////////////////////////////
	public function DB_Query_Affected_Rows($sql)
	{
		$ret = -1;
		$result = self::do_Query($sql,$this->_db_conn);
		if($result)
		{
			$ret = mysql_affected_rows($this->_db_conn);
		}
		return $ret;
	}

	/////////////////////////////////////////////////
	//功　　能：执行数据库语句
	//入口参数：$sql	- 需要执行的sql语句
	//			$conn	- 数据库连接，如果不指定，则使用内置连接
	//返 回 值：详见mysql_query 或mysqli_query
	//说　　明：方便以后数据库升级，建议所有数据库执行使用此方法
	/////////////////////////////////////////////////
	public function do_Query($sql,$conn=null)
	{		
		$ret = false;
		if($conn == null)
			$ret = mysql_query($sql);
		else
			$ret = mysql_query($sql,$conn);

		//失败
		if(!$ret)
		{
			$this->_db_errno = mysql_errno();
			$this->_db_error = mysql_error();
		}
		return $ret;
	}

	/////////////////////////////////////////////////
	//功　　能：从结果集中取得一行作为关联数组，或数字数组，或二者兼有
	//入口参数：$res		- 结果集
	//			$resulttype	- 关联数组类型 MYSQL_ASSOC，MYSQL_NUM 和 MYSQL_BOTH
	//返 回 值：详见mysql_fetch_array() 或 mysqli_fetch_array()
	//说　　明：方便以后数据库升级，建议所有数据库执行使用此方法
	/////////////////////////////////////////////////
	public function DB_fetch_array($res,$resulttype = MYSQL_BOTH)
	{
		return mysql_fetch_array($res,$resulttype);
	}

	/////////////////////////////////////////////////
	//功　　能：取得上一步 INSERT 操作产生的 ID
	//入口参数：$res		- 结果集
	//返 回 值：详见mysql_insert_id() 或 mysqli_fetch_array()
	//说　　明：方便以后数据库升级，建议所有数据库执行使用此方法
	/////////////////////////////////////////////////
	public function DB_insert_id($res = null)
	{
		if($res == null)
		{
			return mysql_insert_id($this->_db_conn);
		}else{
			return mysql_insert_id($res);
		}
	}

	/////////////////////////////////////////////////
	//特殊辅助函数 连接vr中心数据库
	/////////////////////////////////////////////////
	public function DB_Connect_CenterDB()
	{
		//充值中心数据库配置
		$this->setDBConfig(DB_HOST,DB_NAME,DB_ROOT,DB_PWD);
		return $this->DB_Connect();
	}

	/////////////////////////////////////////////////
	//检测数据库是否连接，若没有则重新连接
	/////////////////////////////////////////////////
	public function DB_mysql_ping()
	{
		if(mysql_ping($this->_db_conn))
		{
			return true;
		}
		else
		{
			//关闭数据库连接
			$this->DB_close();
			//重新连接
			return $this->DB_Connect();
		}
	}
}
?>
