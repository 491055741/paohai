<?php
namespace Postcard\Model;


class Image
{
    private $id;
    private $url;

    
    public function exchangeArray($data) {
        $this->id = (isset($data["id"])) ? $data["id"] : null;
        $this->url = (isset($data["url"])) ? $data["url"] : null;
    }


    public function getId() {
        return $this->id;
    }


    public function getUrl() {
        return $this->url;
    }


    public function setUrl($url) {
        $this->url = $url;
        return $this;
    }
}

/* End of file */
