<?php
//数据操作类
//wl 2016-09-14

class data_operation{

	//日志级别	
	const LOG_LEVL_NO = 0;					//无日志
	const LOG_LEVL_ERROR = 0x01;			//错误日志
	const LOG_LEVL_WARNING = 0x02;			//警告日志
	const LOG_LEVL_DEBUG = 0x04;			//调式日志

	//日志文件保存路径
	private $_logPath = "";
	//日志文件名称
	private $_logFileName = 'default_log.log';
	//日志级别开关
	private $_logLevel = 0;
	//日志会话索引
	private $_log_sess = "";
	//日志条数索引
	private $_log_index = 0;

	//mysql数据库对象
	protected $mysql=null;

	//错误码
	private $_error_code = "";
	//错误描述
	private $_error_des = "";

	private static $_paytype_config_arr = array('102'=>'微信','105'=>'支付宝');
	private static $_apply_bill_arr = array('0'=>'申请已提交，等待授理','1'=>'申请已授理','2'=>'授理已完成','3'=>'申请驳回');

	private static $_table_name_arr = array('t_admin'=>'管理员','t_order'=>'订单','t_pay_record'=>'扣费成功','t_merchant'=>'商户','t_package'=>'套餐','t_term'=>'设备','t_user_term'=>'商户设备','t_merchant_bill_day'=>'结算','t_merchant_bill_demands'=>'提现要求','t_package_term_ship'=>'套餐设备关系','t_merchant_code_ship'=>'商户激活码','t_code'=>'激活码','t_mercahnt_balance'=>'余额','t_apply_bill'=>'提现申请','t_bill_pay_record'=>'提现记录');
	private static $_admin_act_arr = array('add'=>'添加','delete'=>'删除','update'=>'修改');

	//获得错误码
	public function get_error_code()
	{
		return $this->_error_code;
	}
	//获得错误描述
	public function get_error_des()
	{
		return $this->_error_des;
	}

