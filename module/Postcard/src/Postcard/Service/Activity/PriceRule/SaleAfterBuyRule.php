<?php
namespace Postcard\Service\Activity\PriceRule;

use Postcard\Model\Order;
use Postcard\Model\ActivityJoinRecord;


class SaleAfterBuyRule extends BaseRule implements PriceRuleInterface
{

    /**
     * @param mixed $config. field priceConf of table
     *  activity_price_rule. eg:
     *      {
     *          defaultPrice: 299,              // required
     *          rule: {
     *              // 2015-02-14 ~ 2015-03-14, 正价支付一张后，随后的一张价格为0
     *              0: {
     *                  baseNum: 1,             // required
     *                  beginTime: 2015-02-14,  // optional
     *                  endTime: 2015-03-14,    // optional
     *              }
     *              ...
     *          }
     *      }
     *
     *
     */
    public function getPrice(Order $order, $config) {
        $price = $config["defaultPrice"];
        foreach ($config["rule"] as $actPrice => $itemConf) {
            if ($this->checkItemConf(
                $order->activityId, $order->userName, $config["defaultPrice"], $itemConf
            )) {
                $price = $actPrice;
                break;
            }
        }

        return $price;
    }


    private function checkItemConf($actId, $userName, $defaultPrice, $conf) {
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
            "userName" => $userName,
            "status" => ActivityJoinRecord::STATUS_COMPLETE,
            );
        if ($beginTime) {
            $condition["joinBeginTime"] = $beginTime;
        }
        if ($endTime) {
            $condition["joinEndTime"] = $endTime;
        }
        $resSet = $recordTable->getRecords($condition, $conf["baseNum"], "joinTime DESC");

        if ($resSet->count() < $conf["baseNum"]) {
            return false;
        }

        foreach ($resSet as $item) {
            if ($item->getPrice() != $defaultPrice) {
                return false;
            }
        }

        return true;
    }
}


/* End of file */
