<?php
namespace Wxpay\Controller;

include_once(dirname(__FILE__)."/../../../view/wxpay/wxpay/WxPayHelper.php");

use WxPayHelper;
use CommonUtil;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

ini_set("display_errors", true);

// order status
define('CANCEL',   99); // 已取消
define('UNPAY',   100); // 待支付
define('PAYED',   101); // 已支付
define('PRINTED', 102); // 已打印
define('SHIPPED', 103); // 已发货

define('JS_TAG', '201409251150'); // 好像不管用，待查

class WxpayController extends AbstractActionController
{
    protected $orderTable;

    public function payAction()
    {
        $orderId = $this->getRequest()->getQuery('orderId', '0');
        $order = $this->getOrderTable()->getOrder($orderId);
        if ($orderId == '0' || !$order) {
            $view =  new ViewModel(array('code' => 1, 'msg' => 'invalid order id: '.$orderId));
            $view->setTemplate('postcard/postcard/error');
            return $view;
        }

        $util = new CommonUtil();
        $util->httpGet('http://'.$_SERVER['SERVER_NAME'].'/postcard/makepicture/'.$orderId);

        $para = array(
            'order'    => $order,
            'tag'         => JS_TAG,
        );

        $viewModel = new ViewModel($para);
        $viewModel->setTerminal(true); // disable layout template
        return $viewModel;
    }

    public function payTestAction()
    {
        $viewModel = new ViewModel($para);
        $viewModel->setTerminal(true); // disable layout template
        return $viewModel;
    }

/* function resultAction()
    支付通知 notify_url 处理  参见http://www.cnblogs.com/txw1958/p/weixin-pay-trade-notice.html
 */
    public function resultAction()
    {
        $getStr = $_SERVER['QUERY_STRING'];
        $postStr = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : file_get_contents("php://input");

        $this->payLogger('GET:'.$getStr.'  POST:'.$postStr);

        $trade_state = $this->getRequest()->getQuery('trade_state', 1);
        if ($trade_state == 0 && $postStr != null) {    // pay success
    
            $postObj = @simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $out_trade_no = $this->getRequest()->getQuery('out_trade_no');
            $transId = $this->getRequest()->getQuery('transaction_id');
            $openId  = $postObj->OpenId;

            // update order status to 'payed'
            $url = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].'/postcard/changestatus/'.$out_trade_no.'/101';
            $html = file_get_contents($url);

            // copy postcard pictures to 'payed' folder
            $this->copyPicture($out_trade_no);
        }

