<?php defined('BASEPATH') OR exit('No direct script access allowed');

/** EDIT ONLY THE VALUES IN HANDLEBARS {{...}} **/
$db['default'] = [
	'dsn' => '',
	'hostname' => '{{db_host}}', // 'localhost' should work
	'username' => '{{db_user}}', // Database Username
	'password' => '{{db_password}}', // Database Password
	'database' => '{{db_name}}', // Name of the Database that UnCodr uses
	'dbprefix' => '{{db_prefix}}', // If you wish to have multiple UnCodr installed in the same database
	'dbdriver' => '{{db_driver}}', // Use 'mysqli' if not too sure
	'pconnect' => false,
	'db_debug' => (ENVIRONMENT !== 'production'),
	'cache_on' => true,
	'cachedir' => APPPATH.'cache/sql',
	'char_set' => 'utf8mb4',
	'dbcollat' => 'utf8mb4_unicode_ci',
	'swap_pre' => '',
	'encrypt' => false,
	'compress' => false,
	'stricton' => true,
	'failover' => [],
	'save_queries' => false
];

$active_group = 'default';	// which connection group to make active
$query_builder = true;		// whether or not to load the query builder class
