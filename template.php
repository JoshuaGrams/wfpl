<?php

# This is a simple template-handling system.  You pass it a big data 
# structure with key/value pairs, and a template string to fill out.
#
# Within a template, it recognizes tags delimited by tildes (~).  When 
# the template is filled out, the tags will be replaced with the 
# corresponding data.  Tags ending with '?' and '.' mark the start and 
# end of a sub-template (for optional or repeated text), and can be 
# wrapped in HTML comments (which will be removed along with the tags
# when the template is filled out).

require_once('code/wfpl/encode.php');
require_once('code/wfpl/file.php');
require_once('code/wfpl/misc.php');


# Public functions
# ----------------

function template($data, $template) {
	return fill_template($data, parse_template($template));
}

function template_file($data, $filename) {
	return template($data, file_get_contents($filename));
}

# First we take the template string and break it up into an array 
# of strings and sub-arrays.  The first item in a sub-array is the tag.

function parse_template($string) {
	# Don't change any of the reference code!  Since PHP
	# references point to the variable, not the data, it
	# really does have to be written exactly like this.
	$stack[] = array(); $tem = &last($stack);
	# note: for some reason this captures '<!--' but not '-->'.
	$pieces = preg_split("/(<!--)?(~[^~]*~)(?(1)-->)/", $string, -1, PREG_SPLIT_DELIM_CAPTURE);
	foreach($pieces as $piece) {
		if($piece[0] == '~') {
			$tag = preg_replace('/~([^?.]*)[?.]?~/', "$1", $piece);
			$last = substr($piece, -2, 1);
			if($last == '?') {
				$stack[] = array($tag);
				$tem[] = &last($stack);
				$tem = &last($stack);
			} elseif($last == '.') {
				$cur = $stack[count($stack)-1][0];
				if($tag && $tag != $cur) {
				   	die("Invalid template: tried to close $tag, but $cur is current.");
				}
				array_pop($stack); $tem = &last($stack);
			} else {
				$tem[] = array($tag);
			}
		} elseif($piece != '<!--') $tem[] = $piece;
	}
	return $tem;
}

# Then we do a depth-first traversal of the template tree,
# replacing all tags with the data values.

function fill_template($data, $template, $context = NULL) {
	$context[] = $data;
	foreach($template as $tem) {
		if(is_string($tem)) $output .= $tem;
		else {
			$tag = array_shift($tem);
			if(count($tem)) {  # sub-template
				$value = tem_get($tag, $context);
				foreach(template_rows($value) as $row) {
					$output .= fill_template($row, $tem, $context);
				}
			} else $output .= tem_get_enc($tag, $context);
		}
	}
	return $output;
}


# Replace values in main with sub-templates from tem.
function merge_templates($main, $tem) {
}



# Internal functions
# ------------------
#
# Of course, nothing stops you from using these, but I don't know
# why you would want to...


# Convert value to array of arrays of key/value pairs for use in
# sub-template expansion.  This adds flexibility to how you represent
# your data.
function template_rows($value) {
	if(is_array($value)) {
		# numeric keys, is already array of arrays -- expand sub-template for each.
		if(array_key_exists(0, $value)) return $value;
		# key/value pairs -- expand sub-template once.
		else return array($value);
	} elseif($value) {
		# value -- expand sub-template once using only parent values
		return array(array());
	} else {
		# empty value -- don't expand sub-template
		return array();
	}
}

function tem_get($key, $context)
{
	while($context) {
		$data = array_pop($context);
		if(array_key_exists($key, $data)) return $data[$key];
	}
}

function tem_get_enc($tag, $context)
{
	$encodings = explode(':', $tag);
	$key = array_shift($encodings);

	$value = tem_get($key, $context);
	if(is_string($value)) {
		foreach($encodings as $encoding) {
			$func = "enc_$encoding";
			if(function_exists($func)) $value = $func($value, $key);
			else die("ERROR: encoder function '$func' not found.<br>\n");
		}
		return $value;
	}
}

?>
