<?php

function loadTemplate($template, $theme = null, $data = null) {

	$CI =& get_instance();
	if(!$theme) { $theme = $CI->theme; }
	$CI->load->view(templatePath($template, $theme), $data);
}

function apiGetOrderBy($sort = [], $table = '', $alias = []) {

	$orderBy = [];
	$dirxn = '';
	foreach($sort as $val) {

		# if first character is '+' then ascending, else descending
		$dirxn = (substr($val, 0, 1) == '+')? 'ASC':'DESC';

		# unset first character and append table name
		$val = substr($val, 1);
		if(isset($alias[$val])) { $val = $alias[$val]; }
		if($table) { $val = $table.'.'.$val; }
		$orderBy[$val] = $dirxn;
	}

	return $orderBy;
}

function apiWhereByType($param, $data, $orderByField = false) {

	if(!isset($param['where'])) { $param['where'] = []; }
	if(!isset($param['where_in'])) { $param['where_in'] = []; }

	foreach ($data as $key => $value) {
		switch(gettype($value)) {
			case 'array':
				$param['where_in'][$key] = $value;
				if($orderByField) { $param['order_by_field'] = [$key => $value]; }
				break;
			case 'string': case 'integer':
				$param['where'][$key] = $value;
				break;
		}
	}
	return $param;
}

function apiGetOffset($param, $limit = 20) {

	$out = [(int) $limit, 0];

	# if start is sent, then use it to set offset
	if(isset($param['start'])) {
		$param['start'] = explode(',', $param['start']);
		$out[1] = (int) $param['start'][0];
		if(isset($param['start'][1])) { $out[0] = $param['start'][1]; }
	}

	# if page number is sent, then calculate offset accordingly
	if(isset($param['page'])) {
		$out[1] = (int) $param['page'];
		$out[1] = ($out[0])*($out[1] - 1);
	}

	return $out;
}
