<?php
////////////////////////////////////////////////////////////////
//退出接口
//wl 2016-09-22
///////////////////////////////////////////////////////////////
require_once 'common.inc.php';

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
$logger->setLogFileName("loginout");
//////////////////////////////////////////////////////////////////////////
if(check_login())
{
	loginout();
	$out_data['code'] = Core_Exception::CODE_DEAL_OK;
	$out_data['msg'] = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);

}
else
{
	$out_data['code'] = Core_Exception::CODE_USER_NOT_LOGIN;
	$out_data['msg'] = Core_Exception::getErrorDes(Core_Exception::CODE_USER_NOT_LOGIN);
}
echo json_encode($out_data);
?>