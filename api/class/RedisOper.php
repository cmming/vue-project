<?php
///////////////////////////////////////////////////
//redis数据库操作对象
//wl 20160908
///////////////////////////////////////////////////
class Core_RedisOper extends Redis
{
	
	///////////////////////////////////////////////////
	//自动初始化
	//////////////////////////////////////////////////
	//初始化redis数据库对象
	public function init_redis_obj()
	{
		$ret = false;
		$step = true;
		try
		{
			if(parent::ping())
			{
				$ret = true;
			}
		}catch(Exception $e)
		{
		}
		if(!$ret){
			if(parent::pconnect(REDIS_USER_HOST,REDIS_USER_PORT))
			{
				if(defined('REDIS_USER_PWD') && (REDIS_USER_PWD != ""))
				{var_dump(REDIS_USER_HOST);
					$step = parent::auth(REDIS_USER_PWD);
				}
				if($step)
				{
					$ret = parent::select(REDIS_USER_INDEX);
				}
			}else{
				//抛出异常，告知用户同时写入日志
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,"Redis连接失败",Core_Logger::LOG_LEVL_ERROR);
			}
		}
		return $ret;
	}
}
?>
