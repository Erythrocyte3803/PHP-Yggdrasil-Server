<?php
//外置登录服务器基本配置//
$servername = "九境尘域"; //外置登录服务器名
$impname = "9cymc-minecraft-auth";
$impver = "1.0"; //版本号
$skinurl = array(
    ".zhjlfx.cn",//皮肤站链接，可填写多个
    ".minecraft.net"
);
$homepage = "https://www.9cymc.cn"; //网站首页
$regurl = "https://reg.zhjlfx.cn"; //玩家注册地址
//外置登录服务器密钥配置//
$publickey = file_get_contents($_SERVER['DOCUMENT_ROOT'] ."/keys/yggdrasil-public-key.pem"); //公钥文件
$privatekey = file_get_contents($_SERVER['DOCUMENT_ROOT'] ."/keys/yggdrasil-private-key.pem"); //私钥文件
//外置登录服务器数据库配置//
$host = 'host'; //数据库地址
$port = 3306; //数据库端口
$user = 'user'; //数据库用户名
$pass = 'pass'; //数据库密码
$dbname = 'database'; //数据库名
//正版直接登录支持
$mojanglogin = true;