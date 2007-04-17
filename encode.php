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


# This file contains basic encodings. These are used by the encoder. You can
# specify any template tag to be encoded with this syntax: ~variable.encoding~
#
# this example: <p>~foo.html~</p>
# will encode foo (using enc_html()) before displaying it, so that characters
# such as < will display properly.


# encode for putting within double-quotes in SQL
function enc_sql($str) {
	$str = str_replace("\\", "\\\\", $str);
	$str = str_replace('"', "\\\"", $str);
	return $str;
}

# Encode for output in html. does nothing with whitespace
#
# Example: <p>~foo.html~</p>
function enc_html($str) {
	$str = str_replace('&', '&amp;', $str);
	$str = str_replace('<', '&lt;', $str);
	$str = str_replace('>', '&gt;', $str);
	return $str;
}


# HTML attribute.
#
# Example: <input name="foo" value="~foo.attr~">
function enc_attr($str) {
	$str = str_replace('&', '&amp;', $str);
	$str = str_replace('"', '&quot;', $str);
	return $str;
}

# URI agument value.
#
# Example:  <a href="http://example.com?foo=~foo.url_val.attr~">http://example.com?foo=~foo.url_val~</a>
function enc_url_val($str) {
	return rawurlencode($str);
}

# This is a hack to work around html's stupid syntax for checkboxes.
#
# Place the template marker just before a " somewhere.
#
# Example: <input type="checkbox" name="foo~foo.checked~">
function enc_checked($str) {
	if($str == 'Yes') {
		return '" checked="checked';
	} else {
		return '';
	}
}

# add a tab at the begining of each non-empty line
function enc_tab($str) {
	$lines = explode("\n", $str);
	$out = '';
	foreach($lines as $line) {
		if($line) {
			$out .= "\t$line";
		}
		$out .= "\n";
	}

	# remove the extra newline added above
	return substr($out, 0, -1);
}


# display <option>s
function enc_states($str) {
	$states_assoc = array("AL" => "Alabama", "AK" => "Alaska", "AZ" => "Arizona", "AR" => "Arkansas", "CA" => "California", "CO" => "Colorado", "CT" => "Connecticut", "DE" => "Delaware", "FL" => "Florida", "GA" => "Georgia", "HI" => "Hawaii", "ID" => "Idaho", "IL" => "Illinois", "IN" => "Indiana", "IA" => "Iowa", "KS" => "Kansas", "KY" => "Kentucky", "LA" => "Louisiana", "ME" => "Maine", "MD" => "Maryland", "MA" => "Massachusetts", "MI" => "Michigan", "MN" => "Minnesota", "MS" => "Mississippi", "MO" => "Missouri", "MT" => "Montana", "NE" => "Nebraska", "NV" => "Nevada", "NH" => "New Hampshire", "NJ" => "New Jersey", "NM" => "New Mexico", "NY" => "New York", "NC" => "North Carolina", "ND" => "North Dakota", "OH" => "Ohio", "OK" => "Oklahoma", "OR" => "Oregon", "PA" => "Pennsylvania", "RI" => "Rhode Island", "SC" => "South Carolina", "SD" => "South Dakota", "TN" => "Tennessee", "TX" => "Texas", "UT" => "Utah", "VT" => "Vermont", "VA" => "Virginia", "WA" => "Washington", "DC" => "Washington, DC", "WV" => "West Virginia", "WI" => "Wisconsin", "WY" => "Wyoming");
	$ret = '';

	foreach($states_assoc as $code => $name) {
		$ret .= "<option name=\"$code\"";
		if($code == $str) {
			$ret .= " selected";
		}
		$ret .= ">$name</option>\n";
	}
	return $ret;
}

?>
