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

function format_int($str) {
	$str = ereg_replace('[^0-9]', '', $str);
	return ereg_replace('^0*([1-9])', '\1', $str);
}

function format_zip($str) {
	return ereg_replace('[^0-9]', '', $str);
}

function format_filename($str) {
	$str = strtolower($str);
	$str = ereg_replace('[^a-z0-9_.]', '_', $str);
	return ereg_replace('^[.]*', '', $str);
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

function format_yesno($str) {
	if($str) {
		return "Yes";
	} else {
		return "No";
	}
}

function format_email($str) {
	# FIXME
	return format_oneline($str);
}

function format_url($str) {
	# FIXME
	return format_oneline($str);
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
