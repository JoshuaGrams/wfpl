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


# Public functions
# ----------------

function template($data, $template) {
	return fill_template($data, parse_template($template));
}

function template_file($data, $filename) {
	return template($data, file_get_contents($filename));
}

# First we take the template string and break it up into an array 
# of strings and sub-arrays.  The first element of each array is 
# the name of that sub-template.

function parse_template($string) {
	$a[] = array(); $tem = &$a[count($a)-1];
	# note: for some reason this captures '<!--' but not '-->'.
	$pieces = preg_split("/(<!--)?(~[^~]*[?.]~)(?(1)-->)/", $string, -1, PREG_SPLIT_DELIM_CAPTURE);
	foreach($pieces as $piece) {
		if($piece[0] == '~') {
			$tag = substr($piece, 1, -2);
			if(substr($piece, -2, 1) == '?') {
				$a[] = array($tag);
				$tem[] = &$a[count($a)-1];
				$tem = &$a[count($a)-1];
			} else {
				array_pop($a); $tem = &$a[count($a)-1];
			}
		} elseif($piece != '<!--') $tem[] = $piece;
	}
	return $tem;
}

# Then we do a depth-first traversal of the template tree,
# replacing all tags with the data values.

function fill_template($data, $template, $context = NULL) {
	$context = new Context($context, $data);
	foreach($template as $tem) {
		if(is_string($tem)) {
			$output .= preg_replace_callback('/~([^~]*)~/', array($context, 'get_enc'), $tem);
		} else {
			$key = array_shift($tem);
			$value = $context->get($key);
			foreach(template_rows($value) as $row) {
				$output .= fill_template($row, $tem, $context);
			}
		}
	}
	return $output;
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

# Since PHP doesn't have closures, we wrap the context array in a class.
class Context {
	# a context is a stack of namespaces (arrays of key/value pairs).
	var $ctx;

	# new (inner) namespaces are added to the beginning.
	function Context($context, $data) {
		if($context) $this->ctx = $context->ctx; else $this->ctx = array();
		array_unshift($this->ctx, $data);
  	}

	# we search from innermost to outermost namespace.
	function get($key)
	{
		foreach($this->ctx as $data) {
			if(array_key_exists($key, $data)) return $data[$key];
		}
	}

	# this is a callback for preg_replace_callback('/~([^~]*)~/', ...);
	# it takes a key[:enc]* tag, looks up the value, and applies the encoding(s).
	function get_enc($match)
	{
		# tag is the text matched by the first sub-expression
		$encodings = explode(':', $match[1]);
		$key = array_shift($encodings);

		$value = $this->get($key);
		if(is_string($value)) {
			foreach($encodings as $encoding) {
				$func = "enc_$encoding";
				if(function_exists($func)) $value = $func($value, $key);
				else die("ERROR: encoder function '$func' not found.<br>\n");
			}
			return $value;
		}
	}
}

?>
