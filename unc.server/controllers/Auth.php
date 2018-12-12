<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends UnCodr {

	public function __construct() {

		parent::__construct(true);
	}

	public function index() {

		if($this->siteConfigs['homepage'] == 'app') {
			$this->load->view('../'.APPDIR.'/index.php');
		}
		else {
			$data = [
				'heading' => '',
				'page' => 'uncodr/auth/',
				'bodyClass' => 'auth',
				'js' => ['files' => [], 'fn' => []]
			];
			$params = func_get_args();

			if(!count($params)) { $params[0] = 'login'; }
			$view = $params[0];
			$data['js']['files'] = ['api', 'auth'];

			switch($params[0]) {
				case 'login':
					$data['heading'] = 'Login';
					$data['js']['fn'] = ['auth.login()'];
					break;
				case 'logout':
					$data['heading'] = 'Logout';
					unset($data['js']['files'][1]);
					$data['js']['fn'] = ['API.auth().logout()'];
					break;
				case 'register':

					# check if registration is enabled
					$conf = $this->siteConfigs(['registration']);
					$conf['registration'] = json_decode($conf['registration'], true);
					if($conf['registration']['enable'] == 0) { redirect(($this->baseURL).'auth'); }

					$data['reg'] = $conf['registration'];
					$data['heading'] = 'Register';
					$data['js']['fn'] = ['auth.register()'];
					break;

				# otp request / password reset page
				case 'reset':
					$data['uData'] = ['otp' => '', 'user' => ''];

					# if vcode is not passed in url
					if(!isset($params[1])) {
						$data['heading'] = 'Recover Password';
						$data['js']['fn'] = ['auth.recover()'];
					} else {
						$vcode = $this->authex->parseVcode($params[1]);
						if(!$vcode) { $view = 'invalid_otp'; }
						$data['uData'] = $vcode;
						$data['heading'] = 'Reset Password';
						$data['js']['fn'] = ['auth.reset()'];
					}
					break;

				default:
					show_404();
					break;
			}

			$data['page'] .= $view;
			$this->load->view(templatePath('blank', 'core'), $data);
		}
	}
}
