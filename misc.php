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

function read_whole_file($name) {
	$fd = fopen($name, 'r');
	if($fd === false) {
		die("Failed to read file: '$name'");
	}
	$file_data = fread($fd, filesize($name));
	fclose($fd);
	return $file_data;
}

function unix_newlines($str) {
	$str = str_replace("\r\n", "\n", $str);
	return str_replace("\r", "\n", $str);
}

# return current year (all 4 digits)
function this_year() {
	return strftime('%Y');
}

# return the number of the current month (1..12)
function this_month() {
	return ereg_replace('^0', '', strftime('%m'));
}


# php4 is broken, in that you cannot set a default value for a parameter that
# is passed by reference. So, this is set up to use the following screwy
# syntax:
#
# function foo($bar = 0) {
#   if($bar !== 0) {
#     $bar = $bar->ref;
#   }
#	...
# }
#
# foo();
# foo(ref($baz));

class stupid_reference {
	var $ref;
	function stupid_reference(&$ref) {
		$this->ref = &$ref;
	}
}
function ref(&$foo) {
	return new stupid_reference($foo);
}

?>
