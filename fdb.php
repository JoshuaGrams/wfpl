<?php

#  Copyright (C) 2007 Jason Woofenden
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


# This file contains code to use a web-writeable directory full of files as a
# very simple database.

# Keys are truncated to 32 bytes, made lowercase, and all characters that are
# not alpha/numeric are replaced with underscores. Periods and hyphens are only
# replaced if they are at the begining.

# Data can be either a string or an array.

# To set up the database, make a directory that's writeable by PHP and call
# fdb_set_dir() passing the path to that directory.


require_once('code/wfpl/file.php');
require_once('code/wfpl/binary.php');

# call this to set what directory is used to store the files
function fdb_set_dir($dir) {
	$GLOBALS['fdb_dir'] = $dir;
}

function fdb_get_dir() {
	if(!isset($GLOBALS['fdb_dir'])) {
		die('you must call fdb_set_dir() before calling other functions in code/wfpl/fdb.php');
	}
	return $GLOBALS['fdb_dir'];
}


function fdb_fix_key($key) {
	$key = ereg_replace('[^a-z0-9.-]', '_', strtolower($key));
	$key = ereg_replace('^[-.]', '_', strtolower($key));
	return substr($key, 0, 32);
}


function fdb_get_raw($key) {
	$key = fdb_fix_key($key);
	return read_whole_file_or_false(fdb_get_dir() . "/$key");
}

function fdb_set_raw($key, $data) {
	$key = fdb_fix_key($key);
	write_whole_file(fdb_get_dir() . "/$key", $data);
}

# like fdb_get() except it returns an array even when there's just one element
function fdb_geta($key) {
	$key = fdb_fix_key($key);
	$data = fdb_get_raw($key);
	if($data === false) {
		return false;
	}
	return raw_to_array($data);
}

# returns:
#
# false if the key is not found in the database
#
# an array from the file otherwise
#
# a string if there's one field in that file (use fdb_geta() if you want an
# array in this case too)
function fdb_get($key) {
	$ret = fdb_geta($key);
	if($ret == false) {
		return false;
	}
	if(count($ret) == 1) {
		return $ret[0];
	} else {
		return $ret;
	}
}

# data can be a string or array
function fdb_set($key, $data) {
	$key = fdb_fix_key($key);
	if(!is_array($data)) {
		$data = array($data);
	}
	fdb_set_raw($key, array_to_raw($data));
}

function fdb_delete($key) {
	$key = fdb_fix_key($key);
	$path = fdb_get_dir() . "/$key";
	if(file_exists($path)) {
		return unlink($path);
	}
	return false;
}

?>
