<?php
// src/Postcard/Model/UserPositionTable.php
namespace Postcard\Model;

use Zend\Db\TableGateway\TableGateway;


class UserPositionTable
{
    protected $tableGateway;


    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
    }


    /**
     * @param string $userName
     *
     * @return UserPosition $userPosition
     */
    public function getPositionByUserName($userName) {
        $rowset = $this->tableGateway->select(array('userName' => $userName));
        $row = $rowset->current();
        if ( ! $row) {
            return FALSE;
        }

        return $row;
    }


    /**
     * @param UserPosition $userPosition
     */
    public function savePosition(UserPosition $userPosition) {
        $data = array(
            'userName' => $userPosition->getUserName(),
            'latitude' => $userPosition->getLatitude(),
            'longitude' => $userPosition->getLongitude(),
            'lastUpdateTimestamp' => $userPosition->getLastUpdateTimestamp(),
        );

        if ($this->getPositionByUserName($userPosition->getUserName())) {
            $this->tableGateway->update($data, array('userName' => $userPosition->getUserName()));
        } else {
            $this->tableGateway->insert($data);
        }
    }
}


/* End of file */
