<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require_once __DIR__.'/Authex_session.php';

class Authex extends Authex_session {

	public function __construct() {

		parent::__construct();
	}

	/**
	 * Create session table
	 * @param	bool		$loadForge		whether to load dbforge class
	 * @return	bool		true on success, false on failure
	 */
	public function setup($tables = [], $loadForge = true) {

		$output = [];
		if(gettype($tables) == 'array') { $tables = array_flip($tables); }

		# create sessions table
		if($tables == true || isset($tables[$this->sessionTable])) {
			$output[$this->sessionTable] = $this->_setupSession($loadForge);
		}

		# create users table
		if($tables == true || isset($tables['users'])) {
			$output['users'] = $this->_setupUsers($loadForge);
		}

		# create groups table
		if($tables == true || isset($tables['groups'])) {
			$output['groups'] = $this->_setupGroups($loadForge);
		}

		return $output;
	}

	private function _setupUsers($loadForge = true) {

		$table = [
			'id' => 'user',
			'fields' => [
				'email' => sqlField('unique', '191'),
				'login' => sqlField('unique_name'),
				'password' => sqlField('var191'),
				'ssoID' => sqlField('text', null, ['null' => true]),
				'otp' => sqlField('int_unsigned', '8', ['null' => true]),
				'screenName' => sqlField('unique_name'),
				'name' => sqlField('var63'),
				'lastLogin' => sqlField('epoch'),
				'loginCount' => sqlField('count', null, ['null' => true]),
				'emailVerified' => sqlField('status')
			],
			'hasDefaults' => false,
			'meta' => 'users_meta'
		];
		return $this->CI->model->createTable('users', $table, $loadForge);
	}

	private function _setupGroups($loadForge = true) {

		$table = [
			'id' => 'group',
			'fields' => [
				'code' => sqlField('unique_name'),
				'name' => sqlField('var31'),
				'expiry' => sqlField('var31'),
				'permissions' => sqlField('text'),
				'registration' => sqlField('status')
			],
			'keys' => ['registration'],
			'hasDefaults' => true,
			'meta' => 'groups_meta'
		];
		$out['groups'] = $this->CI->model->createTable('groups', $table, $loadForge);

		$table = [
			'fields' => [
				'groupID' => sqlField('id-0'),
				'userID' => sqlField('id-0'),
				'expiry' => sqlField('epoch'),
				'meta' => sqlField('longtext')
			],
			'pkeys' => ['groupID', 'userID'],
			'keys' => ['userID'],
			'hasDefaults' => true
		];
		$out['groups_users'] = $this->CI->model->createTable('groups_users', $table, false);

		return $out;
	}

	public function validateUser($axn = null) {

		# check whether sessionID and authToken in headers/cookies exist
		$data = $this->getSessionID();
		if(!$data) { return false; }

		# check whether session record exists
		$this->CI->session = $this->getSession($data);
		if(!$this->CI->session) { return false; }
		elseif($axn) {
			return getPermission($axn, $this->CI->session['userData']['groups']);
		}
		return true;
	}

	/**
	 * create user group. Permissions code:
	 * 0: none; 1: view; 2: insert & update; 4: delete
	 * @param	array		$param			[[keys: code, name, permissions, meta]]
	 * @return	array		array of groupIDs
	 */
	public function createGroup($param) {

		$time = time();
		$meta = null;
		$out = [];

		foreach($param as $i => $group) {

			# if meta is to be set for current group
			if(isset($group['meta'])) { $meta = $group['meta']; }

			# append default fields
			$group = elements(['code', 'name', 'expiry', 'permissions', 'registration', 'status'], $group);
			if($group['registration'] === null) { $group['registration'] = 0; }
			if($group['status'] === null) { $group['status'] = 1; }
			$group['addedOn'] = $time;
			$group['lastUpdatedOn'] = $time;

			# create group
			$out[$i] = $this->CI->model->insert([
				'table' => 'groups',
				'data' => $group
			]);

			# create meta records
			if($out[$i] && $meta !== null) { $this->_addMeta('group', $out[$i], $meta); }
			$meta = null;
		}
		return $out;
	}

