<?php
namespace Postcard\Model;


class Activity
{
    const DEFAULT_ACTIVITY_ID = 1;

    const STATUS_CLOSE = 0;
    const STATUS_OPEN = 1;


    private $id;
    private $startTime;
    private $endTime;
    private $templateIdOrder;     // template在制作页面显示的顺序
    private $priceRuleId;
    private $status;


    public function exchangeArray($data) {
        $this->id = (isset($data["id"])) ? $data["id"] : null;
        $this->startTime = (isset($data["startTime"])) ? $data["startTime"] : null;
        $this->endTime = (isset($data["endTime"])) ? $data["endTime"] : null;
        $this->templateIdOrder = (isset($data["templateIdOrder"])) ? 
            $data["templateIdOrder"] : null;
        $this->priceRuleId = (isset($data["priceRuleId"])) ?
            $data["priceRuleId"] : null;
        $this->status = (isset($data["status"])) ? $data["status"] : null;
    }


    public function getId() {
        return $this->id;
    }


    public function getStartTime() {
        return $this->startTime;
    }


    public function setStartTime($startTime) {
        $this->startTime = $startTime;
        return $this;
    }


    public function getEndTime() {
        return $this->endTime;
    }


    public function setEndTime($endTime) {
        $this->endTime = $endTime;
        return $this;
    }


    public function getTemplateIdOrder() {
        return $this->templateIdOrder;
    }


    public function setTemplateIdOrder($templateIdOrder) {
        $this->templateIdOrder = $templateIdOrder;
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
