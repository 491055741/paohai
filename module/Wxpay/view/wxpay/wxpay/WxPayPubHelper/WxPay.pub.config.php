<?php
/**
* 	配置账号信息
*/
define('_SSLCERT_PATH', dirname(__FILE__)."/cacert/apiclient_cert.pem");
define('_SSLKEY_PATH', dirname(__FILE__).'/cacert/apiclient_key.pem');

class WxPayConf_pub
{
	//=======【基本信息设置】=====================================
	//商户支付密钥Key。审核通过后，在微信发送的邮件中查看
	const KEY = 'quyoumingxinpianquyoumingxinpian';

	//=======【JSAPI路径设置】===================================
	//获取access_token过程中的跳转uri，通过跳转将code传入jsapi支付页面
//    const JS_API_CALL_ADDR_URL = _ADDR_URL;
//    const JS_API_CALL_PREVIEW_URL = _PREVIEW_URL;

	//=======【证书路径设置】=====================================
	//证书路径,注意应该填写绝对路径
	const SSLCERT_PATH = _SSLCERT_PATH;
	const SSLKEY_PATH = _SSLKEY_PATH;

	//=======【curl超时设置】===================================
	//本例程通过curl使用HTTP POST方法，此处可修改其超时时间，默认为30秒
	const CURL_TIMEOUT = 30;

    const ACCESS_TOKEN_KEY      = "accessToken";
    const JSAPI_TICKET_KEY      = "jsapiTicket";
    const TOKEN_EXPIRE_TIME_KEY = "tokenExpireTime";
    const JSAPI_TICKET_EXPIRE_TIME_KEY = "jsapiTicketExpireTime";
    const CUR_QR_SCENE_ID_KEY   = "curQrSceneId";

    static public function appId()
    {
        if (stripos($_SERVER['SERVER_NAME'], 'quyou') !== false) {
            return 'wxbd6694a085209f4d';
        } else if (stripos($_SERVER['SERVER_NAME'], 'paohai') !== false) {
            return 'wx4a41ea3d983b4538';
        } else
            return 'wrongAppId';
    }

    static public function appSecret()
    {
        if (stripos($_SERVER['SERVER_NAME'], 'quyou') !== false) {
            return '7987f43d80ff0b77b1f966fc52b17ae5';
        } else if (stripos($_SERVER['SERVER_NAME'], 'paohai') !== false) {
            return '424b9f967e50a2711460df2a9c9efaaa';
        } else
            return 'wrongAppSecret';
    }

    static public function mchId()
    {
        if (stripos($_SERVER['SERVER_NAME'], 'quyou') !== false) {
            return '10022343';
        } else if (stripos($_SERVER['SERVER_NAME'], 'paohai') !== false) {
            return '1219350001';
        } else
            return 'wrongMchId';
    }

    static public function notifyUrl()
    {
        return 'http://'.$_SERVER['HTTP_HOST'].'/wxpay/result';
    }
}