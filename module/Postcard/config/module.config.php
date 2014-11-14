<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Postcard\Controller\Postcard' => 'Postcard\Controller\PostcardController',
            'Postcard\Controller\Contact' => 'Postcard\Controller\ContactController',
        ),
    ),

    'router' => array(
        'routes' => array(
            'postcard' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/postcard[/][:action][/:id][/:status]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]*',
                        'status' => '[0-9]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Postcard\Controller\Postcard',
                        'action'     => 'index',
                    ),
                ),
            ),
            'contacts' => array(
                'type' => "segment",
                "options" => array(
                    "route" => "/contact[/:action]",
                    "constraints" => array(
                        "action" => "[a-zA-Z][a-zA-Z0-9_-]*",
                    ),
                    "defaults" => array(
                        "controller" => "Postcard\Controller\Contact",
                        "action" => "contactspage",
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
        // 'template_map' => array(
        //     'postcard/postcard/index' => __DIR__ . '/../view/script/index.phtml',
        //     'postcard/postcard/pay'   => __DIR__ . '/../view/script/pay.phtml',
        //     'postcard/postcard/makeOrder' => __DIR__ . '/../view/script/makeOrder.phtml',
        //     'postcard/postcard/orders' => __DIR__ . '/../view/script/orders.phtml',
        //     'postcard/postcard/preview' => __DIR__ . '/../view/script/preview.phtml',
        //     'error/404'               => __DIR__ . '/../view/error/404.phtml',
        //     'error/index'             => __DIR__ . '/../view/error/index.phtml',
        // ),
        'template_path_stack' => array(
            'postcard' => __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
);
