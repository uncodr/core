<?php

class Tags {

	// public $data = ['html' => '', 'heading' => 'Jamboree LMS Tags'];
	private $CI;

	public function __construct($params = [],$get = null) {
		$this->CI =& get_instance();
		$this->CI->model->connect(null, false);
	}

	/* Include Tags HTML & JS */
	public function index() {
		$this->data = ['heading' => 'Tags', 'page' => 'uncodr/config/tags'];
		$this->data['js'] = ['files' => ['tags'], 'fn' => ['tags.init()']];
		return $this->data;
	}

	/*************************Tags Details*************************************/
	/**
	 * Show Tags & Add/Edit/Delete Tags
	 * @return [type] [description]
	 */
	public function apiIndex() {
		if (!$this->CI->authex->validateUser()) {
			$this->CI->exitCode = 401;
			return;
		}
		$params = func_get_args();
		if (!isset($params[0])) {
			$params[0] = null;
		}
		switch ($this->CI->input->method()) {
		case 'get':
			$this->CI->apiResponse = $this->_getTags($params[0], $this->CI->input->get());
			break;
		case 'patch':
			$this->CI->apiResponse = $this->_patchTags($params[0]);
			break;
		case 'put':
			$this->CI->apiResponse = $this->_addTags();
			break;
		case 'delete':
			$this->CI->apiResponse = $this->_deleteTags($params[0]);
			break;
		default:
			$this->CI->exitCode = 405;
			$this->CI->apiResponse = ['error' => 'Request method is invalid'];
			return;
		}
	}

	#  Show list of Tags & particular tags related to tagID
	private function _getTags($tagID = null, $getParams = []) {

		$param = [
			'table' => 'tags',
			'select' => 'tagID as id, tagName, tagType, relatedTo, description'
		];

		# $param['limit'][0] contains pageSize & $param['limit'][1] contains page starting offset value
		if (isset($getParams['page'])) {
			$param['limit'] = apiGetOffset(['page' => $getParams['page']]);
		}
		/*if(!isset($getParams['all'])) {
			$param['limit'] = '20';
		}*/

		if ($tagID) {
			$param['where'] = ['tagID' => $tagID];
		}

		$data = ['data' => $this->CI->model->get($param)];

		if (!$tagID && isset($getParams['meta'])) {
			$getParams['meta'] = json_decode($getParams['meta'], true);
			$data['meta'] = ['pageSize' => $param['limit'][0]];
			if (isset($getParams['meta']['count'])) {
				# count of no. of records ['all', gmat', 'gre']
				$data['meta']['count'] = $this->_countTags($param);
			}
		}
		$this->CI->exitCode = ($data) ? 200 : 404;
		return $data;
	}
	#  Return count of no. records for pagination
	private function _countTags($param) {

		$output = ['all' => 0 ];
		$param['select'] = 'tagType, count(`tagType`) as count';
		$param['group_by'] = 'tagType';

		unset($param['limit']);

		$count = $this->CI->model->get($param);

		foreach($count as $c) {
			$c['count'] = (int) $c['count'];
			$output['all'] += $c['count'];
			if (!isset($output[$c['tagType']])) {
				$output[$c['tagType']] = 0;
			}
			$output[$c['tagType']] += $c['count'];
		}
		return $output;
	}
	#  Add task details
	private function _addTags() {

		$post = json_decode($this->CI->input->raw_input_stream, true);
		$post['addedOn'] = time();
		$post['tagName'] = $post['tag_name'];
		$post['tagType'] = $post['tag_type'];
		$post['relatedTo'] = $post['related_to'];

		unset( $post['tag_name'], $post['tag_type'], $post['related_to']);

		$data = $this->CI->model->insert([
			'table' => 'tags',
			'data' => $post
		]);

		$this->CI->exitCode = ($data) ? 201 : 400;
		return ['data' => $data];
	}

	#  Update task details of a particular taskID
	private function _patchTags($tagID = null) {

		$post = json_decode($this->CI->input->raw_input_stream, true);
		$post['lastUpdatedOn'] = time();
		$post['tagName'] = $post['tag_name'];
		$post['tagType'] = $post['tag_type'];
		$post['relatedTo'] = $post['related_to'];

		unset( $post['tag_name'], $post['tag_type'], $post['related_to']);

		$data = $this->CI->model->update([
			'table' => 'tags',
			'data' => $post,
			'where' => ['tagID' => $tagID]
		]);

		$this->CI->exitCode = ($data) ? 204 : 400;
		return ['data' => $data];
	}

	#  Delete task of a particular taskID
	private function _deleteTags($tagID) {

		$data = $this->CI->model->delete([
			'table' => 'tags',
			'where' => ['tagID' => $tagID]
		]);

		$this->CI->exitCode = ($data) ? 204 : 400;
	}

	/* This is for future use */
	private function _getAllTags() {

		$param = [
			'table' => 'tags',
			'select' => '*'
		];

		if($this->CI->input->get()) {
			if(isset($this->CI->input->get['fields'])) { $param['select'] = $this->CI->input->get['fields']; }
			if(isset($this->CI->input->get['type'])) { $param['where'] = ['tagType' => $this->CI->input->get['type']]; }
		}

		$data = $this->CI->model->get($param);
		if(count($data)) { $this->CI->exitCode = 200; }
		return ['data' => $data];
	}

