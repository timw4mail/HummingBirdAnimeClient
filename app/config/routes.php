<?php

return [
	'convention' => [
		'default_namespace' => '\\Aviat\\AnimeClient\\Controller',
		'default_controller' => '\\Aviat\\AnimeClient\\Controller\\Anime',
		'default_method' => 'index'
	],
	// Routes on all controllers
	'common' => [
		'update' => [
			'path' => '/{controller}/update',
			'action' => 'update',
			'verb' => 'post',
			'tokens' => [
				'controller' => '[a-z_]+'
			]
		],
		'login_form' => [
			'path' => '/{controller}/login',
			'action' => 'login',
			'verb' => 'get',
			'tokens' => [
				'controller' => '[a-z_]+'
			]
		],
		'login_action' => [
			'path' => '/{controller}/login',
			'action' => 'login_action',
			'verb' => 'post',
			'tokens' => [
				'controller' => '[a-z_]+'
			]
		],
		'logout' => [
			'path' => '/{controller}/logout',
			'action' => 'logout',
			'tokens' => [
				'controller' => '[a-z_]+'
			]
		],
	],
	// Routes on collection controller
	'collection' => [
		'collection_add_form' => [
			'path' => '/collection/add',
			'action' => 'form',
			'params' => [],
		],
		'collection_edit_form' => [
			'path' => '/collection/edit/{id}',
			'action' => 'form',
			'tokens' => [
				'id' => '[0-9]+'
			]
		],
		'collection_add' => [
			'path' => '/collection/add',
			'action' => 'add',
			'verb' => 'post'
		],
		'collection_edit' => [
			'path' => '/collection/edit',
			'action' => 'edit',
			'verb' => 'post'
		],
		'collection' => [
			'path' => '/collection/view{/view}',
			'action' => 'index',
			'params' => [],
			'tokens' => [
				'view' => '[a-z_]+'
			]
		],
	],
];
