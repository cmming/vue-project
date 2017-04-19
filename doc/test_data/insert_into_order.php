<?php
set_time_limit(0);
$link = mysql_connect('192.168.0.5','sdk_user','outstandingbull',1);
mysql_select_db('db_htc_center',$link);
/*$conn = mysql_connect('localhost','root','',1);
mysql_select_db('db_pay_center',$conn);
$conn_user = mysql_connect('localhost','root','',1);
mysql_select_db('db_user_center',$conn_user);*/
$str_arr = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
$num_arr = array('0','1','2','3','4','5','6','7','8','9');
$sp_arr = array('10000','10001','10002','10003');
$paytype_arr = array('102','105');
//$k = 1;
for($i=1;$i<=100;$i++)
{
	$id = date('YmdHis').$i;
	$oid = md5($i);
	$mtime = time()-5*60;
	$gtime = rand(300,500);
	$uid = rand(10000,100000);
	$mid = rand(1,10);
	$money = rand(500,5000);
	$paytype = $paytype_arr[rand(0,1)];
	$pid = rand(1,10);
	//$state = rand(0,2);
	$state = 2;
	$time = time()-86400*rand(0,30);
	$ctime = date('Y-m-d H:i:s',$time);
	$sql = "INSERT INTO t_order (id,oid,mtime,gtime,uid,mid,money,paytype,pid,state,ctime) VALUES ('$id','$oid','$mtime','$gtime','$uid','$mid','$money','$paytype','$pid','$state','$ctime')";
	if(mysql_query($sql,$link))
	{
		if($state == 2)
		{
			$sql = "INSERT INTO t_pay_record (id,oid,mtime,gtime,uid,mid,money,paytype,pid,ctime) VALUES ('$id','$oid','$mtime','$gtime','$uid','$mid','$money','$paytype','$pid','$ctime')";
			if(!mysql_query($sql,$link))
			{
				echo $sql;
				exit();
			}
		}
	}
	else
	{
		echo $sql;
		exit();
	}
	
}
?>