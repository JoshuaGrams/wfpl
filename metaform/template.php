<?php

require_once('code/wfpl/template.php');
require_once('code/wfpl/format.php');
#require_once('code/wfpl/db.php'); # fixme

function ~form_name~_get_fields() {
	$GLOBALS['~form_name~_fields'] = array();<!--~formats start~-->

	$value = format_~format~($_REQUEST['~name~']);
	tem_set('~name~', $value);<!--~db_field start~-->
	$GLOBALS['~form_name~_fields'][] = $value;<!--~end~--><!--~end~-->
}
	

if(isset($_REQUEST['~always_field~'])) {
	~form_name~_get_fields();

	if("you're happy with the values") {
		#db_insert('~form_name~', '~db_fields~', $GLOBALS['~form_name~_fields']); # fixme
		header('Content-type: text/plain');
		print "e-mailing this: \n\n";
		tem_output('~form_name~.email.txt');
		exit();
	}
}

tem_output('~form_name~.html');

?>
