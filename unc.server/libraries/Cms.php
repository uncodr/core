<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class Cms {

	private $CI;
	public function __construct() {

		$this->CI =& get_instance();
	}

	/**
	 * get posts and meta data
	 * @param  int $postID
	 * @param  array  $param  keys: search, meta, sort, page/start, author (screenName), status (published/drafts/trash), fields
	 * @return [type]         [description]
	 */
	public function get($postID = null, $param = []) {

		# prepare parameters array for fetching data
		$param2 = [
			'table' => 'posts',
			'select' => 'postID as id, slug, type, title, content, template, posts.password, parentID as parent, excerpt, publishedOn, commentStatus, commentCount, posts.status, posts.lastUpdatedOn, users.screenName as author, users.name as authorName',
			'order_by' => ['posts.lastUpdatedOn' => 'DESC'],
			'limit' => apiGetOffset($param)
		];

		# if postID is sent, then check whether child posts to be fetched
		if($postID) {
			if(isset($param['child']) && $param['child']) {
				$param2['groupClause'] = ['or_where' => ['postID' => $postID, 'parentID' => $postID]];
				$param2['order_by'] = ['posts.parentID' => 'ASC'];
			} else {
				$param2['where'] = ['postID' => $postID];
			}
		}

		# use author, status, type, search and sort values from $param array
		elseif(isset($param['slug'])) {
			$param2['where']['slug'] = $param['slug'];
		}

		# use sort, author, status, type, and search values from $param array
		else {

			if(isset($param['sort'])) { $param2['order_by'] = apiGetOrderBy($param['sort'], 'posts', ['id' => 'postID']); }

			$param2['where'] = [];
			if(isset($param['author'])) { $param2['where']['users.screenName'] = $param['author']; }
			if(isset($param['status'])) {
				switch($param['status']) {
					case 'published':
						$param2['where']['posts.publishedOn !='] = null;
						break;
					case 'drafts':
						$param2['where']['posts.status !='] = 0;
						$param2['where']['posts.publishedOn'] = null;
						break;
					case 'trash':
						$param2['where']['posts.status'] = 0;
						break;
				}
			}
			if(isset($param['type'])) {
				$param2['where']['posts.type'] = $param['type'];
			}
			if(isset($param['search'])) {
				$param2['groupClause'] = [
					'or_like' => ['title' => $param['search'], 'content' => $param['search']]
				];
			}
		}

		# if 'select' has fields from users table, then use join
		if(isset($param['fields'])) { $param2['select'] = $this->_sanitizePostFields($param['fields']); }
		if(strpos($param2['select'], 'users.') !== false) { $param2['join'] = ['users' => 'posts.authorID = users.userID']; }

		# fetch data, return null if not found
		$posts = $this->CI->model->get($param2, false);
		if(!count($posts)) { return null; }

		# if metadata is also to be fetched
		if(isset($param['meta'])) {

			# fetch user's meta data and permission
			if($postID) {
				$posts = $this->getMeta($posts, $param['meta']);
			}

			# fetch page size and users' count
			else {
				$meta = ['pageSize' => $param2['limit'][0]];

				$param['meta'] = array_flip($param['meta']);
				if(isset($param['meta']['count'])) { $meta['count'] = $this->_countPosts($param2); }
				if(isset($param['meta']['post'])) {
					$posts = $this->getMeta($posts, true);
				}
			}
			return compact('meta', 'posts');
		}

		return compact('posts');
	}

	private function _sanitizePostFields($select = null) {

		$select = explode(',', $select);
		$select = array_flip($select);

		if(isset($select['id'])) { $select['postID as id'] = $select['id']; }
		if(isset($select['parent'])) { $select['parentID as parent'] = $select['parent']; }
		if(isset($select['password'])) { $select['posts.password'] = $select['password']; }
		if(isset($select['status'])) { $select['posts.status'] = $select['status']; }
		if(isset($select['lastUpdatedOn'])) { $select['posts.lastUpdatedOn'] = $select['lastUpdatedOn']; }
		if(isset($select['author'])) { $select['users.screenName as author'] = $select['author']; }
		if(isset($select['authorName'])) { $select['users.name as authorName'] = $select['authorName']; }
		unset($select['id'], $select['parent'], $select['password'], $select['status'], $select['lastUpdatedOn'], $select['author'], $select['authorName']);

		asort($select);
		$select = array_flip($select);
		return implode(',', $select);
	}

	private function _countPosts($param = []) {

		$output = [
			'all' => 0,
			'published' => 0,
			'drafts' => 0,
			'trash' => 0
		];
		$param['select'] = 'posts.status, count(`'.$this->CI->db->dbprefix.'posts`.`status`) as count';
		$param['group_by'] = 'posts.status';
		unset($param['order_by'], $param['limit']);

		if(!isset($param['where']['users.screenName'])) { unset($param['join']); }

		$count = $this->CI->model->get($param);
		foreach($count as $c) {
			$c['count'] = (int) $c['count'];
			$output['all'] += $c['count'];
			switch($c['status']) {
				case '3': $output['published'] += $c['count']; break;
				case '2': $output['drafts'] += $c['count']; break;
				case '1': $output['drafts'] += $c['count']; break;
				case '0': $output['trash'] += $c['count']; break;
			}
		}
		return $output;
	}

	public function getMeta($post, $keys = null) {

		if(gettype($post) == 'array') {
			foreach($post as $i => $p) {
				$post[$i]['meta'] = $this->getMeta($p['id'], $keys);
			}
			return $post;
		}
		else { return $this->_getMeta($post, $keys); }
	}

	private function _getMeta($postID, $keys) {

		# prepare query parameters
		$param = ['table' => 'posts_meta', 'select' => 'key, value', 'where' => ['postID' => $postID]];

		# if $keys is array, use 'where_in', if string or integer, use 'where'
		switch(gettype($keys)) {
			case 'array':
				$param['where_in'] = ['key' => $keys];
				break;
			case 'string': case 'integer':
				$param['where']['key'] = $keys;
				break;
		}

		$data = $this->CI->model->get($param);
		return array_column($data, 'value', 'key');
	}

	/**
	 * create post
	 * @return	int		postID
	 */
	public function put($post = [], $authorID = null) {

		# default values
		# post data has: slug, type, title, content, template, excerpt, password, parent, commentStatus
		$time = time();
		$post['parentID'] = (int) $post['parent'];
		$post['authorID'] = $authorID;
		$post['publishedOn'] = ($post['status'] == '3')? $time : null;
		$post['addedOn'] = $time;
		$post['lastUpdatedOn'] = $time;
		unset($post['parent']);

		# create post and get postID
		$postID = $this->CI->model->insert([
			'table' => 'posts',
			'data' => $post
		]);
		return ($postID)? ['id' => $postID] : null;
	}

	/**
	 * update post
	 * @return	int 		number of affected rows
	 */
	public function patch($postID = null, $post = []) {

		if(!$postID && !$post['id']) { return null; }

		# default keys in $post: slug, type, title, content, template, excerpt, password, parent, commentStatus
		$time = time();
		$post['parentID'] = (int) $post['parent'];
		$post['publishedOn'] = ($post['status'] == '3')? $time : null;
		$post['lastUpdatedOn'] = $time;
		unset($post['parent']);

		# prepare parameters array
		$param = ['where' => ['postID' => $postID]];
		if(!$postID) {
			$param = ['where_in' => ['postID' => explode('-', $post['id'])]];
			unset($post['id']);
		}
		$param['table'] = 'posts';
		$param['data'] = $post;

		# update post data
		$affRows = $this->CI->model->update($param, false);
		return ($affRows)? ['count' => $affRows] : null;
	}
}

/*
In DB, `status` column in `posts` table corresponds to:
	0: trash, 1: draft, 2: review pending, 3: published
*/
