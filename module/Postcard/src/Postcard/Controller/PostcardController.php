<?php
namespace Postcard\Controller;

include_once(dirname(__FILE__)."/../../../../Wxpay/view/wxpay/wxpay/CommonUtil.php");
// include_once(dirname(__FILE__)."/../../../../Wxpay/view/wxpay/wxpay/WxPayHelper.php");

use Imagick;
use CommonUtil;
use WxPayHelper;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Postcard\Model\Order;
use Postcard\Model\Contact;
use Postcard\Libs\PinYin;

define('DEFAULT_PICURL', 'http://pic.sc.chinaz.com/files/pic/pic9/201405/apic3699.jpg');
define('DEFAULT_USER', 'default_user_openid'); // default user (my openid is ocKsTuKbE4QqHbwGEXmVnuLHO_sY)

// order status
define('CANCEL',   99); // 已取消
define('UNPAY',   100); // 待支付
define('PAYED',   101); // 已支付
define('PRINTED', 102); // 已打印
define('SHIPPED', 103); // 已发货

define('JS_TAG', '201411191311'); // 好像不管用，待查


class PostcardController extends AbstractActionController
{
    protected $orderTable;
    protected $userPositionTable;
    protected $contactTable;

    public function voiceQrCodeAction()
    {
        $mediaId = $this->getRequest()->getQuery('mediaId', '0');
        if ($mediaId == '0') {
            $view =  new ViewModel(array('code' => 1, 'msg' => 'require media id'));
            $view->setTemplate('postcard/postcard/error');
            return $view;
        }
        $image = file_get_contents('./userdata/voice/'.$mediaId.'.png');
        header("Content-type: image/png");
        echo $image;
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true); // disable layout template
        return $viewModel;
    }

    public function voiceAction()
    {
        $mediaId = $this->getRequest()->getQuery('mediaId', '0');
        if ($mediaId == '0') {
            $view =  new ViewModel(array('code' => 1, 'msg' => 'require media id'));
            $view->setTemplate('postcard/postcard/error');
            return $view;
        }

        $fileName = $this->voicePath().$mediaId.'.mp3';
        if (!file_exists($fileName)) {
            echo 'file '.$fileName.' not exist!';
            return;
        }
        $amr = file_get_contents($fileName);
        header("Content-type: audio/mp3");
        echo $amr;
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true); // disable layout template
        return $viewModel;
    }

    public function requestVoiceAction()
    {
        $orderId = $this->params()->fromRoute('id', '0');

        $order = $this->getOrderTable()->getOrder($orderId);
        if ($orderId == '0' || !$order) {
            $view =  new ViewModel(array('code' => 1, 'msg' => 'invalid order id '.$orderId));
            $view->setTemplate('postcard/postcard/error');
            return $view;
        }

        // var_dump($order);
        $util = new CommonUtil();
        $util->setServiceLocator($this->getServiceLocator());
        $token = $util->getAccessToken();

        // $result = json_decode($util->asyn_request($args));
        $res = $util->httpPost('https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$token,
                                $this->JSON(array(
                                                'touser'  => $order->userName,
                                                'msgtype' => 'text',
                                                'text'    => array('content' => '请说出你的语音留言' ),
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
            $selectedTemplateIndex = 0;
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
            'picurl' => $picUrl,
            'username' => $this->getRequest()->getQuery('username', DEFAULT_USER),
            'tag' => JS_TAG, // if only want update 'kacha.js', modify the tag.   ????????   not work
        ));
        $viewModel->setTerminal(true); // disable layout template
        return $viewModel;
    }

    public function editPostcardAction()
    {
        $orderId = $this->params()->fromRoute("id", "0");
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

        // update mediaId. Media will valid for 3 days on Tecent's server.
        $voiceMediaId = $this->getRequest()->getQuery('voiceMediaId');
        if ($voiceMediaId) {
            $order->voiceMediaId = $voiceMediaId;
            $this->getOrderTable()->saveOrder($order);
        }

        $viewModel =  new ViewModel(array(
            'order' => $order,
            'tag'   => JS_TAG, // if only want update x.js, modify the tag.   ????????   not work
        ));
        $viewModel->setTerminal(true); // disable layout template
        return $viewModel;
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
            ),
        ));
    }

    public function editMessageAction()
    {
        $orderId = $this->params()->fromRoute('id', '0');
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

        // update mediaId. Media will valid for 3 days on Tecent's server.
        $voiceMediaId = $this->getRequest()->getQuery('voiceMediaId');
        if ($voiceMediaId) {
            $order->voiceMediaId = $voiceMediaId;
            // var_dump($order);
            $this->getOrderTable()->saveOrder($order);
        }

        $util = new CommonUtil();
        $util->setServiceLocator($this->getServiceLocator());
//        $token = $util->getAccessToken();

        $viewModel =  new ViewModel(array(
            'order' => $order,
            'tag'   => JS_TAG, // if only want update x.js, modify the tag.   ????????   not work
//            'token' => $token,
        ));
        $viewModel->setTerminal(true); // disable layout template
        return $viewModel;
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

        $canvas_w = 960.0;
        $canvas_h = 1440.0;
        $image = $this->generateFront($order->templateId, $order->offsetX, $order->offsetY, $order->picUrl, $canvas_w, $canvas_h);
        if ($image) {
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
            $view =  new ViewModel(array('code' => 1, 'msg' => 'invalid order id '.$userName));
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
        $util = new CommonUtil();
        $util->setServiceLocator($this->getServiceLocator());
        $token = $util->getAccessToken();
        $mediaId = $this->getRequest()->getQuery('mediaId', '0');
        if ($mediaId == '0') {
            $view =  new ViewModel(array('code' => 1, 'msg' => 'require mediaId'));
            $view->setTemplate('postcard/postcard/error');
            return $view;
        }
        $voiceContent = file_get_contents('http://file.api.weixin.qq.com/cgi-bin/media/get?access_token='.$token.'&media_id='.$mediaId);
        $voiceFile = fopen($this->voicePath().$mediaId.'.amr', 'w') or die("Unable to open file!");
        // echo $this->voicePath().$mediaId.'.amr';
        $length = fwrite($voiceFile, $voiceContent);
        fclose($voiceFile);

        $cmd = 'ffmpeg -i '.$this->voicePath().$mediaId.'.amr '.$this->voicePath().$mediaId.'.mp3';
        exec($cmd);
        // generate qr code image under same folder
        $str = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].'/postcard/voice?mediaId='.$mediaId;
        // echo $str;
        $this->qrcode($str, $this->voicePath().$mediaId.'.png');

        $res = array(
            'code' => 0,
            'msg'  => 'download voice file success',
            'length' => $length,
            'url'  => $str, 
        );
        return new JsonModel($res);
    }

    public function placeOrderAction()
    {
        // cancel old order first
        $userName = $this->getRequest()->getPost('userName', DEFAULT_USER);

        $order = $this->getOrderTable()->getOrderByUserName($userName);
        while ($order) {
            $order->status = CANCEL;
            $this->getOrderTable()->saveOrder($order);
            // echo 'order: '.$order->id.' canceled.';
            $order = $this->getOrderTable()->getOrderByUserName($userName);
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
            $templateId         ? $order->templateId       = $templateId    : null;
            $offsetX            ? $order->offsetX          = $offsetX       : null;
            $offsetY            ? $order->offsetY          = $offsetY       : null;
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
            $msg = 'order '.$orderId.' not exist!';
        } else {
            $this->getOrderTable()->deleteOrder($orderId);
            $code = 0;
            $msg = 'order '.$orderId.' delete success!';
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
                                       'tansid' => $transId,
                                       'openid' => $openId,
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

    private function logger($content)
    {
        file_put_contents($this->logFileName(), date('m/d H:i:s').' '.$content."\n", FILE_APPEND); // notice: use "\n", not '\n'
    }

    private function logFileName()
    {
        return '/tmp/paohai_error.log';
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

    private function postcardsPath()
    {
        $path = dirname(__FILE__).'/../../../../../userdata';
        if (!$this->checkPath($path)) {
            return false;
        }

        $path = $path.'/postcards';
        if (!$this->checkPath($path)) {
            return false;
        }

        $path = $path.'/'.date('Ymd', time());
        if (!$this->checkPath($path)) {
            return false;
        }

        return $path.'/';
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
        $dstpath = $this->postcardsPath();
        if (!is_dir($dstpath)) {
            if (!mkdir($dstpath)) {
                echo 'Create folder '.$dstpath.' failed!';
                $this->logger('Create folder '.$dstpath.' failed!');
                return false;
            }
        }

        $date = date('YmdHis',time());
        $canvas_w = 1970.6;
        $canvas_h = 2880.0;

        $image = $this->generateFront($order, $canvas_w, $canvas_h);
        if (!$image) {
            $this->logger('makePicture/generateFront failed!');
            return false;
        }

        imagejpeg($image, $dstpath.$order->id.'_front.jpg');
        imagedestroy($image);

        $this->adjustBrightness($dstpath.$order->id.'_front.jpg', $dstpath.$order->id.'_bright.jpg');

        $canvas_w = 971.0;
        $canvas_h = 600.0;
        $image = $this->generatePostcardBack($order, $canvas_w, $canvas_h);
        imagejpeg($image, $dstpath.$order->id.'_backface.jpg');
        imagedestroy($image);
        return true;
    }

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

        $image_template = imagecreatefrompng('public/images/big/template' . $order->templateId . '.png');
        imagealphablending($image_template, false);
        imagesavealpha($image_template, true);

        $image_user = imagecreatefromjpeg($order->picUrl);
        if (!$image_user) {
            return FALSE;
        }
        // rotate
        if ($order->templateId > 6) {
            $image_user = imagerotate($image_user, -90, 0);
        }

        $a = imagesx($image_user);
        $b = imagesy($image_user);
        $wRatio = $canvas_w / $a;
        $hRatio = $canvas_h / $b;
        $ratio = $wRatio > $hRatio ? $wRatio : $hRatio;
        $pic_w = $a * $ratio;
        $pic_h = $b * $ratio;
        // var_dump($a);
        // var_dump($b);
        // var_dump($canvas_w);
        // var_dump($canvas_h);
        // var_dump($wRatio);
        // var_dump($hRatio);
        // var_dump($ratio);
        // var_dump($pic_w);
        // var_dump($pic_h);

        $image_dst = imageCreatetruecolor($canvas_w, $canvas_h); // canvas
        imagealphablending($image_dst, true);
        $white = imagecolorallocate($image_dst, 255, 255, 255);
        imagefill($image_dst, 0, 0, $white);

        // 用户照片缩放fitsize
        $x = -$order->offsetX * imagesx($image_user);
        $y = -$order->offsetY * imagesy($image_user);

        imagecopyresampled($image_dst, $image_user, 0, 0, $x, $y, $pic_w, $pic_h, $a, $b);
        imagecopyresampled($image_dst, $image_template, 0, 0, 0, 0, $canvas_w, $canvas_h, imagesx($image_template), imagesy($image_template));

        imagedestroy($image_template);
        imagedestroy($image_user);
        return $image_dst;
    }

    private function generatePostcardBack($order, $canvas_w, $canvas_h)
    {
        $dst = imagecreatetruecolor($canvas_w, $canvas_h);
        $white = imagecolorallocate($dst, 255, 255, 255);
        imagefill($dst, 0, 0, $white);
/* for test
        $background = imagecreatefromjpeg('public/images/big/backface.jpg');
        imagealphablending($background, false);
        imagesavealpha($background, true);
        imagecopyresampled($dst, $background, 0, 0, 0, 0, $canvas_w, $canvas_h, imagesx($background), imagesy($background));
*/
        // can't use imagettftext because it can't adjust char spacing
        $size = 40;
        $x = 85;
        $y = 95;
        $space = 8;
        $font = imagepsloadfont("public/fonts/Schneidler-HTF-Titling.pfb");
        if (!$font) {
            echo 'Load font Schneidler-HTF-Titling.pfb failed.';
        }
        // zip code
        $zip_color = imagecolorallocate($dst, 38, 38, 38);
        imagepstext($dst, $order->zipCode, $font, $size, $zip_color, $white, $x ,$y, $space, 870);

        // salutation
        $pos['left']     = 30;
        $pos['top']      = 155;
        $pos['width']    = 450;
        $pos['fontSize'] = 20;
        $this->draw_txt_to($dst, $pos, $order->salutation);
        // message
        $pos['left']     = 30;
        $pos['top']      = 200;
        $pos['width']    = 450;
        $pos['fontSize'] = 20;
        $this->draw_txt_to($dst, $pos, $order->message);
        // signature
        $pos['left']     = 350;
        $pos['top']      = 500;
        $pos['width']    = 300;
        $pos['fontSize'] = 20;
        $this->draw_txt_to($dst, $pos, '－'.$order->signature);
        // recipient address
        $pos['left']     = 500;
        $pos['top']      = 250;
        $pos['width']    = 400;
        $pos['fontSize'] = 20;
        $this->draw_txt_with_linespace($dst, $pos, $order->address, 55);
        // recipient name
        $pos['left']     = 650;
        $pos['top']      = 400;
        $pos['width']    = 600;
        $pos['fontSize'] = 30;
        $this->draw_txt_to($dst, $pos, $order->recipient);
        // qr code
        if ($order->voiceMediaId && file_exists($this->voicePath().$order->voiceMediaId.'.png')) {
            $image_pr = imagecreatefrompng($this->voicePath().$order->voiceMediaId.'.png');
            imagecopyresampled($dst, $image_pr, 30, 450, 0, 0, 120, 120, imagesx($image_pr), imagesy($image_pr));

            $pos['left']     = 50;
            $pos['top']      = 570;
            $pos['width']    = 120;
            $pos['fontSize'] = 11;
            $this->draw_txt_to($dst, $pos, '扫扫听留言');
        }

        // Commemorative Chop
        if ($order->postmarkId != null) {
            $image = imagecreatefrompng('public/images/postmark/big/youchuo'.$order->postmarkId.'.png');
            $postmark_w = 170;
            $postmark_h = imagesy($image) / imagesx($image) * $postmark_w;
            imagecopyresampled($dst, $image, 750, $canvas_h - $postmark_h - 30, 0, 0, $postmark_w, $postmark_h, imagesx($image), imagesy($image));

            $textAttr = $this->getDateTextAttr($order->postmarkId);
            $this->draw_txt_to($dst, $textAttr, date($textAttr['dateFormat'], time()));
        } else {

            // location postmark
            $location = NULL;
            $util = new CommonUtil();
            $util->setServiceLocator($this->getServiceLocator());
            $location = $util->getUserGeoAddress($order->userName);

            if ($location != NULL) {
                $postmark['left']     = 610;
                $postmark['top']      = 503;
                $postmark['width']    = 600;
                $postmark['fontSize'] = 11;
                $this->draw_txt_to($dst, $postmark, $location['city']);

                $postmark['left']     = 590;
                $postmark['top']      = 530;
                $postmark['width']    = 600;
                $postmark['fontSize'] = 8;
                $this->draw_txt_to($dst, $postmark, strtoupper(PinYin::Pinyin($location['city'], 1)));

                $postmark['left']     = 600;
                $postmark['top']      = 544;
                $postmark['width']    = 600;
                $postmark['fontSize'] = 9;
                $this->draw_txt_to($dst, $postmark, date('Y.m.d', time()));

                $imageName = 'postmark_location.png';
                $image = imagecreatefrompng('public/images/postmark/big/'.$imageName);
                $postmark_h = 120;
                $postmark_w = imagesx($image) / imagesy($image) * $postmark_h;
                imagecopyresampled($dst, $image, 520, $canvas_h - $postmark_h - 30, 0, 0, $postmark_w, $postmark_h, imagesx($image), imagesy($image));
            }
        }
        return $dst;
    }

    private function getDateTextAttr($postmarkId)
    {
        $dateTextArray = array(
            array(
//                'text'     => '成都',
                'left'     => 831,
                'top'      => 517,
                'width'    => 600,
                'fontSize' => 9,
                'fontColor' => array(152, 45, 35),
                'dateFormat' => 'Y.m.d',
            ),

            array(
//                'text'     => '三亚',
                'left'     => 842,
                'top'      => 542,
                'width'    => 600,
                'fontSize' => 8,
                'fontColor' => array(7, 111, 70),
                'dateFormat' => 'Y.m.d',
            ),

            array(
//                'text'     => '杭州',
                'left'     => 860,
                'top'      => 505,
                'width'    => 600,
                'fontSize' => 10,
                'fontColor' => array(134, 91, 67),
                'dateFormat' => 'Y.m.d',
            ),

            array(
//                'text'     => '北京',
                'left'     => 792,
                'top'      => 545,
                'width'    => 600,
                'fontSize' => 10,
                'fontColor' => array(68, 67, 67),
                'dateFormat' => 'Y.m.d',
            ),

            array(
                'text'     => '广州',
                'left'     => 845,
                'top'      => 515,
                'width'    => 600,
                'fontSize' => 10,
                'fontColor' => array(60, 60, 60),
                'dateFormat' => 'Y.m.d',
            ),

            array(
//                'text'     => '上海',
                'left'     => 816,
                'top'      => 547,
                'width'    => 600,
                'fontSize' => 10,
                'fontColor' => array(62, 62, 62),
                'dateFormat' => 'Y    m.d',
            ),

            array(
//                'text'     => '深圳',
                'left'     => 848,
                'top'      => 508,
                'width'    => 600,
                'fontSize' => 10,
                'fontColor' => array(60, 60, 60),
                'dateFormat' => 'Y.m.d',
            ),
        );
        if ($postmarkId < count($dateTextArray)) {
            return $dateTextArray[$postmarkId];
        } else {
            return NULL;
        }
    }

    private function draw_txt_to($image, $pos, $string)
    {
        if (!array_key_exists('fontColor', $pos)) {
            $pos['fontColor'] = array(38, 38, 38);
        }
        $font_color = imagecolorallocate($image, $pos['fontColor'][0], $pos['fontColor'][1], $pos['fontColor'][2]);
        $font_file = "public/fonts/Kaiti.ttc";
        $_string = '';
        $__string = '';
        for ($i = 0; $i < mb_strlen($string, "utf-8"); $i++) {
            $box = imagettfbbox($pos['fontSize'], 0, $font_file, $_string);
            $_string_length = $box[2] - $box[0];
            $box = imagettfbbox($pos['fontSize'], 0, $font_file, mb_substr($string, $i, 1, "utf-8"));

            if ($_string_length + $box[2] - $box[0] < $pos['width']) {
                $_string .= mb_substr($string, $i, 1, "utf-8");
            } else {
                $__string .= $_string . "\n";
                $_string = mb_substr($string, $i, 1, "utf-8");
            }
        }
        $__string .= $_string;
        $box = imagettfbbox($pos['fontSize'], 0, $font_file, mb_substr($__string, 0, 1, "utf-8"));
        imagettftext(
            $image,
            $pos['fontSize'],
            0,
            $pos['left'],
            $pos['top'] + ($box[3] - $box[7]),  
            $font_color,
            $font_file, 
            $__string);
    }

    private function draw_txt_with_linespace($image, $pos, $string, $lineSpace)
    {
        if (!array_key_exists('fontColor', $pos)) {
            $pos['fontColor'] = array(38, 38, 38);
        }
        $font_color = imagecolorallocate($image, $pos['fontColor'][0], $pos['fontColor'][1], $pos['fontColor'][2]);
        $font_file = "public/fonts/Kaiti.ttc";
        $_string = '';
        $offsetY = 0;

        for ($i = 0; $i < mb_strlen($string, "utf-8"); $i++) {
            $box = imagettfbbox($pos['fontSize'], 0, $font_file, $_string);
            $_string_width = $box[2] - $box[0];
            $box = imagettfbbox($pos['fontSize'], 0, $font_file, mb_substr($string, $i, 1, "utf-8"));

            $char = mb_substr($string, $i, 1, "utf-8");
            if (preg_match("/\d/", $char) || $_string_width + $box[2] - $box[0] < $pos['width']) {
                $_string .= $char;
            } else {
                imagettftext(
                    $image,
                    $pos['fontSize'],
                    0,
                    $pos['left'],
                    $pos['top'] + $offsetY,
                    $font_color,
                    $font_file,
                    $_string);
                $offsetY += $lineSpace;
                $_string = $char;
            }
        }

        imagettftext(
            $image,
            $pos['fontSize'],
            0,
            $pos['left'],
            $pos['top'] + $offsetY,
            $font_color,
            $font_file,
            $_string);
    }


    private function getUserPositionTable() {
        if ( ! $this->userPositionTable) {
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

    private function qrcode($str, $filename = false)
    {
        $util = new CommonUtil();
        $util->qrcode($str, $filename);

        $viewModel = new ViewModel();
        $viewModel->setTerminal(true); // disable layout template
        return $viewModel;
    }
}
