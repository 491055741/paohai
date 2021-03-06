<?php
namespace Postcard\Controller;
include_once(dirname(__FILE__)."/../../../../Wxpay/view/wxpay/wxpay/CommonUtil.php");

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Postcard\Model\Contact;
use CommonUtil;

class ContactController extends AbstractActionController
{
    const JS_TAG = "201501121111";

    protected $contactTable;
    protected $util;

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
        $viewModel = new ViewModel(array('tag' => self::JS_TAG,
            'userName' => $userName));
        $viewModel->setTerminal(true); // disable layout template
        return $viewModel;
    }

    public function queryPostcodeAction() {
        $viewModel = new ViewModel(array('tag' => self::JS_TAG));
        $viewModel->setTerminal(true); // disable layout template
        return $viewModel;
    }

    public function saveAction()
    {
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
        $jsApiSignPackage = $this->getUtil()->getJsApiSignPackage();

        $nickName = '';
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$this->getUtil()->getAccessToken().'&openid='.$userName.'&lang=zh_CN';
        $result = json_decode($this->getUtil()->httpGet($url), true);
        if (isset($result['nickname'])) {
            $nickName = $result['nickname'];
        }

        $viewModel = new ViewModel(array(
            'userName'  => $userName,
            'nickName'  => $nickName,
            'tag'       => self::JS_TAG,
            'jsApiSignPackage' => $jsApiSignPackage,
        ));
        $viewModel->setTerminal(true);
        return $viewModel;
    }

    public function postcodeAction() {
        $address = mb_convert_encoding($this->getRequest()->getQuery("address", ""), "GBK", "UTF-8");

        $result = mb_convert_encoding($this->getUtil()->httpGet("http://opendata.baidu.com/post/s?wd=".urlencode($address)."&p=mini&rn=20"), "UTF-8", "GBK");
        preg_match("/em>.*?(\d{6}).*?<\/a/", $result, $postcodeArray);

        $postcode = "";
        if (!empty($postcodeArray[1])) {
            $postcode = $postcodeArray[1];
        }

        return new JsonModel(array(
            "code" => "0",
            "data" => $postcode
        ));
    }

    public function postcodeListAction() {
        $address = mb_convert_encoding($this->getRequest()->getQuery("address", ""), "GBK", "UTF-8");

        $result = mb_convert_encoding($this->getUtil()->httpGet("http://opendata.baidu.com/post/s?wd=".urlencode($address)."&p=mini&rn=20"), "UTF-8", "GBK");
        preg_match_all("/<tr><td>\d{6}.+?<\/tr>/", $result, $postcodeArray);

        $postcodeList = array();
        foreach ($postcodeArray[0] as $element) {
            preg_match("/\d{6}/", $element, $postcode);

            $temp["postcode"] = $postcode[0];

            preg_match_all("/<td>(.*?)<\/td>/", $element, $matches);

            $temp["address"] = preg_replace("/<(\/?).*?>/", "", $matches[1][1]);
            array_push($postcodeList, $temp);
        }

        return new JsonModel(array(
            "code" => "0",
            "data" => $postcodeList
        ));
    }

    private function getContactTable()
    {
        if (!$this->contactTable) {
            $sm = $this->getServiceLocator();
            $this->contactTable = $sm->get('Postcard\Model\contactTable');
        }
        return $this->contactTable;
    }

    private function getUtil()
    {
        if (!$this->util) {
            $this->util = new CommonUtil();
            $this->util->setServiceLocator($this->getServiceLocator());
        }
        return $this->util;
    }
}

/* End of file */
