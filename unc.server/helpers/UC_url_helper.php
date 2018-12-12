<?php
function baseURL($resource = '') {
	$url = sprintf("%s://%s",
		(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')? 'https' : 'http',
		$_SERVER['SERVER_NAME']
	);
	$url .= $_SERVER['BASE'];
	return $url.$resource;
}

function requestURI() {
	return str_replace($_SERVER['BASE'], '/', $_SERVER['REQUEST_URI']);
}

function appFolder() {
	return str_replace(FCPATH, '', APPPATH);
}

function assetURL($resource = '', $theme = 'core') {
	return 'assets/themes/'.$theme.'/'.$resource;
}

function templatePath($template, $theme = 'core') {
	return 'themes/'.$theme.'/'.$template;
}
