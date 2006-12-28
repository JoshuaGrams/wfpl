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


require_once('code/wfpl/encode.php');
require_once('code/wfpl/format.php');

# db_connect() parameters:
#
# database: the name of the database you want to connect to. Defaults to the
# second-to-last part of the domain name. eg for foo.example.com it would be
# "example".
#
# user: username for connecting to the database. Defaults to
# $GLOBALS['db_username'] or (if that's not set) "www".
#
# password: password for connecting to the database. Defaults to
# $GLOBALS['db_password'] or (if that's not set "".
#
# RETURNS: the database connection handle. You'll only need this if you
# want to have multiple databases open at once.

function db_connect($database = 'auto', $user = 'auto', $pass = 'auto', $host = 'localhost') {
	if($database == 'auto') {
		if(isset($GLOBALS['db_name'])) {
			$database = $GLOBALS['db_name'];
		} else {
			$host = $_SERVER['SERVER_NAME'];
			$host = explode('.', $host);
			array_pop($host);
			$database = array_pop($host);
			unset($host);
		}
	}

	if($user == 'auto') {
		if(isset($GLOBALS['db_username'])) {
			$user = $GLOBALS['db_username'];
		} else {
			$user = 'www';
		}
	}

	if($pass == 'auto') {
		if(isset($GLOBALS['db_password'])) {
			$pass = $GLOBALS['db_password'];
		} else {
			$pass = '';
		}
	}

	$GLOBALS['wfpl_db_handle'] = mysql_connect($host, $user, $pass);
	if(!$GLOBALS['wfpl_db_handle']) {
		die('Could not connect to the database: ' . mysql_error());
	}

	if(!mysql_select_db($database, $GLOBALS['wfpl_db_handle'])) {
		die("Couldn not access database \"$database\": " . mysql_error());
	}

	return $GLOBALS['wfpl_db_handle'];
}

# Unless you're doing something unusual like an ALTER TABLE don't call this directly
function db_send_query($sql) {
	#echo("Sending query: " . enc_html($sql) . "<br>\n");
	$result = mysql_query($sql);
	if(!$result) {
		die(enc_html('DATABASE ERROR: ' . mysql_error() . ' in the following query: ' . $sql));
	}

	return $result;
}

# All select queries use this to generate the where clause, so they can work
# like printf. Currently three % codes are supported:
#
# %%  put a % in the output
# %i  put an integer in the output (strips non-numeric digits, and puts in 0 if blank)
# %"  output double quotes, surrounding the variable which is encoded to be in there.
# %s  output encoded to be in double quotes, but don't output the quotes
#
# complex example: db_get_rows('mytable', 'id', 'name=%" or company like "%%%s%%"', $name, $company_partial);

function db_printf($str) {
	$args = func_get_args();
	$args = array_slice($args, 1);
	_db_printf($str, $args);
}

# This function does the work, but takes the parameters in an array, and backwards.
function _db_printf($str, $args) {
	$args = array_reverse($args); # because array_pop() takes from the end
	$out = '';
	while($str) {
		$pos = strpos($str, '%');
		if($pos === false) { # not found
			# we hit the end.
			return $out . $str;
		}
		# move everything up to (but not including) % to the output
		$out .= substr($str, 0, $pos);

		# grab the character after the %
		$chr = substr($str, $pos + 1, 1);

		# remove the stuff we've read from input
		$str = substr($str, $pos + 2);

		if($chr == '"') {
			$out .= '"' . enc_sql(array_pop($args)) . '"';
		} elseif($chr == 'i') {
			$int = format_int(array_pop($args));
			if($int == '') $int = '0';
			$out .= $int;
		} else {
			$out .= $chr;
		}
	}

	return $out;
}


function db_send_get($table, $columns, $where = '', $args) {
	$sql = "SELECT $columns FROM $table";
	if($where) {
		$sql .= ' WHERE ' . _db_printf($where, $args);
	}

	return db_send_query($sql);
}


function db_get_rows($table, $columns, $where = '') {
	$args = func_get_args();
	$args = array_slice($args, 3);
	$result = db_send_get($table, $columns, $where, $args);

	$rows = array();
	while($row = mysql_fetch_row($result)) {
		$rows[] = $row;
	}

	mysql_free_result($result);

	return $rows;
}

function db_get_column($table, $columns, $where = '') {
	$args = func_get_args();
	$args = array_slice($args, 3);
	$result = db_send_get($table, $columns, $where, $args);

	$column = array();
	while($row = mysql_fetch_row($result)) {
		$column[] = $row[0];
	}

	mysql_free_result($result);

	return $column;
}

function db_get_row($table, $columns, $where = '') {
	$args = func_get_args();
	$args = array_slice($args, 3);
	$result = db_send_get($table, $columns, $where, $args);

	$row = mysql_fetch_row($result);

	mysql_free_result($result);

	return $row;
}

function db_get_value($table, $columns, $where = '') {
	$args = func_get_args();
	$args = array_slice($args, 3);
	$result = db_send_get($table, $columns, $where, $args);

	$value = mysql_fetch_row($result);
	if($value !== false) {
		$value = $value[0];
	}

	mysql_free_result($result);

	return $value;
}

# call either of these ways:
#
# db_insert('people', 'name,company', 'jason', 'widgets ltd');
# or
# db_insert('people', 'name,company', array('jason', 'widgets ltd'));
function db_insert($table, $columns, $values) {
	if(!is_array($values)) {
		$values = func_get_args();
		$values = array_slice($values, 2);
	}

	$sql = '';
	foreach($values as $value) {
		if($sql) $sql .= ',';
		$sql .= '"' . enc_sql($value) . '"';
	}

	$sql = "INSERT INTO $table ($columns) values($sql)";

	db_send_query($sql);
}

?>
