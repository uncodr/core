<?php if(!defined('BASEPATH')) { exit('No direct script access allowed'); }

class Config extends UnCodr {

	private $_permission = 0;
	public function __construct() {

		parent::__construct(false);

		$this->isAPI = true;
		$this->model->connect(null, false);
	}

	/**
	 * If request method is:
	 * GET - then fetches current site configurations from config table.
	 * PATCH - then updates the current site configurations (see _patchConfig method).
	 * Session is mandatory.
	 */
	public function index() {

		# check if the user is logged in, and has permission to READ configs
		$this->_permission = $this->authex->validateUser('configs');
		if(!$this->_permission) {
			$this->exitCode = ($this->_permission === 0)? 403 : 401;
			return null;
		}

		# get/update only the config fields required for the settings page
		$validFields = ['siteTitle', 'homepage', 'siteAdmin', 'isCrawlable', 'timezone', 'dateFormat', 'timeFormat', 'permaLinks', 'login', 'registration', 'comments', 'blogCount', 'notification', 'email'];
		$this->siteConfigs = $this->siteConfigs($validFields);
		switch($this->input->method()) {
			case 'get':
				$this->apiResponse['data'] = $this->siteConfigs;
				$this->exitCode = 200;
				break;
			case 'patch':
				$this->apiResponse = $this->_patchConfig($validFields);
				break;
		}
	}

	/**
	 * update post
	 * @param   array	   $validFields			config fields which can be updated
	 * @return  array	   affected config fields (keys)
	 */
	private function _patchConfig($validFields) {

		# check if user has permission to UPDATE configs
		if(!($this->_permission & 2)) {
			$this->exitCode = 403;
			return null;
		}

		$post = json_decode($this->input->raw_input_stream, true);
		$validFields = array_flip($validFields);
		$param = ['table' => 'configs', 'data' => [], 'key' => 'key', 'where' => ['key' => '']];
		$updated = [];

		# check whether current site configuration is different from post data's site configuration
		foreach($post as $key => $value) {
			if(isset($validFields[$key]) && $value != $this->siteConfigs[$key]) {
				$param['data'][] = ['key' => $key, 'value' => $value];
				$param['where']['key'] = $key;
				array_push($updated, $key);
			}
		}
		$this->model->updateBatch($param, false);

		if(count($updated)) { $this->exitCode = 204; }
		return ['updated' => $updated];
	}

	/**
	 * If request method is:
	 * GET - then fetches current site configurations from config table.
	 * PATCH - then updates the current site configurations (see _patchConfig method).
	 * Session is mandatory.
	 */
	public function users($userID = null) {

		# check if the user is logged in, and has permission to READ users
		$this->_permission = $this->authex->validateUser('users');
		if($this->_permission === false) {
			$this->exitCode = 401;
			return null;
		}

		if($userID == 'me') { $userID = $this->session['userID']; }

		switch($this->input->method()) {
			case 'get':
				$this->apiResponse = $this->_getUsers($userID);
				break;
			case 'patch':
				$this->apiResponse = $this->_patchUsers($userID);
				break;
		}
	}

