<?php
namespace Postcard\Model;


class ActivityTemplateConfig
{
    const ROTATE_0 = 0;
    const ROTATE_90 = 1;
    const ROTATE_180 = 2;
    const ROTATE_270 = 3;

    const STATUS_UNUSED = 0;
    const STATUS_USED = 1;

    private $id;
    private $actId;
    private $imgId;
    private $rotate;
    private $priceRuleId;
    private $status;


    public function exchangeArray($data) {
        $this->id = (isset($data["id"])) ? $data["id"] : null;
        $this->actId = (isset($data["actId"])) ? $data["actId"] : null;
        $this->imgId = (isset($data["imgId"])) ? $data["imgId"] : null;
        $this->rotate = (isset($data["rotate"])) ? $data["rotate"] : null;
        $this->priceRuleId = (isset($data["priceRuleId"])) ?
            $data["priceRuleId"] : null;
        $this->status = (isset($data["status"])) ? $data["status"] : null;
    }


    public function getId() {
        return $this->id;
    }


    public function getActId() {
        return $this->actId;
    }


    public function setActId($actId) {
        $this->actId = $actId;
        return $this;
    }


    public function getImgId() {
        return $this->imgId;
    }


    public function setImgId($imgId) {
        $this->imgId = $imgId;
        return $this;
    }


    public function getRotate() {
        return $this->rotate;
    }


    public function setRotate($rotate) {
        $this->rotate = $rotate;
        return $this;
    }


    public function getPriceRuleId() {
        return $this->priceRuleId;
    }


    public function setPriceruleId($priceRuleId) {
        $this->priceRuleId = $priceRuleId;
        return $this;
    }


    public function getStatus() {
        return $this->status;
    }


    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }
}

/* End of file */
