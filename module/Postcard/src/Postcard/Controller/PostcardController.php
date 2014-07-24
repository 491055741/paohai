<?php
namespace Postcard\Controller;

include_once(dirname(__FILE__)."/../../../../Wxpay/view/wxpay/wxpay/CommonUtil.php");
use CommonUtil;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Postcard\Model\Order;

define('DEFAULT_PICURL', 'http://pic.sc.chinaz.com/files/pic/pic9/201405/apic3699.jpg');
define('DEFAULT_MSG', '思念是一季的花香，漫过山谷，笼罩你我，而祝福是无边的关注，溢出眼睛，直到心底，愿愉快伴你一生。');
define('DEFAULT_BANK', 'other');
define('DEFAULT_USER', 'ocKsTuKbE4QqHbwGEXmVnuLHO_sY'); // default user is me
define('DEFAULT_ZIPCODE', '518000');
define('DEFAULT_SENDER', 'sender');
define('DEFAULT_ADDRESS', 'address');
define('DEFAULT_RECIPIENT', 'recipient');

// order status 待支付，已支付，已打印，已发货，已收货，退款状态
define('CANCEL', 99);
define('UNPAY', 100);
define('PAYED', 101);
define('PRINTED', 102);
define('SHIPPED', 103);

define('JS_TAG', '20140717');


class PostcardController extends AbstractActionController
{
    protected $orderTable;

    public function voiceQrCodeAction()
    {
        $mediaId = $this->getRequest()->getQuery('mediaId', '0');
        if ($mediaId == '0') {
            echo 'Invalid mediaId';
            return;
        }
        // $this->qrcode('http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].'/postcard/voiceqrcode&media_id='.$mediaId, './userdata/voice/'.$mediaId.'.png');
        $image = file_get_contents('./userdata/voice/'.$mediaId.'.png');
        // var_dump($image);
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
            echo 'Invalid mediaId';
            return;
        }
        // $this->qrcode('http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].'/postcard/voiceqrcode&media_id='.$mediaId, './userdata/voice/'.$mediaId.'.png');
        $fileName = $this->voicePath().$mediaId.'.mp3';

