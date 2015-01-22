<?php
namespace Postcard\Controller;
include_once(dirname(__FILE__)."/../../../../Wxpay/view/wxpay/wxpay/CommonUtil.php");

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use CommonUtil;
use Postcard\Service\ActivityService;


class ActivityController extends AbstractActionController
{
    /**
     * List current valid activity
     */
    public function indexAction() {

    }


    public function introAction() {
        $actId = $this->params()->fromRoute('id', '1');
        $userName = $this->getRequest()->getQuery('userName');

        $util = new CommonUtil();
        $util->setServiceLocator($this->getServiceLocator());
        $accessToken = $util->getAccessToken();

        $viewModel = new ViewModel(array(
            'actId' => $actId,
            'userName' => $userName,
            'accessToken' => $accessToken,
            ));
        $viewModel->setTerminal(true);

        return $viewModel;
    }
}

/* End of file */