        echo 'success'; // must respond 'success' to wxpay server
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true); // disable layout template
        return $viewModel;
    }

    public function refundAction()
    {
        $orderId = $this->params()->fromRoute('id', '0');
        $wxPayHelper = new WxPayHelper();
        $wxPayHelper->setParameter("partner", PARTNERID);
        // $wxPayHelper->setParameter("out_trade_no", $orderId);
        $wxPayHelper->setParameter("transaction_id", $orderId); // todo: temp for test
        $wxPayHelper->setParameter("out_refund_no", $orderId.'A'); // 'A' 代表兴业银行支付返现退款
        $wxPayHelper->setParameter("total_fee", "1"); //  ¥0.05
        $wxPayHelper->setParameter("refund_fee", "1"); // ¥0.04
        $wxPayHelper->setParameter("op_user_id", PARTNERID);
        $wxPayHelper->setParameter("op_user_passwd", md5("111111"));
        $wxPayHelper->setParameter("input_charset", "UTF-8");
        $wxPayHelper->setParameter("service_version", "1.1");

        $postData = $wxPayHelper->create_refund_package();
        // echo $postData;
        // $viewModel = new ViewModel();
        // $viewModel->setTerminal(true); // disable layout template
        // return $viewModel;

        $cert = dirname(__FILE__)."/1219350001_20140605115805.pem";
        $cacert = dirname(__FILE__)."/cacert.pem";
        $certPass = "1219350001";

        $util = new CommonUtil();
        $util->setServiceLocator($this->getServiceLocator());
        $util->setCertInfo($cert, $certPass);// cert file and cert password
        $util->setCaInfo($cacert);
        $url = "https://mch.tenpay.com/refundapi/gateway/refund.xml";

        // $retStr = $util->httpPost($url, $postData);
        $resStr = passthru('curl -k --cert '.$cert.':'.$certPass.' -cacert '.$cacert.' -d "'.$postData.'" '.$url;

        $retObj = @simplexml_load_string($retStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        // var_dump($retObj);
        $this->refundLogger('Refund: transaction_id:'.$retObj->transaction_id
                            .' out_trade_no:'.$retObj->out_trade_no
                            .' out_refund_no'.$retObj->out_refund_no
                            .' retcode:'.$retObj->retcode
                            .' retmsg:'.$retObj->retmsg
                            .' refund_status:'.$retObj->refund_status
                            .' refund_id:'.$retObj->refund_id
                            .' refund_fee:'.$retObj->refund_fee
                            .' refund_channel:'.$retObj->refund_channel
                            );

        echo 'success'; // must respond 'success' to wxpay server
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true); // disable layout template
        return $viewModel;
    }

    // 参考http://mp.weixin.qq.com/wiki/index.php?title=%E7%BD%91%E9%A1%B5%E6%8E%88%E6%9D%83%E8%8E%B7%E5%8F%96%E7%94%A8%E6%88%B7%E5%9F%BA%E6%9C%AC%E4%BF%A1%E6%81%AF
    // 从授权页面重定向到此页面，用code换取oauth2_access_token
    public function addressAction()
    {
        $code = $this->getRequest()->getQuery('code', '0');
        if ($code == '0') {
            $view =  new ViewModel(array('code' => 1, 'msg' => '需要从授权页面获取的code'));
            $view->setTemplate('postcard/postcard/error');
            return $view;
        }

        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=wx4a41ea3d983b4538&secret=424b9f967e50a2711460df2a9c9efaaa&code='.$code.'&grant_type=authorization_code';
        $res = json_decode(file_get_contents($url));
        if (isset($res->errcode)) {
            // $view =  new ViewModel(array('code' => 1, 'msg' => 'get access_token failed: '. $res->errmsg));
            // $view->setTemplate('postcard/postcard/error');
            // return $view;
            $res->access_token = "fake_token:addressnotavailable";
        }

        $orderId = $this->getRequest()->getQuery('state', '0');
        $order = $this->getOrderTable()->getOrder($orderId);

        if ($orderId == '0' || !$order) {
            $view =  new ViewModel(array('code' => 1, 'msg' => 'invalid order id '.$orderId));
            $view->setTemplate('postcard/postcard/error');
            return $view;
        }

        if ($order->status == CANCEL) {
            $view =  new ViewModel(array('code' => 2, 'msg' => '订单'.$orderId.'已失效，请重新创建明信片'));
            $view->setTemplate('postcard/postcard/error');
            return $view;
        }

        $viewModel =  new ViewModel(array(
            'order' => $order,
            'tag'   => JS_TAG, // if only want update x.js, modify the tag.   ????????   not work
            'url'   => 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],
            'token' => $res->access_token,
        ));
        $viewModel->setTerminal(true); // disable layout template
        return $viewModel;
    }

    // pay test page. say 'pay' to paohai postcard in wechat, you will get the url of this page
    public function testAction()
    {
        $viewModel = new ViewModel();
        // $viewModel->setTerminal(true); // disable layout template
        return $viewModel;
    }

    private function payLogger($content)
    {
        file_put_contents(dirname(__FILE__).'/../../../../../userdata/paying.log', date('m/d H:i:s').' '.$content."\n", FILE_APPEND);
    }

    private function refundLogger($content)
    {
        file_put_contents(dirname(__FILE__).'/../../../../../userdata/refund.log', date('m/d H:i:s').' '.$content."\n", FILE_APPEND);
    }

    private function copyPicture($orderId)
    {
        $dstpath = $this->payedPicPath();
        if (!is_dir($dstpath)) {
            if (!@mkdir($dstpath)) {
                $this->payLogger('Create folder '.$dstpath.' failed!');
                return false;
            }
        }

        if (!@copy($this->postcardsPath($orderId).$orderId.'_front.png', $this->payedPicPath().$orderId.'_front.png')) {
            $this->payLogger('copy '.$this->postcardsPath($orderId).$orderId.'_front.png failed!');
            return false;
        }

        if (!@copy($this->postcardsPath($orderId).$orderId.'_backface.png', $this->payedPicPath().$orderId.'_backface.png')) {
            $this->payLogger('copy '.$this->postcardsPath($orderId).$orderId.'_backface.png failed!');
            return false;
        }
        return true;
    }

    private function postcardsPath($orderId)
    {
        $dateStr = '20'.substr($orderId, 0, 6);
        $year = ((int)substr($dateStr, 0, 4));
        $month = ((int)substr($dateStr, 4, 2));
        $day = ((int)substr($dateStr, 6, 2));
        $time = mktime(0, 0, 0, $month, $day, $year);
        $orderDate = date("Ymd", $time);
        return dirname(__FILE__).'/../../../../../userdata/postcards/' . $orderDate . '/';
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

        $order = $this->getOrderTable()->getOrder($orderId);
        if ($orderId == '0' || !$order) {
            $view =  new ViewModel(array('code' => 1, 'msg' => 'order '.$orderId.' not exist.'));
            $view->setTemplate('postcard/postcard/error');
            return $view;
        }

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
            $order->bank = $postResult->order_info->bank_type;
            $this->getOrderTable()->saveOrder($order);
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

    private function getOrderTable()
    {
        if (!$this->orderTable) {
            $sm = $this->getServiceLocator();
            $this->orderTable = $sm->get('Postcard\Model\orderTable');
        }
        return $this->orderTable;
    }
}

