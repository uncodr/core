<?php if(!defined('BASEPATH')) { exit('No direct script access allowed'); }

class Auth extends UnCodr {

	private $_sessFields = 'userID,email,login,screenName,name,emailVerified,addedOn';
	private $_sessGroupFields = ['groupID','code','expiry','meta','permissions'];

	public function __construct() {

		parent::__construct(false);
		$this->isAPI = true;
	}

	/**
	 * validate the credentials, update loginCount and create session
	 * post data must have username and password
	 * @return	json
	 */
	public function validator() {

		$this->model->connect(null, false);

		# if user is logged in (header's sessionID and authToken match)
		if($this->authex->validateUser()) {
			$this->exitCode = !($this->_checkLoginConfigs($this->session['userData']))? 401 :
				($this->_checkAccess())? 204 : 403;
			return null;
		}

		# post data must have username and password
		$post = json_decode($this->input->raw_input_stream, true);
		if(isset($post['user'], $post['password'])) {

			# get user by email/login ($post['user'])
			$user = $this->authex->getUser($post['user'], ['select' => ($this->_sessFields).', ssoID, password, loginCount, status']);

			# if invalid username or password
			if(!count($user)) { return false; }
			elseif(isset($post['sso'])) {
				if(!$this->_validateSSO($post, $user[0])) { return false; }
			}
			elseif(!password_verify($post['password'], $user[0]['password'])) { return false; }

			# defaults
			$user = $user[0];
			$user['addedOn'] = (int) $user['addedOn'];
			$this->exitCode = 400;

			# if user has been disabled
			if(!$user['status']) {
				$this->apiResponse['message'] = 'Account has been disabled. Please contact the admin.';
				return false;
			}

			# email unverified limit and group access expiry check
			if($this->_checkLoginConfigs($user) == false) { return false; }

			# if the user is logging in for the first time
			$data = [];
			if($user['loginCount'] == 0) { $data = $this->_firstLogin($user); }

			# login successful, update loginCount
			$this->exitCode = 200;
			$this->_updateLoginCount($user);

			# create record in sessionTable
			unset($user['ssoID'], $user['password'], $user['loginCount'], $user['status']);
			$user['emailVerified'] = (bool) $user['emailVerified'];

			# send sessionID, authToken and data (name, email)
			$this->apiResponse = array_merge($data, $this->_createSession($user, false));
		}
	}

	private function _validateSSO($post, &$user) {

		# Verify Token if $post['sso'] is google or FB

		# Match/Update ssoID
		$ssoID = null;
		if($user['ssoID']) {
			$ssoID =  json_decode($user['ssoID'], true);
			if(isset($ssoID[$post['sso']])) { return $ssoID[$post['sso']] == $post['password']; }
			else { $ssoID[$post['sso']] = $post['password']; }
		} else { $ssoID = [$post['sso'] => $post['password']]; }

		# Save ssoID in DB
		if($ssoID) {
			$user['ssoID'] = json_encode($ssoID);
			return true;
		} else { return false; }
	}

	private function _checkLoginConfigs(&$user) {

		# check if the email is verified / unverified but user is allowed to login
		$configs = $this->siteConfigs(['login', 'registration']);
		$configs['login'] = json_decode($configs['login'], true);
		$configs['registration'] = json_decode($configs['registration'], true);
		$isValid = $user['emailVerified'] || ($configs['login']['unverified_limit']*24*3600 + $user['addedOn'] > time());

		# if email ID not verified
		if(!$isValid) { $this->apiResponse['message'] = 'Email ID is unverified. Please use "forgot password".'; }
		else {

			# get user's groups
			if(!isset($user['groups'])) {
				$user['groups'] = $this->authex->getUserGroups($user['userID'], $this->_sessGroupFields);
			}

			# if expired groups are to be denied
			if($configs['login']['disable_on_expiry']) {

				# consider only expiry dates, and ignore expiry of default registration group if it has unlimited expiry
				$isValid = array_column($user['groups'], 'expiry', 'code');
				if(isset($isValid[$configs['registration']['default_group']])) { unset($isValid[$configs['registration']['default_group']]); }

				if(!count($isValid)) {
					$this->apiResponse['message'] = 'Access has Expired. Please contact the admin.';
					$isValid = false;
				} else {

					# check if any group has expiry set as null (no expiry)
					if(array_search(null, $isValid) !== false) { $isValid = true; }

					# else get group with maximum expiry and check it against current time
					else { $isValid = (max($isValid) > time()); }
				}
			}
		}
		return $isValid;
	}

	private function _checkAccess() {

		$path = str_replace($this->baseURL,'',$this->agent->referrer());
		if(strpos($path, 'admin') === 0) {
			return !$this->authex->hasGroup('user');
		}
		return true;
	}

