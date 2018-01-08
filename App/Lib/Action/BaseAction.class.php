<?php
class BaseAction extends Action
{
	protected  $now_city = ''; 	//取得当前站点

	public static function uEncode($returnurl, $isReplaceAnd = false)
	{

		$returnurl = urlencode($returnurl);
		$oldArray = array('%3A', '%2F', '%2B','%3F','%3D');
		$newArray = array(':', '/', '+', '?', '=');
		if ($isReplaceAnd)
		{
			$oldArray[] = '%26';
			$newArray[] = '&';
		}
		$returnurl = str_replace($oldArray, $newArray, $returnurl);
		return $returnurl;
	}
	public  function curl_get_contents($url,$timeout=100) {
		$curlHandle = curl_init();
		curl_setopt( $curlHandle , CURLOPT_URL, $url );
		curl_setopt( $curlHandle , CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $curlHandle , CURLOPT_TIMEOUT, $timeout );
		$result = curl_exec( $curlHandle );
		curl_close( $curlHandle );
		return $result;
	}

    /**
     * 输出json格式数据
     *
     * @param array $array
     */
    public static function echoJson($array) {
        header('Content-Type:application/json; charset=utf-8');
        if(version_compare(PHP_VERSION, '5.4') >= 0){

            echo json_encode($array,JSON_UNESCAPED_UNICODE);
        }else{
            echo json_encode($array);
        }
        exit;
    }
    public function returnJson($status,$data)
    {
        $res = array('status' => $status,'data' => $data);
        if(version_compare(PHP_VERSION, '5.4') >= 0){

            echo json_encode($res,JSON_UNESCAPED_UNICODE);
        }else{
            echo json_encode($res);
        }
        exit;
    }


    public function export_csv($filename, $data, $isExcel = false){
        $contentType = 'application/octet-stream';
        if ($isExcel) {
            $contentType = 'application/vnd.ms-excel';
        }
        Header("Content-type: $contentType");
        Header("Accept-Ranges: bytes");
        Header("Content-Disposition: attachment; filename=".$filename); // 输出文件内容
        echo mb_convert_encoding($data, 'GBK', 'utf-8');
        exit;
    }

    /**
     * 通用导出 cvs
     * @param array $exportData
     * @param array $exportConfig
     * @param array $extraData
     */
    protected function commonExportCvs($exportData, $exportConfig, $extraData) {
        $data = implode(',', arrayColumn($exportConfig, 'title')) . "\n";
        foreach ($exportData as $val) {
            $temp = '';
            foreach ($exportConfig as $key => $field) {
                //echo $key . '<br />';
                if (isset($field['list'])) {
                    $temp .= $extraData[$field['list']][$val[$key]] . ",";
                } else if (isset($field['date'])) {
                    $temp .= date($field['date'], $val[$key]) . ",";
                } else if (isset ($field['callback'])) {
                    $extraDataDetail = isset($extraData[$key]) ? $extraData[$key] : $extraData['callback'];
                    $temp .= $field['callback'][0]->$field['callback'][1]($val, $key, $extraDataDetail) . ",";
                } else {
                    $temp .= "{$val[$key]},";
                }

            }
            $data .= substr($temp, 0, -1) . "\n";
        }
        //echo $data;exit;
        //exit;
        $this->export_csv(time() . '.csv', $data);
    }


}