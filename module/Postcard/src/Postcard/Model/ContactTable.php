<?php
namespace Postcard\Model;

use Zend\Db\TableGateway\TableGateway;

class ContactTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function getContacts($userName)
    {
        $rowset = $this->tableGateway->select(array('userName' => $userName));
        return $rowset;
    }

    public function getContact($userName, $contactName)
    {
        $rowset = $this->tableGateway->select(array('userName' => $userName, 'contactName' => $contactName));
        $row = $rowset->current();
        if (!$row) {
            return FALSE;
        }
        return $row;
    }

    public function saveContact(Contact $contact)
    {
        $data = array(
            'userName'      => $contact->userName,
            'contactName'   => $contact->contactName,
            'zipCode'       => $contact->zipCode,
            'address'       => $contact->address,
        );

        if ($this->getContact($contact->userName, $contact->contactName)) {
            $this->tableGateway->update($data, array('userName' => $contact->userName, 'contactName' => $contact->contactName));
        } else {
            $this->tableGateway->insert($data);
        }
    }

    // public function deleteContact($id)
    // {
    //     $this->tableGateway->delete(array('id' => $id));
    // }
}
