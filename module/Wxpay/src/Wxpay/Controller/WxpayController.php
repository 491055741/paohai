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
            'total_fee'   => ($bank == 'XingYe' ? 100 : 500),
            'order_id'    => $orderId,
        );

        $viewModel = new ViewModel($para);
        $viewModel->setTerminal(true); // disable layout template
        return $viewModel;
    }

/* function resultAction()
    支付通知 notify_url 处理  参见http://jingyan.baidu.com/article/da1091fbd4e6e4027849d607.html
    先取$POST 这是常规的支付通知信息，形如：
    array('bank_type' => '3006', 
    'discount' => '0', 
    'fee_type' => '1', 
    'input_charset' => 'UTF-8', 
    'notify_id' => 'YaNO6cznoNZK0aGb8nJWGgVUWssjt7Ze7gWRaRS0R_5w9oXgGNkRGxReEk0r45yk3I9a2_gzo9IqgqMYbap6bxC2T3p0o-2C', 
    'out_trade_no' => '1214284731', 
    'partner' => '12xxxxxxxx', 
    'product_fee' => '3400', 
    'sign' => '545FA0E8B594BBXXXX48XX142F084TY', 
    'sign_type' => 'MD5', 
    'time_end' => '20130223110224', 
    'total_fee' => '3400', 
    'trade_mode' => '1', 
    'trade_state' => '0', 
    'transaction_id' => '12XXX449012014XXX33174005XXX', 
    'transport_fee' => '0',)

    再用file_get_contents('php://input')读取额外的信息，形如：
    <xml>
    <OpenId><![CDATA[o0pd3jqHaN7b0tVPDFJPzJEkSCLw]]></OpenId>
    <AppId><![CDATA[wxXXX06XX2cXXX88XX]]></AppId>
    <IsSubscribe>1</IsSubscribe>
    <TimeStamp>1400814743</TimeStamp>
    <NonceStr><![CDATA[lqxwMsiY9EXRDpms]]></NonceStr>
    <AppSignature><![CDATA[c2dxxxe186116b32b06axxxc1a688b671eexxx5e]]></AppSignature>
    <SignMethod><![CDATA[sha1]]></SignMethod>
    </xml>
 */
    public function resultAction()
    {
        // var_dump($GLOBALS['HTTP_RAW_POST_DATA']);
        // $postStr = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : file_get_contents("php://input");
        echo $postStr;
        // var_dump($_POST);
        // $trade_state = $_POST['trade_state'];
        // echo 'result result:<br>';
        // echo '$trade_state='.$trade_state.'<br>';
        // if ($trade_state == 0) {    // pay success
        //     $out_trade_no = $_POST['out_trade_no'];
        //     echo '$out_trade_no='.$out_trade_no;
        //     // update order status to 'payed'
        //     // $url = 'paohai.ikamobile.com/postcard/update/'.$out_trade_no.'/101';
        //     // $html = file_get_contents($url);
        //     // $postStr = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : file_get_contents("php://input");
        // }

        echo 'success'; // must respond 'success' to wxpay server
        $viewModel = new ViewModel();
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
        // $access_token = $util->getAccessToken();
        $access_token = 'MgZ9JvuGhlWY7o8RhVOb8JSA6nY67bOZShTXBuwniejezzjdVctQXCN45Fv2LbEz0EknLvp4OwIjmkFQKpnfog';
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
        if (!empty($postStr)) {

            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $OpenId = $postObj->OpenId;
            $AppId = $postObj->AppId;
            $TimeStamp = $postObj->TimeStamp;
            $MsgType = $postObj->MsgType;
            $FeedBackId = $postObj->FeedBackId;
            $Reason = $postObj->Reason;
            $AppSignature = $postObj->AppSignature;
            $SignMethod = $postObj->SignMethod;

            if ($MsgType == 'request') {
                $TransId = $postObj->TransId;
                $Solution = $postObj->Solution;
                $ExtInfo = $postObj->ExtInfo;
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
        echo "feedback recieved!  todo: notify customer service...";
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

        echo "alarm recieved! todo: notify adminstrator...";
        // 通知商户管理员 包括发货延迟 、调用失败、通知失败等情况
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true); // disable layout template
        return $viewModel;
    }
}

