<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/include.php');
if (cmethod::isGet() == false) {
    exceptions::doErr(405,'HTTP/1.1 405 Method not allowed','不支持该请求方法');
    exit;
}
$p_name = $_GET['username'];
$serverid = $_GET['serverId'];
if (isset($_GET['ip'])) {
    $ipaddr = $_GET['ip'];
} else {
    $ipaddr = 'NONE';
}
if ($db->chkSession($p_name,$serverid,$ipaddr)) {
    $acctoken = $db->getAcctokenByServerid($serverid);
    if ($db->getTokenState($acctoken) < 0) {
        exceptions::doErr(403,'ForbiddenOperationException','该Token已失效');
    }
    $userid = $db->getUseridByAcctoken($acctoken);
    $profile = $db->getProfileByOwner($userid);
    echo $profile;
}else{
    header(Exceptions::$codes[204]);
}