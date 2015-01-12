<?php
namespace Postcard\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Where;
use Postcard\Model\Order;

class OrderTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function execSQL($sql)
    {
//        echo PHP_EOL.'excute SQL: '.$sql. PHP_EOL;
        $sql = "INSERT INTO `order_table` VALUES ('15010900001','op','https://mmbiz.qlogo.cn/mmbiz/j8WFfyvBAo8DrIlvCYC2uuNqib9RyvPFnzRlBUa2l4zIGcZv0BnWVHMg9SY1mHtu4MicMreqA4EhXhukLu949Irg/0','99j01bJK6ykY5nwcsDA2i5kXVFLdkKTMS9DAo1yZ9PnEhp2m73NY2weTRVxG21Fu','首先祝愿新的一年顺顺利利平平安安。我是片吧的唯心专一，收到后记得召唤我哦！非常喜欢内蒙的辽阔一直都期待能去一次呢。希望能收到来自内蒙的祝福。','100026','北京市朝阳区石佛营炫特区2号楼B2座402室','谢越','亲爱的谢越','鱿鱼君','','','','','0','2015-1-9 00:00:00','2015-1-9 00:00:00','','','101','','8','5','0','0','','129')";
        $this->tableGateway->adapter->query($sql);
//        var_dump($this->tableGateway->adapter);
        return true;
    }

    public function fetchAll()
    {
        $select = $this->tableGateway->getSql()->select();
        $select->order('orderDate DESC');
        $rowset = $this->tableGateway->selectWith($select);
        return $rowset;
    }

    public function getPayedOrders()
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('status = "101"')->order('payDate DESC'); // 101: payed
        $resultSet = $this->tableGateway->selectWith($select);
        return $resultSet;
    }

    public function getOrdersToRefund()
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('bank = "CMB_CREDIT"')->where('refundFee IS NULL'); // todo: CMB_CREDIT => 兴业银行代码
        $resultSet = $this->tableGateway->selectWith($select);
        return $resultSet;
    }

    public function getOrdersToQueryBank()
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('status > 100')->where('bank IS NULL');
        $resultSet = $this->tableGateway->selectWith($select);
        return $resultSet;
    }

    public function getOrdersByUserName($name, $condition)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where('userName = "'.$name.'"')->where($condition)->order('orderDate DESC');
        return $this->tableGateway->selectWith($select);
    }

    public function getOrderByQrSceneId($sceneId)
    {
        $rowset = $this->tableGateway->select(array('qrSceneId' => $sceneId));
        if (!$rowset) {
            return FALSE;
        }
        $lastRow = null;
        foreach ($rowset as $row) {
            $lastRow = $row;
            break;
        }
        return $lastRow;
    }

    public function getOrder($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            return FALSE;
        }
        return $row;
    }

    public function saveOrder(Order $order)
    {
        $data = array(
            'id'                => $order->id,
            'userName'          => $order->userName,
            'picUrl'            => $order->picUrl,
            'voiceMediaId'      => $order->voiceMediaId,
            'message'           => $order->message,
            'zipCode'           => $order->zipCode,
            'address'           => $order->address,
            'recipient'         => $order->recipient,
            'salutation'        => $order->salutation,
            'recipientMobile'   => $order->recipientMobile,
            'senderName'        => $order->senderName,
            'senderAddress'     => $order->senderAddress,
            'signature'         => $order->signature,
            'senderMobile'      => $order->senderMobile,
            'price'             => $order->price,
            'orderDate'         => $order->orderDate,
            'payDate'           => $order->payDate,
            'partnerQrFileName' => $order->partnerQrFileName,
            'partnerQrText'     => $order->partnerQrText,
            'status'            => $order->status,
            'bank'              => $order->bank,
            'templateId'        => $order->templateId,
            'postmarkId'        => $order->postmarkId,
            'offsetX'           => $order->offsetX,
            'offsetY'           => $order->offsetY,
            'refundFee'         => $order->refundFee,
            'qrSceneId'         => $order->qrSceneId,
        );

        if ($this->getOrder($order->id)) {
            $this->tableGateway->update($data, array('id' => $order->id));
        } else {
            $this->tableGateway->insert($data);
        }
    }

    public function deleteOrder($id)
    {
        $this->tableGateway->delete(array('id' => $id));
    }

    public function calculateOrderPrice($userName)
    {
        $payPrice = 299;
        return $payPrice;
        /*
        $select = $this->tableGateway->getSql()->select();
        $select->where('userName = "'.$userName.'"')
               ->where('price = "1"')    // RMB 0.01
               ->where('status >= "101"');// payed
        $resultSet = $this->tableGateway->selectWith($select);
        if ($resultSet->count() == 0) {// 首次购买1分钱明信片
            $payPrice = 1;
        }
        return $payPrice;

        $priceRules = array(
            100 => 5,       // 前一百张支付 5 分
            300 => 100,     // 101 - 300 支付 100 分
            500 => 200,
        );

        $beginDate = date("Y-m-d 00:00:00");
        $endDate = date("Y-m-d 00:00:00", strtotime("+1 day"));
        $completeCount = $this->countUserCompleteOrder($beginDate, $endDate);
        foreach ($priceRules as $maxCount => $price) {
            if ($completeCount >= $maxCount) {
                continue;
            }

            $payPrice = $price;
            break;
        }

        return $payPrice;
        */
    }


    public function countUserCompleteOrder($beginDate, $endDate) {
        $spec = function(Where $where) use ($beginDate, $endDate) {
            $where
                ->in('status', array(
                    Order::STATUS_PAYED, Order::STATUS_PRINTED,
                    Order::STATUS_SHIPPED
                ))->between('payDate', $beginDate, $endDate);
        };
        $select = $this->tableGateway->getSql()
            ->select()->where($spec);
            
        $resultSet = $this->tableGateway->selectWith($select);
        
        return $resultSet->count();
    }


    /**
     * update order to payed
     *
     * @param int $orderId table order field: id
     * @param int $price RMB fen, eg: 5 -> 5 fen
     * @param string $payDate format: 2014-10-03 15:15:03
     * @param string $wxTransId
     * 
     * @return int $affectedRows
     */
    public function updateOrder2Payed($orderId, $price, $payedDate, $wxTransId, $wxTransId) {
        $data = array(
            'payDate' => $payedDate,
            'status' => Order::STATUS_PAYED,
            'price' => $price,
            'wx_trans_id' => $wxTransId,
        );

        return $this->tableGateway->update($data, array('id' => $orderId));
    }
}
