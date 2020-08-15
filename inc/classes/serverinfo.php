<?php
class serverinfo{
    static function info($servername,$impname,$impver,$homepage,$regurl,$skinurl,$publickey) {
        $serverinfo = array(
            "meta" => array(
                "serverName" => $servername,
                "implementationName" => $impname,
                "implementationVersion" => $impver,
                "links" => array(
                    "homepage" => $homepage,
                    "register" => $regurl,)),
            "skinDomains" => $skinurl,
            "signaturePublickey" => $publickey);
        return $serverinfo;
    }
}