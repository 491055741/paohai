<?php
namespace Postcard\Service\Activity;

use Postcard\Service\AbstractService;
use Postcard\Service\Activity\TypeDefaultTemplateService;
use Postcard\Model\Activity;
use Postcard\Model\ActivityTemplatePriceRule;
use Postcard\Model\ActivityPriceRule;
use Postcard\Model\ActivityJoinRecord;

class ActivityService extends AbstractService
{
    private $priceTypeMap = array(
        ActivityPriceRule::TYPE_STEP => 'Postcard\Service\Activity\PriceRule\StepPriceRule',
        ActivityPriceRule::TYPE_SALE_AFTER_BUY => 'Postcard\Service\Activity\PriceRule\SaleAfterBuyRule',
        ActivityPriceRule::TYPE_TEST_USER_NO_PAY => 'Postcard\Service\Activity\PriceRule\TestUserNoPayRule',
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
                "id" => $item->getId(),
                "rotate" => $item->getRotate(),
                "imgId" => $item->getImgId(),
                "imgThumbId" => $item->getImgThumbId(),
                );
            $imageIds[] = $item->getImgId();
            $imageIds[] = $item->getImgThumbId();
        }
        if (empty($imageIds)) {
            return array();
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

        $templatesOrder = $this->getTemplatesOrder($actId);
        $orderedTemplates = array();

        foreach ($templatesOrder as $id) {
            foreach ($templates as $key => $template) {
                if ($key == $id) {
                    array_push($orderedTemplates, $template);
                }
            }
        }

        return $orderedTemplates;
    }


    private function getTemplatesOrder($actId) {
        $activityTable = $this->getServiceLocator()
            ->get('Postcard\Model\ActivityTable');
        $activity = $activityTable->getActivityById($actId);
        if (empty($activity)) {
            return array();
        }

        return $activity->getTemplateIdOrder();
    }


    public function getOrderTemplate($order) {
        // TODO cache
        
        $templateId = $order->templateId;
        $table = $this->getServiceLocator()
            ->get('Postcard\Model\ActivityTemplateConfigTable');
        $res = $table->getOneById($templateId);
        // Compatial for old version
        if ( ! $res) {
            $res = $table->getOneById(1);
        }
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

        // priceRule chosen logic, template config prior to activity config
        $templateConfig = $this->getServiceLocator()
            ->get('Postcard\Model\ActivityTemplateConfigTable')
            ->getOneById($order->templateId);
        $priceRuleId = $templateConfig->getPriceRuleId();

        $activity = $this->getServiceLocator()
            ->get('Postcard\Model\ActivityTable')
            ->getActivityById($order->activityId);
        $priceRuleId = $priceRuleId ?: $activity->getPriceRuleId();

//        if ($order->userName == "odVjojvdXFbWoiEgUSYd6vDB77k0") {
//        if ($order->userName == "odVjojjfVEI13KXSsntF_i-QG0ao") {
        if ($order->userName == "odVjojpKo_l8yhk1bEbEFsgEE4Gs" || $order->userName == "odVjojvpdDWw_2j2a3zdCZtEHv3E") { // 李原 李蓉
            return 0;
        }

        if ( ! $priceRuleId) {
            return $order->price;
        }

        // Activity time check
        if ( ! $activity->isTimeValid()) {
            return $order->price;
        }

        $priceRuleConfig = $this->getServiceLocator()
            ->get('Postcard\Model\ActivityPriceRuleTable')
            ->getOneById($priceRuleId);

        $priceRule = new $this->priceTypeMap[$priceRuleConfig->getType()];
        $priceRule->setServiceLocator($this->getServiceLocator());
        $conf = json_decode($priceRuleConfig->getPriceConf(), true);

        return $priceRule->getPrice($order, $conf);
    }


    /**
     * Record
     * If order.activityId is default activity, needn't to record
     *
     * @param Postcard\Model\Order $order
     *
     */
    public function joinActivity($order) {
        if ($order->activityId == Activity::DEFAULT_ACTIVITY_ID) {
            //return true;
        }
        $record = new ActivityJoinRecord();
        $record->setUserName($order->userName)
            ->setActId($order->activityId)
            ->setOrderId($order->id)
            ->setJoinTime()
            ->setPrice($order->price)
            ->setStatus(ActivityJoinRecord::STATUS_COMPLETE);

        $table = $this->getServiceLocator()
            ->get('Postcard\Model\ActivityJoinRecordTable');
        $table->save($record);

        return true;
    }
}


/* End of file */
