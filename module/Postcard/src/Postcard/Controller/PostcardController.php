<?php
namespace Postcard\Controller;

include_once(dirname(__FILE__)."/../../../../Wxpay/view/wxpay/wxpay/CommonUtil.php");
include_once(dirname(__FILE__)."/../../../../Wxpay/view/wxpay/wxpay/WxPayPubHelper/WxPay.pub.config.php");

use Imagick;
use CommonUtil;
use WxPayConf_pub;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Postcard\Model\Order;
use Postcard\Model\Contact;
use Postcard\Model\UserPosition;
use Postcard\Libs\PinYin;
use Postcard\Libs\Maps;

define('DEFAULT_PICURL', 'http://pic.sc.chinaz.com/files/pic/pic9/201405/apic3699.jpg');
define('DEFAULT_USER', 'default_user_openid'); // default user (my openid is ocKsTuKbE4QqHbwGEXmVnuLHO_sY / odVjojqR6SGQPtjU2etk_0-tU0K8)

// order status
define('CANCEL',   99); // 已取消
define('UNPAY',   100); // 待支付
define('PAYED',   101); // 已支付
define('PRINTED', 102); // 已打印
define('SHIPPED', 103); // 已发货

define('LEFT', 0);
define('RIGHT', 1);
define('CENTER', 2);

define('JS_TAG', '2015012214161234');

class PostcardController extends AbstractActionController
{
    protected $orderTable;
    protected $userPositionTable;
    protected $contactTable;
    protected $util;

    // 防SQL注入用的，暂未用
    private function postCheck($post)
    {
        if (!get_magic_quotes_gpc()) // 判断magic_quotes_gpc是否为打开
        {
            $post = addslashes($post); // 进行magic_quotes_gpc没有打开的情况对提交数据的过滤
        }
        $post = str_replace("_", "\_", $post); // 把 '_'过滤掉
        $post = str_replace("%", "\%", $post); // 把' % '过滤掉
        $post = nl2br($post); // 回车转换
        $post= htmlspecialchars($post); // html标记转换
        return $post;
    }

