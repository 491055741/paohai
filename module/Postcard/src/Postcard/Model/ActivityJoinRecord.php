<?php
namespace Postcard\Model;


class ActivityJoinRecord
{
    const STATUS_DELETE = -1;
    const STATUS_UNCOMPLETE = 0;
    const STATUS_COMPLETE = 1;

    private $id;
    private $userName;
    private $actId;
    private $orderId;
    private $joinTime;
    private $price;
    private $status;


    public function exchangeArray($data) {
        $this->id = (isset($data["id"])) ? $data["id"] : null;
        $this->userName = (isset($data["userName"])) ? 
            $data["userName"] : null;
        $this->actId = (isset($data["actId"])) ? $data["actId"] : null;
        $this->orderId = (isset($data["orderId"])) ?
            $data["orderId"] : null;
        $this->joinTime = (isset($data["joinTime"])) ?
            $data["joinTime"] : null;
        $this->price = (isset($data["price"])) ? $data["price"] : null;
        $this->status = (isset($data["status"])) ? $data["status"] : null;
    }


    public function getId() {
        return $this->id;
    }


    public function getUserName() {
        return $this->userName;
    }


    public function setUserName($userName) {
        $this->userName = $userName;
        return $this;
    }


    public function getActId() {
        return $this->actId;
    }


    public function setActId($actId) {
        $this->actId = $actId;
        return $this;
    }


    public function getOrderId() {
        return $this->orderId;
    }


    public function setOrderId($orderId) {
        $this->orderId = $orderId;
        return $this;
    }


    public function getJoinTime() {
        return $this->joinTime;
    }


    public function setJoinTime() {
        $this->joinTime = date("Y-m-d H:i:s");
        return $this;
    }

    public function getPrice() {
        return $this->price;
    }


    public function setPrice($price) {
        $this->price = $price;
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
