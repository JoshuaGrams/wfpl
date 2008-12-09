<?php

#  Copyright (C) 2005 Jason Woofenden
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


# This file contains basic encodings. These are used by the encoder. You can
# specify any template tag to be encoded with this syntax: ~variable.encoding~
#
# this example: <p>~foo.html~</p>
# will encode foo (using enc_html()) before displaying it, so that characters
# such as < will display properly.

function enc_cap($str) {
	$str = ucfirst($str);
	return $str;
}

function enc_jsdq($str) {
	$str = enc_sql($str);
	$str = str_replace("\n", "\\n", $str);
	return str_replace("\r", "\\r", $str);
}

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

# Encode for output in html. Convert newlines to <br />
#
# Example: <p>~foo.htmlbr~</p>
function enc_htmlbr($str) {
	$str = enc_html($str);
	$str = str_replace("\n", "<br />\n", $str);
	return $str;
}

# Encode for output in html. Preserves newlines and indentation by converting
# newlines to <br /> and spaces at the begining of lines to &nbsp;&nbsp;
#
# Example: <p>~foo.htmlbrtab~</p>
function enc_htmlbrtab($str) {
	$str = enc_htmlbr($str);
	$space_to_nbsp = create_function('$matches', 'return str_repeat(\'&nbsp;\', strlen($matches[0]) * 2);');
	$str = preg_replace_callback("|^ *|m", $space_to_nbsp, $str);
	return $str;
}

