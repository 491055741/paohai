<?php
namespace Postcard\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Where;
use Postcard\Model\ActivityTemplateConfig;


class ActivityTemplateConfigTable
{
    protected $tableGateway;


    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }


    public function getAllByActId($actId)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where(function($where) use ($actId) {
            $where->equalTo("actId", $actId);
            $where->equalTo("status", ActivityTemplateConfig::STATUS_USED);
            return $where;
        });
        return $this->tableGateway->selectWith($select);
    }

    public function getOneById($id)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where(function($where) use ($id) {
            $where->equalTo("id", $id);
            return $where;
        }

        $results = $this->tableGateway->selectWith($select);
        return  $results->current() ?: NULL;
    }
}

/* End of file */
