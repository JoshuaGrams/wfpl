<?php

#  Copyright (C) 2006 Jason Woofenden
#
#  This file is part of wfpl.
#
#  wfpl is free software; you can redistribute it and/or modify it under the
#  terms of the GNU Lesser General Public License as published by the Free
#  Software Foundation; either version 2.1 of the License, or (at your option)
#  any later version.
#
#  wfpl is distributed in the hope that it will be useful, but WITHOUT ANY
#  WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
#  FOR A PARTICULAR PURPOSE.  See the GNU Lesser General Public License for
#  more details.
#
#  You should have received a copy of the GNU Lesser General Public License
#  along with wfpl; if not, write to the Free Software Foundation, Inc., 51
#  Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA


# This file writes the code for you (sql, php, html, email) to handle a form.

require_once('code/wfpl/template.php');
require_once('code/wfpl/http.php');
require_once('code/wfpl/tar.php');
require_once('code/wfpl/format.php');

# see code/wfpl/metaform/template.html for the html templates for these elements
$GLOBALS['types'] = array(
#    type                  input          format        sql     
	'varname' =>    array('textbox',     'varname',    'varchar(50)'),
	'name' =>       array('textbox',     'oneline',    'varchar(200)'),
	'textbox' =>    array('textbox',     'oneline',    'varchar(200)'),
	'int' =>        array('textbox',     'int',        'int'),
	'decimal' =>    array('textbox',     'decimal',    'decimal(12,12)'),
	'bigint' =>     array('textbox',     'int',        'varchar(100)'), # up to 100 digits, stored as a string
	'zip' =>        array('textbox',     'zip',        'varchar(20)'),
	'email' =>      array('textbox',     'email',      'varchar(100)'),
	'phone' =>      array('textbox',     'phone',      'varchar(32)'),
	'state' =>      array('states',      'oneline',    'varchar(2)'),
	'money' =>      array('textbox',     'money',      'varchar(32)'),
	'date' =>       array('textbox',     'mdy_to_ymd', 'char(10)'),
	'dollars' =>    array('textbox',     'dollars',    'varchar(32)'),
	'url' =>        array('textbox',     'url',        'varchar(200)'),
	'hidden' =>     array('hidden',      'unix',       'varchar(200)'),
	'password' =>   array('password',    'oneline',    'varchar(200)'),
	'textarea' =>   array('textarea',    'unix',       'text'),
	'html' =>       array('html',        'unix',       'text'),
	'pulldown' =>   array('pulldown',    'options',    'varchar(100)'),
	'radio' =>      array('radio',       'oneline',    'varchar(200)'),
	'leftcheck' =>  array('leftcheck',   'yesno',      'varchar(3)'),
	'checkbox' =>   array('checkbox',    'yesno',      'varchar(3)'),
	'yesno' =>      array('checkbox',    'yesno',      'varchar(3)'),
	'delete' =>     array('checkbox',    'yesno',      'n/a'),
	'image' =>      array('image',       'oneline',    'varchar(200)'),
	'submit' =>     array('submit',      'oneline',    'n/a')
);

function list_available_types() {
	$types = '';
	foreach($GLOBALS['types'] as $key => $value) {
		if($types) {
			$types .= ', ';
		}
		$types .= $key;
	}
	tem_set('available_types', $types);
}


