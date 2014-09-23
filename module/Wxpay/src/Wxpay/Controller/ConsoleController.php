<?php
namespace Wxpay\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Console\Request as ConsoleRequest;


class ConsoleController extends AbstractActionController
{
    protected $orderTable;


    public function filterForSaleAction()
    {
        $this->onlyInvokeInConsole();

        $request = $this->getRequest();
        $beginDate = date("Y-m-d", strtotime($request->getParam("beginDate")));
        $endDate = date("Y-m-d", strtotime($request->getParam("endDate")));
        var_dump($beginDate, $endDate);
        if ($endDate < $beginDate) {
            return "beginDate must less than endData\n";
        }

        // TODO filter
        return "Done";

    }


    private function onlyInvokeInConsole() {
        if ( ! ($this->getRequest() instanceof ConsoleRequest)) {
            throw new \RuntimeException("You can only use this action from a console!");
        }
    }


    private function getOrderTable()
    {
        if (!$this->orderTable) {
            $sm = $this->getServiceLocator();
            $this->orderTable = $sm->get('Postcard\Model\orderTable');
        }
        return $this->orderTable;
    }
}


/* End of file */
