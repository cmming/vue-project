<?php
///////////////////////////////////////////////////
//日志处理对象
//bull 2014-05-05 
///////////////////////////////////////////////////
class Core_Logger
{
	//日志级别	
	const LOG_LEVL_NO = 0;					//无日志
	const LOG_LEVL_ERROR = 0x01;			//错误日志
	const LOG_LEVL_WARNING = 0x02;			//警告日志
	const LOG_LEVL_DEBUG = 0x04;			//调式日志
	const LOG_LEVL_DATA = 0x08;				//数据信息

	//日志文件保存路径
	private $_logPath = "";
	//数据文件保存路径
	private $_dataPath = "";
	//数据文件名称
	private $_dataFileName="";
	//日志文件名称
	private $_logFileName = 'default_log.log';
	//日志级别开关
	private $_logLevel = 0;
	//日志会话索引
	private $_log_sess = "";
	//日志条数索引
	private $_log_index = 0;

	//////////////////////////////////////////////////////////////////////////
	//单例实例指针
	protected static $_instance = null;

	private function __clone()
	{
		//触发内部错误，提示开发者该类不允许刻隆
		trigger_error(get_called_class()."为单例模式，不允许被刻隆",E_USER_ERROR);
	}

	public static function getInstance()
    {        
        if (self::$_instance === null) {
            $className = get_called_class();
            self::$_instance = new $className;
			//创建对象失败
			if(self::$_instance == null)
			{
				//抛出异常，告知用户同时写入日志
				Core_Logger::getInstance()->writeLog(get_called_class()."::".__METHOD__.":".__LINE__,"创建对象失败",Core_Logger::LOG_LEVL_ERROR);
				throw new Core_Exception(Core_Exception::CODE_FAILED_CREATE_OBJ);
			}
        }
        return self::$_instance;
    }
	//////////////////////////////////////////////////////////////////////////

	private function __construct()
    {
		//生成会话索引
		$this->_log_sess = "log_".md5(uniqid(rand()."_".time(), true))."_";
		//设置默认路径
		$this->_logPath = PATH_SYS_LOG;
		//设置数据文件
		$this->_dataPath = PATH_SYS_DATA;

		//设置默认级别
		$this->_logLevel = self::LOG_LEVL_ERROR | self::LOG_LEVL_WARNING | self::LOG_LEVL_DEBUG;

        //目录不存在，则创建
		if(!file_exists($this->_logPath))
		{
			mkdir($this->_logPath);
		}

		//目录不存在，则创建
		if(!file_exists($this->_dataPath))
		{
			mkdir($this->_dataPath);
		}
		$this->_dataFileName=date("Y_m_d_")."default_data.txt";
    }

	/////////////////////////////////////////////////
	//功　　能：生成错误定位信息
	//入口参数：$file_loc	- 文件信息
	//			$line_loc	- 行数信息
	//			$method_loc	- 函数信息
	//说　　明：一般在函数外的错误才调用此方法生成定位信息
	/////////////////////////////////////////////////
	public static function getLocation($file_loc="",$line_loc="",$method_loc="")
	{
		$ret_loc = "";
		if(!empty($file_loc))
		{
			$ret_loc = $file_loc;
		}
		if(!empty($line_loc))
		{
			$ret_loc .= "->".$line_loc;
		}
		if(!empty($method_loc))
		{
			$ret_loc .= "->".$method_loc;
		}
		return $ret_loc;
	}

	/////////////////////////////////////////////////
	//功　　能：自定义日志保存路径
	//入口参数：$logPath	- 保存日志文件的文件夹绝对路径
	//返 回 值：无
	/////////////////////////////////////////////////
    public function setLogPath($logPath)
    {
        $this->_logPath = $logPath;
		//目录不存在，则创建
		if(!file_exists($this->_logPath))
		{
			mkdir($this->_logPath);
		}
    }

	/////////////////////////////////////////////////
	//功　　能：自定义数据文件保存路径
	//入口参数：$logPath	- 保存数据文件的文件夹绝对路径
	//返 回 值：无
	/////////////////////////////////////////////////
    public function setDataPath($dataPath)
    {
        $this->_dataPath = $dataPath;
		//目录不存在，则创建
		if(!file_exists($this->_dataPath))
		{
			mkdir($this->_dataPath);
		}
    }

	/////////////////////////////////////////////////
	//功　　能：自定义日志保存文件名
	//入口参数：$logFileName	- 保存日志文件的文件名
	//返 回 值：无
	/////////////////////////////////////////////////
	public function setLogFileName($logFileName)
    {
        $this->_logFileName = $logFileName.".log";
    }

