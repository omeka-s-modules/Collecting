<?php
return [
    'view_manager' => [
        'template_path_stack' => [
            OMEKA_PATH . '/modules/Collecting/view',
        ],
    ],
    'controllers' => [
        'invokables' => [
            'Collecting\Controller\Index' => 'Collecting\Controller\IndexController',
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
            'collecting_prompts' => 'Collecting\Api\Adapter\CollectingPromptAdapter',
        ],
    ],
    'navigation' => [
        'site' => [
            [
                'label' => 'Collecting', // @translate
                'route' => 'admin/site/collecting',
                'action' => 'index',
                'resource' => 'Collecting\Controller\Index',
                'useRouteMatch' => true,
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'admin' => [
                'child_routes' => [
                    'site' => [
                        'child_routes' => [
                            'collecting' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/s/:site-slug/collecting[/:action]',
                                    'defaults' => [
                                        '__NAMESPACE__' => 'Collecting\Controller',
                                        'controller' => 'index',
                                        'action' => 'index',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
