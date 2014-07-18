<?php
ini_set("display_errors", true);

if (!isset($_GET['challenge'])) {
    $array = array('code' => 1, 'msg' => 'need challenge para! for example: challenge=4084nhd34iq9brb2uvpbhpp21d%2B18.542-8.464-12.591-28.283-32.916-38.088-18.498-33.868-5.72-40.204-5.185-26.933');
    echo json_encode($array);
    return;
}

$wLChallengeData = escapeshellcmd($_GET['challenge']); 
// $wLChallengeData = '4084nhd34iq9brb2uvpbhpp21d+18.542-8.464-12.591-28.283-32.916-38.088-18.498-33.868-5.72-40.204-5.185-26.933';
$cmd = 'export DYLD_LIBRARY_PATH=""; java -classpath ./WlAuthenticityRealmForMobile.jar:./ wlauth '. $wLChallengeData .' 2>&1';
$res = exec($cmd);
$array = array(
            'code' => 0,
            'authRealm' => $res);
echo JSON($array);

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

function JSON($array) {
    arrayRecursive($array, 'urlencode', true);
    $json = json_encode($array);
    return urldecode($json);
}


?>


