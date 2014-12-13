<?php
namespace Wxpay\Controller;

include_once(dirname(__FILE__)."/../../../view/wxpay/wxpay/WxPayPubHelper/WxPayPubHelper.php");
include_once(dirname(__FILE__)."/../../../view/wxpay/wxpay/CommonUtil.php");

use Notify_pub;
use CommonUtil;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Postcard\Model\Order;

ini_set("display_errors", true);

// order status
define('CANCEL',   99); // 已取消
define('UNPAY',   100); // 待支付
define('PAYED',   101); // 已支付
define('PRINTED', 102); // 已打印
define('SHIPPED', 103); // 已发货

define('JS_TAG', '201412131541');

class WxpayController extends AbstractActionController
{
    protected $orderTable;
    public function previewAction()
    {
        $orderId = $this->getRequest()->getQuery('orderId', '0');
        if ($orderId == '0') {
            $orderId = $this->getRequest()->getQuery('state', '0');
        }
        $order = $this->getOrderTable()->getOrder($orderId);

        if ($orderId == '0' || !$order) {
            $view =  new ViewModel(array('code' => 1, 'msg' => 'invalid order id '.$orderId));
            $view->setTemplate('postcard/postcard/error');
            return $view;
        }

        if ($order->status == CANCEL) {
            return $this->errorViewModel(array('code' => 2, 'msg' => '订单已失效，请重新创建明信片'));
        }
        $util = new CommonUtil();
        $util->setServiceLocator($this->getServiceLocator());
        $location = $util->getUserGeoAddress($order->userName);

        $price = $this->getOrderTable()->calculateOrderPrice($order->userName);
        $order->price = $price;
        $this->getOrderTable()->saveOrder($order);
        return $this->viewModel(array(
            'payPrice' => $price,
            'order' => $order,
            'tag'   => JS_TAG,
            'city'  => $location ? $location['city'] : '0',
        ));
    }

    public function asyncMakePictureAction()
    {
        $orderId = $this->params()->fromRoute('id', '0');
        $util = new CommonUtil();
        $util->httpGet('http://'.$_SERVER['SERVER_NAME'].'/postcard/makepicture/'.$orderId, 1); // timeout = 1s， not wait response
        return $this->errorViewModel(array('code' => 0, 'msg' => 'Send ok.'));
    }

    public function asyncCopyPictureAction()
    {
        $orderId = $this->params()->fromRoute('id', '0');
        $util = new CommonUtil();
        $util->httpGet('http://'.$_SERVER['SERVER_NAME'].'/wxpay/copypicture/'.$orderId, 1); // timeout = 1s， not wait response
        return $this->errorViewModel(array('code' => 0, 'msg' => 'Copy command send ok.'));
    }

    public function payAction()
    {
        $orderId = $this->getRequest()->getQuery('orderId', '0');
        $order = $this->getOrderTable()->getOrder($orderId);
        if ($orderId == '0' || !$order) {
            return $this->errorViewModel(array('code' => 1, 'msg' => 'invalid order id: '.$orderId));
        }

        $util = new CommonUtil();
        $util->httpPost('http://'.$_SERVER['SERVER_NAME'].'/postcard/makepicture/'.$orderId);

        return $this->viewModel(array(
            'order'    => $order,
            'tag'      => JS_TAG,
        ));
    }

    // 支付通知 notify_url 处理
    public function resultAction()
    {
        //使用通用通知接口
        $notify = new Notify_pub();

        //存储微信的回调
        $xml = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : file_get_contents("php://input");
        $notify->saveData($xml);

        //验证签名，并回应微信。
        //对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
        //微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
        //尽可能提高通知的成功率，但微信不保证通知最终能成功。
        if($notify->checkSign() == FALSE){
            $notify->setReturnParameter("return_code","FAIL");//返回状态码
            $notify->setReturnParameter("return_msg","签名失败");//返回信息
        }else{
            $notify->setReturnParameter("return_code","SUCCESS");//设置返回码

            if ($notify->data["return_code"] == "FAIL") {
                $this->payLogger("【通信出错】:\n".$xml."\n");
            } elseif ($notify->data["result_code"] == "FAIL"){
                $this->payLogger("【业务出错】:\n".$xml."\n");
            } else {
                $this->payLogger("【支付成功】:\n".$xml."\n");
                $out_trade_no = $notify->data['out_trade_no'];
                $order = $this->getOrderTable()->getOrder($out_trade_no);
                if ($out_trade_no != '0' && $order && $order->status == Order::STATUS_UNPAY) {
                    // update order status to 'payed'
                    $url = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].'/postcard/changestatus/'.$out_trade_no.'/101';
                    $html = file_get_contents($url);
                    // copy postcard pictures to 'payed' folder
                    $url = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].'/wxpay/asynccopypicture/'.$out_trade_no;
                    $html = file_get_contents($url);
                }
            }
        }
        $returnXml = $notify->returnXml();
        echo $returnXml;
        return $this->viewModel();
    }
