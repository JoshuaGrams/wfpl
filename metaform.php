<?php

require_once('code/wfpl/template.php');

# see code/wfpl/metaform/template.html for the html templates for these elements
$GLOBALS['types'] = array(
#    type                  input          format        sql     
	'name' =>       array('textbox',     'oneline',    'varchar(200)'),
	'textbox' =>    array('textbox',     'oneline',    'varchar(200)'),
	'email' =>      array('textbox',     'email',      'varchar(100)'),
	'phone' =>      array('textbox',     'phone',      'varchar(32)'),
	'money' =>      array('textbox',     'money',      'varchar(32)'),
	'dollars' =>    array('textbox',     'dollars',    'varchar(32)'),
	'url' =>        array('textbox',     'url',        'varchar(200)'),
	'textarea' =>   array('textarea',    'unix',       'text'),
	'pulldown' =>   array('pulldown',    'options',    'int'),
	'checkbox' =>   array('checkbox',    'yesno',   'int'),
	'yesno' =>      array('checkbox',    'yesno',   'int'),
	'submit' =>     array('submit',      'oneline',    'n/a')
);

if(isset($_REQUEST['form_name'])) {
	$GLOBALS['form_name'] = $_REQUEST['form_name'];
} else {
	$GLOBALS['form_name'] = 'some_form';
}

if(isset($_REQUEST['fields'])) {
	if(isset($_REQUEST['download_sql'])) {
		download_sql();
		exit();
	} elseif(isset($_REQUEST['download_php'])) {
		download_php();
		exit();
	} elseif(isset($_REQUEST['download_template'])) {
		download_template();
		exit();
	} elseif(isset($_REQUEST['download_email'])) {
		download_email();
		exit();
	} else {
		tem_set('message', "Sorry... couldn't tell which button you pressed");
		# fall through
	}
} else {
	tem_output('code/wfpl/metaform/main.html');
}

function field_input($type)  { return $GLOBALS['types'][$type][0]; }
function field_format($type) { return $GLOBALS['types'][$type][1]; }
function field_sql($type)    { return $GLOBALS['types'][$type][2]; }

function get_fields() {
	$fields_str = unix_newlines($_REQUEST['fields']);
	$ret = array();
	$fields_str = rtrim($fields_str);
	$fields = split("\n", $fields_str);
	foreach($fields as $field) {
		list($name, $type, $options) = split('  *', $field);
		if($options) $options = split(',', $options);
		if(!$type) $type = $name;
		$input = field_input($type);
		$format = field_format($type);
		$sql = field_sql($type);
		$ret[] = array($name, $type, $input, $format, $sql, $options);
	}
	return $ret;
}

function download_headers() {
	header('Content-type: application/octet-stream');
	header('Content-disposition: download'); # is this correct? does it do anything?
}
	



function download_sql() {
	tem_load('code/wfpl/metaform/template.sql');
	tem_set('form_name', $GLOBALS['form_name']);
	$fields = get_fields();
	foreach($fields as $field) {
		list($name, $type, $input, $format, $sql) = $field;
		if($sql != 'n/a') {
			tem_set('name', $name);
			tem_set('type', $sql);
			if($sql == 'int') {
				tem_set('default', '0');
			} else {
				tem_set('default', '""');
			}
			tem_sub('column');
		}
	}
	download_headers();
	tem_output();
}
	

function download_template() {
	tem_load('code/wfpl/metaform/template.html');
	tem_set('form_name', $GLOBALS['form_name']);
	$fields = get_fields();
	foreach($fields as $field) {
		list($name, $type, $input, $format, $sql) = $field;
		tem_set('name', $name);
		tem_set('caption', $name); # fixme
		tem_sub($input);
	}
	tem_set('name', 'save');
	tem_set('caption', 'Save');
	tem_sub('submit');
	download_headers();
	tem_output();
}


function download_php() {
	tem_load('code/wfpl/metaform/template.php');
	tem_set('form_name', $GLOBALS['form_name']);
	$fields = get_fields();
	$db_fields = '';
	$always_field = false;
	foreach($fields as $field) {
		list($name, $type, $input, $format, $sql) = $field;
		if($input != 'submit') {
			tem_set('format', $format);
			tem_set('name', $name);
			tem_set('db_field', ''); # we don't want to use the value from last time
			if($sql != 'n/a') {
				tem_sub('db_field');
				if($db_fields != '') $db_fields .= ',';
				$db_fields .= $name;
			}
			tem_sub('formats');
			if(!$always_field and $input != 'checkbox' and $input != 'radio') {
				$always_field = $name;
			}
		}
	}
	# always_field is a form field that always submits (unlike say, checkboxes). It's used to detect if the form has submitted or not.
	tem_set('always_field', $always_field);
	tem_set('db_fields', $db_fields);
	download_headers();
	tem_output();
}


function download_email() {
	tem_load('code/wfpl/metaform/template.email.txt');
	tem_set('form_name', $GLOBALS['form_name']);
	$fields = get_fields();
	foreach($fields as $field) {
		list($name, $type, $input, $format, $sql) = $field;
		tem_set('name', $name);
		tem_set('caption', $name); # fixme
		tem_sub('fields');
	}
	download_headers();
	tem_output();
}

?>
