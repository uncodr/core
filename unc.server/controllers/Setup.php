<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Setup extends UnCodr {

	public function index() {

		# connect to the database
		if($this->model->connect(null, false)) {
			redirect(($this->baseURL).'setup/config');
		}

		# show page
		else { $this->_loadPage(0); }
	}

	public function db() {

		# connect to the database
		if($this->model->connect(null, false)) {
			redirect(($this->baseURL).'setup/config');
		}

		# show page
		else { $this->_loadPage(1); }
	}

	public function config() {

		# connect to the database
		if(!$this->model->connect(null, false)) {
			redirect(($this->baseURL).'setup/db');
		}

		# show page
		else {
			$this->_configValidate();
			$this->_loadPage(2);
		}
	}

	public function finish() {

		# connect to the database
		if(!$this->model->connect(null, false)) {
			redirect(($this->baseURL).'setup/db');
		}

		# show page
		else {
			if($post = $this->input->post()) { $this->_configApp($post); }
			$this->_loadPage(3);
		}
	}

	public function clean() {

		$this->model->dbReset();
		redirect(($this->baseURL).'setup');
	}

	/**
	 * connect to db, create config file database.php
	 */
	public function install() {

		$data = [];

		# if post data is sent
		$post = $this->input->post();
		if($post) {

			# if '_dbConnect' returns some error, i.e. db connection could not be established
			if($error = $this->_dbConnect($post)) {
				$data = [
					'heading' => 'Database Error',
					'page' => 'uncodr/setup/'.$error,
					'post' => $post
				];
			}

			# else write to the config file database.php
			else {

				# if cannot write to the config file
				if($conf = $this->model->dbReset($post)) {
					$data = [
						'heading' => 'File Write Error',
						'page' => 'uncodr/setup/error_file_write',
						'content' => $conf
					];
				};
			}
		}

		# if some error occurs, then show error page
		if(count($data)) {
			$data['bodyClass'] = 'error';
			$this->_loadPage(null, $data);
		}

		# redirect to config page or db installation page depending on whether post data was sent
		else { redirect(($this->baseURL).'setup/config'); }
	}

	/**
	 * chech db connection and create necessary tables
	 * @param	array		$data		keys: db_host, db_user, db_password, db_name, db_prefix, db_driver
	 * @return	mixed		null if no error, else error type [string]
	 */
	private function _dbConnect($data) {

		# assume the connection cannot be established
		$error = 'error_db_conn';
		$config = [
			'hostname' => $data['db_host'],
			'username' => $data['db_user'],
			'password' => $data['db_password'],
			'database' => $data['db_name'],
			'dbprefix' => $data['db_prefix'],
			'dbdriver' => $data['db_driver']
		];

		# connect to the db using the config array
		$dbObj = $this->model->connect($config);
		if(!$dbObj) {

			# retry connecting without selecting db
			$db = $config['database'];
			$config['database'] = 'information_schema';
			$dbObj = $this->model->connect($config);

			# if connected, that implies the selected db does not exist
			if($dbObj) {

				# if possible, try creating the db (assuming root user)
				$dbforge = $this->load->dbforge($dbObj, true);
				if($dbforge->create_database($db)) {
					$config['database'] = $db;
					$dbObj->db_select($db);
					$error = null;
				}

				# else close database connection and send error
				else {
					$dbObj->close();
					$error = 'error_db_404';
				}
			}
		} else { $error = null; }

		return $error;
	}

	private function _configValidate() {

		# repair db
		$tablesCreated = $this->_tablesRepair();
		if(!count($tablesCreated)) {

			# if no table created, then see if siteTitle exists
			$result = $this->model->get([
				'table' => 'configs',
				'where' => ['key' => 'siteTitle']
			]);

			# if siteTitle exists, then see if any user exists
			if(isset($result[0])) {
				$result = $this->model->get([
					'table' => 'users',
					'limit' => 1
				]);
			}

			# if user also exists, then redirect to setup finish page
			if(isset($result[0])) { redirect(($this->baseURL).'setup/finish'); }
		}
	}

	/**
	 * creates essential tables if missing in the db
	 * @return	array		keys: table names, value: true if successfully repaired
	 */
	private function _tablesRepair() {

		# load dbforge library
		$this->load->dbforge();
		$output = [];

		# list of all tables in the db
		$tablesDB = $this->model->listTables(false);
		$tablesDB = array_flip($tablesDB);

		# check authex tables and create if they do not exist
		$authexTables = [];
		$tableName = ($this->db->dbprefix).($this->authex->sessionTable);
		if(!isset($tablesDB[$tableName])) { $authexTables[] = $this->authex->sessionTable; }
		if(!isset($tablesDB[($this->db->dbprefix).'users'])) { $authexTables[] = 'users'; }
		if(!isset($tablesDB[($this->db->dbprefix).'groups'])) { $authexTables[] = 'groups'; }
		$output = $this->authex->setup($authexTables, false);

		# structures of all tables which are required for uncodr
		$tables = $this->_tableStructure();

		foreach($tables as $type => $value) {
			$tableName = ($this->db->dbprefix).$type.'s';

			# if table does not exist in db, then create it
			if(!isset($tablesDB[$tableName])) {
				$output[$type.'s'] = $this->model->createTable($type.'s', $value, false);
			}
		}

		return $output;
	}

	/**
	 * get the structure of the table by type
	 * @param	string		$type		table type for which structure to be retrieved
	 * @return	array		[fields, keys, pkeys (array of primary keys)]
	 */
	private function _tableStructure() {

		# list of essential tables for uncodr
		$tables = [
			'post' => [
				'id' => 'post',
				'fields' => [
					'slug' => sqlField('unique', '191'),
					'authorID' => sqlField('id-n'),
					'type' => sqlField('var31'),
					'title' => sqlField('var191'),
					'content' => sqlField('longtext'),
					'excerpt' => sqlField('text'),
					'publishedOn' => sqlField('epoch'),
					'template' => sqlField('var31'),
					'password' => sqlField('var63'),
					'parentID' => sqlField('id-n'),
					'commentCount' => sqlField('count'),
					'commentStatus' => sqlField('status')
				],
				'keys' => ['authorID', 'type', 'publishedOn', 'parentID'],
				'hasDefaults' => false,
				'meta' => 'posts_meta'
			],
			'comment' => [
				'id' => 'comment',
				'fields' => [
					'postID' => sqlField('id-n'),
					'authorName' => sqlField('var63'),
					'authorEmail' => sqlField('var191'),
					'authorURL' => sqlField('var191'),
					'authorIP' => sqlField('varchar', '39'),
					'content' => sqlField('text'),
					'type' => sqlField('text'),
					'parentID' => sqlField('id-n'),
					'rating' => sqlField('id-0'),
					'voteCount' => sqlField('id-0'),
				],
				'keys' => ['postID', 'authorEmail'],
				'hasDefaults' => false,
				'meta' => 'comments_meta'
			],
			'asset' => [
				'id' => 'asset',
				'fields' => [
					'title' => sqlField('var63'),
					'path' => sqlField('varchar', '127'),
					'content' => sqlField('text'),
					'meta' => sqlField('text'),
					'mimeType' => sqlField('var31'),
					'addedOn' => sqlField('epoch')
				],
				'keys' => ['title', 'mimeType', 'addedOn']
			],
			'log' => [
				'id' => 'log',
				'fields' => [
					'tableName' => sqlField('var31'),
					'action' => sqlField('var15'),
					'queryParams' => sqlField('text'),
					'oldData' => sqlField('text'),
					'addedOn' => sqlField('epoch')
				],
				'keys' => ['addedOn']
			],
			'config' => [
				'id' => 'config',
				'fields' => [
					'key' => sqlField('var31'),
					'value' => sqlField('text'),
					'autoload' => sqlField('status')
				],
				'keys' => ['key', 'autoload']
			]
		];

		return $tables;
	}

	private function _configApp($data) {

		# get current siteTitle from configs table
		$param = [
			'table' => 'configs',
			'where' => ['key' => 'siteTitle'],
			'data' => [
				['key' => 'siteTitle', 'value' => $data['title'], 'autoload' => 1]
			]
		];
		$result = $this->model->get($param);

		# if siteTitle exists, then update
		if(count($result)) {
			$param['data'] = $param['data'][0];
			$this->model->update($param, false);
		}

		# else insert siteTitle record
		else {
			$param['data'] = [
				$param['data'][0],
				['key' => 'homepage', 'value' => 'blog', 'autoload' => 1],
				['key' => 'siteAdmin', 'value' => $data['email'], 'autoload' => 1],
				['key' => 'isCrawlable', 'value' => '0', 'autoload' => 1],
				['key' => 'timezone', 'value' => date('Z'), 'autoload' => 1],
				['key' => 'dateFormat', 'value' => 'F d, Y', 'autoload' => 1],
				['key' => 'timeFormat', 'value' => 'H:i:s', 'autoload' => 1],
				['key' => 'permaLinks', 'value' => ':slug', 'autoload' => 1],
				['key' => 'theme', 'value' => 'earth', 'autoload' => 1],
				['key' => 'login', 'value' => '{"unverified_limit":0,"disable_on_expiry":1}', 'autoload' => 0],
				['key' => 'registration', 'value' => '{"enable":0,"autologin":1,"default_group":"user"}', 'autoload' => 0],
				['key' => 'comments', 'value' => '{"enable":0,"sort":"DESC","author_info":1,"public":0,"moderation":1,"autoclose":0}', 'autoload' => 0],
				['key' => 'email', 'value' => '{}', 'autoload' => 0],
				['key' => 'blogCount', 'value' => '12', 'autoload' => 0],
				['key' => 'notification', 'value' => '[]', 'autoload' => 0]
			];
			$this->model->insertBatch($param);
		}

		# check whether user already exists
		$user = $this->authex->getUser([$data['email'], $data['user']], ['select' => 'userID']);
		if(!count($user)) {

			# create groups
			$groupID = $this->authex->createGroup([
				['code' => 'admin', 'name' => 'Administrator', 'permissions' => '{"*": 7}', 'registration' => '0'],
				['code' => 'mod', 'name' => 'Moderator', 'permissions' => '{"posts": 7, "configs": 7, "*": 1}', 'registration' => '0'],
				['code' => 'editor', 'name' => 'Editor', 'permissions' => '{"posts": 3, "*": 0}', 'registration' => '0'],
				['code' => 'user', 'name' => 'User', 'permissions' => '{"*": 0}', 'registration' => '1']
			]);

			# create user
			$user = $this->authex->createUser([[
				'email' => $data['email'],
				'login' => $data['user'],
				'password' => $data['password'],
				'emailVerified' => 1,
				'status' => 1,
				'group' => ['groupID' => $groupID[0], 'expiry' => null]
			]], false);
		}

		# create 2 pages (home, about)
		# create 2 posts (hello world, bhoomija)
		# have 3 comments (1 nested under another, and 1 independent)
		#
	}

	private function _loadPage($stepNum = 0, $data = null) {

		if(!$data) {
			switch($stepNum) {
				case 0:
					$data = ['page' => 'uncodr/setup/index', 'heading' => APP_NAME.' Setup'];
					break;
				case 1:
					$data = ['page' => 'uncodr/setup/db', 'heading' => 'Install Database'];
					break;
				case 2:
					$data = ['page' => 'uncodr/setup/config', 'heading' => 'Configure Website'];
					break;
				case 3:
					$data = ['page' => 'uncodr/setup/finish', 'heading' => 'Setup Complete'];
					break;
			}
			$data['bodyClass'] = 'setup';
		}

		$this->load->view(templatePath('minimal', 'core'), $data);
	}
}
