<?php

/**
 * @author 栾涛 <286976625@qq.com>
 */
class Request {

    public static function get($url, $params, $timeout = 60, $json = false) {
        return self::_request('get', $url . '?' . http_build_query($params, '', '&'), $params, $timeout, $json);
    }

    public static function post($url, $params, $timeout = 60, $json = false) {
        return self::_request('post', $url, http_build_query($params, '', '&'), $timeout, $json);
    }

    public static function _request($type, $url, $params, $timeout, $json = false) {
        $t1 = microtime(true);
        $fun = $type . "Url";
        $respons = self::$fun($url, $params, $timeout);
        $t2 = microtime(true);
        $responseTime = (string) round($t2 - $t1, 6);
        //$RequestLogModel = D('RequestLog');
        //$RequestLogModel->writeLog($type, $url, $params, $timeout, $responseTime, $respons, $json);
        $json && $respons = json_decode($respons, TRUE);
        return $respons;
    }

    private static function getUrl($url, $timeout = 60) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    private static function postUrl($url, $post_fields, $timeout = 60) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

}
