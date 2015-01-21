<?php
namespace Postcard\Service\Activity;

use Zend\ServiceManager\ServiceLocatorAwareInterface;

use Postcard\Model\Order;


interface PriceRuleInterface extends ServiceLocatorAwareInterface
{
    /**
     * Get order price by template Id
     *
     * @param int $actId
     * @param string $config
     *
     * @return int $price
     */
    public function getPrice(Order $order, $config);

}

/* End of file */
