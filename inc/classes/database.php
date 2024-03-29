<?php
class database {
    public $mysqli;
    function __construct() {
        global $host,
        $port,
        $user,
        $pass,
        $dbname;
        $iscon = mysqli_connect($host.":".$port, $user, $pass, $dbname);
        if (!$iscon) {
            echo "无法连接至MySQL数据库：".mysqli_connect_error();
            header(Exceptions::$codes[500]);
            die();
        }
        $this->mysqli = new mysqli($host.":".$port, $user, $pass, $dbname);
    }
    function query($sql) {
        $stmt = $this->mysqli->prepare($sql);
        if ($stmt == false) {
            echo "MySQL查询出错：".$this->mysqli->error;
            header(Exceptions::$codes[500]);
            return -1;
        }
        $stmt->execute();
        $ret = $stmt->get_result();
        $result = $ret->fetch_all();
        if (empty($result)) {
            return false;
        } else {
            return $result;
        }
    }
    function query_change($sql) {
        $stmt = $this->mysqli->prepare($sql);
        if ($stmt == false) {
            echo "MySQL查询出错：".$this->mysqli->error;
            header(Exceptions::$codes[500]);
            return -1;
        }
        $stmt->execute();
        $ret = $stmt->get_result();
        $result = $this->mysqli->affected_rows;
        if (empty($result)) {
            return false;
        } else {
            return $result;
        }
    }
    function isAvailable($email) {
        $ret = $this->query("select * from pre_ucenter_members where email = '".$email."'");
        return $ret;
    }
    function chkPasswd($email, $passwd) {
        $ret = $this->query("select * from pre_ucenter_members where email = '".$email."'");
        $ucpass = $ret[0][2];
        $salt = $ret[0][12];
        $playername = $ret[0][1];
        $playeruuid = UUID::getUserUuid($playername);
        $skinuuid = file_get_contents("https://api.zhjlfx.cn/?type=getuuid&method=email&email=".$email);
        $verif = password_verify($passwd, $ucpass);
        $newpass = password_hash($passwd, PASSWORD_BCRYPT);
        $saltedpass = md5(md5($passwd).$salt);
        if ($saltedpass == $ucpass) {
            $this->query_change("update pre_ucenter_members set password = '".$newpass."' where uuid = '".$email."'");
            $this->crePlayerUuid($playeruuid, $email, $playername);
            return true;
        } else if ($verif) {
            if ($skinuuid == '') {
                $this->crePlayerUuid($playeruuid, $email, $playername);
                return $verif;
            } else {
                $this->crePlayerUuid($skinuuid, $email, $playername);
                return $verif;
            }

        }
    }
    function updateUser($email, $userid) {
        $this->query_change("update pre_ucenter_members set lastlogintime = '".time()."', userid = '".$userid."' where email = '".$email."'");
    }
    function getUserid($email) {
        $ret = $this->query("select * from pre_ucenter_members where email = '".$email."'");
        if (!$ret) {
            return false;
        } else {
            return $ret[0][15];
        }
    }
    function creToken($cli_token, $userid) {
        $acctoken = UUID::getUserUuid(uniqid().$cli_token);
        $ret = $this->query("select * from pre_ucenter_tokens where owner_uuid = '".$userid."'");
        if (!$ret) {
            $this->query_change("insert into pre_ucenter_tokens (acc_token, cli_token, state, owner_uuid) values ('".$acctoken."', '".$cli_token."', 1, '".$userid."');");
        } else {
            $this->query_change("update pre_ucenter_tokens set acc_token = '".$acctoken."', cli_token = '".$cli_token."', state = 1 where owner_uuid = '".$userid."'");
        }

    }
    function getTokensByOwner($userid) {
        $ret = $this->query("select * from pre_ucenter_tokens where owner_uuid = '".$userid."'");
        if (!ret) {
            return false;
        } else {
            return array($ret[0][0], $ret[0][1]);
        }
    }
    function crePlayerUuid($playeruuid, $email, $playername) {
        $ret = $this->query("select * from pre_ucenter_members where email = '".$email."'");
        $uuid = $ret[0][14];
        if ($uuid == "") {
            $this->query_change("update pre_ucenter_members set uuid = '".$playeruuid."' where email = '".$email."'");
            $this->addPlayerInfo($playername, $playeruuid);
        } else {
            $playeruuid = $uuid;
            $this->addPlayerInfo($playername, $playeruuid);
        }
    }
    function getProfileByOwner($userid) {
        $ret = $this->query("select * from pre_ucenter_members where userid = '".$userid."'");
        if (!$ret) {
            return false;
        } else {
            return new Profile($ret[0][1], $ret[0][16], $ret[0][17]);
        }
    }
    function porfileToken($acctoken, $player_uuid) {
        $this->query_change("update pre_ucenter_tokens set profile = '".$player_uuid."' where acc_token = '".$acctoken."'");
    }
    function getUseridByAcctoken($acctoken) {
        $ret = $this->query("select * from pre_ucenter_tokens where acc_token = '".$acctoken."'");
        if (!$ret) {
            return false;
        } else {
            return $ret[0][5];
        }
    }
    function isAcctokenAvailable($acctoken) {
        $ret = $this->query("select * from pre_ucenter_tokens where acc_token = '".$acctoken."'");
        if (!$ret) {
            return false;
        } else {
            return true;
        }
    }
    function chkAcctoken($acctoken, $clitoken) {
        $ret = $this->query("select * from pre_ucenter_tokens where acc_token = '".$acctoken."'");
        if (!$ret) {
            return false;
        } else {
            return ($clitoken == $ret[0][1]);
        }
    }
    function getTokenState($acctoken) {
        $ret = $this->query("select * from pre_ucenter_tokens where acc_token = '".$acctoken."'");
        if (!$ret) {
            return false;
        } else {
            return $ret[0][4];
        }
    }
    function setTokenState($acctoken) {
        $this->query_change("update pre_ucenter_tokens set state = -1 where acc_token = '".$acctoken."'");
    }
    function killTokensByOwner($userid) {
        $this->query_change("update pre_ucenter_tokens set state = -1 where owner_uuid = '".$userid."'");
    }
    function updateAllTokenState() {
        $this->query_change("update pre_ucenter_tokens set state = 0 where ptime <= date_sub(now(),interval 120 minute);");
        $this->query_change("update pre_ucenter_tokens set state = -1 where ptime <= date_sub(now(),interval 10 days);");
        return $this->query_change("delete from pre_ucenter_tokens where state = -1");
    }
    function chkProfileToken($acctoken, $player_uuid) {
        $ret = $this->query("select * from pre_ucenter_tokens where acc_token = '".$acctoken."'");
        if (!$ret) {
            return false;
        } else {
            return ($player_uuid == $ret[0][2]);
        }
    }
    function creSession($server_id, $acc_token, $ip) {
        $this->query_change("insert into pre_ucenter_sessions (server_id, acc_token, ipaddr, o_time) values ('".$server_id."','".$acc_token."','".$ip."', now())");
    }
    function chkSession($playername, $serverid, $ipaddr) {
        $ret = $this->query("select * from pre_ucenter_sessions where server_id = '".$serverid."'");
        if (!$ret) {
            return false;
        } else {
            $owner_accctoken = $ret[0][1];
            $owner_userid = $this->getUseridByAcctoken($owner_accctoken);
            $player = $this->getProfileByOwner($owner_userid)->name;
            return(($player == $playername) && ($ipaddr == 'NONE' || $ipaddr == $ret[0][2]));
        }
    }
    function getAcctokenByServerid($serverid) {
        $ret = $this->query("select * from pre_ucenter_sessions where server_id = '".$serverid."'");
        if (!$ret) {
            return false;
        } else {
            return $ret[0][1];
        }
    }
    function getProfileByUuid($playeruuid) {
        $ret = $this->query("select * from pre_ucenter_members where uuid = '".$playeruuid."'");
        if (!$ret) {
            return false;
        } else {
            return new Profile($ret[0][1], $ret[0][16], $ret[0][17]);
        }
    }
    function getProfileByPlayer($playername) {
        $ret = $this->query("select * from pre_ucenter_members where username = '".$playername."'");
        if (!$ret) {
            return false;
        } else {
            return new Profile($ret[0][1], $ret[0][16], $ret[0][17]);
        }
    }
    function updateAllSessionState() {
        $this->query_change("delete from pre_ucenter_sessions where date(o_time) <= date_sub(now(),interval 30 second);");
    }
    function updateSkinData($uuid) {
        $stream_opts = [
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ]
        ];
        $texturedata = file_get_contents("https://api.zhjlfx.cn/?type=getjson&uuid=".$uuid, false, stream_context_create($stream_opts));
        $this->query_change("update pre_ucenter_members set texturedata = '".$texturedata."' where uuid = '".$uuid."'");
    }
    function addPlayerInfo($playername, $playeruuid) {
        $ret = $this->query("select * from pre_ucenter_chkname where uuid = '".$playeruuid."'");
        if (!$ret) {
            $this->query_change("insert into pre_ucenter_chkname (uuid, playername) values ('".$playeruuid."', '".$playername."')");
        } else {
            $this->query_change("update pre_ucenter_chkname set playername = '".$playername."' where uuid = '".$playeruuid."'");
        }
    }
    function getPlayerUuidByAcctoken($acctoken) {
        $ret = $this->query("select * from pre_ucenter_tokens where acc_token = '".$acctoken."'");
        if (!$ret) {
            return false;
        } else {
            return $ret[0][2];
        }
    }
    function isPlayerNameChanged($uuid) {
        $getname = $this->query("select * from pre_ucenter_members where uuid = '".$uuid."'");
        $getsavedname = $this->query("select * from pre_ucenter_chkname where uuid = '".$uuid."'");
        $crname = $getname[0][1];
        $this->query_change("update skin.uuid set name = '".$crname."' where uuid = '".$uuid."'");
        $rs = ($crname !== $getsavedname[0][1]);
        if ($getname && $getsavedname) {
            return $rs;
        }
    }
}