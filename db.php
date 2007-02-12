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


require_once('code/wfpl/encode.php');
require_once('code/wfpl/format.php');

# db_connect() -- connect to a mysql database
#
# PARAMETERS:
#
#   database: the name of the database you want to connect to. Defaults to the
#   second-to-last part of the domain name. eg for foo.example.com it would be
#   "example".
# 
#   user: username for connecting to the database. Defaults to
#   $GLOBALS['db_username'] or (if that's not set) "www".
# 
#   password: password for connecting to the database. Defaults to
#   $GLOBALS['db_password'] or (if that's not set "".
#
# RETURNS:
#
#   the database connection handle. You'll only need this if you want to have
#   multiple databases open at once.

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
		die("Couldn not access database \"$database\": " . mysql_error($GLOBALS['wfpl_db_handle']));
	}

	return $GLOBALS['wfpl_db_handle'];
}

# Unless you're doing something unusual like an ALTER TABLE don't call this directly
function db_send_query($sql) {
	#echo("Sending query: " . enc_html($sql) . "<br>\n");
	$result = mysql_query($sql, $GLOBALS['wfpl_db_handle']);
	if(!$result) {
		die(enc_html('DATABASE ERROR: ' . mysql_error($GLOBALS['wfpl_db_handle']) . ' in the following query: ' . $sql));
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

# This function does the work, but takes the parameters in an array
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


function db_send_get($table, $columns, $where, $args) {
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
	
	db_insert_ish('INSERT', $table, $columns, $values);
}
# same as above, except uses the "replace" command instead of "insert"
function db_replace($table, $columns, $values) {
	if(!is_array($values)) {
		$values = func_get_args();
		$values = array_slice($values, 2);
	}
	
	db_insert_ish('REPLACE', $table, $columns, $values);
}
	
# return the value mysql made up for the auto_increment field (for the last insert)
function db_auto_id() {
	return mysql_insert_id($GLOBALS['wfpl_db_handle']);
}


# used to implement db_insert() and db_replace()
function db_insert_ish($command, $table, $columns, $values) {

	$sql = '';
	foreach($values as $value) {
		if($sql) $sql .= ',';
		$sql .= '"' . enc_sql($value) . '"';
	}

	$sql = "$command INTO $table ($columns) values($sql)";

	db_send_query($sql);
}

# to be consistant with the syntax of the other db functions, $values can be an
# array, a single value, or multiple parameters.
#
# as usual the where clause stuff is optional, but it will ofcourse update the
# whole table if you leave it off.
#
# examples:
#
# # name everybody Bruce
# db_update('users', 'name', 'Bruce');
#
# # name user #6 Bruce
# db_update('users', 'name', 'Bruce', 'id= %"', 6);
#
# # update the whole bit for user #6
# db_update('users', 'name,email,description', 'Bruce', 'bruce@example.com', 'is a cool guy', 'id= %"', 6);
#
# # update the whole bit for user #6 (passing data as an array)
# $data = array('Bruce', 'bruce@example.com', 'is a cool guy');
# db_update('users', 'name,email,description', $data, 'id= %"', 6);

# The prototype is really something like this:
# db_update(table, columns, values..., where(optional), where_args...(optional
function db_update($table, $columns, $values) {
	$args = func_get_args();
	$args = array_slice($args, 2);
	$columns = explode(',', $columns);
	$num_fields = count($columns);

	if(is_array($values)) {
		$args = array_slice($args, 1);
	} else {
		$values = array_slice($args, 0, $num_fields);
		$args = array_slice($args, $num_fields);
	}

	$sql = '';
	for($i = 0; $i < $num_fields; ++$i) {
		if($sql != '') {
			$sql .= ', ';
		}
		$sql .= $columns[$i] . ' = "' . enc_sql($values[$i]) . '"';
	}


	$sql = "UPDATE $table SET $sql";

	# if there's any more arguments
	if($args) {
		$where = $args[0];
		$args = array_slice($args, 1);

		$sql .= ' WHERE ';
		# any left for where claus arguments?
		if($args) {
			$sql .= _db_printf($where, $args);
		} else {
			$sql .= $where;
		}

	}

	db_send_query($sql);
}

# pass args for printf-style where clause as usual
function db_delete($table, $where = '') {
	$sql = "DELETE FROM $table";
	if($where) {
		$sql .= ' WHERE ';
		$args = func_get_args();
		$args = array_slice($args, 2);
		if($args) {
			$sql .= _db_printf($where, $args);
		} else {
			$sql .= $where;
		}
	}

	db_send_query($sql);
}

?>
