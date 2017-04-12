<?php
////////////////////////////////////////////////////////////////
//登录接口
//wl 2016-09-13
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
$logger->setLogFileName("login");
//////////////////////////////////////////////////////////////////////////
//接受参数
$request_data = file_get_contents('php://input', true);
$request_data = json_decode($request_data,true);
$uname = isset($request_data['uname'])?$request_data['uname']:'';
$pwd = isset($request_data['pwd'])?$request_data['pwd']:'';
$ad_type = isset($request_data['ad_type'])?$request_data['ad_type']:'';
//$uname = isset($_REQUEST['uname'])?$_REQUEST['uname']:'';
//$pwd = isset($_REQUEST['pwd'])?$_REQUEST['pwd']:'';
//$ad_type = isset($_REQUEST['ad_type'])?$_REQUEST['ad_type']:'';
if($uname && $pwd && $ad_type)
{
	//验证登录名和密码
	$admin = new classAdmin();
	if($admin->login_confirm($uname,$pwd,$ad_type))
	{
		$out_data['code'] = $admin->get_error_code();
		$out_data['msg'] = $admin->get_error_des();
		$out_data['data']['admin_id'] = $admin->get_adminid();
		$out_data['data']['admin_uname'] = $admin->get_admin_uname();
		$out_data['data']['admin_nick'] = $admin->get_admin_nick();
		$out_data['data']['session'] = $_SESSION;
	}
	else
	{
		$out_data['code'] = $admin->get_error_code();
		$out_data['msg'] = $admin->get_error_des();
	}
}
else
{
	$out_data['code'] = Core_Exception::CODE_BAD_PARAM;
	$out_data['msg'] = Core_Exception::getErrorDes(Core_Exception::CODE_BAD_PARAM);
}
echo json_encode($out_data);
?>