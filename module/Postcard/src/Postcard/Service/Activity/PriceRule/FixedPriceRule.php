<?php
namespace Postcard\Service\Activity\PriceRule;

use Zend\ServiceManager\ServiceLocatorInterface;


class FixedPriceRule implements PriceRuleInterface
{
    private $services;


    public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
        $this->services = $serviceLocator;
    }


    public function getServiceLocator() {
        return $this->services;
    }



    /**
     * @param array $config. field priceConf of table
     *      activity_price_rule. The format value
     *      as below:
     *          {
     *              price: 299,  // required
     *          }
     */
    public function getPrice($config) {
        return $config["price"];
    }
}



/* End of file */
