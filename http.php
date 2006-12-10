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

?>
