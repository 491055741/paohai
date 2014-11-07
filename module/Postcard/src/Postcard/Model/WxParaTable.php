<?php
namespace Postcard\Model;


use Zend\Db\TableGateway\TableGateway;

class WxParaTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function getWxPara($paraName)
    {
        $rowset = $this->tableGateway->select(array('paraName' => $paraName));
        $row = $rowset->current();
        if (!$row) {
            return FALSE;
        }
        return $row;
    }

    public function savePara(WxPara $para)
    {
        $data = array(
            'paraName' => $para->paraName,
            'value'    => $para->value,
            'expireTime' => $para->expireTime,
        );

        if ($this->getWxPara($para->paraName)) {
            $this->tableGateway->update($data, array(
                'paraName' => $para->paraName,
            ));
        } else {
            $this->tableGateway->insert($data);
        }
    }

    public function deleteValue($paraName)
    {
        $this->tableGateway->delete(array('paraName' => $paraName));
    }
}
