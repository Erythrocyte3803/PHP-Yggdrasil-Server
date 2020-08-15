<?php
class Encypt{
    //生成 sha1WithRSA 签名
    static function genSigniture($toSign){
        global $privatekey;
        $privateKey = wordwrap($privatekey, 64, "\n", true);
        $key = openssl_get_privatekey($privateKey);
        openssl_sign($toSign, $signature, $key);
        openssl_free_key($key);
        $sign = base64_encode($signature);
        return $sign;
    }

    //校验 sha1WithRSA 签名
    static function verifySigniture($data, $sign){
        global $publickey;
        $sign = base64_decode($sign);
        $pubKey = wordwrap($publickey, 64, "\n", true);
        $key = openssl_pkey_get_public($pubKey);
        $result = openssl_verify($data, $sign, $key, OPENSSL_ALGO_SHA1) === 1;
        return $result;
    }
}