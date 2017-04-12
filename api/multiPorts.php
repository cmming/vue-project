<?php
////////////////////////////////////////////////////////////////
//功能接口接口
//wl 2016-09-20
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
$logger->setLogFileName("multiPorts");
///////////////////////////////////////////////////////////////////
//接受参数
$request_data = file_get_contents('php://input', true);
$request_data = json_decode($request_data,true);
$type = isset($request_data['type'])?$request_data['type']:(isset($_REQUEST['type'])?$_REQUEST['type']:'');
$data = isset($request_data['data'])?$request_data['data']:(isset($_REQUEST['data'])?$_REQUEST['data']:'');
if($type)
{
	//验证是否登录
	if(check_login())
	{
		$multiPorts = new multiPorts();
		$data_arr = array();
		switch($type)
		{
			case 'getMyMenu':
				$data_arr = $multiPorts->get_my_menu();
			break;
			case 'getMenu':
				$data_arr = $multiPorts->get_menu();
			break;
			case 'getTypeMenu':
				$menu_data = json_decode($data,true);
			    $ad_type =  isset($menu_data['ad_type'])?$menu_data['ad_type']:'';
				$data_arr = $multiPorts->gey_type_Menu($ad_type);
			break;
			case 'createMenu':
				$menu_data = json_decode($data,true);
				$data_arr = $multiPorts->create_Menu($menu_data);
			break;
			case 'getMyPowerMenu':
				$menu_data = json_decode($data,true);
				$data_arr = $multiPorts->get_my_power_menu($menu_data);
			break;
			case 'applyMoney':
				$arr = json_decode($data,true);
				$data_arr = $multiPorts->merchant_apply_money($arr);
			break;
			case 'createCode':
				$data_arr = $multiPorts->create_activity_code();
			break;
			default :
				break;
		}
		$out_data['code'] = $multiPorts->get_error_code();
		$out_data['msg'] = $multiPorts->get_error_des();
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