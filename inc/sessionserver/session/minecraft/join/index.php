<?php
header('content-type:application/json;charset=utf8');
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/include.php');
if (cmethod::isPost() == false) {
    exceptions::doErr(405,'HTTP/1.1 405 Method not allowed','不支持该请求方法');
    exit;
}
$check_post_data = array(
    "accessToken","selectedProfile","serverId"
);
$data = json_decode(file_get_contents('php://input'),true,10);
if ($data == null) {
    exceptions::doErr(400,'IllegalArgumentException','提交的数据不是JSON数据');
    exit;
}
foreach ($check_post_data as $v) {
    if (!isset($data[$v])) {
        exceptions::doErr(400,'IllegalArgumentException','缺少参数');
        exit;
    }
}
$acctoken = $data['accessToken'];
$selected = $data['selectedProfile'];
$serverid = $data['serverId'];
if (!$db->isAcctokenAvailable($acctoken)) {
    exceptions::doErr(403,'ForbiddenOperationException','该Token不存在','Token_Not_Exist');
}
if (!(isset($selected) == $db->chkProfileToken($acctoken,$selected))) {
    exceptions::doErr(403,'ForbiddenOperationException','指定的Profile无效','Wrong_Profile_UUID');
}
if ($db->getTokenState($acctoken) < 0) {
    exceptions::doErr(403,'ForbiddenOperationException','该Token已失效','Token_Not_Ready');
}
$ip = $_SERVER['REMOTE_ADDR'];
$db->creSession($serverid,$acctoken,$ip);
header(Exceptions::$codes[204]);