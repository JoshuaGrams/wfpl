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


# This file facilitates making a site with mixed PHP and html files. All URLs
# have the .html extension. One benefit of this is that you can change static
# html files to php scripts without having to update links.

# This script will pull the filename from the URL. It looks for a file with
# that name, and for one with the same basename, but with the .php extension
# and acts accordingly:

#--------------------------------------------#
#        | .php file exists   | no .php file #
#--------+--------------------+--------------#
# .html  | load html file as  | pass html    #
# file   | a template and run | file through #
# exists | run the php file   | as is        #
#--------+--------------------+--------------#
# no     |                    |              #
# .html  | run php file       | display 404  #
# file   |                    |              #
#--------------------------------------------#



# To activate this script in a directory, you'll need to:
#
# 1) make a symbolic link to (or copy of) this file in your directory. and
#
# 3) Set your webserver to run this script instead of html files. Here's how to
# do that with apache: put something like the following in you your .htaccess
# file (where /foo/ is the part of the url between the hostname and the
# filename.) The example below would work for this url:
# http://example.com/foo/bar.html

# RewriteEngine  on
# RewriteRule    ^$  /foo/run.php
# RewriteRule    ^foo/[^/]*\.html$  /foo/run.php

require_once('code/wfpl/file_run.php');
require_once('code/wfpl/http.php');
require_once('code/wfpl/template.php');

function run_php($basename = false) {
	if(!$basename) {
		$basename = $_SERVER['REDIRECT_URL'];
		$basename = ereg_replace('.*/', '', $basename);
		$basename = ereg_replace('\.html$', '', $basename);
		if($basename == '') {
			$basename = 'index';
		}
	}

	$html_file = "$basename.html";
	$php_file = "$basename.php";

	if(!file_exists($php_file)) {
		if(file_exists($html_file)) {
			readfile($html_file);
		} else {
			header('HTTP/1.0 404 File Not Found');
			if(file_exists('404.php') || file_exists('404.html')) {
				run_php('404');
			} else {
				echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><html><head><title>404</title></head><body><h1>404 File Not Found</h1></body></html>';
			}
		}
		exit();
	}

	if(file_exists($html_file)) {
		$GLOBALS['wfpl_template'] = new tem();
		tem_load($html_file);
	}

	$other = file_run($php_file);
	if($other) {
		if(strpos($other, ':')) {
			redirect($other);
			exit();
		}
		run_php($other);
		return;
	}

	if($GLOBALS['wfpl_template']) {
		if(file_exists('template.html')) {
			$tem = new tem();
			$tem->load("template.html");
			$sections = tem_top_subs();
			if($sections) foreach($sections as $name => $val) {
				$tem->set($name, $val);
			}

			if(file_exists("$basename.css")) {
				$tem->set('basename', $basename);
				$tem->sub('css_links');
			}

			$GLOBALS['wfpl_template'] = $tem;
		}

		if(function_exists('display_messages')) {
			display_messages();
		}
		tem_output();
	}
}

run_php();

?>