	//初始化mysql数据库对象
	private function init_mysql_obj()
	{
		$ret = false;
		if($this->mysql == null)
		{
			$this->mysql = new Core_DBOper;
			//创建对象失败
			if($this->mysql == null)
			{
				//抛出异常，告知用户同时写入日志
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,"创建数据库对象失败",Core_Logger::LOG_LEVL_ERROR);
				throw new Core_Exception(Core_Exception::CODE_FAILED_CREATE_OBJ);
			}
			else
			{
				if($this->mysql->DB_Connect_CenterDB() == false)
				{
					Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,"连接数据库失败",Core_Logger::LOG_LEVL_ERROR);
					//抛出异常，告知用户同时写入日志
					throw new Core_Exception(Core_Exception::CODE_DB_CONNECT_ERROR);
				}
				else
					$ret = true;
			}
		}
		else
			$ret = true;

		return $ret;
	}
	//搜所平台管理员订单
	public function search_admin_order($search_arr)
	{
		$data_arr = array();
		if($this->init_mysql_obj())
		{
			$btime = isset($search_arr['btime'])?strtotime(mysql_real_escape_string($search_arr['btime'])):'';
			$etime = (isset($search_arr['etime'])&&!empty($search_arr['etime']))?(strtotime(mysql_real_escape_string($search_arr['etime'])) + 86399):'';
			$paytype = isset($search_arr['paytype'])?mysql_real_escape_string($search_arr['paytype']):'';
			$mid = isset($search_arr['mid'])?mysql_real_escape_string($search_arr['mid']):'';
			//cm 2017-2-17  page 为-1时不分页
			//$page = isset($search_arr['page'])?mysql_real_escape_string($search_arr['page']):1;
			$isAllpage=true;
			if(isset($search_arr['page'])){
			    $page=mysql_real_escape_string($search_arr['page']);
			    if($page=='-1'){
			        $isAllpage=false;
			    }else{
			        $page = isset($search_arr['page'])?mysql_real_escape_string($search_arr['page']):1;
			    }
			}
			$bnums = ($page-1)*PAGE_NUMS;
			// cm 关键字关联订单号
			$keyword=isset($search_arr['keyword'])?mysql_real_escape_string($search_arr['keyword']):'';

			$sql = 'select * from t_pay_record where 1=1 ';
			if($btime)
				$sql .= ' and unix_timestamp(ctime)>='.$btime;
			if($etime)
				$sql .= ' and unix_timestamp(ctime)<='.$etime;
			if($paytype)
				$sql .= ' and paytype='.$paytype;
			if($mid)
				$sql .= ' and mid='.$mid;
			if($keyword)
			{
				$top_c = substr($keyword,0,1);
				if(strcmp($top_c,'J') == 0)
					$sql .= ' and id like "%'.$keyword.'%"';
				else
					$sql .= ' and pay_sp_oid like "%'.$keyword.'%"';
			}
			$allnums = $this->mysql->DB_Query_Res_And_Count($sql);
			$allpage = ceil($allnums/PAGE_NUMS);
            //cm 2017-2-17  page 为-1时不分页   $isAllpage
             if($isAllpage){
                $sql .= ' limit '.$bnums.','.PAGE_NUMS;
             }
			//$sql .= ' limit '.$bnums.','.PAGE_NUMS;
			if($result = $this->mysql->do_Query($sql))
			{
				while($row = $this->mysql->DB_fetch_array($result))
				{
					$tmp_arr = array();
					$tmp_arr['id'] = $row['id'];
					$tmp_arr['oid'] = '****'.substr($row['pay_sp_oid'],-8);
					$tmp_arr['ctime'] = $row['ctime'];
					$tmp_arr['mtime'] = date('Y-m-d H:i:s',$row['mtime']);
					$tmp_arr['gtime'] = get_hour_by_miao($row['gtime']);
					$tmp_arr['uid'] = $row['uid'];
					// 用户编号
					$tmp_arr['mid_num']=$row['mid'];
					//根据活动ID获得活动信息
					$package_info = $this->get_package_info_byid($row['pid']);
					//根据商户ID获得商户信息
					$admin = new classAdmin();
					$merchant_info = $admin->get_merchant_info_byid($row['mid']);
					//var_dump($this->mysql);exit();

					$tmp_arr['mid'] = isset($merchant_info['name'])?$merchant_info['name']:'未知商户';
					$tmp_arr['money'] = round($row['money']/100,2);
					$tmp_arr['paytype'] = isset(self::$_paytype_config_arr[$row['paytype']])?self::$_paytype_config_arr[$row['paytype']]:'未知';
					$tmp_arr['pid'] = isset($package_info['name'])?$package_info['name']:'未知活动';
					$data_arr['data'][] = $tmp_arr;
				}
				$order_arr = $this->get_page_top_order_nums();
				$data_arr['yOrder'] = $order_arr['yOrder'];
				$data_arr['mOrder'] = $order_arr['mOrder'];
				$data_arr['aOrder'] =$order_arr['aOrder'];
				$data_arr['allNums'] = $allnums;
				$data_arr['allpage'] = $allpage;
				$this->_error_code = Core_Exception::CODE_DEAL_OK;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $data_arr;
	}
	//搜所订单
	public function search_merchant_order($search_arr)
	{
		$data_arr = array();
		if($this->init_mysql_obj())
		{
			$btime = isset($search_arr['btime'])?strtotime(mysql_real_escape_string($search_arr['btime'])):'';
			$etime = (isset($search_arr['etime'])&&!empty($search_arr['etime']))?(strtotime(mysql_real_escape_string($search_arr['etime'])) + 86399):'';
			$paytype = isset($search_arr['paytype'])?mysql_real_escape_string($search_arr['paytype']):'';
			//cm 2017-2-17  page 为-1时不分页
            //$page = isset($search_arr['page'])?mysql_real_escape_string($search_arr['page']):1;
            $isAllpage=true;
            if(isset($search_arr['page'])){
                $page=mysql_real_escape_string($search_arr['page']);
                if($page=='-1'){
                    $isAllpage=false;
                 }else{
            	    $page = isset($search_arr['page'])?mysql_real_escape_string($search_arr['page']):1;
            	 }
            }
			$bnums = ($page-1)*PAGE_NUMS;

			$mid = isset($_SESSION['HTC_LOGIN_DATA']['ad_tid'])?$_SESSION['HTC_LOGIN_DATA']['ad_tid']:'';
			// cm 关键字关联订单号
			$keyword=isset($search_arr['keyword'])?mysql_real_escape_string($search_arr['keyword']):'';

			$sql = 'select * from t_pay_record where mid='.$mid.' ';
			if($btime)
				$sql .= ' and unix_timestamp(ctime)>='.$btime;
			if($etime)
				$sql .= ' and unix_timestamp(ctime)<='.$etime;
			if($paytype)
				$sql .= ' and paytype='.$paytype;
			if($keyword)
			{
				$top_c = substr($keyword,0,1);
				if(strcmp($top_c,'J') == 0)
					$sql .= ' and id like "%'.$keyword.'%"';
				else
					$sql .= ' and pay_sp_oid like "%'.$keyword.'%"';
			}
			
			$allnums = $this->mysql->DB_Query_Res_And_Count($sql);
			$allpage = ceil($allnums/PAGE_NUMS);

			//cm 2017-2-17  page 为-1时不分页   $isAllpage
            if($isAllpage){
               $sql .= ' limit '.$bnums.','.PAGE_NUMS;
            }
            //$sql .= ' limit '.$bnums.','.PAGE_NUMS;
			if($result = $this->mysql->do_Query($sql))
			{
				while($row = $this->mysql->DB_fetch_array($result))
				{
					$tmp_arr = array();
					$tmp_arr['id'] = $row['id'];
					$tmp_arr['oid'] = '****'.substr($row['pay_sp_oid'],-8);
					$tmp_arr['ctime'] = $row['ctime'];
					$tmp_arr['mtime'] = date('Y-m-d H:i:s',$row['mtime']);
					$tmp_arr['gtime'] = get_hour_by_miao($row['gtime']);
					$tmp_arr['uid'] = $row['uid'];
					//根据活动ID获得活动信息
					$package_info = $this->get_package_info_byid($row['pid']);
					$tmp_arr['money'] = round($row['money']/100,2);
					$tmp_arr['paytype'] = isset(self::$_paytype_config_arr[$row['paytype']])?self::$_paytype_config_arr[$row['paytype']]:'未知';
					$tmp_arr['pid'] = isset($package_info['name'])?$package_info['name']:'未知活动';
					$data_arr['data'][] = $tmp_arr;
				}
				$order_arr = $this->get_page_top_order_nums($mid);
				$data_arr['yOrder'] = $order_arr['yOrder'];
				$data_arr['mOrder'] = $order_arr['mOrder'];
				$data_arr['aOrder'] = $order_arr['aOrder'];
				$data_arr['allNums'] = $allnums;
				$data_arr['allpage'] = $allpage;
				$this->_error_code = Core_Exception::CODE_DEAL_OK;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $data_arr;
	}
	public function search_order($search_arr)
	{
		$data_arr = array();
		if(check_admin())
			$data_arr = $this->search_admin_order($search_arr);
		else
			$data_arr = $this->search_merchant_order($search_arr);

		return $data_arr;
	}
	//根据活动ID获得活动信息
	public function get_package_info_byid($id)
	{
		$data_arr = array();
		if($this->init_mysql_obj())
		{
			$id = mysql_real_escape_string($id);
			$sql = 'select * from t_package where id='.$id;
			if($result = $this->mysql->do_Query($sql))
			{
				$row = $this->mysql->DB_fetch_array($result,MYSQL_ASSOC);
				if(!empty($row))
				{
					$this->_error_code = Core_Exception::CODE_DEAL_OK;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
					$data_arr = $row;
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $data_arr;
	}
	//根据设备ID获得设备信息
	public function get_device_info_byid($id)
	{
		$data_arr = array();
		if($this->init_mysql_obj())
		{
			$id = mysql_real_escape_string($id);
			$sql = 'select * from t_term where id='.$id;
			if($result = $this->mysql->do_Query($sql))
			{
				$row = $this->mysql->DB_fetch_array($result,MYSQL_ASSOC);
				if(!empty($row))
				{
					$this->_error_code = Core_Exception::CODE_DEAL_OK;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
					$data_arr = $row;
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $data_arr;
	}
	//根据条件获得营业额
	public function get_tumover_by_con($search_arr)
	{
		$tumover = 0;
		if($this->init_mysql_obj())
		{
			$btime = isset($search_arr['btime'])?mysql_real_escape_string($search_arr['btime']):'';
			$etime = isset($search_arr['etime'])?mysql_real_escape_string($search_arr['etime']):'';
			$paytype = isset($search_arr['paytype'])?mysql_real_escape_string($search_arr['paytype']):'';
			$mid = isset($search_arr['mid'])?mysql_real_escape_string($search_arr['mid']):'';

			$sql = 'select sum(money) as tumover from t_pay_record where 1=1 ';
			if($btime)
				$sql .= ' and unix_timestamp(ctime)>='.$btime;
			if($etime)
				$sql .= ' and unix_timestamp(ctime)<='.$etime;
			if($paytype)
				$sql .= ' and paytype='.$paytype;
			if($mid)
				$sql .= ' and mid='.$mid;
			
			if($result = $this->mysql->do_Query($sql))
			{
				if($row = $this->mysql->DB_fetch_array($result))
				{
					$tumover = round($row['tumover']/100,2);
					$this->_error_code = Core_Exception::CODE_DEAL_OK;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $tumover;
	}
	//根据条件获得营业额  订单的笔数
	public function get_order_nums_by_con($search_arr)
	{
		$order_num = 0;
		if($this->init_mysql_obj())
		{
			$btime = isset($search_arr['btime'])?mysql_real_escape_string($search_arr['btime']):'';
			$etime = isset($search_arr['etime'])?mysql_real_escape_string($search_arr['etime']):'';
			$paytype = isset($search_arr['paytype'])?mysql_real_escape_string($search_arr['paytype']):'';
			$mid = isset($search_arr['mid'])?mysql_real_escape_string($search_arr['mid']):'';
			// cm 关键字关联订单号
			$keyword=isset($search_arr['keyword'])?mysql_real_escape_string($search_arr['keyword']):'';

			$sql = 'select id from t_pay_record where 1=1 ';
			if($btime)
				$sql .= ' and unix_timestamp(ctime)>='.$btime;
			if($etime)
				$sql .= ' and unix_timestamp(ctime)<='.$etime;
			if($paytype)
				$sql .= ' and paytype='.$paytype;
			if($mid)
				$sql .= ' and mid='.$mid;
			if($keyword)
				$sql .= ' and id like "%'.$keyword.'%"';
			
			$order_num = $this->mysql->DB_Query_Res_And_Count($sql);
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $order_num;
	}
	//获得客单价/数
	public function get_guestUnit_priceandnums($month,$mid='')
	{
		$data_arr = array();
		if($this->init_mysql_obj())
		{
			$mid = mysql_real_escape_string($mid);
			$month_arr = getMonth($month);
			$btime = $month_arr[0];
			$etime = $month_arr[1];
			$nowdate = date('Y-m-d');
			while($btime<=$etime)
			{
				$day = date('d',strtotime($btime));
				$data_arr['day'][] = $day;
				//本月还未到的天数
				if($btime<=$nowdate)
				{
					$btime_str = strtotime($btime);
					$etime_str = $btime_str + 86399;
					$sql = 'select sum(money) as money from t_pay_record where unix_timestamp(ctime)>='.$btime_str.' and unix_timestamp(ctime)<='.$etime_str;
					if($mid)
						$sql .= ' and mid='.$mid;

					if($result = $this->mysql->do_Query($sql))
					{
						if($row = $this->mysql->DB_fetch_array($result))
						{
							$data_arr['price'][]=isset($row['money'])?round($row['money']/100,2):0;
						}
					}
					else
					{
						$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
						Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
					}
					$sql = 'select distinct uid from t_pay_record where unix_timestamp(ctime)>='.$btime_str.' and unix_timestamp(ctime)<='.$etime_str;
					if($mid)
						$sql .= ' and mid='.$mid;

					$data_arr['nums'][] = $this->mysql->DB_Query_Res_And_Count($sql);
				}
				else
				{
					$data_arr['price'][]=0;
					$data_arr['nums'][]=0;
				}
				$btime = date('Y-m-d',strtotime($btime)+86400);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $data_arr;
	}
	//商户营业排行
	public function get_mtRanking($month)
	{
		$data_arr = array('name'=>array(),'money'=>array());
		if($this->init_mysql_obj())
		{
			$month_arr = getMonth($month);
			$btime_str = date('Ymd',strtotime($month_arr[0]));
			$etime_str = date('Ymd',strtotime($month_arr[1]));
			$sql = 'select mid,sum(money) as money from t_merchant_bill_day where time>='.$btime_str.' and time<='.$etime_str.' group by mid order by money DESC limit 0,4';
			if($result = $this->mysql->do_Query($sql))
			{
				while($row = $this->mysql->DB_fetch_array($result))
				{
					//根据商户ID获得商户信息
					$admin = new classAdmin();
					$merchant_info = $admin->get_merchant_info_byid($row['mid']);
					$data_arr['name'][] = isset($merchant_info['name'])?$merchant_info['name']:'未知商户';
					$data_arr['money'][] = isset($row['money'])?$row['money']:'0';
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $data_arr;
	}
	//获得首页的数据
	public function get_admin_index_data($search_arr)
	{
		$data_arr = array();
		if($this->init_mysql_obj())
		{
			if(isset($search_arr['guest_time']) && isset($search_arr['m_time']))
			{
				$guest_time = !empty($search_arr['guest_time'])?mysql_real_escape_string($search_arr['guest_time']):date('Y-m');
				$m_time = !empty($search_arr['m_time'])?mysql_real_escape_string($search_arr['m_time']):date('Y-m');
				$order_arr = $this->get_page_top_order_nums();
				$tumover_arr = $this->get_index_part_data();
				//客单价/数
				$guestUnit_arr = $this->get_guestUnit_priceandnums($guest_time);
				//商户营业排行
				$mtRanking = $this->get_mtRanking($m_time);
				//周游戏排行
				//$wgRanking = $this->wgRanking();
				$wgRanking = array();
				
				$data_arr['yOrder'] = $order_arr['yOrder'];
				$data_arr['mOrder'] = $order_arr['mOrder'];
				$data_arr['tTumover'] = $tumover_arr['tTumover'];
				$data_arr['yTumover'] = $tumover_arr['yTumover'];
				$data_arr['mTumover'] = $tumover_arr['mTumover'];
				$data_arr['tRate'] = $tumover_arr['tRate'];
				$data_arr['yRate'] = $tumover_arr['yRate'];
				$data_arr['mRate'] = $tumover_arr['mRate'];
				$data_arr['guestUnit'] = $guestUnit_arr;
				$data_arr['mtRanking'] = $mtRanking;
				$data_arr['wgRanking'] = $wgRanking;

				$this->_error_code = Core_Exception::CODE_DEAL_OK;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_FORMDATA_BAD;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_FORMDATA_BAD);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $data_arr;
	}
	//获得首页的数据
	public function get_merchant_index_data($search_arr)
	{
		$data_arr = array();
		if($this->init_mysql_obj())
		{
			if(isset($search_arr['guest_time']))
			{
				$guest_time = !empty($search_arr['guest_time'])?mysql_real_escape_string($search_arr['guest_time']):date('Y-m');
				$mid = isset($_SESSION['HTC_LOGIN_DATA']['ad_tid'])?$_SESSION['HTC_LOGIN_DATA']['ad_tid']:'';
				$order_arr = $this->get_page_top_order_nums($mid);
				$tumover_arr = $this->get_index_part_data($mid);
				//客单价/数
				$guestUnit_arr = $this->get_guestUnit_priceandnums($guest_time,$mid);
				//周游戏排行
				//$wgRanking = $this->wgRanking();
				$wgRanking = array();

				$data_arr['yOrder'] = $order_arr['yOrder'];
				$data_arr['mOrder'] = $order_arr['mOrder'];
				$data_arr['tTumover'] = $tumover_arr['tTumover'];
				$data_arr['yTumover'] = $tumover_arr['yTumover'];
				$data_arr['mTumover'] = $tumover_arr['mTumover'];
				$data_arr['tRate'] = $tumover_arr['tRate'];
				$data_arr['yRate'] = $tumover_arr['yRate'];
				$data_arr['mRate'] = $tumover_arr['mRate'];
				$data_arr['guestUnit'] = $guestUnit_arr;
				$data_arr['wgRanking'] = $wgRanking;

				$this->_error_code = Core_Exception::CODE_DEAL_OK;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_FORMDATA_BAD;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_FORMDATA_BAD);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $data_arr;
	}
	//获得首页的数据
	public function get_index_data($search_arr)
	{
		$data_arr = array();
		if(check_admin())
			$data_arr = $this->get_admin_index_data($search_arr);
		else
			$data_arr = $this->get_merchant_index_data($search_arr);

		return $data_arr;
	}
	//获得我的设备
	public function get_my_device($search_arr)
	{
		$data_arr = array();
		if($this->init_mysql_obj())
		{
			$btime = isset($search_arr['btime'])?mysql_real_escape_string($search_arr['btime']).' 00:00:00':'';
			$etime = (isset($search_arr['etime'])&&!empty($search_arr['etime']))?mysql_real_escape_string($search_arr['etime']).' 23:59:59':'';
			$eid = isset($search_arr['eid'])?mysql_real_escape_string($search_arr['eid']):'';
			$name = isset($search_arr['name'])?mysql_real_escape_string($search_arr['name']):'';
			$page = isset($search_arr['page'])?mysql_real_escape_string($search_arr['page']):1;
			$bnums = ($page-1)*PAGE_NUMS;
			if(check_admin())
				$mid = isset($search_arr['spid'])?mysql_real_escape_string($search_arr['spid']):'';
			else
				$mid = isset($_SESSION['HTC_LOGIN_DATA']['ad_tid'])?$_SESSION['HTC_LOGIN_DATA']['ad_tid']:'';

			$sql = 'select * from t_term where 1=1 ';
			if($mid)
				$sql .= ' and spid='.$mid;
			if($btime)
				$sql .= ' and ctime>='.$btime;
			if($etime)
				$sql .= ' and ctime<='.$etime;
			if($eid)
				$sql .= ' and id='.$eid;
			if($name)
				$sql .= ' and name like "%'.$name.'%"';
			
			$allnums = $this->mysql->DB_Query_Res_And_Count($sql);
			$allpage = ceil($allnums/PAGE_NUMS);

			$sql .= ' limit '.$bnums.','.PAGE_NUMS;
			
			if($result = $this->mysql->do_Query($sql))
			{
				while($row = $this->mysql->DB_fetch_array($result))
				{
					$tmp_arr = array();
					$tmp_arr['id'] = $row['id'];     	
					$tmp_arr['termid'] = $row['termid'];
					$tmp_arr['name'] = $row['name'];
					$tmp_arr['ctime'] = $row['ctime'];
					$tmp_arr['spid'] = $row['spid'];
					$tmp_arr['enable'] = $row['enable'];
					$tmp_arr['state'] = $row['state'];
					$data_arr['data'][] = $tmp_arr;
				}
			}
			if(check_merchant())
			{
				$order_arr = $this->get_page_top_order_nums($mid);
				$data_arr['yOrder'] = $order_arr['yOrder'];
				$data_arr['mOrder'] = $order_arr['mOrder'];
				$data_arr['aOrder'] = $order_arr['aOrder'];
				$data_arr['allNums'] = $allnums;
			}
			$data_arr['allpage'] = $allpage;
			$this->_error_code = Core_Exception::CODE_DEAL_OK;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $data_arr;
	}
	//将订单信息插入订单表
	public function insert_into_order($data)
	{
		$ret = false;
		if(is_array($data) && !empty($data))
		{
			if($this->init_mysql_obj())
			{
				foreach($data as $key=>$val)
				{
					$key = mysql_real_escape_string($key);
					$val = mysql_real_escape_string($val);
					$data[$key] = $val;
				}
				$sql = 'insert into t_order(id,oid,mid,money,paytype,pid,eid) values("'.$data['id'].'","'.$data['oid'].'","'.$data['mid'].'","'.$data['money'].'","'.$data['paytype'].'","'.$data['pid'].'","'.$data['eid'].'")';
				$result = $this->mysql->DB_Query($sql);
				if($result)
					$ret = true;
				else
				{
					Core_Logger::getInstance()->writeLog(get_called_class()."::".__METHOD__.":".__LINE__,"数据库操作失败 sql=".$sql,Core_Logger::LOG_LEVL_ERROR);
				}
			}
			else
			{
				Core_Logger::getInstance()->writeLog(get_called_class()."::".__METHOD__.":".__LINE__,"数据库连接失败",Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			Core_Logger::getInstance()->writeLog(get_called_class()."::".__METHOD__.":".__LINE__,'参数错误',Core_Logger::LOG_LEVL_ERROR);
		}
		return $ret;
	}
	//将订单信息插入订单表
	public function insert_into_pay_record($data)
	{
		$ret = false;
		if(is_array($data) && !empty($data))
		{
			if($this->init_mysql_obj())
			{
				foreach($data as $key=>$val)
				{
					$key = mysql_real_escape_string($key);
					$val = mysql_real_escape_string($val);
					$data[$key] = $val;
				}
				$sql = 'insert into t_pay_record(id,oid,mid,money,paytype,pid,eid) values("'.$data['id'].'","'.$data['oid'].'","'.$data['mid'].'","'.$data['money'].'","'.$data['paytype'].'","'.$data['pid'].'","'.$data['eid'].'")';
				$result = $this->mysql->DB_Query($sql);
				if($result)
				{
					$this->_error_code = Core_Exception::CODE_DEAL_OK;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
					$ret = true;
				}
				else
				{
					$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
					Core_Logger::getInstance()->writeLog(get_called_class()."::".__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
				Core_Logger::getInstance()->writeLog(get_called_class()."::".__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_BAD_PARAM;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_BAD_PARAM);
			Core_Logger::getInstance()->writeLog(get_called_class()."::".__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $ret;
	}
	//根据订单ID判断订单信息是否存在
	public function check_order_by_oid($id)
	{
		$ret = false;
		if($this->init_mysql_obj())
		{
			$id = mysql_real_escape_string($id);
			$sql = 'select * from t_order where id="'.$id.'"';
			if($this->mysql->DB_Query_Res_And_Count($sql)>0)
			{
				$this->_error_code = Core_Exception::CODE_DEAL_OK;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
				$ret = true;
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(get_called_class()."::".__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $ret;
	}
	//将设备写入用户设备表
	public function insert_into_user_term($data)
	{
		$ret = false;
		if($this->init_mysql_obj())
		{
			foreach($data as $key=>$val)
			{
				$key = mysql_real_escape_string($key);
				$val = mysql_real_escape_string($val);
				$data[$key] = $val;
			}
			$sql = 'insert into t_user_term(mid,time,eid,pid) values("'.$data['mid'].'","'.$data['time'].'","'.$data['eid'].'","'.$data['pid'].'")';
			$result = $this->mysql->DB_Query($sql);
			if($result)
			{
				$this->_error_code = Core_Exception::CODE_DEAL_OK;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
				$ret = true;
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
				Core_Logger::getInstance()->writeLog(get_called_class()."::".__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(get_called_class()."::".__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $ret;
	}
	//跟新付费成功表
	public function update_pay_record_state($id)
	{
		$ret = false;
		if($this->init_mysql_obj())
		{
			$id = mysql_real_escape_string($id);
			$sql = 'update t_pay_record set state=1 where id="'.$id.'"';
			if($this->mysql->DB_Query_Affected_Rows($sql)>0)
			{
				$this->_error_code = Core_Exception::CODE_DEAL_OK;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
				$ret = true;
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(get_called_class()."::".__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $ret;
	}
	//获得活动列表
	public function get_my_package($search_arr)
	{
		$data_arr = array();
		if($this->init_mysql_obj())
		{
			$id = isset($search_arr['id'])?mysql_real_escape_string($search_arr['id']):'';
			$name = isset($search_arr['name'])?mysql_real_escape_string($search_arr['name']):'';
			$page = isset($search_arr['page'])?mysql_real_escape_string($search_arr['page']):1;
			$bnums = ($page-1)*PAGE_NUMS;

			if(check_admin())
				$mid = isset($search_arr['mid'])?mysql_real_escape_string($search_arr['mid']):'';
			else
				$mid = isset($_SESSION['HTC_LOGIN_DATA']['ad_tid'])?$_SESSION['HTC_LOGIN_DATA']['ad_tid']:'';

			$sql = 'select * from t_package where 1=1 ';
			if($mid)
				$sql .= ' and mid='.$mid;
			if($id)
				$sql .= ' and id='.$id;
			if($name)
				$sql .= ' and name like "%'.$name.'%" ';

			$allnums = $this->mysql->DB_Query_Res_And_Count($sql);
			$allpage = ceil($allnums/PAGE_NUMS);

			$sql .= ' limit '.$bnums.','.PAGE_NUMS;
			
			if($result = $this->mysql->do_Query($sql))
			{
				while($row = $this->mysql->DB_fetch_array($result))
				{
					$tmp_arr = array();
					$tmp_arr['id'] = $row['id'];
					$tmp_arr['mid'] = $row['mid'];
					$tmp_arr['name'] = $row['name'];
					$tmp_arr['price'] = $row['price'];
					$tmp_arr['utime'] = $row['utime'];
					$tmp_arr['des'] = $row['des'];
					$tmp_arr['state'] = $row['state'];
					$tmp_arr['state_name'] = $row['state']?'可用':'不可用';
					$data_arr['data'][] = $tmp_arr;
				}
				$this->_error_code = Core_Exception::CODE_DEAL_OK;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
				Core_Logger::getInstance()->writeLog(get_called_class()."::".__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
			}
			$data_arr['allpage'] = $allpage;
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $data_arr;
	}
	//获得页面上的订单数
	private function get_page_top_order_nums($mid='')
	{
		$data_arr = array();
		//昨天订单数
		$yesterday = date("Y-m-d",strtotime("-1 day"));
		$con_arr = array('btime'=>strtotime($yesterday),'etime'=>strtotime($yesterday)+86399,'mid'=>$mid);
		$yOrder = $this->get_order_nums_by_con($con_arr);
		//本月订单数
		$month_arr = getMonth(date('Y-m-d'));
		$con_arr = array('btime'=>strtotime($month_arr[0]),'etime'=>strtotime($month_arr[1])+86399,'mid'=>$mid);
		$mOrder = $this->get_order_nums_by_con($con_arr);
		//所有订单数
		$aOrder = $this->get_order_nums_by_con(array('mid'=>$mid));
		$data_arr = array('yOrder'=>$yOrder,'mOrder'=>$mOrder,'aOrder'=>$aOrder);
		return $data_arr;
	}
	//首页数据
	private function get_index_part_data($mid='')
	{
		$data_arr = array();
		//今日营业额
		//$nowday = date('Y-m-d');
		//$con_arr = array('btime'=>strtotime($nowday),'etime'=>strtotime($nowday)+86399,'mid'=>$mid);
		//$tTumover = $this->get_tumover_by_con($con_arr);
		$tTumover = $this->get_nowday_tumover($mid);
		//昨日营业额
		$yesterday = date("Ymd",strtotime("-1 day"));
		$con_arr = array('btime'=>$yesterday,'etime'=>$yesterday,'mid'=>$mid);
		$yTumover = $this->get_tumover_by_con_day($con_arr);
		//前天营业额
		$qday = date("Ymd",strtotime("-2 day"));
		$con_arr = array('btime'=>$qday,'etime'=>$qday,'mid'=>$mid);
		$qTumover = $this->get_tumover_by_con_day($con_arr);
		//今日增涨比
		$tRate = $yTumover==0?0:round(($tTumover-$yTumover)/$yTumover,2)*100;
		$tRate = $tRate.'%';
		//昨日增涨比
		$yRate = $qTumover==0?0:round(($yTumover-$qTumover)/$qTumover,2)*100;
		$yRate = $yRate.'%';
		//本月营业额
		$month_arr = getMonth(date('Y-m-d'));
		$con_arr = array('btime'=>date('Ymd',strtotime($month_arr[0])),'etime'=>date('Ymd',strtotime($month_arr[1])),'mid'=>$mid);
		$mTumover = $this->get_tumover_by_con_day($con_arr);
		//上月营业额
		$last_month = getlastMonthDays(date('Y-m-d'));
		$con_arr = array('btime'=>date('Ymd',strtotime($last_month[0])),'etime'=>date('Ymd',strtotime($last_month[1])),'mid'=>$mid);
		$lTumover = $this->get_tumover_by_con_day($con_arr);
		//月增涨比
		$mRate = $lTumover==0?0:round(($mTumover-$lTumover)/$lTumover,2)*100;
		$mRate = $mRate.'%';
		$data_arr = array('tTumover'=>$tTumover,'yTumover'=>$yTumover,'mTumover'=>$mTumover,'tRate'=>$tRate,'yRate'=>$yRate,'mRate'=>$mRate);
		return $data_arr;
	}
	//根据条件获得账单信息
	public function get_my_bill($search_arr)
	{
		$ret = '还没有账单信息';
		if($this->init_mysql_obj())
		{
			$data_arr = array();

			$btime = isset($search_arr['btime'])?mysql_real_escape_string($search_arr['btime']):'';
			$etime = isset($search_arr['etime'])?mysql_real_escape_string($search_arr['etime']):'';
			
			//判断登录类型
			if(check_merchant())
				$mid = isset($_SESSION['HTC_LOGIN_DATA']['ad_tid'])?$_SESSION['HTC_LOGIN_DATA']['ad_tid']:'';
			else
				$mid = isset($search_arr['mid'])?mysql_real_escape_string($search_arr['mid']):'';

			$sql = 'select * from t_merchant_bill_day where 1=1 ';
			if($btime)
			{
				$btime = date('Ymd',strtotime($btime));
				$sql .= ' and time>='.$btime;
			}
			if($etime)
			{
				$etime = date('Ymd',strtotime($etime));
				$sql .= ' and time<='.$etime;
			}
			if($mid)
				$sql .= ' and mid='.$mid;

			$sql .= ' order by time DESC';
			
			if($result = $this->mysql->do_Query($sql))
			{
				//实例化管理员操作类
				$admin = new classAdmin();
				while($row = $this->mysql->DB_fetch_array($result))
				{
					$tmp_arr = array();
					$merchant_info = $admin->get_merchant_info_byid($row['mid']);
					$tmp_arr['id'] = $row['id'];
					$tmp_arr['mid'] = isset($merchant_info['name'])?$merchant_info['name']:'未知';
					$tmp_arr['time'] = date('Y-m-d',strtotime($row['time']));
					$tmp_arr['paytype'] = $row['paytype'];
					$tmp_arr['money'] = $row['money'];
					$data_arr[] = $tmp_arr;
				}
				$this->_error_code = Core_Exception::CODE_DEAL_OK;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
				Core_Logger::getInstance()->writeLog(get_called_class()."::".__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
			}
			if(!empty($data_arr))
			{
				$ret = create_bill_excel($mid,$data_arr);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $ret;
	}
	// 修改商户设备
	public function update_term($id,$formdata)
	{
		$ret = false;
		if($this->init_mysql_obj())
		{
			if($id&&!empty($formdata))
			{
				$id = mysql_real_escape_string($id);
				$update_arr = array('termid','name','enable');
				foreach($formdata as $key=>$val)
				{
					$key = mysql_real_escape_string($key);
					$val = mysql_real_escape_string($val);

					if(in_array($key,$update_arr))
					{
						$formdata[$key] = $val;
					}
					else
						unset($formdata[$key]);
				}
				$sql = 'update t_term set ';
				$i=0;
				$up_num = count($formdata)-1;
				foreach($formdata as $key=>$val)
				{
					$sql .= $key.'="'.$val.'"';
					if($i!=$up_num)
						$sql.=',';
					$i++;
				}
				$sql .= ' where id="'.$id.'"';
				
				if($this->mysql->DB_Query_Affected_Rows($sql)>=0)
				{
					//操作成功写入操作记录表
					$data_arr = array('aid'=>$_SESSION['HTC_LOGIN_DATA']['ad_id'],'time'=>time(),'t_name'=>'t_term','act'=>'update','data'=>json_encode(array('id'=>$id,'data'=>$formdata)));
					if($this->insert_into_admin_log($data_arr))
					{
						$this->_error_code = Core_Exception::CODE_DEAL_OK;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
						$ret = true;
					}
					else
					{
						$this->_error_code = Core_Exception::CODE_ADMIN_LOG_ERROR;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_ADMIN_LOG_ERROR);
						Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
					}
				}
				else
				{
					$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
					Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_FORMDATA_BAD;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_FORMDATA_BAD);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $ret;
	}
	//判断终端设备标识是否重复
	public function check_termid($termid)
	{
		$result=false;
		if($this->init_mysql_obj())
		{
			$termid = mysql_real_escape_string($termid);
			$sql = 'select id from t_term where termid="'.$termid.'"';
			$result = $this->mysql->DB_Query_Res_And_Count($sql);
			if($result>0)
			{
				$result = true;
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_USER_NOT_EXIST;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_USER_NOT_EXIST);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
		}
		return $result;
	}
	//添加设备
	public function add_term($formdata)
	{
		$ret = false;
		if($this->init_mysql_obj())
		{
			if(isset($formdata['termid'])&&isset($formdata['name'])&&isset($formdata['spid'])&&isset($formdata['enable']))
			{
				foreach($formdata as $key=>$val)
				{
					$key = mysql_real_escape_string($key);
					$val = mysql_real_escape_string($val);
					$formdata[$key] = $val;
				}
				//判断终端设备标识是否重复
				if(!$this->check_termid($formdata['termid']))
				{
					$sql = 'insert into t_term(termid,name,spid,enable) values("'.$formdata['termid'].'","'.$formdata['name'].'","'.$formdata['spid'].'","'.$formdata['enable'].'")';
					if($this->mysql->DB_Query($sql))
					{
						//操作成功写入操作记录表
						$data_arr = array('aid'=>$_SESSION['HTC_LOGIN_DATA']['ad_id'],'time'=>time(),'t_name'=>'t_term','act'=>'add','data'=>json_encode($formdata));
						if($this->insert_into_admin_log($data_arr))
						{
							$this->_error_code = Core_Exception::CODE_DEAL_OK;
							$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
							$ret = true;
						}
						else
						{
							$this->_error_code = Core_Exception::CODE_ADMIN_LOG_ERROR;
							$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_ADMIN_LOG_ERROR);
							Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
						}
					}
					else
					{
						$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
						Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
					}
				}
				else
				{
					$this->_error_code = Core_Exception::CODE_TERMID_EXISTS;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_TERMID_EXISTS);
					Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_FORMDATA_BAD;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_FORMDATA_BAD);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		return $ret;
	}

	//添加套餐
	public function add_package($formdata)
	{
		$ret = false;
		if($this->init_mysql_obj())
		{
			if(isset($formdata['mid'])&&isset($formdata['name'])&&isset($formdata['price'])&&isset($formdata['utime'])&&isset($formdata['des'])&&isset($formdata['state']))
			{
				foreach($formdata as $key=>$val)
				{
					$key = mysql_real_escape_string($key);
					$val = mysql_real_escape_string($val);
					$formdata[$key] = $val;
				}
				$sql = 'insert into t_package(mid,name,price,utime,des,state) values("'.$formdata['mid'].'","'.$formdata['name'].'","'.$formdata['price'].'","'.$formdata['utime'].'","'.$formdata['des'].'","'.$formdata['state'].'")';
				
				if($this->mysql->DB_Query($sql))
				{
					//操作成功写入操作记录表
					$data_arr = array('aid'=>$_SESSION['HTC_LOGIN_DATA']['ad_id'],'time'=>time(),'t_name'=>'t_package','act'=>'add','data'=>json_encode($formdata));
					if($this->insert_into_admin_log($data_arr))
					{
						$this->_error_code = Core_Exception::CODE_DEAL_OK;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
						$ret = true;
					}
					else
					{
						$this->_error_code = Core_Exception::CODE_ADMIN_LOG_ERROR;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_ADMIN_LOG_ERROR);
						Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
					}
				}
				else
				{
					$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
					Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_FORMDATA_BAD;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_FORMDATA_BAD);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		return $ret;
	}
	//删除设备
	public function delete_term($id)
	{
		$ret = false;
		if($this->init_mysql_obj())
		{
			$id = mysql_real_escape_string($id);
			$sql = 'delete from t_term where id="'.$id.'"';
			if($this->mysql->DB_Query_Affected_Rows($sql) > 0)
			{
				//同时删掉设备套餐关系表中的关系
				$this->delete_package_term_ship_bytid($id);
				//操作成功写入操作记录表
				$data_arr = array('aid'=>$_SESSION['HTC_LOGIN_DATA']['ad_id'],'time'=>time(),'t_name'=>'t_term','act'=>'delete','data'=>json_encode(array('id'=>$id)));
				if($this->insert_into_admin_log($data_arr))
				{
					$this->_error_code = Core_Exception::CODE_DEAL_OK;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
					$ret = true;
				}
				else
				{
					$this->_error_code = Core_Exception::CODE_ADMIN_LOG_ERROR;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_ADMIN_LOG_ERROR);
					Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $ret;
	}
	//删除套餐
	public function delete_package($id)
	{
		$ret = false;
		if($this->init_mysql_obj())
		{
			$id = mysql_real_escape_string($id);
			$sql = 'delete from t_package where id="'.$id.'"';
			if($this->mysql->DB_Query_Affected_Rows($sql) > 0)
			{
				//删除设备套餐表中跟此套餐相关的关系
				$this->delete_package_term_ship_bypid($id);

				//操作成功写入操作记录表
				$data_arr = array('aid'=>$_SESSION['HTC_LOGIN_DATA']['ad_id'],'time'=>time(),'t_name'=>'t_package','act'=>'delete','data'=>json_encode(array('id'=>$id)));
				if($this->insert_into_admin_log($data_arr))
				{
					$this->_error_code = Core_Exception::CODE_DEAL_OK;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
					$ret = true;
				}
				else
				{
					$this->_error_code = Core_Exception::CODE_ADMIN_LOG_ERROR;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_ADMIN_LOG_ERROR);
					Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $ret;
	}
	// 修改商户套餐
	public function update_package($id,$formdata)
	{
		$ret = false;
		if($this->init_mysql_obj())
		{
			if($id&&!empty($formdata))
			{
				$id = mysql_real_escape_string($id);
				$update_arr = array('name','price','utime','des','state');
				foreach($formdata as $key=>$val)
				{
					$key = mysql_real_escape_string($key);
					$val = mysql_real_escape_string($val);

					if(in_array($key,$update_arr))
					{
						$formdata[$key] = $val;
					}
					else
						unset($formdata[$key]);
				}
				$sql = 'update t_package set ';
				$i=0;
				$up_num = count($formdata)-1;
				foreach($formdata as $key=>$val)
				{
					$sql .= $key.'="'.$val.'"';
					if($i!=$up_num)
						$sql.=',';
					$i++;
				}
				$sql .= ' where id="'.$id.'"';
				
				if($this->mysql->DB_Query_Affected_Rows($sql)>=0)
				{
					//操作成功写入操作记录表
					$data_arr = array('aid'=>$_SESSION['HTC_LOGIN_DATA']['ad_id'],'time'=>time(),'t_name'=>'t_package','act'=>'update','data'=>json_encode(array('id'=>$id,'data'=>$formdata)));
					if($this->insert_into_admin_log($data_arr))
					{
						$this->_error_code = Core_Exception::CODE_DEAL_OK;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
						$ret = true;
					}
					else
					{
						$this->_error_code = Core_Exception::CODE_ADMIN_LOG_ERROR;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_ADMIN_LOG_ERROR);
						Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
					}
				}
				else
				{
					$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
					Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_FORMDATA_BAD;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_FORMDATA_BAD);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $ret;
	}
	//获得商户天结算信息
	public function get_merchant_money($search_arr)
	{
		$data_arr = array();
		if($this->init_mysql_obj())
		{

			$btime = isset($search_arr['btime'])?mysql_real_escape_string($search_arr['btime']):'';
			$etime = isset($search_arr['etime'])?mysql_real_escape_string($search_arr['etime']):'';
			$paytype = isset($search_arr['paytype'])?mysql_real_escape_string($search_arr['paytype']):'';
			
			//判断登录类型
			if(check_merchant())
				$mid = isset($_SESSION['HTC_LOGIN_DATA']['ad_tid'])?$_SESSION['HTC_LOGIN_DATA']['ad_tid']:'';
			else
				$mid = isset($search_arr['mid'])?mysql_real_escape_string($search_arr['mid']):'';

			$page = isset($search_arr['page'])?mysql_real_escape_string($search_arr['page']):1;
			$bnums = ($page-1)*PAGE_NUMS;

			$sql = 'select * from t_merchant_bill_day where 1=1 ';
			if($btime)
			{
				$btime = date('Ymd',strtotime($btime));
				$sql .= ' and time>='.$btime;
			}
			if($etime)
			{
				$etime = date('Ymd',strtotime($etime));
				$sql .= ' and time<='.$etime;
			}
			if($paytype)
				$sql .= ' and paytype='.$paytype;
			if($mid)
				$sql .= ' and mid='.$mid;

			$allnums = $this->mysql->DB_Query_Res_And_Count($sql);
			$allpage = ceil($allnums/PAGE_NUMS);
			$sql .= ' order by time DESC limit '.$bnums.','.PAGE_NUMS;

			if($result = $this->mysql->do_Query($sql))
			{
				//实例化管理员操作类
				$admin = new classAdmin();
				while($row = $this->mysql->DB_fetch_array($result))
				{
					$tmp_arr = array();
					$merchant_info = $admin->get_merchant_info_byid($row['mid']);
					$tmp_arr['id'] = $row['id'];
					$tmp_arr['mid'] = isset($merchant_info['name'])?$merchant_info['name']:'未知';
					$tmp_arr['time'] = date('Y-m-d',strtotime($row['time']));
					$tmp_arr['paytype'] = isset(self::$_paytype_config_arr[$row['paytype']])?self::$_paytype_config_arr[$row['paytype']]:'未知';;
					$tmp_arr['money'] = $row['money'];
					$data_arr['data'][] = $tmp_arr;
				}
				$this->_error_code = Core_Exception::CODE_DEAL_OK;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
				Core_Logger::getInstance()->writeLog(get_called_class()."::".__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
			}
			$data_arr['allpage'] = $allpage;
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $data_arr;
	}
	//添加商户提现要求
	public function add_bill_demends($formdata)
	{
		$ret = false;
		if($this->init_mysql_obj())
		{
			if(isset($formdata['mid'])&&isset($formdata['money'])&&isset($formdata['time']))
			{
				foreach($formdata as $key=>$val)
				{
					$key = mysql_real_escape_string($key);
					$val = mysql_real_escape_string($val);
					$formdata[$key] = $val;
				}
				$sql = 'insert into t_merchant_bill_demands(mid,money,time) values("'.$formdata['mid'].'","'.$formdata['money'].'","'.$formdata['time'].'")';
				if($this->mysql->DB_Query($sql))
				{
					//操作成功写入操作记录表
					$data_arr = array('aid'=>$_SESSION['HTC_LOGIN_DATA']['ad_id'],'time'=>time(),'t_name'=>'t_merchant_bill_demands','act'=>'add','data'=>json_encode($formdata));
					if($this->insert_into_admin_log($data_arr))
					{
						$this->_error_code = Core_Exception::CODE_DEAL_OK;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
						$ret = true;
					}
					else
					{
						$this->_error_code = Core_Exception::CODE_ADMIN_LOG_ERROR;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_ADMIN_LOG_ERROR);
						Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
					}
				}
				else
				{
					$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
					Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_FORMDATA_BAD;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_FORMDATA_BAD);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		return $ret;
	}
	// 修改提现要求
	public function update_bill_demends($id,$formdata)
	{
		$ret = false;
		if($this->init_mysql_obj())
		{
			if($id&&!empty($formdata))
			{
				$id = mysql_real_escape_string($id);
				$update_arr = array('money','time');
				foreach($formdata as $key=>$val)
				{
					$key = mysql_real_escape_string($key);
					$val = mysql_real_escape_string($val);

					if(in_array($key,$update_arr))
					{
						$formdata[$key] = $val;
					}
					else
						unset($formdata[$key]);
				}
				$sql = 'update t_merchant_bill_demands set ';
				$i=0;
				$up_num = count($formdata)-1;
				foreach($formdata as $key=>$val)
				{
					$sql .= $key.'="'.$val.'"';
					if($i!=$up_num)
						$sql.=',';
					$i++;
				}
				$sql .= ' where id="'.$id.'"';
				
				if($this->mysql->DB_Query_Affected_Rows($sql)>=0)
				{
					//操作成功写入操作记录表
					$data_arr = array('aid'=>$_SESSION['HTC_LOGIN_DATA']['ad_id'],'time'=>time(),'t_name'=>'t_merchant_bill_demands','act'=>'update','data'=>json_encode(array('id'=>$id,'data'=>$formdata)));
					if($this->insert_into_admin_log($data_arr))
					{
						$this->_error_code = Core_Exception::CODE_DEAL_OK;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
						$ret = true;
					}
					else
					{
						$this->_error_code = Core_Exception::CODE_ADMIN_LOG_ERROR;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_ADMIN_LOG_ERROR);
						Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
					}
				}
				else
				{
					$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
					Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_FORMDATA_BAD;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_FORMDATA_BAD);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $ret;
	}
	//根据商户ID获得商户结算要求信息
	public function get_demends_info_bymid($search)
	{
		$data_arr = array();
		if($this->init_mysql_obj())
		{
			if(isset($search['mid']))
			{
				$mid = mysql_real_escape_string($search['mid']);
				$sql = 'select id,money,time from t_merchant_bill_demands where mid='.$mid;
				if($result = $this->mysql->do_Query($sql))
				{
					$row = $this->mysql->DB_fetch_array($result,MYSQL_ASSOC);
					if(!empty($row))
					{
						$this->_error_code = Core_Exception::CODE_DEAL_OK;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
						$data_arr = $row;
					}
				}
				else
				{
					$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
					Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_BAD_PARAM;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_BAD_PARAM);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $data_arr;
	}
	//删除结算要求
	public function delete_bill_demends($id)
	{
		$ret = false;
		if($this->init_mysql_obj())
		{
			$id = mysql_real_escape_string($id);
			$sql = 'delete from t_merchant_bill_demands where id="'.$id.'"';
			if($this->mysql->DB_Query_Affected_Rows($sql) > 0)
			{
				//操作成功写入操作记录表
				$data_arr = array('aid'=>$_SESSION['HTC_LOGIN_DATA']['ad_id'],'time'=>time(),'t_name'=>'t_merchant_bill_demands','act'=>'delete','data'=>json_encode(array('id'=>$id)));
				if($this->insert_into_admin_log($data_arr))
				{
					$this->_error_code = Core_Exception::CODE_DEAL_OK;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
					$ret = true;
				}
				else
				{
					$this->_error_code = Core_Exception::CODE_ADMIN_LOG_ERROR;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_ADMIN_LOG_ERROR);
					Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $ret;
	}
	//添加套餐设备关系
	public function add_package_term_ship($formdata)
	{
		$ret = false;
		if($this->init_mysql_obj())
		{
			if(isset($formdata['pid'])&&isset($formdata['tid']))
			{
				foreach($formdata as $key=>$val)
				{
					$key = mysql_real_escape_string($key);
					$val = mysql_real_escape_string($val);
					$formdata[$key] = $val;
				}
				$sql = 'insert into t_package_term_ship(pid,tid) values("'.$formdata['pid'].'","'.$formdata['tid'].'")';
				if($this->mysql->DB_Query($sql))
				{
					//操作成功写入操作记录表
					$data_arr = array('aid'=>$_SESSION['HTC_LOGIN_DATA']['ad_id'],'time'=>time(),'t_name'=>'t_package_term_ship','act'=>'add','data'=>json_encode($formdata));
					if($this->insert_into_admin_log($data_arr))
					{
						$this->_error_code = Core_Exception::CODE_DEAL_OK;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
						$ret = true;
					}
					else
					{
						$this->_error_code = Core_Exception::CODE_ADMIN_LOG_ERROR;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_ADMIN_LOG_ERROR);
						Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
					}
				}
				else
				{
					$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
					Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_FORMDATA_BAD;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_FORMDATA_BAD);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		return $ret;
	}
	//删除套餐设备关系
	public function delete_package_term_ship($id)
	{
		$ret = false;
		if($this->init_mysql_obj())
		{
			$id = mysql_real_escape_string($id);
			$sql = 'delete from t_package_term_ship where id="'.$id.'"';
			if($this->mysql->DB_Query_Affected_Rows($sql) > 0)
			{
				//操作成功写入操作记录表
				$data_arr = array('aid'=>$_SESSION['HTC_LOGIN_DATA']['ad_id'],'time'=>time(),'t_name'=>'t_package_term_ship','act'=>'delete','data'=>json_encode(array('id'=>$id)));
				if($this->insert_into_admin_log($data_arr))
				{
					$this->_error_code = Core_Exception::CODE_DEAL_OK;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
					$ret = true;
				}
				else
				{
					$this->_error_code = Core_Exception::CODE_ADMIN_LOG_ERROR;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_ADMIN_LOG_ERROR);
					Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $ret;
	}
	//根据套餐ID删除套餐设备关系
	public function delete_package_term_ship_bypid($pid)
	{
		$ret = false;
		if($this->init_mysql_obj())
		{
			$pid = mysql_real_escape_string($pid);
			$sql = 'delete from t_package_term_ship where pid="'.$pid.'"';
			if($this->mysql->DB_Query_Affected_Rows($sql) > 0)
			{
				//操作成功写入操作记录表
				$data_arr = array('aid'=>$_SESSION['HTC_LOGIN_DATA']['ad_id'],'time'=>time(),'t_name'=>'t_package_term_ship','act'=>'delete','data'=>json_encode(array('pid'=>$pid)));
				if($this->insert_into_admin_log($data_arr))
				{
					$this->_error_code = Core_Exception::CODE_DEAL_OK;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
					$ret = true;
				}
				else
				{
					$this->_error_code = Core_Exception::CODE_ADMIN_LOG_ERROR;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_ADMIN_LOG_ERROR);
					Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $ret;
	}
	//根据套餐ID删除套餐设备关系
	public function delete_package_term_ship_bytid($tid)
	{
		$ret = false;
		if($this->init_mysql_obj())
		{
			$tid = mysql_real_escape_string($tid);
			$sql = 'delete from t_package_term_ship where tid="'.$tid.'"';
			if($this->mysql->DB_Query_Affected_Rows($sql) > 0)
			{
				//操作成功写入操作记录表
				$data_arr = array('aid'=>$_SESSION['HTC_LOGIN_DATA']['ad_id'],'time'=>time(),'t_name'=>'t_package_term_ship','act'=>'delete','data'=>json_encode(array('tid'=>$tid)));
				if($this->insert_into_admin_log($data_arr))
				{
					$this->_error_code = Core_Exception::CODE_DEAL_OK;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
					$ret = true;
				}
				else
				{
					$this->_error_code = Core_Exception::CODE_ADMIN_LOG_ERROR;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_ADMIN_LOG_ERROR);
					Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $ret;
	}
	//根据设备ID获得套餐设备关系信息
	public function get_ship_info_bytid($search_arr)
	{
		$data_arr = array();
		if($this->init_mysql_obj())
		{
			if(isset($search_arr['tid']))
			{
				$tid = isset($search_arr['tid'])?mysql_real_escape_string($search_arr['tid']):'';
				$page = isset($search_arr['page'])?mysql_real_escape_string($search_arr['page']):1;
				$bnums = ($page-1)*PAGE_NUMS;

				$sql = 'select id,pid from t_package_term_ship where tid='.$tid;
				
				$allnums = $this->mysql->DB_Query_Res_And_Count($sql);
				$allpage = ceil($allnums/PAGE_NUMS);
				$sql .= ' limit '.$bnums.','.PAGE_NUMS;
				if($result = $this->mysql->do_Query($sql))
				{
					while($row = $this->mysql->DB_fetch_array($result,MYSQL_ASSOC))
					{
						$package_info = $this->get_package_info_byid($row['pid']);
						$row['name'] = isset($package_info['name'])?$package_info['name']:'未知';
						$this->_error_code = Core_Exception::CODE_DEAL_OK;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
						$data_arr['data'][] = $row;
					}
				}
				else
				{
					$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
					Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
				}
				$data_arr['allpage'] = $allpage;
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_BAD_PARAM;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_BAD_PARAM);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $data_arr;
	}
	//获得首页的数据
	public function get_bill_pay_record($search_arr)
	{
		$data_arr = array();
		if(check_admin())
			$data_arr = $this->get_bill_pay_record_admin($search_arr);
		else
			$data_arr = $this->get_bill_pay_record_merchant($search_arr);

		return $data_arr;
	}
	//获得提现申请记录
	public function get_bill_pay_record_admin($search_arr)
	{
		$data_arr = array();
		if($this->init_mysql_obj())
		{

			$btime = isset($search_arr['btime'])?mysql_real_escape_string($search_arr['btime']):'';
			$etime = isset($search_arr['etime'])?mysql_real_escape_string($search_arr['etime']):'';
			$mid = isset($search_arr['mid'])?mysql_real_escape_string($search_arr['mid']):'';
			$state = isset($search_arr['state'])?mysql_real_escape_string($search_arr['state']):'-1';

			$page = isset($search_arr['page'])?mysql_real_escape_string($search_arr['page']):1;
			$bnums = ($page-1)*PAGE_NUMS;

			$sql = 'select * from t_bill_pay_record where 1=1 ';
			if($btime)
			{
				$btime = strtotime($btime);
				$sql .= ' and ctime>='.$btime;
			}
			if($etime)
			{
				$etime = strtotime($etime) + 86399;
				$sql .= ' and ctime<='.$etime;
			}
			if($mid)
				$sql .= ' and mid='.$mid;
			if($state!=''&&$state>=0)
				$sql .= ' and state='.$state;

			$allnums = $this->mysql->DB_Query_Res_And_Count($sql);
			$allpage = ceil($allnums/PAGE_NUMS);
			$sql .= ' order by ctime ASC limit '.$bnums.','.PAGE_NUMS;
			
			if($result = $this->mysql->do_Query($sql))
			{
				//实例化管理员操作类
				$admin = new classAdmin();
				while($row = $this->mysql->DB_fetch_array($result))
				{
					$tmp_arr = $admin_info = array();
					$merchant_info = $admin->get_merchant_info_byid($row['mid']);
					if($row['ad_id'])
						$admin_info = $admin->get_admin_info_byid($row['ad_id']);
					$tmp_arr['id'] = $row['id'];
					$tmp_arr['mid'] = isset($merchant_info['name'])?$merchant_info['name']:'未知';
					$tmp_arr['account'] = isset($merchant_info['account'])?$merchant_info['account']:'未知';
					$tmp_arr['ctime'] = date('Y-m-d H:i:s',$row['ctime']);
					$tmp_arr['money'] = $row['money'];
					$tmp_arr['state_name'] = $row['state']?'已发放':'未发放';
					$tmp_arr['state'] = $row['state'];
					$tmp_arr['ad_id'] = empty($admin_info)?'':$admin_info['ad_nick'];
					$tmp_arr['ftime'] = empty($row['ftime'])?'':date('Y-m-d H:i:s',$row['ftime']);
					$data_arr['data'][] = $tmp_arr;
				}
				$this->_error_code = Core_Exception::CODE_DEAL_OK;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
				Core_Logger::getInstance()->writeLog(get_called_class()."::".__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
			}
			$data_arr['allpage'] = $allpage;
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $data_arr;
	}
	//获得提现申请记录
	public function get_bill_pay_record_merchant($search_arr)
	{
		$data_arr = array();
		if($this->init_mysql_obj())
		{

			$btime = isset($search_arr['btime'])?mysql_real_escape_string($search_arr['btime']):'';
			$etime = isset($search_arr['etime'])?mysql_real_escape_string($search_arr['etime']):'';
			$mid = isset($_SESSION['HTC_LOGIN_DATA']['ad_tid'])?$_SESSION['HTC_LOGIN_DATA']['ad_tid']:'';

			$page = isset($search_arr['page'])?mysql_real_escape_string($search_arr['page']):1;
			$bnums = ($page-1)*PAGE_NUMS;

			$sql = 'select * from t_apply_bill where 1=1 ';
			if($btime)
			{
				$btime = strtotime($btime);
				$sql .= ' and ctime>='.$btime;
			}
			if($etime)
			{
				$etime = strtotime($etime) + 86399;
				$sql .= ' and ctime<='.$etime;
			}
			if($mid)
				$sql .= ' and mid='.$mid;

			$allnums = $this->mysql->DB_Query_Res_And_Count($sql);
			$allpage = ceil($allnums/PAGE_NUMS);
			$sql .= ' order by ctime DESC limit '.$bnums.','.PAGE_NUMS;
			
			if($result = $this->mysql->do_Query($sql))
			{
				while($row = $this->mysql->DB_fetch_array($result))
				{
					$tmp_arr = array();
					$tmp_arr['id'] = $row['id'];
					$tmp_arr['ctime'] = date('Y-m-d H:i:s',$row['ctime']);
					$tmp_arr['money'] = $row['money'];
					$tmp_arr['state'] = isset(self::$_apply_bill_arr[$row['state']])?self::$_apply_bill_arr[$row['state']]:'未知';
					$data_arr['data'][] = $tmp_arr;
				}
				$this->_error_code = Core_Exception::CODE_DEAL_OK;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
				Core_Logger::getInstance()->writeLog(get_called_class()."::".__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
			}
			$data_arr['allpage'] = $allpage;
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $data_arr;
	}
	//添加提现申请记录
	public function add_bill_pay_record($formdata)
	{
		$ret = false;
		if($this->init_mysql_obj())
		{
			if(isset($formdata['mid'])&&isset($formdata['money']))
			{
				foreach($formdata as $key=>$val)
				{
					$key = mysql_real_escape_string($key);
					$val = mysql_real_escape_string($val);
					$formdata[$key] = $val;
				}
				//判断该商户在该时间段类是否有这么多的钱
				if($this->check_merchant_money($formdata['mid'],$formdata['money']))
				{
					//若有，冻结商户提现的钱
					if($this->update_mercahnt_balance_amoney($formdata['mid'],$formdata['money']))
					{
						$ctime = time();
						$sql = 'insert into t_apply_bill(mid,ctime,money) values("'.$formdata['mid'].'","'.$ctime.'","'.$formdata['money'].'")';
						if($this->mysql->DB_Query($sql))
						{
							//操作成功写入操作记录表
							$data_arr = array('aid'=>$_SESSION['HTC_LOGIN_DATA']['ad_id'],'time'=>time(),'t_name'=>'t_apply_bill','act'=>'add','data'=>json_encode($formdata));
							if($this->insert_into_admin_log($data_arr))
							{
								$this->_error_code = Core_Exception::CODE_DEAL_OK;
								$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
								$ret = true;
							}
							else
							{
								$this->_error_code = Core_Exception::CODE_ADMIN_LOG_ERROR;
								$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_ADMIN_LOG_ERROR);
								Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
							}
						}
						else
						{
							$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
							$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
							Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
						}
					}
					else
					{
						$this->_error_code = Core_Exception::CODE_UPDATE_AMONEY_FAIL;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_UPDATE_AMONEY_FAIL);
						Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
					}
				}
				else
				{
					$this->_error_code = Core_Exception::CODE_NOT_MONEY;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_NOT_MONEY);
					Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_FORMDATA_BAD;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_FORMDATA_BAD);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		return $ret;
	}
	//冻结商户提现的钱
	private function update_mercahnt_balance_amoney($mid,$amoney)
	{
		$ret = false;
		if($this->init_mysql_obj())
		{
			$mid = mysql_real_escape_string($mid);
			$amoney = mysql_real_escape_string($amoney);
			$sql = 'update t_mercahnt_balance set amoney=amoney+'.$amoney.' where mid="'.$mid.'"';
			if($this->mysql->DB_Query_Affected_Rows($sql)>0)
			{
				$this->_error_code = Core_Exception::CODE_DEAL_OK;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
				$ret = true;
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(get_called_class()."::".__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $ret;
	}
	//判断该商户是否有这么多的钱
	private function check_merchant_money($mid,$money)
	{
		$ret = false;
		if($this->init_mysql_obj())
		{
			$mid = mysql_real_escape_string($mid);
			$money = mysql_real_escape_string($money);

			$sql = 'select money,amoney from t_mercahnt_balance where mid="'.$mid.'"';
			if($result = $this->mysql->do_Query($sql))
			{
				$row = $this->mysql->DB_fetch_array($result,MYSQL_ASSOC);
				if(!empty($row))
				{
					if(($row['money']-$row['amoney']) >= $money)
						$ret = true;
					else
					{
						$this->_error_code = Core_Exception::CODE_NOT_MONEY;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_NOT_MONEY);
						Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
					}
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $ret;
	}
	//根据商户ID获得商户结算要求信息
	public function get_demends_info()
	{
		$data_arr = array();
		if($this->init_mysql_obj())
		{
			$sql = 'select id,money,time from t_merchant_bill_demands where mid=0';
			if($result = $this->mysql->do_Query($sql))
			{
				$data_arr = $this->mysql->DB_fetch_array($result,MYSQL_ASSOC);

				$this->_error_code = Core_Exception::CODE_DEAL_OK;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $data_arr;
	}
	//获得商户的提前申请要求
	public function get_merchant_demands($mid)
	{
		$data_arr = array();
		$data_arr = $this->get_demends_info_bymid(array('mid'=>$mid));
		if(empty($data_arr))  //获得官方的提现申请要求
			$data_arr = $this->get_demends_info();
		return $data_arr;
	}
	//获得商户最近一次的提现记录
	public function get_merchant_bill_record_last($mid)
	{
		$data_arr = array();
		if($this->init_mysql_obj())
		{
			$mid = mysql_real_escape_string($mid);
			$sql = 'select ctime from t_apply_bill where mid='.$mid.' order by ctime DESC limit 1';
			if($result = $this->mysql->do_Query($sql))
			{
				$data_arr = $this->mysql->DB_fetch_array($result,MYSQL_ASSOC);

				$this->_error_code = Core_Exception::CODE_DEAL_OK;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $data_arr;
	}
	//添加商户激活码
	public function add_merchant_code($formdata)
	{
		$ret = false;
		if($this->init_mysql_obj())
		{
			$mid = '';
			//判断登录类型
			if(check_merchant())
				$mid = isset($_SESSION['HTC_LOGIN_DATA']['ad_tid'])?$_SESSION['HTC_LOGIN_DATA']['ad_tid']:'';
			else
				$mid = isset($formdata['mid'])?mysql_real_escape_string($formdata['mid']):'';

			if($mid)
			{
				//获得一个还未分配的激活码
				$code = $this->get_activity_code();
				if($code)
				{
					$sql = 'insert into t_merchant_code_ship(code,mid) values("'.$code.'","'.$formdata['mid'].'")';
					if($this->mysql->DB_Query($sql))
					{
						//跟新激活码是否已分配状态
						if($this->update_activity_code_belong($code))
						{
							//操作成功写入操作记录表
							$data_arr = array('aid'=>$_SESSION['HTC_LOGIN_DATA']['ad_id'],'time'=>time(),'t_name'=>'t_merchant_code_ship','act'=>'add','data'=>json_encode(array('code'=>$code,'mid'=>$formdata['mid'])));
							if($this->insert_into_admin_log($data_arr))
							{
								$this->_error_code = Core_Exception::CODE_DEAL_OK;
								$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
								$ret = true;
							}
							else
							{
								$this->_error_code = Core_Exception::CODE_ADMIN_LOG_ERROR;
								$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_ADMIN_LOG_ERROR);
								Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
							}
						}
						else
						{
							$this->_error_code = Core_Exception::CODE_UPDATE_CODE_BELONG_FAIL;
							$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_UPDATE_CODE_BELONG_FAIL);
							Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
						}
					}
					else
					{
						$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
						Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
					}
				}
				else
				{
					$this->_error_code = Core_Exception::CODE_GET_CODE_FAIL;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_GET_CODE_FAIL);
					Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_FORMDATA_BAD;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_FORMDATA_BAD);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		return $ret;
	}
	//获得一个还未分配的激活码
	private function get_activity_code()
	{
		$ret = false;
		if($this->init_mysql_obj())
		{
			$sql = 'select code from t_code where is_belong=0 order by id ASC limit 1';
			if($result = $this->mysql->do_Query($sql))
			{
				$row = $this->mysql->DB_fetch_array($result,MYSQL_ASSOC);
				if(!empty($row))
				{
					$ret = $row['code'];
					$this->_error_code = Core_Exception::CODE_DEAL_OK;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
					}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $ret;
	}
	//获得激活码列表
	public function get_activity_code_list($search_arr)
	{
		$data_arr = array();
		if($this->init_mysql_obj())
		{
			$mid = '';
			$page = isset($search_arr['page'])?mysql_real_escape_string($search_arr['page']):1;
			$bnums = ($page-1)*PAGE_NUMS;
			if(check_admin())
				$mid = isset($search_arr['mid'])?mysql_real_escape_string($search_arr['mid']):'';
			else
				$mid = isset($_SESSION['HTC_LOGIN_DATA']['ad_tid'])?$_SESSION['HTC_LOGIN_DATA']['ad_tid']:'';

			$sql = 'select id,code,mid,nums from t_merchant_code_ship where 1=1 ';

			if($mid)
				$sql .= ' and mid='.$mid;
			
			$allnums = $this->mysql->DB_Query_Res_And_Count($sql);
			$allpage = ceil($allnums/PAGE_NUMS);

			$sql .= ' limit '.$bnums.','.PAGE_NUMS;
			
			if($result = $this->mysql->do_Query($sql))
			{
				//实例化管理员操作类
				$admin = new classAdmin();
				while($row = $this->mysql->DB_fetch_array($result))
				{
					//根据激活码获得激活码的信息
					$code_info = $this->get_activity_code_info($row['code']);
					//根据商户ID获得商户名称
					$merchant_info = $admin->get_merchant_info_byid($row['mid']);
					$tmp_arr = array();
					$tmp_arr['id'] = $row['id'];     	
					$tmp_arr['code'] = $row['code'];
					$tmp_arr['mid'] = isset($merchant_info['name'])?$merchant_info['name']:'未知';
					$tmp_arr['ctime'] = isset($code_info['ctime'])?$code_info['ctime']:'';
					$tmp_arr['nums'] = $row['nums'];
					$data_arr['data'][] = $tmp_arr;
				}
			}
			$data_arr['allpage'] = $allpage;
			$this->_error_code = Core_Exception::CODE_DEAL_OK;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $data_arr;
	}
	//根据激活码获得激活码的信息
	public function get_activity_code_info($code)
	{
		$data_arr = array();
		if($this->init_mysql_obj())
		{
			$code = mysql_real_escape_string($code);
			$sql = 'select id,code,ctime,is_belong from t_code where code='.$code;
			if($result = $this->mysql->do_Query($sql))
			{
				$row = $this->mysql->DB_fetch_array($result,MYSQL_ASSOC);
				if(!empty($row))
				{
					$this->_error_code = Core_Exception::CODE_DEAL_OK;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
					$data_arr = $row;
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $data_arr;
	}
	//将激活码写入激活表
	public function insert_activity_code($code)
	{
		$ret = false;
		if(!empty($code))
		{
			if($this->init_mysql_obj())
			{
				$sql = 'insert into t_code(code) values("'.$code.'")';
				$result = $this->mysql->DB_Query($sql);
				if($result)
					$ret = true;
				else
				{
					$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
					Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_FORMDATA_BAD;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_FORMDATA_BAD);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $ret;
	}
	//根据设备ID获得所有套餐设备关系信息
	public function get_all_ship_info_bytid($tid)
	{
		$data_arr = array();
		if($this->init_mysql_obj())
		{
			if($tid)
			{
				$sql = 'select pid from t_package_term_ship where tid='.$tid;
				if($result = $this->mysql->do_Query($sql))
				{
					while($row = $this->mysql->DB_fetch_array($result,MYSQL_ASSOC))
					{
						$this->_error_code = Core_Exception::CODE_DEAL_OK;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
						$data_arr[] = $row['pid'];
					}
				}
				else
				{
					$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
					Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_BAD_PARAM;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_BAD_PARAM);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $data_arr;
	}
	//根据设备ID获得所有套餐设备关系信息
	public function get_all_package_bymid($mid)
	{
		$data_arr = array();
		if($this->init_mysql_obj())
		{
			if($mid)
			{
				$sql = 'select id,name,price,utime,des from t_package where state=1 and mid='.$mid;
				if($result = $this->mysql->do_Query($sql))
				{
					while($row = $this->mysql->DB_fetch_array($result,MYSQL_ASSOC))
					{
						$this->_error_code = Core_Exception::CODE_DEAL_OK;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
						$data_arr[] = $row;
					}
				}
				else
				{
					$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
					Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_BAD_PARAM;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_BAD_PARAM);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $data_arr;
	}
	//获得设备可添加的套餐列表
	public function get_term_package($search_arr)
	{
		$data_arr = array();
		if($this->init_mysql_obj())
		{
			$mid = '';
			$tid = isset($search_arr['tid'])?mysql_real_escape_string($search_arr['tid']):'';

			if(check_admin())
				$mid = isset($search_arr['mid'])?mysql_real_escape_string($search_arr['mid']):'';
			else
				$mid = isset($_SESSION['HTC_LOGIN_DATA']['ad_tid'])?$_SESSION['HTC_LOGIN_DATA']['ad_tid']:'';
			if($mid && $tid)
			{
				//获得商户的所有套餐
				$package_info = $this->get_all_package_bymid($mid);
				//获得设备已添加的套餐
				$has_package_id = $this->get_all_ship_info_bytid($tid);
				if(!empty($package_info))
				{
					foreach($package_info as $num_arr)
					{
						if(!in_array($num_arr['id'],$has_package_id))
							$data_arr[] = $num_arr;
					}
				}
				else
				{
					$this->_error_code = Core_Exception::CODE_NO_PACKAGE;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_NO_PACKAGE);
					Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_WARNING);
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_BAD_PARAM;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_BAD_PARAM);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $data_arr;
	}
	//跟新激活码分配状态
	public function update_activity_code_belong($code)
	{
		$ret = false;
		if($this->init_mysql_obj())
		{
			$code = mysql_real_escape_string($code);
			$sql = 'update t_code set is_belong=1 where code="'.$code.'"';
			if($this->mysql->DB_Query_Affected_Rows($sql)>0)
			{
				$this->_error_code = Core_Exception::CODE_DEAL_OK;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
				$ret = true;
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(get_called_class()."::".__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $ret;
	}
	//发放用户提现申请
	public function update_bill_pay_state($id)
	{
		$ret = false;
		if($this->init_mysql_obj())
		{
			$ad_id = isset($_SESSION['HTC_LOGIN_DATA']['ad_id'])?$_SESSION['HTC_LOGIN_DATA']['ad_id']:'';
			$ftime = time();
			$id = mysql_real_escape_string($id);
			$sql = 'update t_bill_pay_record set state=1,ad_id='.$ad_id.',ftime='.$ftime.' where id="'.$id.'"';
			if($this->mysql->DB_Query_Affected_Rows($sql)>0)
			{
				//根据提现记录id 获得申请ID
				$aid = $this->get_applyid_by_billid($id);
				if($aid)
				{
					//跟新申请表标志
					if($this->update_apply_state($aid))
					{
						$this->_error_code = Core_Exception::CODE_DEAL_OK;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
						$ret = true;
					}
					else
					{
						$this->_error_code = Core_Exception::CODE_UPDATE_APPLY_STATE_FAIL;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_UPDATE_APPLY_STATE_FAIL);
						Core_Logger::getInstance()->writeLog(get_called_class()."::".__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
					}
				}
				else
				{
					$this->_error_code = Core_Exception::CODE_GET_APPLY_ID_FAIL;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_GET_APPLY_ID_FAIL);
					Core_Logger::getInstance()->writeLog(get_called_class()."::".__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
				Core_Logger::getInstance()->writeLog(get_called_class()."::".__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(get_called_class()."::".__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $ret;
	}
	//根据提现记录id 获得申请ID
	private function get_applyid_by_billid($id)
	{
		$ret = false;
		if($this->init_mysql_obj())
		{
			$id = mysql_real_escape_string($id);
			if($id)
			{
				$sql = 'select aid from t_bill_pay_record where id='.$id;
				if($result = $this->mysql->do_Query($sql))
				{
					if($row = $this->mysql->DB_fetch_array($result,MYSQL_ASSOC))
					{
						$this->_error_code = Core_Exception::CODE_DEAL_OK;
						$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
						$ret = $row['aid'];
					}
				}
				else
				{
					$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
					Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_BAD_PARAM;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_BAD_PARAM);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $ret;
	}
	//跟新申请表标志
	private function update_apply_state($id)
	{
		$ret = false;
		if($this->init_mysql_obj())
		{
			$id = mysql_real_escape_string($id);
			$sql = 'update t_apply_bill set state=2 where id="'.$id.'"';
			if($this->mysql->DB_Query_Affected_Rows($sql)>0)
			{
				$this->_error_code = Core_Exception::CODE_DEAL_OK;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
				$ret = true;
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(get_called_class()."::".__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $ret;
	}
	//判断是否已在提现，每个商户不允许同时提现
	public function check_mercahnt_balance($mid)
	{
		$ret = false;
		if($this->init_mysql_obj())
		{
			$mid = mysql_real_escape_string($mid);
			$sql = 'select money from t_mercahnt_balance where mid="'.$mid.'" and docker=0';
			if($this->mysql->DB_Query_Res_And_Count($sql)>0)
			{
				$this->_error_code = Core_Exception::CODE_DEAL_OK;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
				$ret = true;
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(get_called_class()."::".__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $ret;
	}
	//锁定提现状态，不允许同一帐号同时多人提现
	public function update_mercahnt_balance_docker($mid,$docker)
	{
		$ret = false;
		if($this->init_mysql_obj())
		{
			$mid = mysql_real_escape_string($mid);
			$docker = mysql_real_escape_string($docker);
			$sql = 'update t_mercahnt_balance set docker='.$docker.' where mid="'.$mid.'"';
			if($this->mysql->DB_Query_Affected_Rows($sql)>0)
			{
				$this->_error_code = Core_Exception::CODE_DEAL_OK;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
				$ret = true;
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(get_called_class()."::".__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $ret;
	}
	//获得所有商户余额
	public function get_all_merchant_banlance($search_arr)
	{
		$data_arr = array();
		if(check_admin())
		{
			if($this->init_mysql_obj())
			{
				$page = isset($search_arr['page'])?mysql_real_escape_string($search_arr['page']):1;
				$bnums = ($page-1)*PAGE_NUMS;
				$mid = isset($search_arr['mid'])?mysql_real_escape_string($search_arr['mid']):'';

				$sql = 'select mid,money,amoney from t_mercahnt_balance where 1=1 ';
				if($mid)
					$sql .= ' and mid='.$mid;

				$allnums = $this->mysql->DB_Query_Res_And_Count($sql);
				$allpage = ceil($allnums/PAGE_NUMS);

				$sql .= ' limit '.$bnums.','.PAGE_NUMS;
				
				if($result = $this->mysql->do_Query($sql))
				{
					//根据商户ID获得商户信息
					$admin = new classAdmin();
					while($row = $this->mysql->DB_fetch_array($result))
					{
						$merchant_info = $admin->get_merchant_info_byid($row['mid']);

						$tmp_arr = array();
						$tmp_arr['mid'] = isset($merchant_info['name'])?$merchant_info['name']:'未知';
						$tmp_arr['money'] = $row['money'] - $row['amoney'];  //可用余额
						$tmp_arr['amoney'] = $row['amoney'];  //提现申请正在处理的余额
						$data_arr['data'][] = $tmp_arr;
					}
					$this->_error_code = Core_Exception::CODE_DEAL_OK;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
				}
				else
				{
					$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
					Core_Logger::getInstance()->writeLog(get_called_class()."::".__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
				}
				$data_arr['allpage'] = $allpage;
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_NO_POWER;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_NO_POWER);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $data_arr;
	}
	//获得所有商户余额
	public function get_merchant_banlance()
	{
		$data_arr = array();
		if(check_merchant())
		{
			if($this->init_mysql_obj())
			{
				$mid = isset($_SESSION['HTC_LOGIN_DATA']['ad_tid'])?$_SESSION['HTC_LOGIN_DATA']['ad_tid']:'';
				$sql = 'select mid,money,amoney from t_mercahnt_balance where mid="'.$mid.'" ';
				if($result = $this->mysql->do_Query($sql))
				{
					if($row = $this->mysql->DB_fetch_array($result))
					{
						$data_arr['money'] = $row['money'] - $row['amoney'];  //可用余额
						$data_arr['amoney'] = $row['amoney'];  //提现申请正在处理的余额
					}
					else
					{
						$data_arr['money'] = 0;  //可用余额
						$data_arr['amoney'] = 0;  //提现申请正在处理的余额
					}
					$this->_error_code = Core_Exception::CODE_DEAL_OK;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
				}
				else
				{
					$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
					Core_Logger::getInstance()->writeLog(get_called_class()."::".__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_NO_POWER;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_NO_POWER);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $data_arr;
	}
	//根据条件获得营业额
	public function get_tumover_by_con_day($search_arr)
	{
		$tumover = 0;
		if($this->init_mysql_obj())
		{
			$btime = isset($search_arr['btime'])?mysql_real_escape_string($search_arr['btime']):'';
			$etime = isset($search_arr['etime'])?mysql_real_escape_string($search_arr['etime']):'';
			$paytype = isset($search_arr['paytype'])?mysql_real_escape_string($search_arr['paytype']):'';
			$mid = isset($search_arr['mid'])?mysql_real_escape_string($search_arr['mid']):'';

			$sql = 'select sum(pmoney) as tumover from t_merchant_bill_day where 1=1 ';
			if($btime)
				$sql .= ' and time>='.$btime;
			if($etime)
				$sql .= ' and time<='.$etime;
			if($paytype)
				$sql .= ' and paytype='.$paytype;
			if($mid)
				$sql .= ' and mid='.$mid;
			
			if($result = $this->mysql->do_Query($sql))
			{
				if($row = $this->mysql->DB_fetch_array($result))
				{
					$tumover = $row['tumover'];
					$this->_error_code = Core_Exception::CODE_DEAL_OK;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $tumover;
	}
	//获得当天的营业额
	public function get_nowday_tumover($mid)
	{
		$tumover = 0;
		if($this->init_mysql_obj())
		{
			$btime = date('Y-m-d 00:00:00');
			$etime = date('Y-m-d H:i:s');
			$sql = 'select paytype,money from t_pay_record where ctime>="'.$btime.'" and ctime<="'.$etime.'"';
			if($mid)
				$sql .= ' and mid='.$mid;
			
			if($result = $this->mysql->do_Query($sql))
			{
				while($row = $this->mysql->DB_fetch_array($result))
				{
					//将分转化为元
					$pmoney = round($row['money']/100,2);
					$tumover += $pmoney ;
					$this->_error_code = Core_Exception::CODE_DEAL_OK;
					$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
				}
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $tumover;
	}
	//获得可用激活码个数
	public function get_code_nums()
	{
		$nums = 0;
		if($this->init_mysql_obj())
		{
			$sql = 'select code from t_code where is_belong=0';
			
			if($nums = $this->mysql->DB_Query_Res_And_Count($sql))
			{
				$this->_error_code = Core_Exception::CODE_DEAL_OK;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $nums;
	}
	//将管理员操作记录写入操作记录表
	public function insert_into_admin_log($data)
	{
		$ret = false;
		if(is_array($data) && !empty($data))
		{
			if($this->init_mysql_obj())
			{
				foreach($data as $key=>$val)
				{
					$key = mysql_real_escape_string($key);
					$val = mysql_real_escape_string($val);
					$data[$key] = $val;
				}
				$sql = 'insert into t_admin_log(aid,time,t_name,act,data) values("'.$data['aid'].'","'.$data['time'].'","'.$data['t_name'].'","'.$data['act'].'","'.$data['data'].'")';
				Core_Logger::getInstance()->writeLog(get_called_class()."::".__METHOD__.":".__LINE__,"sql=".$sql,Core_Logger::LOG_LEVL_DEBUG);
				$result = $this->mysql->DB_Query($sql);
				if($result)
					$ret = true;
				else
				{
					Core_Logger::getInstance()->writeLog(get_called_class()."::".__METHOD__.":".__LINE__,"数据库操作失败 sql=".$sql,Core_Logger::LOG_LEVL_ERROR);
				}
			}
			else
			{
				Core_Logger::getInstance()->writeLog(get_called_class()."::".__METHOD__.":".__LINE__,"数据库连接失败",Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			Core_Logger::getInstance()->writeLog(get_called_class()."::".__METHOD__.":".__LINE__,'参数错误',Core_Logger::LOG_LEVL_ERROR);
		}
		return $ret;
	}
	//查询管理员操作记录
	public function get_admin_log($search_arr)
	{	
		$data_arr = array();
		if($this->init_mysql_obj())
		{
			$btime = isset($search_arr['btime'])?strtotime(mysql_real_escape_string($search_arr['btime'])):'';
			$etime = (isset($search_arr['etime'])&&!empty($search_arr['etime']))?(strtotime(mysql_real_escape_string($search_arr['etime'])) + 86399):'';
			$act = isset($search_arr['act'])?mysql_real_escape_string($search_arr['act']):'';
			if(check_admin())
				$aid = isset($search_arr['aid'])?mysql_real_escape_string($search_arr['aid']):'';
			else
				$aid = isset($_SESSION['HTC_LOGIN_DATA']['ad_id'])?$_SESSION['HTC_LOGIN_DATA']['ad_id']:'';
			
			$page = isset($search_arr['page'])?mysql_real_escape_string($search_arr['page']):1;
			$bnums = ($page-1)*PAGE_NUMS;

			$sql = 'select * from t_admin_log where 1=1 ';
			if($btime)
				$sql .= ' and time>='.$btime;
			if($etime)
				$sql .= ' and time<='.$etime;
			if($aid)
				$sql .= ' and aid='.$aid;
			if($act)
				$sql .= ' and act="'.$act.'"';

			$allnums = $this->mysql->DB_Query_Res_And_Count($sql);
			$allpage = ceil($allnums/PAGE_NUMS);

			$sql .= ' limit '.$bnums.','.PAGE_NUMS;
			if($result = $this->mysql->do_Query($sql))
			{
				$admin = new classAdmin();
				while($row = $this->mysql->DB_fetch_array($result))
				{
					$tmp_arr = array();
					$tmp_arr['id'] = $row['id'];
					$admin_info = $admin->get_admin_info_byid($row['aid']);
					$ad_nick = isset($admin_info['ad_nick'])?$admin_info['ad_nick']:'未知';
					$tmp_arr['aid'] = '['.$row['aid'].']'.$ad_nick;
					$tmp_arr['time'] = date('Y-m-d H:i:s',$row['time']);
					$tmp_arr['t_name'] = isset(self::$_table_name_arr[$row['t_name']])?self::$_table_name_arr[$row['t_name']]:'';
					$tmp_arr['act'] = isset(self::$_admin_act_arr[$row['act']])?self::$_admin_act_arr[$row['act']]:'';
					$tmp_arr['data'] = array2string(json_decode($row['data'],true),',');

					$data_arr['data'][] = $tmp_arr;
				}
				$data_arr['allNums'] = $allnums;
				$data_arr['allpage'] = $allpage;
				$this->_error_code = Core_Exception::CODE_DEAL_OK;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DEAL_OK);
			}
			else
			{
				$this->_error_code = Core_Exception::CODE_DB_EXCUTE_ERROR;
				$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_EXCUTE_ERROR);
				Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des.',sql='.$sql,Core_Logger::LOG_LEVL_ERROR);
			}
		}
		else
		{
			$this->_error_code = Core_Exception::CODE_DB_CONNECT_ERROR;
			$this->_error_des = Core_Exception::getErrorDes(Core_Exception::CODE_DB_CONNECT_ERROR);
			Core_Logger::getInstance()->writeLog(__METHOD__.":".__LINE__,$this->_error_des,Core_Logger::LOG_LEVL_ERROR);
		}
		return $data_arr;
	}
}
?>