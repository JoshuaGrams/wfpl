<?php

#  Copyright (C) 2006 Jason Woofenden
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



# This file generates a simple calendar which looks something like this:
#
#            December 2006
#     Sun Mon Tue Wed Thu Fri Sat
#      1   2
#      3   4   5   6   7   8   9
#     10  11  12  13  14  15  16
#     17  18  19  20  21  22  23
#     24  25  26  27  28  29  30
#     31
#
# The days with events have different CSS classes and can link to custom pages
# or javascript or whatever.
#
# The html and CSS is completely customizable without opening a php file.



require_once('code/wfpl/template.php');

function calendar_week(&$template) {
	$template->sub('week');
}

function calendar_day($kind, &$template) {
	$template->sub($kind);
	$template->sub('day');
}

# php4 is broken, in that you cannot set a default value for a parameter that
# is passed by reference. So, this is set up to use the following screwy
# syntax:
#
# calendar('2006', '12', $events, ref($my_template))
function calendar($year, $month, $events = 0, $template = 0) {
	if($template === 0) {
		$template = &$GLOBALS['wfpl_template'];
	} else {
		$template = &$template->ref;
	}

	if(strlen($year) == 2) {
		$year = "20$year";
	}

	$start_timestamp = strtotime("$year-$month-01 00:00");
	$cell = 0;

	$template->set('month_year', strftime('%B', $start_timestamp) . " " . $year);

	# number of non-day slots at the begining of the month
	$pre_non_days = date('w', $start_timestamp );

	# first display empty cells so the 1st can be in the right column
	while($cell < $pre_non_days) {
		calendar_day('nonday', $template);
		$cell++;
	}

	# do the days in this month
	$days_count = date('t', $start_timestamp );
	for($day = 1; $day <= $days_count; $day++ ) {
		$template->set('day_number', $day);
		if(($cell + 1) % 7 < 2) {
			$type = 'weekend';
		} else {
			$type = 'day';
		}
		if($events[$day]) {
			$template->set('day_page', $events[$day]);
			calendar_day("busy_$type", $template);
		} else {
			calendar_day("empty_$type", $template);
		}

		$cell++;
		if($cell % 7 == 0) {
			calendar_week($template);
		}
	}

	# fill the rest of the row with empty cells
	if($cell % 7) {
		while($cell % 7) {
			calendar_day('nonday', $template);
			$cell++;
		}
		calendar_week($template);
	}
}

?>