/*
    private function refund($orderId)
    {
        $wxPayHelper = new WxPayHelper();
        $wxPayHelper->setParameter("partner", PARTNERID);
        $wxPayHelper->setParameter("out_trade_no", $orderId);
        // $wxPayHelper->setParameter("transaction_id", $orderId); // todo: temp for test
        $wxPayHelper->setParameter("out_refund_no", $orderId.'A'); // 'A' 代表兴业银行支付返现退款
        $wxPayHelper->setParameter("total_fee", "5"); //  ¥0.05   // todo : use real fee
        $wxPayHelper->setParameter("refund_fee", "4"); // ¥0.04
        $wxPayHelper->setParameter("op_user_id", PARTNERID);
        $wxPayHelper->setParameter("op_user_passwd", md5("111111"));
        $wxPayHelper->setParameter("input_charset", "UTF-8");
        $wxPayHelper->setParameter("service_version", "1.1");

        $postData = $wxPayHelper->create_refund_package();

        $cert = dirname(__FILE__)."/paohaicert.pem";
        $cacert = dirname(__FILE__)."/cacert.pem";
        $certPass = "1219350001";

        $util = new CommonUtil();
        $util->setServiceLocator($this->getServiceLocator());
        $util->setCertInfo($cert, $certPass);// cert file and cert password
        $util->setCaInfo($cacert);
        $url = "https://mch.tenpay.com/refundapi/gateway/refund.xml";

        $retStr = $util->httpPost($url, $postData);

        $retObj = @simplexml_load_string($retStr, 'SimpleXMLElement', LIBXML_NOCDATA);

        $this->refundLogger('Refund:'
                            .' orderId:'.$orderId
                            .' transaction_id:'.$retObj->transaction_id
                            .' out_refund_no:'.$retObj->out_refund_no
                            .' retcode:'.$retObj->retcode
                            .' retmsg:'.$retObj->retmsg
                            .' refund_status:'.$retObj->refund_status
                            .' refund_id:'.$retObj->refund_id
                            .' refund_fee:'.$retObj->refund_fee
                            .' refund_channel:'.$retObj->refund_channel
                            );
        return $retObj;
    }
*/
    public function refundAction()
    {
        $orderId = $this->params()->fromRoute('id', '0');
        $order = $this->getOrderTable()->getOrder($orderId);
        if ($orderId == '0' || !$order) {
            return $this->errorViewModel(array('code' => 1, 'msg' => 'order '.$orderId.' not exist.'));
        }

        $this->refund($orderId);
        echo 'success'; // must respond 'success' to wxpay server
        return $this->viewModel();
    }

    // pay test page. say 'pay' to quyou postcard in Wechat, you will get the url of this page
    public function testAction()
    {
        return $this->viewModel();
    }

    private function payLogger($content)
    {
        file_put_contents($this->payedPicPath().'/../paying.log', date('m/d H:i:s').' '.$content."\n", FILE_APPEND);
    }

    private function refundLogger($content)
    {
        file_put_contents(dirname(__FILE__).'/../../../../../userdata/refund.log', date('m/d H:i:s').' '.$content."\n", FILE_APPEND);
    }

    public function copyPictureAction()
    {
        $orderId = $this->params()->fromRoute('id', '0');
        $order = $this->getOrderTable()->getOrder($orderId);
        if ($orderId == '0' || !$order) {
            return $this->errorViewModel(array('code' => 1, 'msg' => 'order '.$orderId.' not exist.'));
        }

        $res = $this->copyPicture($orderId);
        return $this->errorViewModel(array('code' => 0, 'msg' => $res ? 'copy success' : 'copy failed'));
    }

    private function copyPicture($orderId)
    {
        if (!$this->tryCopy($this->postcardsPath($orderId).$orderId.'_front.jpg', $this->payedPicPath().$orderId.'_front.jpg')) {
            $this->payLogger('copy '.$this->postcardsPath($orderId).$orderId."_front.jpg failed!\n");
            return false;
        }

        if (!$this->tryCopy($this->postcardsPath($orderId).$orderId.'_backface.jpg', $this->payedPicPath().$orderId.'_backface.jpg')) {
            $this->payLogger('copy '.$this->postcardsPath($orderId).$orderId."_backface.jpg failed!\n");
            return false;
        }
        return true;
    }

    private function tryCopy($src, $dst)
    {
        $retryTimes = 3;
        $sleepTime = 3;
        for ($i = 0; $i < $retryTimes; $i++) {
            if (@copy($src, $dst)) {
                return true;
            }
            sleep($sleepTime*2);
        }
        return false;
    }

    private function postcardsPath($orderId)
    {
        $dateStr = '20'.substr($orderId, 0, 6);
        $year  = ((int)substr($dateStr, 0, 4));
        $month = ((int)substr($dateStr, 4, 2));
        $day   = ((int)substr($dateStr, 6, 2));
        $time  = mktime(0, 0, 0, $month, $day, $year);
        $orderDate = date("Ymd", $time);
        $path  = dirname(__FILE__).'/../../../../../userdata/postcards/' . $orderDate;
        $this->checkPath($path);
        return $path . '/';
    }

    private function payedPicPath()
    {
        $payPath = dirname(__FILE__).'/../../../../../userdata/payed/';
        $this->checkPath($payPath);
        $payPath = $payPath.date('Ymd', time());
        $this->checkPath($payPath);
        return $payPath.'/';
    }

    private function checkPath($path)
    {
        if (!is_dir($path)) {
            if (!mkdir($path)) {
                echo 'Create folder '.$path.' failed!';
                $this->payLogger('Create folder '.$path.' failed!');
                return false;
            }
        }
        return true;
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
    private function orderQuery($orderId)
    {
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
        return $postResult;
    }

    // query order pay info from tencent server
    public function orderQueryAction()
    {
        // https://api.weixin.qq.com/pay/orderquery?access_token=xxxxxx
        $orderId = $this->params()->fromRoute('id', '0');

        $order = $this->getOrderTable()->getOrder($orderId);
        if ($orderId == '0' || !$order) {
            return $this->errorViewModel(array('code' => 1, 'msg' => 'order '.$orderId.' not exist.'));
        }

        $postResult = $this->orderQuery($orderId);
        // echo $postResult;
        echo '<br>errcode:';
        echo $postResult->errcode;
        echo '<br>errmsg:';
        echo $postResult->errmsg;
        if ($postResult->errcode == 0) {
            echo '<br>orderinfo:';
            var_dump($postResult->order_info);
        }

        return $this->viewModel();
    }

    public function updateBankAction()
    {
        // https://api.weixin.qq.com/pay/orderquery?access_token=xxxxxx
        // $orderId = $this->params()->fromRoute('id', '0');

        $orders = $this->getOrderTable()->getOrdersToQueryBank();
        foreach ($orders as $order) {
        //     var_dump($order);
        //     echo "<br>";
            $postResult = $this->orderQuery($order->id);
            // var_dump($postResult);
            if ($postResult->errcode == 0 && $postResult->order_info) {
                // echo '<br>orderinfo:';
                // var_dump($postResult->order_info);
                $order->bank = $postResult->order_info->bank_type;
                echo '<br>';
                echo 'order id:'.$order->id.' bank:'.$order->bank;
                $this->getOrderTable()->saveOrder($order);
            }
        }

        return $this->errorViewModel(array('code' => 0, 'msg' => 'update bank success.'));
    }

    public function refundAllAction()
    {
        $orders = $this->getOrderTable()->getOrdersToRefund();
        foreach ($orders as $order) {
        //     var_dump($order);
        //     echo "<br>";
            $retObj = $this->refund($order->id);
            // var_dump($retObj);
            if ($retObj->retcode == 0) {
                // echo '<br>orderinfo:';
                $order->refundFee = $retObj->refund_fee;
                echo '<br>';
                echo 'order id:'.$order->id.' refund_fee:'.$retObj->refund_fee;
                $this->getOrderTable()->saveOrder($order);
            }
        }

        return $this->errorViewModel(array('code' => 0, 'msg' => 'update success.'));
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
        return $this->viewModel();
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
        // todo: 通知商户管理员 包括发货延迟 、调用失败、通知失败等情况
        return $this->viewModel();
    }

    private function getOrderTable()
    {
        if (!$this->orderTable) {
            $sm = $this->getServiceLocator();
            $this->orderTable = $sm->get('Postcard\Model\orderTable');
        }
        return $this->orderTable;
    }

    private function viewModel($para = null)
    {
        $viewModel = new ViewModel($para);
        $viewModel->setTerminal(true); // disable layout template
        return $viewModel;
    }

    private function errorViewModel($para = null)
    {
        $viewModel = new ViewModel($para);
        $viewModel->setTemplate('postcard/postcard/error');
        return $viewModel;
    }
}

