<?php

#  Copyright (C) 2006 Jason Woofenden
#
#  This file is part of wfpl.
#
#  wfpl is free software; you can redistribute it and/or modify it
#  under the terms of the GNU General Public License as published by
#  the Free Software Foundation; either version 2, or (at your option)
#  any later version.
#
#  wfpl is distributed in the hope that it will be useful, but
#  WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
#  General Public License for more details.
#
#  You should have received a copy of the GNU General Public License
#  along with wfpl; see the file COPYING.  If not, write to the
#  Free Software Foundation, Inc., 59 Temple Place - Suite 330, Boston,
#  MA 02111-1307, USA.


# This file writes the code for you (sql, php, html, email) to handle a form.

require_once('code/wfpl/template.php');
require_once('code/wfpl/tar.php');

# see code/wfpl/metaform/template.html for the html templates for these elements
$GLOBALS['types'] = array(
#    type                  input          format        sql     
	'name' =>       array('textbox',     'oneline',    'varchar(200)'),
	'textbox' =>    array('textbox',     'oneline',    'varchar(200)'),
	'int' =>        array('textbox',     'int',        'varchar(100)'),
	'email' =>      array('textbox',     'email',      'varchar(100)'),
	'phone' =>      array('textbox',     'phone',      'varchar(32)'),
	'money' =>      array('textbox',     'money',      'varchar(32)'),
	'dollars' =>    array('textbox',     'dollars',    'varchar(32)'),
	'url' =>        array('textbox',     'url',        'varchar(200)'),
	'hidden' =>     array('hidden',      'unix',       'varchar(200)'),
	'password' =>   array('password',    'oneline',    'varchar(200)'),
	'textarea' =>   array('textarea',    'unix',       'text'),
	'pulldown' =>   array('pulldown',    'options',    'int'),
	'checkbox' =>   array('checkbox',    'yesno',      'int'),
	'yesno' =>      array('checkbox',    'yesno',      'int'),
	'submit' =>     array('submit',      'oneline',    'n/a')
);

if(isset($_REQUEST['form_name'])) {
	$GLOBALS['form_name'] = ereg_replace('[^a-z0-9_-]', '', $_REQUEST['form_name']);
} else {
	$GLOBALS['form_name'] = 'some_form';
}

if(isset($_REQUEST['fields'])) {
	if(isset($_REQUEST['view_sql'])) {
		view_sql();
		exit();
	} elseif(isset($_REQUEST['view_php'])) {
		view_php();
		exit();
	} elseif(isset($_REQUEST['view_template'])) {
		view_template();
		exit();
	} elseif(isset($_REQUEST['view_email'])) {
		view_email();
		exit();
	} elseif(isset($_REQUEST['download_tar'])) {
		download_tar();
		exit();
	} elseif(isset($_REQUEST['preview'])) {
		preview();
		exit();
	} elseif(isset($_REQUEST['edit'])) {
		tem_set('fields', $_REQUEST['fields']);
		tem_set('form_name', $GLOBALS['form_name']);
		# fall through
	} else {
		die("Sorry... couldn't tell which button you pressed");
	}
}

set_form_action();
tem_output('code/wfpl/metaform/main.html');
exit();


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

function set_form_action() {
	$action = ereg_replace('.*/', '', $_SERVER['REQUEST_URI']);
	if($action == '') $action = './';
	tem_set('form_action', $action);
}

# perfect HTTP headers for viewing created files
function view_headers() {
	header('Content-type: text/plain');
}
	



function make_sql() {
	$tem = new tem();
	$tem->load('code/wfpl/metaform/template.sql');
	$tem->set('form_name', $GLOBALS['form_name']);
	$fields = get_fields();
	foreach($fields as $field) {
		list($name, $type, $input, $format, $sql) = $field;
		if($sql != 'n/a') {
			$tem->set('name', $name);
			$tem->set('type', $sql);
			if($sql == 'int') {
				$tem->set('default', '0');
			} else {
				$tem->set('default', '""');
			}
			$tem->sub('column');
		}
	}
	view_headers();
	return $tem->run();
}

function view_sql() {
	view_headers();
	echo make_sql();
}
	

# pass false if you want to exclude the <head> and <body> tag etc.
function make_template($whole_file = true) {
	$tem = new tem();
	$tem->load('code/wfpl/metaform/template.html');
	$tem->set('form_name', $GLOBALS['form_name']);
	$fields = get_fields();
	foreach($fields as $field) {
		list($name, $type, $input, $format, $sql) = $field;
		$tem->set('name', $name);
		$tem->set('caption', $name); # fixme
		$tem->sub($input);
		if($input != 'hidden') {
			$tem->sub('row');
		}
	}
	$tem->set('name', 'save');
	$tem->set('caption', 'Save');
	$tem->sub('submit');
	$tem->sub('row');
	$tem->sub('form');
	if($whole_file) {
		return $tem->run();
	} else {
		return $tem->get('form');
	}
}

function view_template() {
	view_headers();
	echo make_template();
}


function make_php() {
	$tem = new tem();
	$tem->load('code/wfpl/metaform/template.php');
	$tem->set('form_name', $GLOBALS['form_name']);
	$fields = get_fields();
	$db_fields = '';
	$php_fields = '';
	$always_field = false;
	foreach($fields as $field) {
		list($name, $type, $input, $format, $sql) = $field;
		if($input != 'submit') {
			$tem->set('format', $format);
			$tem->set('name', $name);
			$tem->set('db_field', ''); # we don't want to use the value from last time
			if($sql != 'n/a') {
				if($db_fields != '') $db_fields .= ',';
				$db_fields .= $name;
				if($php_fields != '') $php_fields .= ', ';
				$php_fields .= '$' . $name;
			}
			$tem->sub('formats');
			if(!$always_field and $input != 'checkbox' and $input != 'radio') {
				$always_field = $name;
			}
		}
	}
	# always_field is a form field that always submits (unlike say, checkboxes). It's used to detect if the form has submitted or not.
	$tem->set('always_field', $always_field);
	$tem->set('db_fields', $db_fields);
	$tem->set('php_fields', $php_fields);
	return $tem->run();
}

function view_php() {
	view_headers();
	echo make_php();
}


function make_email() {
	$tem = new tem();
	$tem->load('code/wfpl/metaform/template.email.txt');
	$tem->set('form_name', $GLOBALS['form_name']);
	$fields = get_fields();
	foreach($fields as $field) {
		list($name, $type, $input, $format, $sql) = $field;
		$tem->set('name', $name);
		$tem->set('caption', $name); # fixme
		$tem->sub('fields');
	}
	return $tem->run();
}

function view_email() {
	view_headers();
	echo make_email();
}


function preview() {
	$tem = new tem();
	$tem->load('code/wfpl/metaform/preview.html');
	$tem->set('form_name', $GLOBALS['form_name']);
	$tem->set('fields', $_REQUEST['fields']);
	$preview_tem = new tem();
	$preview = $preview_tem->run(make_template(false));
	unset($preview_tem);
	$tem->set('preview', $preview);
	set_form_action();
	$tem->output();
}

function download_tar() {
	$name = $GLOBALS['form_name'];
	$data = array(
		"$name.html" => make_template(),
		"$name.sql" => make_sql(),
		"$name.email.txt" => make_email(),
		"$name.php" => make_php());
	make_tar($name, $data);
}

?>