	private function _getUsers($userID = null) {

		# if the user does not have permission to READ other users
		if(($userID != $this->session['userID']) && !($this->_permission & 1)) {
			$this->exitCode = 403;
			return null;
		}

		# get parameters
		$param = $this->input->get();
		$search = (isset($param['search']))? $param['search'] : null;
		$groupID = (isset($param['gid']))? $param['gid'] : null;
		$getMeta = (isset($param['meta']))? $param['meta'] : null;
		$offset = apiGetOffset($param, 50);

		# if userID is passed, then use it while getting users
		if($userID) { $param['userID'] = $userID; }

		# status sent is: all/active/inactive
		if(isset($param['status'])) {
			switch($param['status']) {
				case 'active':
					$param['users.status'] = 1;
					break;
				case 'inactive':
					$param['users.status'] = 0;
					break;
			}
			unset($param['status']);
		}

		# if sort is sent
		$orderBy = (isset($param['sort']))? apiGetOrderBy($param['sort'], 'users', ['id' => 'userID']): ['users.addedOn' => 'DESC'];

		# if fields are sent, then use it as $select
		$select = 'users.userID as id, email, login, screenName, users.name, lastLogin, loginCount, emailVerified, users.status, users.addedOn, users.lastUpdatedOn';
		if(isset($param['fields'])) { $select = $this->_selectUsersColumns(json_decode($param['fields'], true)); }

		if($select['fields']) {

			# unset keys which do not correspond to db columns
			unset($param['meta'], $param['start'], $param['page'], $param['fields'], $param['sort'], $param['gid'], $param['search']);

			# prepare parameters array for fetching data
			$param2 = [
				'table' => 'users',
				'where' => $param,
				'select' => $select['fields'],
				'order_by' => $orderBy,
				'limit' => $offset
			];

			# if search was set in get parameters, then match in email and login name
			if($search) {
				$param2['groupClause'] = ['like' => ['email' => $search], 'or_like' => ['login' => $search]];
			}

			# fetch data, and if it exists, then set exitCode as 200
			$data = $this->model->get($param2);
		} else { $data = []; }

		# if metadata is also to be fetched
		if($getMeta) {
			$meta = [];

			# fetch user's meta data and permission
			if($userID) {
				if($getMeta == 'true') { $getMeta = true; }
				else { $getMeta = json_decode($getMeta, true); }
				$meta = $this->authex->getUserMeta($userID, $getMeta);
				$meta['_'] = $this->_permission;
			}

			# fetch page size and users' count
			else {
				$meta['pageSize'] = $offset[0];
				if(in_array('count', $getMeta)) {
					$meta['count'] = $this->_countUsers($search);
				}
			}
			$this->exitCode = 200;
		}
		if(count($data)) {
			if($userID) { $data = $data[0]; }
			if($select['groups']) {
				$param2 = ($userID)? $userID : array_column($data, 'id');
				$groups = $this->_getGroupsByUserIDs($param2, $select['groups']);
			}
			$this->exitCode = 200;
		}

		return compact('meta', 'data', 'groups');
	}

	private function _countUsers($search = null) {

		$output = [
			'all' => 0,
			'active' => 0,
			'inactive' => 0
		];
		$param = [
			'table' => 'users',
			'select' => 'status, count(status) as count',
			'group_by' => 'status'
		];
		if($search) {
			$param['groupClause'] = ['like' => ['email' => $search], 'or_like' => ['login' => $search]];
		}

		$count = $this->model->get($param);
		foreach($count as $c) {
			$c['count'] = (int) $c['count'];
			$output['all'] += $c['count'];
			switch($c['status']) {
				case '1': $output['active'] += $c['count']; break;
				case '0': $output['inactive'] += $c['count']; break;
			}
		}
		return $output;
	}

	private function _selectUsersColumns($select = null) {

		$hasGroups = isset($select['groups'])? $select['groups'] : false;
		$select = array_flip($select['users']);

		if(isset($select['id'])) { $select['users.userID as id'] = $select['id']; }
		unset($select['id'], $select['password'], $select['otp'], $select['groups']);

		asort($select);
		$select = array_flip($select);
		return ['fields' => implode(',', $select), 'groups' => $hasGroups];
	}

	private function _getGroupsByUserIDs($userIDs = null, $fields = null) {


		$out = [];
		if($userIDs === null) { return $out; }

		if(gettype($userIDs) == 'array') {
			$isArray = true;
			$fields[] = 'userID';
		} else { $isArray = null; }
		$groups = $this->authex->getUserGroups($userIDs, $fields, $isArray);
		if($isArray) {
			$uID = 0;
			foreach($groups as $group) {
				$uID = (int) $group['userID'];
				unset($group['userID']);
				if(!isset($out[$uID])) { $out[$uID] = []; }
				array_push($out[$uID], $group);
			}
			return $out;
		}
		return $groups;
	}

