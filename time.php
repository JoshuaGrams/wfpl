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


# This file contains functions to manipulate/calculate dates/times

function ymd_to_days($year, $month, $day) {
	return (int)(mktime(12,0,0,$month,$day, $year, 0) / 86400);
}

function days_to_ymd($days) {
	return explode('-', date('Y-n-j', $days * 86400 + 43200));
}

function days_to_weekday_name($days) {
	$day_names = array('Thursday', 'Friday', 'Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday');
	return $day_names[$days % 7];
}


#function time_test() {
#	for($i = 0; $i < 41 * 86400; $i += 86399) {
#		echo "seconds: $i";
#		$days = (int)($i / 86400);
#		list($year, $month, $day) = days_to_ymd($days);
#		echo ", days_to_ymd($days): $year-$month-$day";
#		$days = ymd_to_days($year, $month, $day);
#		echo ", ymd_to_days($year, $month, $day): $days\n<br>";
#	}
#}


# pass anything, and get a valid date
#
# returns array($year, $month, $day)
function clean_ymd($year, $month, $day) {
	$year = intval($year, 10);
	$month = intval($month, 10);
	$day = intval($day, 10);

	if($year < 100) {
		$year += 2000;
	}
	if($month < 1) {
		$month = 1;
	} elseif($month > 12) {
		$month = 12;
	}
	if($day < 1) {
		$day = 1;
	} elseif($day > 31) {
		# FIXME this should check the month
		$day = 31;
	}

	return array($year, $month, $day);
}

# convert date string from mm/dd/yyyy to yyy-mm-dd
function mdy_to_ymd($date) {
	$date = ereg_replace('[^0-9/-]', '', $date);
	$date = ereg_replace('-', '/', $date);
	$parts = explode('/', $date);
	switch(count($parts)) {
		case 1:
			$year = $parts[0];
			if(strlen($year) == 0) {
				list($month, $day, $year) = explode('/', date('m/d/Y'));
			} else {
				list($month, $day) = explode('/', date('m/d'));
			}
		break;
		case 2:
			list($month, $year) = $parts;
			$year = date('d');
		break;
		default:
			list($month, $day, $year) = $parts;
	}

	list($year, $month, $day) = clean_ymd($year, $month, $day);

	return sprintf('%04u-%02u-%02u', $year, $month, $day);
}

# convert date string from yyy-mm-dd to mm/dd/yyyy
function ymd_to_mdy($date) {
	$date = ereg_replace('[^0-9/-]', '', $date);
	$date = ereg_replace('/', '-', $date);
	$parts = explode('-', $date);
	switch(count($parts)) {
		case 1:
			$year = $parts[0];
			if(strlen($year) == 0) {
				list($year, $month, $day) = explode('-', date('Y-m-d'));
			} else {
				list($month, $day) = explode('-', date('m-d'));
			}
		break;
		case 2:
			list($year, $month) = $parts;
			$year = date('d');
		break;
		default:
			list($year, $month, $day) = $parts;
	}

	list($year, $month, $day) = clean_ymd($year, $month, $day);

	return sprintf('%02u/%02u/%04u', $month, $day, $year);
}

function enc_mdy($date) {
	return ymd_to_mdy($date);
}

?>
