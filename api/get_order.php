<?php
/////////////////////////////////////////////////////
//生成订单接口
//wuli 2016-09-22
/////////////////////////////////////////////////////
require_once 'common.inc.php';
require_once 'class/BlueSDK/BlueSDK.config.php';
require_once 'class/BlueSDK/BlueSDK_Processor.Class.php';

$out_data['code'] = Core_Exception::CODE_UNKNOW_ERROR;
$out_data['msg'] = Core_Exception::getErrorDes(Core_Exception::CODE_UNKNOW_ERROR);
$out_data['data'] = array();

//创建日志类
$logger = Core_Logger::getInstance();

//////////////////////////////////////////////////////////////////////
//设置日志输出级别
//$logger->setLogLevel(Core_Logger::LOG_LEVL_NO);		//无日志
$logger->setLogLevel(Core_Logger::LOG_LEVL_DEBUG|Core_Logger::LOG_LEVL_ERROR|Core_Logger::LOG_LEVL_WARNING|Core_Logger::LOG_LEVL_DATA);	//仅输出错误,警告日志,数据记录日志
//设置针对每个应用设置日志文件，方便查询
$logger->setLogPath(PATH_SYS_LOG);
//设置日志文件路径
$logger->setLogFileName("get_order");
/////////////////////////////////////////////////////////////////////

//Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,"收到数据IP=[".getip_str()."] data=".urldecode(http_build_query($_REQUEST)),Core_Logger::LOG_LEVL_DEBUG);
$request_data = file_get_contents('php://input', true);
$request_data = json_decode($request_data,true);
$eid = isset($request_data['eid'])?$request_data['eid']:'';
$price = isset($request_data['price'])?$request_data['price']:'';
$pid = isset($request_data['pid'])?$request_data['pid']:'';
$paytype = isset($request_data['paytype'])?$request_data['paytype']:'';
$_ts = isset($request_data['_ts'])?$request_data['_ts']:'';
$_sign = isset($request_data['_sign'])?$request_data['_sign']:'';

if($eid&& $price&& $pid  && $paytype && $_ts && $_sign)
{
	$eid = trim($eid);
	$price = trim($price);
	$pid = trim($pid);
	$paytype = trim($paytype);
	$_ts = trim($_ts);
	$_sign = trim($_sign);

	if(!empty($eid) && !empty($pid) && !empty($price) && !empty($paytype))
	{
		//$sign = getSign($_REQUEST,SIGN_KEY,$str);
		//if(strcasecmp($sign,$_sign) == 0)
		if(1)
		{
			if(check_login())
			{
				$spid = 0;
				$out_data['data'] = get_user_pay_ewcode($eid,$price,$paytype,$spid,$pid);
				$out_data['code'] = Core_Exception::CODE_DEAL_OK;
				$out_data['msg'] = "";
			}else{
				$out_data['code'] = Core_Exception::CODE_USER_UNKNOW_ERROR;
				$out_data['msg'] = Core_Exception::getErrorDes(Core_Exception::CODE_USER_UNKNOW_ERROR);
				
			}
		}else{
			$out_data['code'] = Core_Exception::CODE_BAD_SIGN;
			$out_data['msg'] = Core_Exception::getErrorDes(Core_Exception::CODE_BAD_SIGN);
		}
	}else{
		$out_data['code'] = Core_Exception::CODE_BAD_PARAM;
		$out_data['msg'] = Core_Exception::getErrorDes(Core_Exception::CODE_BAD_PARAM);
	}
}else{
	$out_data['code'] = Core_Exception::CODE_BAD_PARAM;
	$out_data['msg'] = Core_Exception::getErrorDes(Core_Exception::CODE_BAD_PARAM);
}
header("Content-Type: text/json; charset=utf-8");
echo json_encode($out_data);
?>