	/**
	 * events to be triggered when the user logs in for the first time
	 * see 'validator' method of this class
	 * @param	array		$user			keys: fields in 'users' table
	 * @return	bool
	 */
	private function _firstLogin(&$user) {

		# call segment's track
		/*$this->analytics->track([
			'userId' => $user['userID'],
			'event' => 'Activated the Account'
		]);
		return true;*/
		$user['firstLogin'] = true;
		return ['firstLogin' => true];
	}

	/**
	 * update loginCount and lastLogin (timestamp) in users table
	 * @param	int			$userID
	 * @param	int			$oldLoginCount	current loginCount
	 * @return	int			number of affected rows
	 */
	private function _updateLoginCount($user) {

		$data = [
			'lastLogin' => time(),
			'loginCount' => (int) $user['loginCount']
		];
		$data['loginCount'] += 1;
		if($user['ssoID']) { $data['ssoID'] = $user['ssoID']; }
		return $this->authex->updateUser($user['userID'], $data);
	}

	/**
	 * create record in sessionTable
	 * @param   array		$data			keys: 'userID' and session's userData (currently 'email, name, emailVerified, addedOn, groups')
	 * @param   bool		$persistent		whether to create a persistent session
	 * @return  array		session data
	 */
	private function _createSession($data, $persistent) {

		# remove userID offset as it is a separate field in session table
		$userID = $data['userID'];

		# sanitize and create session
		# currently userData consists of email, name and emailVerified
		unset($data['userID']);
		$output = [
			'userID' => $userID,
			'userData' => $data,
			'authToken' => md5(time())
		];
		$headers = $this->authex->getSessionID(false);
		if(isset($headers['sessionID'])) { $output['sessionID'] = $headers['sessionID']; }

		$this->session = $this->authex->setSession($output, false, $persistent);
		unset($output['userID'], $output['userData']);

		# execute the hook
		$this->runHook('auth/login', [$userID, $data]);

		$output['sessionID'] = $this->session['sessionID'];
		$output['data'] = elements(['name', 'email', 'screenName'], $this->session['userData']);
		return $output;
	}

	/**
	 * delete the record from sessionTable
	 * @return  json
	 */
	public function logout() {

		$headers = $this->authex->getSessionID(false);
		if($headers) {
			$this->model->connect(null, false);
			$this->authex->unsetSession($headers['sessionID']);

			# execute the hook
			$this->runHook('auth/logout');
		}
		$this->exitCode = 204;
	}

	/**
	 * register a user
	 * @return  json
	 */
	public function register() {

		$this->model->connect(null, false);
		$groups = [];

		# if the user is not logged in or does not have WRITE/UPDATE permission,
		# then check whether registration is enabled
		$permission = $this->authex->validateUser('users');
		if(($permission === false) || !($permission & 2)) {
			$conf = $this->authex->getDefaultGroup(false);
			if(!$conf) { return false; }
			$groups[] = $conf['code'];
			$permission = 0;
		}

		# check the mandatory fields
		$error = [];
		$post = json_decode($this->input->raw_input_stream, true);
		if(isset($post['sso'])) { $post['ssoID'] = json_encode([$post['sso'] => $post['password']]); }
		$post = elements(['name','email','login','password','ssoID','group','meta','autologin'], $post);
		if(!$post['name']) { $error[] = 'name'; }
		if(!$post['email']) { $error[] = 'email'; }
		if(!$post['login']) { $error[] = 'login'; }
		if(count($error)) {
			$this->exitCode = 422;
			$this->apiResponse['error'] = $error;
			$this->apiResponse['message'] = 'Mandatory field(s) \''.implode('\', \'', $error).'\' missing.';
			return false;
		}

		# check whether user is already registered
		$this->exitCode = 400;
		$user = $this->authex->getUser([$post['email'], $post['login']], ['select' => 'userID, login']);
		if(isset($user[0])) {
			$this->apiResponse['error'] = ($user[0]['login'] == $post['login'])? 'login' : 'email';
			$this->apiResponse['message'] = 'User \''.$post[$this->apiResponse['error']].'\' already exists.';
			$this->exitCode = 404-count($user);
			return false;
		}

		# get groups to be assigned
		if($post['group']) {
			switch(gettype($post['group'])) {
				case 'array':
					$groups = array_merge($groups, $post['group']);
					break;
				case 'string':
				default:
					$groups[] = $post['group'];
					break;
			}
		}
		$groups = $this->authex->getGroups(['code' => $groups], 'groupID, expiry', !($permission & 2));
		if(!count($groups)) {
			$this->apiResponse['error'] = 'group';
			$this->apiResponse['message'] = 'Selected group is invalid.';
			return false;
		}

		# modify $post['group'] such that it has groupID and expiry timestamp
		$post['groups'] = $post['group'];
		$post['group'] = [];
		foreach($groups as $gp) {
			if(($gp['groupID'] != '1') || ($permission == 7)) {
				$post['group'][] = ['groupID' => $gp['groupID'], 'expiry' => ($gp['expiry'])? codeToTime($gp['expiry']) : null];
			}
		}

		# if user session to be started
		$autologin = ($post['autologin'] && $conf['autologin']);
		$post['autologin'] = $autologin;
		if($autologin) {
			$post['loginCount'] = 1;
			$post['lastLogin'] = time();
		}

		# create user and get userID, otp & screenName
		$user = $this->authex->createUser($post);

		# if user created successfully
		if(isset($user[0])) {
			$this->exitCode = 201;

			# execute the hook
			$isHookExecuted = true;
			$out = $this->runHook('auth/register', [$user[0]['userID'], $post]);
			foreach ($out as $hook => $output) {
				$isHookExecuted = ($isHookExecuted && $output['success']);
			}

			# email the verification link
			if($isHookExecuted) {
				$this->authex->emailOTP([
					'name' => $post['name'],
					'email' => $post['email'],
					'otp' => $user[0]['otp']
				], 'registration');
			}

			# start session
			if($autologin) {
				$this->exitCode = 200;
				$post = elements(explode(',', $this->_sessFields), $post);
				$user = array_merge($post, $user[0]);
				$user['emailVerified'] = false;
				$user['addedOn'] = time();
				$user['groups'] = $this->authex->getUserGroups($user['userID'], $this->_sessGroupFields);

				# send response
				$this->apiResponse = array_merge($this->_firstLogin($user), $this->_createSession($user, false));
			} else {
				$this->apiResponse['data'] = [
					'name' => $post['name'],
					'email' => $post['email'],
					'screenName' => $user[0]['screenName']
				];
			}
		}

		# else data is inconsistent (insert query in users table failed). probable reasons:
		# - login name, which needs to be unique, already exists
		else {
			$this->apiResponse['error'] = ['login'];
			return false;
		}
	}

