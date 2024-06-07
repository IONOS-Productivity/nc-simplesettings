<?php
declare(strict_types=1);
return [
	'resources' => [
		'AuthSettings' => ['url' => '/authtokens' , 'root' => ''],
	],
	'routes' => [
		['name' => 'MainJsSourcemapController#sourceMap', 'url' => '/js/main.js.map', 'verb' => 'GET']
	]
];
