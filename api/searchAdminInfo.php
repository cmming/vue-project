<?php
////////////////////////////////////////////////////////////////
//搜索管理员信息接口
//wl 2016-09-18
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
$logger->setLogFileName("searchAdminInfo");
///////////////////////////////////////////////////////////////////
//接受参数
$request_data = file_get_contents('php://input', true);
$request_data = json_decode($request_data,true);
$type = isset($request_data['type'])?$request_data['type']:'';
$search = isset($request_data['search'])?$request_data['search']:'';
//$type = isset($_REQUEST['type'])?$_REQUEST['type']:'';
//$search = isset($_REQUEST['search'])?$_REQUEST['search']:'';
if($type&&$search)
{
	//验证是否登录
	if(check_login())
	{
		$data_arr = array();
		$search = json_decode($search,true);
		//实例化功能操作类
		$admin = new classAdmin();
		switch($type)
		{
			case 'merchant':
				$data_arr = $admin->search_merchant($search);
				break;
			case 'admin':
				$data_arr = $admin->search_admin($search);
				break;
			case 'getMerchantInfo':
			{
				if(check_admin())
					$mid = isset($search['mid'])?$search['mid']:'';
				else
					$mid = isset($_SESSION['HTC_LOGIN_DATA']['ad_tid'])?$_SESSION['HTC_LOGIN_DATA']['ad_tid']:'';

				$data_arr = $admin->get_merchant_info_byid($mid);
			}
			break;
			default :
			break;
		}
		$out_data['code'] = $admin->get_error_code();
		$out_data['msg'] = $admin->get_error_des();
		$out_data['data'] = $data_arr;
	}
	else
	{
		$out_data['code'] = Core_Exception::CODE_USER_NOT_LOGIN;
		$out_data['msg'] = Core_Exception::getErrorDes(Core_Exception::CODE_USER_NOT_LOGIN);
	}
}
else
{
	$out_data['code'] = Core_Exception::CODE_BAD_PARAM;
	$out_data['msg'] = Core_Exception::getErrorDes(Core_Exception::CODE_BAD_PARAM);
}
echo json_encode($out_data);
?>