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
     *      price:          user payed price
     *      status:         default value is ActivityJoinRecord::STATUS_USED
     *      joinBeginTime:  the min time of user joinTime
     *      joinEndTime:    the max time of user jointime
     *
     * @param array|int $limit eg:
     *      array: array($limit, $offset)
     *      int: $limit
     *
     * @param string|array $order referer to zend/db/dql   
     */
    public function getRecords($condition, $limit = NULL, $order = NULL)
    {
        $condition = array_merge(
            array("status" => ActivityJoinRecord::STATUS_COMPLETE),
            $condition
        );
        $select = $this->tableGateway->getSql()->select();
        $select->where(function($where) use($condition) {
            $equalFields = array(
                "actId", "userName", "orderId", "status", "price"
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

        if ($limit) {
            $offset = 0;
            if (is_array($limit)) {
                @list($limit, $offset) = $limit;
            }
            $limit = (int) $limit;
            $offset = (int) $offset;
            $select->offset($offset)->limit($limit);
        }

        if ($order) {
            $select->order($order);
        }

        return $this->tableGateway->selectWith($select);
    }


    public function save(ActivityJoinRecord $record) {
        $this->tableGateway->insert(array(
            "userName" => $record->getUserName(),
            "actId" => $record->getActId(),
            "orderId" => $record->getOrderId(),
            "joinTime" => $record->getJoinTime(),
            "price" => $record->getPrice(),
            "status" => $record->getStatus(),
            ));
    }
}


/* End of file */
