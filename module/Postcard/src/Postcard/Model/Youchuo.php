<?php
namespace Postcard\Model;


class Youchuo
{
    public $id;
    public $name;
    public $path;

    public function exchangeArray($data)
    {
        $this->id = (isset($data['id'])) ? $data['id'] : null;
        $this->name = (isset($data['name'])) ? $data['name'] : null;
        $this->path    = (isset($data['path'])) ? $data['path'] : null;
    }
}
