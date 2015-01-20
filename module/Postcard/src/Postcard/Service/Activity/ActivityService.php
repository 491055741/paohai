<?php
namespace Postcard\Service\Activity;

use Postcard\Service\AbstractService;
use Postcard\Service\Activity\TypeDefaultTemplateService;
use Postcard\Model\Activity;
use Postcard\Model\ActivityTemplatePriceRule;
use Postcard\Model\ActivityPriceRule;

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
     *          id => array(
     *              "imgId" => 123,
     *              "imgThumbId" => 234,
     *              "thumbUrl" => xxxxx, 
     *              "url" => "xxxxxx", 
     *              "rotate" => "xxxxx"
     *              ),
     *          ...             
     *      )
     */
    public function getTemplates($actId) {
        // TODO cache

        $templates = array();
        $table = $this->getServiceLocator()
            ->get('Postcard\Model\ActivityTemplateConfigTable');
        $res = $table->getAllByActId($actId);
        $imageIds = array();
        foreach ($res as $item) {
            $templates[$item->getId()] = array(
                "rotate" => $item->getRotate(),
                "imgId" => $item->getImgId(),
                "imgThumbId" => $item->getImgThumbId(),
                );
            $imageIds[] = $item->getImgId();
            $imageIds[] = $item->getImgThumbId();
        }
        
        $imgTable = $this->getServiceLocator()
            ->get('Postcard\Model\ImageTable');
        $res = $imgTable->getUrls($imageIds);
        $imgs = array();
        foreach ($res as $item) {
            $imgs[$item->getId()] = $item->getUrl(); 
        }

        foreach($templates as $id => &$info) {
            $info["url"] = $imgs[$info["imgId"]];
            $info["thumbUrl"] = $imgs[$info["imgThumbId"]];
        }

        return $templates;
    }


    public function getOrderTemplate($order) {
        // TODO cache
        
        $templateId = $order->templateId;
        $table = $this->getServiceLocator()
            ->get('Postcard\Model\ActivityTemplateConfigTable');
        $res = $table->getOneById($templateId);
        $template = array(
            "rotate" => $res->getRotate(),
            "imgId" => $res->getImgId(),
            "imgThumbId" => $res->getImgThumbId(),
            );

        $imgTable = $this->getServiceLocator()
            ->get('Postcard\Model\ImageTable');
        $res = $imgTable->getUrls(array(
            $template["imgId"], $template["imgThumbId"]
        ));
        $imgs = array();
        foreach ($res as $item) {
            $imgs[$item->getId()] = $item->getUrl(); 
        }

        $template["url"] = $imgs[$template["imgId"]];
        $template["thumbUrl"] = $imgs[$template["imgThumbId"]];

        return $template;
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
