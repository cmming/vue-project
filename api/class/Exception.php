<?php

class Core_Exception extends Exception
{
	const CODE_DEAL_FAIL = '100';
	const CODE_DEAL_OK = '200';
	const CODE_FORBIDDEN = '403';

	const CODE_FORMDATA_BAD = '2001';
	const CODE_UNAME_EXISTS = '2002';
	const CODE_ADD_ADMIN_FAIL = '2003';
	const CODE_NO_POWER = '2004';
	const CODE_ERROR_ADMIN_TYPE = '2005';
	const CODE_NOT_ADD_ADMIN_TYPE_POWER = '2006';
	const CODE_CREATE_ADMIN_MENU_FAIL = '2007';
	const CODE_TERMID_EXISTS = '2008';
	const CODE_UPDATE_ISPAY_FAIL = '2009';
	const CODE_NOT_MONEY = '2010';
	const CODE_MERCHANT_NOT_ACCOUNT = '2011';
	const CODE_MERCHANT_DEMANDS_FAIL = '2012';
	const CODE_NOT_REACH_DEMANDS = '2013';
	const CODE_BILL_RECORD_FAIL = '2014';
	const CODE_GET_CODE_FAIL = '2015';
	const CODE_NO_PACKAGE = '2016';
	const CODE_UPDATE_CODE_BELONG_FAIL = '2017';
	const CODE_UPDATE_AMONEY_FAIL = '2018';
	const CODE_GET_APPLY_ID_FAIL = '2019';
	const CODE_UPDATE_APPLY_STATE_FAIL = '2020';
	const CODE_MERCHANT_NOT_BILL = '2021';
	const CODE_MERCHANT_DOCKER_FAIL = '2022';
	const CODE_ADMIN_LOG_ERROR = '2023';

    const CODE_BAD_PARAM = '3001';
	const CODE_BAD_ACTION = '3002';
	const CODE_BAD_SIGN = '3003';

	const CODE_FAILED_CREATE_OBJ = '3008';
	const CODE_DB_CONNECT_ERROR = '3009';
	const CODE_DB_EXCUTE_ERROR = '3010';	

	const CODE_USER_UNKNOW_ERROR = '3030';
	const CODE_USER_NOT_EXIST = '3031';
	const CODE_USER_DISABLED = '3032';
	const CODE_USER_NOT_LOGIN = '3033';
	const CODE_USER_TOKEN_ERR = '3034';
	const CODE_REGISTRY_DEVID_EXIST = '3035';
	const CODE_REGISTRY_NICK_EXIST = '3036';
	const CODE_FAIL_PWD = '3037';
	const CODE_UNAME_NO_ACTIVATE = '3038';

	const CODE_TERID_NOT_EXIST = '3040';
	const CODE_TERID_EXIST = '3041';

	const CODE_SHARE_SAVE_ERROR = '3051';
	const CODE_SHARE_NO_PRIV = '3052';
	const CODE_SHARE_FILE_TOO_BIG = '3053';	
	const CODE_SHARE_NO_PIC_AND_LINK = '3054';	

	const CODE_PINGLUN_FAIL = '3071';	
	const CODE_NOZAN_VAL  = '3072';

	const CODE_BAD_PAYTYPE  = '4001';
	const CODE_REMOTE_API_FAIL  = '4002';

    const CODE_FILE_NOT_EXISTS  = '6001';

	const CODE_UNKNOW_ERROR = '800000';
	const CODE_DEAL_FAILED = '800001';
	const CODE_FUNC_FORBIDDEN = '800002';
	const CODE_SYS_UPGRADE = '800003';
	const CODE_FUNC_BUILDING = '800004';
	

    private static $_MESSAGE_MAP = array(
		'100' => 'FAIL',
		'200' => 'OK',
		'403' => '禁止访问',

		'2001' => '表单数据缺失',
		'2002' => '登录名已经存在',
		'2003' => '添加管理员失败',
		'2004' => '没有权限操作接口',
		'2005' => '管理员类型错误',
		'2006' => '还未添加该类型管理员菜单',
		'2007' => '创建管理员权限菜单失败',
		'2008' => '终端设备标识已存在',
		'2009' => '跟新结算表值失败',
		'2010' => '商户申请的金额超过商户所拥有的钱',
		'2011' => '商户没有提现帐号',
		'2012' => '获得商户提现要求失败',
		'2013' => '没有达到提现要求',
		'2014' => '提现失败',
		'2015' => '获得激活码失败',
		'2016' => '该商户目前没有套餐',
		'2017' => '跟新激活码状态失败',
		'2018' => '冻结商户申请金额失败',
		'2019' => '获得提现申请ID失败',
		'2020' => '跟新提现标志失败',
		'2021' => '正在处理提现',
		'2022' => '锁定提现状态失败',
		'2023' => '写入管理员操作记录失败',

        '3001' => '传入参数不完整',
		'3002' => '错误的动作请求',
		'3003' => '数据签名错误',
		'3008' => '创建对象失败',
		'3009' => '数据库连接失败',
		'3010' => '数据库语句执行失败',

		'3030' => '用户登陆未知错误',
		'3031' => '用户不存在',
		'3032' => '帐号被禁用',
		'3033' => '用户未登陆',
		'3034' => '令牌错误',
		'3035' => '要注册的用户设备已存在',
		'3036' => '昵称已存在',
		'3037' => '密码错误',
		'3038' => '帐号未激活',

		'3040' =>'设备标识可用',
		'3041' =>'设备标识重复添加',

		'3051' => '分享信息保存出错',
		'3052' => '没有权限分享',
		'3053' => '分享的文件过大',
		'3054' => '我为人人，人人为我，图片和链接至少分享一个吧！',

		'3071' => '评论失败',
		'3072' => '点赞失败，赞力值不够',

		'4001' => '支付类型错误',
		'4002' => '远程接口调用失败',

		'6001' => '文件不存在',
		
        '800000'=>'未知错误，请检查',
		'800001'=>'处理业务失败',
		'800002'=>'功能被禁止使用',
		'800003'=>'系统维护中，暂停服务',
		'800004'=>'功能开发中，暂未开放',
    );

    public function __construct($code, $message='')
    {     
       if (isset(self::$_MESSAGE_MAP[$code])) {
            //$message = "[err:".$code."]".self::$_MESSAGE_MAP[$code].$message;
			$message = self::$_MESSAGE_MAP[$code].$message;
        }
        parent::__construct($message, $code);
    }

	public static function getErrorDes($code)
	{
		if (isset(self::$_MESSAGE_MAP[$code])) {
            return self::$_MESSAGE_MAP[$code];
        }
		return "";
	}

}
