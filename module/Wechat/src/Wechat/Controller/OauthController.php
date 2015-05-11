<?php
namespace Wechat\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;


class OauthController extends AbstractActionController
{
    /**
     * Invoke by wx
     */
    public function callbackAction() {
        $requst = $this->getRequest();
        $code = $requst->getQuery("code");
        $state = $requst->getQuery("state");
        $service = $this->getServiceLocator()
            ->get("Wechat\Service\OauthService");
        $redirectUrl = $service->OauthCallback($code, $state);

        if ($redirectUrl) {
            Header("Location: $redirectUrl");
            exit;
        }

        return $this->errorViewModel(array(
            "code" => 0,
            "msg" => "授权失败，请重试",
        ));
    }
}