<?php
header('content-type:application/json;charset=utf8');
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/include.php');
if (cmethod::isPost() == false) {
    exceptions::doErr(405,'HTTP/1.1 405 Method not allowed','不支持该请求方法');
    exit;
}
$check_post_data = array(
    "username","password"
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
$email = $data['username'];
$passwd = $data['password'];
if ($email == '' or $passwd == '') {
    exceptions::doErr(403,'ForbiddenOperationException','邮箱或密码不能为空');
    exit;
}
//header("Content-Type: application/json; charset=utf-8");
if (!$db->isAvailable($email)) {
    exceptions::doErr(404,'ForbiddenOperationException','您输入的账号不存在');
    exit;
}
if (!$db->chkPasswd($email,$passwd)) {
    exceptions::doErr(403,'ForbiddenOperationException','您输入的邮箱或密码错误');
    exit;
}
$userid = UUID::getUserUuid(md5($email));
$db->updateUser($email,$userid);
$available_userid = $db->getUserid($email);
if (!isset($data['clientToken'])) {
    $cli_token = UUID::getUserUuid(md5(md5(uniqid()).$available_userid));
} else {
    $cli_token = $data['clientToken'];
}
if (!isset($data['requestUser'])) {
    $req_user = false;
} else {
    $req_user = $data['requestUser'];
}
$db->creToken($cli_token,$available_userid);
$tokens = $db->getTokensByOwner($available_userid);
$profile = $db->getProfileByOwner($available_userid);
$authdata = array(
    "accessToken" => $tokens[0],
    "clientToken" => $tokens[1]
);
if ($profile !== null) {
    $db->porfileToken($tokens[0],$profile->UUID);
    $authdata["availableProfiles"] = array(
        $profile->getArrayFormated()
    );
    $authdata["selectedProfile"] = $profile->getArrayFormated();
}
if ($req_user) {
    $authdata["user"] = (new User($json["username"],"",$userid,"zh_CN"))->getArrayFormated();
}
echo json_encode($authdata);