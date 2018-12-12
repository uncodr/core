<?php if(!defined('BASEPATH')) { exit('No direct script access allowed'); }

class UnCodr extends CI_Controller {

	public $siteConfigs = [];
	public $theme = 'core';
	public $baseURL = '';
	public $exitCode = 404;
	public $isAPI = false;
	public $apiResponse = [];
	public $session = [];

	public function __construct($loadConfig = false) {

		parent::__construct();

		$this->baseURL = baseURL();
		$this->config->set_item('base_url', $this->baseURL);
		$this->output->set_header('X-Powered-By: '.APP_NAME, true);
		// $this->output->set_header('X-Frame-Options: sameorigin', true);

		if($loadConfig) {

			# connect to db and get autoload siteConfigs
			$this->model->connect();
			$this->siteConfigs = $this->siteConfigs();

			# get theme
			if(isset($this->siteConfigs['theme'])) {
				$this->theme = $this->siteConfigs['theme'];
			}
		}
	}

	public function __destruct() {

		// parent:: __destruct();
		if($this->isAPI) {

			# load api response message
			# api response message has key as 'class.function.method'
			if(!isset($this->apiResponse['message'])) {
				$key = $this->router->fetch_class();
				$key .= '.'.($this->router->fetch_method());
				$key .= '.'.($this->input->method());
				$this->apiResponse['message'] = $this->_apiMessage($key);
			}

			# output everything
			$this->output
				->set_status_header($this->exitCode)
				->set_content_type('application/json');
			if(count($this->apiResponse) && ($this->exitCode != 204)) {
				$this->output->set_output(json_encode($this->apiResponse));
			}
			$this->output->_display();
		}
	}

	/*
	 * Request Methods
	 * GET (safe, idempotent): retrieve data,
	 * HEAD (safe, idempotent): similar to GET, but w/o any response body,
	 * POST (unsafe, non-idempotent): change server state,
	 * PUT (unsafe, idempotent): replace all current representations of target resource,
	 * PATCH (unsafe, non-idempotent): update target resource partially,
	 * DELETE (unsafe, idempotent): delete specified resource,
	 * OPTIONS (safe, idempotent): communication options for target resource,

	 * https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers
	 */
	private function _apiMessage($key) {

		$responses = [
			'default' => [
				200 => 'Success',				# Response contains: requested data [GET]; data or result of the action [POST]; no body [HEAD].
				201 => 'Created',				# Resource created. [POST/PUT] Combine with a Location header pointing to the new resource.
				204 => 'No Content',			# Success, but response won't contain a body. [HEAD/PATCH/DELETE]
				301 => 'Moved Permanently',		# Resource has moved to different URI.
				304 => 'Not Modified',			# Cached data can be used.
				400 => 'Bad Request',			# Request invalid or cannot be served, details in error payload (missing parameters, large file size).
				401 => 'Unauthorized',			# User authentication missing or IP blocked.
				403 => 'Forbidden',				# Access not allowed / When authentication succeeded but user doesn't have permissions.
				404 => 'Not Found',				# No resource behind the URI.
				405 => 'Method Not Allowed',	# HTTP method not allowed. E.g. GET method accessed via POST; PUT/PATCH/DELETE on read-only resource.
				406 => 'Not Acceptable',		# Character-set or other characteristics of resource do not match client request headers
				408 => 'Request Timed Out',		# Client did not send request within the time the server was prepared for.
				410 => 'Gone',					# Resource no longer available. Useful as a blanket response for old API versions.
				422 => 'Unprocessable Entity',	# Validation errors. URL is valid and request body is syntactically correct, but semantically wrong.
				429 => 'Too Many Requests',		# Request rejected due to rate limiting.
				500 => 'Internal System Error',	# AVOID THIS. If an error occurs, the stracktrace should be logged and not returned as response.
				501 => 'Not Implemented',		# Request method is currently unavailable, but may be there in future.
				503 => 'Service Unavailable'	# Server overloaded or down for maintenance.
			],
			'auth.validator.post' => [
				400 => 'Email ID not verified',
				403 => 'User disabled',
				404 => 'Invalid Username or Password'
			],
			'auth.register.put' => [
				403 => 'Invalid data'
			],

			'auth.recover.post' => [
				404 => 'No user found'
			],
			'auth.reset.patch' => [
				200 => 'Success! You may login with new password.',
				404 => 'Verification code is invalid. Please retry "forgot password".'
			],

			'posts.index' => [
				400 => 'Required parameter is missing'
			]
		];

		$output = (isset($responses[$key], $responses[$key][$this->exitCode]))? $responses[$key] : $responses['default'];
		return $output[$this->exitCode];
	}

	/**
	 * get site configs
	 * @param	string		$key			name of the key [optional]
	 * @return	array		[key-value pairs]
	 */
	public function siteConfigs($key = null) {

		# if not connected to database, then redirect to setup page
		if(!property_exists($this, 'db')) {
			$success = $this->model->connect(null, false);
			if(!$success) { redirect(($this->baseURL).'setup'); }
		}

		else {

			# load those configs which have autoload enabled
			$param = [
				'table' => 'configs',
				'where' => ['autoload' => '1']
			];

			# if $key is passed, then ignore autoload and use $key as 'key'
			if($key) {
				switch(gettype($key)) {
					case 'string':
						$param['where'] = ['key' => $key];
						break;
					case 'array':
						unset($param['where']);
						$param['where_in'] = ['key' => $key];
						break;
					case 'boolean':
						unset($param['where']);
						break;
				}
			}

			$output = $this->model->get($param);
			/*try {

				# get data and if none exists, then redirect to 'setup/config' page
				$output = $this->model->get($param);
				if(!count($output)) {
					throw new Exception('Database Connection Error', 1);
				}
			} catch (Exception $e) {
				redirect(($this->baseURL).'setup/config');
			}*/

			return array_column($output, 'value', 'key');
		}
	}

