<?php

# This form requires wfpl. If you didn't recieve wfpl along with this file,
# see: http://jasonwoof.org/wfpl

# This form was auto-generated. If you would like to alter the parameters and
# generate a new one try this URL:
#
# ~metaform_url~


# SETUP
<!--~opt_email_1 start~-->
# To send results by e-mail, all you have to do is set your e-mail address here:
$GLOBALS['~form_name~_form_recipient'] = "fixme@example.com";
<!--~end~--><!--~opt_db_1 start~-->
# To save results to a database, you'll need to create the ~form_name~ table
# (the file ~form_name~.sql should help with this), and create a file called
# 'db_connect.php' or 'code/db_connect.php' which calls db_connect() see:
# code/wfpl/examples/db_connect.php
<!--~end~--><!--~upload_settings start~-->
# Set this to the path to your uploads directory. It can be relative to the
# location of this script. IT MUST END WITH A SLASH
$GLOBALS['upload_directory'] = 'uploads/';
<!--~end~--><!--~opt_http_pass_1 start~-->
# Define the username and password required to view this form:
define('AUTH_REALM', '~form_name~ administration area');
define('AUTH_USER', 'fixme');
define('AUTH_PASS', 'fixme');
<!--~end~-->

if(!file_exists('code/wfpl/template.php')) { die('This form requires <a href="http://jasonwoof.org/wfpl">wfpl</a>.'); }
require_once('code/wfpl/template.php');
require_once('code/wfpl/format.php');
require_once('code/wfpl/messages.php');
require_once('code/wfpl/email.php');<!--~opt_db_2 start~-->
require_once('code/wfpl/db.php');<!--~end~--><!--~image_include start~-->
require_once('code/wfpl/upload.php');<!--~end~-->

function ~form_name~_get_fields() {<!--~formats start~-->
	$~name~ = format_~format~($_REQUEST['~name~']);<!--~end~--><!--~image_upload start~-->
	if($_FILE['~name~'] && $_FILE['~name~']['error'] == 0) {
		$~name~ = substr(save_uploaded_image('~name~', $GLOBALS['upload_directory']), strlen($GLOBALS['upload_directory']));
	} else {
		$~name~ = format_filename($_REQUEST['old_~name~']);
	}<!--~end~-->
	<!--~tem_sets start~-->
	tem_set('~name~', $~name~);<!--~end~-->

	return array(~php_fields~);
}

function ~form_name~() {<!--~opt_http_pass_2 start~-->
	# To remove password protection, just delete this block:
	if (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER'] != AUTH_USER || $_SERVER['PHP_AUTH_PW'] != AUTH_PASS) {
		header('WWW-Authenticate: Basic realm="' . AUTH_REALM . '"');
		header('HTTP/1.0 401 Unauthorized');
		echo '401 Unauthorized';
		exit;
	}
	<!--~end~--><!--~opt_db_3 start~-->
	$edit_id = format_int($_REQUEST['~form_name~_edit_id']);
	unset($_REQUEST['~form_name~_edit_id']);
	if($edit_id) {
		# add hidden field for database id of row we're editing
		tem_set('~form_name~_edit_id', $edit_id);
		tem_sub('editing');
		tem_sub('edit_msg');
	}

	$delete_id = format_int($_REQUEST['~form_name~_delete_id']);
	unset($_REQUEST['~form_name~_delete_id']);
	if($delete_id) {
		db_delete('~form_name~', 'where id=%i', $delete_id);
		message('Entry deleted.');

		# FIXME: what to do after delete?
		return;
	}

	if(!$edit_id && !$delet_id) {
		tem_sub('new_msg');
	}<!--~end~-->

	if(isset($_REQUEST['~always_field~'])) {
		list(~php_fields~) = ~form_name~_get_fields();

		if("you're happy with the POSTed values") {<!--~opt_db_4 start~-->
			if(file_exists($db_connector = 'db_connect.php') || file_exists($db_connector = 'code/db_connect.php')) {
				require_once($db_connector);
				if($edit_id) {<!--~image_db start~-->
					# uploading nothing means leaving it as is.
					if(!$~name~ && $delete_~name~ != 'Yes') {
						$~name~ = db_get_value('~form_name~', '~name~', 'where id=%i', $edit_id);
					}
					<!--~end~-->
					db_update('~form_name~', '~db_fields~', ~php_fields~, 'where id=%i', $edit_id);
					message('Entry updated.');
				} else {
					db_insert('~form_name~', '~db_fields~', ~php_fields~);
					message('Entry saved.');
				}
			}<!--~end~--><!--~opt_email_2 start~-->
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
				if(email($from, $to, $subject, $message, $cc, $bcc)) {
					message('Due to an internal error, your message could not be sent. Please try again later.');
					$error = true;
				}
			}<!--~end~-->
			if($error !== true) {
				tem_load('~form_name~.html');
				tem_sub('thankyou');
				tem_output();
				exit();
			}
		}
		# otherwise, we display the form again. ~form_name~_get_fields() has
		# already put the posted values back into the template engine, so they will
		# show up in the form fields. You should add some message asking people to
		# fix their entry in whatever way you require.<!--~opt_db_5 start~-->
	} elseif($edit_id) {
		# we've recieved an edit id, but no data. So we grab the values to be edited from the database
		list(~php_fields~) = db_get_row('~form_name~', '~db_fields~', 'where id=%i', $edit_id);
		~tem_sets.tab~<!--~end~-->
	} else {
		# form not submitted, you can set default values like so:
		#tem_set('~always_field~', 'Yes');
	}<!--~upload_max start~-->

	tem_set('upload_max_filesize', upload_max_filesize());<!--~end~-->

	display_messages();
	tem_sub('form');
}

?>
