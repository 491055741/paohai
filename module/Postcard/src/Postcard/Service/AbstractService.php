<?php
namespace Postcard\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Http\Request as HttpRequest;


class AbstractService implements
    ServiceLocatorAwareInterface
{
    protected $services;
    protected $requst;


    /**
     * By default, the Zend Framework MVC registers an initializer
     * that will inject the ServiceManager instance, which is an
     * implementationm of Zend\ServiceManager\ServiceLocatorInterface,
     * into any class implementing 
     * Zend\ServiceManager\ServiceLocatorAwareInterface
     */
    public function setServiceLocator(ServiceLocatorInterface $services) {
        $this->services = $services;
        return $this;
    }


    public function getServiceLocator() {
        return $this->services;
    }


    public function setRequest(HttpRequest $requst) {
        $this->requst = $requst;
        return $this;
    }


    public function getRequest() {
        return $this->requst;
    }
}


/* End of file */
