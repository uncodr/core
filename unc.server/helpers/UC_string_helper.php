<?php
function camelCase($str, $separator = '_') {
	$regex = '/(^|'.$separator.')([a-z])/e';
	return preg_replace($regex, 'strtoupper("\\2")', $str);
}

function snakeCase($str) {
	return preg_replace('/(^|[a-z])([A-Z])/e', 'strtolower(strlen("\\1") ? "\\1_\\2" : "\\2")', $str);
}

function codeToTime($str, $t = null) {
	if(!$t) { $t = time(); }
	return strtotime('+'.preg_replace(['/m/i','/w/i','/d/i'], ['months ','weeks ','days '], $str), $t);
}
