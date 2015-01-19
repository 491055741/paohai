<?php
namespace Postcard\Service\Activity;

use Postcard\Service\AbstractService;
use Postcard\Service\Activity\TypeDefaultTemplateService;
use Posrcard\Model\Activity;
use Postcard\Model\ActivityTemplatePriceRule;

class ActivityService extends AbstractService
{
    private $priceTypeMap = array(
        ActivityPriceRule::TYPE_STEP => 'Postcard\Service\Activity\PriceRule\StepPriceRule',
        );


    /**
     *
     */
    public function getActivityInfo() {

    }

    /**
     * @param int $actId
     * 
     * @return array $templates. eg:
     *      array(
     *          id => array(thumbUrl, urla, rotate),
     *          ...             
     *      )
     */
    public function getTemplates($actId) {

    }


    /**
     * Caculate price by template type and config
     *
     */
    public function getPrice($order) {
        $defaultPrice = 299;
        if ($order->activityId == Activity::DEFAULT_ACTIVITY_ID) {
            return $defaultPrice;
        }

        // priceRule chosen logic, template config prior to activity config
        $templateConfig = $this->getServiceLocator()
            ->get('Postcard\Model\ActivityTemplateConfigTable')
            ->getOneById($order->templateId);
        $priceRuleId = $templateConfig->getPriceRuleId();

        if ( ! $priceRuleId) {
            $activity = $this->getServiceLocator()
                ->get('Postcard\Model\ActivityTable')
                ->getActivityById($order->activityId);
            $priceRuleId = $activity->getPriceRuleId();
        }

        $priceRuleConfig = $this->getServiceLocator()
            ->get('Postcard\Model\ActivityPriceRuleTable')
            ->getOneById($priceRuleId);

        $priceRule = new $priceTypeMap[$priceRuleConfig->getType()];
        $priceRule->setServiceLocator($this->getServiceLocator());
        $conf = json_decode($priceRuleConfig->getPriceConf(), true);

        return $priceRule->getPrice($conf);
    }
}


/* End of file */
