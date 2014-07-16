
<?php
ini_set("display_errors", true);

if (!isset($_GET['challenge'])) {
    $array = array('code' => 1, 'msg' => 'need challenge para! for example: challenge=4084nhd34iq9brb2uvpbhpp21d+18.542-8.464-12.591-28.283-32.916-38.088-18.498-33.868-5.72-40.204-5.185-26.933');
    echo json_encode($array);
    return;
}

$wLChallengeData = escapeshellcmd($_GET['challenge']); 
// $wLChallengeData = '4084nhd34iq9brb2uvpbhpp21d+18.542-8.464-12.591-28.283-32.916-38.088-18.498-33.868-5.72-40.204-5.185-26.933';
$cmd = 'export DYLD_LIBRARY_PATH=""; java -classpath ./WlAuthenticityRealmForMobile.jar:./ wlauth '. $wLChallengeData .' 2>&1';
// echo $cmd;
echo exec($cmd);