	/**
	 * create user and send registration email
	 * @param	array		$param			[data => [email => '', login => '', password => ''], meta => [key => value]]
	 * @return	array		keys: userID, otp, screenName
	 */
	public function createUser($param) {

		$out = [];
		$time = time();
		$meta = $group = null;
		if(isAssoc($param)) { $param = [$param]; }

		foreach($param as $i => $user) {

			# if meta & group is to be set for current user
			if(isset($user['meta'])) { $meta = $user['meta']; }
			if(isset($user['group'])) { $group = $user['group']; }

			# append default fields
			$user = elements(['email', 'login', 'password', 'ssoID', 'screenName', 'name', 'emailVerified', 'status', 'loginCount', 'lastLogin'], $user);
			$user['otp'] = rand(100000,999999);
			$user['email'] = strtolower($user['email']);
			$user['login'] = strtolower($user['login']);
			if(!$user['password']) { $user['password'] = $user['otp']; }
			$user['password'] = password_hash($user['password'], PASSWORD_BCRYPT, ['cost' => 10]);
			if(!$user['screenName']) { $user['screenName'] = hash('crc32',$time.rand(100,999)).substr($user['login'],0,16); }
			if(!$user['name']) { $user['name'] = ucfirst($user['login']); }
			if($user['status'] === null) { $user['status'] = 1; }
			if($user['emailVerified'] === null) { $user['emailVerified'] = 0; }
			$user['addedOn'] = $time;
			$user['lastUpdatedOn'] = $time;
			$out[$i] = [];

			# insert in users table and get userID
			$out[$i]['userID'] = $this->CI->model->insert([
				'table' => 'users',
				'data' => $user
			]);

			# create meta records and group
			if($out[$i]['userID']) {
				if($meta) { $this->_addMeta('user', $out[$i]['userID'], $meta); }
				if($group) { $this->updateUserGroup($out[$i]['userID'], ['add' => $group], []); }
				$out[$i]['otp'] = $user['otp'];
				$out[$i]['screenName'] = $user['screenName'];
			} else { unset($out[$i]); }
			$meta = $group = null;
		}
		return $out;
	}

	/**
	 * update user's group
	 * @param	int			$userID
	 * @param	array		$param			['add' => [groupID => expiry], 'update' => [keys: groupID, status, expiry] 'remove' => [array of groupIDs]]
	 * @return	bool		true on success
	 */
	public function updateUserGroup($userID, $param, $groups = null) {

		$out = $data = [];
		$time = time();
		$gID = null;

		# get current groups of the user
		if($groups === null) {
			$groups = $this->getUserGroups($userID, ['groupID', 'expiry'], null);
			$groups = array_column($groups, 'expiry', 'groupID');
		}

		if(isset($param['add'])) {
			if(isAssoc($param['add'])) { $param['add'] = [$param['add']]; }
			foreach($param['add'] as $value) {
				$gID = isset($value['id'])? $value['id'] : $value['groupID'];
				if(!isset($groups[$gID])) {
					$data[] = [
						'userID' => $userID,
						'groupID' => $gID,
						'expiry' => $value['expiry'],
						'meta' => null,
						'addedOn' => $time,
						'lastUpdatedOn' => $time,
						'status' => 1
					];
				}
			}

			# batch insert group data
			$out[] = $this->CI->model->insertBatch([
				'table' => 'groups_users',
				'data' => $data
			]);
		}

		if(isset($param['update'])) {
			if(isAssoc($param['update'])) { $param['update'] = [$param['update']]; }
			foreach($param['update'] as $group) {
				$gID = isset($group['id'])? $group['id'] : $group['groupID'];
				if(isset($groups[$gID])) {
					$group['lastUpdatedOn'] = $time;
					unset($group['groupID'], $group['id']);
					if(isset($group['newGroupID'])) {
						$group['groupID'] = $group['newGroupID'];
						unset($group['newGroupID']);
					}
					$out[] = $this->CI->model->update([
						'table' => 'groups_users',
						'data' => $group,
						'where' => ['userID' => $userID, 'groupID' => $gID]
					]);
				}
			}
		}

		if(isset($param['remove'])) {
			$out[] = $this->CI->model->delete([
				'table' => 'groups_users',
				'where' => ['userID' => $userID],
				'where_in' => ['groupID' => $param['remove']]
			], true);
		}
		return $out;
	}

	private function _addMeta($tableID, $pKey, $data) {

		$meta = [];
		foreach($data as $key => $value) {
			if(gettype($value) != 'string') { $value = json_encode($value); }
			array_push($meta, [
				$tableID.'ID' => $pKey,
				'key' => $key,
				'value' => $value
			]);
		}
		$this->CI->model->insertBatch([
			'table' => $tableID.'s_meta',
			'data' => $meta
		]);
	}

	/**
	 * email an otp to user when forgot password is used
	 * @param	string		$user			email or login
	 * @return	array		email, name, otp, vcode (base64 encoded otp and email)
	 */
	public function sendOTP($user) {

		# check if email is valid
		$user = $this->getUser($user, ['select' => 'email, name, otp']);
		if(count($user) === 1) {

			# generate otp and update it in users table
			$user = $user[0];
			if(!$user['otp']) {
				$user['otp'] = rand(100000,999999);
				$this->CI->model->update([
					'table' => 'users',
					'where' => ['email' => $user['email']],
					'data' => ['otp' => $user['otp']]
				], false);
			}
			$this->emailOTP($user);
		}
		return $user;
	}

