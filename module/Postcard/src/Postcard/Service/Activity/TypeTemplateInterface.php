<?php
namespace Postcard\Service\Activity;


interface TypeTemplateInterface
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
