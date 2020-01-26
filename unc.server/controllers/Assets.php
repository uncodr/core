<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Assets extends UnCodr {

	public function index() {

		$path = func_get_args();

		if(!isset($path[0])) {
			$html = '<p class="no-margin">Redirecting to homepage</p>'."\n";
			$html .= "\t\t\t".'<script type="text/javascript">window.setTimeout(function() { window.location.href = \'\'; }, 500)</script>';
			$this->errorPage('', $html);
			return null;
		}

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
			case 'node_modules': case 'dump':
				$pre = FCPATH;
				break;
			default:
				unset($path);
				$this->errorPage(404);
				return;
		}
		$this->fetchFile($pre.implode('/', $path));
	}
}
