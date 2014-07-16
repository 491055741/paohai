<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Wxpay\Controller\Wxpay' => 'Wxpay\Controller\WxpayController',
        ),
    ),

    'router' => array(
        'routes' => array(
            'wxpay' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/wxpay[/][:action][/:id]',
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
    ),
);
