<?php
namespace Wechat\Controller;

include_once(dirname(__FILE__)."/../../../../Wxpay/view/wxpay/wxpay/CommonUtil.php");

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Postcard\Model\Order;
use Postcard\Model\UserPosition;
use Postcard\Libs\Maps;
use CommonUtil;


define("TOKEN", "quyoumessagetoken");
ini_set("display_errors", true);

session_start();

class WechatController extends AbstractActionController
{
    protected $orderTable;
    protected $userPositionTable;


    public function indexAction()
    {
        $this->responseMsg();
        return $this->getResponse();
    }

    public function refreshAccessTokenAction()
    {
        $util = new CommonUtil();
        $util->setServiceLocator($this->getServiceLocator());
        $token = $util->refreshAccessToken();
        echo $token;
        return $this->getResponse();
    }

    public function accessTokenAction()
    {
        $util = new CommonUtil();
        $util->setServiceLocator($this->getServiceLocator());
        echo $util->getAccessToken();
        return $this->getResponse();
    }

    private function responseMsg()
    {
        if (isset($_GET["echostr"])) {
            return $this->validate();
        }

        //get post data, May be due to the different environments
        $postStr = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : file_get_contents("php://input");
        // echo $postStr;
        // extract post data
        if (!empty($postStr)) {

            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
            $msgType = $postObj->MsgType;
            $content = trim($postObj->Content);
            $time = time();

            if ($msgType == "voice") {
                $mediaId = $postObj->MediaId;
                $textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            <FuncFlag>0</FuncFlag>
                            </xml>";
                $replyMsgType = "text";

                $order = $this->getOrderTable()->getOrderByUserName($fromUsername);
                if (!$order) {
                    $contentStr = '请先上传照片(内测调试中，不能真正邮寄明信片，敬请期待)';
                } else {
                    $url = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].'/postcard/downloadvoicemedia?mediaId='.urlencode($mediaId);
                    @file_get_contents($url);
                    $contentStr = "已收到语音留言，<a href='http://".$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].'/postcard/editmessage/'.$order->id.'?voiceMediaId='.$mediaId."'>点击继续编辑</a>";
                }
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $replyMsgType, $contentStr);
                echo $resultStr;
                return;

            } else if ($msgType == "image") {
                $newsTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <ArticleCount>1</ArticleCount>
                            <Articles>
                                <item>
                                    <Title><![CDATA[%s]]></Title>
                                    <Description><![CDATA[%s]]></Description>
                                    <PicUrl><![CDATA[%s]]></PicUrl>
                                    <Url><![CDATA[%s]]></Url>
                                </item>
                            </Articles>
                            </xml>";

                $picUrl = $postObj->PicUrl;
                $replyMsgType = "news";
                $title = "点击创建明信片";
                $desc = "就是这张么？如果确定了，就戳戳图片开始制作明信片啦~";
                $url = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"]. '/postcard?picurl='.$picUrl.'&username='.$fromUsername;
                $resultStr = sprintf($newsTpl, $fromUsername, $toUsername, $time, $replyMsgType, $title, $desc, $picUrl, $url);
                echo $resultStr;
                return;
            } else {
                $textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            <FuncFlag>0</FuncFlag>
                            </xml>";
                $replyMsgType = "text";
                if ($msgType == "event") {
                    $event = $postObj->Event;
                    if ($event == "subscribe") {// 订阅
                        $contentStr = <<<WELCOME_TEXT
HELLO,看这边！泡海明信片在这恭候你多时啦！
美妙瞬间只能通过电子照回忆？SORRY，我想要触摸的质感。
赶快DIY属于你的纸质明信片吧，中国邮政全国范围寄送上门！

【国庆疯玩！5分钱大放送，不玩就亏了】
黄金假期发福利，原价5元一张，现在只需5分钱啊！很疯狂有木有！只限前100张！手快有手慢无！
DO IT NOW！
WELCOME_TEXT;
                    } else if ($event == "CLICK") {
                        $eventKey = $postObj->EventKey;
                        if ($eventKey == "begin") {// 开始
                            $contentStr = "切换到对话框，点击“+”号，上传手机照片或直接拍摄，即可开始制作。";
                        } else if ($eventKey == "promotion") { // 活动
                            //$contentStr = '拥有兴业银行卡的小伙伴们有福啊，送给只属于你的，独一无二的爱的咔嚓！支付时选择兴业银行，只要1元！就可以寄送你爱的明信片啦！';
                            $contentStr = "亲，只要上传你手机里的任何一张照片到泡海明信片，你就可以定制一张独一无二的专属明信片啦。这张明信片可以通过邮局寄送到国内任何地方。。。别再犹豫，点击“+”号，赶快开始DIY吧。";
                        } else if ($eventKey == "orders") { // 我的订单
                            $contentStr = '订单查询功能开发中！';
                        } else {
                            $contentStr = "请上传一张照片";
                        }
                    } else if ($event == 'LOCATION') {
                        $this->receiveUserLatitude($postObj);
                        return;

                    } else {
                        $contentStr = "请上传一张照片";
                    }
                } else { // text
                    if ($content == 'pay') { // test wxpay
                        // $contentStr = 'http://paohai.ikamobile.com/wxpay';
                        $newsTpl = "<xml>
                                    <ToUserName><![CDATA[%s]]></ToUserName>
                                    <FromUserName><![CDATA[%s]]></FromUserName>
                                    <CreateTime>%s</CreateTime>
                                    <MsgType><![CDATA[%s]]></MsgType>
                                    <ArticleCount>1</ArticleCount>
                                    <Articles>
                                        <item>
                                            <Title><![CDATA[%s]]></Title>
                                            <Description><![CDATA[%s]]></Description>
                                            <PicUrl><![CDATA[%s]]></PicUrl>
                                            <Url><![CDATA[%s]]></Url>
                                        </item>
                                    </Articles>
                                    </xml>";

                        $picUrl = 'http://pic.sc.chinaz.com/files/pic/pic9/201405/apic3699.jpg';
                        $replyMsgType = "news";
                        $title = "支付测试";
                        $desc = "点击图片进入支付测试";
                        $url = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"]. '/wxpay/test';
                        $resultStr = sprintf($newsTpl, $fromUsername, $toUsername, $time, $replyMsgType, $title, $desc, $picUrl, $url);
                        echo $resultStr;
                        return;
                    } else {
                        $contentStr = "请上传一张照片";
                    }
                }

                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $replyMsgType, $contentStr);
                echo $resultStr;
                return;
            }
        } else {
            $msg = "no post data";
            echo $msg;
        }
        return $msg;
    }

    private function getOrderTable()
    {
        if (!$this->orderTable) {
            $sm = $this->getServiceLocator();
            $this->orderTable = $sm->get('Postcard\Model\OrderTable');
        }
        return $this->orderTable;
    }

    private function getUserPositionTable() {
        if ( ! $this->userPositionTable) {
            $sm = $this->getServiceLocator();
            $this->userPositionTable = $sm->get('Postcard\Model\UserPositionTable');
        }
        return $this->userPositionTable;
    }

    private function validate()
    {
        $echoStr = $this->getRequest()->getQuery('echostr');
        //valid signature , option
        if ($this->checkSignature()) {
            echo $echoStr;
            exit;
        } else {
            echo "checkSignature failed.";
        }
    }

    private function checkSignature()
    {
        $signature = $this->getRequest()->getQuery('signature');
        $timestamp = $this->getRequest()->getQuery('timestamp');
        $nonce     = $this->getRequest()->getQuery('nonce');

        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        
        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }


    private function receiveUserLatitude($receiveData) {
        $latitude = $receiveData->Latitude;
        $longitude = $receiveData->Longitude;

        $userPosition = new UserPosition();
        $userPosition->setUserName($receiveData->FromUserName)
            ->setLatitude($receiveData->Latitude)
            ->setLongitude($receiveData->Longitude)
            ->updateTimestamp();

        $this->getUserPositionTable()->savePosition($userPosition);
    }


    /**
     * 查询user_position表，根据lnglat转换为地址信息
     */
    private function getUserGeoAddress($userName) {
        $userLngLat = $this->getUserPositionTable()
            ->getPositionByUserName($userName);
        if ( ! $userLngLat) {
            return NULL;
        }

        $longitude = $userLngLat->getLongitude();
        $latitude = $userLngLat->getLatitude();

        $res = Maps::geoLatLng2Address($longitude, $latitude);
        $data = json_decode($res, true);
        if ( ! $data) {
            return NULL;
        }

        if ($data['status'] != '0') {
            // TODO ERR LOG
            $errorMsg = $data['msg'];
            return NULL;
        }
        $addressComponent = $data['result']['addressComponent'];

        return array(
            'province' => $addressComponent['province'],
            'city' => $addressComponent['city'],
            'district' => $addressComponent['district'],
            'street' => $addressComponent['street'],
            'cityCode' => $data['result']['cityCode'],
        );
    }
}
