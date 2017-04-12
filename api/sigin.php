<?php
// header("Access-Control-Allow-Origin: *");

$request_data = file_get_contents('php://input', true);
$request_data = json_decode($request_data,true);

if(isset($request_data['userName'])&&isset($request_data['userPwd'])){
    $userName=$request_data['userName'];
    $userPwd=$request_data['userPwd'];
    // 判断帐号
    if($userName=="admin"&&($userPwd=="admin")){
        $data['code']="200";
        $data['msg']="登录成功";
        $data['data']="登录成功";
    }else{
        $data['code']="20001";
        $data['msg']="密码或帐号错误";
        $data['data']="登录失败";
    }

}else{
    $data['code']="20002";
    $data['msg']="缺少参数";
    $data['data']="缺少参数";
}


echo json_encode($data);
?>

