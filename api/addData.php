<?php
////////////////////////////////////////////////////////////////
//增加管理员数据接口，平台管理员操作
//wl 2016-09-14
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
$logger->setLogFileName("addData");
///////////////////////////////////////////////////////////////////
//接受参数
$request_data = file_get_contents('php://input', true);
$request_data = json_decode($request_data,true);
$type = isset($request_data['type'])?$request_data['type']:'';
$formdata = isset($request_data['formdata'])?$request_data['formdata']:'';
//$type = isset($_REQUEST['type'])?$_REQUEST['type']:'';
//$formdata = isset($_REQUEST['formdata'])?$_REQUEST['formdata']:'';
if($type&&$formdata)
{
	//验证是否登录
	if(check_login())
	{
		//判断是否有权限操作本接口
		$formdata = json_decode($formdata,true);
		//实例化功能操作类
		$data_operation = new data_operation();
		switch($type)
		{
			case 'addTerm':
				$data_operation->add_term($formdata);
				break;
			case 'addPackage':
				$data_operation->add_package($formdata);
				break;
			case 'billDemends':
				$data_operation->add_bill_demends($formdata);
				break;
			case 'packageTermShip':
				$data_operation->add_package_term_ship($formdata);
				break;
			case 'billPayRecord':
				$data_operation->add_bill_pay_record($formdata);
				break;
			case 'merchantCode':
				$data_operation->add_merchant_code($formdata);
				break;
			default :
				break;
		}
		$out_data['code'] = $data_operation->get_error_code();
		$out_data['msg'] = $data_operation->get_error_des();
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