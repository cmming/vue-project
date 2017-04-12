<?php
//支付回调页面
//wl 20160908
////////////////////////////////////////////////////////
require_once 'common.inc.php';
require_once 'class/BlueSDK/BlueSDK.config.php';
require_once 'class/BlueSDK/BlueSDK_Processor.Class.php';

//创建日志类
$logger = Core_Logger::getInstance();

//////////////////////////////////////////////////////////////////////
//设置日志输出级别
//$logger->setLogLevel(Core_Logger::LOG_LEVL_NO);		//无日志
$logger->setLogLevel(Core_Logger::LOG_LEVL_DEBUG|Core_Logger::LOG_LEVL_ERROR|Core_Logger::LOG_LEVL_WARNING|Core_Logger::LOG_LEVL_DATA);	//仅输出错误,警告日志,数据记录日志
//设置针对每个应用设置日志文件，方便查询
$logger->setLogPath(PATH_SYS_LOG);
//设置日志文件路径
$logger->setLogFileName("PayRecv");

//设置数据文件保存路径
$logger->setDataPath(PATH_SYS_LOG);
//直接保存文件
$logger->setDataFileName("PayRecv");


$logger->writeLog(__METHOD__.":".__LINE__,http_build_query($_REQUEST),Core_Logger::LOG_LEVL_DEBUG);


//创建处理器
$processor = new BlueSDK_Processor(CP_APP_ID,CP_APP_KEY,CP_APP_SECRET);
//设置日志路径
$processor->setLogPath(PATH_SYS_LOG.'recv_'.date('Ymd').'.log');

$deal_ret = false;
//校验数据合法性
$tmp_oid = isset($_REQUEST['order_id'])?trim($_REQUEST['order_id']):'';
if($tmp_oid)
{
	if($processor->check_notify_data($_REQUEST))
	{
		$deal_ret = deal_cp_app_logic($processor);
	}else{
		$logger->writeLog(__METHOD__.":".__LINE__,"订单校验失败 oid=".$tmp_oid,Core_Logger::LOG_LEVL_WARNING);
	}
}else{
	$logger->writeLog(__METHOD__.":".__LINE__,"订单不存在 oid=".$tmp_oid,Core_Logger::LOG_LEVL_WARNING);
}

//输出结果
$processor->echo_result($deal_ret);

//////////////////////////////////////////////////////////////////////////////////////////////
//以下方法需要CP自己处理
//////////////////////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////
//功　	能：CP应用内逻辑处理
//入口参数：$processor - 支付处理对象，所有字段已解析，可用
//返 回 值：true	- 数据合法，解析成功 
//			false	- 数据非法，解析失败
//说　　明：需要注意订单的合法性，以及订单是否被重复处理
//			晃游平台不保证订单支付结果不被重复通知
///////////////////////////////////////////////
function deal_cp_app_logic($processor)
{
	$ret = false;
	//////////////////////////////////////////////
	//需要注意订单的合法性，以及订单是否被重复处理
	//晃游平台不保证订单支付结果不被重复通知
	//////////////////////////////////////////////
	//TODO:逻辑处理
	$arg_arr=json_decode(base64_decode($processor->_ext),true);
	if(is_array($arg_arr)&&!empty($arg_arr)&&isset($arg_arr['cp_uid'])&&isset($arg_arr['pay']))
	{
		$eid = $pid = '';
		$str = $processor->_cp_product_id;
		if($str)
		{
			$str_arr = explode('_',$str);
			$eid = isset($str_arr[0])?$str_arr[0]:'';
			$pid = isset($str_arr[1])?$str_arr[1]:'';
		}
		$data['id']=$pay_sdk->_cp_oid;
		$data['oid']=$processor->_order_id;
		$data['mid']=$processor->_pay_user_id;
		$data['eid']=$eid;
		$data['money']=$processor->_amount;
		$data['paytype']=$processor->_pay_type;
		$data['pid']=$pid;
		/*
		$data['id']='2016091808435617';
		$data['oid']='aab3238922bcc25a6f606eb525ffdc56';
		$data['mid']=4;
		$data['eid']=1;
		$data['money']=1;
		$data['paytype']=102;
		$data['pid']=1;*/
		
		$data_str = json_encode($data);

		Core_Logger::getInstance()->writeLog("RECV",$data_str,Core_Logger::LOG_LEVL_DATA);

		//$suc_file = PATH_ORDER_SUC.$processor->_order_id.".txt";
		$data_operation = new data_operation();
		//判断该订单是否属于订单表中
		if($data_operation->check_order_by_oid($data['id']))
		{
			if($data_operation->insert_into_pay_record($data))
			{
				//////////////////////////////////////////////
				//发放订单
				$data_arr = array('mid'=>$processor->_pay_user_id,'time'=>time(),'eid'=>$eid,'pid'=>$pid);
				//$data_arr = array('mid'=>$data['mid'],'time'=>time(),'eid'=>$data['eid'],'pid'=>$data['pid']);
				if(!$data_operation->insert_into_user_term($data_arr))
					$logger->writeLog(__METHOD__.":".__LINE__,"发放订单失败".json_encode($data),Core_Logger::LOG_LEVL_WARNING);
				else  //跟新付费成功表
				{
					if($data_operation->update_pay_record_state($data['id']))
						$ret = true;
					else
						$logger->writeLog(__METHOD__.":".__LINE__,"跟新付费成果表状态失败 oid=".$data['id'],Core_Logger::LOG_LEVL_WARNING);
				}
			}else{
				$logger->writeLog(__METHOD__.":".__LINE__,"保存订单信息失败 oid=".$processor->_order_id,Core_Logger::LOG_LEVL_WARNING);
			}
		}
		else
			$logger->writeLog(__METHOD__.":".__LINE__,"异常订单 oid=".$data['id'],Core_Logger::LOG_LEVL_WARNING);
	}else{
		$logger->writeLog(__METHOD__.":".__LINE__,"扩展参数不正确 oid=".$processor->_order_id,Core_Logger::LOG_LEVL_WARNING);
	}
	return $ret;
}





?>
