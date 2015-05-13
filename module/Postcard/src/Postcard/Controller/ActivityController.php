<?php
namespace Postcard\Controller;
include_once(dirname(__FILE__)."/../../../../Wxpay/view/wxpay/wxpay/CommonUtil.php");

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use CommonUtil;
use Postcard\Service\ActivityService;

define('JS_TAG', '201502141223');

class ActivityController extends AbstractActionController
{
    /**
     * List current valid activity
     */
    public function indexAction() {

    }


    public function introAction() {
        $actName = $this->getRequest()->getQuery('actname', '');
        
        if ( ! $this->checkTemplateExist($actName)) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $util = new CommonUtil();
        $util->setServiceLocator($this->getServiceLocator());
        $accessToken = $util->getAccessToken();

        switch ($actName) {
            case "paopaohai":
                $title = "泡泡海";
                break;
            case "guanzhichezhan":
                $title = "观致汽车";
                break;
            default:
                $title = "趣邮明信片";
        }

        $viewModel = new ViewModel(array(
            'accessToken' => $accessToken,
            'actName' => $actName,
            'JS_TAG' => JS_TAG,
            'title' => $title
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


    private function checkTemplateExist($name) {
        $templateName = "postcard/activity/include/{$name}.phtml";
        $resolver = $this->getEvent()
            ->getApplication()
            ->getServiceManager()
            ->get('Zend\View\Resolver\TemplatePathStack');

        if ($resolver->resolve($templateName) === false) {
            return false;
        }

        return true;
    }
}

/* End of file */
