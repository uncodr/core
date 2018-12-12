<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends UnCodr {

	public function __construct() {

		parent::__construct(true);
	}

	public function index() {

		$this->loadPage('dashboard');
	}

	public function loadPage() {

		$data = ['heading' => '', 'page' => 'uncodr/admin/', 'js' => ['files' => [], 'fn' => []]];
		$path = func_get_args();

		switch($path[0]) {
			case 'dashboard':
				$data['heading'] = 'Dashboard';
				$data['page'] .= 'dashboard';
				$data['js']['files'] = ['dashboard'];
				$data['js']['fn'] = ['admin.dashboard()'];
				break;
			case 'posts':
				if($path[1] == 'media') {
					$data['heading'] = 'Media';
					$data['page'] .= 'posts/media';
					$data['js']['fn'] = ['admin.media()'];
				} else {
					$data['page'] .= 'posts/posts';
					$subPage = substr($path[1], 0, -1);
					$data['heading'] = ucfirst($subPage).'s';
					$data['subPage'] = $subPage;
					$data['js']['fn'] = ['admin.posts(\''.$subPage.'\')'];
				}
				$data['js']['files'] = ['posts'];
				break;
			case 'plugins':
				if(isset($path[1])) {
					unset($path[0]);
					$data = $this->_loadPlugin(...$path);
				} else {
					$data['heading'] = 'Plugins';
					$data['page'] .= 'plugins';
					$data['js']['files'] = [];
					$data['js']['fn'] = [];
				}
				break;
			case 'config':
				$data['js']['files'] = ['config'];
				if(isset($path[1])) {
					$data['heading'] = ucfirst($path[1]);
					$data['page'] .= 'config/'.$path[1];
					$data['js']['fn'] = ['admin.'.$path[1].'()'];
					if($path[1] == 'users') {
						$data['js']['files'] = ['config.users'];

						# get all groups
						$data['groups'] = $this->model->get([
							'table' => 'groups',
							'select' => 'groupID, code, name, expiry',
						]);
					}
				}

				# settings page
				else {
					$data['heading'] = 'Settings';
					$data['page'] .= 'config/index';
					$data['js']['fn'] = ['admin.settings()'];

					# get groups with registration enabled
					$data['groups'] = $this->model->get([
						'table' => 'groups',
						'select' => 'code, name',
						'where' => ['registration' => 1]
					]);
					if(isset($data['groups'][0])) { $data['groups'] = array_column($data['groups'], 'name', 'code'); }
				}
				break;
			default:
				show_404();
				break;
		}
		$data['navMenu'] = $this->_navMenu(implode('/', $path));

		$this->load->view(templatePath('admin', 'core'), $data);
	}

	private function _navMenu($current = 'dashboard') {

		# list of all links
		$links = [
			'dashboard' => ['text' => 'Dashboard', 'icon' => 'grid', 'class' => 'separate '],
			'posts/articles' => ['text' => 'Blog Articles', 'icon' => 'compose', 'class' => ''],
			'posts/pages' => ['text' => 'Pages', 'icon' => 'document', 'class' => ''],
			'posts/media' => ['text' => 'Media', 'icon' => 'images', 'class' => 'separate '],
			'config/users' => ['text' => 'Users &amp; Groups', 'icon' => 'person-stalker', 'class' => ''],
			'config/themes' => ['text' => 'Themes', 'icon' => 'paintbrush', 'class' => ''], // wand, paintbrush, eye
			'plugins' => ['text' => 'Plugins', 'icon' => 'settings', 'class' => ''],
			'config' => ['text' => 'Settings', 'icon' => 'gear-a', 'class' => 'separate ']
		];

		# add active class
		if(isset($links[$current])) {
			$links[$current]['class'] .= 'active';
		}

		return $links;
	}
}
