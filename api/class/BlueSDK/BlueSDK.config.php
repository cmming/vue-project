<?php
////////////////////////////////////////////////////////
//配置文件，保存晃游平台分配给应用的配置信息
////////////////////////////////////////////////////////

//【注意修改】联调时，请把该值设置为true，上线后请设置后false
//调试模式可可输出日志
define('BLUE_TEST_FLAG',false);
//测试地址
define('BLUE_TEST_URL',"http://api.17huang.com/test/");
//define('BLUE_TEST_URL',"http://192.168.1.250:8080/blue/api/");
//define('BLUE_TEST_URL',"http://192.168.1.4:8080/CoreSDK/");
//define('BLUE_TEST_URL',"http://192.168.0.4/api/");

//平台分内的内部加密密钥
define('SDK_SECRET_KEY','&&@!#$__++2134234~~~@!#42134s^^^@@#$d42');

//晃游平台分配给CP的应用编号
define('CP_APP_ID','2010');
//晃游平台分配给CP的应用标识
define('CP_APP_KEY','2ba6ed7a77972797ce81b6f782f6bb4f');
//晃游平台分配给CP的应用的通信息密钥
define('CP_APP_SECRET','A334bSDF!==2--==@edL;W');

require_once(dirname(__FILE__) . '/BlueSDK_Pro.Class.php');

?>