	/**
	 * email otp to user
	 * @param	array		$data			keys: name, email, otp
	 * @return	boolean
	 */
	public function emailOTP($data, $template = 'recover') {

		$data['vcode'] = b64encode($data['email'].' | '.$data['otp']);
		return $this->CI->sendMail([
			'emailTemplate' => $template,
			'data' => $data,
			'subject' => ($template == 'recover')? 'OTP for Password reset: '.$data['otp'] : 'Account Activation'
		]);
	}

	/**
	 * check user-otp combination
	 * @param	string		$user			email ID or login
	 * @param	int			$otp
	 * @return	bool		true on success, false on failure
	 */
	public function checkOTP($user, $otp) {

		if(!$user || !$otp) { return false; }

		# get user and check whether $otp is same as that saved in db
		$user = $this->getUser($user, ['select' => 'email, otp']);
		if(count($user) === 1) { return ($user[0]['otp'] === $otp); }

		return false;
	}

	public function parseVcode($vcode = null, $validate = true) {

		if(!$vcode) { return null; }

		$vcode = explode(' | ', b64decode($vcode));
		if(!isset($vcode[1])) { return null; }

		# validate otp and user
		else {
			if($validate) { $validate = $this->checkOTP($vcode[0], $vcode[1]); }
			else { $validate = true; }

			return (!$validate)? null : ['otp' => $vcode[1], 'user' => $vcode[0]];
		}
	}

	/**
	 * @param	array		$params			keys: user (email or login), otp, password
	 * @return	int			200: success, 404: user not found, 401: otp invalid
	 */
	public function updateUserMeta($userID, $meta = []) {

		if(!count($meta)) { return null; }

		$metaOld = $this->CI->model->getMeta('users', ['where' => ['userID' => $userID, 'key' => array_keys($meta)]]);
		$params = ['table' => 'users_meta', 'data' => []];
		$remove = [];

		foreach($meta as $key => $value) {
			if(isset($metaOld[$key])) { $remove[] = $key; }
			$params['data'][] = ['userID' => $userID, 'key' => $key, 'value' => $value];
		}

		if(count($remove)) {
			$params['where'] = ['userID' => $userID];
			$params['where_in'] = ['key' => $remove];
			$this->CI->model->delete($params, false);
		}

		return $this->CI->model->insertBatch($params);
	}

	/**
	 * reset user's password
	 * @param	array		$params			keys: user (email or login), otp, password
	 * @return	int			200: success, 404: user not found, 401: otp invalid
	 */
	public function updateUser($userID, $data, $log = false) {

		if(!$userID || !count($data)) { return false; }

		return $this->CI->model->update([
			'table' => 'users',
			'data' => $data,
			'where' => ['userID' => $userID]
		], $log);
	}

	public function deleteUser($userID, $log = true) {

		$this->CI->model->delete([
			'table' => 'users',
			'where' => ['userID' => $userID]
		], $log);
		$this->CI->model->delete([
			'table' => 'users_meta',
			'where' => ['userID' => $userID]
		], $log);
		$this->CI->model->delete([
			'table' => 'groups_users',
			'where' => ['userID' => $userID]
		], $log);
	}

	/**
	 * get group by code
	 * @param	string		$code			group code
	 * @param	string		$fields			fields to be selected from users table
	 * @param	bool		$public			if only active groups to be fetched
	 * @return	array		blank array if not found, else associative array using fields as keys
	 */
	public function getGroups($identifier = null, $fields = '*', $public = true) {

		# if identifier is not sent
		if(!$identifier) { return []; }

		$param = ['table' => 'groups', 'select' => $fields, 'where' => []];
		if($public) { $param['where'] = ['registration' => '1', 'status' => '1']; }

		if(isset($identifier['code'])) { $param = apiWhereByType($param, ['code' => $identifier['code']]); }
		elseif(isset($identifier['id'])) { $param = apiWhereByType($param, ['groupID' => $identifier['id']]); }
		else { return []; }

		return $this->CI->model->get($param);
	}

	/**
	 * get user by email ID/login
	 * @param	string		$user			email ID or login
	 * @param	string		$fields			fields to be selected from users table
	 * @return	array		blank array if not found, else records of associative arrays using fields as keys
	 */
	public function getUser($user, $params = []) {

		$params['table'] = 'users';
		$key = null;
		switch(gettype($user)) {
			case 'array':
				$key = 'where_in';
				break;
			case 'string': case 'integer':
				$key = 'where';
				break;
		}
		if($key) {
			if(!isset($params['groupClause'])) {
				$params['groupClause'] = [
					'or_'.$key => ['email' => $user, 'login' => $user]
				];
			} elseif(isset($params['groupClause']['or_'.$key])) {
				$params['groupClause']['or_'.$key] = array_merge(
					$params['groupClause']['or_'.$key],
					['email' => $user, 'login' => $user]
				);
			} else {
				$params['groupClause']['or_'.$key] = ['email' => $user, 'login' => $user];
			}
		}
		return $this->CI->model->get($params, false);
	}

