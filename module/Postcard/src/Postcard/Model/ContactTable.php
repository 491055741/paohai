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
        $entries   = array();
        foreach ($rowset as $row) {
            $entry = new Contact();
            $entry->userName = $row->userName;
            $entry->contactName = $row->contactName;
            $entry->address = $row->address;
            $entry->zipCode = $row->zipCode;
            $entry->mobiel = $row->mobile;
            $entries[] = $entry;
        }
        return $entries;
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
            'mobile'        => $contact->mobile
        );

        if ($this->getContact($contact->userName, $contact->contactName)) {
            $this->tableGateway->update($data, array('userName' => $contact->userName, 'contactName' => $contact->contactName));
        } else {
            $this->tableGateway->insert($data);
        }
    }

    public function deleteContact($userName, $contactName)
    {
        $this->tableGateway->delete(array(
            'userName' => $userName,
            'contactName' => $contactName,    
        ));
    }
}