	public function themeConfigs($theme = 'core') {

		$output = [];
		if(file_exists('public/themes/'.$theme.'/config.json')) {
			$output = file_get_contents('public/themes/'.$theme.'/config.json');
			$output = json_decode($output, true);
		}
		return $output;
	}

	public function fetchFile($path, $mimetype = null) {

		if(file_exists($path) && !is_dir($path)) {
			if(!$mimetype) { $mimetype = get_mime_by_extension($path); }
			$this->output
				->set_header('Expires:"'.gmdate('D, d M Y H:i:s', time()+30*86400).' GMT"')
				->set_header('Cache-Control: max-age='.(30*86400).', public')
				->set_content_type($mimetype)
				->set_output(file_get_contents($path));
		} else { $this->errorPage(404); }
	}

	/**
	 * send mail function
	 * @param  array	$param		keys: 'emailTemplate', 'subject', 'data' => ['name' => '', 'email' => '', 'key0' => ''], 'attachments' (optional)
	 * @return bool
	 */
	public function sendMail($param) {

		# get smtp email settings from configs table
		$siteConfigs = $this->siteConfigs(['email', 'siteTitle', 'siteAdmin']);
		$param['data']['siteTitle'] = $siteConfigs['siteTitle'];
		$siteConfigs['email'] = json_decode($siteConfigs['email'], true);

		# if required, load email library with config parameters
		if(!property_exists($this, 'email')) {
			$this->load->library('email');
			if(count($siteConfigs['email'])) {
				$siteConfigs['email'] = [
					'protocol' => 'smtp',
					'smtp_host' => $siteConfigs['email']['host'],
					'smtp_user' => $siteConfigs['email']['user'],
					'smtp_pass' => unshuffler($siteConfigs['email']['pass']),
					'smtp_crypto' => 'tls',
					'smtp_port' => $siteConfigs['email']['port'],
					// 'smtp_keepalive' => true,
					'mailtype' => 'html',
					'charset' => 'iso-8859-1',
					'newline' => "\r\n",
					'crlf' => "\r\n"
				];
			} else { $siteConfigs['email'] = []; }
			$this->email->initialize($siteConfigs['email']);
		}

		# this is required if mailing in a loop
		// $this->email->clear();

		# load email template
		$message = $this->load->view('emails/'.$param['emailTemplate'], $param['data'], true);

		# configure email
		if(!isset($siteConfigs['email']['smtp_user'])) {
			$siteConfigs['email']['smtp_user'] = $siteConfigs['siteAdmin'];
		}
		$this->email
			->to($param['data']['email'], $param['data']['name'])
			->from($siteConfigs['email']['smtp_user'], $siteConfigs['siteTitle'])
			->reply_to($siteConfigs['email']['smtp_user'], $siteConfigs['siteTitle'])
			->subject($param['subject'])
			->message($message);

		# attach files if 'attachments' array sent in parameters
		if(isset($param['attachments'])) {
			foreach($param['attachments'] as $file) {
				$this->email->attach($file);
			}
		}

		# send email and return result
		return $this->email->send();
	}

	public function errorPage($code = 404, $html = '') {

		$data = [
			'bodyClass' => 'error e',
			'heading' => ($code == 404)? '404 Page Not Found' : 'Error '.$code,
			'html' => $html? $html : '<p class="no-margin">The requested URL <strong>'.requestURI().'</strong> does not exist on this server.</p>'
		];

		$data['bodyClass'] .= $code;
		$data['html'] = "\t\t".$data['html']."\n";

		if(!$code) { $code = 500; }

		$this->output->set_status_header($code);
		$this->load->view(templatePath('minimal', $this->theme), $data);
	}

	protected function _loadPlugin() {

		# if parameters not passed
		$params = func_get_args();
		if(!isset($params[0])) { return null; }
		if(!isset($params[1])) { $params[1] = $params[0]; }

		# get the library name and method
		if(!file_exists(APPPATH.'libraries/'.$params[0].'/'.ucfirst($params[1]).'.php')) { return null; }
		$plugin = $params[0];
		$libName = $plugin.'_'.$params[1];
		$method = 'index';
		$this->load->library($plugin.'/'.$params[1], null, $libName);

		if(isset($params[2]) && !is_numeric($params[2])) {
			$method = $params[2];
			unset($params[2]);
		}
		if($this->isAPI) {
			$method = 'api'.ucfirst($method);
		}
		if(!method_exists($this->{$libName}, $method)) { return null; }

		unset($params[0], $params[1]);
		$params = array_values($params);
		$pluginData = $this->{$libName}->{$method}(...$params);

		if($pluginData && isset($pluginData['js'], $pluginData['js']['files'])) {
			foreach ($pluginData['js']['files'] as $key => $value) {
				$pluginData['js']['files'][$key] = '../../../plugins/'.$plugin.'/'.$value;
			}
		}
		return $pluginData;
	}

	public function addHook($hook, $conf) {


	}
}