	/************************Tags Search client & admin************************/
	/**
	 * Search Tags & Add/Delete Tags in tags_relation
	 */
	public function apiResource() {
		if (!$this->CI->authex->validateUser()) {
			$this->CI->exitCode = 401;
			return;
		}
		$params = func_get_args();
		if (!isset($params[0])) {
			$this->CI->exitCode = 400;
			$this->CI->apiResponse = ['message' => 'ResourceID is mandatory'];
			return null;
		}
		switch ($this->CI->input->method()) {
		case 'get':
			$this->CI->apiResponse = $this->getTagsByResourceID($params[0], $this->CI->input->get());
			break;
		case 'patch':
			$this->CI->apiResponse = $this->patchResourceTags($params[0], $this->CI->input->get());
			break;
		case 'delete':
			$this->CI->apiResponse = $this->deleteResourceTags($params[0],$this->CI->input->get());
			break;
		default:
			$this->CI->exitCode = 405;
			$this->CI->apiResponse = ['error' => 'Request method is invalid'];
			return;
		}
	}

	#  Show all Tags related to resourceID
	public function getTagsByResourceID($resourceID, $getParams = []) {

		if(!isset($getParams['type'])){
			$this->CI->exitCode = 400;
			return ['message'=> 'Resource type is mandatory'];
		}

		$tableName = $this->_getTableName($getParams['type']);

		$param = [
			'table' => 'tags_relation',
			'where' => ['resourceID' => $resourceID, 'tableName' => $tableName]
		];

		if(gettype($resourceID) == 'array'){
			unset($param['where']['resourceID']);
			$param['where_in'] = ['resourceID' => $resourceID];
		}

		if(isset($getParams['tagType'])){
			$param['where']['tags.tagType'] = $getParams['tagType'];
			$param['join'] = ['tags' => 'tags.tagID = tags_relation.tagID'];
		}

		if (isset($getParams['fields'])) {
			$param['select'] = $this->_sanitize($getParams['fields']);
		} else {
			$param['select'] = 'tagID as id';
		}

		if(strpos($param['select'], 'tags.') !== false && !isset($param['join']['tags'])) {
			$param['join'] = ['tags' => 'tags.tagID = tags_relation.tagID'];
		}

		$data = $this->CI->model->get($param);
		$this->CI->exitCode = ($data) ? 200 : 404;
		return ['data' => $data];
	}

	private function _sanitize($select) {

		$select = explode(',', $select);
		$select = array_flip($select);
		$output = [];

		if(isset($select['id'])) { $output['tags_relation.tagID as id'] = $select['id']; }
		if(isset($select['resourceID'])) { $output['resourceID'] = $select['resourceID']; }
		if(isset($select['name'])) { $output['tags.tagName as name'] = $select['name']; }
		if(isset($select['parent'])) { $output['tags.relatedTo as parent'] = $select['parent']; }
		if(isset($select['type'])) { $output['tags.tagType as type'] = $select['type']; }
		if(isset($select['description'])) { $output['tags.description'] = $select['description']; }
		asort($output);
		$output = array_flip($output);
		return implode(',', $output);
	}

	#  Delete & add ags in tags_resource table
	public function patchResourceTags($resourceID = null, $getParams) {

		$post = json_decode($this->CI->input->raw_input_stream, true);

		$this->CI->exitCode = 400;
		if (!isset($post['type'], $post['tagID'])) {
			return ['message' => 'Resource type & tagID are mandatory'];
		}

		if($this->CI->authex->hasGroup(['admin', 'mod'])) {
			$this->_deleteResourceTags($resourceID, ['type' => $post['type']]);
			$tableName = $this->_getTableName($post['type']);

			$tagLen = count($post['tagID']);
			$data = [];

			for ($i=0; $i < $tagLen; $i++) {
				$params = [
					'tagID'=> $post['tagID'][$i],
					'tableName' => $tableName,
					'resourceID' => $resourceID
				  ];
				array_push($data, $params);
			}

			$data = $this->CI->model->insertBatch([
				'table' => 'tags_relation',
				'data' => $data
			]);
			if ($data) {
				$this->CI->exitCode = 204;
			}
		}
	}

	#  Delete tags in tags_relation
	public function deleteResourceTags($resourceID, $getParams) {

		if(!isset($getParams['type'])){
			$this->CI->exitCode = 400;
			return ['message'=> 'Resource type is mandatory'];
		}

		$param = ['tableName' => $this->_getTableName($getParams['type']), 'resourceID'=> $resourceID];

		if(isset($getParams['tagID'])){
			$param['tagID'] = $getParams['tagID'];
		}

		$data = $this->CI->model->delete([
			'table' => 'tags_relation',
			'where' => $param
		]);

		$this->CI->exitCode = ($data) ? 204 : 400;
	}

	#  Get table name from $post['resource']
	private function _getTableName($resource){
		switch ($resource) {
			case 'post':
				return 'posts';
			case 'test_question':
				return 'jlms_tests_questions';
			case 'taskgroup':
				return 'jlms_taskgroups';
			default:
				return null;
		}
	}
}
