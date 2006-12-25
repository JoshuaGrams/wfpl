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

# encode for putting within double-quotes in SQL
function enc_sql($str) {
	$str = str_replace("\\", "\\\\", $str);
	$str = str_replace('"', "\\\"", $str);
	return $str;
}

# encode for output in html. does nothing with whitespace
function enc_html($str) {
	$str = str_replace('&', '&amp;', $str);
	$str = str_replace('<', '&lt;', $str);
	$str = str_replace('>', '&gt;', $str);
	return $str;
}


# html attributes (eg  <input value="...."
function enc_attr($str) {
	$str = str_replace('&', '&amp;', $str);
	$str = str_replace('"', '&quot;', $str);
	return $str;
}

# this is a stupid hack to work around html's stupid syntax for checkboxes
function enc_checked($str) {
	if($str == 'Yes') {
		return '" checked="checked';
	} else {
		return '';
	}
}
	

