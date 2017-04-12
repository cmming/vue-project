<?php
////////////////////////////////////////////////////////////////
//搜索数据接口
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
$logger->setLogFileName("searchData");
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
		$data_operation = new data_operation();
		switch($type)
		{
			case 'payorder':
			{
				$data_arr = $data_operation->search_order($search);
			}
			break;
			case 'indexdata':
			{
				$data_arr = $data_operation->get_index_data($search);
			}
			break;
			case 'getMyDevice':
			{
				$data_arr = $data_operation->get_my_device($search);
			}
			break;
			case 'getMyPackage':
			{
				$data_arr = $data_operation->get_my_package($search);
			}
			break;
			case 'getMyBill':
			{
				$data_arr = $data_operation->get_my_bill($search);
			}
			break;
			case 'getMerchantMoney':
			{
				$data_arr = $data_operation->get_merchant_money($search);
			}
			break;
			case 'getDemendsInfo':
			{
				$data_arr = $data_operation->get_demends_info_bymid($search);
			}
			break;
			case 'getShip':
			{
				$data_arr = $data_operation->get_ship_info_bytid($search);
			}
			break;
			case 'getBillPayRecord':
			{
				$data_arr = $data_operation->get_bill_pay_record($search);
			}
			break;
			case 'getActivityCode':
			{
				$data_arr = $data_operation->get_activity_code_list($search);
			}
			break;
			case 'getTermPackage':
			{
				$data_arr = $data_operation->get_term_package($search);
			}
			break;
			case 'getMerchantBalance':
			{
				$data_arr = $data_operation->get_merchant_banlance();
			}
			break;
			case 'getAllMerchantBalance':
			{
				$data_arr = $data_operation->get_all_merchant_banlance($search);
			}
			break;
			case 'getCommonDemendsInfo':            //获得官方的提现要求
			{
				$data_arr = $data_operation->get_demends_info();
			}
			break;
			case 'getCodeNums':            //获得可用激活码个数
			{
				$data_arr = $data_operation->get_code_nums();
			}
			break;
			case 'getAdminLog':
			{
				$data_arr = $data_operation->get_admin_log($search);
			}
			break;
			default :
				break;
		}
		$out_data['code'] = $data_operation->get_error_code();
		$out_data['msg'] = $data_operation->get_error_des();
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