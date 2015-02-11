<?php
namespace Postcard\Service\Activity\PriceRule;

use Zend\ServiceManager\ServiceLocatorInterface;


class BaseRule
{
    protected $services;


    public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
        $this->services = $serviceLocator;
    }


    public function getServiceLocator() {
        return $this->services;
    }

}


/* End of file */
