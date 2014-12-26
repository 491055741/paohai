<?php
namespace Postcard\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Postcard\Model\Contact;


class ContactController extends AbstractActionController
{
    const JS_TAG = "201412251802";

    protected $contactTable;

    public function testShareAction()
    {
        $userName = $this->getRequest()->getQuery('userName', '');
        $viewModel = new ViewModel(array('tag' => self::JS_TAG, 'userName' => $userName));
        $viewModel->setTerminal(true); // disable layout template
        return $viewModel;
    }

    public function fillAddressAction()
    {
        $userName = $this->getRequest()->getQuery('userName', '');
        $viewModel = new ViewModel(array('tag' => self::JS_TAG, 'userName' => $userName));
        $viewModel->setTerminal(true); // disable layout template
        return $viewModel;
    }

    public function saveAction() {
        $userName = $this->getRequest()->getPost('userName', '');
        $contactName = $this->getRequest()->getPost('contactName', '');
        if (empty($userName) || empty($contactName)) {
            return new JsonModel(array(
                "code" => "10000",
                "msg" => "请输入姓名",
            ));
        }
        $contact = $this->getContactTable()->getContact($userName, $contactName);
        if ( ! $contact) {
            $contact = new Contact();
            $contact->userName    = $userName;
            $contact->contactName = $contactName;
        }

        $contact->address = $this->getRequest()->getPost("address", "");
        $contact->zipCode = $this->getRequest()->getPost("zipCode", "");
        $this->getContactTable()->saveContact($contact);

        $res = array(
            "code" => "0",
            "msg"  => "Contact add OK.",
        );
        return new JsonModel($res);
    }

    public function deleteAction() {
        $userName = $this->getRequest()->getPost("userName", "");
        $contactName = $this->getRequest()->getPost("contactName", "");
        if (empty($userName) || empty($contactName)) {
            return new JsonModel(array(
                "code" => "10000",
                "msg" => "请输入姓名",
            ));
        }
        $contact = $this->getContactTable()->getContact($userName, $contactName);
        if ( ! $contact) {
            return new JsonModel(array(
                "code" => "10001",
                "msg" => "您要删除的用户不存在",
            ));
        }

        $this->getContactTable()->deleteContact($userName, $contactName);
        return new JsonModel(array(
            "code" => "0",
            "msg" => "删除成功",
        ));
    }

    public function listContactsAction() {
        $userName = $this->getRequest()->getQuery("userName", "");
        if (empty($userName)) {
            return new JsonModel(array(
                "code" => "10000",
                "msg" => "用户名必填",
            ));
        }

        return new JsonModel(array(
            "code" => "0",
            "data" => $this->getContactTable()->getContacts($userName)
        ));
    }

    public function contactsPageAction() {
        $userName = $this->getRequest()->getQuery("userName", "");
        if (empty($userName)) {
            $view = new ViewModel(array(
                "code" => "10000",
                "msg" => "用户名必填",
            ));
            $view->setTemplate("postcard/postcard/error");
            return $view;
        }

        $viewModel = new ViewModel(array(
            "userName" => $userName,
            "tag" => self::JS_TAG,
        ));
        $viewModel->setTerminal(true);
        return $viewModel;
    }

    private function getContactTable()
    {
        if (!$this->contactTable) {
            $sm = $this->getServiceLocator();
            $this->contactTable = $sm->get('Postcard\Model\contactTable');
        }
        return $this->contactTable;
    }
}

/* End of file */
