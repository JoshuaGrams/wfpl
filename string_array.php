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


# This file contains code to convert an array into a string, and back again.

require_once('code/wfpl/binary.php');

function string_to_array($data) {
	$header_count = pop_int($data);
	$out = array();
	while($header_count--) {
		$size = pop_int($data);
		$out[] = substr($data, 0, $size);
		$data = substr($data, $size);
	}
	return $out;
}

function array_to_string($array) {
	$ret = to_raw_int(count($array));
	foreach($array as $element) {
		$ret .= to_raw_int(strlen($element));
		$ret .= $element;
	}
	return $ret;
}

?>
