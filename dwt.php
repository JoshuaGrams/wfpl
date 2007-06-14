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

require_once('code/wfpl/file.php');

function dwt_reset() {
	$GLOBALS['_dwt_keys'] = array('<!-- TemplateEndEditable -->');
	$GLOBALS['_dwt_values'] = array('');
}

function dwt_init() {
	if(isset($GLOBALS['_dwt_keys']) && isset($GLOBALS['_dwt_values'])) {
		return;
	}
	dwt_reset();
}

function dwt_load($filename) {
	$GLOBALS['_dwt_template'] = read_whole_file($filename);
	dwt_init();
}

function dwt_set_raw($name, $value) {
	$GLOBALS['_dwt_keys'][] = $name;
	$GLOBALS['_dwt_values'][] = $value;
}

function dwt_set($name, $value) {
	dwt_set_raw("<!-- TemplateBeginEditable name=\"$name\" -->", $value);
}

# returns index into arrays
function dwt_find_raw($name) {
	for($i = 0; $i < count($GLOBALS['_dwt_keys']); ++$i) {
		if($GLOBALS['_dwt_keys'][$i] == $name) {
			return $i;
		}
	}
	return null;
}

# returns index into arrays
function dwt_find($name) {
	return dwt_find_raw("<!-- TemplateBeginEditable name=\"$name\" -->");
}

function dwt_append_raw($name, $value) {
	$index = dwt_find_raw($name);
	if($index !== null) {
		$GLOBALS['_dwt_values'][$index] .= $value;
	} else {
		dwt_set_raw($name, $value);
	}
}

function dwt_append($name, $value) {
	dwt_append_raw("<!-- TemplateBeginEditable name=\"$name\" -->", $value);
}

function dwt_output($filename = null) {
	if($filename !== null) {
		dwt_load($filename);
	}
	print(str_replace($GLOBALS['_dwt_keys'], $GLOBALS['_dwt_values'], $GLOBALS['_dwt_template']));
}

?>
