<?php

return [

    // رقم النسخة للإصدارات الثابتة
    'version' => env('APP_VERSION', 1),

    // الأدوار التي يسمح لها بالوصول للوحة الإدارة
    'roles' => ['admin', 'editor'],

    // اللوحة الافتراضية بعد تسجيل الدخول
    'panel' => 'dashboard',

    /*
    |--------------------------------------------------------------------------
    | إعداد المسارات
    |--------------------------------------------------------------------------
    */
    'routes' => [
        'routes' => [
            'admin'   => [
                'prefix'     => 'admin',
                'middleware' => ['web','auth'],
            ],
            'jqadm'   => [
                'prefix'     => 'admin/{site}/jqadm',
                'middleware' => ['web','auth'],
            ],
            'graphql' => [
                'prefix'     => 'admin/{site}/graphql',
                'middleware' => ['web','auth'],
            ],
            'jsonadm' => [
                'prefix'     => 'admin/{site}/jsonadm',
                'middleware' => ['web','auth'],
            ],
            'jsonapi' => [
                'prefix'     => 'jsonapi',
                'middleware' => ['web','api'],
            ],
            'account' => [
                'prefix'     => 'profile',
                'middleware' => ['web','auth'],
            ],
            'default' => [
                'prefix'     => 'shop',
                'middleware' => ['web'],
            ],
            'basket'  => [
                'prefix'     => 'shop',
                'middleware' => ['web'],
            ],
            'checkout'=> [
                'prefix'     => 'shop',
                'middleware' => ['web'],
            ],
            'confirm' => [
                'prefix'     => 'shop',
                'middleware' => ['web'],
            ],
            'supplier'=> [
                'prefix'     => 'brand',
                'middleware' => ['web'],
            ],
            'page'    => [
                'prefix'     => 'p',
                'middleware' => ['web'],
            ],
            'home'    => [
                'middleware' => ['web'],
            ],
            'update'  => [],
        ],
    ],

    // إعداد لوحة الإدارة
    'admin' => [
        'roles' => ['admin', 'editor'],
    ],

    /*
    |--------------------------------------------------------------------------
    | إعداد الموارد (قاعدة البيانات والملفات الثابتة)
    |--------------------------------------------------------------------------
    | يجب تعريف fs-theme وfs-media وغيرها كي يعرف Aimeos مكان الأصول.
    */
    'resource' => [
        // إعداد الاتصال بقاعدة البيانات مع تفعيل وضع ANSI
        'db' => [
            'adapter'  => env('DB_CONNECTION', 'mysql'),
            'host'     => env('DB_HOST', '127.0.0.1'),
            'port'     => env('DB_PORT', '3306'),
            'socket'   => env('DB_SOCKET', ''),
            'database' => env('DB_DATABASE', 'skfari_db'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'stmt'     => [
                // تفعيل وضع ANSI حتى تقبل MySQL علامات الاقتباس المزدوجة
                "SET SESSION sql_mode='ANSI'",
            ],
        ],
        // ملفات الوسائط (صور المنتجات وغيرها)
        'fs-media' => [
            'adapter'  => 'Standard',
            'tempdir'  => storage_path('tmp'),
            'basedir'  => public_path('aimeos'),
            'baseurl'  => rtrim(env('ASSET_URL', PHP_SAPI === 'cli' ? env('APP_URL') : ''), '/')
                        . '/aimeos',
        ],
        // أيقونات الملفات
        'fs-mimeicon' => [
            'adapter'  => 'Standard',
            'tempdir'  => storage_path('tmp'),
            'basedir'  => public_path('vendor/shop/mimeicons'),
            'baseurl'  => rtrim(env('ASSET_URL', PHP_SAPI === 'cli' ? env('APP_URL') : ''), '/')
                        . '/vendor/shop/mimeicons',
        ],
        // ملفات السمات (CSS/JS/صور الواجهة)
        'fs-theme' => [
            'adapter'  => 'Standard',
            'tempdir'  => storage_path('tmp'),
            'basedir'  => public_path('vendor/shop/themes'),
            'baseurl'  => rtrim(env('ASSET_URL', PHP_SAPI === 'cli' ? env('APP_URL') : ''), '/')
                        . '/vendor/shop/themes',
        ],
        // بقية الموارد الافتراضية
        'fs-admin' => [
            'adapter' => 'Standard',
            'tempdir' => storage_path('tmp'),
            'basedir' => storage_path('admin'),
        ],
        'fs-export' => [
            'adapter' => 'Standard',
            'tempdir' => storage_path('tmp'),
            'basedir' => storage_path('export'),
        ],
        'fs-import' => [
            'adapter' => 'Standard',
            'tempdir' => storage_path('tmp'),
            'basedir' => storage_path('import'),
        ],
        'fs-secure' => [
            'adapter' => 'Standard',
            'tempdir' => storage_path('tmp'),
            'basedir' => storage_path('secure'),
        ],
        // إعداد قائمة الانتظار (Queue)
        'mq' => [
            'adapter' => 'Standard',
            'db'      => 'db',
        ],
        // إعداد البريد الإلكتروني
        'email' => [
            'from-email' => config('mail.from.address'),
            'from-name'  => config('mail.from.name'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | إعدادات واجهة العميل HTML
    |--------------------------------------------------------------------------
    */
    'client' => [
        'html' => [
            'basket' => [
                'cache' => [
                    // 'enable' => false,
                ],
            ],
            'common' => [
                'cache' => [
                    // 'force' => true,
                ],
            ],
            'catalog' => [
                'lists' => [
                    'basket-add' => true,
                    // 'infinite-scroll' => true,
                    // 'size' => 48,
                ],
                'selection' => [
                    'type' => [
                        'color'  => 'radio',
                        'length' => 'radio',
                        'width'  => 'radio',
                    ],
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | إعدادات المتحكمات والإدارة
    |--------------------------------------------------------------------------
    */
    'controller' => [
        'frontend' => [
            'catalog' => [
                'levels-always' => 3, // عدد مستويات الفئات في قائمة التصنيفات
            ],
        ],
    ],

    'i18n'   => [],
    'madmin' => [
        'cache' => [
            'manager' => [
                // 'name' => 'None',
            ],
        ],
        'log' => [
            'manager' => [
                // 'loglevel' => 7,
            ],
        ],
    ],

    'mshop' => [
        'locale' => [
            // 'site' => '<custom site code>',
        ],
    ],

    'command'  => [],
    'frontend' => [],
    'backend'  => [],
];
