<?php
function camelCase($str) {
	return preg_replace('/(^|_)([a-z])/e', 'strtoupper("\\2")', $str);
}

function snakeCase($str) {
	return preg_replace('/(^|[a-z])([A-Z])/e', 'strtolower(strlen("\\1") ? "\\1_\\2" : "\\2")', $str);
}

function codeToTime($str) {
	return strtotime('+'.preg_replace(['/m/i','/w/i','/d/i'], ['months ','weeks ','days '], $str));
}
