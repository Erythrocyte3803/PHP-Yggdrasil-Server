<?php
header('content-type:application/json;charset=utf8');
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/include.php');
if (cmethod::isPost() == false) {
    exceptions::doErr(405,'HTTP/1.1 405 Method not allowed','不支持该请求方法');
    exit;
}
$data = json_decode(file_get_contents('php://input'),true,10);
if ($data == null) {
    exceptions::doErr(400,'IllegalArgumentException','提交的数据不是JSON数据',json_last_error());
    exit;
}
if (count($data)>10){
    Exceptions::doErr(400,"IllegalArgumentException","提交的数据过多","一次只能同时提交10条数据");
    exit;
}
$result = array();
foreach($data as $pname){
    $result[]=$db->getProfileByPlayer($pname)->getArrayFormated();
}
echo json_encode($result);