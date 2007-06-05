<?php

#  Copyright (C) 2007 Jason Woofenden
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


# This file contains code to use a web-writeable directory full of files as a
# very simple database.

# Keys are truncated to 32 bytes, made lowercase, and all characters that are
# not alpha/numeric are replaced with underscores. Periods and hyphens are only
# replaced if they are at the begining.

# Data can be either a string or an array.

# To set up the database, make a directory that's writeable by PHP and call
# fdb_set_dir() passing the path to that directory.


require_once('code/wfpl/file.php');

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

# return a 4 bytes that represent the passed integer as a big-endian binary number
function to_raw_int($int) {
	return chr($int >> 24) . chr(($int >> 16) & 0xff) . chr(($int >> 8) & 0xff) . chr($int & 0xff);
}

# return a php number from the string you pass in. The first 4 bytes of the
# string are read in as a binary value in big-endian format.
function from_raw_int($quad) {
	return (ord(substr($quad, 0, 1)) << 24) + (ord(substr($quad, 1, 1)) << 16) + (ord(substr($quad, 2, 1)) << 8) + ord(substr($quad, 3, 1));
}

function int_at($string, $index) {
	return from_raw_int(substr($string, $index * 4, 4));
}

# remove the first 4 bytes of the string, and return them as an int
function pop_int(&$string) {
	$int = from_raw_int(substr($string, 0, 4));
	$string = substr($string, 4);
	return $int;
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
	$header_count = pop_int($data);
	$out = array();
	while($header_count--) {
		$size = pop_int($data);
		$out[] = substr($data, 0, $size);
		$data = substr($data, $size);
	}
	return $out;
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
	$out = to_raw_int(count($data));
	foreach($data as $dat) {
		$out .= to_raw_int(strlen($dat));
		$out .= $dat;
	}
	fdb_set_raw($key, $out);
}

?>
