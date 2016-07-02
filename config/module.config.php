<?php
return [
    'view_manager' => [
        'template_path_stack' => [
            OMEKA_PATH . '/modules/Collecting/view',
        ],
    ],
    'controllers' => [
        'invokables' => [
            'Collecting\Controller\Admin\Index' => 'Collecting\Controller\Admin\IndexController',
            'Collecting\Controller\Admin\Form' => 'Collecting\Controller\Admin\FormController',
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
        'Collecting' => [
            [
                'label' => 'Forms', // @translate
                'route' => 'admin/site/slug/collecting/default',
                'action' => 'index',
                'useRouteMatch' => true,
            ],
            [
                'label' => 'Admin', // @translate
                'route' => 'admin/site/slug/collecting/default',
                'action' => 'admin',
                'useRouteMatch' => true,
                'resource' => 'Omeka\Entity\Site',
                'privilege' => 'create',
            ],
        ],
    ],
    'router' => [
        'routes' => [
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
