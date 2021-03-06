<?php

#  Copyright (C) 2006 Jason Woofenden
#
#  This program is free software: you can redistribute it and/or modify
#  it under the terms of the GNU General Public License as published by
#  the Free Software Foundation, either version 3 of the License, or
#  (at your option) any later version.
#  
#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU General Public License for more details.
#  
#  You should have received a copy of the GNU General Public License
#  along with this program.  If not, see <http://www.gnu.org/licenses/>.


# you'll need these database tables:
# create table wfpl_sessions (id int unique auto_increment, session_key varchar(16), length int, expires int);
# create table wfpl_session_data (id int unique auto_increment, session_id int, name varchar(100), value text);
# run this command to install/clear the tables:
#   mysql DATABASE_NAME < code/wfpl/examples/session.sql
# note: you may need these parameters for mysql:  -u USERNAME -p

# GLOSSARY
#
# session_key  16 digit string identifying the session
# session_id   integer id of the record in the "sessions" table of the database
# UNTIL_CLOSE  a constant passed as session length to indicate "until browser window closes"


# session_id is kept in $GLOBALS
# session_key is sent as a cookie, and thus appears in $_REQUEST. The clean version is in $GLOBALS

# generate a new random 16-character string
function session_generate_key() {
	$character_set = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $id = "                ";

	# PHP 4.2.0 and up seed the random number generator for you.
	# Lets hope that it seeds with something harder to guess than the clock.
    for($i = 0; $i < 16; ++$i) {
        $id{$i} = $character_set{mt_rand(0, 61)};
    }

    return $id;
}

# track this user with a session cookie (ie a cookie that goes away when the
# user closes the browser). The timestamp is how long to track the session in
# the database. Defaults to one day.
function session_new($length = 86400) {
	$session_key = session_generate_key();

	db_insert('wfpl_sessions', 'session_key,length', $session_key, $length);
	$GLOBALS['session_id'] = db_auto_id();
	$GLOBALS['session_key'] = $session_key;
	$_REQUEST['session_key'] = $session_key; #just in case someone calls session_exists() after session_new()
	session_touch($length);
	return $GLOBALS['session_key'];
}

# call to renew the timeout for the session.
# assumes there's a session. call init_session() if you'd like one auto-create one if not found.
function session_touch($length = false) {
	if(!$length) {
		$length = db_get_value('wfpl_sessions', 'length', 'where id=%i', $GLOBALS['session_id']);
	}
	$expires = time() + $length;

	header('Set-Cookie: session_key=' . $GLOBALS['session_key']);

	db_update('wfpl_sessions', 'expires', $expires, 'where id=%i', $GLOBALS['session_id']);
}

# delete the current session
function kill_session() {
	if(!session_exists()) {
	    return;
	}
	_kill_session($GLOBALS['session_id']);
}

# for internal use. use kill_session() above
function _kill_session($id) {
	db_delete('wfpl_session_data', 'where session_id=%i', $id);
	db_delete('wfpl_sessions', 'where id=%i', $id);
}

# delete expired sessions from database
function session_purge_old() {
	$now = time();
	$expired_sessions = db_get_column('wfpl_sessions', 'id', 'where expires < %i', $now);
	if($expired_sessions) foreach($expired_sessions as $expired_session) {
		_kill_session($expired_session);
	}
}

# return true if a session exists
function session_exists() {
	if(!isset($_REQUEST['session_key'])) {
		return false;
	}

	if(isset($GLOBALS['session_id'])) {
		return true;
	}

	$session_key = ereg_replace('[^a-zA-Z0-9]', '', $_REQUEST['session_key']);

	if(!strlen($session_key) == 16) {
		return false;
	}

	$GLOBALS['session_key'] = $session_key;

	session_purge_old();
	$id = db_get_value('wfpl_sessions', 'id', 'where session_key=%"', $session_key);
	if($id === false) {
		return false;
	}

	$GLOBALS['session_id'] = $id;
	return true;
}

# return username if a session exists and is authenticated
function session_exists_and_authed() {
	if(!session_exists()) {
		return false;
	}

	return session_get('auth_username');
}


# find existing session, or make one
function init_session() {
	if(!session_exists()) {
		session_new();
	}
}

# save a variable into the session
function session_set($name, $value) {
	session_clear($name);
	db_insert('wfpl_session_data', 'session_id,name,value', $GLOBALS['session_id'], $name, $value);
}

# remove variable from the session
function session_clear($name) {
	db_delete('wfpl_session_data', 'where session_id=%i && name=%"', $GLOBALS['session_id'], $name);
}

# get a variable into the session
function session_get($name) {
	return db_get_value('wfpl_session_data', 'value', 'where session_id=%i && name=%"', $GLOBALS['session_id'], $name);
}

?>
