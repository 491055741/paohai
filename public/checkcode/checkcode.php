<?php
ini_set("display_errors", true);

if (!isset($_GET['date'])) {
    $array = array('code' => 1, 'msg' => 'need date para! for example: date=20140721');
    echo json_encode($array);
    return;
}

$filename = './'.$_GET['date'].'.data';
if (!file_exists($filename)) {
    $array = array('code' => 1, 'msg' => 'file '.$filename.' not exist!');
    echo json_encode($array);
    return;
}

$array = array(
            'code' => 0,
            'deviceNumber' => '你好1234567890abcdefg',
            'checkCode'    => file_get_contents($filename));

/**************************************************************
*
*  使用特定function对数组中所有元素做处理
*  @param  string  &$array     要处理的字符串
*  @param  string  $function   要执行的函数
*  @return boolean $apply_to_keys_also     是否也应用到key上
*  @access public
*
*************************************************************/
function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
{
    static $recursive_counter = 0;
    if (++$recursive_counter > 1000) {
        die('possible deep recursion attack');
    }
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            arrayRecursive($array[$key], $function, $apply_to_keys_also);
        } else {
            $array[$key] = $function($value);
        }

        if ($apply_to_keys_also && is_string($key)) {
            $new_key = $function($key);
            if ($new_key != $key) {
                $array[$new_key] = $array[$key];
                unset($array[$key]);
            }
        }
    }
   $recursive_counter--;
}

/**************************************************************
*
*  将数组转换为JSON字符串（兼容中文）
*  @param  array   $array      要转换的数组
*  @return string      转换得到的json字符串
*  @access public
*
*************************************************************/
function JSON($array) {
    arrayRecursive($array, 'urlencode', true);
    $json = json_encode($array);
    return urldecode($json);
}

?>

<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
</head>
<body>
    <?php  echo JSON($array); ?>
</body>
</html>


