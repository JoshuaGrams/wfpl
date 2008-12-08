<?php

# This form requires wfpl. See: http://jasonwoof.org/wfpl

# This form was initially auto-generated. If you would like to alter the
# parameters and generate a new one try this URL:
#
# ~metaform_url~


# SETUP
<!--~opt_email_1 start~-->
# To send results by e-mail, all you have to do is set your e-mail address here:
$GLOBALS['~form_name~_form_recipient'] = "fixme@example.com";
<!--~end~--><!--~opt_db_1 start~-->
# To save results to a database, you'll need to create the ~form_name~ table
# (the file ~form_name~.sql should help with this), and create the file
# 'code/db_connect.php' which calls db_connect() see:
# code/wfpl/examples/db_connect.php
#
# if you rename any of the database fields, you'll need to update this:

define('~form_name.upper~_DB_FIELDS', '~db_fields~');
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

require_once('code/wfpl/template.php');
require_once('code/wfpl/format.php');
require_once('code/wfpl/messages.php');
require_once('code/wfpl/email.php');<!--~opt_db_2 start~-->
require_once('code/db_connect.php');<!--~end~--><!--~image_include start~-->
require_once('code/wfpl/upload.php');<!--~end~-->

function ~form_name~_get_fields() {<!--~formats start~-->
	$~name~ = format_~format~($_REQUEST['~name~']<!--~pulldown_format_extra start~-->, '~name~'<!--~end~-->);<!--~end~--><!--~image_upload start~-->
	if($_FILES['~name~'] && $_FILES['~name~']['error'] == 0) {
		$~name~ = substr(save_uploaded_image('~name~', $GLOBALS['upload_directory']), strlen($GLOBALS['upload_directory']));
	} else {
		if($_REQUEST['delete_~name~'] == 'Yes') {
			$~name~ = '';
		} else {
			$~name~ = format_filename($_REQUEST['old_~name~']);
		}
	}<!--~end~-->

	~form_name~_tem_sets(~php_fields~);

	return array(~php_fields~);
}

function ~form_name~_tem_sets(~php_fields~) {<!--~tem_sets start~-->
	tem_set('~name~', $~name~);<!--~end~-->
}
<!--~opt_listing_2 start~-->
# You may pass a "where clause" for the db query.
function ~form_name~_display_listing($where = 'order by ~always_field~ limit 100') {
	$rows = db_get_rows('~form_name~', 'id,~always_field~', $where);
	if($rows == false || count($rows) == 0) {
		tem_show('empty_listing');
		tem_show('listings');
		return false;
	}

	foreach($rows as $row) {
		list($id, $~always_field~) = $row;
		tem_set('id', $id);
		if($~always_field~ == '') {
			$~always_field~ = '--';
		}
		tem_set('~always_field~', $~always_field~);
		tem_show('listing_row');
	}
	tem_show('populated_listing');
	tem_show('listings');
	return true;
}
<!--~end~-->
function ~form_name~_main() {<!--~opt_display_1 start~-->
	if(isset($_REQUEST['~form_name~_id'])) {
		$ret = ~form_name~_display_main();
		if($ret) {
			return $ret;
		}
		tem_show('display_body');
	} else {
		$ret = ~form_name~_edit_main();
		if($ret) {
			return $ret;
		}
		tem_show('edit_body');
	}
<!--~end~--><!--~opt_display_1_else start~-->
	$ret = _~form_name~_main();
	if($ret) {
		return $ret;
	}
<!--~end~-->
	# sections displayed with tem_show() will be coppied to the main template if you have one.
	tem_show('main_body');
}<!--~opt_display_2 start~-->

function ~form_name~_display_main() {
	$id = format_int($_REQUEST['~form_name~_id']);
	unset($_REQUEST['~form_name~_id']);
	if(!$id) {
		message('Error: Broken link');
		return './~form_name~';
	}
	$row = db_get_row('~form_name~', ~form_name.upper~_DB_FIELDS, 'where id=%i', $id);
	if(!$row) {
		message('Error: Not found');
		return './~form_name~';
	}
	list(~php_fields~) = $row;
	~form_name~_tem_sets(~php_fields~);
	tem_set('id', $id);
}

