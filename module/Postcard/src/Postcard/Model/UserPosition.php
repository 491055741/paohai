<?php
// src/Postcard/Model/UserPosition.php
namespace Postcard\Model;


class UserPosition
{
    private $userName;
    private $latitude;
    private $longitude;
    private $lastUpdateTimestamp;


    public function exchangeArray($data) {
        $this->userName = (isset($data['userName'])) ? $data['userName'] : null;
        $this->latitude = (isset($data['latitude'])) ? $data['latitude'] : null;
        $this->longitude = (isset($data['longitude'])) ? $data['longitude'] : null;
        $this->lastUpdateTimestamp = (isset($data['lastUpdateTimestamp'])) ?
            $data['lastUpdateTimestamp'] : 0;
    }


    public function getUserName() {
        return $this->userName;
    }


    public function setUserName($userName) {
        $this->userName = $userName;
        return $this;
    }


    public function getLatitude() {
        return $this->latitude;
    }


    public function setLatitude($latitude) {
        $this->latitude = $latitude;
        return $this;
    }


    public function getLongitude() {
        return $this->longitude;
    }


    public function setLongitude($longitude) {
        $this->longitude = $longitude;
        return $this;
    }


    public function getLastUpdateTimestamp() {
        return $this->lastUpdateTimestamp;
    }


    public function updateTimestamp() {
        $this->lastUpdateTimestamp = time();
        return $this;
    }
}


/* End of file */
