<?php
/*
 * 图库LogicModel
 * */
class PhotoLogicModel extends BaseLogicModel
{
    private $_pkey = 'd9c6dbd0d8db685e42b7a8720c23f3d4';
    private $_mkey = 'cd33159678ea24381c7f230412b306a5';
    private $_helper;

    public function __construct(){
        import('ORG.Net.ResourceApi');
        $this->_helper = new ResourceApiClient($this->_pkey, $this->_mkey);
    }

    /*
     * 图片上传
     * */
    public function upload($file){
        $pathinfo = pathinfo($file['name']);
        $_origin_temp_file = str_replace('tmp', $pathinfo['extension'] ,tempnam(RUNTIME_PATH, uniqid()));
        //if(strpos($_origin_temp_file, '.') === false)
            $_origin_temp_file .= '.'.$pathinfo['extension'];
        $result = false;
        //var_dump("_file",$file,"_pathinfo",$pathinfo, "origin_temp_file",$_origin_temp_file);
     //   echo $file['tmp_name'];exit;
       //
       // move_uploaded_file($file['tmp_name'], $_origin_temp_file);exit;
        //var_dump(move_uploaded_file($file['tmp_name'], $_origin_temp_file));die;
        if(false !== move_uploaded_file($file['tmp_name'], $_origin_temp_file)) {
            $res = $this->_helper->upload($_origin_temp_file);
          //  echo $_origin_temp_file;
           // print_r($res);exit;
            if($res['success'] == true){
                $msg = $res['msg'];
                $result = array(
                    'src' => $msg['fpath'].'_p'.$msg['pid'].'_mk'.$msg['mid'],
                    'ext' => $msg['fext'],
                    'width' => $msg['image_width'],
                    'height' => $msg['image_height'],
                    'size' => $msg['fsize'],
                );
            }
        }
        return $result;
    }

    /*
     * 拼接访问特定格式图片的url
     *
     * @param  size 格式100X80
     * @param  clip 格式
     * @param scale 格式
     * @param watermark  水印ID
     * */
    public function url($fpath, $ext, $size = '', $clip = '',
                        $border = '', $scale = '', $watermark  = '',
                        $backgroud_color = '', $quality = ''){
        if(!$fpath)
            return false;
        $tmp = explode('_', $fpath);
        $file_id = $tmp[0];
        $pid = substr($tmp[1], 1);
        $mkid = substr($tmp[2], 2);
        return
            $this->_helper->get_resource_url($pid, $mkid, $file_id, $ext, $size, $clip,
            $border, $scale, $watermark, $backgroud_color, $quality);
    }

    /**
     * 
     * @param string $filePathName 带路径的文件
     * 
     * @return array 格式
     *           [
     *               'code' => 100,
     *               'msg' =>  [
     *                  'pid' => '1',
     *                  'mid' => '1',
     *                  'fname' => '5663FBE.jpg',
     *                  'fsize' => 6120,
     *                  'image_width' => 144,
     *                  'image_height' => 106,
     *                  'fmimetype' => 'image/jpeg',
     *                  'fpath' => 'b5/18/5/d4c2ace7790f961c7a6c76e51fe',
     *                  'fext' => 'jpg',
     *                  'SERVER_ADDR' => '172.16.244.142',
     *                  'HTTP_X_FORWARDED_FOR' => '123.124.163.242',
     *                  'REMOTE_ADDR' => '172.16.244.187',
     *                  'furl' => 'http://src.house.sina.com.cn/imp/imp/deal/b5/18/5/d4c2ace7790f961c7a6c76e51fe_p1_mk1.jpg',
     *                  'oscode' => '4764ae',
     *               ],
     *               'success' => true,
     *               'time' => '2015-12-15 14:21:02',
     *             ]
     */
    public function apiUpload($filePathName) {
        return $this->_helper->upload($filePathName);
    }
}
?>