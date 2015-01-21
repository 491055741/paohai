<?php
namespace Postcard\Service\Activity\PriceRule;

use Zend\ServiceManager\ServiceLocatorInterface;

use Postcard\Model\Order;

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
    public function getPrice(Order $order, $config) {
        $price = $config["defaultPrice"];
        $step = ksort($config["step"]);
        foreach ($config["step"] as $actPrice => $itemConf) {
            if ($this->checkItemConf($order->activityId, $order->userName, $actPrice, $itemConf)) {
                $price = $actPrice;
                break;
            }
        }

        return $price;
    }


    private function checkItemConf($actId, $userName, $price, $conf) {
        $currentTime = date("Y-m-d H:i:s");
        $beginTime = isset($conf["beginTime"]) ? 
            $conf["beginTime"] : NULL;
        $endTime = isset($conf["endTime"]) ?
            $conf["endTime"] : NULL;

        if ($beginTime && $currentTime < $beginTime) {
            return false;
        }
        if ($endTime && $currentTime > $endTime) {
            return false;
        }

        $recordTable = $this->getServiceLocator()
            ->get('Postcard\Model\ActivityJoinRecordTable');
        $condition = array(
            "actId" => $actId,
            "price" => $price,
        );
        if ($beginTime) {
            $condition["joinBeginTime"] = $beginTime;
        }
        if ($endTime) {
            $condition["joinEndTime"] = $endTime;
        }
        if (isset($conf["totalNum"])) {
            $resSet = $recordTable->getRecords($condition);
            if ($resSet->count() >= $conf["totalNum"]) {
                return false;
            }
        }
        if (isset($conf["perNum"])) {
            $condition["userName"] = $userName;
            $resSet = $recordTable->getRecords($condition);
            if ($resSet->count() >= $conf["perNum"]) {
                return false;
            }
        }

        return true;
    }
}


/* End of file */
