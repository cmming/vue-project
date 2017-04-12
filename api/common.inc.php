<?php
////////////////////////////////////////////////////////////
//公共包含文件
//wl 20160913
///////////////////////////////////////////////////////////
session_start();
//设置时区
ini_set('date.timezone','Asia/Shanghai');
include('inc/db_config.php');               //数据库配置
include('inc/const_config.php');            //常量配置
include('class/Logger.php');                //日志类
include('class/Exception.php');             //异常处理类
include('class/DBOper.php');                //数据库操作类
//include('class/RedisOper.php');                //数据库操作类
// include('class/classAdmin.php');             //处理方法类
include('class/classAdminData.php');             //管理员处理方法类
include('class/multiPorts.class.php');             //功能方法类
include('class/classData.php');             //数据处理类
include('com/func.php');             //处理方法类
?>