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
