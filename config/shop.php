<?php

return [

	'apc_enabled' => false, // enable for maximum performance if APCu is available
	'apc_prefix' => 'laravel:', // prefix for caching config and translation in APCu
	'num_formatter' => 'Locale', // locale based number formatter (alternative: "Standard")
	'pcntl_max' => 4, // maximum number of parallel command line processes when starting jobs
	'version' => env( 'APP_VERSION', 1 ), // shop CSS/JS file version
	'roles' => ['admin', 'editor'], // user groups allowed to access the admin backend
	'panel' => 'dashboard', // panel shown in admin backend after login

	'routes' => [
		   //Docs: https://aimeos.org/docs/latest/laravel/extend/#custom-routes
		  // Multi-sites: https://aimeos.org/docs/latest/laravel/customize/#multiple-shops
		  'routes' => [
			'admin'   => [
				'prefix'     => 'admin',
				'middleware' => ['web', 'auth'],   // ← أضِف auth هنا
			],
			'jqadm'   => [
				'prefix'     => 'admin/{site}/jqadm',
				'middleware' => ['web', 'auth'],
			],
			'graphql' => [
				'prefix'     => 'admin/{site}/graphql',
				'middleware' => ['web', 'auth'],
			],
			'jsonadm' => [
				'prefix'     => 'admin/{site}/jsonadm',
				'middleware' => ['web', 'auth'],
			],
		
			'jsonapi' => ['prefix' => 'jsonapi', 'middleware' => ['web', 'api']],
			'account' => ['prefix' => 'profile', 'middleware' => ['web', 'auth']],
			'default' => ['prefix' => 'shop', 'middleware' => ['web']],
			'basket'  => ['prefix' => 'shop', 'middleware' => ['web']],
			'checkout'=> ['prefix' => 'shop', 'middleware' => ['web']],
			'confirm' => ['prefix' => 'shop', 'middleware' => ['web']],
			'supplier'=> ['prefix' => 'brand', 'middleware' => ['web']],
			'page'    => ['prefix' => 'p', 'middleware' => ['web']],
			'home'    => ['middleware' => ['web']],
			'update'  => [],
		],
		
	],

	'admin' => [],

	'client' => [
		'html' => [
			'basket' => [
				'cache' => [
					// 'enable' => false, // Disable basket content caching for development
				],
			],
			'common' => [
				'cache' => [
					// 'force' => true // enforce caching for logged in users
				],
			],
			'catalog' => [
				'lists' => [
					'basket-add' => true, // shows add to basket in list views
					// 'infinite-scroll' => true, // load more products in list view
					// 'size' => 48, // number of products per page
				],
				'selection' => [
					'type' => [// how variant attributes are displayed
						'color' => 'radio',
						'length' => 'radio',
						'width' => 'radio',
					],
				],
			],
		],
	],

	'controller' => [
		'frontend' => [
			'catalog' => [
				'levels-always' => 3 // number of category levels for mega menu
			]
		]
	],

	'i18n' => [
	],

	'madmin' => [
		'cache' => [
			'manager' => [
				// 'name' => 'None', // Disable caching for development
			],
		],
		'log' => [
			'manager' => [
				// 'loglevel' => 7, // Enable debug logging into madmin_log table
			],
		],
	],

	'mshop' => [
		'locale' => [
			// =='site' => '<custom site code>', // used instead of "default"
			'site' => 'default', 
		]
	],


	'command' => [
	],

	'frontend' => [
	],

	'backend' => [
	],

];
