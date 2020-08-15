<?php
header('content-type:application/json;charset=utf8');
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/include.php');
if (cmethod::isPost() == false) {
    exceptions::doErr(405,'HTTP/1.1 405 Method not allowed','不支持该请求方法');
    exit;
}
$check_post_data = array(
    "accessToken"
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
$available_userid = $db->getUseridByAcctoken($acctoken);
$acctoken = $data['accessToken'];
$clitoken = $data['clientToken'];
$uuid = $db->getPlayerUuidByAcctoken($acctoken);
if (!isset($clitoken)) {
    $cli_token = UUID::getUserUuid(md5(md5(uniqid()).$available_userid));
} else {
    $cli_token = $data['clientToken'];
}
if (!$db->isAcctokenAvailable($acctoken)) {
    exceptions::doErr(403,'ForbiddenOperationException','该Token不存在');
}
if (!(isset($clitoken) == $db->chkAcctoken($acctoken,$clitoken))) {
    exceptions::doErr(403,'ForbiddenOperationException','指定的ClientToken无效');
}
if ($db->getTokenState($acctoken) < 0) {
    exceptions::doErr(403,'ForbiddenOperationException','该Token已失效');
}
if($db->isPlayerNameChanged($uuid)){
    $db->setTokenState($acctoken);
}
header(Exceptions::$codes[204]);