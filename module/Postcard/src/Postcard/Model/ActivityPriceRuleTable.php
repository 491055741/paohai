<?php
namespace Postcard\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Where;
use Postcard\Model\ActivityPriceRule;


class ActivityPriceRuleTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }


    public function getOneById($id)
    {
        $select = $this->tableGateway->getSql()->select();    
        $select->where(function($where) use ($id) {
            $where->equalTo("id", $id);
            return $where;
        });
        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet->current() ?: NULL;
    }
}

/* End of file */