    public function makeOrdersAction()
    {
        $error = "ok"; //上传文件出错信息
        $fileElementName = 'uploadFile';
        $errCode = $_FILES[$fileElementName]['error'];
        if (!empty($errCode)) {
            switch($errCode) {
                case '1':
                    $error = '传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值';
                    break;
                case '2':
                    $error = '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值';
                    break;
                case '3':
                    $error = '文件只有部分被上传';
                    break;
                case '4':
                    $error = '没有文件被上传';
                    break;
                case '6':
                    $error = '找不到临时文件夹';
                    break;
                case '7':
                    $error = '文件写入失败';
                    break;
                default:
                    $error = '未知错误';
            }
        } elseif (empty($_FILES[$fileElementName]['tmp_name']) || $_FILES[$fileElementName]['tmp_name'] == 'none') {
            $error = '没有上传文件.';
        } else {
            $isFirstLine = true;

            $file = fopen($_FILES[$fileElementName]['tmp_name'],"r");
            while (!feof($file)) {

                $valArray = fgetcsv($file);
                if ($isFirstLine) {
                    $isFirstLine = false;
                    continue;
                }
                if (count($valArray) < 2) {
                    break;
                }
                $newArray = array_map(function ($str){return sprintf("'%s'", $str);}, $valArray);
                $values = implode(',', $newArray);
//                $values = $this->postCheck($values);
                $sql = 'INSERT INTO `order_table` VALUES ('.$values.')';
                $sm = $this->getServiceLocator();
                $adapter = $sm->get('Zend\Db\Adapter\Adapter');
                $adapter->query($sql, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
            }
            fclose($file);
        }
        return $this->errorViewModel(array('code' => 0, 'msg' => $error));
    }

    public function voiceAction()
    {
        $mediaId = $this->getRequest()->getQuery('mediaId', '0');
        if ($mediaId == '0') {
            return $this->errorViewModel(array('code' => 1, 'msg' => 'require media id'));
        }

        $fileName = $this->voicePath().$mediaId;
        if (file_exists($fileName.'.mp3')) {
            header("Content-type: audio/mp3");
            echo file_get_contents($fileName.'.mp3');
        } else if (file_exists($fileName.'.spx')) {
            header("Content-type: audio/x-speex-with-header-byte; rate=16000");
            echo file_get_contents($fileName.'.spx');
        } else {
            return $this->errorViewModel(array('code' => 2, 'msg' => 'file '.$fileName.' not exist!'));
        }

        $viewModel = new ViewModel();
        $viewModel->setTerminal(true); // disable layout template
        return $viewModel;
    }

    public function playVoiceAction()
    {
        $orderId = $this->params()->fromRoute('id', '0');
        $mediaId = $this->getRequest()->getQuery('mediaId', '0');
        if ($mediaId == '0') {
            return $this->errorViewModel(array('code' => 1, 'msg' => 'require media id'));
        }

        $viewModel = new ViewModel(array(
            'orderId' => $orderId,
            'file'    =>'http://'.$_SERVER['HTTP_HOST'].'/postcard/voice?mediaId='.$mediaId,
            'tag'     => JS_TAG
        ));
        $viewModel->setTerminal(true); // disable layout template
        return $viewModel;
    }

    // 发送提示给用户，让用户语音留言
    public function requestVoiceAction()
    {
        $orderId = $this->params()->fromRoute('id', '0');

        $order = $this->getOrderTable()->getOrder($orderId);
        if ($orderId == '0' || !$order) {
            return $this->errorViewModel(array('code' => 1, 'msg' => 'invalid order id '.$orderId));
        }

        // var_dump($order);
        $token = $this->getUtil()->getAccessToken();
        $res = $this->getUtil()->httpPost('https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$token,
                                $this->JSON(array(
                                                'touser'  => $order->userName,
                                                'msgtype' => 'text',
                                                'text'    => array('content' => '请说出你的语音留言（点击左下角的语音录入按钮可以开始录音。）' ),
                                                ))
                                );
        $array = $this->object2array(json_decode($res));
        return new JsonModel($array);
    }

    public function indexAction()
    {
        $orderId = $this->getRequest()->getQuery('orderId', '0');
        $order = $this->getOrderTable()->getOrder($orderId);
        $picUrl = $this->getRequest()->getQuery('picurl', DEFAULT_PICURL);

        if ($orderId == '0' || !$order) {
            $selectedTemplateIndex = -1;
            $offsetX = 0;
            $offsetY = 0;
        } else {
            $selectedTemplateIndex = $order->templateId;
            $offsetX = $order->offsetX;
            $offsetY = $order->offsetY;
            $picUrl = $order->picUrl;
        }

        $viewModel =  new ViewModel(array(
            'templateIndex' => $selectedTemplateIndex,
            'offsetX' => $offsetX,
            'offsetY' => $offsetY,
            'orderId' => $this->getRequest()->getQuery('orderId', '0'),
            'picurl'  => $picUrl,
            'username' => $this->getRequest()->getQuery('username', DEFAULT_USER),
            'tag' => JS_TAG,
        ));
        $viewModel->setTerminal(true); // disable layout template
        return $viewModel;
    }

    public function editPostcardAction()
    {
        $orderId = $this->params()->fromRoute("id", "0");
        $order = $this->getOrderTable()->getOrder($orderId);
        if ($orderId == '0' || !$order) {
            return $this->errorViewModel(array('code' => 1, 'msg' => 'invalid order id '.$orderId));
        }

        if ($order->status == CANCEL) {
            return $this->errorViewModel(array('code' => 2, 'msg' => '订单'.$orderId.'已失效，请重新创建明信片'));
        }

        // update mediaId. Media will valid for 3 days on Tecent's server.
        $voiceMediaId = $this->getRequest()->getQuery('voiceMediaId');
        if ($voiceMediaId && $order->qrSceneId == null) {
            $order->voiceMediaId = $voiceMediaId;
            $order->qrSceneId = $this->getUtil()->getQrSceneId();
//            echo 'order qr sceneId:'.$order->qrSceneId;
            $this->getWXQrImage($order->qrSceneId, $this->voicePath().$voiceMediaId.'.png');
            $this->getOrderTable()->saveOrder($order);
        }

        $jsApiSignPackage = $this->getUtil()->getJsApiSignPackage();
        return $this->viewModel(array(
            'order' => $order,
            'tag'   => JS_TAG,
            'userName' => $order->userName,
            'jsApiSignPackage' => $jsApiSignPackage,
        ));
    }

    public function userLngLatAction() {
        $orderId = $this->params()->fromRoute("id", "0");
        $order = $this->getOrderTable()->getOrder($orderId);
        if ($orderId == '0' || !$order) {
            $res = array(
                'code' => 1,
                'msg' => 'invalid order id ' . $orderId,
            );

            return new JsonModel($res);
        }

        if ($order->status == CANCEL) {
            $res = array(
                'code' => 2,
                'msg' => '订单: ' . $orderId . ' 已失效，请重新创建明信片',
            );

            return new JsonModel($res);
        }

        $userLngLat = $this->getUserPositionTable()
            ->getPositionByUserName($order->userName);
        if ( ! $userLngLat) {
            return new JsonModel(array(
                'code' => 0,
                'lnglat' => array(),
            ));
        }

        $longitude = $userLngLat->getLongitude();
        $latitude = $userLngLat->getLatitude();
        $lastUpdateTimestamp = $userLngLat->getLastUpdateTimestamp();
        return new JsonModel(array(
            'code' => 0,
            'lnglat' => array(
                'longitude' => $longitude,
                'latitude' => $latitude,
                'lastUpdateTime' => $lastUpdateTimestamp,
            ),
        ));
    }

    public function shareImageAction()
    {
        $orderId = $this->params()->fromRoute('id', '0');
        $order = $this->getOrderTable()->getOrder($orderId);
        if ($orderId == '0' || !$order) {
            $view =  new ViewModel(array('code' => 1, 'msg' => 'invalid order id '.$orderId));
            $view->setTemplate('postcard/postcard/error');
            return $view;
        }

        $canvas_w = 973.0;
        $canvas_h = 1440.0;
        $image = $this->generateFront($order, $canvas_w, $canvas_h);
        if ($image) {
            // TODO rotate
            if ($order->templateId >= 8) {
                $image = imagerotate($image, 90, 0);
            }

            header("Content-type: image/png");
            imagepng($image);
            imagedestroy($image);
            $viewModel = new ViewModel();
            $viewModel->setTerminal(true); // disable layout template
            return $viewModel;

        } else {
            $res = array(
                'code' => 1,
                'msg'  => 'url:'.$order->picUrl,
            );
            return new JsonModel($res);
        }
    }

    public function ordersAction()
    {
        return new ViewModel(array(
            'orders' => $this->getOrderTable()->fetchAll(),
        ));
    }

    public function payedAction()
    {
        return new ViewModel(array(
            'orders' => $this->getOrderTable()->getPayedOrders(),
        ));
    }

    public function orderListAction() // user query his orders via wechat menu
    {
        $userName = $this->getRequest()->getQuery('userName', '0');
        if ($userName == '0') {
            $view =  new ViewModel(array('code' => 1, 'msg' => 'invalid username: '.$userName));
            $view->setTemplate('postcard/postcard/error');
            return $view;
        }
        $orders = $this->getOrderTable()->getOrdersByUserName($userName, 'status >='.UNPAY);
        $view = new ViewModel(array('orders' => $orders,));
        $view->setTerminal(true); // disable layout template
        return $view;
    }

    public function ordersToRefundAction()
    {
        $view =  new ViewModel(array('orders' => $this->getOrderTable()->getOrdersToRefund()));
        $view->setTemplate('postcard/postcard/orders');
        return $view;
    }

    public function contactsAction()
    {
        $userName = $this->getRequest()->getQuery('userName', '0');
        if ($userName == '0') {
            $view =  new ViewModel(array('code' => 1, 'msg' => 'invalid user name: '.$userName));
            $view->setTemplate('postcard/postcard/error');
            return $view;
        }
        return new JsonModel($this->getContactTable()->getContacts($userName));
    }

    public function addContactAction()
    {
        $userName = $this->getRequest()->getPost('userName', '0');
        $contactName = $this->getRequest()->getPost('contactName', '0');
        if ($userName == '0' || $contactName == '0') {
            $view =  new ViewModel(array('code' => 1, 'msg' => 'UserName needed.'));
            $view->setTemplate('postcard/postcard/error');
            return $view;
        }
        $contact = $this->getContactTable()->getContact($userName, $contactName);
        if (!$contact) {
            $contact = new Contact();
            $contact->userName    = $userName;
            $contact->contactName = $contactName;
        }

        $contact->address = $this->getRequest()->getPost('address', '');
        $contact->zipCode = $this->getRequest()->getPost('zipCode', '');
        $this->getContactTable()->saveContact($contact);

        $res = array(
            'code' => 0,
            'msg'  => 'Contact add OK.',
        );
        return new JsonModel($res);
    }

    public function makeAllPicAction()
    {
        $subDay = $this->params()->fromRoute('id', '0');
        $orders = $this->getOrderTable()->fetchAll();
        $msg = '';
        foreach ($orders as $order) {
            if (substr($order->orderDate, 0, 10) == date('Y-m-d', strtotime('-'.$subDay.' day'))) {
                $url = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].'/wxpay/asyncmakepicture/'.$order->id;
                @file_get_contents($url);
                $msg .= 'make pic of '.$order->id."\n";
            }
        }
        return $this->errorViewModel(array('code' => 0, 'msg' => $msg));
    }

