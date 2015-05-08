<?php
namespace Postcard\Model;

// 订单号（日期＋随机数），用户名，原始图片链接（需要保存到自己服务器），语音留言链接（3天有效，需要保存到自己服务器上），
// 留言，邮编，收信地址，收信人，发信人，发信人手机号，收信人手机号，支付金额，支付日期，生成本地明信片图片名（订单号＋front/back），
// 订单状态（未支付，已支付，已打印，已发货，已收货，退款状态，。。。）

class Order
{
    const STATUS_CANCEL = 99;
    const STATUS_UNPAY = 100;
    const STATUS_PAYED = 101;
    const STATUS_PRINTED = 102;
    const STATUS_SHIPPED = 103;


    public $id;
    public $userName;
    public $picUrl;
    public $voiceMediaId;
    public $message;
    public $zipCode;
    public $address;
    public $recipient;  // 信封上的收信人姓名
    public $salutation; // 信件正文抬头的称呼，可以是昵称
    public $recipientMobile;
    public $senderName;   // 信封上的发信人姓名
    public $senderAddress;
    public $signature;   // 信件正文的签名，可以是昵称
    public $senderMobile;
    public $price;       // 总金额
    public $orderDate;
    public $payDate;
    public $partnerQrFileName;
    public $partnerQrText;
    public $status;
    public $bank;
    public $templateId;
    public $postmarkId;
    public $offsetX;
    public $offsetY;
    public $refundFee; // 已退款金额
    public $qrSceneId; // 带参数永久二维码的场景id，全局取值范围1～100000，从1递增，用户扫码后根据sceneId来找对应order
    public $activityId;
    public $partnerId;
    public $like;
    public $unlike;
    public $couponId;

    public function exchangeArray($data)
    {
        $this->id                 = (isset($data['id']))                 ? $data['id']                : null;
        $this->userName           = (isset($data['userName']))           ? $data['userName']          : null;
        $this->picUrl             = (isset($data['picUrl']))             ? $data['picUrl']            : null;
        $this->voiceMediaId       = (isset($data['voiceMediaId']))       ? $data['voiceMediaId']      : null;
        $this->message            = (isset($data['message']))            ? $data['message']           : null;
        $this->zipCode            = (isset($data['zipCode']))            ? $data['zipCode']           : null;
        $this->address            = (isset($data['address']))            ? $data['address']           : null;
        $this->recipient          = (isset($data['recipient']))          ? $data['recipient']         : null;
        $this->salutation         = (isset($data['salutation']))         ? $data['salutation']        : null;
        $this->recipientMobile    = (isset($data['recipientMobile']))    ? $data['recipientMobile']   : null;
        $this->senderName         = (isset($data['senderName']))         ? $data['senderName']        : null;
        $this->senderAddress      = (isset($data['senderAddress']))      ? $data['senderAddress']     : null;
        $this->signature          = (isset($data['signature']))          ? $data['signature']         : null;
        $this->senderMobile       = (isset($data['senderMobile']))       ? $data['senderMobile']      : null;
        $this->price              = (isset($data['price']))              ? $data['price']             : null;
        $this->orderDate          = (isset($data['orderDate']))          ? $data['orderDate']         : null;
        $this->payDate            = (isset($data['payDate']))            ? $data['payDate']           : null;
        $this->partnerQrFileName  = (isset($data['partnerQrFileName']))  ? $data['partnerQrFileName'] : null;
        $this->partnerQrText      = (isset($data['partnerQrText']))      ? $data['partnerQrText']     : null;
        $this->status             = (isset($data['status']))             ? $data['status']            : null;
        $this->bank               = (isset($data['bank']))               ? $data['bank']              : null;
        $this->templateId         = (isset($data['templateId']))         ? $data['templateId']        : null;
        $this->postmarkId         = (isset($data['postmarkId']))         ? $data['postmarkId']        : null;
        $this->offsetX            = (isset($data['offsetX']))            ? $data['offsetX']           : null;
        $this->offsetY            = (isset($data['offsetY']))            ? $data['offsetY']           : null;
        $this->refundFee          = (isset($data['refundFee']))          ? $data['refundFee']         : null;
        $this->qrSceneId          = (isset($data['qrSceneId']))          ? $data['qrSceneId']         : null;
        $this->activityId         = (isset($data['activityId']))         ? $data['activityId']        : null;
        $this->partnerId          = (isset($data['partnerId']))          ? $data['partnerId']         : null;
        $this->like               = (isset($data['like']))               ? $data['like']              : null;
        $this->unlike             = (isset($data['unlike']))             ? $data['unlike']            : null;
        $this->couponId           = (isset($data['couponId']))           ? $data['couponId']          : null;
    }
}
