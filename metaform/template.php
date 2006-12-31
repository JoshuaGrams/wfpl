<?php

# This form requires wfpl. See: http://jasonwoof.org/wfpl

# You'll need to set the following to a valid email address:
$GLOBALS['~form_name~_form_recipient'] = "fixme@example.com";

if(!file_exists('code/wfpl/template.php')) { die('This form requires <a href="http://jasonwoof.org/wfpl">wfpl</a>.'); }
require_once('code/wfpl/template.php');
require_once('code/wfpl/format.php');
require_once('code/wfpl/email.php');

function ~form_name~_get_fields() {
	$fields = array();
	<!--~formats start~-->
	$~name~ = format_~format~($_REQUEST['~name~']);
	tem_set('~name~', $~name~);
	<!--~end~-->
	return array(~php_fields~);
}

function ~form_name~() {
	if(isset($_REQUEST['~always_field~'])) {
		list(~php_fields~) = ~form_name~_get_fields();

		if("you're happy with the POSTed values") {
			# uncomment the following lines to save the values recieved to the
			# database. You can use ~form_name~.sql to create the database table.
			#require_once('db_connect.php');
			#db_insert('~form_name~', '~db_fields~', ~php_fields~);
			if($GLOBALS['~form_name~_form_recipient'] != "fixme@example.com") {
				$to = $GLOBALS['~form_name~_form_recipient'];
				if(isset($_REQUEST['email']) and valid_email($_REQUEST['email'])) {
					$from = $_REQUEST['email'];
					if($_REQUEST['name'] and ereg('^[a-zA-Z0-9_\' -]*$', $_REQUEST['name']) !== false) {
						$from = "$_REQUEST[name] <$from>";
					}
				} else {
					$from = $to;
				}
				$subject = '~form_name~ form submitted';
				$message = tem_run('~form_name~.email.txt');
				$cc = '';
				$bcc = '';
				email($from, $to, $subject, $message, $cc, $bcc);
			}
			tem_load('~form_name~.html');
			tem_sub('thankyou');
			tem_output();
			exit();
		}
		# otherwise, we display the form again. ~form_name~_get_fields() has
		# already put the posted values back into the template engine, so they will
		# show up in the form fields. You should add some message asking people to
		# fix their entry in whatever way you require.
	}

	tem_sub('form');
}

# emulate run.php if it's not being used
if(!function_exists('run_php')) {
	tem_load('~form_name~.html');
	~form_name~();
	tem_output();
}

?>