    public function makePictureAction()
    {
        $orderId = $this->params()->fromRoute('id', '0');
        $order = $this->getOrderTable()->getOrder($orderId);
        if ($orderId == '0' || !$order) {
            $view =  new ViewModel(array('code' => 1, 'msg' => 'invalid order id '.$orderId));
            $view->setTemplate('postcard/postcard/error');
            return $view;
        }

        if (!$this->makePicture($order)) {
            $res = array(
                'code' => 100,
                'msg'  => 'make picture failed',
            );
        } else {
            $res = array(
                'code' => 0,
                'msg'  => 'make picture success',
            );
        }
        return new JsonModel($res);
    }

    public function downloadVoiceMediaAction()
    {
        $orderId = $this->params()->fromRoute('id', '0');
        $order = $this->getOrderTable()->getOrder($orderId);
        if ($orderId == '0' || !$order) {
            $view =  new ViewModel(array('code' => 1, 'msg' => 'invalid order id '.$orderId));
            $view->setTemplate('postcard/postcard/error');
            return $view;
        }

        $mediaId = $this->getRequest()->getQuery('mediaId', '0');
        if ($mediaId == '0') {
            $view =  new ViewModel(array('code' => 1, 'msg' => 'require mediaId'));
            $view->setTemplate('postcard/postcard/error');
            return $view;
        }

        $order->voiceMediaId = $mediaId;
        if ($order->qrSceneId == null) {
            $order->qrSceneId = $this->getUtil()->getQrSceneId();
//            echo 'order qr sceneId:'.$order->qrSceneId;
        }
        $this->getOrderTable()->saveOrder($order);
        $this->getWXQrImage($order->qrSceneId, $this->voicePath().$mediaId.'.png');

        $token = $this->getUtil()->getAccessToken();
        $url = 'http://file.api.weixin.qq.com/cgi-bin/media/get?access_token='.$token.'&media_id='.$mediaId;
        $fileName = $this->voicePath().$mediaId;
//        $len = file_put_contents($fileName, $this->getUtil()->httpGet($url, 60));
        $this->getUtil()->httpGetFile($url, $fileName);
        // convert from amr to mp3
        $cmd = 'ffmpeg -i '.$fileName.' '.$this->voicePath().$mediaId.'.mp3';
//        echo 'url: '.$url.PHP_EOL.'len: '.$len.PHP_EOL.'exec: '.$cmd.PHP_EOL;
        exec($cmd);

        return new JsonModel(array(
            'code' => 0,
            'msg'  => 'download voice file success',
        ));
    }

    public function placeOrderAction()
    {
        // cancel old order first
        $userName = $this->getRequest()->getPost('userName', DEFAULT_USER);

        $orders = $this->getOrderTable()->getOrdersByUserName($userName, 'status='.UNPAY);
        foreach ($orders as $order) {
            $order->status = CANCEL;
            $this->getOrderTable()->saveOrder($order);
            // echo 'order: '.$order->id.' canceled.';
        }

        // new order
        while (1) {
            $orderId = date("ymd") . rand(10000, 99999);
            if (!$this->getOrderTable()->getOrder($orderId)) {
                break;
            }
        }

        $order = new Order();
        $order->id         = $orderId;
        $order->userName   = $this->getRequest()->getPost('userName',   DEFAULT_USER);
        $order->picUrl     = $this->getRequest()->getPost('userPicUrl', DEFAULT_PICURL);
        $order->templateId = $this->getRequest()->getPost('templateIndex', '0');
        $order->offsetX    = $this->getRequest()->getPost('offsetX', '0');
        $order->offsetY    = $this->getRequest()->getPost('offsetY', '0');
        $order->status     = UNPAY;
        $order->orderDate  = date('Y-m-d H:i:s');
        // var_dump($order);
        $this->getOrderTable()->saveOrder($order);

        $res = array(
            'code' => 0,
            'orderId' => $orderId,
        );
        return new JsonModel($res);
    }

    public function updateOrderAction()
    {
        $orderId = $this->params()->fromRoute('id', '0');
        $order = $this->getOrderTable()->getOrder($orderId);

        if ($orderId == '0' || !$order) {
            $res = array(
                    'code' => -1,
                    'msg' => 'invalid order id',
                );
        } else {
            $templateId         = $this->getRequest()->getPost('templateIndex');
            $offsetX            = $this->getRequest()->getPost('offsetX');
            $offsetY            = $this->getRequest()->getPost('offsetY');

            $postmarkId         = $this->getRequest()->getPost('postmarkId');
            if ($postmarkId === "" || $postmarkId === NULL) {
                $postmarkId = NULL;
            }
            $zipCode            = $this->getRequest()->getPost('zipcode');
            $message            = $this->getRequest()->getPost('message');
            $senderName         = $this->getRequest()->getPost('senderName');
            $senderAddress      = $this->getRequest()->getPost('senderAddress');
            $signature          = $this->getRequest()->getPost('signature');
            $address            = $this->getRequest()->getPost('address');
            $recipient          = $this->getRequest()->getPost('recipient');
            $salutation         = $this->getRequest()->getPost('salutation');
            $userName           = $this->getRequest()->getPost('userName');
            $picUrl             = $this->getRequest()->getPost('userPicUrl');
            $status             = $this->getRequest()->getPost('status');
            $bank               = $this->getRequest()->getPost('bank');
            $mobile             = $this->getRequest()->getPost('mobile');

            $order->postmarkId = $postmarkId;
            $templateId         ? $order->templateId        = $templateId    : null;
            $offsetX            ? $order->offsetX           = $offsetX       : null;
            $offsetY            ? $order->offsetY           = $offsetY       : null;
            $zipCode            ? $order->zipCode           = $zipCode       : null;
            $message            ? $order->message           = $message       : null;
            $senderName         ? $order->senderName        = $senderName    : null;
            $senderAddress      ? $order->senderAddress     = $senderAddress : null;
            $signature          ? $order->signature         = $signature     : null;
            $address            ? $order->address           = $address       : null;
            $recipient          ? $order->recipient         = $recipient     : null;
            $salutation         ? $order->salutation        = $salutation    : null;
            $userName           ? $order->userName          = $userName      : null;
            $picUrl             ? $order->picUrl            = $picUrl        : null;
            $status             ? $order->status            = $status        : null;
            $bank               ? $order->bank              = $bank          : null;
            $mobile             ? $order->recipientMobile   = $mobile        : null;
            $this->getOrderTable()->saveOrder($order);
            $this->logger('msg:['.$order->message.']');
            $res = array(
                'code' => 0,
                'msg' => 'success',
            );
        }

        return new JsonModel($res);
    }

