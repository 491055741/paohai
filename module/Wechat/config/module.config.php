<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Wechat\Controller\Wechat' => 'Wechat\Controller\WechatController',
        ),
    ),

    'router' => array(
        'routes' => array(
            'wechat' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/wechat[/][:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Wechat\Controller\Wechat',
                        'action'     => 'index',
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
            // 'wechat/wechat/index' => __DIR__ . '/../view/script/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            'wechat' => __DIR__ . '/../view',
        ),
    ),
);
