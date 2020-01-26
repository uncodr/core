<?php if(!defined('BASEPATH')) { exit('No direct script access allowed'); }

class Posts extends UnCodr {

	public function __construct() {

		parent::__construct(false);
		$this->isAPI = true;
	}

	public function index($postID = null) {

		$this->model->connect(null, false);
		$this->load->library('cms');

		switch($this->input->method()) {
			case 'get':
				$getParams = $this->input->get();
				if(!$this->_hasAccessLevel(1)) { $getParams['status'] = 'published'; }
				if(isset($getParams['meta']) && $getParams['meta'] == 'true') { $getParams['meta'] = true; }

				$this->apiResponse = $this->cms->get($postID, $getParams);
				if($this->apiResponse) {
					$this->exitCode = 200;
					$this->apiResponse['data'] = $this->apiResponse['posts'];
					unset($this->apiResponse['posts']);
				}
				break;
			case 'put':
				$this->apiResponse = $this->_put();
				break;
			case 'post': case 'patch':
				$this->apiResponse = $this->_patch($postID);
				break;
			case 'delete':
				$this->apiResponse = $this->_delete($postID);
				break;
		}
	}

	/**
	 * create post
	 * @return	int		postID
	 */
	private function _put() {

		# check if the user is logged in
		if(!$this->_hasAccessLevel(2)) { return []; }

		$post = json_decode($this->input->raw_input_stream, true);

		$meta = isset($post['meta']) ? $post['meta'] : ['put' => [], 'patch' => []];
		unset($post['meta']);

		$out = $this->cms->put($post, $this->session['userID']);

		if(isset($out['id'])) {
			$this->exitCode = 201;
			if(isset($meta['put'], $meta['put'][0])) {
				$this->_putMeta($out['id'], $meta['put']);
			}
		}
		return $out;
	}

	/**
	 * update post
	 * @return	int 		number of affected rows
	 */
	private function _patch($postID = null) {

		# check if the user is logged in
		if(!$this->_hasAccessLevel(2)) { return []; }

		$post = json_decode($this->input->raw_input_stream, true);

		$meta = isset($post['meta']) ? $post['meta'] : ['put' => [], 'patch' => []];
		unset($post['meta']);

		$out = $this->cms->patch($postID, $post);

		if (isset($meta['put'][0])) {
			$this->_putMeta($postID, $meta['put']);
		}
		if (isset($meta['patch'][0])) {
			$this->_patchMeta($postID, $meta['patch']);
		}


		$this->exitCode = ($out)? 204 : 400;
		return $out;
	}

	/**
	 * delete post
	 * @return	int 		number of affected rows
	 */
	private function _delete($postID = null) {

		if(!$postID) {
			$this->exitCode = 400;
			return [];
		}

		# check if the user is logged in
		if(!$this->_hasAccessLevel(4)) { return []; }

		# delete post and its meta
		$this->model->delete([
			'table' => 'posts',
			'where' => ['postID' => $postID]
		], true);
		$this->exitCode = 204;
		$this->_deleteMeta($postID);
		return ['count' => $affRows];
	}

	public function uploader($key = null) {

		# Show error, if $fileName doesn't have value
		if(!$key) {
			$this->exitCode = 400;
			return ['message' => 'File not specified'];
		}

		# If user is not logged in
		$this->model->connect(null, false);
		if(!$this->authex->validateUser()) {
			$this->exitCode = 401;
			return;
		}

		# If post request is received then go to _uploadfile()
		switch($this->input->method()) {
			case 'put': case 'post':
				$this->apiResponse = $this->_upload($key);
				break;
			default:
				$this->exitCode = 405;
				$this->apiResponse = ['error' => 'Request method is invalid'];
				return;
		}
	}

	private function _upload($fileName = null) {

		$date = new DateTime();
		$config =  [
			'upload_path' => FCPATH.'public/uploads/'.($date->format('Y')).'/'.($date->format('m')).'/',
			'allowed_types' => 'svg|jpeg|jpg|png|doc|docx|pdf|txt|ppt|pptx|xls|xlsx',
			'max_size' => 8192,
			'remove_spaces' => true,
			'file_name' => rand(1000,9999).time()
		];

		# Load library for file upload
		$this->load->library('upload', $config);

 		# Creating directory for file upload path & assign permission 0777 for directory
 		if(!is_dir($config['upload_path'])) { mkdir($config['upload_path'], 0777, true); }
		elseif(fileperms($config['upload_path']) != '0777') { chmod($config['upload_path'], 0777); }

		# Uploading file if exists else showing error
		if(!$this->upload->do_upload($fileName)) {
			$this->exitCode = 400;
			return ['message' => $this->upload->display_errors()];
		} else {
			$this->exitCode = 201;
			$config = $this->upload->data();
			return ['path' => str_replace(VIEWPATH, '', $config['full_path'])];
		}
	}

