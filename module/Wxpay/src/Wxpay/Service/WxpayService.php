<?php
namespace Wxpay\Service;

use Postcard\Service\AbstractService;


class WxpayService extends AbstractService
{
    /**
     * Get wx payment pay parameters
     *
     * @param int $orderId
     *
     * @return array array($price, $payPara)
     */
    public function getPayPara($orderId) {
        include_once(__DIR__ . "/../../../view/wxpay/wxpay/WxPayPubHelper/js_api_call.php");

        $orderTable = $this->getServiceLocator()
            ->get('Postcard\Model\OrderTable');
        $order = $orderTable->getOrder($orderId);
        if ( ! $order) {
            throw new \Exception("order not exists");
        }
        if ($order->price == 0) {
            return array(0, '');
        }

        $redirectUri = \WXJsPay::JS_API_CALL_PREVIEW_URL;
        return array($order->price, \WXJsPay::getPayPara($redirectUri, $orderId, $order->price));
    }
}

/* End of file */
