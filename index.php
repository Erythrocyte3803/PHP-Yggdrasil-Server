<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/include.php');
if ($_SERVER["REQUEST_URI"] != "/") {
    $requri = explode("?",$_SERVER["REQUEST_URI"])[0];
    if (strpos($requri,"sessionserver/session/minecraft/profile") > -1) {
        include("inc/sessionserver/session/minecraft/profile/index.php");
    }else
        include("inc".$requri."/index.php");
} else {
    header('content-type:application/json;charset=utf8');
    echo json_encode(serverinfo::info($servername,$impname,$impver,$homepage,$regurl,$skinurl,$publickey));
}