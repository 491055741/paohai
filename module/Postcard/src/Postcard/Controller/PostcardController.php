<?php
namespace Postcard\Controller;

include_once(dirname(__FILE__)."/../../../../Wxpay/view/wxpay/wxpay/CommonUtil.php");
use CommonUtil;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Postcard\Model\Order;

define("DEFAULT_PICURL", 'http://pic.sc.chinaz.com/files/pic/pic9/201405/apic3699.jpg');
define("DEFAULT_MSG", '思念是一季的花香，漫过山谷，笼罩你我，而祝福是无边的关注，溢出眼睛，直到心底，愿愉快伴你一生。');
define("DEFAULT_BANK", 'other');
define("DEFAULT_USER", "ocKsTuKbE4QqHbwGEXmVnuLHO_sY"); // default user is me
// order status 待支付，已支付，已打印，已发货，已收货，退款状态
define("UNPAY", 100);
define("PAYED", 101);
define("PRINTED", 102);
define("SHIPPED", 103);


class PostcardController extends AbstractActionController
{
    protected $orderTable;

    public function voiceAction()
    {
        $commonUtil = new CommonUtil();
        $commonUtil->setServiceLocator($this->getServiceLocator());
        $token = $commonUtil->getAccessToken();

        $args["host"] = 'api.weixin.qq.com';
        $args["url"] = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$token;
        $args["method"] = "POST";

        // for Chinese value, don't use json_encode, otherwise Chinese will be convert to \u8bf7\u8bf4...
        $args["data"] = $this->JSON(array(
                'touser'  => $this->getRequest()->getQuery('username', DEFAULT_USER),
                'msgtype' => 'text',
                'text'    => array('content' => '请说出你的语音留言' ),
                ));

        $result = json_decode($this->asyn_request($args));
        $array = $this->object2array($result);
        return new JsonModel($array);
    }

    public function indexAction()
    {
        return $this->newPostcard();
    }

    private function newPostcard()
    {
        $viewModel =  new ViewModel(array(
            'picurl' => $this->getRequest()->getQuery('picurl', DEFAULT_PICURL),
            'username' => $this->getRequest()->getQuery('username', 'tester'),
            'tag' => '201405291059', // if only want update 'kacha.js', modify the tag.   ????????   not work
        ));
        $viewModel->setTerminal(true); // disable layout template
        return $viewModel;
    }

    public function previewAction()
    {
        $templateIndex = $this->getRequest()->getQuery('templateIndex', 1);
        $offsetX = $this->getRequest()->getQuery('offsetX', 0);
        $offsetY = $this->getRequest()->getQuery('offsetY', 0);
        $userPicUrl = $this->getRequest()->getQuery('userPicUrl', DEFAULT_PICURL);
        $canvas_w = 960.0;
        $canvas_h = 1440.0;
        $image = $this->generateFront($templateIndex, $offsetX, $offsetY, $userPicUrl, $canvas_w, $canvas_h);
        if ($image) {
            $para = array(
                'msg'   => '',
                'image' => $image,
            );
            header("Content-type: image/png");
            imagepng($image);
            imagedestroy($image);
        } else {
            $para = array(
                'msg'   => 'generate image failed.',
            );
        }
        $viewModel = new ViewModel($para);
        $viewModel->setTerminal(true); // disable layout template
        return $viewModel;
    }

    public function ordersAction()
    {
        return new ViewModel(array(
            'orders' => $this->getOrderTable()->fetchAll(),
        ));
    }

