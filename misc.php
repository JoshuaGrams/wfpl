<?php

#  Copyright (C) 2006 Jason Woofenden
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

# returns an array containing just the elements of $pipes that are readable (without blocking)
# timeout 0 means don't wait, timeout NULL means wait indefinitely
function readable_sockets($pipes, $timeout = 0){
	$read = array_values($pipes);
	$ret = stream_select($read, $write = NULL, $exceptions = NULL, $timeout);
	if($ret === false) {
		return false;
	}
	if($ret) {
		return $read;
	} else {
		return array();
	}
}


# Parameters:
#     command
#     stdin
# Returns: (as array)
#     exit code
#     stdout
function exec_pipe($command, $stdin) {
	$descriptorspec = array(
   	   0 => array('pipe', 'r'),  // stdin is a pipe that the child will read from
   	   1 => array('pipe', 'w'),  // stdout is a pipe that the child will write to
   	   2 => array('file', '/dev/null', 'w')   // stderr is a pipe that the child will write to
	);

	$process = proc_open($command, $descriptorspec, $pipes);

	if (is_resource($process)) {
		fwrite($pipes[0], $stdin);
		fclose($pipes[0]);

		while (!feof($pipes[1])) {
			$chunk = fread($pipes[1], 1024);
			$stdout .= $chunk;
			sleep(0.5);
		}

		fclose($pipes[1]);

		// It is important that you close any pipes before calling
		// proc_close in order to avoid a deadlock
		$return_value = proc_close($process);

		return array($return_value, $stdout);
	}
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

# return today's date in yyyy-mm-dd format
function today_ymd() {
	return strftime('%Y-%m-%d');
}


function get_text_between($text, $start_text, $end_text) {
	$start = strpos($text, $start_text);
	if($start === false) {
		return false;
	}
	$text = substr($text, $start + strlen($start_text));
	$end = strpos($text, $end_text);
	if($end === false) {
		return false;
	}
	return substr($text, 0, $end);
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
