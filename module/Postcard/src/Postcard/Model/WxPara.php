<?php
namespace Postcard\Model;


class WxPara
{
    public $paraName;
    public $value;

    public function exchangeArray($data)
    {
        $this->paraName = (isset($data['paraName'])) ? $data['paraName'] : null;
        $this->value    = (isset($data['value'])) ? $data['value'] : null;
    }
}
