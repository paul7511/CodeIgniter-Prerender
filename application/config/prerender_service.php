<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['enable'] = true;

$config['prerender_url'] = 'https://service.prerender.io';

$config['escaped_fragment_name'] = 'ESCAPED_FRAGMENT_NAME';

$config['prerender_token'] = 'PRERENDER_TOKEN';

$config['crawler_user_agents'] = [
	'googlebot',
	'slurp',
	'bingbot',
	'baiduspider',
	'yandexbot',
	'ahrefsbot',
	'adsbot-naver',
	'compatible; Yeti/1.1; +http://naver.me/spd',
];

$config['whitelist'] = [
	'home/index',
];

$config['blacklist'] = [];

/**
 * [
 * 	controller_name/controller_method => [ prerender allow parameter]
 * ]
 */
$config['allow_parameter'] = [
	'home/index' => ['keyword'],
];
