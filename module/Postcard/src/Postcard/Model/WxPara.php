<?php
namespace Postcard\Model;


class WxPara
{
    public $paraName;
    public $value;
    public $expireTime;

    public function exchangeArray($data)
    {
        $this->paraName = (isset($data['paraName'])) ? $data['paraName'] : null;
        $this->value    = (isset($data['value'])) ? $data['value'] : null;
        $this->expireTime = (isset($data['expireTime'])) ? $data['expireTime'] : null;
    }
}
