<?php
/**
* 	配置账号信息
*/
define('_SSLCERT_PATH', dirname(__FILE__)."/cacert/apiclient_cert.pem");
define('_SSLKEY_PATH', dirname(__FILE__).'/cacert/apiclient_key.pem');
//define('_ADDR_URL', 'http://'.$_SERVER['SERVER_NAME'].'/wxpay/address');
//define('_PREVIEW_URL', 'http://'.$_SERVER['SERVER_NAME'].'/wxpay/preview');

class WxPayConf_pub
{
	//=======【基本信息设置】=====================================
	//微信公众号身份的唯一标识。审核通过后，在微信发送的邮件中查看
	const APPID = 'wxbd6694a085209f4d';
	//受理商ID，身份标识
	const MCHID = '10022343';
	//商户支付密钥Key。审核通过后，在微信发送的邮件中查看
	const KEY = 'quyoumingxinpianquyoumingxinpian';//'60ff84ad83b44a3d8500cbd2ba7e44bf';
	//JSAPI接口中获取openid，审核后在公众平台开启开发模式后可查看
	const APPSECRET = '7987f43d80ff0b77b1f966fc52b17ae5';

	//=======【JSAPI路径设置】===================================
	//获取access_token过程中的跳转uri，通过跳转将code传入jsapi支付页面
//    const JS_API_CALL_ADDR_URL = _ADDR_URL;
//    const JS_API_CALL_PREVIEW_URL = _PREVIEW_URL;

	//=======【证书路径设置】=====================================
	//证书路径,注意应该填写绝对路径
	const SSLCERT_PATH = _SSLCERT_PATH;
	const SSLKEY_PATH = _SSLKEY_PATH;

	//=======【异步通知url设置】===================================
	//异步通知url，商户根据实际开发过程设定
	const NOTIFY_URL = 'http://quyou.ikamobile.com/wxpay/result';

	//=======【curl超时设置】===================================
	//本例程通过curl使用HTTP POST方法，此处可修改其超时时间，默认为30秒
	const CURL_TIMEOUT = 30;

    const ACCESS_TOKEN_KEY = "accessToken";
    const TOKEN_EXPIRE_TIME_KEY = "tokenExpireTime";
    const ACCESS_TOKEN_EXPIRES = 7000;
    const CUR_QR_SCENE_ID_KEY  = "curQrSceneId";
}
	
?>
