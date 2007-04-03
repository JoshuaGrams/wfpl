<?php

# Copyright 2007 Jason Woofenden   PUBLIC DOMAIN


# To run this file:
#
# 1) make a link to it from your website directory which has the code/ directory in it
#
# 2) edit code/wfpl/test/session_test_db_connect.php to connect to your TESTING database
#
# 3) add the wfpl_session tables to your TESTING database:
#
# create table wfpl_sessions (id int unique auto_increment, session_key varchar(16), length int, expires int);
# create table wfpl_session_data (id int unique auto_increment, session_id int, name varchar(100), value text);

##############
# WARNING: this code deletes all wfpl sessions. Do not use on a live site's database
##############

require_once('code/wfpl/test/session_test_db_connect.php');
require_once('code/wfpl/session.php');

function session_dump($message) {
	$ses = db_get_rows('wfpl_sessions', 'id,session_key,length,expires');
	if($ses) foreach($ses as $row) {
		list($id, $session_key, $length, $expires) = $row;
		tem_set('id', $id);
		tem_set('session_key', $session_key);
		tem_set('length', $length);
		tem_set('expires', $expires);
		tem_sub('wfpl_sessions_row');
	}

	$data = db_get_rows('wfpl_session_data', 'id,session_id,name,value');
	if($data) foreach($data as $row) {
		list($id, $session_id, $name, $value) = $row;
		tem_set('id', $id);
		tem_set('session_id', $session_id);
		tem_set('name', $name);
		tem_set('value', $value);
		tem_sub('wfpl_session_data_row');
	}

	tem_set('message', $message);
	tem_sub('block');
}


function session_test() {
	tem_load('code/wfpl/test/session_test.html');

	db_delete('wfpl_sessions');
	db_delete('wfpl_session_data');
	session_dump('Clean slate');

	session_new();
	session_dump('new session');

	session_set('username', 'jason');
	session_dump('username jason');

	session_set('username', 'phil');
	session_dump('overwrote username as phil');

	$old = $GLOBALS['session_id'];

	session_new();
	session_dump('new session');

	session_set('username', 'jason');
	session_set('bamph', 'foo');
	session_dump('set username=jason and bamph=foo in new session');

	session_clear('username');
	session_dump('cleared username in new session');

	_kill_session($old);
	session_dump('killed old session');

	kill_session();
	session_dump('kill_session()');

	tem_output();
}

?>
