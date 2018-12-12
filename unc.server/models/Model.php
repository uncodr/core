<?php

class Model extends CI_Model {

	public function __construct() {

		parent:: __construct();
	}

	/**
	 * connect to the default database
	 * If unable to connect, then resets database file and redirects to setup
	 * @param	array		$config			keys: hostname, username, password, database, dbdriver
	 * @param	boolean		$reset			whether to reset the database config
	 * @return	mixed		if config is passed, then either db object or null, else redirect to setup page on failure
	 */
	public function connect($config = null, $reset = true) {

		if($config) {

			# append default fields in config array
			$config = array_merge([
				'dsn' => '',
				'hostname' => 'localhost',
				'dbprefix' => '',
				'dbdriver' => 'mysqli',
				'pconnect' => false,
				'db_debug' => false,
				'cache_on' => false,
				'cachedir' => '',
				'char_set' => 'utf8mb4',
				'dbcollat' => 'utf8mb4_unicode_ci',
				'swap_pre' => '',
				'encrypt' => false,
				'compress' => false,
				'stricton' => true,
				'failover' => [],
				'save_queries' => false
			], $config);

			# connect to the database
			$dbObj = $this->load->database($config, true);
			return ($dbObj->conn_id)? $dbObj : null;
		}

		# connect to the default database
		else {
			$this->load->database();
			if(!$this->db->conn_id) {
				if($reset) {
					$this->dbReset();
					redirect(($this->baseURL).'setup');
				}
				return false;
			}
			return true;
		}
	}

	/**
	 * create database.php using the sample file (database.sample.php) by replacing the values in handlebars
	 * @param	array		$data		keys: db_host, db_user, db_password, db_name, db_prefix, db_driver
	 * @return	mixed		null if successful, else config file content [string]
	 */
	public function dbReset($data = null) {

		if(!$data) {
			$data = [
				'db_host' => 'localhost',
				'db_user' => '',
				'db_password' => '',
				'db_name' => '',
				'db_prefix' => 'uc_',
				'db_driver' => 'mysqli',
			];
		}

		$output = file_get_contents(APPPATH.'config/database.sample.php');
		$search = array_map(function($v) { return '{{'.$v.'}}'; }, array_keys($data));
		$output = str_replace($search, $data, $output);

		return (@file_put_contents(APPPATH.'config/database.php', $output))? null : $output;
	}

	/**
	 * select query
	 * @param	array		$param			keys: table, distinct, select, where, where_in, or_where, etc.
	 * @param	string		$returnType		query/array/object/row/row_array/unbuffered/num_rows
	 * @return	mixed		query, array, etc. depending on $returnType
	 */
	public function get($param = [], $cached = false) {

		if(!$cached) {
			$this->db->cache_off();
			$this->db->flush_cache();
		} else { $this->db->cache_on(); }

		$returnType = null;
		if(isset($param['returnType'])) {
			$returnType = $param['returnType'];
			unset($param['returnType']);
		}

		# if $param['table'] = 'table1 as table2', then use 'table2' as table name
		// $alias = (preg_match('/\sas\s/ ', $param['table']))? str_replace(' as ', '', strstr($param['table'],' as ')) : $param['table'];

		# select all if not specified
		// if(!isset($param['select'])) { $param['select'] = '*'; }

		$this->_getQuery($param);

		if($returnType == 'query') { return $this->db->get_compiled_select(); }
		else {
			$output = $this->db->get();
			switch($returnType) {
				case 'object':
					return $output->result();
				case 'row':
					return $output->row();
				case 'row_array':
					return $output->row_array();
				case 'unbuffered':
					return $output->unbuffered_row();
				case 'num_rows':
					return $output->num_rows();
				case 'array':
				default:
					return $output->result_array();
			}
		}
	}

