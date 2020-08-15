<?php
class cmethod {
    static function isGet() {
        return $_SERVER['REQUEST_METHOD'] == 'GET' ? true : false;
    }
    static function isPost() {
        return $_SERVER['REQUEST_METHOD'] == 'POST' ? true : false;
    }
}