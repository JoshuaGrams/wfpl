<?php

#  Copyright (C) 2005 Jason Woofenden
#
#  This file is part of wfpl.
#
#  wfpl is free software; you can redistribute it and/or modify it
#  under the terms of the GNU General Public License as published by
#  the Free Software Foundation; either version 2, or (at your option)
#  any later version.
#
#  wfpl is distributed in the hope that it will be useful, but
#  WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
#  General Public License for more details.
#
#  You should have received a copy of the GNU General Public License
#  along with wfpl; see the file COPYING.  If not, write to the
#  Free Software Foundation, Inc., 59 Temple Place - Suite 330, Boston,
#  MA 02111-1307, USA.


# This file contains basic encodings

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


#function ftest($val) {
#	printf("$val: '%s'<br />\n", format_phone($val, true));
#}
#
#echo "FORMAT TESTS<br><br>";
#ftest("$3");
#ftest("3.99");
#ftest("3.5");
#ftest("891234");
#ftest("8221234");
#ftest("82212334");
#ftest("122313234");
#ftest("1158221234");
#ftest("1558221234");
#ftest("12235513234");
#ftest("122355123334");
#ftest("1585552212334");
#ftest("15855522123334");

?>
