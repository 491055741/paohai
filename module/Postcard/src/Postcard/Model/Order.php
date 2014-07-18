<?php
namespace Postcard\Model;

// 订单号（日期＋随机数），用户名，原始图片链接（需要保存到自己服务器），语音留言链接（3天有效，需要保存到自己服务器上），
// 留言，邮编，收信地址，收信人，发信人，发信人手机号，收信人手机号，支付金额，支付日期，生成本地明信片图片名（订单号＋front/back），
// 订单状态（未支付，已支付，已打印，已发货，已收货，退款状态，。。。）

class Order
{
    public $id;
    public $userName;
    public $picUrl;
    public $voiceMediaId;
    public $message;
    public $zipCode;
    public $address;
    public $recipient;
    public $recipientMobile;
    public $sender;
    public $senderMobile;
    public $price;
    public $orderDate;
    public $payDate;
    public $postcardFileName;
    public $status;
    public $bank;
    public $templateId;
    public $offsetX;
    public $offsetY;

    public function exchangeArray($data)
    {
        $this->id        = (isset($data['id'])) ? $data['id'] : null;
        $this->userName  = (isset($data['userName'])) ? $data['userName'] : null;
        $this->picUrl    = (isset($data['picUrl'])) ? $data['picUrl'] : null;
        $this->voiceMediaId  = (isset($data['voiceMediaId'])) ? $data['voiceMediaId'] : null;
        $this->message   = (isset($data['message'])) ? $data['message'] : null;
        $this->zipCode   = (isset($data['zipCode'])) ? $data['zipCode'] : null;
        $this->address   = (isset($data['address'])) ? $data['address'] : null;
        $this->recipient = (isset($data['recipient'])) ? $data['recipient'] : null;
        $this->price     = (isset($data['price'])) ? $data['price'] : null;
        $this->orderDate   = (isset($data['orderDate'])) ? $data['orderDate'] : null;
        $this->payDate   = (isset($data['payDate'])) ? $data['payDate'] : null;
        $this->status    = (isset($data['status'])) ? $data['status'] : null;
        $this->bank      = (isset($data['bank'])) ? $data['bank'] : null;
        $this->sender    = (isset($data['sender'])) ? $data['sender'] : null;
        $this->senderMobile     = (isset($data['senderMobile'])) ? $data['senderMobile'] : null;
        $this->recipientMobile  = (isset($data['recipientMobile'])) ? $data['recipientMobile'] : null;
        $this->postcardFileName = (isset($data['postcardFileName'])) ? $data['postcardFileName'] : null;
        $this->templateId = (isset($data['templateId'])) ? $data['templateId'] : null;
        $this->offsetX    = (isset($data['offsetX'])) ? $data['offsetX'] : null;
        $this->offsetY    = (isset($data['offsetY'])) ? $data['offsetY'] : null;
    }
}
