<?php
//API文件根目录
define('API_FILE_ROOT_PATH',realpath(dirname(__FILE__)) . '/../');
//日志路径
define('LOG_PATH',API_FILE_ROOT_PATH.'logs/');
// define('LOG_PATH','/alidata2/web_data/joy.bluevr.cn/admin_data/');

//普通系统日志文件路径
define('PATH_SYS_LOG', LOG_PATH."sys_log/");
//系统记录的数据文件路径
define('PATH_SYS_DATA', LOG_PATH."Data_sys/");
//行为统记数据文件路径
define('PATH_STAT_DATA', LOG_PATH."Data_stat/");
//权限菜单文件
define('ADMIN_RIGTHS_PATH',API_FILE_ROOT_PATH.'../resource/menuData/adminMenu/');
//菜单文件
define('ALL_MENU_PATH',API_FILE_ROOT_PATH.'../resource/menuData/');
//文件上传路径
define('UPLOAD_FILE_PATH',API_FILE_ROOT_PATH.'uploads/');

//分页每页数
define('PAGE_NUMS',10);

//访问域名
define('DOMAIN_HTTP','http://s.lgshouyou.com/vr_mall/');
//define('DOMAIN_HTTP','http://192.168.0.92/vr_mall/admin_new/api/');
//回调地址
define('WP_PAY_RECV_URL',DOMAIN_HTTP."recv.php");

//付款方式配置
$paytype_config_arr = array('102'=>'支付宝','105'=>'微信'); 
global $paytype_config_arr;

//批次生成激活码的个数
define('CREATE_ACTIVITY_CODE_NUMS',100);
?>