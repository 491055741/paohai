<?php
namespace Postcard\Model;

class Contact
{
    public $userName;    // 一个用户可以有多个联系人，每个联系人存为一条记录。一个联系人只能有一条记录
    public $contactName; // 联系人姓名
    public $zipCode;
    public $address;
    public $mobile;

    public function exchangeArray($data)
    {
        $this->userName      = (isset($data['userName'])) ? $data['userName'] : null;
        $this->contactName   = (isset($data['contactName'])) ? $data['contactName'] : null;
        $this->zipCode       = (isset($data['zipCode'])) ? $data['zipCode'] : null;
        $this->address       = (isset($data['address'])) ? $data['address'] : null;
        $this->mobile        = (isset($data['mobile'])) ? $data['mobile'] : null;
    }
}