# Encode for output in html. Spaces converted to &nbsp;
#
# Example: <option value="12">~foo.htmlnbsp~</option>
function enc_htmlnbsp($str) {
	$str = enc_html($str);
	$str = str_replace(' ', '&nbsp;', $str);
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

# FIXME
function enc_url_path($str) {
	$str = rawurlencode($str);
	$str = str_replace('%2F', '/', $str);
	return $str;
}


# This is a hack to work around html's stupid syntax for checkboxes.
#
# Place the template marker just before a " somewhere.
#
# Example: <input type="checkbox" name="foo~foo.checked~">
function enc_checked($str) {
	if($str && $str !== 'No' && $str !== 'False' && $str !== 'false') {
		return '" checked="checked';
	} else {
		return '';
	}
}

# checkboxe values are stored in the db and handled in php as 0 or 1. When you
# want it displayed as "Yes" or "No" use this:
# Example: (displaying values from a form submission)  Over 60?: ~over_60.yesno~
function enc_yesno($str) {
	if($str && $str !== 'No' && $str !== 'False' && $str !== 'false') {
		return 'Yes';
	} else {
		return 'No';
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

function enc_upper($str) {
	return strtoupper($str);
}

# pass date in the form 2008-05-23
# ercodes date as 05/23/2008
function enc_mmddyyyy($yyyy_mm_dd) {
	if(strlen($yyyy_mm_dd) != 10) {
		return date('m/d/Y');
	}
	return substr($yyyy_mm_dd, 5, 2) . '/' . substr($yyyy_mm_dd, 8, 2) . '/' . substr($yyyy_mm_dd, 0, 4);
}

function enc_mmddyyyyhhmm($seconds) {
	return date('m/d/Y g:ia', (int)$seconds);
}




define('PULLDOWN_ARRAY', 0); define('PULLDOWN_HASH', 1); define('PULLDOWN_2D', 2);

function pulldown_options_to_2d($options, $keys_from) {
	# convert other types of input to value=>display hash
	switch($keys_from) {
		case PULLDOWN_HASH:
			$new_options = array();
			foreach($options as $value => $display) {
				$new_options[] = array($value, $display);
			}
			return $new_options;
		case PULLDOWN_ARRAY:
			$new_options = array();
			foreach($options as $opt) {
				$new_options[] = array($opt, $opt);
			}
			return $new_options;
		break;
		case PULLDOWN_2D:
			return $options;
		break;
		default:
			die('pulldown_options_to_2d(): unknown value: "' . print_r($keys_from) . '" passed in $keys_from parameter');
	}
}


# call this function before you run() the template so enc_options() knows what
# to do
#
# Parameters:
#
#   name: the name of the html control
#
#   options: an array of options to display in the pulldown/selectbox
#
#   keys_from: Set to one of:
#        PULLDOWN_ARRAY: (default) values of $options are displayd and posted
#        PULLDOWN_HASH: values of $options are display, keys are posted
#        PULLDOWN_2D: $options is a 2 dimensional array.
#                     $options[0][1] is displayed, $options[0][0] is posted.
#                     $options[1][1] is displayed, $options[1][0] is posted.
#
#   multiple: UNTESTED set to true for multiple-select boxes. 

function pulldown($name, $options, $keys_from = PULLDOWN_ARRAY, $multiple = false) {
	$options = pulldown_options_to_2d($options, $keys_from);
	$GLOBALS[$name . '_options'] = array();
	$GLOBALS[$name . '_options']['options'] = $options;
	$GLOBALS[$name . '_options']['multiple'] = $multiple;
}

# output a bunch of <option> tags
function enc_options($values, $name) {
	if(!isset($GLOBALS[$name . '_options'])) {
		die('pulldown() must be called before this template can be run. See code/wfpl/encode.php');
	}
	if($GLOBALS[$name . '_options']['multiple']) { # FIXME test this
		$values = explode(', ', $values);
	}
	return encode_options($values, $GLOBALS[$name . '_options']['options'], PULLDOWN_2D);
}

# use this function along with a special template to generate the html for pulldowns and multiple select boxes.
#
# Parameters:
#
#    selected: can be a string or (for multiple-selects) an array
#
#    options, keys_from: see documentation for pulldown() above
function encode_options($selected, $options, $keys_from) {
	if(!is_array($selected)) {
		$selected = array($selected);
	}

	if($keys_from != PULLDOWN_2D) {
		$options = pulldown_options_to_2d($options, $keys_from);
	}

	$out = '';
	foreach($options as $valdisp) {
		list($value, $display) = $valdisp;
		$out .= '<option';

		if(in_array($value, $selected)) {
			$out .= ' selected="selected"';
		}

		if($value !== $display) {
			$out .= ' value="';
			$out .= enc_attr($value);
			$out .= '"';
		}
			
		$out .= '>';

		$out .= enc_htmlnbsp($display);

		$out .= "</option>\n";
	}

	return $out;
}

$GLOBALS['wfpl_states_assoc'] = array("AL" => "Alabama", "AK" => "Alaska", "AZ" => "Arizona", "AR" => "Arkansas", "CA" => "California", "CO" => "Colorado", "CT" => "Connecticut", "DE" => "Delaware", "FL" => "Florida", "GA" => "Georgia", "HI" => "Hawaii", "ID" => "Idaho", "IL" => "Illinois", "IN" => "Indiana", "IA" => "Iowa", "KS" => "Kansas", "KY" => "Kentucky", "LA" => "Louisiana", "ME" => "Maine", "MD" => "Maryland", "MA" => "Massachusetts", "MI" => "Michigan", "MN" => "Minnesota", "MS" => "Mississippi", "MO" => "Missouri", "MT" => "Montana", "NE" => "Nebraska", "NV" => "Nevada", "NH" => "New Hampshire", "NJ" => "New Jersey", "NM" => "New Mexico", "NY" => "New York", "NC" => "North Carolina", "ND" => "North Dakota", "OH" => "Ohio", "OK" => "Oklahoma", "OR" => "Oregon", "PA" => "Pennsylvania", "RI" => "Rhode Island", "SC" => "South Carolina", "SD" => "South Dakota", "TN" => "Tennessee", "TX" => "Texas", "UT" => "Utah", "VT" => "Vermont", "VA" => "Virginia", "WA" => "Washington", "DC" => "Washington, DC", "WV" => "West Virginia", "WI" => "Wisconsin", "WY" => "Wyoming");

# display <option>s
function enc_states($str) {
	$ret = '';

	return encode_options($str, $GLOBALS['wfpl_states_assoc'], PULLDOWN_HASH);
}

$GLOBALS['wfpl_provinces_assoc'] = array("AB" => "Alberta", "BC" => "British Columbia", "MB" => "Manitoba", "NF" => "Newfoundland", "NB" => "New Brunswick", "NS" => "Nova Scotia", "NT" => "Northwest Territories", "NU" => "Nunavut", "ON" => "Ontario", "PE" => "Prince Edward Island", "QC" => "Quebec", "SK" => "Saskatchewan", "YT" => "Yukon Territory");

# display <option>s
function enc_provinces($str) {
	$ret = '';

	return encode_options($str, $GLOBALS['wfpl_provinces_assoc'], PULLDOWN_HASH);
}

# returns "odd", then "even", then "odd" etc.
function enc_evenodd($values, $name) {
	if(!isset($GLOBALS['wfpl_even_odds'])) {
		$GLOBALS['wfpl_even_odds'] = array();
	}

	if($GLOBALS['wfpl_even_odds'][$name]) {
		$GLOBALS['wfpl_even_odds'][$name] = false;
		return 'even';
	} else {
		$GLOBALS['wfpl_even_odds'][$name] = true;
		return 'odd';
	}
}

?>
