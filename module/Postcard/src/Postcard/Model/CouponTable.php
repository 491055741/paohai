<?php
namespace Postcard\Model;


use Zend\Db\TableGateway\TableGateway;

class CouponTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function getCoupon($code) {
        $select = $this->tableGateway->getSql()->select();
        $select->where('code = "'.$code.'"');;
        $resultSet = $this->tableGateway->selectWith($select);

        $result = [];
        foreach ($resultSet as $row) {
            array_push($result, $row);
            break;
        }

        return $result[0];
    }

    public function getCouponById($id) {
        $select = $this->tableGateway->getSql()->select();
        $select->where('id='.$id);;
        $resultSet = $this->tableGateway->selectWith($select);

        $result = [];
        foreach ($resultSet as $row) {
            array_push($result, $row);
            break;
        }

        return $result[0];
    }

    public function saveCoupon(Coupon $coupon)
    {
        $data = array(
            'status'       => $coupon->status
        );

        $this->tableGateway->update($data, array('id' => $coupon->id));
    }
}