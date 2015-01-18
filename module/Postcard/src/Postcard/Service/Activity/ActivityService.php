<?php
namespace Postcard\Service\Activity;

use Postcard\Service\AbstractService;
use Postcard\Service\Activity\TypeDefaultTemplateService;
use Postcard\Model\ActivityTemplatePriceRule;

class ActivityService extends AbstractService
{
    private $priceTypeMap = array(
        ActivityPriceRule::TYPE_FIXED => 'Postcard\Service\Activity\PriceRule\FixedPriceRule',
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
    public function getPrice($id) {
        $priceRuleConfig = $this->getServiceLocator()
            ->get("Postcard\Model\ActivityPriceRuleTable")
            ->getOneById($id);

        $priceRule = new $priceTypeMap[$priceRuleConfig->getType()];
        $priceRule->setServiceLocator($this->getServiceLocator());
        $conf = json_decode($priceRuleConfig->getPriceConf(), true);

        return $priceRule->getPrice($conf);
    }
}


/* End of file */
