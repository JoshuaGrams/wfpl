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


# return our best guess at the url used to access this page, without the path or query string
function this_url_sans_path() {
	if(strtolower($_SERVER['HTTPS']) == 'on' || strtolower($_SERVER['HTTPS']) == 'yes') {
		$url = 'https';
		$expected_port = 443;
	} else {
		$url = 'http';
		$expected_port = 80;
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

	return $url;
}

# just the hostname, no port number
function this_host() {
	if($_SERVER['HTTP_HOST']) {
		$host = $_SERVER['HTTP_HOST'];
		$p = strpos($host, ':');
		if($p) {
			$host = substr($host, 0, $p);
		}
		return $host;
	} else {
		return $_SERVER['SERVER_NAME'];
	}
}



# return our best guess at the url used to access this page
function this_url() {
	$url = this_url_sans_path();

	$url .= $_SERVER['REQUEST_URI'];

	return $url;
}

# sends an HTTP redirect
#
# $url can be:
#   1) a full URL
#   2) an absolute path
#   3) a filename (you can pass a directory/file.html and such, but "../" prefix is not supported yet)
function redirect($url, $status = '302 Moved Temporarily', $message = '') {
	if(!strpos($url, ':')) {
		while(substr($url, 0, 2) == './') {
			$url = substr($url, 2);
		}
		if(substr($url, 0, 1) == '/') {
			$url = this_url_sans_path() . $url;
		} else {
			$url = ereg_replace('/[^/]*$', "/$url", this_url());
		}
	}
			
	if(function_exists('session_save_messages')) {
		session_save_messages();
	}

	header("HTTP/1.0 $status");
	header("Location: $url");
	echo($message);
	exit();
}

?>