	private function _getQuery($param = []) {

		foreach($param as $key => $value) {
			switch($key) {
				case 'table':
					$this->db->from($value);
					break;
				case 'distinct':
					$this->db->distinct();
					break;
				case 'select': case 'select_max': case 'select_min': case 'select_avg': case 'select_sum': case 'where': case 'group_by':
					$this->db->{$key}($value);
					break;

				# value is array, so use foreach loop
				case 'where_in': case 'where_not_in': case 'like': case 'not_like': case 'or_where': case 'or_where_in': case 'or_where_not_in': case 'or_like': case 'or_not_like':
					foreach($value as $k => $v) { $this->db->{$key}($k, $v); }
					break;

				# always use left join
				case 'join':
					foreach($value as $k => $v) { $this->db->join($k, $v, 'left'); }
					break;
				case 'order_by': case 'having': case 'or_having':
					$this->_switcher($key, $value);
					break;
				case 'limit':
					switch(gettype($value)) {

						# if limit is array, then first value is limit size and second is offset
						case 'array':
							if(!isset($value[1])) { $value[1] = 0; }
							$this->db->limit($value[0], $value[1]);
							break;

						# else use only limit size
						case 'integer': case 'string':
							$this->db->limit($value);
							break;
					}
					break;

				# order_by_field is an array with column name as key and array as the value
				case 'order_by_field':
					foreach($value as $k => $v) { $this->db->order_by('FIELD (`'.$k.'`, '.implode(', ', $v).')'); }
					// $this->db->_protect_identifiers = true;
					break;
				case 'groupClause':
					$this->db->group_start();
					$this->_getQuery($value);
					$this->db->group_end();
					break;
			}
		}
	}

	/**
	 * insert query
	 * $param can be an associative array with keys: table and data, or can be multidimensional array with same keys
	 * $param['data'] can be associative array with column names as keys, or can be multidimensional array with same keys
	 * @param	array		$param			keys: table, data
	 * @return	mixed		insertID or array with insertIDs
	 */
	public function insert($param = []) {

		# if table and data keys exist in param, then pass $param in _insert() method
		if(isset($param['table'], $param['data'])) { return $this->_insert($param); }

		# param is multidimensional array
		else {
			$output = [];

			# use foreach and pass each value in _insert() method
			foreach($param as $i => $value) { $output[$i] = $this->_insert($value); }
			return $output;
		}
	}

	/**
	 * (a) inserts single record if $param['data'] is associative array,
	 * (b) inserts multiple records if $param['data'] is multidimensional array
	 * @param	array		$param			keys: table, data [to be inserted]
	 * @return	mixed		insertID (int) if $param['data'] is associative, array of insertIDs if $param['data'] is multidimensional
	 */
	private function _insert($param) {

		# if $param['data'] is associative array, then call _insertSingle() method
		if(isAssoc($param['data'])) { return $this->_insertSingle($param['table'], $param['data']); }

		# call _insertSingle() method for each $param['data']'s offset
		else {
			$output = [];
			foreach($param['data'] as $i => $value) {
				$output[$i] = $this->_insertSingle($param['table'], $value);
			}
			return $output;
		}
	}

	/**
	 * inserts single record
	 * @param	string		$table			name of the table
	 * @param	array		$data			data to be inserted, keys: column names in the table
	 * @return	int			insertID
	 */
	private function _insertSingle($table, $data) {

		$this->db->insert($table, $data);
		return $this->db->insert_id();
	}

	/**
	 * insert batch data
	 * @param	array		$param			keys: table, data
	 * @return	int			number of rows inserted
	 */
	public function insertBatch($param = []) {

		return $this->db->insert_batch($param['table'], $param['data']);
	}

	/**
	 * update query
	 * $param can be an associative array with keys: table, where/where_in and data, or can be multidimensional array with same keys
	 * @param	array		$param			keys: table, data, where/where_in
	 * @param	bool		$log			whether to insert in db log table or not
	 * @return	mixed		number of rows affected or array of number of rows affected
	 */
	public function update($param = [], $log = true) {

		$this->db->cache_off();

		# if table and data keys exist in param, then pass $param in _update() method
		if(isset($param['table'], $param['data'])) {
			if($log) { $this->logData($param, 'update'); }
			return $this->_update($param);
		}

		# param is multidimensional array
		else {
			$output = [];

			# use foreach and pass each value in _update() method
			if($log) {
				foreach($param as $i => $value) {
					$this->logData($value, 'update');
					$output[$i] = $this->_update($value);
				}
			} else {
				foreach($param as $i => $value) {
					$output[$i] = $this->_update($value);
				}
			}
			return $output;
		}
	}

