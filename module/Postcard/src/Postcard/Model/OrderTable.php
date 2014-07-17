<?php
namespace Postcard\Model;

use Zend\Db\TableGateway\TableGateway;

class OrderTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
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
            'id'        => $order->id,
            'userName'  => $order->userName,
            'picUrl'    => $order->picUrl,
            'voiceUrl'  => $order->voiceUrl,
            'message'   => $order->message,
            'zipCode'   => $order->zipCode,
            'address'   => $order->address,
            'recipient' => $order->recipient,
            'sender'    => $order->sender,
            'price'     => $order->price,
            'payDate'   => $order->payDate,
            'status'    => $order->status,
            'bank'      => $order->bank,
            'senderMobile'     => $order->senderMobile,
            'recipientMobile'  => $order->recipientMobile,
            'postcardFileName' => $order->postcardFileName,
            'templateId'       => $order->templateId,
            'offsetX'          => $order->offsetX,
            'offsetY'          => $order->offsetY,
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
}