	public function meta($postID = null) {

		if(!$postID) {
			$this->exitCode = 404;
			return null;
		}

		$this->model->connect(null, false);
		$this->load->library('cms');

		switch($this->input->method()) {
			case 'get':
				$this->apiResponse = $this->_getMeta($postID, $this->input->get());
				if($this->apiResponse['data']) { $this->exitCode = 200; }
				break;
			case 'put':
				$this->apiResponse = $this->_putMeta($postID, json_decode($this->input->raw_input_stream, true));
				break;
			case 'post': case 'patch':
				$this->apiResponse = $this->_patchMeta($postID, json_decode($this->input->raw_input_stream, true));
				break;
			case 'delete':
				$this->apiResponse = $this->_deleteMeta($postID, $this->input->get());
				break;
		}
	}

	private function _getMeta($postID, $getParams) {

		# get meta using postID
		$limit = (isset($getParams['page']) || isset($getParams['start']))? elements(['page', 'start'], $getParams) : null;
		$where = ['postID' => $postID];
		if(isset($getParams['key'])) {
			$where['key'] = json_decode($getParams['key'], true);
		}
		$data = $this->model->getMeta('posts', ['where' => $where, 'limit' => $limit]);

		if(isset($data[0]) && isset($getParams['count'])) {
			$meta = ['pageSize' => apiGetOffset($getParams)[0]];
			unset($where['key']);
			$meta['count'] = $this->model->countMeta('posts', $where);
			$meta['count'] = (isset($meta['count'][0]))? $meta['count'][0]['count'] : 0;
			return compact('data', 'meta');
		} else { return ['data' => $data]; }
	}

	private function _putMeta($postID, $data) {

		if(!$this->_hasAccessLevel(2)) { return []; }

		if(isset($data['key'], $data['value'])) { $data = [$data]; }
		$out = $this->model->putMeta('posts', ['postID' => $postID, 'data' => $data]);
		$this->exitCode = ($out)? 201 : 401;
	}

	private function _patchMeta($postID, $data) {

		if(!$this->_hasAccessLevel(2)) { return []; }

		if(isset($data['key'], $data['value'])) { $data = [$data]; }
		$out = $this->model->patchMeta('posts', ['postID' => $postID, 'data' => $data]);
		$this->exitCode = ($out)? 204 : 401;
	}

	private function _deleteMeta($postID, $params) {

		if(!$this->_hasAccessLevel(4)) { return []; }

		$params = isset($params['key'])? ['key' => $params['key']] : [];
		$params['postID'] = $postID;
		$out = $this->model->deleteMeta('posts', $params);
		$this->exitCode = ($out)? 204 : 401;
	}

	private function _hasAccessLevel($level) {

		$permission = $this->authex->validateUser('posts');
		if(!$permission) {
			$this->exitCode = 401;
			return false;
		} elseif(!($permission & $level)) {
			$this->exitCode = 403;
			return false;
		}
		return true;
	}




	public function resources($resourceID = null) {

		switch($this->input->method()) {
			case 'get':
				$this->apiResponse = $this->_getResources($resourceID);
				break;
			case 'put':
				$this->apiResponse = $this->_putResources();
				break;
			case 'post':
			case 'patch':
				break;
			case 'delete':
				break;
		}
	}

	private function _getResources($resourceID = null) {

		# get parameters
		$param = $this->input->get();
		$search = (isset($param['search']))? $param['search'] : null;
		$offset = apiGetOffset($param);

		# if postID is passed, then use it while getting posts
		if($resourceID) { $param['resourceID'] = $resourceID; }

		# if metadata is also to be fetched
		if(isset($param['meta'])) {
			$meta = [];
		}

		# if sort is sent
		$orderBy = (isset($param['sort']))? apiGetOrderBy($param['sort'], 'assets', ['id' => 'assetID']): ['addedOn' => 'DESC'];

		# unset keys which do not correspond to db columns
		# and fetch data
		unset($param['start'], $param['page'], $param['sort'], $param['meta'], $param['search']);
		$param2 = [
			'table' => 'resources',
			'where' => $param,
			'select' => 'resourceID as id, title, path, content, description, type, addedOn',
			'order_by' => $orderBy,
			'limit' => $offset
		];
		if($search) {
			$param2['groupClause'] = ['like' => ['title' => $search], 'or_like' => ['description' => $search]];
		}
		$data = $this->model->get($param2);

		# if data exists, then set exitCode as 200
		if(count($data)) { $this->exitCode = 200; }
		return compact('meta', 'data');
	}

}
