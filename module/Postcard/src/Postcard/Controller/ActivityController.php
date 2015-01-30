<?php
namespace Postcard\Controller;
include_once(dirname(__FILE__)."/../../../../Wxpay/view/wxpay/wxpay/CommonUtil.php");

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use CommonUtil;
use Postcard\Service\ActivityService;

define('JS_TAG', '201501301522');

class ActivityController extends AbstractActionController
{
    /**
     * List current valid activity
     */
    public function indexAction() {

    }


    public function introAction() {
        $userName = $this->getRequest()->getQuery('userName');
        $actName = $this->getRequest()->getQuery('actname', '');
        


        if ( ! $userName) {
            $currUrl = $this->getCurrentUrl();
            $oauthUrl = $this->getServiceLocator()
                ->get("Wechat\Service\OauthService")
                ->setRequest($this->getRequest())
                ->getOauthUrl($currUrl); 
            Header("Location: $oauthUrl");
            exit;
        }

        $util = new CommonUtil();
        $util->setServiceLocator($this->getServiceLocator());
        $accessToken = $util->getAccessToken();

        $viewModel = new ViewModel(array(
            'userName' => $userName,
            'accessToken' => $accessToken,
            'actName' => $actName,
            'JS_TAG' => JS_TAG,
            ));
        $viewModel->setTerminal(true);

        return $viewModel;
    }


    private function getCurrentUrl() {
        $uri = $this->getRequest()->getUri();
        $scheme = $uri->getScheme();
        $host = $uri->getHost();
        $port = $uri->getPort();
        $path = $uri->getPath();
        $query = $uri->getQuery();
        $query = trim(str_replace(ltrim($path, "/"), '', $query), "&");
        $currUrl = $scheme . "://" . $host . ":" . $port . $path;
        if ($query) {
            $currUrl .= "?" . $query;
        }

        return $currUrl;
    }
}

/* End of file */
