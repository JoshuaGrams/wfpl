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


# return our best guess at the url used to access this page
function this_url() {
	list($protocol, $version) = explode('/', $_SERVER['SERVER_PROTOCOL']);
	$url = strtolower($protocol);

	if($url == 'http') {
		$expected_port = 80;
	} elseif ($url == 'https') {
		$expected_port = 443;
	} else {
		$expected_port = -1;
	}

	$url .= '://';
	if($_SERVER['HTTP_HOST']) {
		$url .= $_SERVER['HTTP_HOST'];
	} else {
		$url .= $_SERVER['SERVER_NAME'];
		if($_SERVER['SERVER_PORT'] != $expected_port) {
			$url .= ':' . $_SERVER['SERVER_PORT'];
		}
	}

	$url .= $_SERVER['REQUEST_URI'];

	return $url;
}

function redirect($url, $status = '302 Moved Temporarily', $message = '') {
	header("HTTP/1.0 $status");
	header("Location: $url");
	echo($message);
	exit();
}

?>
