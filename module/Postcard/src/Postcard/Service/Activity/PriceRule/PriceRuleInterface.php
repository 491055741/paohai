<?php
namespace Postcard\Service\Activity;

use Zend\ServiceManager\ServiceLocatorAwareInterface;


interface PriceRuleInterface extends ServiceLocatorAwareInterface
{
    /**
     * Get order price by template Id
     *
     * @param string $config
     *
     * @return int $price
     */
    public function getPrice($config);

}

/* End of file */
