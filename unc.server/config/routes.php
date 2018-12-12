<?php defined('BASEPATH') OR exit('No direct script access allowed');

/* https://codeigniter.com/user_guide/general/routing.html */
$route['default_controller'] = 'posts';
$route['404_override'] = 'assets/notFound';
$route['translate_uri_dashes'] = true;

// static files
$route['assets'] = 'assets/index';
$route['assets/html'] = 'assets/get/html/index.html';
$route['assets/(.+)'] = 'assets/get/$1';
$route['app/assets/(.+)'] = 'assets/get/app/$1';
$route['node_modules/(.+)'] = 'assets/get/node_modules/$1';
$route['uploads'] = 'assets/index';
$route['uploads/(.+)'] = 'assets/get/uploads/$1';

$route['setup'] = 'setup/index';
$route['setup/(.+)'] = 'setup/$1';
$route['admin'] = 'admin/index';
$route['admin/(.+)'] = 'admin/loadPage/$1';
$route['auth'] = 'auth/index';
$route['auth/(.+)'] = 'auth/index/$1';
$route['api'] = 'api/plugins/error';
$route['api/(auth|config|posts)'] = 'api/$1/index';
$route['api/(auth|config|posts)/(:num)'] = 'api/$1/index/$2';
$route['api/(auth|config|posts)/(:any)'] = 'api/$1/$2';
$route['api/(auth|config|posts)/(:any)/(.+)'] = 'api/$1/$2/$3';
$route['api/(.+)'] = 'api/plugins/index/$1';
$route['(.+)'] = 'posts/get/$1';
