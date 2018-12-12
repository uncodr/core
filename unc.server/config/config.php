<?php defined('BASEPATH') OR exit('No direct script access allowed');

$config['base_url'] = '';
$config['index_page'] = '';

$config['language']	= 'english';
$config['charset'] = 'UTF-8';
$config['time_reference'] = 'local';

$config['proxy_ips'] = '';

$config['uri_protocol'] = 'REQUEST_URI';
$config['url_suffix'] = '';
$config['permitted_uri_chars'] = 'a-z 0-9~%.:_\-@';
$config['allow_get_array'] = true;

$config['enable_query_strings'] = false;
$config['controller_trigger'] = 'c';
$config['function_trigger'] = 'm';
$config['directory_trigger'] = 'd';

$config['enable_hooks'] = false;
$config['subclass_prefix'] = 'UC_';
$config['composer_autoload'] = false;

$config['log_threshold'] = 1;
$config['log_path'] = '';
$config['log_file_extension'] = '';
$config['log_file_permissions'] = 0644;
$config['log_date_format'] = 'H:i:s';

$config['error_views_path'] = '';
$config['cache_path'] = '';
$config['cache_query_string'] = false;

$config['encryption_key'] = hex2bin('46f2acb35d4e3ef519c61bab593ae297');
$config['global_xss_filtering'] = false;

$config['sess_driver'] = 'files';                   // files, database, redis, memcached
$config['sess_cookie_name'] = 'uncodr';             // The session cookie name, must contain only [0-9a-z_-] characters
$config['sess_expiration'] = 7200;                  // # of SECONDS you want the session to last, 0 (zero) means expire when the browser is closed
$config['sess_save_path'] = APPPATH.'cache/sessions';   // Driver dependent. 'files': absolute path to a writeable directory, 'database': table name
$config['sess_match_ip'] = false;                   // Whether to match the user's IP address when reading the session data
$config['sess_time_to_update'] = 300;               // # of SECONDS between CI regenerating the session ID
$config['sess_regenerate_destroy'] = false;         // Whether to destroy session data associated with the old session ID while auto-regenerating
/* Note: These settings (except 'cookie_prefix' and 'cookie_httponly') will also affect sessions. */
$config['cookie_prefix']	= '';                   // Set a cookie name prefix if you need to avoid collisions
$config['cookie_domain']	= '';                   // Set to .domain.com for site-wide cookies
$config['cookie_path']		= '/';                  // Typically will be a forward slash
$config['cookie_secure']	= false;                // Cookie will only be set if a secure HTTPS connection exists.
$config['cookie_httponly'] 	= false;                // Cookie will only be accessible via HTTP(S) (no javascript)

$config['csrf_protection'] = false;
$config['csrf_token_name'] = 'csrf_unc_token';      // The token name
$config['csrf_cookie_name'] = 'csrf_unc_cookie';    // The cookie name
$config['csrf_expire'] = 7200;                      // The number in seconds the token should expire
$config['csrf_regenerate'] = true;                  // Regenerate token on every submission
$config['csrf_exclude_uris'] = array();             // Array of URIs which ignore CSRF checks

$config['standardize_newlines'] = false;
$config['compress_output'] = false;
$config['rewrite_short_tags'] = false;

spl_autoload_register(function($class) {
	if(substr($class, 0, 3) !== 'CI_') {
		if(file_exists($file = APPPATH.'core/'.$class.'.php')) {
			require $file;
		}
	}
});

/*
Codeigniter user guide {{CI_GUIDE}} available at https://codeigniter.com/user_guide.

1. proxy_ips: can be csv string or php array. '10.0.1.200,192.168.5.0/24' or array('10.0.1.200', '192.168.5.0/24')
2. For language and charset, take a look at http://php.net/htmlspecialchars
3. uri_protocol: can be REQUEST_URI, QUERY_STRING, or PATH_INFO.
    {{CI_GUIDE}}/general/urls.html
4. subclass_prefix:
    {{CI_GUIDE}}/general/core_classes.html
    {{CI_GUIDE}}/general/creating_libraries.html
5. composer_autoload: can be false boolean, or '/path/to/vendor/autoload.php' string
6. log_threshold: can be 0-4 integer or php array with threshold levels to show individual error types
    Ex: 0 = Disables logging, Error logging TURNED OFF
    Ex: 1 = Error Messages (including PHP errors)
    Ex: 2 = Debug Messages
    Ex: 3 = Informational Messages
    Ex: 4 = All Messages
    Ex: array(2) = Debug Messages, without Error Messages
7. encryption_key: a binary key passed to the library
    {{CI_GUIDE}}/libraries/encryption.html
*/
