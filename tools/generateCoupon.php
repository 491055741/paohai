<?php
date_default_timezone_set('Asia/Shanghai');
$conn = mysql_connect("localhost", "root", "");
mysql_query("set names 'utf8'");
mysql_select_db("paohai_postcard");

$count = 5000;

$year = 2015;
$month = 8;
$day = 30;

$hour = 23;
$minute = 59;
$second = 59;
$expiredAt = mktime($hour, $minute, $second, $month, $day, $year);
$price = 299; //单位是分
$status = 0; // 0 not use, // 1 have been used;

$codeLength = 6;

$numbers = range(0, 9);
while ($count > 0) {
    $temp = [];
    for($i = 0; $i < $codeLength; $i++){
        $temp[] = array_rand($numbers);
    }

    $code = join("", $temp);
    $sql = "insert into coupon(code, expiredAt, price, status) values('$code', $expiredAt, $price, $status)";
    if (mysql_query($sql, $conn)) {
        echo $code."\r\n";
        $count--;
    }
}

mysql_close($conn);

// how to use : php tools/generateCoupon.php>coupon.txt