	/**
	 * get user's groups by email ID/login
	 * @param	int			$userID			userID
	 * @param	string		$fields			fields to be selected from groups table
	 * @return	array		blank array not found
	 */
	public function getUserGroups($userID, $fields = '*', $active = true) {

		# if user is not sent
		if(!$userID) { return []; }

		# get the record from user's table
		$param = [
			'table' => 'groups_users',
			'select' => $this->_sanitizeGroupFields($fields),
			'order_by' => ['groups_users.status' => 'DESC', 'groups_users.expiry' => 'DESC']
		];
		$param = apiWhereByType($param, ['groups_users.userID' => $userID]);

		if($active) {
			$param['where']['groups_users.status'] = 1;
			$param['groupClause'] = [
				'where' => ['groups_users.expiry > ' => time()],
				'or_where' => ['groups_users.expiry' => null]
			];
		} elseif($active === false) {
			$param['where']['groups_users.expiry !='] = null;
			$param['groupClause'] = [
				'where' => ['groups_users.status' => 0],
				'or_where' => ['groups_users.expiry <' => time()]
			];
		}
		if(strpos($param['select'], 'groups.') !== false) {
			$param['join'] = ['groups' => 'groups_users.groupID = groups.groupID'];
		}

		return $this->CI->model->get($param);
	}

	public function getDefaultGroup($getGroupID = true) {

		$out = $this->CI->siteConfigs('registration');
		$out['registration'] = json_decode($out['registration'], true);
		if($out['registration']['enable'] == 0) { return null; }
		$out = ['code' => $out['registration']['default_group'], 'autologin' => $out['registration']['autologin']];
		if($getGroupID) {
			$group = $this->CI->authex->getGroups(['code' => $out['code']], 'groupID, expiry', true);
			$out = isset($group[0])? array_merge($out, $group[0]) : null;
		}
		return $out;
	}

	private function _sanitizeGroupFields($fields) {

		if($fields == '*') {
			$fields = 'groups_users.groupID, groups_users.userID, groups.code, groups.name, groups.permissions, groups_users.expiry, groups_users.meta, groups_users.status';
		} else {
			$fields = array_flip($fields);

			if(isset($fields['id'])) { $fields['groups_users.groupID as id'] = $fields['id']; }
			if(isset($fields['groupID'])) { $fields['groups_users.groupID'] = $fields['groupID']; }
			if(isset($fields['userID'])) { $fields['groups_users.userID'] = $fields['userID']; }
			if(isset($fields['meta'])) { $fields['groups_users.meta'] = $fields['meta']; }
			if(isset($fields['code'])) { $fields['groups.code'] = $fields['code']; }
			if(isset($fields['name'])) { $fields['groups.name'] = $fields['name']; }
			if(isset($fields['permissions'])) { $fields['groups.permissions'] = $fields['permissions']; }
			if(isset($fields['expiry'])) { $fields['groups_users.expiry'] = $fields['expiry']; }
			if(isset($fields['status'])) { $fields['groups_users.status'] = $fields['status']; }
			unset($fields['id'], $fields['groupID'], $fields['userID'], $fields['meta'], $fields['code'], $fields['name'], $fields['permissions'], $fields['expiry'], $fields['status']);

			asort($fields);
			$fields = implode(',', array_flip($fields));
		}
		return $fields;
	}

	static public function partitionGroups($groups) {

		$time = time();
		$output = ['active' => [], 'inactive' => []];
		foreach($groups as $group) {
			if($group['status'] && (($group['expiry'] === null) || $group['expiry'] > $time)) {
				$output['active'][$group['groupID']] = $group;
			} else { $output['inactive'][$group['groupID']] = $group; }
		}
		return $output;
	}

	public function hasGroup($code = null, $findAll = false) {

		# if group code is not passed or session has no groups
		if(!$code || !isset($this->CI->session, $this->CI->session['userData']['groups'])) { return false; }

		$groups = array_column($this->CI->session['userData']['groups'],'groupID','code');
		switch(gettype($code)) {
			case 'string': case 'number':
				return isset($groups[$code]);
			case 'array':
				$groups = array_intersect_key($groups, array_flip($code));
				return ($findAll)? (count($groups) == count($code)) : (count($groups) > 0);
		}
		return false;
	}
}
