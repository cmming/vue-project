<?php
//函数由给定的地址重定向
//参数 $location 重定向地址
function open_redirect($location)
{
	if(is_string($location))
		header('location:'.$location);
	exit();
}
//将秒变为小时分
function get_hour_by_miao($miao)
{
	$hour = floor($miao/3600);
	$minute = floor(($miao-3600*$hour)/60);
	$second = floor((($miao-3600*$hour)-60*$minute)%60);
	return $hour.'时'.$minute.'分'.$second.'秒';
}
//验证登录
function check_login()
{
	$ret = false;
	global $Admin_ID;
	if($Admin_ID)
	{
		if($Admin_ID == $_SESSION['HTC_LOGIN_DATA']['ad_id'])
			$ret = true;
	}
	return $ret;
	//return true;
}
//验证是否为平台管理员
function check_admin()
{
	$ret = false;
	$ad_type = isset($_SESSION['HTC_LOGIN_DATA']['ad_type'])?$_SESSION['HTC_LOGIN_DATA']['ad_type']:'';
	if($ad_type == 1)
		$ret = true;

	return $ret;
}
//验证是否为商户
function check_merchant()
{
	$ret = false;
	$ad_type = isset($_SESSION['HTC_LOGIN_DATA']['ad_type'])?$_SESSION['HTC_LOGIN_DATA']['ad_type']:'';
	if($ad_type == 2)
		$ret = true;

	return $ret;
}
//获得本月的第一天和最后一天
function getMonth($date)
{
     $firstday = date("Y-m-01",strtotime($date));
     $lastday = date("Y-m-d",strtotime("$firstday +1 month -1 day"));
     return array($firstday,$lastday);
}
//获得上个月的第一天和最后一天
function getlastMonthDays($date){
     $timestamp=strtotime($date);
     $firstday=date('Y-m-01',strtotime(date('Y',$timestamp).'-'.(date('m',$timestamp)-1).'-01'));
     $lastday=date('Y-m-d',strtotime("$firstday +1 month -1 day"));
     return array($firstday,$lastday);
 }
 //生成平台订单号
function create_orderid($uid)
{
	return date('YmdHis').$uid;
}
 //获取用户用于支付的二维码
