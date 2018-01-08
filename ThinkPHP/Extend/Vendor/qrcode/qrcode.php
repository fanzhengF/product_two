<?php
/**
 * qrcode
 * @author xinhua@leju.sina.com.cn
 */
class Plugin_Qrcode 
{
    public function __construct()
    {
    	
    }
    
    /**
     * 
     * 生成二维码
     */
    public function make_qrcode($data, $filename = FALSE, $level = 'L', $size = '3', $out_Frame = 0,$saveandprint = FALSE)
    {
		if (empty($data) === TRUE || !is_string($data))
		{
			return FALSE;	
		}
		
    	include_once("phpqrcode/qrlib.php");  		
		QRcode::png($data, $filename, $level, $size, $out_Frame, $saveandprint);   
    }
}

?>