    public function completeAction()
    {
        $orderId = $this->params()->fromRoute('id', '0');
        $order = $this->getOrderTable()->getOrder($orderId);

        if ($orderId == '0' || !$order) {
            $view =  new ViewModel(array('code' => 1, 'msg' => 'invalid order id '.$orderId));
            $view->setTemplate('postcard/postcard/error');
            return $view;
        }

        // 价格为0，修改状态为已支付
        if ($order->price == 115 && $order->status == Order::STATUS_UNPAY) {
            // update order status to 'payed'
            $url = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].'/postcard/changestatus/' . $orderId . '/101';
            $html = file_get_contents($url);
        }

        $viewModel = new ViewModel(array('orderId' => $orderId, 'tag' => JS_TAG));
        $viewModel->setTerminal(true); // disable layout template
        return $viewModel;
    }

    public function deleteAction()
    {
        $orderId = $this->params()->fromRoute('id', '0');
        $order = $this->getOrderTable()->getOrder($orderId);
        if ($orderId == '0' || !$order) {
            $code = 1;
            $msg = '订单 '.$orderId.' 不存在!';
        } else {
            $this->getOrderTable()->deleteOrder($orderId);
            $code = 0;
            $msg = '订单 '.$orderId.' 已删除';
        }

        $view =  new ViewModel(array('code' => $code, 'msg' => $msg));
        $view->setTemplate('postcard/postcard/error');
        return $view;
    }

    public function changeStatusAction()
    {
        $orderId = $this->params()->fromRoute('id', 0);
        echo "orderId:" . $orderId . "</br>";
        $status = $this->params()->fromRoute('status');
        echo "new status:" . $status . "</br>";
        if (!$orderId || ($status != PRINTED && $status != SHIPPED && $status != PAYED)) {
            echo "wrong para! status must be 101(PAYED), 102(PRINTED) or 103(SHIPPED)!";
        } else {
            $order = $this->getOrderTable()->getOrder($orderId);
            if (!$order) {
                echo "order not exist!";
            } else {
                $order->status = $status;
                $order->payDate = date('Y-m-d H:i:s');
                $this->getOrderTable()->saveOrder($order);
                echo "update success";

                if ($status == SHIPPED) {     // 调用发货通知接口通知微信
                    $this->deliverNotify(array('orderid' => $out_trade_no,
                                       'tansid' => $transId,  // todo !!
                                       'openid' => $openId,   // todo !!
                                        )
                                );
                }
            }
        }

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


    public function testGeoAction() {
        $latitude = $this->getRequest()->getQuery('latitude', '0'); 
        $longitude = $this->getRequest()->getQuery('longitude', '0');
        $res = Maps::geoLatLng2Address($longitude, $latitude);
        echo $res;exit;
    }

    public function deliverNotify($data)
    {
        $access_token = $this->getUtil()->getAccessToken();
        $url = "https://api.weixin.qq.com/pay/delivernotify?access_token=".$access_token;

        $wxPayHelper = new WxPayHelper();
        $nativeObj['appid'] = WxPayConf_pub::appId();
        $nativeObj['openid'] = $data['openid'];
        $nativeObj['transid'] = $data['transid'];
        $nativeObj['out_trade_no'] = $data['orderid'];
        $nativeObj['deliver_timestamp'] = $wxPayHelper->create_timestamp();
        $nativeObj['deliver_status'] = '1';
        $nativeObj['deliver_msg'] = 'ok';
        $nativeObj["app_signature"] = $wxPayHelper->get_biz_sign($nativeObj);
        $nativeObj["sign_method"] = SIGNTYPE;
        $postResult = json_decode($this->getUtil()->httpPost($url, json_encode($nativeObj)));

        return $postResult;
    }

    public function clientReportLnglatAction()
    {
        $userName = $this->getRequest()->getPost('username');
        $latitude = $this->getRequest()->getPost('latitude');
        $longitude = $this->getRequest()->getPost('longitude');

        if ( ! $userName) {
            return new JsonModel(array(
                'code' => 1,
                'msg' => '用户不能为空'
            ));
        }
        $latlngParttern = "/^\d+\.\d+$/";
        if (
            preg_match($latlngParttern, $latitude) == 0 ||
            preg_match($latlngParttern, $longitude) == 0
        ) {
            return new JsonModel(array(
                'code' => 1,
                'msg' => '经纬度格式错误'
            ));
        }
        
        $userPosition = new UserPosition();
        $userPosition->setUserName($userName)
            ->setLatitude($latitude)
            ->setLongitude($longitude)
            ->updateTimestamp();
        $this->getUserPositionTable()->savePosition($userPosition);

        return new JsonModel(array(
            'code' => 0,
            'msg' => 'update user position successed',    
        ));
    }

    private function logger($content)
    {
        file_put_contents($this->logFileName(), date('m/d H:i:s').' '.$content."\n", FILE_APPEND); // notice: use "\n", not '\n'
    }

    private function logFileName()
    {
        return '/tmp/quyou.log';
    }

    private function getOrderTable()
    {
        if (!$this->orderTable) {
            $sm = $this->getServiceLocator();
            $this->orderTable = $sm->get('Postcard\Model\orderTable');
        }
        return $this->orderTable;
    }

    private function getContactTable()
    {
        if (!$this->contactTable) {
            $sm = $this->getServiceLocator();
            $this->contactTable = $sm->get('Postcard\Model\contactTable');
        }
        return $this->contactTable;
    }

    private function checkPath($path)
    {
        if (!is_dir($path)) {
            if (!mkdir($path)) {
                echo 'Create folder '.$path.' failed!';
                return false;
            }
        }
        return true;
    }

    private function voicePath()
    {
        $path = dirname(__FILE__).'/../../../../../userdata';
        if (!$this->checkPath($path)) {
            return false;
        }

        $path = $path.'/voice';
        if (!$this->checkPath($path)) {
            return false;
        }

        return $path.'/';
    }

    private function object2array($array)
    {
        if (is_object($array)) {
            $array = (array)$array;
        }
        if (is_array($array)) {
            foreach($array as $key=>$value) {
            $array[$key] = $this->object2array($value);
            }
        }
      return $array;
    }

    private function makePicture($order)
    {
        $dstpath = $this->postcardsPath($order->id);

        $canvas_w = 1946.0;
        $canvas_h = 2880.0;

        $image = $this->generateFront($order, $canvas_w, $canvas_h);
        if (!$image) {
            $this->logger('makePicture/generateFront failed!');
            return false;
        }

        imagejpeg($image, $dstpath.$order->id.'_front.jpg');
        imagedestroy($image);

//        $this->adjustBrightness($dstpath.$order->id.'_front.jpg', $dstpath.$order->id.'_bright.jpg');

        $image = $this->generatePostcardBack($order);
        imagejpeg($image, $dstpath.$order->id.'_backface.jpg', 90);
        imagedestroy($image);
        return true;
    }

    public function getPictureAction()
    {
        $orderId = $this->params()->fromRoute('id', '0');
        $face = $this->params()->fromRoute('status', '0');
        $order = $this->getOrderTable()->getOrder($orderId);
        if ($orderId == '0' || !$order) {
            $view =  new ViewModel(array('code' => 1, 'msg' => 'invalid order id '.$orderId));
            $view->setTemplate('postcard/postcard/error');
            return $view;
        }

        $canvas_w = 1946.0;
        $canvas_h = 2880.0;
        if ($face == '0') {
            $image = $this->generateFront($order, $canvas_w, $canvas_h);
        } else {
            $image = $this->generatePostcardBack($order);
        }

        if ($image) {
//            header("Content-type: mine");//image/png
            $filename = $orderId.($face == '0' ? '_front.jpg' : '_backface.jpg');

            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.$filename);
            header('Content-Transfer-Encoding: binary');
//            header("viewport: width=device-width, initial-scale=1");
//            header("title:".$orderId.$name);
            imagejpeg($image, NULL, 90);
            imagedestroy($image);
            $viewModel = new ViewModel();
            $viewModel->setTerminal(true); // disable layout template
            return $viewModel;
        } else {
            $res = array(
                'code' => 1,
                'msg'  => 'generate picture failed, original image url:'.$order->picUrl,
            );
            return new JsonModel($res);
        }
    }

    // need imagick module
    private function adjustBrightness($srcFileName, $dstFileName)
    {
        $image = new Imagick($srcFileName);
        $image->modulateImage(120, 100, 100);
        $image->writeImage($dstFileName);
        $image->destroy();
    }

    private function generateFront($order, $canvas_w, $canvas_h)
    {
        if (abs($order->offsetX) >= 1 || abs($order->offsetY) >= 1) {
            echo 'wrong offset!';
            return FALSE;
        }

        $image_template = imagecreatefrompng('public/images/big/template'.$order->templateId.'.png');
        imagealphablending($image_template, false);
        imagesavealpha($image_template, true);

        // save user's original picture
        $dstPath = $this->postcardsPath($order->id);
        $origPicName = $dstPath.$order->id.'_orig.jpg';
        if (!file_exists($origPicName)) {
            file_put_contents($origPicName, $this->getUtil()->httpGet($order->picUrl, 120));
        }

        $angel = ($order->templateId >= 8) ? -90 : 0; // 与web旋转方向一致，为顺时针方向旋转
        $image_user = $this->getAutoRotatedImg($origPicName, $angel);

        $a = imagesx($image_user);
        $b = imagesy($image_user);

        // 用户照片移动
        $x = -$order->offsetX * $a;
        $y = -$order->offsetY * $b;

        $moved = imagecreatetruecolor($a - $x, $b - $y);
        imagecopy($moved, $image_user, 0, 0, $x, $y, $a, $b);
        imagedestroy($image_user);

        $a = imagesx($moved);
        $b = imagesy($moved);
        $ratio = 1.48;
        $w = $a; $h = $b;
        if ($h/$w > $ratio) {
            $h = $w * $ratio;
        } else {
            $w = $h / $ratio;
        }

        $croped = imagecreatetruecolor($w, $h);
        imagecopy($croped, $moved, 0, 0, 0, 0, $a, $b);
        imagedestroy($moved);

        $image_dst = imageCreatetruecolor($canvas_w, $canvas_h); // canvas
        imagealphablending($image_dst, true);
        $white = imagecolorallocate($image_dst, 255, 255, 255);
        imagefill($image_dst, 0, 0, $white);

        imagecopyresampled($image_dst, $croped, 0, 0, 0, 0, $canvas_w, $canvas_h, imagesx($croped), imagesy($croped));
        imagecopyresampled($image_dst, $image_template, 0, 0, 0, 0, $canvas_w, $canvas_h, imagesx($image_template), imagesy($image_template));

        imagedestroy($image_template);
        imagedestroy($croped);

        return $image_dst;
    }

    private function getAutoRotatedImg($imgName, $angelAdjust)
    {
        $exif = exif_read_data($imgName, 'IFD0');
        if ($exif === false) {
            $orientation = 0;
        } else {
            $orientation = isset($exif['Orientation'])?$exif['Orientation']:1;
        }
//        var_dump($exif);
        $img = imagecreatefromjpeg($imgName);
        switch ($orientation) {
            case 1:
                $angel = 0;
                break;
            case 6:
                $angel = -90;
                break;
            case 8:
                $angel = 90;
                break;
            case 3:
                $angel = 180;
                break;
            default:
                $angel = 0;
                break;
        }
        if ($angel + $angelAdjust != 0) {
            $img = imagerotate($img, $angel + $angelAdjust, 0);  // 旋转角度为正值表示反时针方向旋转
        }
        return $img;
    }

    private function generatePostcardBack($order)
    {
        // 148mm*100mm, 300dpi, 1mm => 11.81px
        $canvas_w = 1748.0;
        $canvas_h = 1181.0;

        $dst = imagecreatetruecolor($canvas_w, $canvas_h);
        $white = imagecolorallocate($dst, 255, 255, 255);
        imagefill($dst, 0, 0, $white);

        $background = imagecreatefromjpeg('public/images/big/postCardBack.jpg');
        imagealphablending($background, false);
        imagesavealpha($background, true);
        imagecopyresampled($dst, $background, 0, 0, 0, 0, $canvas_w, $canvas_h, imagesx($background), imagesy($background));

        // zip code
        $pos['top'] = 155;
        $pos['width'] = 94;
        $pos['font-size'] = 50;
        $pos['font-file'] = "public/fonts/simkai.ttf";
        for ($i = 0; $i < mb_strlen($order->zipCode, "utf-8") && $i < 6; $i++) {
            $char = mb_substr($order->zipCode, $i, 1, "utf-8");
            $pos['left'] = 115 + $i * 112;
            $this->draw_txt_to($dst, $pos, $char);
        }
        unset($pos['font-file']);
        // salutation
        if ($order->salutation) {
            $pos['left']     = 80;
            $pos['top']      = 300;
            $pos['width']    = 810;
            $pos['font-size'] = 36;
            $this->draw_txt_to($dst, $pos, $order->salutation);
        }
        // message
        $msgEndPos = 954;
        if ($order->message) {
            $pos['left']      = 160;
            $pos['top']       = 423;
            $pos['width']     = 756;
            $pos['font-size']  = 36;
            $pos['lineSpace'] = 94;
            $msgEndPos = $this->draw_txt_to($dst, $pos, $order->message);
        }
        // signature
        if ($order->signature) {
            $pos['left']       = 450;
            $pos['top']        = $msgEndPos + 94;
            $pos['width']      = 500;
            $pos['font-size']   = 36;
            $pos['text-align'] = true;
            $this->draw_txt_to($dst, $pos, '－'.$order->signature);
            unset($pos['text-align']);
        }
        // recipient address
        $pos['left']      = 1116;
        $pos['top']       = 500;
        $pos['width']     = 540;
        $pos['font-size']  = 30;
        $pos['lineSpace'] = 94;
        $this->draw_txt_to($dst, $pos, $order->address);
        // recipient name
        $pos['left']     = 1270;
        $pos['top']      = $pos['top'] + 282;
        $pos['width']    = 1080;
        $pos['font-size'] = 30;
//        $pos['font-file'] = "public/fonts/simsun.ttf";
        $this->draw_txt_to($dst, $pos, $order->recipient);
//        unset($pos['font-file']);
        // voice qr code
        if ($order->voiceMediaId) {
            if (!file_exists($this->voicePath().$order->voiceMediaId.'.png')) {
                $this->getWXQrImage($order->qrSceneId, $this->voicePath().$order->voiceMediaId.'.png');
                $this->getOrderTable()->saveOrder($order);
            }

            $image_qr = imagecreatefrompng($this->voicePath().$order->voiceMediaId.'.png');

            // add logo onto qr
            $logo_canvas = imagecreatetruecolor(50, 50);
            $white = imagecolorallocate($logo_canvas, 255, 255, 255);
            imagefill($logo_canvas, 0, 0, $white);
            $image_logo = imagecreatefromjpeg('public/images/small/logo.jpg');
            imagecopyresampled($logo_canvas, $image_logo, 5, 5, 0, 0, 40, 40, imagesx($image_logo), imagesy($image_logo));
            $logo_width = imagesx($image_qr)*0.15;
            $logo_height = imagesy($image_qr)*0.15;
            imagecopyresampled($image_qr, $logo_canvas, (imagesx($image_qr)-$logo_width)/2, (imagesy($image_qr)-$logo_height)/2, 0, 0,
                $logo_width, $logo_height, imagesx($logo_canvas), imagesy($logo_canvas));
            $text = '听取你的留言';
        } else {
            // quyou qr code
            $image_qr = imagecreatefromjpeg('public/images/big/qr_quyou.jpg');
            $text = 'DIY你的明信片';
        }
        $width=$height=250;
        imagecopyresampled($dst, $image_qr, $canvas_w-$width-70, $canvas_h-$height-110, 0, 0, $width, $height, imagesx($image_qr), imagesy($image_qr));
        $pos['text-align'] = CENTER;
        $pos['left']     = $canvas_w-$width-70;
        $pos['top']      = 1100;
        $pos['width']    = $width;
        $pos['font-size'] = 20;
        $pos['font-file'] = "public/fonts/simkai.ttf";
        $this->draw_txt_to($dst, $pos, $text);

        // partner Qr code
        if ($order->partnerQrFileName) {
            $file = 'public/images/big/'.$order->partnerQrFileName;
            if (!file_exists($file)) {
                echo 'qr code image ['.$file.'] not exist!';
                return null;
            }

            if ($this->endWith($file, '.jpg')) {
                $image_qr = imagecreatefromjpeg($file);
            } else if ($this->endWith($file, '.png')) {
                $image_qr = imagecreatefrompng($file);
            } else {
                echo 'qr code image ['.$file.'] not support!';
                return null;
            }

            $width=$height=250;
            imagecopyresampled($dst, $image_qr, 1180, $canvas_h-$height-110, 0, 0, $width, $height, imagesx($image_qr), imagesy($image_qr));

            if ($order->partnerQrText) {
                $pos['text-align'] = CENTER;
                $pos['left']     = 1180;
                $pos['top']      = 1100;
                $pos['width']    = $width;
                $pos['font-size'] = 20;
                $pos['font-file'] = "public/fonts/simkai.ttf";
                $this->draw_txt_to($dst, $pos, $order->partnerQrText);
            }
        }

        // stamp   82px => 7mm
        $width=$height=300;
        $image = imagecreatefrompng('public/images/big/stamp.png');
        imagecopyresampled($dst, $image, $canvas_w-82-$width, 82, 0, 0, $width, $height, imagesx($image), imagesy($image));

        // Commemorative Chop
        if ($order->postmarkId != null) {
            $image = imagecreatefrompng('public/images/postmark/big/youchuo'.$order->postmarkId.'.png');
            $postmark_w = 306;
            $postmark_h = imagesy($image) / imagesx($image) * $postmark_w;
            $postmark_x = 1150;
            $postmark_y = 170;

            imagecopyresampled($dst, $image, $postmark_x, $postmark_y, 0, 0, $postmark_w, $postmark_h, imagesx($image), imagesy($image));

            $textAttr = $this->getDateTextAttr($order->postmarkId, $postmark_x, $postmark_y);
            if ($textAttr != NULL) {
                $this->draw_txt_to($dst, $textAttr, date($textAttr['dateFormat'], time()));
            }

        } else {

            // location postmark
//            $location = NULL;
//            $location = array('city' => '猪牛羊');
            $location = $this->getUtil()->getUserGeoAddress($order->userName);

            if ($location != NULL) {
                $postmark_x = 1150;
                $postmark_y = 200;

                $pos['text-align'] = CENTER;
                $pos['nowrap'] = true;

                $pos['left']     = $postmark_x + 142;
                $pos['top']      = $postmark_y + 127;
                $pos['width']    = 90;
                $pos['font-size'] = 20;
                $this->draw_txt_to($dst, $pos, $location['city']);

                $pos['left']     = $postmark_x + 130;
                $pos['top']      = $postmark_y + 155;
                $pos['width']    = 110;
                $pos['font-size'] = 15;
                $this->draw_txt_to($dst, $pos, strtoupper(PinYin::Pinyin($location['city'], 1)));

                $pos['left']     = $postmark_x + 128;
                $pos['top']      = $postmark_y + 180;
                $pos['width']    = 110;
                $pos['font-size'] = 16;
                $this->draw_txt_to($dst, $pos, date('Y.m.d', time()));

                $imageName = 'youchuo_empty.png';
                $image = imagecreatefrompng('public/images/postmark/big/'.$imageName);
                $postmark_h = 216;
                $postmark_w = imagesx($image) / imagesy($image) * $postmark_h;
                imagecopyresampled($dst, $image, $postmark_x, $postmark_y, 0, 0, $postmark_w, $postmark_h, imagesx($image), imagesy($image));
            }
        }

        return $dst;
    }

    private function getDateTextAttr($postmarkId, $x, $y)
    {
        $dateTextArray = array(
            array(
// 0               'text'     => '成都',
                'left'     => $x + 150,
                'top'      => $y + 216,
                'width'    => 600,
                'font-size' => 16,
                'font-color' => array(152, 45, 35),
                'dateFormat' => 'Y.m.d',
            ),

            array(
// 1               'text'     => '三亚',
                'left'     => $x + 168,
                'top'      => $y + 156,
                'width'    => 600,
                'font-size' => 15,
                'font-color' => array(7, 111, 70),
                'dateFormat' => 'Y.m.d',
            ),

            array(
// 2               'text'     => '杭州',
                'left'     => $x + 196,
                'top'      => $y + 102,
                'width'    => 600,
                'font-size' => 18,
                'font-color' => array(134, 91, 67),
                'dateFormat' => 'Y.m.d',
            ),

            array(
// 3               'text'     => '北京',
                'left'     => $x + 80,
                'top'      => $y + 165,
                'width'    => 600,
                'font-size' => 18,
                'font-color' => array(68, 67, 67),
                'dateFormat' => 'Y.m.d',
            ),

            array(
// 4               'text'     => '广州',
                'left'     => $x + 160,
                'top'      => $y + 130,
                'width'    => 600,
                'font-size' => 18,
                'font-color' => array(60, 60, 60),
                'dateFormat' => 'Y.m.d',
            ),

            array(
// 5               'text'     => '上海',
                'left'     => $x + 116,
                'top'      => $y + 165,
                'width'    => 600,
                'font-size' => 18,
                'font-color' => array(62, 62, 62),
                'dateFormat' => 'Y  m.d',
            ),

            array(
// 6               'text'     => '深圳',
                'left'     => $x + 180,
                'top'      => $y + 145,
                'width'    => 600,
                'font-size' => 18,
                'font-color' => array(60, 60, 60),
                'dateFormat' => 'Y.m.d',
            ),

            array(
// 7               'text'     => '厦门',
                'left'     => $x + 180,
                'top'      => $y + 145,
                'width'    => 600,
                'font-size' => 18,
                'font-color' => array(60, 60, 60),
                'dateFormat' => 'Y.m.d',
            ),

            array(
// 8               'text'     => '昆明',
                'left'     => $x + 180,
                'top'      => $y + 145,
                'width'    => 600,
                'font-size' => 18,
                'font-color' => array(60, 60, 60),
                'dateFormat' => 'Y.m.d',
            ),
        );
        if ($postmarkId < count($dateTextArray)) {
            $dateTextArray[$postmarkId]['font-file'] = "public/fonts/simkai.ttf";
            return $dateTextArray[$postmarkId];
        } else {
            return NULL;
        }
    }

    private function get_txt_pos($textAlign, $left, $width, $strWidth) {
        $x = $left;
        if ($textAlign == RIGHT) {
            $x = $left + $width - $strWidth;
        } else if ($textAlign == CENTER) {
            $x = $left + $width/2 - $strWidth/2;
        }
        return $x;
    }

    private function draw_txt_to($image, $pos, $string)
    {
        $nowrap = false;
        if (array_key_exists('nowrap', $pos)) {
            $nowrap = $pos['nowrap'];
        }

        if (!array_key_exists('text-align', $pos)) {
            $pos['text-align'] = LEFT;
        }

        if (!array_key_exists('font-color', $pos)) {
            $pos['font-color'] = array(38, 38, 38);
        }
        if (!array_key_exists('font-file', $pos)) {
            $pos['font-file'] = "public/fonts/Xing.ttf";
        }
        if (!array_key_exists('lineSpace', $pos)) {
            $pos['lineSpace'] = 50;
        }

        $font_color = imagecolorallocate($image, $pos['font-color'][0], $pos['font-color'][1], $pos['font-color'][2]);
        $_string = '';
        $offsetY = 0;

        for ($i = 0; $i < mb_strlen($string, "utf-8"); $i++) {
            $box = imagettfbbox($pos['font-size'], 0, $pos['font-file'], $_string);
            $_string_width = $box[2] - $box[0];
            $_string_height = $box[3] - $box[5];
            $box = imagettfbbox($pos['font-size'], 0, $pos['font-file'], mb_substr($string, $i, 1, "utf-8"));

            $char = mb_substr($string, $i, 1, "utf-8");

            $x = $this->get_txt_pos($pos['text-align'], $pos['left'], $pos['width'], $_string_width);
            // place new line using custom line space
            if ($char == "\n") {
                imagettftext(
                    $image,
                    $pos['font-size'],
                    0,
                    $x,
                    $pos['top'] + $offsetY,
                    $font_color,
                    $pos['font-file'],
                    $_string);

                if ($nowrap) {
                    return $pos['top'] + $offsetY + $_string_height;
                }

                $offsetY += $pos['lineSpace'];
                $_string = '';
                continue;
            }
            // when char is number, not wrap up
            if (preg_match("/\d/", $char)
                || preg_match("/(%7E|%60|%21|%40|%23|%24|%25|%5E|%26|%27|%2A|%28|%29|%2B|%7C|%5C|%3D|\-|_|%5B|%5D|%7D|%7B|%3B|%22|%3A|%3F|%3E|%3C|%2C|\.|%2F|%A3%BF|%A1%B7|%A1%B6|%A1%A2|%A1%A3|%A3%AC|%7D|%A1%B0|%A3%BA|%A3%BB|%A1%AE|%A1%AF|%A1%B1|%A3%FC|%A3%BD|%A1%AA|%A3%A9|%A3%A8|%A1%AD|%A3%A4|%A1%A4|%A3%A1|%E3%80%82|%EF%BC%81|%EF%BC%8C|%EF%BC%9B|%EF%BC%9F|%EF%BC%9A|%E3%80%81|%E2%80%A6%E2%80%A6|%E2%80%9D|%E2%80%9C|%E2%80%98|%E2%80%99)/", urlencode($char))
                || $_string_width + $box[2] - $box[0] < $pos['width']) {
                $_string .= $char;
            } else { // auto wrap up
                imagettftext(
                    $image,
                    $pos['font-size'],
                    0,
                    $x,
                    $pos['top'] + $offsetY,
                    $font_color,
                    $pos['font-file'],
                    $_string);

                if ($nowrap) {
                    return $pos['top'] + $offsetY + $_string_height;
                }

                $offsetY += $pos['lineSpace'];
                $_string = $char;
            }
        }
        $box = imagettfbbox($pos['font-size'], 0, $pos['font-file'], $_string);
        $_string_width = $box[2] - $box[0];
        $_string_height = $box[3] - $box[5];
        $x = $this->get_txt_pos($pos['text-align'], $pos['left'], $pos['width'], $_string_width);
        imagettftext(
            $image,
            $pos['font-size'],
            0,
            $x,
            $pos['top'] + $offsetY,
            $font_color,
            $pos['font-file'],
            $_string);
        return $pos['top'] + $offsetY + $_string_height;
    }

    private function getUserPositionTable() {
        if (! $this->userPositionTable) {
            $sm = $this->getServiceLocator();
            $this->userPositionTable = $sm->get('Postcard\Model\UserPositionTable');
        }
        return $this->userPositionTable;
    }

    /**************************************************************
    *
    *  使用特定function对数组中所有元素做处理
    *  @param  string  &$array     要处理的字符串
    *  @param  string  $function   要执行的函数
    *  @return boolean $apply_to_keys_also     是否也应用到key上
    *  @access public
    *
    *************************************************************/
    private function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
    {
        static $recursive_counter = 0;
        if (++$recursive_counter > 1000) {
            die('possible deep recursion attack');
        }
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->arrayRecursive($array[$key], $function, $apply_to_keys_also);
            } else {
                $array[$key] = $function($value);
            }
      
            if ($apply_to_keys_also && is_string($key)) {
                $new_key = $function($key);
                if ($new_key != $key) {
                    $array[$new_key] = $array[$key];
                    unset($array[$key]);
                }
            }
        }
       $recursive_counter--;
    }

    /**************************************************************
    *
    *  将数组转换为JSON字符串（兼容中文）
    *  @param  array   $array      要转换的数组
    *  @return string      转换得到的json字符串
    *  @access public
    *
    *************************************************************/
    private function JSON($array) {
        $this->arrayRecursive($array, 'urlencode', true);
        $json = json_encode($array);
        return urldecode($json);
    }

