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
	 * @return [type]		 [description]
	 */
	public function get($postID = null, $param = []) {

		# prepare parameters array for fetching data
		$param2 = [
			'table' => 'posts',
			'select' => 'postID as id, slug, type, title, content, template, posts.password, parentID as parent, excerpt, publishedOn, commentStatus, commentCount, posts.status, posts.lastUpdatedOn, users.screenName as author, users.name as authorName',
			'order_by' => ['posts.lastUpdatedOn' => 'DESC'],
			'limit' => apiGetOffset($param)
		];
		$isSingle = 0;

		# if postID is sent, then check whether child posts to be fetched
		if($postID) {
			if(isset($param['child']) && $param['child']) {
				$param2['groupClause'] = ['or_where' => ['postID' => $postID, 'parentID' => $postID]];
				$param2['order_by'] = ['posts.parentID' => 'ASC'];
			} else {
				$param2['where'] = ['postID' => $postID];
				$isSingle = 1;
			}
		}

		# else if slug is sent
		elseif(isset($param['slug'])) {
			$param2['where']['slug'] = $param['slug'];
			$isSingle = 1;
		}

		# use sort, author, type, and search values from $param array
		else {

			if(isset($param['sort'])) { $param2['order_by'] = apiGetOrderBy($param['sort'], 'posts', ['id' => 'postID']); }

			$param2['where'] = [];
			if(isset($param['author'])) { $param2['where']['users.screenName'] = $param['author']; }
			if(isset($param['type'])) { $param2['where']['posts.type'] = $param['type']; }
			if(isset($param['search']) && $param['search']) {
				$param2['groupClause'] = [
					'or_like' => ['title' => $param['search'], 'content' => $param['search']]
				];
			}
		}

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

		# if 'select' has fields from users table, then use join
		if(isset($param['fields'])) { $param2['select'] = $this->_sanitizePostFields($param['fields'], $isSingle); }
		if(strpos($param2['select'], 'users.') !== false) { $param2['join'] = ['users' => 'posts.authorID = users.userID']; }

		# fetch data, return null if not found
		$posts = $this->CI->model->get($param2, false);
		if(!count($posts)) { return null; }

		# if metadata is also to be fetched
		if(isset($param['meta'])) {

			# fetch user's meta data and permission
			if($isSingle !== 0) {
				if(!$postID) { $postID = isset($posts[0]['postID'])? $posts[0]['postID'] : $posts[0]['id']; }
				if($isSingle !== 1) { unset($posts[0]['postID']); }
				$posts[0]['meta'] = $this->getMeta($postID, $param['meta']);
			}

			# fetch page size and users' count
			else {
				$meta = ['pageSize' => $param2['limit'][0]];

				$param['meta'] = array_flip($param['meta']);
				if(isset($param['meta']['count'])) {
					$meta['count'] = $this->_countPosts($param2, !isset($param['restrictCount']));
				}
				if(isset($param['meta']['post'])) {
					$posts = $this->getMeta($posts, true);
				}
			}
			return compact('meta', 'posts');
		}

		return compact('posts');
	}

	private function _sanitizePostFields($select = null, &$isSingle) {

		$select = explode(',', $select);
		$select = array_flip($select);
		$hasID = false;

		if(isset($select['id'])) { $select['postID as id'] = $select['id']; $hasID = true; }
		if(isset($select['parent'])) { $select['parentID as parent'] = $select['parent']; }
		if(isset($select['password'])) { $select['posts.password'] = $select['password']; }
		if(isset($select['status'])) { $select['posts.status'] = $select['status']; }
		if(isset($select['lastUpdatedOn'])) { $select['posts.lastUpdatedOn'] = $select['lastUpdatedOn']; }
		if(isset($select['author'])) { $select['users.screenName as author'] = $select['author']; }
		if(isset($select['authorName'])) { $select['users.name as authorName'] = $select['authorName']; }
		unset($select['id'], $select['parent'], $select['password'], $select['status'], $select['lastUpdatedOn'], $select['author'], $select['authorName']);

		asort($select);
		if($isSingle && !$hasID && !isset($select['postID'])) {
			$select['postID'] = count($select);
			$isSingle = -1;
		}

		$select = array_flip($select);
		return implode(',', $select);
	}

	private function _countPosts($param = [], $getAll = true) {

		$output = [
			'all' => 0,
			'published' => 0,
			'drafts' => 0,
			'trash' => 0
		];
		$param['select'] = 'posts.status, count(`'.$this->CI->db->dbprefix.'posts`.`status`) as count';
		$param['group_by'] = 'posts.status';
		unset($param['order_by'], $param['limit'], $param['where']['posts.status'], $param['where']['posts.status !=']);
		if($getAll) { unset($param['where']['posts.publishedOn'], $param['where']['posts.publishedOn !=']); }

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

	public function getMeta($postIDs, $keys = null) {

		if(gettype($postIDs) == 'array') {
			foreach($postIDs as $i => $p) {
				$postIDs[$i]['meta'] = $this->CI->model->getMeta('posts', ['where' => ['postID' => $p['id'], 'key' => $keys]]);
			}
			return $postIDs;
		}
		else { return $this->CI->model->getMeta('posts', ['where' => ['postID' => $postIDs, 'key' => $keys]]); }
	}

	/**
	 * create post
	 * @return	int		postID
	 */
	public function put($post = [], $authorID = null) {

		# default values
		# post data has: slug, type, title, content, template, excerpt, password, parent, commentStatus
		$time = time();
		$post['parentID'] = ($post['parent'])? (int) $post['parent'] : null;
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
	public function patch($postID = null, $data = [], $isSync = false) {

		if(!$postID && !$data['id']) { return null; }

		# default keys in $data: slug, type, title, content, template, excerpt, password, parent, commentStatus
		if(isset($data['parent'])) {
			$data['parentID'] = ($data['parent'])? (int) $data['parent'] : null;
		}
		unset($data['parent']);

		# if only updating the commentCount, then this parameter is true, so publishedOn does not change
		if(!$isSync) {
			$time = time();
			$data['publishedOn'] = ($data['status'] == '3')? $time : null;
			$data['lastUpdatedOn'] = $time;
		}

		# prepare parameters array
		$param = ['where' => ['postID' => $postID]];
		if(!$postID) {
			$param = ['where_in' => ['postID' => explode('-', $data['id'])]];
			unset($data['id']);
		}
		$param['table'] = 'posts';
		$param['data'] = $data;

		# update post data
		$affRows = $this->CI->model->update($param, false);
		return ($affRows)? ['count' => $affRows] : null;
	}

	public function getComments($postID = null, $getPublished = true) {

		if(!$postID) { return null; }

		# NOTE: code within multiline comments (/**/) is for recursive selection of
		# comments. This code outputs an array with parent comments on zeroth offset
		# and children on remaining offsets with parent comment's ID as the key.
		$param = [
			'table' => 'comments',
			'select' => 'commentID as id, authorName as name, authorEmail as email, content, postID, parentID as parent, lastUpdatedOn',
			'where' => [/*'parentID' => null*/],
			'where_in' => [],
			'order_by' => ['lastUpdatedOn' =>  'DESC']
		];
		if($getPublished) { $param['where']['status'] = 3; }
		else { $param['select'] .= ', status'; }

		$param = apiWhereByType($param, ['postID' => $postID]);

		$output = $this->CI->model->get($param);

		# zeroth offset is an array of all comments on given postID
		/*return (count($output))? $this->_getCommentsRecursively(array_column($output, 'id'), [$output], $param) : null;*/
		return (count($output))? $output : null;
	}

	private function _getCommentsRecursively($parentIDs, $output, $param) {

		# get comments using $parentIDs in 'where_in' condition
		if(!count($parentIDs)) { return []; }
		$param['where_in'] = ['parentID' => $parentIDs];
		$data = $this->CI->model->get($param);
		if(count($data)) {

			# commentIDs is an array to store the commentID for recursive lookup
			$commentIDs = [];
			foreach ($data as $key => $value) {
				$commentIDs[] = (int) $value['id'];

				# push the current record (i.e. $value) at 'parentID' offset of output
				$parentID = (int) $value['parent'];
				unset($value['parent']);
				if(!isset($output[$parentID])) { $output[$parentID] = []; }
				$output[$parentID][] = $value;
			}

			# recursive lookup
			$commentIDs = array_unique($commentIDs);
			if (count($commentIDs)) {
				$output = $this->_getCommentsRecursively($commentIDs, $output);
			}
		}

		return $output;
	}

	public function putComment($data = [], $author = []) {

		# default keys in data: content, parent, postID, status
		if(!isset($data['content']) || !isset($data['postID']) || !isset($data['status'])) { return null; }

		# get original post
		$post = $this->get($data['postID'], ['fields' => 'commentStatus,commentCount']);
		$post = $post['posts'];
		if(!isset($post[0]) || !$post[0]['commentStatus']) { return null; }

		$time = time();
		$data['parentID'] = ($data['parent'])? (int) $data['parent'] : null;
		$data['authorName'] = $author['name'];
		$data['authorEmail'] = $author['email'];
		$data['addedOn'] = $time;
		$data['lastUpdatedOn'] = $time;
		unset($data['parent']);

		# create comment and get commentID
		$commentID = $this->CI->model->insert([
			'table' => 'comments',
			'data' => $data
		]);

		if($commentID) {

			# update post's commentCount if comment is published
			if($data['status'] == '3') {
				$post[0]['commentCount'] = (int) $post[0]['commentCount'];
				$this->patch($data['postID'], ['commentCount' => $post[0]['commentCount']+1], true);
			}

			return ['id' => $commentID];
		} else { return null; }
	}

	public function patchComment($commentID = null, $data = []) {

		if(!$commentID && !$data['id']) { return null; }

		# get original post
		if(isset($data['validate'])) {
			$post = $this->get($data['postID'], ['fields' => 'commentStatus,commentCount']);
			$post = $post['posts'];
			if(!isset($post[0]) || !$post[0]['commentStatus']) { return null; }
			unset($data['validate'], $data['postID']);
		}

		if(isset($data['parent'])) {
			$data['parentID'] = ($data['parent'])? (int) $data['parent'] : null;
		}
		unset($data['parent']);

		$data['lastUpdatedOn'] = time();

		# prepare parameters array
		$param = ['where' => ['commentID' => $commentID]];
		if(!$commentID) {
			$commentID = explode('-', $data['id']);
			$param = ['where_in' => ['commentID' => $commentID]];
			unset($data['id']);
		}
		$param['table'] = 'comments';
		$param['data'] = $data;

		# update comments
		$affRows = $this->CI->model->update($param, false);
		if($affRows) {

			# update commentCount for the given postID
			if(isset($data['status'])) {
				if(isset($param['where'])) {
					$this->updateCommentCount($data['postID']);
				} else {
					$data['postID'] = array_unique(explode('-', $data['postID']));
					foreach($data['postID'] as $i => $val) {
						$this->updateCommentCount($val);
					}
				}
			}
			return ['count' => $affRows];
		} else { return null; }
	}

	public function getCommentCount($postID = null) {

		if(!$postID) { return null; }
		$param = [
			'table' => 'comments',
			'select' => 'status,COUNT(*) as count',
			'where' => ['postID' => $postID],
			'group_by' => ['status']
		];

		$output = $this->CI->model->get($param);

		return (count($output))? $output : null;
	}

	public function updateCommentCount($postID = null) {

		$comments = $this->getCommentCount($postID);
		if(!$comments) { return null; }

		$l = count($comments);
		foreach ($comments as $i => $val) {
			if($val['status'] == 3) {
				$this->patch($postID, ['commentCount' => $val['count']], true);
				break;
			}
		}
	}
}

/*
In DB, `status` column in `posts` table corresponds to:
	0: trash, 1: draft, 2: review pending, 3: published
*/
