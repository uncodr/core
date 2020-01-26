<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Posts extends UnCodr {

	public $template = 'index';

	public function __construct() {

		parent::__construct(true);
	}

	public function index() {

		$this->load->library('cms');
		$data = [];
		switch($this->siteConfigs['homepage']) {
			case 'app':
				$this->_loadApp();
				return null;
			case 'blog':
				$params = ['sort' => ['-publishedOn'], 'status' => 'published', 'type' => 'article', 'page' => 1, 'meta' => ['post']];
				$data = $this->cms->get(null, $params);
				break;
			default:
				$homepage = explode(':',$this->siteConfigs['homepage']);
				$data['posts'] = $this->cms->get($homepage[1], ['meta' => true, 'child' => true]);
				break;
		}

		$data['bodyClass'] = 'page home';
		$data['heading'] = $this->siteConfigs['siteTitle'];
		$data['pageType'] = 'homepage';
		$data['navMenu'] = $this->_navMenu();
		$data['themeConf'] = $this->_loadThemeConf();

		$this->load->view(templatePath($this->template, $this->theme), $data);
	}

	public function get($slug = null) {

		if($this->siteConfigs['homepage'] == 'app') {
			$this->_loadApp(func_get_args());
			return;
		}

		if(!$slug) { redirect($this->baseURL); }
		elseif($slug == 'admin') { redirect(($this->baseURL).'auth'); }

		$this->load->library('cms');
		$params = ['sort' => ['-publishedOn'], 'status' => 'published', 'slug' => $slug, 'type' => 'article', 'page' => 1, 'meta' => true];
		$data = $this->cms->get(null, $params);
		$data['bodyClass'] = 'page';
		if(count($data['posts'])) {
			$data['heading'] = $data['posts'][0]['title'];
		} else {
			$this->errorPage(404);
			return null;
		}
		$data['pageType'] = 'single';

		$data['navMenu'] = $this->_navMenu();
		$data['themeConf'] = $this->_loadThemeConf();

		$this->load->view(templatePath($this->template, $this->theme), $data);
	}

	private function _loadThemeConf() {

		$out = $this->themeConfigs($this->theme);
		if(isset($out['partialsPost'], $out['partials'])) {
			foreach ($out['partialsPost'] as $key => $value) {
				$out['partials'][$key] = $out['partials']['posts'][$value];
			}
			unset($out['partialsPost']);
		}
		return $out;
	}

	private function _loadApp($path = []) {

		$validURLs = ['bundle.js', 'bundle.min.js', 'assets', 'html'];
		$validURLs = array_flip($validURLs);

		if(isset($path[0]) && ($path[0] == 'app')) { array_shift($path); }

		if(!isset($path[0]) || !isset($validURLs[$path[0]])) {
			switch($this->input->method()) {
				case 'post': case 'put':
					$this->siteConfigs['__POST'] = $this->input->post();
					break;
			}
			$this->load->view('../'.APPDIR.'/index', ['data' => $this->siteConfigs]);
			return null;
		}
		elseif ($path[0]=='html') {
			$path[0] = 'src/components';
		}
		$this->fetchFile(APPDIR.'/'.implode('/', $path));
	}

	private function _navMenu($current = 'home') {

		# list of all links
		$links = [
			'about' => ['text' => 'About', 'class' => ''],
			'auth' => ['text' => 'Login', 'class' => 'separate']
		];

		# add active class
		if(isset($links[$current])) { $links[$current]['class'] .= ' active'; }

		return $links;
	}
}
