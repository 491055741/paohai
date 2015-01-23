<?php
return array(
    'service_manager' => array(
        'invokables' => array(
            'Wxpay\Service\WxpayService' => 'Wxpay\Service\WxpayService',
            ),
        ),
    'controllers' => array(
        'invokables' => array(
            'Wxpay\Controller\Wxpay' => 'Wxpay\Controller\WxpayController',
            'Wxpay\Controller\Console' => 'Wxpay\Controller\ConsoleController',
        ),
    ),

    'router' => array(
        'routes' => array(
            'wxpay' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/wxpay[/][:action][/][/:id]', // '[/]' after '[:action]' is a workaround for wrong feedback url 'http://paohai.ikamobile.com/wxpay/feedback/' (extra '/' at tail)
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Wxpay\Controller\Wxpay',
                        'action'     => 'pay',
                    ),
                ),
            ),
        ),
    ),

    'console' => array(
        'router' => array(
            'routes' => array(
                // Console routes go here
                'filter-sales-order' => array(
                    'options' => array(
                        'route' => 'order filter-for-sale <beginDate> <endDate>',
                        'defaults' => array(
                            'controller' => 'Wxpay\Controller\Console',
                            'action' => 'filterForSale',
                        ),
                    ),
                ),
                'refund_to_user' => array(
                    'options' => array(
                        'route' => 'refund',
                        'defaults' => array(
                            'controller' => 'Wxpay\Controller\Console',
                            'action' => 'refundToUser',
                        ),
                    ),
                ),
            ),
        ),
    ),

    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            'wxpay' => __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
);