function get_user_pay_ewcode($gid,$amount,$paytype,$spid,$pid)
{
	$result=array('oid'=>'','qrcode'=>'');

	if($gid&&$paytype&&is_numeric($amount)&&$amount>0)
	{
		require_once API_FILE_ROOT_PATH.'class/BlueSDK/BlueSDK.config.php';
		require_once API_FILE_ROOT_PATH.'class/BlueSDK/BlueSDK_Processor.Class.php';
		require_once API_FILE_ROOT_PATH.'com/phpqrcode.php';

		$pay_sdk = new BlueSDK_Processor(CP_APP_ID,CP_APP_KEY,CP_APP_SECRET);
		//透传参数，原样返回	
		$a_name = "vrmall";
		$u_name = $spid;
		//$cpoid=date("YmdHis",time())."_".$spid;	
		$mid = isset($_SESSION['HTC_LOGIN_DATA']['ad_tid'])?$_SESSION['HTC_LOGIN_DATA']['ad_tid']:'';
		$cpoid=create_orderid($mid);
		$arg_arr=array('cp_uid'=>$spid,''=>$u_name,'type'=>$paytype,'pay'=>$amount,'oid'=>$cpoid);
		$ext=base64_encode(json_encode($arg_arr));
		
		switch($paytype)
		{
			case 'zfb':
				if($pay_sdk->get_order(BlueSDK_Processor::PT_ALIPAY_F2F,$mid,$amount,$gid.'_'.$pid,$spid,$u_name,$a_name,$cpoid,"blue_vr_mall",0,WP_PAY_RECV_URL,$ext))
				{
					$result['oid']=$pay_sdk->_order_id;
					ob_clean();
					ob_start();
					QRcode::png($pay_sdk->_qrcode,false,QR_ECLEVEL_L,5);
					$pic_data = ob_get_contents();
					ob_end_clean();
					$result['qrcode'] = base64_encode($pic_data);		//二维码图像

					$data['id']=$cpoid;
					$data['oid']=$pay_sdk->_order_id;
					$data['mid']=$pay_sdk->_pay_user_id;
					$data['eid']=$gid;
					$data['money']=$pay_sdk->_amount;
					$data['pid']=$pid;
					$data['paytype']=$pay_sdk->_pay_type;

					//将订单信息写入数据库
					$data_oper = new data_operation();
					$data_oper->insert_into_order($data);
				}else{
					Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,"远程接口调用失败",Core_Logger::LOG_LEVL_ERROR);
				}
				break;
			case 'weixin':
				if($pay_sdk->get_order(BlueSDK_Processor::PT_WXPAY_NATIVE,$mid,$amount,$gid.'_'.$pid,$spid,$u_name,"vrmall",$cpoid,"blue_vr_mall",0,WP_PAY_RECV_URL,$ext))
				{
					$result['oid']=$pay_sdk->_order_id;

					ob_start();
					QRcode::png($pay_sdk->_qrcode,false,QR_ECLEVEL_L,5);
					$pic_data = ob_get_contents();
					ob_end_clean();
					$result['qrcode'] = base64_encode($pic_data);		//二维码图像

					$data['id']=$cpoid;
					$data['oid']=$pay_sdk->_order_id;
					$data['mid']=$pay_sdk->_pay_user_id;
					$data['eid']=$gid;
					$data['money']=$pay_sdk->_amount;
					$data['pid']=$pid;
					$data['paytype']=$pay_sdk->_pay_type;

					//将订单信息写入数据库
					$data_oper = new data_operation();
					$data_oper->insert_into_order($data);
				}else{
					Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,"远程接口调用失败",Core_Logger::LOG_LEVL_ERROR);
				}
				break;
			default:
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,"支付类型错误",Core_Logger::LOG_LEVL_ERROR);
				break;
		}
	}else{
		Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,"参数不合法",Core_Logger::LOG_LEVL_ERROR);
	}
	return $result;
}
//退出登录
function loginout()
{
	unset($_SESSION['HTC_LOGIN_DATA']);
	session_destroy();
}
//根据数据创建表格
function create_bill_excel($mid,$data_arr)
{
	$str = '暂时还没有数据';
	$admin = new classAdmin();
	$name = '所有商户';
	if($mid)
	{
		$merchant_info = $admin->get_merchant_info_byid($mid);
		$name = isset($merchant_info['name'])?$merchant_info['name']:'未知';
	}
	if(!empty($data_arr))
	{
		global $paytype_config_arr;
		$paytype_arr = $mid_arr = array();
		$all_money = 0;
		$str = '<table class="table text-center table-bordered table-responsive table-striped table-hover"><tr align="center"><th class="text-" colspan=4>账单信息&nbsp;&nbsp;[&nbsp;商户&nbsp;：'.$name.'&nbsp;]</th></tr>';
		$detail_str = '<tr><td colspan=4>详细信息</td></tr><tr align="center" class="table-name"><td>商户</td><td>时间</td><td>金额/元</td><td>支付方式</td></tr>';
		foreach($data_arr as $num_arr)
		{
			$all_money += $num_arr['money'];
			if(isset($paytype_arr[$num_arr['paytype']]))
				$paytype_arr[$num_arr['paytype']] += $num_arr['money'];
			else
				$paytype_arr[$num_arr['paytype']] = $num_arr['money'];

			if(isset($mid_arr[$num_arr['mid']]))
				$mid_arr[$num_arr['mid']] += $num_arr['money'];
			else
				$mid_arr[$num_arr['mid']] = $num_arr['money'];

			$paytype = isset($paytype_config_arr[$num_arr['paytype']])?$paytype_config_arr[$num_arr['paytype']]:'未知';

			$detail_str .= '<tr align="center"><td>'.$num_arr['mid'].'</td><td>'.$num_arr['time'].'</td><td>'.$num_arr['money'].'</td><td>'.$paytype.'</td></tr>';
		}
		
		$all_str = '<tr align="center"><td colspan=4  align="left">合计 :  <span id="allMoney">'.$all_money.'</span>元</td></tr><tr align="center" class="table-name">';
		foreach($paytype_arr as $paytype=>$money)
		{
			$paytype = isset($paytype_config_arr[$paytype])?$paytype_config_arr[$paytype]:'未知';
			$all_str .= '<td colspan=2>'.$paytype.'</td>';
		}
		$all_str .= '</tr><tr align="center">';
		foreach($paytype_arr as $paytype=>$money)
			$all_str .= '<td colspan=2>'.$money.'</td>';
		$all_str .= '</tr>';
		
		$mid_str = '';
		if(!$mid)
		{
			$mid_str = '<tr align="center"><td colspan=4  align="left">商户详情</td></tr>';
			foreach($mid_arr as $mname=>$money)
			{
				$mid_str .= '<tr><td colspan=2>'.$mname.'</td><td colspan=2>'.$money.'</td></tr>';
			}
		}

		$str .= $all_str.$mid_str.$detail_str.'</table>';	
	}
	return $str;
}
//生成激活码
function create_activity_code($num)
{
	//return date('Ymd').str_pad(mt_rand(1, 99999), 5,$num, STR_PAD_LEFT);

	list($msec, $sec) = explode(" ",microtime()); 
	$n_sec = mt_rand(1,9).mt_rand(100,599).mt_rand(299,999);
	$n_msec = mt_rand(1,9).substr($msec,2,6);

	$cnum = "";
	for($j=0;$j<7;++$j)
	{
		$cnum = $cnum.$n_msec[$j].$n_sec[$j];
	}
	return $cnum;
}
//将数组转化为字符串
function array2string($array,$flag){
 
    $string = array();
 
    if($array && is_array($array)){
 
        foreach ($array as $key=> $value){
			if(is_array($value))
			{
				$str = $key.':{';
				foreach($value as $k=>$v)
					$str .= '['.$k.':'.$v.']';
				$string[] = $str.'}';
			}
			else
				$string[] = $key.':'.$value;
        }
    }
 
    return implode($flag,$string);
}
?>