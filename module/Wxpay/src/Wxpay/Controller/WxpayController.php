<?php
namespace Wxpay\Controller;

include_once(dirname(__FILE__)."/../../../view/wxpay/wxpay/WxPayHelper.php");

use WxPayHelper;
use CommonUtil;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

ini_set("display_errors", true);

class WxpayController extends AbstractActionController
{
    public function payAction()
    {
        $orderId = $this->params()->fromRoute('id', '0');
        $bank = $this->getRequest()->getQuery('bank', 'other');
        $para = array(
            'total_fee'   => ($bank == 'XingYe' ? 1 : 5), // 100, 500
            'order_id'    => $orderId,
        );

        $viewModel = new ViewModel($para);
        $viewModel->setTerminal(true); // disable layout template
        return $viewModel;
    }

/* function resultAction()
    支付通知 notify_url 处理  参见http://www.cnblogs.com/txw1958/p/weixin-pay-trade-notice.html
 */
    public function resultAction()
    {

        $postStr = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : file_get_contents("php://input");
        $this->logger(json_encode($_GET).'  '.json_encode($postStr));

        $trade_state = $this->getRequest()->getQuery('trade_state', 1);
        if ($trade_state == 0) {    // pay success

            $out_trade_no = $this->getRequest()->getQuery('out_trade_no');
            $transId = $this->getRequest()->getQuery('transaction_id');

            
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $openId  = $postObj->OpenId;

            // update order status to 'payed'
            $url = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].'/postcard/changestatus/'.$out_trade_no.'/101';
            $html = file_get_contents($url);

            // copy postcard pictures to 'payed' folder
            $this->copyPicture($out_trade_no);

            // 假设这里直接自动发货成功，调用发货通知接口通知微信
            $this->deliverNotify(array('orderid' => $out_trade_no,
                                       'tansid' => $transId,
                                       'openid' => $openId,
                                        )
                                );
        }

        echo 'success'; // must respond 'success' to wxpay server
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true); // disable layout template
        return $viewModel;
    }

    public function testDeliverNotifyAction()
    {
        $data = array('orderid' => '14080598856',
                     'transid' => '1219350001201408053164276949',
                     'openid' => 'ocKsTuKbE4QqHbwGEXmVnuLHO_sY',
                      );
        $rc = $this->deliverNotify($data);

        $view =  new ViewModel(array('code' => $rc->errcode, 'msg' => $rc->errmsg));
        $view->setTemplate('postcard/postcard/error');
        return $view;
    }

    public function deliverNotify($data)
    {
        $util = new CommonUtil();
        $util->setServiceLocator($this->getServiceLocator());
        $access_token = $util->getAccessToken();
        $url = "https://api.weixin.qq.com/pay/delivernotify?access_token=".$access_token;

        $wxPayHelper = new WxPayHelper();
        $nativeObj['appid'] = APPID;
        $nativeObj['openid'] = $data['openid'];
        $nativeObj['transid'] = $data['transid'];
        $nativeObj['out_trade_no'] = $data['orderid'];
        $nativeObj['deliver_timestamp'] = $wxPayHelper->create_timestamp();
        $nativeObj['deliver_status'] = '1';
        $nativeObj['deliver_msg'] = 'ok';
        $nativeObj["app_signature"] = $wxPayHelper->get_biz_sign($nativeObj);
        $nativeObj["sign_method"] = SIGNTYPE;
        $postResult = json_decode($util->httpPost($url, json_encode($nativeObj)));

        return $postResult;
    }

    // pay test page. say 'pay' to paohai postcard in wechat, you will get the url of this page
    public function testAction()
    {
        $viewModel = new ViewModel();
        // $viewModel->setTerminal(true); // disable layout template
        return $viewModel;
    }

    private function logger($content)
    {
        file_put_contents($this->logFileName(), date('m/d H:i:s').' '.$content."\n", FILE_APPEND);
    }

    private function copyPicture($orderId)
    {
        $dstpath = $this->payedPicPath();
        if (!is_dir($dstpath)) {
            if (!mkdir($dstpath)) {
                echo 'Create folder '.$dstpath.' failed!';
                return false;
            }
        }

        if (!copy($this->postcardsPath().$orderId.'_front.png', $this->payedPicPath().$orderId.'_front.png')) {
            echo 'copy '.$this->postcardsPath().$orderId.'_front.png failed!';
            return false;
        }

        if (!copy($this->postcardsPath().$orderId.'_backface.png', $this->payedPicPath().$orderId.'_backface.png')) {
            echo 'copy '.$this->postcardsPath().$orderId.'_backface.png failed!';
            return false;
        }
        return true;
    }

    private function logFileName()
    {
        return dirname(__FILE__).'/../../../../../userdata/paohai_paying.log';
    }

    private function postcardsPath()
    {
        return dirname(__FILE__).'/../../../../../userdata/postcards/' . date('Ymd', time()) . '/';
    }

    private function payedPicPath()
    {
        return dirname(__FILE__).'/../../../../../userdata/payed/' . date('Ymd', time()) . '/';
    }


