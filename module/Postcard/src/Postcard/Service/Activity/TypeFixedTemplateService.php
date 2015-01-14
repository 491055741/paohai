<?php
namespace Postcard\Service\Activity;

use Postcard\Service\Activity\TypeTemplateInterface;


class TypeFixedTemplateService implements TypeTemplateInterface
{
    /**
     * @param array $config. field priceConf of table
     *      activity_template_price_rule. The format value
     *      as below:
     *          {
     *              defaultPrice: 299, 
     *              actPrice: 10, 
     *              totalNum: 1000,     // optional
     *              perNum: 2,          // optional
     *              global: true        // optional
     *          }
     */
    public function getPrice($config) {
        //TODO
    }
}



/* End of file */
