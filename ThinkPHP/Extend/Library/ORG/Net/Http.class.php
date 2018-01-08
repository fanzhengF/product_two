<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

/**
 * Http 工具类
 * 提供一系列的Http方法
 * @category   ORG
 * @package  ORG
 * @subpackage  Net
 * @author    liu21st <liu21st@gmail.com>
 */
class Http {

    /**
     * 采集远程文件
     * @access public
     * @param string $remote 远程文件名
     * @param string $local 本地保存文件名
     * @return mixed
     */
    static public function curlDownload($remote,$local) {
        $cp = curl_init($remote);
        $fp = fopen($local,"w");
        curl_setopt($cp, CURLOPT_FILE, $fp);
        curl_setopt($cp, CURLOPT_HEADER, 0);
        curl_exec($cp);
        curl_close($cp);
        fclose($fp);
    }

    /**
     * 采集远程文件同步到vfs中    guo.hao 2014-12-30
     * @access public
     * @param string $remote 远程文件名
     * @param string $upload_dir 存储目录
     * @return mixed
     */
    static public function download2VFS($remote,$upload_dir='test_dir/',$thumb_data=array(),$thumb_type=array(THUMB_SCALING),$new_filename=''){
        //上传临时目录
        $temp_dir = C('UPLOAD_TEMP_DIR');
        if(false === is_dir($temp_dir)) {
            if(false === mkdir($temp_dir, 0777, true)) {
                echo C('ORG_UPLOAD_TEMP');
                return false;
            }
        }
        //下载
        $cp = curl_init($remote);
        $_origin_temp_file = tempnam($temp_dir, uniqid());
        $fp = fopen($_origin_temp_file,"w");
        curl_setopt($cp, CURLOPT_FILE, $fp);
        curl_setopt($cp, CURLOPT_HEADER, 0);
        curl_exec($cp);
        curl_close($cp);
        fclose($fp);
        //获取文件信息
        $info = pathinfo($remote);
        $ext = $info['extension'];
        if($new_filename==''){
            $filename = $upload_dir . uniqid(). '.' . $ext;
        }else{
            $filename = $upload_dir . $new_filename. '.' . $ext;
        }
        //乐居vfs使用rsync同步文件
        import('ORG.SINA.LejuVfs');
        $vfs = LejuVfs::getInstance();
        $ret = $vfs->save($_origin_temp_file, $filename, true);
        // 处理返回结果
        if(false === $ret) {
            echo C('ORG_UPLOAD_ERROR');
            return false;
        } else {
            if(!empty($thumb_data)){
                self::thumb2VFS($_origin_temp_file,$filename,$thumb_data,$thumb_type);
            }
            return $filename;
        }
    }

