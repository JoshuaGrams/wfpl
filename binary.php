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


# This file contains code to work with "raw" binary numbers in big-endian format


# return a 4 byte string that represent the passed integer as a big-endian binary number
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

# convert an array (not hash) to a string of bytes
function array_to_raw($data) {
	$ret = to_raw_int(count($data));
	foreach($data as $dat) {
		$ret .= to_raw_int(strlen($dat));
		$ret .= $dat;
	}
	return $ret;
}

function raw_to_array($data) {
	$header_count = pop_int($data);
	$ret = array();
	while($header_count--) {
		$size = pop_int($data);
		$ret[] = substr($data, 0, $size);
		$data = substr($data, $size);
	}
	return $ret;
}

?>
