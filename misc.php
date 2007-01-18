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
