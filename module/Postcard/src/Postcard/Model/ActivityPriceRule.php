<?php
namespace Postcard\Model;


class ActivityPriceRule
{
    const TYPE_STEP = 1;
    const TYPE_SALE_AFTER_BUY = 2;


    private $id;
    private $type;
    private $priceConf;


    public function exchangeArray($data) {
        $this->id = (isset($data["id"])) ? $data["id"] : null;
        $this->type = (isset($data["type"])) ? $data["type"] : null;
        $this->priceConf = (isset($data["priceConf"])) ? $data["priceConf"] : null;
    }


    public function getId() {
        return $this->id;
    }


    public function getType() {
        return $this->type;
    }


    public function setType($type) {
        $this->type = $type;
        return $this;
    }


    public function getPriceConf() {
        return $this->priceConf;
    }


    public function setPriceConf($priceConf) {
        $this->priceConf = $priceConf;
        return $this;
    }
}


/* End of file */
