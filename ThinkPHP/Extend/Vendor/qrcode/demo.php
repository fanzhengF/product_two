<?php
include './qrcode.php';
$qrcode = new Plugin_Qrcode();
$pay_sn = '1234567890';
header("Content-type: image/png");
$qrcode->make_qrcode("$pay_sn", FALSE, 'L', '3', 5);