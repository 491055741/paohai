<?php
namespace Postcard\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Where;
use Postcard\Model\ActivityPriceRule;


class ActivityTemplatePriceRuleTable
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
        $results = $this->tableGateway->selectWith($select);
        if ($results) {
            return $results[0];
        }

        return NULL;
    }
}

/* End of file */