        if (!file_exists($fileName)) {
            echo 'file '.$fileName.' not exist!';
            return;
        }
        // echo $fileName;
        $amr = file_get_contents($fileName);
        // var_dump($amr);    
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
            echo 'invalid order id';
            // $viewModel =  new ViewModel();
            // $viewModel->setTerminal(true); // disable layout template
            // return $viewModel;
            return;
        }

        // var_dump($order);
        $util = new CommonUtil();
        $util->setServiceLocator($this->getServiceLocator());
        $token = $util->getAccessToken();

        $args["host"] = 'api.weixin.qq.com';
        $args["url"] = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$token;
        $args["method"] = "POST";
        // var_dump($order);
        // for Chinese value, don't use json_encode, otherwise Chinese will be convert to \u8bf7\u8bf4...
        $args["data"] = $this->JSON(array(
                'touser'  => $order->userName,
                'msgtype' => 'text',
                'text'    => array('content' => '请说出你的语音留言' ),
                ));

        $result = json_decode($util->asyn_request($args));
        $array = $this->object2array($result);
        return new JsonModel($array);
    }

    public function indexAction()
    {
        $viewModel =  new ViewModel(array(
            'picurl' => $this->getRequest()->getQuery('picurl', DEFAULT_PICURL),
            'username' => $this->getRequest()->getQuery('username', DEFAULT_USER),
            'tag' => JS_TAG, // if only want update 'kacha.js', modify the tag.   ????????   not work
        ));
        $viewModel->setTerminal(true); // disable layout template
        return $viewModel;
    }

    public function editMessageAction()
    {
        $orderId = $this->params()->fromRoute('id', '0');
        $order = $this->getOrderTable()->getOrder($orderId);
        if ($orderId == '0' || !$order) {
            echo 'invalid order id';
            $viewModel =  new ViewModel();
            $viewModel->setTerminal(true); // disable layout template
            return $viewModel;
        }
        // update mediaId. Media will valid for 3 days on Tecent's server.
        $voiceMediaId = $this->getRequest()->getQuery('voiceMediaId');
        if ($voiceMediaId) {
            $order->voiceMediaId = $voiceMediaId;
            // var_dump($order);
            $this->getOrderTable()->saveOrder($order);
        }

        $viewModel =  new ViewModel(array(
            'orderId' => $orderId,
            'tag' => JS_TAG, // if only want update x.js, modify the tag.   ????????   not work
            'templateIndex' => $order->templateId,
            'offsetX' => $order->offsetX,
            'offsetY' => $order->offsetY,
            'picUrl'  => $order->picUrl,
            'voiceMediaId' => $order->voiceMediaId ? $order->voiceMediaId : '0',
        ));
        // var_dump($viewModel);
        $viewModel->setTerminal(true); // disable layout template
        return $viewModel;
    }

    public function payAction()
    {
        $orderId = $this->params()->fromRoute('id', '0');
        $order = $this->getOrderTable()->getOrder($orderId);
        if ($orderId == '0' || !$order) {
            echo 'not valid order id';
            return;
        }

        $this->confirmOrder($order);
        $viewModel =  new ViewModel(array(
            'orderId' => $orderId,
            'tag' => '201405291059', // if only want update 'kacha.js', modify the tag.   ????????   not work
        ));
        $viewModel->setTerminal(true); // disable layout template
        return $viewModel;
    }

    public function previewAction()
    {
        $orderId = $this->params()->fromRoute('id', '0');
        $order = $this->getOrderTable()->getOrder($orderId);
        if ($orderId == '0' || !$order) {
            echo 'invalid order id';
            return;
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

    public function makePictureAction()
    {
        $orderId = $this->params()->fromRoute('id', '0');
        $order = $this->getOrderTable()->getOrder($orderId);
        if ($orderId == '0' || !$order) {
            echo "order not exist!";
            return;
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
            echo 'invalid media id';
            return;
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
        $order->templateId = $this->getRequest()->getPost('templateIndex', '1');
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
            echo "order not exist!";
        } else {
            $zipCode    = $this->getRequest()->getPost('zipcode');
            $message    = $this->getRequest()->getPost('message');
            $sender     = $this->getRequest()->getPost('sender');
            $address    = $this->getRequest()->getPost('address');
            $recipient  = $this->getRequest()->getPost('recipient');
            $userName   = $this->getRequest()->getPost('userName');
            $picUrl     = $this->getRequest()->getPost('userPicUrl');
            $bank       = $this->getRequest()->getPost('bank');

            $zipcode   ? $order->zipCode   = $zipCode   : null;
            $message   ? $order->message   = $message   : null;
            $sender    ? $order->sender    = $sender    : null;
            $address   ? $order->address   = $address   : null;
            $recipient ? $order->recipient = $recipient : null;
            $userName  ? $order->userName  = $userName  : null;
            $picUrl    ? $order->picUrl    = $picUrl    : null;
            $status    ? $order->status    = $status    : null;
            $bank      ? $order->bank      = $bank      : null;

            // var_dump($order);
            $this->getOrderTable()->saveOrder($order);
            echo "order update success!";
        }

        $res = array(
            'code' => 0,
            'msg' => 'success',
        );
        return new JsonModel($res);
    }

    private function confirmOrder($order)
    {
        // make picture
        $args["host"] = $_SERVER['SERVER_NAME'];
        $args["url"] = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].'/postcard/makepicture/'.$order->id;
        $args["method"] = "POST";
        $util = new CommonUtil();
        $util->asyn_request($args);
    }

    public function deleteAction()
    {
        $orderId = $this->params()->fromRoute('id', '0');
        $order = $this->getOrderTable()->getOrder($orderId);
        if ($orderId == '0' || !$order) {
            echo "order not exist!";
        } else {
            $this->getOrderTable()->deleteOrder($orderId);
            echo "order delete success!";
        }

        $viewModel = new ViewModel();
        $viewModel->setTerminal(true); // disable layout template
        return $viewModel;
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
            }
        }

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

    private function dstPath()
    {
        return dirname(__FILE__).'/../../../../../userdata/postcards/' . date('Ymd', time()) . '/';
    }

    private function voicePath()
    {
        return dirname(__FILE__).'/../../../../../userdata/voice/';
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
        $dstpath = $this->dstPath();
        if (!is_dir($dstpath)) {
            if (!mkdir($dstpath)) {
                echo 'Create folder' . $dstpath . 'failed!';
                return false;
            }
        }

        $date = date('YmdHis',time());
        $canvas_w = 960.0;
        $canvas_h = 1440.0;

        $image = $this->generateFront($order->templateId, $order->offsetX, $order->offsetY, $order->picUrl, $canvas_w, $canvas_h);
        if (!$image) {
            return false;
        }

        imagepng($image, $dstpath.$order->id.'_front.png');
        imagedestroy($image);

        $canvas_w = 971.0;
        $canvas_h = 600.0;
        $image = $this->generatePostcardBack($order->zipcode, $order->message, $order->sender, $order->address, $order->recipient, $canvas_w, $canvas_h);
        // $image = generatePostcardBack('518000', '思念是一季的花香，漫过山谷，笼罩你我，而祝福是无边的关注，溢出眼睛，直到心底，愿愉快伴你一生。', '李生', '上海杨浦区淞沪路303号创智天地三期8号楼8楼', '泡泡海', $canvas_w, $canvas_h);
        imagepng($image, $dstpath.$order->id.'_backface.png');
        imagedestroy($image);
        return true;
    }

    private function generateFront($templateIndex, $offsetX, $offsetY, $userPicUrl, $canvas_w, $canvas_h)
    {
        if (abs($offsetX) >= 1 || abs($offsetY) >= 1) {
            echo 'wrong offset!';
            return FALSE;
        }

        $image_template = imagecreatefrompng('public/images/big/template' . $templateIndex . '.png');
        imagealphablending($image_template, false);
        imagesavealpha($image_template, true);

        $image_user = imagecreatefromjpeg($userPicUrl);
        if (!$image_user) {
            return FALSE;
        }

        if ($templateIndex >= 3) {
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
        $x = -$offsetX * imagesx($image_user);
        $y = -$offsetY * imagesy($image_user);

        imagecopyresampled($image_dst, $image_user, 0, 0, $x, $y, $pic_w, $pic_h, $a, $b);
        imagecopyresampled($image_dst, $image_template, 0, 0, 0, 0, $canvas_w, $canvas_h, imagesx($image_template), imagesy($image_template));

        imagedestroy($image_template);
        imagedestroy($image_user);
        return $image_dst;
    }

    private function generatePostcardBack($zipcode, $message, $sender, $address, $recipient, $canvas_w, $canvas_h)
    {
        $dst = imagecreatetruecolor($canvas_w, $canvas_h);

        $white = imagecolorallocate($dst, 255, 255, 255);
        imagefill($dst, 0, 0, $white);

        // use backface template
        // $backImg = imagecreatefrompng("public/images/big/postCardBack.png");
        // imagealphablending($backImg, false);
        // imagesavealpha($backImg, true);
        // imagecopyresampled($dst, $backImg, 0, 0, 0, 0, $canvas_w, $canvas_h, imagesx($backImg), imagesy($backImg));

        $text_color = imagecolorallocate($dst, 255, 255, 255);
        $pos['color'] = $text_color;
        // $zipcode = "152000";   // can't use imagettftext because it can't adjust char spacing
        $size = 40;
        $x = 85;
        $y = 95;
        $space = 8;
        $font = imagepsloadfont("public/fonts/Schneidler-HTF-Titling.pfb");
        if (!$font) {
            echo 'Load font Schneidler-HTF-Titling.pfb failed.';
        }
        $zip_color = imagecolorallocate($dst, 0, 0, 0);
        imagepstext($dst, $zipcode, $font, $size, $zip_color, $text_color, $x ,$y, $space, 870);

        // $message = "思念是一季的花香，漫过山谷，笼罩你我，而祝福是无边的关注，溢出眼睛，直到心底，愿愉快伴你一生。";
        $pos['left']     = 30;
        $pos['top']      = 200;
        $pos['width']    = 450;
        $pos['fontsize'] = 20;
        $this->draw_txt_to($dst, $pos, $message);

        // $sender = "李生";
        $sender = '－' . $sender;
        $pos['left']     = 350;
        $pos['top']      = 400;
        $pos['width']    = 300;
        $pos['fontsize'] = 20;
        $this->draw_txt_to($dst, $pos, $sender);

        // $address = "上海杨浦区淞沪路303号创智天地三期8号楼8楼";
        $pos['left']     = 500;
        $pos['top']      = 250;
        $pos['width']    = 400;
        $pos['fontsize'] = 20;
        $this->draw_txt_to($dst, $pos, $address);

        // $recipient = "泡泡海";
        $pos['left']     = 600;
        $pos['top']      = 400;
        $pos['width']    = 600;
        $pos['fontsize'] = 30;
        $this->draw_txt_to($dst, $pos, $recipient);
        return $dst;
    }

    private function draw_txt_to($image, $pos, $string)
    {
        $font_color = imagecolorallocate($image, $pos['color'][0], $pos['color'][1], $pos['color'][2]);
        $font_file = "public/fonts/Kaiti.ttc";
        $_string = '';
        $__string = '';
        for ($i = 0; $i < mb_strlen($string); $i++) {
            $box = imagettfbbox($pos['fontsize'], 0, $font_file, $_string);
            $_string_length = $box[2] - $box[0];
            $box = imagettfbbox($pos['fontsize'], 0, $font_file, mb_substr($string, $i, 1, "utf-8"));

            if ($_string_length + $box[2] - $box[0] < $pos['width']) {
                $_string .= mb_substr($string, $i, 1, "utf-8");
            } else {
                $__string .= $_string . "\n";
                $_string = mb_substr($string, $i, 1, "utf-8");
            }
        }
        $__string .= $_string;
        $box = imagettfbbox($pos['fontsize'], 0, $font_file, mb_substr($__string, 0, 1, "utf-8"));
        imagettftext(
            $image,
            $pos['fontsize'],
            0,
            $pos['left'],
            $pos['top'] + ($box[3] - $box[7]),  
            $font_color,
            $font_file, 
            $__string);
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