    public function makePictureAction()
    {
        if (!$this->makePicture()) {
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

    public function placeOrderAction()
    {
        $args["host"] = $_SERVER['SERVER_NAME'];
        $args["url"] = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].'/postcard/makepicture';
        $args["method"] = "POST";
        $args["data"] = array(
            'templateIndex' => $this->getRequest()->getPost('templateIndex', 1),
            'offsetX'       => $this->getRequest()->getPost('offsetX', 0),
            'offsetY'       => $this->getRequest()->getPost('offsetY', 0),
            'userPicUrl'    => $this->getRequest()->getPost('userPicUrl', DEFAULT_PICURL),
            'zipcode'       => $this->getRequest()->getPost('zipcode', '610041'),
            'message'       => $this->getRequest()->getPost('message', DEFAULT_MSG),
            'sender'        => $this->getRequest()->getPost('sender', 'sender'),
            'address'       => $this->getRequest()->getPost('address', 'address'),
            'recipient'     => $this->getRequest()->getPost('recipient', 'recipient'),
            'userName'      => $this->getRequest()->getPost('userName', 'username')
        );
        $this->asyn_request($args);

        while (1) {
            $orderId = date("ymd") . rand(1000, 9999);
            if (!$this->getOrderTable()->getOrder($orderId)) {
                break;
            }
        }

        $order = new Order();
        $order->id         = $orderId;
        $order->zipCode    = $this->getRequest()->getPost('zipcode', '610041');
        $order->message    = $this->getRequest()->getPost('message', DEFAULT_MSG);
        $order->sender     = $this->getRequest()->getPost('sender', 'sender');
        $order->address    = $this->getRequest()->getPost('address', 'address');
        $order->recipient  = $this->getRequest()->getPost('recipient', 'recipient');
        $order->userName   = $this->getRequest()->getPost('userName', 'username');
        $order->picUrl     = $this->getRequest()->getPost('userPicUrl', DEFAULT_PICURL);
        $order->status     = UNPAY;
        $order->bank       = $this->getRequest()->getPost('bank', DEFAULT_BANK);
        // var_dump($order);
        $this->getOrderTable()->saveOrder($order);

        $res = array(
            'code' => 0,
            'orderId' => $orderId,
        );
        return new JsonModel($res);
    }

    public function deleteAction()
    {
        $orderId = $this->params()->fromRoute('id', '0');
        $order = $this->getOrderTable()->getOrder($orderId);
        if (!$order) {
            echo "order not exist!";
        } else {
            $this->getOrderTable()->deleteOrder($orderId);
            echo "order delete success!";
        }

        $viewModel = new ViewModel();
        $viewModel->setTerminal(true); // disable layout template
        return $viewModel;
    }

    public function updateAction()
    {
        $orderId = $this->params()->fromRoute('id', 0);
        echo "orderId:" . $orderId . "</br>";
        $status = $this->params()->fromRoute('status');
        echo "new status:" . $status . "</br>";
        if (!$orderId || ($status != PRINTED && $status != SHIPPED && $status != PAYED)) {
            echo "wrong para! status must be 102(PRINTED) or 103(SHIPPED)!";
        } else {
            $order = $this->getOrderTable()->getOrder($orderId);
            if (!$order) {
                echo "order not exist!";
            } else {
                $order->status = $status;
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
        return './postcards/' . date('Ymd', time()) . '/';
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

    private function makePicture()
    {
        $dstpath = $this->dstPath();
        if (!is_dir($dstpath)) {
            if (!mkdir($dstpath)) {
                echo 'Create folder' . $dstpath . 'failed!';
                return false;
            }
        }

        $date           = date('YmdHis',time());
        $templateIndex  = $this->getRequest()->getPost('templateIndex', 1);
        $offsetX        = $this->getRequest()->getPost('offsetX', 0);
        $offsetY        = $this->getRequest()->getPost('offsetY', 0);
        $userPicUrl     = $this->getRequest()->getPost('userPicUrl', DEFAULT_PICURL);

        $zipcode    = $this->getRequest()->getPost('zipcode', '610041');
        $message    = $this->getRequest()->getPost('message', DEFAULT_MSG);
        $sender     = $this->getRequest()->getPost('sender', 'sender');
        $address    = $this->getRequest()->getPost('address', 'address');
        $recipient  = $this->getRequest()->getPost('recipient', 'recipient');
        $username   = $this->getRequest()->getPost('userName', 'username');

        $canvas_w = 960.0;
        $canvas_h = 1440.0;

        $image = $this->generateFront($templateIndex, $offsetX, $offsetY, $userPicUrl, $canvas_w, $canvas_h);
        if (!$image) {
            return false;
        }

        imagepng($image, $dstpath . $username . '_' . $date . '_front.png');
        imagedestroy($image);

        $canvas_w = 971.0;
        $canvas_h = 600.0;
        $image = $this->generatePostcardBack($zipcode, $message, $sender, $address, $recipient, $canvas_w, $canvas_h);
        // $image = generatePostcardBack('518000', '思念是一季的花香，漫过山谷，笼罩你我，而祝福是无边的关注，溢出眼睛，直到心底，愿愉快伴你一生。', '李生', '上海杨浦区淞沪路303号创智天地三期8号楼8楼', '泡泡海', $canvas_w, $canvas_h);
        imagepng($image, $dstpath . $username . '_' . $date . '_backface.png');
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

    private function asyn_request($args)
    {
        $host = $args["host"] ?  $args["host"] : "localhost";//主机
        $method = $args["method"] == "POST" ? "POST" : "GET";//方法   
        $url = $args["url"] ? $args["url"] : "http://".$host ;//地址
        $data = is_array($args["data"]) ? $args["data"] : array();//请求参数   
        $fp = @fsockopen($host, 80, $errno, $errstr, 30);
        //错误
        if (!$fp) {echo "$errstr ($errno)<br/>\n"; exit;}

        $qstr = $method == "GET" ? urlencode($args["data"]) : $args["data"];
        $params = '';
        $params.= $method == "GET" ? "GET {$url}?{$qstr} HTTP/1.1\r\n" :  "POST {$url} HTTP/1.1\r\n";
        $params.= "Host: ".$host."\r\n";
        $params.= "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9.1.5) Gecko/20091102 Firefox/3.5.5\r\n";
        $params.= "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n";
        $params.= "Accept-Language: zh-cn,zh;q=0.5\r\n";
        $params.= "Accept-Encoding: gzip,deflate\r\n";
        $params.= "Accept-Charset: GB2312,utf-8;q=0.7,*;q=0.7\r\n";
        $params.= "Keep-Alive: 300\r\n";
        $params.= "Connection: keep-alive\r\n";
        $params.= "Content-Type: application/x-www-form-urlencoded; charset=UTF-8;encoding=utf-8;\r\n";
        // $params.= "Content-Type: application/json; encoding=utf-8\r\n";
        $params.= "Content-Length: ".strlen($qstr)."\r\n\r\n";
        $params.= $method == "GET" ? null :$qstr;

        //file_put_contents("C:\\http.txt",$params);

        fwrite($fp, $params);

        // echo '<br>params:' . $params . '<br>';
        //取得回應的內容
        // $line = fgets($fp, 1024);
        // echo "result:<br>";
        // echo $line;
        // if (!preg_match('/^HTTP/1.. 200/i', $line)) return;

        $results = "";
        $inheader = true;
        while (!feof($fp)) {
          $line = fgets($fp, 2048);
          if ($inheader && ($line == "\n" || $line == "\r\n")) {
            $inheader = false;
          } else if (!$inheader) {
            $results .= $line;
          }
        }
        // echo '<br>results:' . $results . '<br>';
        fclose($fp);
        return $results;
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

}