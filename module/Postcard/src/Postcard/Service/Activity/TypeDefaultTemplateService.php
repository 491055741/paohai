<?php
namespace Postcard\Service\Activity;

use Postcard\Service\Activity\TypeTemplateInterface;

class TypeDefaultTemplateService implements TypeTemplateInterface
{
    /**
     * @param mixed $config. field priceConf of table 
     *      activity_template_price_rule. The value is 
     *      actual price. eg: 299
     */
    public function getPrice($config) {
        return $config;
    }
}


/* End of file */
