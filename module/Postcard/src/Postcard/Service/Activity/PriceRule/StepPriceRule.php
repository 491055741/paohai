<?php
namespace Postcard\Service\Activity\PriceRule;

use Zend\ServiceManager\ServiceLocatorInterface;

class StepPriceRule implements PriceRuleInterface
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
    public function getPrice($config) {
        $price = $config["defaultPrice"];
        $step = ksort($config["step"]);
        foreach ($step as $actPrice => $itemConf) {
            if ($this->checkItemConf($itemConf)) {
                $price = $actPrice;
                break;
            }
        }

        return $price;
    }


    private function checkItemConf($conf) {
        $recordTable = $this->getServiceLocator()
            ->get('Postcard\Model\ActivityJoinRecordTable');

        if (isset($conf["totalNum"])) {

        }

        if (isset($conf["perNum"])) {

        }

        if (isset($conf["beginTime"])) {

        }

        if (isset($conf["endTime"])) {

        }

        return true;
    }
}


/* End of file */
