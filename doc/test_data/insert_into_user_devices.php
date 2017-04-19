<?php
set_time_limit(0);
$link = mysql_connect('192.168.0.5','sdk_user','outstandingbull',1);
mysql_select_db('db_htc_center',$link);
$str_arr = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
$num_arr = array('0','1','2','3','4','5','6','7','8','9');
$sp_arr = array('10000','10001','10002','10003');
$paytype_arr = array('102','105');
//$k = 1;
for($i=1;$i<=100;$i++)
{ 
	//插入设备表
	$name = $str_arr[rand(0,25)].$str_arr[rand(0,25)].$str_arr[rand(0,25)].$str_arr[rand(0,25)];
	$addr = "http://192.168.0.92/new_frame/";
	$port = 8800;
	$state = rand(0,1);
	$sql = 'insert into t_equipment(name,addr,port,state) values("'.$name.'","'.$addr.'","'.$port.'","'.$state.'")';
	$result = mysql_query($sql,$link);
	$eid = mysql_insert_id();
	//插入套餐表
	$name = $str_arr[rand(0,25)].$str_arr[rand(0,25)].$str_arr[rand(0,25)].$str_arr[rand(0,25)];
	$price = rand(1,10);
	$utime = rand(10,30);
	$des = $str_arr[rand(0,25)].$str_arr[rand(0,25)].$str_arr[rand(0,25)].$str_arr[rand(0,25)];
	$sql = 'insert into t_package(name,price,utime,des) values("'.$name.'","'.$price.'","'.$utime.'","'.$des.'")';
	$result = mysql_query($sql,$link);
	$pid = mysql_insert_id();
	//插入商户设备表
	$mid = rand(1,5);
	$time = time()-86400*rand(0,30);
	$sql = "INSERT INTO t_user_equipment (mid,time,eid,pid) VALUES ('$mid','$time','$eid','$pid')";
	mysql_query($sql,$link);
	
}
?>