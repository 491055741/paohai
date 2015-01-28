<?php
namespace Wechat\Service;

include_once(__DIR__ . "/../../../../Wxpay/view/wxpay/wxpay/WxPayPubHelper/WxPayPubHelper.php");

use Postcard\Service\AbstractService;

class OauthService extends AbstractService
{
    const SNSAPI_BASE = "snsapi_base";
    const SNSAPI_USERINFO = "snsapi_userinfo";

    
    /**
     * @param string $redirectUri 用户授权后，微信回调地址。调用方式为：
     *              redirect_uri/?code=CODE&state=STATE
     * @param string $redirectUri 回调时，微信会将该参数透传给回调地址，对应state参数
     * @param string $scope 授权类型。
     *              self::SNSAPI_BASE 静默授权，用户无感知，仅获取openid
     *              self::SNSAPI_USERINFO 需要用户手动同意，获取用户基本信息
     *
     * @return string $oauthUrl
     */
    public function getOauthUrl($redirectUri, $scope=self::SNSAPI_BASE) {
        $uri = $this->getRequest()->getUri();
        $oauthCallbackUrl = "http://" . $uri->getHost() . "/oauth/callback";
        $jsApi = new \JsApi_pub();
        $url = $jsApi->createOauthUrlForCode(
            urlencode($oauthCallbackUrl), urlencode($redirectUri)
        );
        return $url;
    }


    /**
     * 授权回调处理，根据code换取openid及access_token (与常规access_token不同)
     *
     * @param string $code
     * @param string $state 获取用户openid后，跳转到该地址
     *
     * @return string $redirectUri 
     */
    public function OauthCallback($code, $state) {
        $url = $state;

        $jsApi = new \JsApi_pub();
        $jsApi->setCode($code);
        $openId = $jsApi->getOpenId();
        if ( ! $openId) {
            return NULL;
        }

        list($url, $fragment) = explode("#", $url);
        $url = rtrim($url, "?");
        $url = parse_url($url, PHP_URL_QUERY) ?
            $url . "&userName=" . $openId :
            $url . "?userName=" . $openId;
        if ($fragment) {
            $url .= "#" . $fragment;
        }

        return $url;
    }
}

/* End of file */
