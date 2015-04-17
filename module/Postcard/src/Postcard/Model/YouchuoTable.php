<?php
namespace Postcard\Model;


use Zend\Db\TableGateway\TableGateway;

class YouchuoTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function getYouchuoList($activityId) {
        $select = $this->tableGateway->getSql()->select();
        $select->where('activityId='.(int)$activityId);
        $rowset = $this->tableGateway->selectWith($select);

        $result = [];
        foreach ($rowset as $row) {
            array_push($result, $row);
        }

        return $result;
    }
}