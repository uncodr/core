<?php
# http://stackoverflow.com/questions/173400/how-to-check-if-php-array-is-associative-or-sequential
function isAssoc(array $arr) {
	if(array() === $arr) { return false; }
	return (array_keys($arr) !== range(0, count($arr) - 1));
}

function array_unshift_assoc(&$arr, $key, $val) {
	$arr = array_reverse($arr, true);
	$arr[$key] = $val;
	return array_reverse($arr, true);
}

function sqlField($type = 'varchar', $length = '15', $params = []) {
	$output = [];
	switch($type) {
		case 'id':
			$output = ['type' => 'INT', 'constraint' => '10', 'unsigned' => true, 'auto_increment' => true]; break;
		case 'id-0':
			$output = ['type' => 'INT', 'constraint' => '10', 'unsigned' => true, 'default' => 0]; break;
		case 'id-n':
			$output = ['type' => 'INT', 'constraint' => '10', 'unsigned' => true, 'null' => true]; break;
		case 'id2':
			$output = ['type' => 'INT', 'constraint' => '10', 'unsigned' => true, 'default' => 0]; break;
		case 'count':
			$output = ['type' => 'INT', 'constraint' => '6', 'unsigned' => true, 'default' => 0]; break;
		case 'int_unsigned':
			$output = ['type' => 'INT', 'constraint' => $length, 'unsigned' => true, 'default' => 0]; break;
		case 'epoch':
			$output = ['type' => 'INT', 'constraint' => '12', 'null' => true]; break;
		case 'status':
			$output = ['type' => 'TINYINT', 'constraint' => '1', 'unsigned' => true, 'default' => 0]; break;
		case 'status1':
			$output = ['type' => 'TINYINT', 'constraint' => '1', 'unsigned' => true, 'default' => 1]; break;
		case 'datetime':
			$output = ['type' => 'DATETIME']; break;
		case 'text':
			$output = ['type' => 'TEXT', 'null' => true]; break;
		case 'longtext':
			$output = ['type' => 'LONGTEXT', 'null' => true]; break;
		case 'var191': case 'var63': case 'var31': case 'var15':
			$output = ['type' => 'VARCHAR', 'constraint' => substr($type, 3), 'null' => true]; break;
		case 'unique_name':
			$output = ['type' => 'VARCHAR', 'constraint' => '31', 'unique' => true]; break;
		case 'unique':
			$output = ['type' => 'VARCHAR', 'constraint' => $length, 'unique' => true]; break;
		default:
			$output = ['type' => $type, 'constraint' => $length]; break;
	}
	return (count($params))? array_merge($output, $params) : $output;
}

function pairedArray($args) {

	$out = [];
	foreach($args as $i => $value) {
		if($i%2) { $out[$args[$i-1]] = $value; }
	}
	return $out;
}

function randomArray($n) {

	$out = [];
	for($i = 0; $i < $n; $i++) {
		$out[$i] = chr(rand(33,126));
	}
	return $out;
}
