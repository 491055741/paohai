<?php
namespace Postcard\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Where;
use Postcard\Model\ActivityJoinRecord;


class ActivityJoinRecordTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }


    public function getRecordByOrderId($orderId) {
        return $this->getRecords(array("orderId", $orderId));
    }


    /**
     * @param array $condition. available key as below:
     *      actId
     *      userName:       user wx opeinid
     *      orderId:        user order id 
     *      status:         default value is ActivityJoinRecord::STATUS_USED
     *      joinBeginTime:  the min time of user joinTime
     *      joinEndTime:    the max time of user jointime
     */
    public function getRecords($condition)
    {
        $condition = array_merge(
            array("status" => ActivityJoinRecord::STATUS_USED),
            $condition
        );
        $select = $this->tableGateway->getSql()->select();
        $select->where(function($where) use($condition) {
            $where->equalTo("id", $actId);
            $equalFields = array(
                "actId", "userName", "orderId", "status"
            );
            foreach ($equalFields as $field) {
                if (isset($condition[$field])) {
                    $where->equalTo($field, $condition[$field]);
                }
            }

            if (isset($condition["joinBeginTime"])) {
                $where->greaterThanOrEqualTo(
                    "joinTime", $condition["joinBeginTime"]
                );
            }
            if (isset($condition["joinEndTime"])) {
                $where->lessThanOrEqualTo(
                    "joinTime", $condition["joinEndTime"]
                );
            }
            return $where;
        });
        return $this->tableGateway->selectWith($select);
    }
}


/* End of file */
