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


# This file contains generally useful template handling code. It is wrapped in
# an object so that if you want/need to you can make more than one instance of
# it and they won't step on each other's toes. Also there are a set of global
# functions at the bottom so you don't have to mess around with objects if you
# don't want to. The documentation will be on the object methods, but just know
# that each has a straight function wrapper at the bottom with 'tem_' prepended
# to the name.

# This is designed to be as simple as it can be for your project. The simple
# way to use it is to set some key/value pairs with tem_set() then call
# tem_output('filename.html') to output the page. A more complex example
# including the use of sub-templates can be found in tem_test.php

# FIXME: sub-sub templates need to be cleared when the sub template containing
# them is run

require_once('code/wfpl/encode.php');
require_once('code/wfpl/misc.php');
require_once('code/wfpl/file.php');

class tem {
	var $keyval;        # an array containing key/value pairs 
	var $filename;      # template filename (sometimes not set)
	var $template;      # contents of template
	var $sub_templates; # tag-name/template-string pairs
	var $sub_subs;      # key: sub-template name  value: array of names of the sub-templates of this one

	# initialize variables
	function tem() {
		$this->keyval = array('' => '~'); # so that ~~ in the template creates a single ~
		$this->sub_templates = array();
	}

	# set a key/value pair. if a ~tag~ in the template matches key it will be replaced by value
	function set($key, $value) {
		$this->keyval[$key] = $value;
	}

	# clear a value. Functionally equivalent to set($key, '') but cleaner and more efficient
	function clear($key) {
		unset($this->keyval[$key]);
	}

	# grab a value you stuck in earlier with set()
	function get($key) {
		return $this->keyval[$key];
	}

	# run the template engine on one of the sub-templates and append the result
	# to the keyval in the main array. See tem_test.php for an example of how
	# this can be used.
	function sub($sub_template_name) {
		$this->keyval[$sub_template_name] .= template_run($this->sub_templates[$sub_template_name], $this->keyval);

		# after running a sub-template, clear its sub-templates
		if(isset($this->sub_subs[$sub_template_name])) {
			foreach($this->sub_subs[$sub_template_name] as $sub_sub) {
				$this->clear($sub_sub);
			}
		}
	}

	# this is used by tem::load() and should be otherwise useless
	function _load(&$in, &$out, &$parents, &$parent) {
		while($in) {
			# scan for one of: 1) the begining of a sub-template 2) the end of this one 3) the end of the file
			$n = strpos($in, '<!--~');
			if($n === false) { # not found
				# we hit the end of the file
				$out .= $in;
				$in = '';
				return;
			}

			# move everything up to (but not including) <!-- to the output
			$out .= substr($in, 0, $n);
			$in = substr($in, $n);

			# we found something.
			# is it an end tag?
			if(strcmp('<!--~end~-->', substr($in, 0, 12)) == 0) {
				$in = substr($in, 12);
				$parent = array_pop($parents);
				return;
			}

			$matches = array();
			# this limits sub_template names to 50 chars
			if(ereg('^<!--~([^~]*) start~-->', substr($in, 0, 65), $matches)) {
				list($start_tag, $tag_name) = $matches;

				# keep track of the tree
				if(!isset($this->sub_subs[$parent])) {
					$this->sub_subs[$parent] = array();
				}
				array_push($this->sub_subs[$parent], $tag_name);
				array_push($parents, $parent);
				$parent = $tag_name;

				$out .= '~' . $tag_name . '~';
				$in = substr($in, strlen($start_tag));
				$this->sub_templates[$tag_name] = '';
				$this->_load($in, $this->sub_templates[$tag_name], $parents, $parent);
			} else {
				# it's not a start tag or end tag, so let's pass it through:
				$out .= substr($in, 0, 5);
				$in = substr($in, 5);
			}
		} #repeat
	}

	# This is useful when you have sub-templates that you want to mess with
	# before the main template is run. But can also be used to simply specify
	# the filename ahead of time.
	function load($filename) {
		$this->filename = $filename;
		$tmp = read_whole_file($filename);
		$this->template = '';
		$parents = array('top_level_subs' => array());
		$parent = 'top_level_subs';
		$this->_load($tmp, $this->template, $parents, $parent);
	}
		
	# Run the template. Pass a filename, or a string, unless you've already
	# specified a template with load()
	function run($templ = false) {
		$template_string = $this->template;
		$template_file = $this->file;
		if($templ !== false) {
			if(strlen($templ) < 150 && file_exists($templ)) {
				$template_file = $templ;
				unset($template_string);
			} else {
				$template_string = $templ;
			}
		}

		if(!$template_string) {
			if(!$template_file) {
				print "sorry, no template to run\n";
				exit(1);
			}

			$template_string = read_whole_file($template_file);
		}
		
		return template_run($template_string, $this->keyval);
	}	

	# same as run() except the output is print()ed
	function output($templ = false) {
		print($this->run($templ));
	}

	# return the contents of the top-level sub-templates
	#
	# this does not run the sub-templates, so if you've not called tem_sub() on them, they will be blank.
	#
	# Return a hash.
	#     keys: name of top level sub-template.
	#     values: contents of said sub-template.
	function top_subs() {
		$ret = array();
		if(isset($this->sub_subs['top_level_subs'])) {
			foreach($this->sub_subs['top_level_subs'] as $name) {
				$ret[$name] = $this->get($name);
			}
		}
		return $ret;
	}
}

# Below are functions so you can use the above class without allocating or
# keeping track of it.

# get a reference to the current template object
function tem_init() { 
	if(!$GLOBALS['wfpl_template']) {
		$GLOBALS['wfpl_template'] = new tem();
	}
}
		
function tem_set($key, $value) {
	tem_init();
	$GLOBALS['wfpl_template']->set($key, $value);
}
	
function tem_get($key) {
	tem_init();
	return $GLOBALS['wfpl_template']->get($key);
}

function tem_run($templ = false) {
	tem_init();
	return $GLOBALS['wfpl_template']->run($templ);
}

function tem_sub($sub_template_name) {
	tem_init();
	$GLOBALS['wfpl_template']->sub($sub_template_name);
}

function tem_load($filename) {
	tem_init();
	$GLOBALS['wfpl_template']->load($filename);
}

function tem_output($filename = false) {
	tem_init();
	$GLOBALS['wfpl_template']->output($filename);
}



# this is used in template_run() and should be of no other use
function template_filler($matches) {
	list($tag, $enc) = explode('.', $matches[1], 2);
	$value = $GLOBALS['wfpl_template_keyval'][$tag];
	if($enc) {
		$encs = explode('.', $enc);
		foreach($encs as $enc) {
			$enc = "enc_$enc";
			if(function_exists($enc)) {
				$value = $enc($value);
			} else {
				print "ERROR: encoder function '$enc' not found.<br>\n";
				exit(1);
			}
		}
	}
	return $value;
}


# pass a template string and an associative array of the key/values and it
# returns the result.
function template_run($template, &$keyval) {
	$GLOBALS['wfpl_template_keyval'] =& $keyval;
	return preg_replace_callback(array('|<!--~([^~]*)~-->|', '|~([^~]*)~|', '|<span class="template">([^<]*)</span>|', '|<p class="template">([^<]*)</p>|'), 'template_filler', $template);
}

function tem_top_subs() {
	tem_init();
	return $GLOBALS['wfpl_template']->top_subs();
}

?>
