<?php if (!defined('BASEPATH')) {exit('No direct script access allowed');}

class Plugins extends UnCodr {

	public function __construct() {

		parent::__construct(false);

		$this->isAPI = true;
	}

	public function index() {

		# if parameters not passed
		$params = func_get_args();
		$this->_loadPlugin(...$params);
	}

	public function error() {

		$this->exitCode = 404;
	}
}
