<?php
return [
    'view_manager' => [
        'template_path_stack' => [
            OMEKA_PATH . '/modules/Collecting/view',
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'collecting' => 'Collecting\View\Helper\Collecting',
        ],
    ],
    'controllers' => [
        'invokables' => [
            'Collecting\Controller\Admin\Index' => 'Collecting\Controller\Admin\IndexController',
            'Collecting\Controller\Admin\Form' => 'Collecting\Controller\Admin\FormController',
        ],
        'factories' => [
            'Collecting\Controller\Site\Index' => 'Collecting\Service\Controller\Site\IndexControllerFactory',
        ],
    ],
    'block_layouts' => [
        'invokables' => [
            'collecting' => 'Collecting\Site\BlockLayout\Collecting',
        ],
    ],
    'entity_manager' => [
        'mapping_classes_paths' => [
            OMEKA_PATH . '/modules/Collecting/src/Entity',
        ],
    ],
    'api_adapters' => [
        'invokables' => [
            'collecting_forms' => 'Collecting\Api\Adapter\CollectingFormAdapter',
            'collecting_items' => 'Collecting\Api\Adapter\CollectingItemAdapter',
        ],
    ],
    'navigation' => [
        'site' => [
            [
                'label' => 'Collecting', // @translate
                'route' => 'admin/site/slug/collecting/default',
                'action' => 'index',
                'useRouteMatch' => true,
                'pages' => [
                    [
                        'route' => 'admin/site/slug/collecting/id',
                        'visible' => false,
                    ],
                    [
                        'route' => 'admin/site/slug/collecting/default',
                        'visible' => false,
                    ],
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'site' => [
                'child_routes' => [
                    'collecting' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/collecting/:form-id/:action',
                            'defaults' => [
                                '__NAMESPACE__' => 'Collecting\Controller\Site',
                            ],
                            'constraints' => [
                                'form-id' => '\d+',
                            ],
                        ],
                    ],
                ],
            ],
            'admin' => [
                'child_routes' => [
                    'site' => [
                        'child_routes' => [
                            'slug' => [
                                'child_routes' => [
                                    'collecting' => [
                                        'type' => 'Literal',
                                        'options' => [
                                            'route' => '/collecting',
                                            'defaults' => [
                                                '__NAMESPACE__' => 'Collecting\Controller\Admin',
                                                'controller' => 'index',
                                                'action' => 'index',
                                            ],
                                        ],
                                        'may_terminate' => true,
                                        'child_routes' => [
                                            'id' => [
                                                'type' => 'Segment',
                                                'options' => [
                                                    'route' => '/:id[/:action]',
                                                    'constraints' => [
                                                        'id' => '\d+',
                                                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                                    ],
                                                    'defaults' => [
                                                        'action' => 'show',
                                                    ],
                                                ],
                                            ],
                                            'default' => [
                                                'type' => 'Segment',
                                                'options' => [
                                                    'route' => '/:action',
                                                    'constraints' => [
                                                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ]
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