function metaform() {
	if(isset($_REQUEST['form_name'])) {
		$GLOBALS['form_name'] = ereg_replace('[^a-z0-9_-]', '', $_REQUEST['form_name']);
		$GLOBALS['opt_email'] = format_yesno($_REQUEST['opt_email']);
		tem_set('opt_email', $GLOBALS['opt_email']);
		$GLOBALS['opt_db'] = format_yesno($_REQUEST['opt_db']);
		tem_set('opt_db', $GLOBALS['opt_db']);
		$GLOBALS['opt_listing'] = format_yesno($_REQUEST['opt_listing']);
		tem_set('opt_listing', $GLOBALS['opt_listing']);
		$GLOBALS['opt_http_pass'] = format_yesno($_REQUEST['opt_http_pass']);
		tem_set('opt_http_pass', $GLOBALS['opt_http_pass']);
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
		} elseif(isset($_REQUEST['view_html'])) {
			view_html();
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
	list_available_types();
	tem_output('code/wfpl/metaform/main.html');
}


function field_input($type)  { return $GLOBALS['types'][$type][0]; }
function field_format($type) { return $GLOBALS['types'][$type][1]; }
function field_sql($type)    { return $GLOBALS['types'][$type][2]; }

function get_fields() {
	# no sense in doing all this so many times
	if(isset($GLOBALS['gotten_fields'])) {
		return $GLOBALS['gotten_fields'];
	}

	$fields_str = unix_newlines($_REQUEST['fields']);
	$GLOBALS['gotten_fields'] = array();
	$fields_str = rtrim($fields_str);
	$fields = split("\n", $fields_str);
	foreach($fields as $field) {
		list($name, $type, $options) = split('  *', $field);
		if($options) $options = split(',', $options);
		if(!$type) $type = $name;
		$input = field_input($type);
		$format = field_format($type);
		$sql = field_sql($type);
		$GLOBALS['gotten_fields'][] = array($name, $type, $input, $format, $sql, $options);
	}
	return $GLOBALS['gotten_fields'];
}

# this one, that you're using to create forms
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
			} elseif($format == 'yesno') {
				$tem->set('default', '"No"');
			} else {
				$tem->set('default', '""');
			}
			$tem->show('column');
		}
	}
	view_headers();
	return $tem->run();
}

function view_sql() {
	view_headers();
	echo make_sql();
}

# always_field is a form field that always submits (unlike say, checkboxes). It's used to detect if the form has submitted or not.
function find_always_field($fields) {
	foreach($fields as $field) {
		list($name, $type, $input, $format, $sql) = $field;
		if($input != 'submit' && $input != 'checkbox' && $input != 'radio') {
			return $name;
		}
	}

	return false;
}
	
	

# pass false if you want to exclude the <head> and <body> tag etc.
function make_html($whole_file = true) {
	$uploads_output_already = false;
	$has_html_editors = false;
	$tem = new tem();
	$tem->load('code/wfpl/metaform/template.html');
	$tem->set('form_name', $GLOBALS['form_name']);
	$fields = get_fields();
	$tem->set('always_field', find_always_field($fields));
	foreach($fields as $field) {
		list($name, $type, $input, $format, $sql) = $field;
		$tem->set('name', $name);
		$tem->set('caption', format_caption($name));
		$tem->show($input);
		if($input != 'hidden') {
			$tem->show('row');
		}
		if($input == 'image' && !$uploads_output_already) {
			$tem->show('uploads');
			$tem->set('enctype_attr', '" enctype="multipart/form-data');
			$uploads_output_already = true;
		} elseif($input == 'html') {
			$has_html_editors = true;
			$tem->set('html_field_name', $name);
			$tem->show('replace_textarea');
		}
	}

	if($GLOBALS['opt_db'] == 'Yes') {
		$tem->show('opt_db_1');
		$tem->show('opt_db_2');
	} else {
		$tem->show('opt_db_1_else');
	}

	if($GLOBALS['opt_listing'] == 'Yes') {
		$tem->show('opt_listing_1');
	} else {
		$tem->show('opt_listing_1_else');
	}

	if($GLOBALS['opt_email'] == 'Yes' && $GLOBALS['opt_db'] != 'Yes') {
		$tem->set('name', 'send');
		$tem->set('caption', 'Send');
	} else {
		$tem->set('name', 'save');
		$tem->set('caption', 'Save');
	}
	$tem->show('submit');
	$tem->show('row');

	$tem->show('form');

	if($has_html_editors) {
		$tem->show('html_editor_headers');
	}

	if($whole_file) {
		return $tem->run();
	} else {
		return $tem->get('form');
	}
}

function view_html() {
	view_headers();
	echo make_html();
}