    /**
     * 将抓取的图片进行缩略、水印
     * @access public
     * @param string $filename 临时文件
     * @param string $real_filename 同步到vfs后的文件名
     * @param string $thumb_data 缩略参数
     * @param string $default_thumb_type 默认的缩略类型
     * @return mixed
     */
    static public function thumb2VFS($filename,$real_filename,$thumb_data,$default_thumb_type=array(THUMB_SCALING)){
        //上传临时目录
        $temp_dir = C('UPLOAD_TEMP_DIR');
        if(false === is_dir($temp_dir)) {
            if(false === mkdir($temp_dir, 0777, true)) {
                echo C('ORG_UPLOAD_TEMP');
                return false;
            }
        }
        import('ORG.Util.Image.ThinkImage');
        @require_once(WEB_ROOT."ThinkPHP/Extend/Library/ORG/Util/Image/ThinkImage.class.php");
        $thinkimage = new ThinkImage(THINKIMAGE_MAGICKWAND);
        $thumb_info = array();
        foreach($thumb_data as $key=>$data) {
            $_thumb_temp_file = tempnam($temp_dir, uniqid());
            $_src_thumb_temp_file = $filename;
            $image_resource = $thinkimage->open($_src_thumb_temp_file);
            if(isset($data['mode']) && is_array($data['mode'])) {
                foreach($data['mode'] as $thumb_type) {
                    $image_resource->thumb($data['width'], $data['height'], $thumb_type);
                }
            } else {
                foreach($default_thumb_type as $thumb_type) {
                    $image_resource->thumb($data['width'], $data['height'], $thumb_type);
                }
            }
            $image_resource->save($_thumb_temp_file);
            $_temp_file_path = $_thumb_temp_file;   //最后使用的临时文件
            //判断是否添加水印
            if($data['water_mark']){    //添加水印；传水印图地址
                $image_resource2 = $thinkimage->open($_thumb_temp_file);    //创建水印图资源
                $_thumb_temp_file2 = tempnam($temp_dir, uniqid());  //返回的是地址
                $image_resource2->water($data['water_mark'], 9, 0)->save($_thumb_temp_file2);   //默认水印图放到右下角
                $_temp_file_path = $_thumb_temp_file2;
            }

            $suffix	= isset($data['suffix']) ? $data['suffix'] : "_{$data['width']}x{$data['height']}";
            //获取文件信息
            $info = pathinfo($real_filename);
            $base_name = explode('.',$info['basename']);
            $base_name = $base_name[0];
            $thumb_filename = $info['dirname'].'/' .$base_name  . $suffix . '.' . $info['extension'];
            //乐居vfs使用rsync同步文件
            import('ORG.SINA.LejuVfs');
            $vfs = LejuVfs::getInstance();
            $ret = $vfs->save($_temp_file_path, $thumb_filename, true);

            if(false === $ret) return false;
            $thumb_info['w'.$data['width'].'h'.$data['height']] = array(
                'src'   => $thumb_filename,
                'width' => intval($thinkimage->width()),
                'height'=> intval($thinkimage->height()),
                'size'	=> filesize($_temp_file_path),
                'ext'   => $thinkimage->type(),
            );
            @unlink($_thumb_temp_file);
            isset($_thumb_temp_file2) && @unlink($_thumb_temp_file2);
        }
        return $thumb_info;
    }

