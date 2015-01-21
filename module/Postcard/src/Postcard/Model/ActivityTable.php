<?php
namespace Postcard\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Where;
use Postcard\Model\Order;


class ActivityTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
    }

    public function getActivityById($id) {
        $select = $this->tableGateway->getSql()->select();    
        $select->where(function($where) use ($id) {
            $where->equalTo("id", $id);
            return $where;
        });
        $resultSet = $this->tableGateway->selectWith($select);
        return $resultSet->current() ?: NULL;
    }
}
