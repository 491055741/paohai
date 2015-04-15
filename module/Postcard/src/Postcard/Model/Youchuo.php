<?php
namespace Postcard\Model;


class Youchuo
{
    public $name;
    public $path;

    public function exchangeArray($data)
    {
        $this->name = (isset($data['name'])) ? $data['name'] : null;
        $this->path    = (isset($data['path'])) ? $data['path'] : null;
    }
}
