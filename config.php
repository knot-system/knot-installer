<?php

$homestead_version = '0.1.3-pre';

$sources = [
	'eigenheim' => [
		'name' => 'Eigenheim',
		'target' => 'eigenheim/',
		'zipball' => 'https://api.github.com/repos/maxhaesslein/eigenheim/releases',
	],
	'sekretaer' => [
		'name' => 'Sekretär',
		'target' => 'sekretaer/',
		'zipball' => 'https://api.github.com/repos/maxhaesslein/sekretaer/releases',
	],
	'postamt' => [
		'name' => 'Postamt',
		'target' => 'postamt/',
		'zipball' => 'https://api.github.com/repos/maxhaesslein/postamt/releases',
	]
];

$php_min_version_major = 8;

$useragent = 'maxhaesslein/homestead/'.$homestead_version;
