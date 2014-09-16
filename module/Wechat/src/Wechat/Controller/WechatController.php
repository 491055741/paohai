<?php
namespace Wechat\Controller;

include_once(dirname(__FILE__)."/../../../../Wxpay/view/wxpay/wxpay/CommonUtil.php");

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Postcard\Model\Order;
use Postcard\Model\UserPosition;
use CommonUtil;


define("TOKEN", "ademoforpaohai");
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
                $desc = "点击图片完成创建";
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
                        $contentStr = 'HELLO,欢迎加入泡海明信片，一起玩“泡海”
手机拍了那么多美照干嘛用？
赶快定制一张泡海明信片吧！
给自己留念，向朋友炫耀，送家人祝福……
盖上邮戳的真实明信片，中国邮政7天寄上门~
把生活点滴记录下来，把人生分享出去，你能遇见更好的自己！';
                    } else if ($event == "CLICK") {
                        $eventKey = $postObj->EventKey;
                        if ($eventKey == "begin") {// 开始
                            $contentStr = '点击“+”号，上传手机照片或直接拍摄，开始制作';
                        } else if ($eventKey == "promotion") { // 活动
                            $contentStr = '拥有兴业银行卡的小伙伴们有福啊，送给只属于你的，独一无二的爱的咔嚓！支付时选择兴业银行，只要1元！就可以寄送你爱的明信片啦！';
                        } else if ($eventKey == "orders") { // 我的订单
                            $contentStr = '订单查询功能开发中！';
                        } else {
                            $contentStr = "请上传一张照片";
                        }
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
                        $url = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"]. '/wxpay/pay/1234';
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

        $userPosition = new userPosition();
        $userPosition->setUserName($receiveData->FromUserName)
            ->setLatitude($receiveData->Latitude)
            ->setLongitude($receiveData->Longitude)
            ->updateTimestamp();
        $this->getUserPositionTable()->savePosition($userPosition);
    }
}
