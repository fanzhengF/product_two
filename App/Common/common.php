<?php
/**
 * 加密函数
 * @param string $txt 需要加密的字符串
 * @param string $key 密钥
 * @return string 返回加密结果
 */
function encrypt_en($txt, $key = ''){
    if (empty($txt)) return $txt;
    if (empty($key)) $key = md5(c('MD5_KEY'));
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.";
    $ikey ="-x6g6ZWm2G9g_vr0Bo.pOq3kRIxsZ6rm";
    $nh1 = rand(0,64);
    $nh2 = rand(0,64);
    $nh3 = rand(0,64);
    $ch1 = $chars{$nh1};
    $ch2 = $chars{$nh2};
    $ch3 = $chars{$nh3};
    $nhnum = $nh1 + $nh2 + $nh3;
    $knum = 0;$i = 0;
    while(isset($key{$i})) $knum +=ord($key{$i++});
    $mdKey = substr(md5(md5(md5($key.$ch1).$ch2.$ikey).$ch3),$nhnum%8,$knum%8 + 16);
    $txt = base64_encode(time().'_'.$txt);
    $txt = str_replace(array('+','/','='),array('-','_','.'),$txt);
    $tmp = '';
    $j=0;$k = 0;
    $tlen = strlen($txt);
    $klen = strlen($mdKey);
    for ($i=0; $i<$tlen; $i++) {
        $k = $k == $klen ? 0 : $k;
        $j = ($nhnum+strpos($chars,$txt{$i})+ord($mdKey{$k++}))%64;
        $tmp .= $chars{$j};
    }
    $tmplen = strlen($tmp);
    $tmp = substr_replace($tmp,$ch3,$nh2 % ++$tmplen,0);
    $tmp = substr_replace($tmp,$ch2,$nh1 % ++$tmplen,0);
    $tmp = substr_replace($tmp,$ch1,$knum % ++$tmplen,0);
    return $tmp;
}
/**
 * 解密函数
 * @param string $txt 需要解密的字符串
 * @param string $key 密匙
 * @return string 字符串类型的返回结果
 */
function decrypt_de($txt, $key = '', $ttl = 0){
    if (empty($txt)) return $txt;
    if (empty($key)) $key = md5(c('MD5_KEY'));
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.";
    $ikey ="-x6g6ZWm2G9g_vr0Bo.pOq3kRIxsZ6rm";
    $knum = 0;$i = 0;
    $tlen = @strlen($txt);
    while(isset($key{$i})) $knum +=ord($key{$i++});
    $ch1 = @$txt{$knum % $tlen};
    $nh1 = strpos($chars,$ch1);
    $txt = @substr_replace($txt,'',$knum % $tlen--,1);
    $ch2 = @$txt{$nh1 % $tlen};
    $nh2 = @strpos($chars,$ch2);
    $txt = @substr_replace($txt,'',$nh1 % $tlen--,1);
    $ch3 = @$txt{$nh2 % $tlen};
    $nh3 = @strpos($chars,$ch3);
    $txt = @substr_replace($txt,'',$nh2 % $tlen--,1);
    $nhnum = $nh1 + $nh2 + $nh3;
    $mdKey = substr(md5(md5(md5($key.$ch1).$ch2.$ikey).$ch3),$nhnum % 8,$knum % 8 + 16);
    $tmp = '';
    $j=0; $k = 0;
    $tlen = @strlen($txt);
    $klen = @strlen($mdKey);
    for ($i=0; $i<$tlen; $i++) {
        $k = $k == $klen ? 0 : $k;
        $j = strpos($chars,$txt{$i})-$nhnum - ord($mdKey{$k++});
        while ($j<0) $j+=64;
        $tmp .= $chars{$j};
    }
    $tmp = str_replace(array('-','_','.'),array('+','/','='),$tmp);
    $tmp = trim(base64_decode($tmp));
    if (preg_match("/\d{10}_/s",substr($tmp,0,11))){
        if ($ttl > 0 && (time() - substr($tmp,0,11) > $ttl)){
            $tmp = null;
        }else{
            $tmp = substr($tmp,11);
        }
    }
    return $tmp;
}
/**
 * 生成相应的签名
 * @param string $string
 *
 * @return string
 */
function genSign($string) {
    return hash(C('API_HASH_METHOD'), C('API_PRE_KEY') . $string . C('API_SUF_KEY'));
}

/**
 * 生成加密用的字符串
 *
 * @param array $array
 *
 * @return string
 */
function genString($array, $filterSignKey = 'sign') {
    ksort($array);
    $filterArray = explode(',', $filterSignKey);
    $str = '';
    foreach ($array as $key => $val) {
        if (!in_array($key, $filterArray)) {
            $str .= $key . $val;
        }
    }
    return $str;
}

/**
 * 验证签名是否正确
 *
 * @param string $verifyString
 * @param string $sgin
 *
 * @return boolean true代表正确
 */
function verify($verifyString, $sgin) {
    return $sgin == genSign($verifyString);
}

/**
 * 参看php手册 array_column函数
 *
 * @param array $array
 * @param string $columnKey
 * @param string $indexKey
 *
 * @return array
 */
function arrayColumn($array, $columnKey, $indexKey = null) {
    if (version_compare(PHP_VERSION, '5.5') >= 0) {
        return array_column($array, $columnKey, $indexKey);
    }
    $temp = array();
    if (empty($indexKey)) {
        foreach ($array as $val) {
            $temp[] = $val[$columnKey];
        }
        return $temp;
    }
    foreach ($array as $val) {
        $temp[$val[$indexKey]] = $val[$columnKey];
    }
    return $temp;
}

/**
 * 比较两个数组相同字段的内容
 * 注意，以源数组的键做参照，同时数组仅限一维数组
 *
 * @param array $src    源数组
 * @param array $aim    对比数组
 *
 * @return bool
 */
function compareArray($src, $aim) {
    foreach ($src as $key => $val) {
        if (!isset($aim[$key]) || 0 !== strcmp($val, $aim[$key])) {
            return false;
        }
    }
    return true;
}
/**
 *
 * @param type $url
 * @param type $params 数组形式传入
 * @param type $timeout
 */
function get($url, $params, $timeout=60, $json = false){
    return Request::get($url, $params, $timeout,$json);
}
/**
 *
 * @param type $url
 * @param type $params 数组形式传入
 * @param type $timeout
 */
function post($url, $params, $timeout=60,$json = false){
    return Request::post($url, $params, $timeout, $json);
}

/**
 * 依据session中的信息判断
 */
function hasModifyRight($userId) {
    return 1 == $_SESSION['authId'] || UserLogicModel::getUid() == $userId;
}

function decodeBnsLogData($mixedData) {
    if (!$mixedData) {
        return '';
    }
    return json_encode(unserialize($mixedData));
}

function msubstr($str, $start=0, $length = 20, $charset="utf-8", $suffix=true) {
    import('ORG.Util.String');
    return String::msubstr($str, $start, $length, $charset, $suffix);
}

function jsonEncode($res){
    if(version_compare(PHP_VERSION, '5.4') >= 0){
        return json_encode($res,JSON_UNESCAPED_UNICODE);
    }else{
        return json_encode($res);
    }
}





