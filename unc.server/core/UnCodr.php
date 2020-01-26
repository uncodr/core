<?php if(!defined('BASEPATH')) { exit('No direct script access allowed'); }

class UnCodr extends CI_Controller {

	public $siteConfigs = [];
	public $theme = 'core';
	public $baseURL = '';
	public $exitCode = 404;
	public $isAPI = false;
	public $apiResponse = [];
	public $session = [];
	public $built = '19.08.03';

	public function __construct($loadConfig = false) {

		parent::__construct();

		$this->baseURL = baseURL();
		$this->config->set_item('base_url', $this->baseURL);
		$this->output->set_header('X-Powered-By: '.APP_NAME, true);
		$this->output->set_header('X-Frame-Options: sameorigin', true);

		if($loadConfig) {

			# connect to db and get autoload siteConfigs
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
			# api response message has key as 'class.function.request-method'
			if(!isset($this->apiResponse['message'])) {
				$key = $this->router->fetch_class();
				$key .= '.'.($this->router->fetch_method());
				$key .= '.'.($this->input->method());
				$this->apiResponse['message'] = $this->_apiMessage($key);
			}

			# if sessionID has been updated, then include it in response
			if(!isset($this->apiResponse['sessionID'])) {
				$headers = $this->authex->getSessionID(false);
				if(!isset($this->session['sessionID'])) {
					if(!$headers) { $this->apiResponse['sessionID'] = $this->authex->newSessionID(); }
				}
				elseif(!$headers || ($headers['sessionID'] != $this->session['sessionID'])) {
					$this->apiResponse['sessionID'] = $this->session['sessionID'];
				}
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
				// 403 => 'Forbidden',				# Access not allowed / When authentication succeeded but user doesn't have permissions.
				403 => 'Permission Denied',				# Access not allowed / When authentication succeeded but user doesn't have permissions.
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

		# load those configs which have autoload enabled
		$param = [
			'table' => 'configs',
			'where' => ['autoload' => '1']
		];

		# if $key is passed, then ignore autoload and use $key as 'key'
		if($key) {
			if($key === true) { unset($param['where']); }
			else {
				$param = apiWhereByType($param, ['key' => $key]);
				unset($param['where']['autoload']);
			}
		}

		$output = $this->model->get($param);

		return (isset($output[0]))? array_column($output, 'value', 'key') : [];
	}

	public function themeConfigs($theme = 'core') {

		$output = [];
		if(file_exists(VIEWPATH.'themes/'.$theme.'/config.json')) {
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

	public function postCurl($url, $data = null, $isJSON = false) {

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERAGENT, APP_NAME);
		if($data) {
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
			if($isJSON) {
				curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
			} else {
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
			}
		} else {
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		$output = curl_exec($ch);
		curl_close($ch);
		return $output;
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
		$lib = $this->_findLib($params,'');
		if($lib == null) { return null; }

		$pluginData = $this->_executeLibMethod($lib['path'], $lib['data']);
		if($pluginData === null) { return null; }
		// else { $pluginData = $pluginData['data']; }

		# modify js file path for plugin
		if($pluginData && isset($pluginData['js'], $pluginData['js']['files'])) {
			foreach ($pluginData['js']['files'] as $key => $value) {
				if($lib['path'][0]) {
					$pluginData['js']['files'][$key] = '../../../plugins/'.$lib['path'][0].$value;
				} else {
					$pluginData['js']['files'][$key] = '../../../plugins/'.$lib['path'][1].'/'.$value;
				}
			}
		}
		return $pluginData;
	}

	private function _findLib($params, $dir = '') {

		$path = APPPATH.'libraries/'.$dir;
		if (file_exists($path.ucfirst($params[0]).'.php')) {

			# default method name is index, unless third non-integer parameter specified
			$className = $params[0];
			$method = 'index';
			if(isset($params[1]) && !is_numeric($params[1])) {
				$method = $params[1];
				unset($params[1]);
			}
			if($this->isAPI) { $method = 'api'.ucfirst($method); }
			unset($params[0]);

			return ['path' => [$dir, $className, $method], 'data' => array_values($params)];
		}
		elseif (is_dir($path.$params[0])) {

			if(!isset($params[1]) || is_numeric($params[1])) {
				array_splice($params, 1, 0, $params[0]);
			}
			$path = $params[0].'/';
			unset($params[0]);
			return $this->_findLib(array_values($params), $path);
		}
		else { return null; }
	}

	private function _executeLibMethod($lib, $data) {

		$methodName = array_pop($lib);
		$className = array_pop($lib);
		$path = implode('/',$lib);
		$lib = implode('_',$lib).'_'.$className;

		# get the library name and method
		if(!file_exists(APPPATH.'libraries/'.$path.'/'.ucfirst($className).'.php')) { return null; }
		$this->load->library($path.'/'.$className, null, $lib);
		if(!method_exists($this->{$lib}, $methodName)) { return null; }

		return $this->{$lib}->{$methodName}(...$data);
	}

	public function runHook($hook, $data = []) {

		$hookArr =	explode('/', $hook);
		if(!isset($hookArr[1])) { return null; }
		$hookName = 'hooks.'.$hookArr[0];
		$output = [];

		# load the hooks from configs table
		$hooks = $this->siteConfigs($hookName);
		if(!isset($hooks[$hookName])) { return null; }

		# parse json and run the hook
		$hooks = json_decode($hooks[$hookName], true);
		if(isset($hooks[$hookArr[1]])) {
			switch(gettype($hooks[$hookArr[1]])) {
				case 'string':
					$hooks = [$hooks[$hookArr[1]]];
					break;
				case 'array':
					$hooks = $hooks[$hookArr[1]];
					break;
				default:
					$hooks = [];
					break;
			}

			# execute the hook
			foreach($hooks as $h) {
				$output[$h] = $this->_executeLibMethod(explode('/', $h), $data);
			}
		}
		return $output;
	}

	public function addHook($hook, $value = '') {
	}
}