	/**
	 * update rows based on $param['where']/$param['where_in']
	 * @param	array		$param			keys: table, where/where_in, data
	 * @return	int			number of rows affected
	 */
	private function _update($param) {

		# use where or where_in depending on which key exists in $param
		if(isset($param['where'])) { $this->_switcher('where', $param['where']); }
		if(isset($param['where_in'])) { $this->_switcher('where_in', $param['where_in']); }

		# update and return number of affected rows
		$this->db->update($param['table'], $param['data']);
		return $this->db->affected_rows();
	}

	/**
	 * update batch data based on primary column $param['key']
	 * $param['key'] column name should exist in $param['data']
	 * @param	array		$param			keys: table, key, data
	 * @return	int			number of rows affected
	 */
	public function updateBatch($param = [], $log = true) {

		# log data if $log is truthy
		if($log) {
			$this->logData([
				'table' => $param['table'],
				'where_in' => [$param['key'] => array_column($param['data'], $param['key'])]
			], 'update');
		}

		# update and return number of affected rows
		return $this->db->update_batch($param['table'], $param['data'], $param['key']);
	}

	/**
	 * delete query
	 * $param can be an associative array with keys: table and where/where_in, or can be multidimensional array with same keys
	 * @param	array		$param			keys: table, where/where_in
	 * @param	bool		$log			whether to insert in db log table or not
	 * @return	int			number of rows affected
	 */
	public function delete($param, $log = true) {

		# if table key exists in param, then pass $param in _delete() method
		if(isset($param['table'])) {
			if($log) { $this->logData($param, 'delete'); }
			return $this->_delete($param);
		}

		# param is multidimensional array
		else {
			$output = [];

			# use foreach and pass each value in _delete() method
			if($log) {
				foreach($param as $i => $value) {
					$this->logData($value, 'delete');
					$output[$i] = $this->_delete($value);
				}
			} else {
				foreach($param as $i => $value) { $output[$i] = $this->_delete($value); }
			}
			return $output;
		}
	}

	/**
	 * delete rows based on $param['where']
	 * @param	array		$param			keys: table, where/where_in
	 * @return	int			number of rows affected
	 */
	private function _delete($param) {

		# use where or where_in depending on which key exists in $param
		if(isset($param['where'])) { $this->db->where($param['where']); }
		if(isset($param['where_in'])) { $this->db->where_in($param['where_in']); }

		# delete and return number of affected rows
		$this->db->delete($param['table']);
		return $this->db->affected_rows();
	}

	/**
	 * replace query
	 * $param can be an associative array with keys: table and where/where_in, or can be multidimensional array with same keys
	 * @param	array		$param			keys: table, data
	 * @param	bool		$log			whether to insert in db log table or not
	 * @return	int			number of rows affected
	 */
	public function replace($param, $log = true) {

		# if table key exists in param, then pass $param in _replace() method
		if(isset($param['table'])) {
			if($log) { $this->logData($param, 'replace'); }
			return $this->_replace($param);
		}

		# param is multidimensional array
		else {
			$output = [];

			# use foreach and pass each value in _replace() method
			if($log) {
				foreach($param as $i => $value) {
					$this->logData($value, 'replace');
					$output[$i] = $this->_replace($value);
				}
			} else {
				foreach($param as $i => $value) { $output[$i] = $this->_replace($value); }
			}
			return $output;
		}
	}

	/**
	 * replace rows based on $param['where']
	 * @param	array		$param			keys: table, data
	 * @return	int			number of rows affected
	 */
	private function _replace($param) {

		# replace and return number of affected rows
		$this->db->replace($param['table'], $param['data']);
		return $this->db->affected_rows();
	}