	/////////////////////////////////////////////////
	//功　　能：根据平台分配给CP的Appkey自定义日志保存文件名
	//入口参数：$Appkey	- 平台分配给CP的Appkey
	//返 回 值：无
	/////////////////////////////////////////////////
	public function setLogFileNameByAppkey($Appkey)
    {
        $this->_logFileName = $Appkey.".log";
    }

	/////////////////////////////////////////////////
	//功　　能：自定义数据文件保存文件名
	//入口参数：$dataFileName	- 保存数据文件的文件名
	//返 回 值：无
	/////////////////////////////////////////////////
	public function setDataFileName($dataFileName)
    {
        $this->_dataFileName = date("Y_m_d_").$dataFileName.".txt";
    }

	/////////////////////////////////////////////////
	//功　　能：自定义数据文件保存文件名
	//入口参数：$dataFileName	- 保存数据文件的文件名
	//返 回 值：无
	/////////////////////////////////////////////////
	public function setDataFileNameByAppkey($Appkey)
    {
        $this->_dataFileName = date("Y_m_d_").$Appkey."_data.txt";
    }

	/////////////////////////////////////////////////
	//功　　能：设置日志输出级别
	//入口参数：$level	- 日志级别LOG_LEVL_NO 或 LOG_LEVL_ERROR | LOG_LEVL_WARNING | LOG_LEVL_DEBUG的组合值
	//返 回 值：无
	//说　　明：默认所有日志全开，设置日志级别后，将只会打印相应级别日志
	/////////////////////////////////////////////////
	public function setLogLevel($level)
	{
		$this->_logLevel = $level;
	}
	/////////////////////////////////////////////////
	//功　　能：获取日志输出级别
	//入口参数：
	//返 回 值：日志级别LOG_LEVL_NO 或 LOG_LEVL_ERROR | LOG_LEVL_WARNING | LOG_LEVL_DEBUG的组合值
	//说　　明：
	/////////////////////////////////////////////////
	public function getLogLevel()
	{
		return $this->_logLevel;
	}

	//写日志
	//
	/////////////////////////////////////////////////
	//功　　能：写日志
	//入口参数：$location	- 日志定位　一般为__METHOD__ 或 __FILE__."->".__LINE__等
	//			$message	- 日志内容
	//			$level		- 日志级别 LOG_LEVL_ERROR,LOG_LEVL_WARNING,LOG_LEVL_DEBUG
	//			$utime		- 日志中是否显示时间
	//			$need_echo	- 是否需要回显
	//返 回 值：无
	//说　　明：如果指定的$level不在对象日志级别中，则不会写入日志文件
	/////////////////////////////////////////////////
    public function writeLog($location, $message,$level= self::LOG_LEVL_DEBUG,$utime = true,$need_echo = false)
    {
		$logFile = "";
		//数据单独存一份
		if($level==self::LOG_LEVL_DATA)
		{
			//数据文件单独存
			//目录不存在，则创建
			if(!file_exists($this->_logPath))
			{
				mkdir($this->_logPath);
			}
			$logFile = $this->_dataPath.$this->_dataFileName;
			$msg = "[".date('Y-m-d H:i:s')."]".$message;
			self::writeFileLock($logFile,$msg);
		}
		//日志级别
		if(($level & $this->_logLevel) > 0)
		{
			//目录不存在，则创建
			if(!file_exists($this->_logPath))
			{
				mkdir($this->_logPath);
			}
			
			$msg = "";
			if($utime)
			{
				$msg = date('Y-m-d H:i:s');
			}
			//加上日志索引
			++$this->_log_index;
			$msg .= "[".$this->_log_sess.$this->_log_index."]";
			if($level & self::LOG_LEVL_ERROR)
			{
				$msg .= "[Error]";
			}else if($level & self::LOG_LEVL_WARNING)
			{
				$msg .= "[Warning]";
			}else if($level & self::LOG_LEVL_DEBUG)
			{
				$msg .= "[Debug]";
			}			
			$msg .= "[".$location."]".$message;
			$logFile = $this->_logPath.$this->_logFileName;
			self::writeFileLock($logFile,$msg);
		}

		//回显日志
		if($need_echo)
		{
			echo $msg."<br>";
		}
    }

	//加锁写文件
	public static function writeFileLock($logFile,$msg)
	{
		$isNewFile = !file_exists($logFile);
		$fp = fopen($logFile, 'a');
		if($fp)
		{
			if (flock($fp, LOCK_EX)) {
				if ($isNewFile) {
					chmod($logFile, 0666);
				}
				fwrite($fp, $msg . "\n");
				flock($fp, LOCK_UN);
			}
			fclose($fp);
		}
	}


}
