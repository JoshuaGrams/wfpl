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


# This exists because file_get_contents() is not documented well. (It says that
# the second parameter is optional, but does not specify the default behavior.)
function read_whole_file($name) {
	$fd = fopen($name, 'r');
	if($fd === false) {
		die("Failed to read file: '$name'");
	}
	$file_data = fread($fd, filesize($name));
	fclose($fd);
	return $file_data;
}

# This exists because file_put_contents() is not included in PHP4.
function write_whole_file($name, $data) {
	$fd = fopen($name, 'w');
	if($fd === false) {
		die("Failed to read file: '$name'");
	}
	fwrite($fd, $data);
	fclose($fd);
}

function read_whole_file_or_false($name) {
	if(!file_exists($name)) {
		return false;
	}
	return read_whole_file($name);
}

?>