function make_php() {
	$tem = new tem();
	$tem->load('code/wfpl/metaform/template.php');
	$tem->set('form_name', $GLOBALS['form_name']);
	$fields = get_fields();
	$db_fields = '';
	$php_fields = '';
	$always_field = find_always_field($fields);
	$image_included_yet = false;
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
			if($input == 'image') {
				$tem->show('image_upload');
				$tem->show('image_db');
				if(!$image_included_yet) {
					$tem->show('image_include');
					$tem->show('upload_max');
					$tem->show('upload_settings');
					$image_included_yet = true;
				}
			} else {
				if($input == 'pulldown') {
					$tem->show('pulldowns');
					$tem->show('pulldown_format_extra');
				}
				$tem->show('formats');
			}
			$tem->show('tem_sets');
		}
	}

	$tem->set('always_field', $always_field);
	$tem->set('db_fields', $db_fields);
	$tem->set('php_fields', $php_fields);
	$tem->set('metaform_url', edit_url());
	if($GLOBALS['opt_listing'] == 'Yes') {
		$tem->show('opt_listing_1');
		$tem->show('opt_listing_2');
		$tem->show('opt_listing_4');
	} else {
		$tem->show('opt_listing_4_else');
	}
	if($GLOBALS['opt_db'] == 'Yes') {
		$tem->show('opt_db_1');
		$tem->show('opt_db_2');
		$tem->show('opt_db_3');
		$tem->show('opt_db_4');
		$tem->show('opt_db_5');
	}
	if($GLOBALS['opt_email'] == 'Yes') {
		$tem->show('opt_email_1');
		$tem->show('opt_email_2');
	}
	if($GLOBALS['opt_http_pass'] == 'Yes') {
		$tem->show('opt_http_pass_1');
		$tem->show('opt_http_pass_2');
	}
	return $tem->run();
}

# make a URL for the edit page with all the fields filled in
function edit_url() {
	$url = this_url();
	$url = ereg_replace('view_php=[^&]*', 'edit=yes', $url);
	$url = ereg_replace('download_tar=[^&]*', 'edit=yes', $url);
	$url = ereg_replace('/[a-z0-9_.]*\?', '/?', $url);
	$url = str_replace('jasonwoof.l', 'jasonwoof.com', $url); # so that code generated on Jason's home computer will display a publically accessible link.
	return $url;
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
		if($type == 'textarea') {
			$tem->show('multi_line');
		} else {
			$tem->show('normal');
		}
		$tem->show('fields');
	}
	return $tem->run();
}

function make_htaccess() {
	$tem = new tem();
	$tem->set('form', $GLOBALS['form_name']);
	return $tem->run('code/wfpl/metaform/htaccess');
}

function view_email() {
	view_headers();
	echo make_email();
}


function preview() {
	tem_load('code/wfpl/metaform/preview.html');
	tem_set('form_name', $GLOBALS['form_name']);
	tem_set('fields', $_REQUEST['fields']);
	$preview_tem = new tem();
	$preview_tem->load_str(make_html(false));
	if($GLOBALS['opt_db'] == 'Yes') {
		$preview_tem->show('new_msg');
	}
	$fields = get_fields();
	foreach($fields as $field) {
		list($name, $type, $input, $format, $sql) = $field;
		if($type == 'pulldown') {
			pulldown($name, array('option 1', 'option 2', 'option 3'));
		}
	}
	$preview = $preview_tem->run();
	unset($preview_tem);
	tem_set('preview', $preview);
	set_form_action();
	tem_output();
}

function download_tar() {
	$name = $GLOBALS['form_name'];
	$data = array(
		".htaccess" => make_htaccess(),
		"run.php ->" => 'code/wfpl/run.php',
		"style.css" => read_whole_file('code/wfpl/metaform/style.css'),
		"$name.html" => make_html(),
		"$name.php" => make_php());
	if($GLOBALS['opt_db'] == 'Yes') {
		$data["$name.sql"] = make_sql();
	}
	if($GLOBALS['opt_email'] == 'Yes') {
		$data["$name.email.txt"] = make_email();
	}
	make_wfpl_tar($name, $data);
}


metaform();
exit();

?>