/*
post:
{"appid":"wx4a41ea3d983b4538","package":"out_trade_no=1406267508&partner=1219350001&sign=B6EE01B3B797C4AF6DBB730D2C92457A",
"timestamp":1404111513,"app_signature":"fbb20962110f73d7323688a077e52eacca84223e","sign_method":"sha1"}

respend:
{"errcode":0,"errmsg":"ok",
"order_info":{"ret_code":0,"ret_msg":"","input_charset":"GBK","trade_state":"0","trade_mode":"1","partner":"1219350001",
            "bank_type":"CMB_FP","bank_billno":"201406263490184758","total_fee":"1","fee_type":"1","transaction_id":"1219350001201406263237124432",
            "out_trade_no":"1406267508","is_split":"false","is_refund":"false","attach":"","time_end":"20140626171415","transport_fee":"0",
            "product_fee":"1","discount":"0","rmb_total_fee":""
            }
}
  */
    // query order pay info from tencent server
    public function orderQueryAction()
    {
        // https://api.weixin.qq.com/pay/orderquery?access_token=xxxxxx
        $orderId = $this->params()->fromRoute('id', '0');
        $wxPayHelper = new WxPayHelper();
        $wxPayHelper->setParameter("partner", PARTNERID);
        $wxPayHelper->setParameter("out_trade_no", $orderId);
        $postData = $wxPayHelper->create_order_query_package();
        // echo $postData;

        $util = new CommonUtil();
        $util->setServiceLocator($this->getServiceLocator());
        $access_token = $util->getAccessToken();
        $url = "https://api.weixin.qq.com/pay/orderquery?access_token=".$access_token;
        $postResult = json_decode($util->httpPost($url, $postData));
        // echo $postResult;
        echo '<br>errcode:';
        echo $postResult->errcode;
        echo '<br>errmsg:';
        echo $postResult->errmsg;
        if ($postResult->errcode == 0) {
            echo '<br>orderinfo:';
            var_dump($postResult->order_info);
        }

        $viewModel = new ViewModel();
        $viewModel->setTerminal(true); // disable layout template
        return $viewModel;
    }

    public function feedbackAction()
    {
        $postStr = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : file_get_contents("php://input");
        // echo $postStr;
        // extract post data

        // todo: 转发维权信息到客服邮箱
        if (!empty($postStr)) {

            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $OpenId     = $postObj->OpenId;
            $AppId      = $postObj->AppId;
            $TimeStamp  = $postObj->TimeStamp;
            $MsgType    = $postObj->MsgType;
            $FeedBackId = $postObj->FeedBackId;
            $Reason     = $postObj->Reason;
            $AppSignature = $postObj->AppSignature;
            $SignMethod = $postObj->SignMethod;

            if ($MsgType == 'request') {
                $TransId  = $postObj->TransId;
                $Solution = $postObj->Solution;
                $ExtInfo  = $postObj->ExtInfo;
            }

            echo $MsgType . ' ' . $Reason;
            // todo: picinfo
            /* 
            <PicInfo>
<item><PicUrl><![CDA T A[http://mmbiz.qpic.cn/mmbiz/49ogibiahRNtOk37iaztwmdgFbyFS9FU rqfodiaUAmxr4hOP34C6R4nGgebMalKuY3H35riaZ5vtzJh25tp7vBUwWxw/0]]></PicUrl> </item>
<item>
<PicUrl>
<![CDA T A[http://mmbiz.qpic.cn/mmbiz/49ogibiahRNtOk37iaztwmdgFbyFS9FUrqfn3y72eHKRS A wVz1PyIcUSjBrDzXAibTiaAdrTGb4eBFbib9ibFaSeic3OIg/0]]></PicUrl>
</item>
<item>
<PicUrl>
<![CDA T A[]]></PicUrl></item><item><PicUrl><![CDA T A[]]></PicUrl></item><item><PicUrl ><![CDA T A[]]></PicUrl></item></PicInfo>
*/
            // $ = $postObj->;
            // $ = $postObj->;
            // $ = $postObj->;
            // $ = $postObj->;
            // $ = $postObj->;

// 标记处理状态
// https://api.weixin.qq.com/payfeedback/update?access_token=xxxxx&openid=XXXX&feedbackid=xxxx

        }
        echo "success";
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true); // disable layout template
        return $viewModel;
    }

    public function alarmAction()
    {
// <xml>
// <AppId><![CDA T A[wxf8b4f85f3a794e77]]></AppId> <ErrorType>1001</ErrorType>
// <Description><![CDA T A[错误描述 ]]></Description> <AlarmContent><![CDA T A[错误详情 ]]></AlarmContent>
// <TimeStamp>1393860740</TimeStamp>
// <AppSignature><![CDA T A[f8164781a303f4d5a944a2dfc68411a8c7e4fbea]]></AppSignatur e>
// <SignMethod><![CDA T A[sha1]]></SignMethod> </xml>

        echo "success";
        // 通知商户管理员 包括发货延迟 、调用失败、通知失败等情况
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true); // disable layout template
        return $viewModel;
    }
}

