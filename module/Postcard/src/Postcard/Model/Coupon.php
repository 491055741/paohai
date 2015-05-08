<?php
namespace Postcard\Model;


class Coupon
{
    public $id;
    public $code;
    public $expiredAt;
    public $price;
    public $status;

    const UNUSE = 0;
    const USED = 1;

    public function exchangeArray($data)
    {
        $this->id    = (isset($data['id'])) ? $data['id'] : null;
        $this->code    = (isset($data['code'])) ? $data['code'] : null;
        $this->expiredAt    = (isset($data['expiredAt'])) ? $data['expiredAt'] : null;
        $this->price    = (isset($data['price'])) ? $data['price'] : null;
        $this->status    = (isset($data['status'])) ? $data['status'] : null;
    }
}
