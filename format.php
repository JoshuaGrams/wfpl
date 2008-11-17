<?php

#  Copyright (C) 2005 Jason Woofenden
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


# This file contains basic encodings

function format_caption($str) {
	$str = str_replace('_', ' ', $str);
	$str = ucwords($str);
	return str_replace('Email', 'E-mail', $str);
}

# This function makes sure that $str is in the list of options, and returns "" otherwise
function format_options($str, $name) {
	if(!isset($GLOBALS[$name . '_options'])) {
		die("Couldn't find options for \"$name\". Be sure to call pulldown().");
	}

	foreach($GLOBALS[$name . '_options']['options'] as $keyval) {
		list($key, $value) = $keyval;
		if($str == $key) {
			return $str;
		}
	}

	return $str;
}

function format_int($str) {
	$str = ereg_replace('[^0-9]', '', $str);
	return ereg_replace('^0*([0-9])', '\1', $str);
}

function format_decimal($str) {
	$str = ereg_replace('[^0-9.]', '', $str);
	$pos = strpos($str, '.');
	if($pos !== false) {
		$str = str_replace('.', '', $str);
		if($pos == 0) {
			return '0.' . $str;
		} elseif($pos == strlen($str)) {
			return $str;
		} else {
			return substr($str, 0, $pos) . '.' . substr($str, $pos);
		}
	}
	return $str;
}

# return 0 of there's no digits
function format_int_0($str) {
	$str = format_int($str);
	if($str == '') {
		return '0';
	}
	return $str;
}

function format_zip($str) {
	$str = ereg_replace('[^0-9]', '', $str);
	if(strlen($str) > 5) {
		return substr($str, 0, 5) . '-' . substr($str, 5);
	}
	return $str;
}

function format_filename($str, $allow_uppercase = false) {
	if(!$allow_uppercase) {
		$str = strtolower($str);
	}
	$str = ereg_replace('[^a-zA-Z0-9_.-]', '_', $str);
	return ereg_replace('^[.-]', '_', $str);
}

function client_path_to_filename($path) {
	$filename = ereg_replace(".*[:/\\]", '', $path);
	return format_filename($filename, true);
}


function format_h_w_image($str) {
	$fields = explode(' ', $str);
	if(count($fields) != 3) {
		return '';
	}

	list($width, $height, $filename) = $fields;
	$width = format_int_0($width);
	$height = format_int_0($height);
	$filename = format_filename($filename);

	return "$width $height $filename";
}

function format_varname($str) {
	$str = strtolower($str);
	$str = ereg_replace('[^a-z0-9_]', '_', $str);
	return ereg_replace('^[0-9]*', '', $str);
}

function format_oneline($str) {
	$str = str_replace("\r", '', $str);
	return str_replace("\n", '', $str);
}

function format_unix($str) {
	return unix_newlines($str);
}

function format_bool($str) {
	if($str && $str !== 'No' && $str !== 'False' && $str !== 'false') {
		return 1;
	} else {
		return 0;
	}
}

function format_yesno($str) {
	if($str && $str !== 'No' && $str !== 'False' && $str !== 'false') {
		return 'Yes';
	} else {
		return 'No';
	}
}

function format_email($str) {
	# FIXME
	return trim(format_oneline($str));
}

function format_url($str) {
	# FIXME check for TLD? encode special chars?
	$str = trim(format_oneline($str));
	if($str !== '') {
		if(strpos($str, ':') === false) {
			$str = 'http://' . $str;
		}
	}
	return $str;
}

function format_money($str, $display_cents = true) {
	$str = ereg_replace('[^0-9.]', '', $str);
	if($display_cents) {
		$int = (int)($str * 100);
		$cents = $int % 100;
		$cents = sprintf('.%02d', $cents);
		$int = (int)($str); # go from the source again, so we can print numbers above 2M without cents.
	} else {
		$cents = '';
		$int = round($str);
	}
	$chars = (string)$int;
	$output = '';
	$comma = 4;
	$index = strlen($chars);
	while($index) {
		--$index;
		--$comma;
		if($comma == 0) {
			$comma = 3;
			$output = ',' . $output;
		}
		$char = substr($chars, $index, 1);
		$output = $char . $output;
	}
	$output = '$' . $output . $cents;
	return $output;
}

function format_dollars($str) {
	return format_money($str, false);
}

# date is edited as mm/dd/yyyy but stored as yyyy-mm-dd
function format_mdy_to_ymd($str) {
	require_once('code/wfpl/time.php');
	return mdy_to_ymd(format_oneline($str));
}

function format_phone($str) {
	$str = ereg_replace('[^0-9]', '', $str);
	$str = ereg_replace('^1*', '', $str);
	$len = strlen($str);
	$output = '';

	if($len < 10 && $len != 7) {
		#NOT A VALID PHONE NUMBER
		return $str;
	}

	if($len > 10) {
		$output = ' ext: ' . substr($str, 10);
		$len = 10;
	}

	if($len == 10) {
		$area = substr($str, 0, 3);
		$str = substr($str, 3);
	}

	$output = substr($str, 3) . $output;
	$output = substr($str, 0, 3) . '-' . $output;

	if($area) {
		$output = "($area) " . $output;
	}

	return $output;
}

?>