	private function _patchUsers($userIDs = null) {

		$post = json_decode($this->input->raw_input_stream, true);
		if(!$userIDs || !count($post)) {
			$this->exitCode = 404;
			return null;
		}

		$time = time();
		$myGroups = array_flip(array_column($this->session['userData']['groups'], 'groupID'));
		$userIDs = explode('-', $userIDs);
		foreach($userIDs as $userID) {
			$data = ['user' => [], 'unique' => [], 'hasLogin' => false, 'hasScrName' => false, 'group' => null, 'meta' => null];
			$data = $this->_patchUsersSanitize($post, $data);

			# get all groups of user being edited
			$uGroups = $this->authex->getUserGroups($userID, ['groupID','expiry','permissions','status'], null);
			$uGroups = $this->authex->partitionGroups($uGroups);
			$uPermission = getPermission('users', $uGroups['active']);

			# editing own record, AND either not editing email or has WRITE permission
			$canEdit = ($userID == $this->session['userID']) && !($data['hasLogin'] && !($this->_permission & 2));
			# OR have WRITE permission and user being edited has lesser permissions
			$canEdit = $canEdit || (($this->_permission & 2) && !(($uPermission & 2) && ($this->_permission < $uPermission)));
			if(!$canEdit) {
				$this->exitCode = 403;
				return null;
			}

			if(count($post)) {

				# get other users with same email/login/screenName
				# if user exists, then send exitCode 400
				if($data['hasLogin'] || $data['hasScrName']) {
					$error = $this->_checkOldUser($userID, $data);
					if($error !== null) {
						$this->exitCode = 400;
						return ['message' => 'User already exists', 'error' => $error];
					}
				}

				# update details in 'users' table
				$post['lastUpdatedOn'] = $time;
				$output = $this->authex->updateUser($userID, $post, $data['hasLogin']);
				if($output) { $this->exitCode = 204; }
			}

			if($data['meta']) {
				$output = $this->authex->updateUserMeta($userID, $data['meta']);
				if($output) { $this->exitCode = 204; }
			}

			if($data['group']) {
				$param = elements(['add', 'update', 'remove'], $data['group']);
				$uGroups = array_merge($uGroups['active'], $uGroups['inactive']);
				$uGroups = array_flip(array_column($uGroups, 'groupID'));

				foreach($param as $key => $group) {
					if(!$group) { unset($param[$key]); }
					else {
						foreach($group as $gp) {
							if((($gp['id'] == 1) || ($gp == 1)) && !isset($myGroups[1])) {
								$this->exitCode = 403;
								return ['message' => 'You do not have access of \'Administrator\' group'];
							}
						}
					}
				}

				$output = $this->authex->updateUserGroup($userID, $param, $uGroups);
				if($output) { $this->exitCode = 204; }
			}
		}
	}

	/**
	 * sanitize post data received by _patchUsers method
	 * @param	array		$post			post data
	 * @param	array		$data			see $data array in _patchUsers method
	 * @return	array		modified $data array
	 */
	private function _patchUsersSanitize(&$post, $data) {

		foreach($post as $key => $value) {
			switch($key) {
				case 'group': case 'meta':
					$data[$key] = $value;
					unset($post[$key]);
					break;
				case 'email': case 'login':
					$data['hasLogin'] = true;
					$data['user'][] = $value;
					$data['unique'][$key] = $value;
					break;
				case 'screenName':
					$data['hasScrName'] = true;
					$data['unique'][$key] = $value;
					break;
				case 'emailVerified': case 'status':
					$post[$key] = (int) $value;
				case 'name':
					break;
				default:
					unset($post[$key]);
					break;
			}
		}
		return $data;
	}

	/**
	 * get other users with same email/login/screenName as sent in patch users post data
	 * @param	array		$data			see $data array in _patchUsers method
	 * @return	mixed		null on no error, else array with field names, values of which exist in users table
	 */
	private function _checkOldUser($userID, $data) {

		$error = null;
		$param = [
			'select' => 'email, login, screenName',
			'where' => ['userID !=' => $userID],
			'limit' => 3
		];
		if($data['hasScrName']) { $param['groupClause'] = ['or_where' => ['screenName' => $data['unique']['screenName']]]; }
		$data['user'] = $this->authex->getUser($data['hasLogin']? $data['user'] : false, $param);

		if(count($data['user'])) {
			$error = [];
			foreach($data['user'] as $value) {
				foreach ($value as $key => $val) {
					if(isset($data['unique'][$key]) && ($val === $data['unique'][$key])) { $error[] = $key; }
				}
			}
		}
		return $error;
	}
}