function ~form_name~_edit_main() {<!--~end~--><!--~opt_display_2_else start~-->


function _~form_name~_main() {<!--~end~--><!--~opt_http_pass_2 start~-->
	# To remove password protection, just delete this block:
	if (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER'] != AUTH_USER || $_SERVER['PHP_AUTH_PW'] != AUTH_PASS) {
		header('WWW-Authenticate: Basic realm="' . AUTH_REALM . '"');
		header('HTTP/1.0 401 Unauthorized');
		echo '401 Unauthorized';
		exit;
	}
	<!--~end~--><!--~pulldowns start~-->
	pulldown('~name~', array('option 1', 'option 2', 'option 3'));
	<!--~end~--><!--~opt_db_3 start~-->
	$edit_id = format_int($_REQUEST['~form_name~_edit_id']);
	unset($_REQUEST['~form_name~_edit_id']);
	if($edit_id) {
		# add hidden field for database id of row we're editing
		tem_set('~form_name~_edit_id', $edit_id);
		tem_show('editing');
	}

	$delete_id = format_int($_REQUEST['~form_name~_delete_id']);
	unset($_REQUEST['~form_name~_delete_id']);
	if($delete_id) {
		db_delete('~form_name~', 'where id=%i', $delete_id);
		message('Entry deleted.');

		return './~form_name~.html';
	}

	if(!$edit_id) {<!--~opt_listing_1 start~-->
		if(!isset($_REQUEST['~form_name~_new']) && !isset($_REQUEST['~always_field~'])) {
			~form_name~_display_listing();
			return;
		}
		<!--~end~-->
		tem_show('new_msg');
	}<!--~end~-->

	if(isset($_REQUEST['~always_field~'])) {
		list(~php_fields~) = ~form_name~_get_fields();

		if("you're happy with the POSTed values") {<!--~opt_db_4 start~-->
			if($edit_id) {
				db_update('~form_name~', ~form_name.upper~_DB_FIELDS, ~php_fields~, 'where id=%i', $edit_id);
				message('Updated.');
			} else {
				db_insert('~form_name~', ~form_name.upper~_DB_FIELDS, ~php_fields~);
				message('Saved.');
			}<!--~end~--><!--~opt_email_2 start~-->
			if($GLOBALS['~form_name~_form_recipient'] != "fixme@example.com") {
				$to = $GLOBALS['~form_name~_form_recipient'];
				$from = $to;
				$reply_to = '';
				if(isset($_REQUEST['email']) and valid_email($_REQUEST['email'])) {
					$reply_to = $_REQUEST['email'];
					if($_REQUEST['name'] and ereg('^[a-zA-Z0-9_\' -]*$', $_REQUEST['name']) !== false) {
						$reply_to = "$_REQUEST[name] <$reply_to>";
					}
				}
				$subject = '~form_name~ form submitted';
				$message = tem_run('~form_name~.email.txt');
				$cc = '';
				$bcc = '';
				if(email($from, $to, $subject, $message, $reply_to, $cc, $bcc)) {
					message('Due to an internal error, your message could not be sent. Please try again later.');
					$error = true;
				} else {
					message('Message sent');
				}
			}<!--~end~-->
			if($error !== true) {
				return './~form_name~'; # FIXME is this the page you want to go to after successful form submission?
			}
		}
		# otherwise, we display the form again. ~form_name~_get_fields() has
		# already put the posted values back into the template engine, so they will
		# show up in the form fields. You should add some message asking people to
		# fix their entry in whatever way you require.<!--~opt_db_5 start~-->
	} elseif($edit_id) {
		# we've recieved an edit id, but no data. So we grab the values to be edited from the database
		list(~php_fields~) = db_get_row('~form_name~', ~form_name.upper~_DB_FIELDS, 'where id=%i', $edit_id);
		~form_name~_tem_sets(~php_fields~);<!--~end~-->
	} else {
		# form not submitted, you can set default values like so:
		#tem_set('~always_field~', 'Yes');
	}<!--~upload_max start~-->

	tem_set('upload_max_filesize', upload_max_filesize());<!--~end~-->

	# this has to be later in the file because it requres that ~always_field~ be set already
	if($edit_id) {
		tem_show('edit_msg');
	}

	tem_show('form');
}

?>
