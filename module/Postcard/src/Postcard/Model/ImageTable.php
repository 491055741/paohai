<?php
namespace Postcard\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Where;
use Postcard\Model\Image;


class ImageTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }


    public function getUrls($ids) {
        $ids = (array) $ids;
        $select = $this->tableGateway->getSql()->select();
        $select->where(function($where) use ($ids) {
            $where->in("id", $ids);
            return $where;
        }); 

        return $this->tableGateway->selectWith($select);
    }
}

/* End of file */