    /**
     * 使用 fsockopen 通过 HTTP 协议直接访问(采集)远程文件
     * 如果主机或服务器没有开启 CURL 扩展可考虑使用
     * fsockopen 比 CURL 稍慢,但性能稳定
     * @static
     * @access public
     * @param string $url 远程URL
     * @param array $conf 其他配置信息
     *        int   limit 分段读取字符个数
     *        string post  post的内容,字符串或数组,key=value&形式
     *        string cookie 携带cookie访问,该参数是cookie内容
     *        string ip    如果该参数传入,$url将不被使用,ip访问优先
     *        int    timeout 采集超时时间
     *        bool   block 是否阻塞访问,默认为true
     * @return mixed
     */
    static public function fsockopenDownload($url, $conf = array()) {
        $return = '';
        if(!is_array($conf)) return $return;

        $matches = parse_url($url);
        !isset($matches['host']) 	&& $matches['host'] 	= '';
        !isset($matches['path']) 	&& $matches['path'] 	= '';
        !isset($matches['query']) 	&& $matches['query'] 	= '';
        !isset($matches['port']) 	&& $matches['port'] 	= '';
        $host = $matches['host'];
        $path = $matches['path'] ? $matches['path'].($matches['query'] ? '?'.$matches['query'] : '') : '/';
        $port = !empty($matches['port']) ? $matches['port'] : 80;

        $conf_arr = array(
            'limit'		=>	0,
            'post'		=>	'',
            'cookie'	=>	'',
            'ip'		=>	'',
            'timeout'	=>	15,
            'block'		=>	TRUE,
        );

        foreach (array_merge($conf_arr, $conf) as $k=>$v) ${$k} = $v;

        if($post) {
            if(is_array($post))
            {
                $post = http_build_query($post);
            }
            $out  = "POST $path HTTP/1.0\r\n";
            $out .= "Accept: */*\r\n";
            //$out .= "Referer: $boardurl\r\n";
            $out .= "Accept-Language: zh-cn\r\n";
            $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
            $out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
            $out .= "Host: $host\r\n";
            $out .= 'Content-Length: '.strlen($post)."\r\n";
            $out .= "Connection: Close\r\n";
            $out .= "Cache-Control: no-cache\r\n";
            $out .= "Cookie: $cookie\r\n\r\n";
            $out .= $post;
        } else {
            $out  = "GET $path HTTP/1.0\r\n";
            $out .= "Accept: */*\r\n";
            //$out .= "Referer: $boardurl\r\n";
            $out .= "Accept-Language: zh-cn\r\n";
            $out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
            $out .= "Host: $host\r\n";
            $out .= "Connection: Close\r\n";
            $out .= "Cookie: $cookie\r\n\r\n";
        }
        $fp = @fsockopen(($ip ? $ip : $host), $port, $errno, $errstr, $timeout);
        if(!$fp) {
            return '';
        } else {
            stream_set_blocking($fp, $block);
            stream_set_timeout($fp, $timeout);
            @fwrite($fp, $out);
            $status = stream_get_meta_data($fp);
            if(!$status['timed_out']) {
                while (!feof($fp)) {
                    if(($header = @fgets($fp)) && ($header == "\r\n" ||  $header == "\n")) {
                        break;
                    }
                }

                $stop = false;
                while(!feof($fp) && !$stop) {
                    $data = fread($fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit));
                    $return .= $data;
                    if($limit) {
                        $limit -= strlen($data);
                        $stop = $limit <= 0;
                    }
                }
            }
            @fclose($fp);
            return $return;
        }
    }

    /**
     * 下载文件
     * 可以指定下载显示的文件名，并自动发送相应的Header信息
     * 如果指定了content参数，则下载该参数的内容
     * @static
     * @access public
     * @param string $filename 下载文件名
     * @param string $showname 下载显示的文件名
     * @param string $content  下载的内容
     * @param integer $expire  下载内容浏览器缓存时间
     * @return void
     */
    static public function download ($filename, $showname='',$content='',$expire=180) {
        if(is_file($filename)) {
            $length = filesize($filename);
        }elseif(is_file(UPLOAD_PATH.$filename)) {
            $filename = UPLOAD_PATH.$filename;
            $length = filesize($filename);
        }elseif($content != '') {
            $length = strlen($content);
        }else {
            throw_exception($filename.L('下载文件不存在！'));
        }
        if(empty($showname)) {
            $showname = $filename;
        }
        $showname = basename($showname);
        if(!empty($filename)) {
            $type = mime_content_type($filename);
        }else{
            $type	 =	 "application/octet-stream";
        }
        //发送Http Header信息 开始下载
        header("Pragma: public");
        header("Cache-control: max-age=".$expire);
        //header('Cache-Control: no-store, no-cache, must-revalidate');
        header("Expires: " . gmdate("D, d M Y H:i:s",time()+$expire) . "GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s",time()) . "GMT");
        header("Content-Disposition: attachment; filename=".$showname);
        header("Content-Length: ".$length);
        header("Content-type: ".$type);
        header('Content-Encoding: none');
        header("Content-Transfer-Encoding: binary" );
        if($content == '' ) {
            readfile($filename);
        }else {
            echo($content);
        }
        exit();
    }

    /**
     * 显示HTTP Header 信息
     * @return string
     */
    static function getHeaderInfo($header='',$echo=true) {
        ob_start();
        $headers   	= getallheaders();
        if(!empty($header)) {
            $info 	= $headers[$header];
            echo($header.':'.$info."\n"); ;
        }else {
            foreach($headers as $key=>$val) {
                echo("$key:$val\n");
            }
        }
        $output 	= ob_get_clean();
        if ($echo) {
            echo (nl2br($output));
        }else {
            return $output;
        }

    }

    /**
     * HTTP Protocol defined status codes
     * @param int $num
     */
    static function sendHttpStatus($code) {
        static $_status = array(
            // Informational 1xx
            100 => 'Continue',
            101 => 'Switching Protocols',

            // Success 2xx
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',

            // Redirection 3xx
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',  // 1.1
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            // 306 is deprecated but reserved
            307 => 'Temporary Redirect',

            // Client Error 4xx
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',

            // Server Error 5xx
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            509 => 'Bandwidth Limit Exceeded'
        );
        if(isset($_status[$code])) {
            header('HTTP/1.1 '.$code.' '.$_status[$code]);
        }
    }
}//类定义结束
if( !function_exists ('mime_content_type')) {
    /**
     * 获取文件的mime_content类型
     * @return string
     */
    function mime_content_type($filename) {
        static $contentType = array(
            'ai'		=> 'application/postscript',
            'aif'		=> 'audio/x-aiff',
            'aifc'		=> 'audio/x-aiff',
            'aiff'		=> 'audio/x-aiff',
            'asc'		=> 'application/pgp', //changed by skwashd - was text/plain
            'asf'		=> 'video/x-ms-asf',
            'asx'		=> 'video/x-ms-asf',
            'au'		=> 'audio/basic',
            'avi'		=> 'video/x-msvideo',
            'bcpio'		=> 'application/x-bcpio',
            'bin'		=> 'application/octet-stream',
            'bmp'		=> 'image/bmp',
            'c'			=> 'text/plain', // or 'text/x-csrc', //added by skwashd
            'cc'		=> 'text/plain', // or 'text/x-c++src', //added by skwashd
            'cs'		=> 'text/plain', //added by skwashd - for C# src
            'cpp'		=> 'text/x-c++src', //added by skwashd
            'cxx'		=> 'text/x-c++src', //added by skwashd
            'cdf'		=> 'application/x-netcdf',
            'class'		=> 'application/octet-stream',//secure but application/java-class is correct
            'com'		=> 'application/octet-stream',//added by skwashd
            'cpio'		=> 'application/x-cpio',
            'cpt'		=> 'application/mac-compactpro',
            'csh'		=> 'application/x-csh',
            'css'		=> 'text/css',
            'csv'		=> 'text/comma-separated-values',//added by skwashd
            'dcr'		=> 'application/x-director',
            'diff'		=> 'text/diff',
            'dir'		=> 'application/x-director',
            'dll'		=> 'application/octet-stream',
            'dms'		=> 'application/octet-stream',
            'doc'		=> 'application/msword',
            'dot'		=> 'application/msword',//added by skwashd
            'dvi'		=> 'application/x-dvi',
            'dxr'		=> 'application/x-director',
            'eps'		=> 'application/postscript',
            'etx'		=> 'text/x-setext',
            'exe'		=> 'application/octet-stream',
            'ez'		=> 'application/andrew-inset',
            'gif'		=> 'image/gif',
            'gtar'		=> 'application/x-gtar',
            'gz'		=> 'application/x-gzip',
            'h'			=> 'text/plain', // or 'text/x-chdr',//added by skwashd
            'h++'		=> 'text/plain', // or 'text/x-c++hdr', //added by skwashd
            'hh'		=> 'text/plain', // or 'text/x-c++hdr', //added by skwashd
            'hpp'		=> 'text/plain', // or 'text/x-c++hdr', //added by skwashd
            'hxx'		=> 'text/plain', // or 'text/x-c++hdr', //added by skwashd
            'hdf'		=> 'application/x-hdf',
            'hqx'		=> 'application/mac-binhex40',
            'htm'		=> 'text/html',
            'html'		=> 'text/html',
            'ice'		=> 'x-conference/x-cooltalk',
            'ics'		=> 'text/calendar',
            'ief'		=> 'image/ief',
            'ifb'		=> 'text/calendar',
            'iges'		=> 'model/iges',
            'igs'		=> 'model/iges',
            'jar'		=> 'application/x-jar', //added by skwashd - alternative mime type
            'java'		=> 'text/x-java-source', //added by skwashd
            'jpe'		=> 'image/jpeg',
            'jpeg'		=> 'image/jpeg',
            'jpg'		=> 'image/jpeg',
            'js'		=> 'application/x-javascript',
            'kar'		=> 'audio/midi',
            'latex'		=> 'application/x-latex',
            'lha'		=> 'application/octet-stream',
            'log'		=> 'text/plain',
            'lzh'		=> 'application/octet-stream',
            'm3u'		=> 'audio/x-mpegurl',
            'man'		=> 'application/x-troff-man',
            'me'		=> 'application/x-troff-me',
            'mesh'		=> 'model/mesh',
            'mid'		=> 'audio/midi',
            'midi'		=> 'audio/midi',
            'mif'		=> 'application/vnd.mif',
            'mov'		=> 'video/quicktime',
            'movie'		=> 'video/x-sgi-movie',
            'mp2'		=> 'audio/mpeg',
            'mp3'		=> 'audio/mpeg',
            'mpe'		=> 'video/mpeg',
            'mpeg'		=> 'video/mpeg',
            'mpg'		=> 'video/mpeg',
            'mpga'		=> 'audio/mpeg',
            'ms'		=> 'application/x-troff-ms',
            'msh'		=> 'model/mesh',
            'mxu'		=> 'video/vnd.mpegurl',
            'nc'		=> 'application/x-netcdf',
            'oda'		=> 'application/oda',
            'patch'		=> 'text/diff',
            'pbm'		=> 'image/x-portable-bitmap',
            'pdb'		=> 'chemical/x-pdb',
            'pdf'		=> 'application/pdf',
            'pgm'		=> 'image/x-portable-graymap',
            'pgn'		=> 'application/x-chess-pgn',
            'pgp'		=> 'application/pgp',//added by skwashd
            'php'		=> 'application/x-httpd-php',
            'php3'		=> 'application/x-httpd-php3',
            'pl'		=> 'application/x-perl',
            'pm'		=> 'application/x-perl',
            'png'		=> 'image/png',
            'pnm'		=> 'image/x-portable-anymap',
            'po'		=> 'text/plain',
            'ppm'		=> 'image/x-portable-pixmap',
            'ppt'		=> 'application/vnd.ms-powerpoint',
            'ps'		=> 'application/postscript',
            'qt'		=> 'video/quicktime',
            'ra'		=> 'audio/x-realaudio',
            'rar'		=> 'application/octet-stream',
            'ram'		=> 'audio/x-pn-realaudio',
            'ras'		=> 'image/x-cmu-raster',
            'rgb'		=> 'image/x-rgb',
            'rm'		=> 'audio/x-pn-realaudio',
            'roff'		=> 'application/x-troff',
            'rpm'		=> 'audio/x-pn-realaudio-plugin',
            'rtf'		=> 'text/rtf',
            'rtx'		=> 'text/richtext',
            'sgm'		=> 'text/sgml',
            'sgml'		=> 'text/sgml',
            'sh'		=> 'application/x-sh',
            'shar'		=> 'application/x-shar',
            'shtml'		=> 'text/html',
            'silo'		=> 'model/mesh',
            'sit'		=> 'application/x-stuffit',
            'skd'		=> 'application/x-koan',
            'skm'		=> 'application/x-koan',
            'skp'		=> 'application/x-koan',
            'skt'		=> 'application/x-koan',
            'smi'		=> 'application/smil',
            'smil'		=> 'application/smil',
            'snd'		=> 'audio/basic',
            'so'		=> 'application/octet-stream',
            'spl'		=> 'application/x-futuresplash',
            'src'		=> 'application/x-wais-source',
            'stc'		=> 'application/vnd.sun.xml.calc.template',
            'std'		=> 'application/vnd.sun.xml.draw.template',
            'sti'		=> 'application/vnd.sun.xml.impress.template',
            'stw'		=> 'application/vnd.sun.xml.writer.template',
            'sv4cpio'	=> 'application/x-sv4cpio',
            'sv4crc'	=> 'application/x-sv4crc',
            'swf'		=> 'application/x-shockwave-flash',
            'sxc'		=> 'application/vnd.sun.xml.calc',
            'sxd'		=> 'application/vnd.sun.xml.draw',
            'sxg'		=> 'application/vnd.sun.xml.writer.global',
            'sxi'		=> 'application/vnd.sun.xml.impress',
            'sxm'		=> 'application/vnd.sun.xml.math',
            'sxw'		=> 'application/vnd.sun.xml.writer',
            't'			=> 'application/x-troff',
            'tar'		=> 'application/x-tar',
            'tcl'		=> 'application/x-tcl',
            'tex'		=> 'application/x-tex',
            'texi'		=> 'application/x-texinfo',
            'texinfo'	=> 'application/x-texinfo',
            'tgz'		=> 'application/x-gtar',
            'tif'		=> 'image/tiff',
            'tiff'		=> 'image/tiff',
            'tr'		=> 'application/x-troff',
            'tsv'		=> 'text/tab-separated-values',
            'txt'		=> 'text/plain',
            'ustar'		=> 'application/x-ustar',
            'vbs'		=> 'text/plain', //added by skwashd - for obvious reasons
            'vcd'		=> 'application/x-cdlink',
            'vcf'		=> 'text/x-vcard',
            'vcs'		=> 'text/calendar',
            'vfb'		=> 'text/calendar',
            'vrml'		=> 'model/vrml',
            'vsd'		=> 'application/vnd.visio',
            'wav'		=> 'audio/x-wav',
            'wax'		=> 'audio/x-ms-wax',
            'wbmp'		=> 'image/vnd.wap.wbmp',
            'wbxml'		=> 'application/vnd.wap.wbxml',
            'wm'		=> 'video/x-ms-wm',
            'wma'		=> 'audio/x-ms-wma',
            'wmd'		=> 'application/x-ms-wmd',
            'wml'		=> 'text/vnd.wap.wml',
            'wmlc'		=> 'application/vnd.wap.wmlc',
            'wmls'		=> 'text/vnd.wap.wmlscript',
            'wmlsc'		=> 'application/vnd.wap.wmlscriptc',
            'wmv'		=> 'video/x-ms-wmv',
            'wmx'		=> 'video/x-ms-wmx',
            'wmz'		=> 'application/x-ms-wmz',
            'wrl'		=> 'model/vrml',
            'wvx'		=> 'video/x-ms-wvx',
            'xbm'		=> 'image/x-xbitmap',
            'xht'		=> 'application/xhtml+xml',
            'xhtml'		=> 'application/xhtml+xml',
            'xls'		=> 'application/vnd.ms-excel',
            'xlt'		=> 'application/vnd.ms-excel',
            'xml'		=> 'application/xml',
            'xpm'		=> 'image/x-xpixmap',
            'xsl'		=> 'text/xml',
            'xwd'		=> 'image/x-xwindowdump',
            'xyz'		=> 'chemical/x-xyz',
            'z'			=> 'application/x-compress',
            'zip'		=> 'application/zip',
        );
        $type = strtolower(substr(strrchr($filename, '.'),1));
        if(isset($contentType[$type])) {
            $mime = $contentType[$type];
        }else {
            $mime = 'application/octet-stream';
        }
        return $mime;
    }
}

if(!function_exists('image_type_to_extension')){
    function image_type_to_extension($imagetype) {
        if(empty($imagetype)) return false;
        switch($imagetype) {
            case IMAGETYPE_GIF    	: return '.gif';
            case IMAGETYPE_JPEG		: return '.jpg';
            case IMAGETYPE_PNG    	: return '.png';
            case IMAGETYPE_SWF    	: return '.swf';
            case IMAGETYPE_PSD    	: return '.psd';
            case IMAGETYPE_BMP    	: return '.bmp';
            case IMAGETYPE_TIFF_II 	: return '.tiff';
            case IMAGETYPE_TIFF_MM 	: return '.tiff';
            case IMAGETYPE_JPC    	: return '.jpc';
            case IMAGETYPE_JP2    	: return '.jp2';
            case IMAGETYPE_JPX    	: return '.jpf';
            case IMAGETYPE_JB2    	: return '.jb2';
            case IMAGETYPE_SWC    	: return '.swc';
            case IMAGETYPE_IFF    	: return '.aiff';
            case IMAGETYPE_WBMP    	: return '.wbmp';
            case IMAGETYPE_XBM    	: return '.xbm';
            default                	: return false;
        }
    }

}