	private function _switcher($key, $value) {
		switch(gettype($value)) {
			case 'array':
				foreach($value as $k => $v) { $this->db->{$key}($k, $v); }
				break;
			default:
				$this->db->{$key}($value);
				break;
		}
	}

	/**
	 * insert record in db log table
	 * @param	array		$param 			keys: table, where/where_in
	 * @param	string		$action			update/delete
	 * @return	bool
	 */
	public function logData($param = [], $action = 'delete') {

		# get current (old) data using $param
		$data = $this->get($param);
		if(count($data)) {

			# insert log record
			$data = [
				'tableName' => $param['table'],
				'action' => $action,
				'queryParams' => json_encode($param),
				'oldData' => json_encode($data),
				'addedOn' => time()
			];
			return $this->db->insert('logs', $data);
		} else { return true; }
	}

	public function listTables($cached = false) {

		if(!$cached) { $this->db->cache_off(); }
		else { $this->db->cache_on(); }

		return $this->db->list_tables();
	}

	public function runQuery($query) {

		$query = $this->db->query($query);
		if(gettype($query) == 'object') {
			if($query->result_array()) { return $query->result_array(); }
			elseif($this->db->affected_rows()) { return $this->db->affected_rows(); }
		} else { return $query; };
	}

	/**
	 * create table in db
	 * @param	string		$tableName
	 * @param	array		$structure		keys: id, fields, pkeys, keys, hasDefaults (optional)
	 * @param	bool		$loadForge		whether to load dbforge class
	 * @return	bool
	 */
	public function createTable($tableName = '', $structure = [], $loadForge = true) {

		if($loadForge) { $this->load->dbforge(); }

		# add fields, primary keys and indexes in dbforge cache
		$structure = $this->_appendDefaults($structure);
		$this->dbforge->add_field($structure['fields']);
		foreach($structure['pkeys'] as $val) { $this->dbforge->add_key($val, true); }
		foreach($structure['keys'] as $val) { $this->dbforge->add_key($val); }

		# create table
		$out = $this->dbforge->create_table($tableName, false, ['ENGINE' => 'InnoDB']);
		if($out && isset($structure['meta'])) {
			$out = $this->_createTableMeta($structure['meta'], $structure['id']);
		}
		return $out;
	}

	/**
	 * create meta table in db
	 */
	private function _createTableMeta($tableName = '', $id = '') {

		$id = $id.'ID';
		$table = [
			'fields' => [
				$id => sqlField('id-0'),
				'key' => sqlField(),
				'value' => sqlField('text')
			],
			'pkeys' => [$id, 'key'],
			'keys' => ['key']
		];
		return $this->createTable($tableName, $table, false);
	}

	/**
	 * append default fields in the table Structure
	 * @param	array		$structure		keys: id, fields, pkeys, keys, hasDefaults (optional)
	 * @return	array		modified structure without 'hasDefaults' key
	 */
	private function _appendDefaults($structure) {

		# prepend primary field (userID, postID, etc.)
		if(!isset($structure['pkeys'])) {
			$structure['fields'] = array_merge([$structure['id'].'ID' => sqlField('id')], $structure['fields']);
			$structure['pkeys'] = [$structure['id'].'ID'];
		}
		if(!isset($structure['keys'])) { $structure['keys'] = []; }

		# append default fields (status, addedOn, lastUpdatedOn)
		if(isset($structure['hasDefaults'])) {
			$structure['fields']['status'] = sqlField($structure['hasDefaults']? 'status1' : 'status');
			$structure['fields']['addedOn'] = sqlField('epoch');
			$structure['fields']['lastUpdatedOn'] = sqlField('epoch');
		}

		# if 'status' field exists, then append it in list of fields to be indexed
		if(isset($structure['fields']['status'])) { array_push($structure['keys'], 'status'); }

		unset($structure['hasDefaults']);
		return $structure;
	}

}