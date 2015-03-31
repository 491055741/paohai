<?php
namespace Postcard\Service\Activity\PriceRule;

use Zend\ServiceManager\ServiceLocatorInterface;

use Postcard\Model\Order;

class TestUserNoPayRule implements PriceRuleInterface
{
    private $services;


    public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
        $this->services = $serviceLocator;
    }


    public function getServiceLocator() {
        return $this->services;
    }



    /**
     * @param mixed $config. field priceConf of table
     *      activity_price_rule. The value is
     *      actual price. eg:
     *          {
     *              defaultPrice: 299,                  // required
     *              step: {
     *                  0: {
     *                      totalNum: 100,              // optional
     *                      perNum: 1,                  // optional
     *                      beginTime: 2015-01-10,      // optional
     *                      endTime: 2015-01-12,        // optional
     *                  },
     *                  100: {
     *                      totalNum: 500,
     *                      perNum: 2,
     *                      beginTime: 2015-01-10,
     *                      endTime: 2015-01-20,
     *                  }
     *                  ...
     *              }
     *          }
     *
     */
    public function getPrice(Order $order, $config) {
        $price = $config["defaultPrice"];

        if ($order->userName == "odVjojvdXFbWoiEgUSYd6vDB77k0") {
            $price = 0;
        }

        return $price;
    }
}