	/**
	 * email the vcode when forgot password is used
	 * post data must have email
	 * @return  json
	 */
	public function recover() {

		# assume email is invalid by default
		$post = json_decode($this->input->raw_input_stream, true);
		$this->exitCode = 404;

		# post data must have user (email ID or login)
		if(isset($post['user'])) {

			$this->model->connect(null, false);
			$user = $this->authex->sendOTP($post['user']);
			if(count($user)) { $this->exitCode = 204; }
		}
	}

	/**
	 * email the vcode when forgot password is used
	 * post data must have email
	 * @return  json
	 */
	public function reset() {

		# assume email is invalid by default
		$post = json_decode($this->input->raw_input_stream, true);
		$post = elements(['user', 'otp', 'password', 'vcode', 'autologin'], $post);

		# validate vcode if present
		if($post['vcode']) {
			$data = $this->authex->parseVcode($post['vcode'], false);
			if(!$data) {
				$this->exitCode = 401;
				$this->apiResponse = ['message' => 'Link is invalid, please try "forgot password" again'];
				return null;
			} else { $post = array_merge($post, $data); }
		}

		if($post['user'] && $post['otp'] && $post['password']) {
			$this->model->connect(null, false);
			$this->exitCode = $this->_resetPassword($post);
		} else {
			$this->exitCode = 401;
			$this->apiResponse = ['message' => 'OTP is invalid'];
		}
	}

	/**
	 * reset user's password
	 * @param	array		$post			keys: user (email or login), otp, password
	 * @return	int			200: success, 404: user not found, 401: otp invalid
	 */
	private function _resetPassword($post) {

		# get the user by $post['user']
		$select = 'password, otp, loginCount, ';
		$select .= ($post['autologin'])? $this->_sessFields : 'emailVerified, userID, email';
		$user = $this->authex->getUser($post['user'], ['select' => $select]);

		if(count($user) === 1) {
			$user = $user[0];

			# if OTP does not match old password and otp
			if(!password_verify($post['otp'], $user['password']) && ($user['otp'] != $post['otp'])) {
				$this->apiResponse['message'] = is_numeric($post['otp'])? 'OTP' : 'Old Password';
				$this->apiResponse['message'] .= ' is invalid';
				return 401;
			}

			# set loginCount to 0 if user is logging in for the first time
			if($user['loginCount'] == null) { $user['loginCount'] = 0; }

			# execute the hook
			if(!$user['emailVerified']) {
				$this->runHook('auth/emailVerified', [$user]);
			}

			# update otp, password and emailVerified
			$this->authex->updateUser($user['userID'], [
				'password' => password_hash($post['password'], PASSWORD_BCRYPT, ['cost' => 10]),
				'otp' => null,
				'emailVerified' => 1,
				'loginCount' => $user['loginCount']
			], false);

			if(!$post['autologin']) { return 204; }
			else {
				$data = [];
				$user['groups'] = $this->authex->getUserGroups($user['userID'], $this->_sessGroupFields);
				if($user['loginCount'] == 0) { $data = $this->_firstLogin($user); }

				unset($user['otp'], $user['loginCount'], $user['password']);

				# send sessionID, authToken and data (name, email)
				$this->apiResponse = array_merge($data, $this->_createSession($user, false));
				return 200;
			}
		}
		return 404;
	}
}
