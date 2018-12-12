<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Assets extends UnCodr {

	public function index() {

		$html = '<p class="no-margin">Redirecting to homepage</p>'."\n";
		$html .= "\t\t\t".'<script type="text/javascript">window.setTimeout(function() { window.location.href = \'\'; }, 500)</script>';
		$this->errorPage('', $html);
		// redirect($this->baseURL);
	}

	public function get() {

		$path = func_get_args();
		$pre = VIEWPATH;

		switch($path[0]) {
			case 'themes': case 'plugins':
				array_splice($path, 2, 0, 'assets');
				if($path[0] == 'plugins') { array_splice($path, 0, 0, 'uncodr'); }
			case 'uploads':
				break;
			case 'app':
				$path[0] = APPDIR;
				array_splice($path, 1, 0, 'assets');
			case 'node_modules': case 'html':
				$pre = FCPATH;
				break;
			default:
				unset($path);
				$this->errorPage(404);
				return;
		}

		if($path) { $this->fetchFile($pre.implode('/', $path)); }
	}

	public function notFound() {

		$this->errorPage(404);
	}
}
