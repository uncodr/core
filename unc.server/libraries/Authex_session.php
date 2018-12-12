<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class Authex_session {

	private $refTime = 1495313597;
	public $CI;
	public $sessionTable = 'sessions';

	public function __construct() {

		$this->CI =& get_instance();
	}

	/**
	 * Create session table
	 * @param	bool		$loadForge		whether to load dbforge class
	 * @return	bool		true on success, false on failure
	 */
	protected function _setupSession($loadForge = true) {

		$table = [
			'id' => substr($this->sessionTable, 1),
			'fields' => [
				'sessionID' => sqlField('varchar', 18),
				'sessionID2' => sqlField('varchar', 18, ['null' => true]),
				'authToken' => sqlField('varchar', 40, ['null' => true]),
				'userID' => sqlField('id-n'),
				'ipAddress' => sqlField('varchar', 39),
				'userAgent' => sqlField('text'),
				'lastActivity' => sqlField('epoch'),
				'sessionExpiry' => sqlField('epoch'),
				'userData' => sqlField('longtext')
			],
			'pkeys' => ['sessionID'],
			'keys' => ['sessionID2', 'authToken', 'userID', 'sessionExpiry']
		];

		return $this->CI->model->createTable($this->sessionTable, $table, $loadForge);
	}

	/**
	 * Check headers for sessionID and authToken. Use this only in restful api calls.
	 * In other cases, use checkCookies method of this class.
	 * @param	bool		$hasToken		whether to check authToken too
	 * @return	mixed		null if no sessionID or authToken found, else headers array
	 */
	public function checkHeaders($hasToken = true) {

		$headers = getallheaders();

		# if sessionID or authToken not set in headers, then return null
		if(!isset($headers['sessionID']) || ($hasToken && !isset($headers['authToken']))) {
			return null;
		}

		// return elements(['sessionID', 'authToken'], $headers);
		return $headers;
	}

	/**
	 * Check cookies for sessionID and authToken. Use this only in non-api calls.
	 * In api calls, use checkHeaders method of this class.
	 * @param	bool		$hasToken		whether to check authToken too
	 * @return	mixed		null if no sessionID or authToken found, else cookies array
	 */
	public function checkCookies($hasToken = true) {

		$cookies = $this->CI->input->cookie();

		# if sessionID or authToken not set in cookies, then return null
		if(!isset($cookies['sessionID']) || ($hasToken && !isset($cookies['authToken']))) {
			return null;
		}

		// return elements(['sessionID', 'authToken'], $cookies);
		return $cookies;
	}

	/**
	 * Generate a partially-random sessionID
	 * @param	int			$time 			timestamp used to generate sessionID [optional]
	 * @param	bool		$persistent 	whether to create a persistent session
	 * @return	string		sessionID
	 */
	private function _setSessionID($time = null, $persistent = false) {

		if(!$time) { $time = time(); }

		# first 2 digits of sessionID are random
		$sessionID = rand(10, 99);

		# if persistent session, then 3rd digit of sessionID is 1, else 0 (browser's session)
		$sessionID .= ($persistent)? 1 : 0;

		# next few digits of sessionID = 7 multiplied by current time
		$sessionID .= 7*($time - $this->refTime);

		# last 3 digits are random
		$sessionID .= rand(100, 999);

		return $sessionID;
	}

	/**
	 * Get the sessionExpiry timestamp from sessionID
	 * @param	string		$sessionID
	 * @return	int			timestamp in unix epoch
	 */
	private function _getExpiryFromSessionID($sessionID) {

		# ignore first 3 and last 3 digits
		$addedOn = (int) substr($sessionID, 3, -3);

		# divide by 7 to get session's creation timestamp
		$addedOn = (int) $addedOn/7;

		# get expiry using the creation timestamp
		return $this->_getExpiryFromTime($addedOn + ($this->refTime), substr($sessionID, 2, 1));
	}

	/**
	 * Get the sessionExpiry timestamp from session creation timestamp
	 * @param	int			$addedOn		timestamp used to generate sessionID
	 * @param	bool		$persistent 	whether to create a persistent session
	 * @return	int			timestamp in unix epoch
	 */
	private function _getExpiryFromTime($addedOn, $persistent = false) {

		# add config's sessionExpiry to session's timestamp
		$expiry = $addedOn + ($this->CI->config->item('sess_expiration'));

		# if persisten session, add 1 year to session expiry
		$expiry += ($persistent)? 3600*24*365 : 0;

		return $expiry;
	}

	/**
	 * Generate new sessionID is session is expired or inactive for more than 10 minutes
	 * @param	array		$session 		current session record
	 * @param	string		$oldSessionID	reference sessionID used while retrieving session record
	 * @param	int			$time			timestamp used to generate sessionID [optional]
	 * @return	array		new session array
	 */
	private function _renewSessionIfExpired($session, $oldSessionID, $time = null) {

		if(!$time) { $time = time(); }

		# update sessionID2 as $data['sessionID'] when sessionID is different from the one in $data
		# this happens in case of race conditions for ajax requests
		if($session['sessionID'] != $oldSessionID) {
			$session['sessionID2'] = $oldSessionID;
		}

		# else check if the session is expired
		else {

			# generate new session if lastActivity was more than 10 minutes ago or session expired
			if(($session['lastActivity'] + 600 < $time) || ($session['sessionExpiry'] < $time)) {
				$persistent = (substr($session['sessionID'], 2, 1) == '1');
				$session['sessionID2'] = $session['sessionID'];
				$session['ipAddress'] = $this->CI->input->ip_address();

				# generate new sessionID
				$session['sessionID'] = $this->_setSessionID($time, $persistent);
				$session['sessionExpiry'] = $this->_getExpiryFromTime($time, $persistent);
			}
		}

		# update lastActivity timestamp and return
		$session['lastActivity'] = $time;
		return $session;
	}

	/**
	 * Get session data from sessionTable
	 * $data argument is same as output of checkHeaders/checkCookies method of this class.
	 * @param	array		$data			keys: sessionID, authToken, sessionID2 (optional)
	 * @return	mixed		null if session does not exist or sessionID-authToken mismatch, else session array
	 */
	public function getSession($data = []) {

		# get session record
		$session = $this->_getSession($data);

		# if no session exists then return null
		if(!count($session)) { return null; }

		$time = time();
		$session = $session[0];

		# if session exists but authToken in '$data' does not match session's authToken
		if(isset($data['authToken']) && ($session['authToken'] != $data['authToken'])) { return null; }

		# check if the session is expired
		$newSession = $this->_renewSessionIfExpired($session, $data['sessionID'], $time);

		# update session in db
		$this->CI->model->update([
			'table' => $this->sessionTable,
			'where' => ['sessionID' => $session['sessionID']],
			'data' => $newSession
		], false);

		# sanitize and return
		unset($session, $newSession['sessionID2']);
		$newSession['userData'] = json_decode($newSession['userData'], true);
		return $newSession;
	}

	/**
	 * Get session data from sessionTable using sessionID
	 * @param	array		$data			keys: sessionID, sessionID2 [optional]
	 * @return	array		record from sessionTable
	 */
	private function _getSession($data) {

		# prepare parameters array for select query from sessions table
		$param = [
			'table' => $this->sessionTable,
			'where' => ['sessionID' => $data['sessionID']],
			'or_where' => ['sessionID2' => $data['sessionID']]
		];
		if(isset($data['sessionID2'])) {
			$param['or_where']['sessionID'] = $data['sessionID2'];
		}

		# get session record
		return $this->CI->model->get($param);
	}

	/**
	 * Set/Update the session data in sessionTable.
	 * If sessionID is passed, then old session is updated, else new session record is inserted in sessionTable.
	 * While logging in/out a user, pass overWrite = true.
	 * @param	array		$data			keys: 'sessionID', 'authToken', 'userID', 'userData' => []
	 * @param	boolean		$overWrite		whether to remove the old authToken, userID and userData
	 * @param	boolean		$persistent		whether to create a persistent session
	 * @return	mixed		null if sessionID is invalid, else session array
	 */
	public function setSession($data, $overWrite = false, $persistent = false) {

		# set default values
		$time = time();
		$param = ['table' => $this->sessionTable];
		$data = elements(['sessionID', 'authToken', 'userID', 'userData'], $data);

		# if sessionID is passed, then get existing session
		if($data['sessionID']) {
			$session = $this->_getSession($data);

			# if session does not exist, then user is already logged out
			# it happens in case of race conditions
			if(!count($session)) {
				return null;
			}

			# if the session record exists, then use old sessionID in where clause for update query
			$param['where'] = ['sessionID' => $session[0]['sessionID']];

			# renew sessionID if expired
			$session = $this->_renewSessionIfExpired($session[0], $data['sessionID'], $time);
			$session['ipAddress'] = $this->CI->input->ip_address();
			$session['userAgent'] = $this->CI->agent->agent_string();

			# if old session's data to be preserved
			if(!$overWrite) {

				# if old userData exists
				if($session['userData']) {
					$session['userData'] = json_decode($session['userData'], true);

					# merge the old and new userData
					if($data['userData']) {
						$session['userData'] = array_merge($session['userData'], $data['userData']);
					}
				}

				# else use new userData
				else {
					$session['userData'] = $data['userData'];
				}
			}

			# overwrite session data
			else {
				if($data['authToken']) { $session['authToken'] = $data['authToken']; }
				if($data['userID']) { $session['userID'] = $data['userID']; }
				if($data['userData']) { $session['userData'] = $data['userData']; }
			}

			# update session record in db
			$param['data'] = $session;
			$param['data']['userData'] = json_encode($param['data']['userData']);
			$this->CI->model->update($param, false);
		}

		# create new session
		else {
			$session = $data;
			$session['sessionID'] = $this->_setSessionID($time, $persistent);
			$session['sessionExpiry'] = $this->_getExpiryFromTime($time, $persistent);
			$session['sessionID2'] = null;
			$session['ipAddress'] = $this->CI->input->ip_address();
			$session['userAgent'] = $this->CI->agent->agent_string();
			$session['lastActivity'] = $time;

			# insert session record in db
			$param['data'] = $session;
			$param['data']['userData'] = json_encode($param['data']['userData']);
			$this->CI->model->insert($param);
		}

		# sanitize and return
		unset($session['sessionID2']);
		return $session;
	}

	/**
	 * Delete the record from sessionTable
	 * @param	string		$sessionID
	 * @return	bool
	 */
	public function unsetSession($sessionID = null) {

		# prepare parameters array for select query from sessions table
		$param = [
			'table' => $this->sessionTable,
			'where' => ['sessionID' => $sessionID],
			'or_where' => ['sessionID2' => $sessionID]
		];
		$sessions = $this->CI->model->get($param, false);
		$count = 0;
		unset($param['or_where']);

		# delete session record
		foreach($sessions as $session) {
			$param['where'] = ['sessionID' => $session['sessionID']];
			return $this->CI->model->delete($param, false);
		}
	}
}
