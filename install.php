<?php

$php_min_version_major = 8;

if( ! function_exists('phpversion') ) {
	echo 'PHP version is too old (you need at least PHP '.$php_min_version_major.')';
	exit;
}

$phpversion = explode( '.', phpversion() );


if( $phpversion[0] < $php_min_version_major ) {
	echo 'PHP version is too old (you need at least PHP '.$php_min_version_major.')';
	exit;
}

$sources = [
	'eigenheim' => 'https://api.github.com/repos/maxhaesslein/eigenheim/releases',
	'sekretaer' => 'https://api.github.com/repos/maxhaesslein/sekretaer/releases',
	'postamt' => 'https://api.github.com/repos/maxhaesslein/postamt/releases',
];

var_dump($sources);
