<?php

#  Copyright (C) 2006 Jason Woofenden
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
# RewriteRule    .*\.html$  /foo/run.php

function run_php($basename = false) {
	if($basename) {
		$html_file = "$basename.html";
		$php_file = "$basename.php";
	} else {
		$html_file = $_SERVER['REDIRECT_URL'];
		$html_file = ereg_replace('.*/', '', $html_file);
		if($html_file == '') {
			$html_file = 'index.html';
		}
		$php_file = ereg_replace('\.html$', '.php', $html_file);
	}
	if($php_file != $html_file && file_exists($php_file)) {
		require_once('code/wfpl/template.php');
		if(file_exists($html_file)) tem_load($html_file);
		require $php_file;
		if(file_exists($html_file)) tem_output();
	} else {
		if(file_exists($html_file)) {
			require $html_file;
		} else {
			header('HTTP/1.0 404 File Not Found');
			if(file_exists('404.php') || file_exists('404.html')) {
				run_php('404');
			} else {
				echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><html><head><title>404</title></head><body><h1>404 File Not Found</h1></body></html>';
			}
		}
	}
}

run_php();

?>
