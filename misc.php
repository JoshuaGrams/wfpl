<?php

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
	return strftime('%m');
}

?>
