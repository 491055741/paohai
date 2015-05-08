<?php
date_default_timezone_set('Asia/Shanghai');
$conn = mysql_connect("localhost", "root", "");
mysql_query("set names 'utf8'");
mysql_select_db("paohai_postcard");

$count = 10000;

$year = 2020;
$month = 1;
$day = 1;

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
    echo $code."\n";
    $sql = "insert into coupon(code, expiredAt, price, status) values('$code', $expiredAt, $price, $status)";
    if (mysql_query($sql, $conn)) {
        echo $code."\n";
        $count--;
    }
}

mysql_close($conn);