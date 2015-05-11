<?php
/**
 * JS_API支付demo
 * ====================================================
 * 在微信浏览器里面打开H5网页中执行JS调起支付。接口输入输出数据格式为JSON。
 * 成功调起支付需要三个步骤：
 * 步骤1：网页授权获取用户openid
 * 步骤2：使用统一支付接口，获取prepay_id
 * 步骤3：使用jsapi调起支付
*/
include_once("WxPayPubHelper.php");


define('_ADDR_URL', 'http://'.$_SERVER['SERVER_NAME'].'/wxpay/address');
define('_PREVIEW_URL', 'http://'.$_SERVER['SERVER_NAME'].'/client/index.html#/order');

class WXJsPay {

    //=======【JSAPI路径设置】===================================
    //获取access_token过程中的跳转uri，通过跳转将code传入jsapi支付页面
    const JS_API_CALL_ADDR_URL = _ADDR_URL;
    const JS_API_CALL_PREVIEW_URL = _PREVIEW_URL;

    static public function getPayPara($redirectUri, $orderId, $payPrice)
    {
        $openid = $_GET['openId'];

        //=========步骤2：使用统一支付接口，获取prepay_id============
        //使用统一支付接口
        $unifiedOrder = new UnifiedOrder_pub();


        //设置统一支付接口参数
        //设置必填参数
        //appid已填,商户无需重复填写
        //mch_id已填,商户无需重复填写
        //noncestr已填,商户无需重复填写
        //spbill_create_ip已填,商户无需重复填写
        //sign已填,商户无需重复填写
        $unifiedOrder->setParameter("openid","$openid");
        $unifiedOrder->setParameter("body","趣邮明信片");//商品描述
        $unifiedOrder->setParameter("out_trade_no","$orderId");//商户订单号
        $unifiedOrder->setParameter("total_fee",$payPrice);//总金额
        $unifiedOrder->setParameter("notify_url",WxPayConf_pub::notifyUrl());//通知地址
        $unifiedOrder->setParameter("trade_type","JSAPI");//交易类型
        //非必填参数，商户可根据实际情况选填
        //$unifiedOrder->setParameter("sub_mch_id","XXXX");//子商户号
        //$unifiedOrder->setParameter("device_info","XXX");//设备号
        //$unifiedOrder->setParameter("attach","XXXX");//附加数据
        //$unifiedOrder->setParameter("time_start","XXXX");//交易起始时间
        //$unifiedOrder->setParameter("time_expire","XXXX");//交易结束时间
        //$unifiedOrder->setParameter("goods_tag","XXXX");//商品标记
        //$unifiedOrder->setParameter("product_id","XXXX");//商品ID

        $prepay_id = $unifiedOrder->getPrepayId();
        if ($prepay_id != '') {
        //=========步骤3：使用jsapi调起支付============
            $jsApi = new JsApi_pub();
            $jsApi->setPrepayId($prepay_id);
            $jsApiParameters = $jsApi->getParameters();
            // echo $jsApiParameters;
        } else {
            $jsApiParameters = 'error';
        }

        logger($jsApiParameters);
        return $jsApiParameters;
    }
}
