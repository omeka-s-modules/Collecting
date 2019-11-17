<?php
namespace Collecting;

return [
    'view_manager' => [
        'template_path_stack' => [
            OMEKA_PATH . '/modules/Collecting/view',
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'formPromptHtml' => Form\View\Helper\FormPromptHtml::class,
        ],
        'factories' => [
            'collectingPrepareForm' => Service\ViewHelper\CollectingPrepareFormFactory::class,
            'collecting' => Service\ViewHelper\CollectingFactory::class,
        ],
    ],
    'translator' => [
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => OMEKA_PATH . '/modules/Collecting/language',
                'pattern' => '%s.mo',
                'text_domain' => null,
            ],
        ],
    ],
    'controllers' => [
        'invokables' => [
            'Collecting\Controller\SiteAdmin\Form' => Controller\SiteAdmin\FormController::class,
            'Collecting\Controller\SiteAdmin\Item' => Controller\SiteAdmin\ItemController::class,
            // 'Collecting\Controller\SiteAdmin\User' => Controller\SiteAdmin\UserController::class,
        ],
        'factories' => [
            'Collecting\Controller\Site\Index' => Service\Controller\Site\IndexControllerFactory::class,
        ],
    ],
    'controller_plugins' => [
        'invokables' => [
            'collectingCurrentForm' => Mvc\Controller\Plugin\CollectingCurrentForm::class,
        ],
    ],
    'block_layouts' => [
        'invokables' => [
            'collecting' => Site\BlockLayout\Collecting::class,
        ],
    ],
    'entity_manager' => [
        'mapping_classes_paths' => [
            OMEKA_PATH . '/modules/Collecting/src/Entity',
        ],
        'proxy_paths' => [
            OMEKA_PATH . '/modules/Collecting/data/doctrine-proxies',
        ],
    ],
    'api_adapters' => [
        'invokables' => [
            'collecting_forms' => Api\Adapter\CollectingFormAdapter::class,
            'collecting_items' => Api\Adapter\CollectingItemAdapter::class,
            'collecting_users' => Api\Adapter\CollectingUserAdapter::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            'Collecting\MediaTypeManager' => Service\MediaTypeManagerFactory::class,
        ],
    ],
    'collecting_media_types' => [
        'invokables' => [
            'url' => MediaType\Url::class,
            'html' => MediaType\Html::class,
        ],
        'factories' => [
            'upload' => Service\MediaType\UploadFactory::class,
            'upload_multiple' => Service\MediaType\UploadMultipleFactory::class,
        ],
    ],
    'navigation' => [
        'site' => [
            [
                'label' => 'Collecting', // @translate
                'route' => 'admin/site/slug/collecting',
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
                    [
                        'route' => 'admin/site/slug/collecting/item',
                        'visible' => false,
                    ],
                    [
                        'route' => 'admin/site/slug/collecting/item/default',
                        'visible' => false,
                    ],
                    [
                        'route' => 'admin/site/slug/collecting/item/id',
                        'visible' => false,
                    ],
                ],
            ],
        ],
        'Collecting' => [
            [
                'label' => 'Form Information', // @translate
                'route' => 'admin/site/slug/collecting/id',
                'action' => 'show',
                'useRouteMatch' => true,
            ],
            [
                'label' => 'Collected Items', // @translate
                'route' => 'admin/site/slug/collecting/item',
                'action' => 'index',
                'useRouteMatch' => true,
                'pages' => [
                    [
                        'route' => 'admin/site/slug/collecting/item/default',
                        'visible' => false,
                    ],
                    [
                        'route' => 'admin/site/slug/collecting/item/id',
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
                        'type' => \Zend\Router\Http\Segment::class,
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
                    'collecting-item' => [
                        'type' => \Zend\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/collecting/item/:item-id',
                            'defaults' => [
                                '__NAMESPACE__' => 'Collecting\Controller\Site',
                                'action' => 'item-show',
                            ],
                            'constraints' => [
                                'item-id' => '\d+',
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
                                        'type' => \Zend\Router\Http\Literal::class,
                                        'options' => [
                                            'route' => '/collecting',
                                            'defaults' => [
                                                '__NAMESPACE__' => 'Collecting\Controller\SiteAdmin',
                                                'controller' => 'Form',
                                                'action' => 'index',
                                            ],
                                        ],
                                        'may_terminate' => true,
                                        'child_routes' => [
                                            'id' => [
                                                'type' => \Zend\Router\Http\Segment::class,
                                                'options' => [
                                                    'route' => '/:form-id[/:action]',
                                                    'constraints' => [
                                                        'form-id' => '\d+',
                                                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                                    ],
                                                    'defaults' => [
                                                        'action' => 'show',
                                                    ],
                                                ],
                                            ],
                                            'default' => [
                                                'type' => \Zend\Router\Http\Segment::class,
                                                'options' => [
                                                    'route' => '/:action',
                                                    'constraints' => [
                                                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                                    ],
                                                ],
                                            ],
                                            'item' => [
                                                'type' => \Zend\Router\Http\Segment::class,
                                                'options' => [
                                                    'route' => '/:form-id/item',
                                                    'constraints' => [
                                                        'form-id' => '\d+',
                                                    ],
                                                    'defaults' => [
                                                        'controller' => 'Item',
                                                        'action' => 'index',
                                                    ],
                                                ],
                                                'may_terminate' => true,
                                                'child_routes' => [
                                                    'id' => [
                                                        'type' => \Zend\Router\Http\Segment::class,
                                                        'options' => [
                                                            'route' => '/:item-id[/:action]',
                                                            'constraints' => [
                                                                'item-id' => '\d+',
                                                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                                            ],
                                                            'defaults' => [
                                                                'action' => 'show',
                                                            ],
                                                        ],
                                                    ],
                                                    'default' => [
                                                        'type' => \Zend\Router\Http\Segment::class,
                                                        'options' => [
                                                            'route' => '/:action',
                                                            'constraints' => [
                                                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                                            ],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
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
