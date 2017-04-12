
<?php
// header("Access-Control-Allow-Origin: *");
function array_remove(&$arr, $offset) 
{ 
array_splice($arr, $offset, 1); 
} 
// var_dump($_REQUEST);exit();

// post的请求的化直接使用这种方式才能接受前端传来的参数

$request_data = file_get_contents('php://input', true);
$request_data = json_decode($request_data,true);

//  var_dump($request_data['type']);exit();
$type = isset($request_data['type'])?$request_data['type']:'';
// $type=isset($_REQUEST['type'])?$_REQUEST['type']:'';
// $act=isset($_REQUEST['act'])?$_REQUEST['act']:'';
$act=isset($request_data['act'])?$request_data['act']:'';

$data=array(array(
    "id"=>"12",
    "name"=> "陈明",
    "birthday"=> "19930427",
    "sex"=> "男",
    "state"=>"1"
),array(
    "id"=>"13",
    "name"=> "陈明",
    "birthday"=> "19930427",
    "sex"=> "男",
    "state"=>"2"
),array(
    "id"=>"14",
    "name"=> "陈明",
    "birthday"=> "19930427",
    "sex"=> "男",
    "state"=>"3"
),array(
    "id"=>"15",
    "name"=> "陈明",
    "birthday"=> "19930427",
    "sex"=> "男",
    "state"=>"4"
));
if($type=='tableDate'&&$act=='del'){
    $del_id=isset($request_data['del_id'])?$request_data['del_id']:'';
    foreach($data as $key=>$item){
        if($item['id']==$del_id){
            array_remove($data, $key); 
        }
    }
}
echo json_encode($data);
?>