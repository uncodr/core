<?php

/**
 * base64 encode a string to make it url-safe
 * @param	string		$str
 * @param	bool		$encrypted whether to encrypt the string
 * @return	string
 */
function b64encode($str) {

	# encode and replace url unsafe characters
	$str = base64_encode($str);
	return str_replace(['+', '/', '='], ['-', '_', ''], $str);
}

/**
 * base64 decode a string which is url-safe, decrypt also if encrypted
 * @param	string		$str
 * @param	bool		$encrypted whether data is encrypted
 * @return	string
 */
function b64decode($str) {

	# replace url safe characters with base64 encoded characters
	$str = str_replace(['-', '_'], ['+', '/'], $str);

	# append "=" to make length a multiple of 4
	$mod4 = strlen($str) % 4;
	if($mod4) { $str .= substr('====', $mod4); }

	# decode
	return base64_decode($str);
}

/**
 * hide a n-character string in a 4n-character haystack
 * @param	string		$n
 * @return	string		a 4n-character string
 */
function shuffler($n) {

	$l = strlen($n);
	$h = randomArray(4*$l);
	$n = str_split($n);
	// 0, 5, 10, 15, 16, 21, 26, 31, 32, 37, 42, 47, 48
	for($i = 0; $i < $l; $i++) {
		$k = 5*$i - 4*floor($i/4);
		$h[$k] = $n[$i];
	}
	return implode('', $h);
}

/**
 * get a value hidden in a 4n-character haystack
 * @param	string		$h
 * @return	string		a n-character string
 */
function unshuffler($haystack) {

	$l = (int) strlen($haystack)/4;
	$haystack = str_split($haystack);
	$n = [];
	for($i = 0; $i < $l; $i++) {
		$k = 5*$i - 4*floor($i/4);
		$n[$i] = $haystack[$k];
	}
	return implode('', $n);
}

function aesEncrypt($val, $key, $cipher = 'aes-256-cbc') {
	$key = hash('sha256', $key, true);
	$output = openssl_encrypt($val, $cipher, $key, OPENSSL_RAW_DATA);

	return b64encode($output);
}

function getPermission($axn, $groups) {

	$access = 0;
	foreach($groups as $gp) {
		if($gp['groupID'] == '1') {
			$access = 7;
			break;
		} else {
			$gp['permissions'] = json_decode($gp['permissions'], true);
			if(isset($gp['permissions'][$axn])) { $access = $gp['permissions'][$axn] | $access; }
			else { $access = $gp['permissions']['*'] | $access; }
			if($access == 7) { break; }
		}
	}
	return $access;
}
