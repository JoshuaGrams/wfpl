<?php

#  2006 Public Domain
#
#  This file was placed into the public domain on November 16th, 2008 by it's
#  sole author Jason Woofenden

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

if(file_exists('code/config.php')) {
	file_run('code/config.php');
}

# pass the basename of the page you want for normal execution
# pass ./page.html to redirect to page.html in this directory
# pass http://foo.com/bar.html to redirect to a full directory
function run_php($dest = false) {
	if($dest) {
		# if it's got a : it must be a full URL, redirect
		if(strpos($dest, ':')) {
			redirect($dest);
			exit();
		}

		# if it starts with './' then it's a relative URL, redirect
		if(substr($dest, 0, 2) == './') {
			redirect(ereg_replace('/[^/]*$', substr($dest, 1), this_url()));
			exit();
		}

		# otherwise, it's a normal basename, display that content
		$basename = $dest;

	} else { # no dest arg
		$basename = $_SERVER['REDIRECT_URL'];
		$basename = ereg_replace('.*/', '', $basename);
		$basename = ereg_replace('\.html$', '', $basename);
		if($basename == '') {
			$basename = 'index';
		}
	}

	$html_file = "$basename.html";
	$php_file = "$basename.php";

	$html_exists = file_exists($html_file);
	$php_exists = file_exists($php_file);

	# cms_get can return one of:
	# 1) false to indicate that there's no cms content for this basename
	# 2) a string to indicate a soft/full redirect just as foo_main()
	# 3) a hash of key/value pairs to be tem_set(key,value) on the template
	if(function_exists('cms_get')) {
		$cms_content = cms_get($basename);
		if(is_string($cms_content)) {
			run_php($cms_content);
			return;
		}
	} else {
		$cms_content = false;
	}

	if(!$php_exists && !$html_exists && !$cms_content) {
		header('HTTP/1.0 404 File Not Found');
		if(file_exists('404.php') || file_exists('404.html')) {
			run_php('404');
			return;
		} else {
			echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><html><head><title>404</title></head><body><h1>404 File Not Found</h1></body></html>';
		}
	}

	# If there's no template.html we don't want to parse $html_file.
	if($html_exists && !$php_exists && !file_exists('template.html')) {
		readfile($html_file);
		exit();
	}

	if($html_exists) {
		tem_load_new($html_file);
	}

	if($php_exists) {
		# files can return a basename or URL of a page to be run/displayed
		$other = file_run($php_file);
		if($other) {
			run_php($other);
			return;
		}
	} else {
		$sub_names = tem_top_sub_names();
		foreach($sub_names as $sub_name) {
			tem_show($sub_name);
		}
	}

	# Check for $GLOBALS['wfpl_template'] because it might have been set (or unset) by the php script.
	if($GLOBALS['wfpl_template']) {
		if(file_exists('template.html')) {
			$tem = new tem();
			$tem->load("template.html");
			$tem->set('basename', $basename);
			if($cms_content) foreach($cms_content as $name => $val) {
				$tem->append($name, $val);
			}
			$sections = tem_top_subs();
			if($sections) foreach($sections as $name => $val) {
				$tem->append($name, $val);
			}

			if(file_exists("$basename.css")) {
				$tem->set('css_link', "$basename.css");
				$tem->show('css_links');
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
