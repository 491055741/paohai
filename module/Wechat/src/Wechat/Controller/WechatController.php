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
    protected $util;

    public function indexAction()
    {
        $this->responseMsg();
        return $this->getResponse();
    }

    public function refreshAccessTokenAction()
    {
        $token = $this->getUtil()->refreshAccessToken();
        echo $token;
        return $this->getResponse();
    }

    public function accessTokenAction()
    {
        echo $this->getUtil()->getAccessToken();
        return $this->getResponse();
    }

    private function getUtil()
    {
        if ($this->util == null) {
            $this->util = new CommonUtil();
            $this->util->setServiceLocator($this->getServiceLocator());
        }
        return $this->util;
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
            $time = time();
/*
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

                $orders = $this->getOrderTable()->getOrdersByUserName($fromUsername, 'status = 100'); // query UNPAY order
                if (!$orders) {
                    $contentStr = '请先上传照片';
                } else {
                    foreach ($orders as $order) {
                        $url = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].'/postcard/downloadvoicemedia?mediaId='.urlencode($mediaId);
                        @file_get_contents($url);
                        $contentStr = "已收到语音留言，<a href='http://".$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].'/postcard/editpostcard/'.$order->id.'?voiceMediaId='.$mediaId."'>点击继续编辑</a>";
                        break;
                    }
                }
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $replyMsgType, $contentStr);
                echo $resultStr;
                return true;

            } else */if ($msgType == "image") {
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
                    $url = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"]. '/postcard?picurl='.$picUrl.'&username='.$fromUsername.'&nonce='.time();
                    $resultStr = sprintf($newsTpl, $fromUsername, $toUsername, $time, $replyMsgType, $title, $desc, $picUrl, $url);
                    echo $resultStr;
                    return true;
                } else { // event, txt, video
                    $textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            <FuncFlag>0</FuncFlag>
                            </xml>";
                    $replyMsgType = "text";
//                $contentStr = '您发送的视频id为:'.$postObj->MediaId;
//                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $replyMsgType, $contentStr);
//                echo $resultStr;
//                return true;
                
                    if ($msgType == "video") {
                        $contentStr = '您发送的视频id为:'.$postObj->MediaId;
//                        $contentStr = 'http://file.api.weixin.qq.com/cgi-bin/media/get?access_token='.$this->getUtil()->getAccessToken().'&media_id='.$postObj->MediaId;
                    } else if ($msgType == "event") {
                        $event = $postObj->Event;
// test
//                    $newsTpl = "<xml>
//                                    <ToUserName><![CDATA[%s]]></ToUserName>
//                                    <FromUserName><![CDATA[%s]]></FromUserName>
//                                    <CreateTime>%s</CreateTime>
//                                    <MsgType><![CDATA[%s]]></MsgType>
//                                    <ArticleCount>1</ArticleCount>
//                                    <Articles>
//                                        <item>
//                                            <Title><![CDATA[%s]]></Title>
//                                            <Description><![CDATA[%s]]></Description>
//                                            <PicUrl><![CDATA[%s]]></PicUrl>
//                                            <Url><![CDATA[%s]]></Url>
//                                        </item>
//                                    </Articles>
//                                    </xml>";
//
//                    $picUrl = 'http://pic.sc.chinaz.com/files/pic/pic9/201405/apic3699.jpg';
//                    $replyMsgType = "news";
//                    $title = "扫码测试";
//                    $desc = "点击图片收听留言";
//                    $url = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].'/postcard/voice?mediaId='.$order->mediaId;
//                    $resultStr = sprintf($newsTpl, $fromUsername, $toUsername, $time, $replyMsgType, $title, $desc, $picUrl, $url);

//                    $contentStr = $postObj->Event;
//                    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $replyMsgType, $contentStr);
//                    echo $resultStr;
//                    return true;
// end of test
                    if (($event == "subscribe" || $event == "SCAN") && isset($postObj->Ticket)) {
                        $sceneId = str_replace('qrscene_', '', $postObj->EventKey);
                        if (strlen($sceneId) > 0) {
                            $order = $this->getOrderTable()->getOrderByQrSceneId($sceneId);
                            if ($order && isset($order->voiceMediaId)) {
                                if ($sceneId == '138') {
                                    $txt = '聆听自然堂新年明星祝福';
                                } else {
                                    $txt = '快来听听你的留言吧';
                                }
                                $contentStr = '<a href="http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].'/postcard/playvoice/'.$order->id.'?nonce='.time().'">'.$txt.'</a>';
                            } else {
                                $contentStr = '没有找到语音留言,sceneId:'.$sceneId;
                            }
                        } else {
                            $contentStr = 'sceneId为空';
                        }
                    } else if ($event == "subscribe") {// 订阅

                        $contentStr = <<<WELCOME_TEXT
Hello，看这边！趣邮终于把亲盼来啦！
用电子照回忆美妙瞬间？
Sorry，我们留恋触摸的质感；
用千篇一律的明信片传祝福？
No！还要独家定制的专属感！
趣邮，手机明信片第一品牌，
实现云端自制，邮政送达，
点左下切换到对话框，点“+”号上传照片，开始DIY专属于你的纸质明信片吧！
WELCOME_TEXT;

                    } else if ($event == "CLICK") {
                        $eventKey = $postObj->EventKey;
                        if ($eventKey == "begin") {// 开始
                            $contentStr = <<<BEGIN_TEXT
点击左下角小键盘，切换到对话框模式，点“+”上传手机照片，或者直接拍摄，即可开始制作你的趣邮明信片。
BEGIN_TEXT;
                        } else if ($eventKey == "tutorial") { // 指南
                            $contentStr = <<<TUTORIAL_TEXT
Step 1：关注“趣邮明信片”微信公众号，点击菜单“制作明信片”—“趣邮DIY”
Step 2：根据弹出的操作指引，点击左下角小键盘，切换到对话框模式，点 “+”选择“照片”上传你喜欢的照片，或直接“拍摄”。
Step 3：选择你喜欢的模板美化明信片。
Step 4：图片编辑完成之后，点击“下一步”，填写收件人姓名、地址和邮编等信息，以及留言祝福等内容。更可添加语音，你的亲友在收到明信片之后，扫描二维码，即可听到。内容填写完毕，点击下方可获取纪念戳，同时还可获取你所在地的坐标戳。
Step 5：点击“确认支付”，通过微信支付你的明信片费用。完成后，中国邮政会把你亲手DIY的明信片打印出来，送到你的亲友手上啦！
TUTORIAL_TEXT;
                        } else if ($eventKey == "promotion") { // 活动
                            //$contentStr = '拥有兴业银行卡的小伙伴们有福啊，送给只属于你的，独一无二的爱的咔嚓！支付时选择兴业银行，只要1元！就可以寄送你爱的明信片啦！';
                            $contentStr = <<<PROMOTION_TEXT
亲，活动准备中，即将推出
PROMOTION_TEXT;
                        } else if ($eventKey == "orders") { // 我的订单
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

                            $picUrl = "https://mmbiz.qlogo.cn/mmbiz/j8WFfyvBAoibe4vTSJeXxicKYuRSTRl5noFE8VWHGsQoeiaS89mMPaDe1FWYxsl0zm9v9OPQLiaNianSKe0iaPz0QGSw/0";
                            $replyMsgType = "news";
                            $title = "趣邮明信片--我寄出的明信片";
                            $desc = "查看我都寄出了哪些明信片";
                            $url = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"]. '/postcard/orderlist?userName='.$fromUsername.'&nonce='.time();
                            $resultStr = sprintf($newsTpl, $fromUsername, $toUsername, $time, $replyMsgType, $title, $desc, $picUrl, $url);
                            echo $resultStr;
                            return true;
                        } else if ($eventKey == "address_book") {

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

                            $picUrl = "https://mmbiz.qlogo.cn/mmbiz/j8WFfyvBAoibe4vTSJeXxicKYuRSTRl5noxkjxMU9W4rzN08TSIgyib1l3wXTMdDPpHsTnseEcLZuRJZAIlbHv5kg/0";
                            $replyMsgType = "news";
                            $title = "趣邮明信片--我的地址簿";
                            $desc = "把常用联系人的地址保存在此，能让之后的寄送更方便哦";
                            $url = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"]. '/contact/contactspage?userName='.$fromUsername.'&nonce='.time();
                            $resultStr = sprintf($newsTpl, $fromUsername, $toUsername, $time, $replyMsgType, $title, $desc, $picUrl, $url);
                            echo $resultStr;
                            return true;
                        } else if ($eventKey == "preset_card") {
//                            $contentStr = $this->getPresetCardMessage($postObj);
//                            echo $contentStr;
//                            $contentStr = "新用户关注有礼，首次0.01元";
//                            return true;
                            $contentStr = "订制明信片只需2.99元！";
                        } else {
                            $contentStr = "请上传一张照片";
                        }
                    } else if ($event == 'LOCATION') {
                        $this->receiveUserLatitude($postObj);
                        return true;
                    } else {
                        $contentStr = "请上传一张照片";
                    }
                } else if ($msgType == "text") {
                    $content = trim($postObj->Content);
                    if ($content == 'share') { // test share
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
                        $title = "分享测试";
                        $desc = "点击图片进入测试";
//                        $url = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT']. '/contact/testshare?userName='.$fromUsername;
                        $url = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].'/testshare.html';
                        $resultStr = sprintf($newsTpl, $fromUsername, $toUsername, $time, $replyMsgType, $title, $desc, $picUrl, $url);
                        echo $resultStr;
                        return true;
                    } else {
                        $contentStr = "请上传一张照片";
                    }
                }

                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $replyMsgType, $contentStr);
                echo $resultStr;
                return true;
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

    /********************** event handler ************************/
    private function receiveUserLatitude($receiveData) {
//        $latitude = $receiveData->Latitude;
//        $longitude = $receiveData->Longitude;
        $userPosition = new UserPosition();
        $userPosition->setUserName($receiveData->FromUserName)
            ->setLatitude($receiveData->Latitude)
            ->setLongitude($receiveData->Longitude)
            ->updateTimestamp();

        $this->getUserPositionTable()->savePosition($userPosition);
    }

    private function getPresetCardMessage($receiveData) {

//        $images = array(
//            "https://mmbiz.qlogo.cn/mmbiz/j8WFfyvBAoicVx982wian4uhRZf4WK7EQib0pLd6hS9eHLhVhwGqjFmWNDRGEcPAs77ZC17228JDQg26tGDTwxibwA/0",
//        );

        $url = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].'/activity.html';
        $items = <<<PRESET_TITLE_TEXT
    <item>
        <Title><![CDATA[即日起，只要关注“趣邮明信片”微信公众号，并首次使用“趣邮”DIY 明信片即可享受0.01元/首张的优惠价格哦\r\n快来呼朋唤友DIY自己的明信片吧！]]></Title>
        <PicUrl><![CDATA[https://mmbiz.qlogo.cn/mmbiz/j8WFfyvBAoicVx982wian4uhRZf4WK7EQibeRLH6TpTZ6ftTAnljmzXP3JHHSylRbIQbJia9k70Oj7Kcs2u4Saec5Q/0]]></PicUrl>
        <Url><![CDATA[{$url}]]></Url>
    </item>
PRESET_TITLE_TEXT;

//        foreach ($images as $picUrl) {
//            $url = 'http://'.$_SERVER['SERVER_NAME'] . ':' . $_SERVER["SERVER_PORT"] .
//                '/postcard?picurl=' . $picUrl . '&username=' . $receiveData->FromUserName;
//            $items .= <<<PRESET_ITEM_TEXT
//    <item>
//        <Title><![CDATA[圣诞限定！2014珍藏版Christmas Card]]></Title>
//        <PicUrl><![CDATA[{$picUrl}]]></PicUrl>
//        <Url><![CDATA[{$url}]]></Url>
//    </item>
//PRESET_ITEM_TEXT;
//        }

        $vars = array(
            "currtTime" => time(),
            "itemsCount" => 1,
            "items" => $items, 
        );

        $content = <<<PRESET_CARD_TEXT
<xml>
    <ToUserName><![CDATA[{$receiveData->FromUserName}]]></ToUserName>
    <FromUserName><![CDATA[{$receiveData->ToUserName}]]></FromUserName>
    <CreateTime>{$vars["currtTime"]}</CreateTime>
    <MsgType><![CDATA[news]]></MsgType>
    <ArticleCount>{$vars["itemsCount"]}</ArticleCount>
    <Articles>
    {$vars["items"]}
    </Articles>
</xml>
PRESET_CARD_TEXT;

        return $content;
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