//    private function qrcode($str, $filename = false)
//    {
//        $this->getUtil()->qrcode($str, $filename);
//
//        $viewModel = new ViewModel();
//        $viewModel->setTerminal(true); // disable layout template
//        return $viewModel;
//    }

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

    private function getUtil()
    {
        if (!$this->util) {
            $this->util = new CommonUtil();
            $this->util->setServiceLocator($this->getServiceLocator());
        }
        return $this->util;
    }

    private function getWXQrImage($sceneId, $fileName)
    {
        $tempJson = '{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": '.$sceneId.'}}}';
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".$this->getUtil()->getAccessToken();
        $tempArr = json_decode($res = $this->getUtil()->httpPost($url, $tempJson), true);
// not ask for qr code from wx server, we generate it locally.
//        if (@array_key_exists('ticket', $tempArr)) {
//            return 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$tempArr['ticket'];
//        } else {
//            var_dump($tempArr);
//            return null;
//        }
        if (@array_key_exists('url', $tempArr)) {
//            var_dump($tempArr);
            $this->getUtil()->qrcode($tempArr['url'], $fileName);
        }
    }

    private function postcardsPath($orderId = null)
    {
        $path = dirname(__FILE__).'/../../../../../userdata';
        if (!$this->checkPath($path)) {
            return false;
        }
        $path = $path.'/postcards';
        if (!$this->checkPath($path)) {
            return false;
        }

        if ($orderId == null) {
            $path = $path.'/'.date('Ymd', time());
            if (!$this->checkPath($path)) {
                return false;
            }
            return $path.'/';
        }

        $dateStr = '20'.substr($orderId, 0, 6);
        $year  = ((int)substr($dateStr, 0, 4));
        $month = ((int)substr($dateStr, 4, 2));
        $day   = ((int)substr($dateStr, 6, 2));
        $time  = mktime(0, 0, 0, $month, $day, $year);
        $orderDate = date("Ymd", $time);
        $path  = $path.'/'. $orderDate;
        $this->checkPath($path);
        return $path . '/';
    }

    private function endWith($haystack, $needle)
    {
        $length = strlen($needle);
        if($length == 0)
        {
            return true;
        }
        return (substr($haystack, -$length) === $needle);
    }
}

