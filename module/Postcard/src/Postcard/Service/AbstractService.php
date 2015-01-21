<?php
namespace Postcard\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AbstractService implements
    ServiceLocatorAwareInterface
{
    protected $services;


    /**
     * By default, the Zend Framework MVC registers an initializer
     * that will inject the ServiceManager instance, which is an
     * implementationm of Zend\ServiceManager\ServiceLocatorInterface,
     * into any class implementing 
     * Zend\ServiceManager\ServiceLocatorAwareInterface
     */
    public function setServiceLocator(ServiceLocatorInterface $services) {
        $this->services = $services;
    }


    public function getServiceLocator() {
        return $this->services;
    }
}


/* End of file */
