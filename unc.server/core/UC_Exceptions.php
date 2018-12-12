<?php if(!defined('BASEPATH')) { exit('No direct script access allowed'); }

class UC_Exceptions extends CI_Exceptions {

	public function __construct() {

		parent::__construct();
	}

	function show_error($heading, $message, $template = 'error_general', $status_code = 500) {

		$CI =& get_instance();
		$data2 = [
			'heading' => $heading,
			'html' => $message,
			'template' => $template,
			'code' => $status_code,
			'bodyClass' => 'error e'.$status_code
		];
		$html = $CI->load->view('themes/core/minimal', $data2, true);
		echo $html;
	}
}
