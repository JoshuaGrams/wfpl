<?php

# This form requires wfpl. See: http://jasonwoof.org/wfpl

# This code can send form results by e-mail and/or save them to a database. See
# the next two comments to enable either or both.

# To send results by e-mail, all you have to do is set your e-mail address here:
$GLOBALS['~form_name~_form_recipient'] = "fixme@example.com";
<!--~upload_settings start~-->
# Set this to the path to your uploads directory. It can be relative to the
# location of this script. IT MUST END WITH A SLASH
$GLOBALS['upload_directory'] = 'uploads/';
<!--~end~-->
# To save results to a database, you'll need to create the ~form_name~ table
# (the file ~form_name~.sql should help with this), and create a file called
# 'db_connect.php' which calls db_connect() see:
# code/wfpl/examples/db_connect.php

if(!file_exists('code/wfpl/template.php')) { die('This form requires <a href="http://jasonwoof.org/wfpl">wfpl</a>.'); }
require_once('code/wfpl/template.php');
require_once('code/wfpl/format.php');
require_once('code/wfpl/email.php');
require_once('code/wfpl/db.php');<!--~image_include start~-->
require_once('code/wfpl/upload.php');<!--~end~-->

function ~form_name~_get_fields() {
	$fields = array();
	<!--~formats start~-->
	$~name~ = format_~format~($_REQUEST['~name~']);<!--~end~--><!--~image_upload start~-->
	$~name~ = save_uploaded_image('~name~', $GLOBALS['upload_directory']);<!--~end~-->
	<!--~tem_sets start~-->
	tem_set('~name~', $~name~);<!--~end~-->

	return array(~php_fields~);
}

function ~form_name~() {
	$edit_id = format_int($_REQUEST['~form_name~_edit_id']);
	if($edit_id) {
		# add hidden field for database id of row we're editing
		tem_set('~form_name~_edit_id', $edit_id);
		tem_sub('editing');
	}

	$delete_id = format_int($_REQUEST['~form_name~_delete_id']);
	if($delete_id) {
		db_delete('~form_name~', 'id = %"', $delete_id);

		# FIXME: what to do after delete?
		return;
	}

	if(isset($_REQUEST['~always_field~'])) {
		list(~php_fields~) = ~form_name~_get_fields();

		if("you're happy with the POSTed values") {
			# to enable saving to a database, create a file called 'db_connect.php'
			# see: code/wfpl/examples/db_connect.php
			if(file_exists('db_connect.php')) {
				require_once('db_connect.php');
				if($edit_id) {<!--~image_db start~-->
					# uploading nothing means leaving it as is.
					if(!$~name~ && $delete_~name~ != 'Yes') {
						$~name~ = db_get_value('~form_name~', '~name~', 'id = %"', $edit_id);
					}
					<!--~end~-->
					db_update('~form_name~', '~db_fields~', ~php_fields~, 'id = %"', $edit_id);
					tem_set('did', 'updated');
				} else {
					db_insert('~form_name~', '~db_fields~', ~php_fields~);
					tem_set('did', 'saved');
				}
			}
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
	} elseif($edit_id) {
		# we've recieved an edit id, but no data. So we grab the values to be edited from the database
		list(~php_fields~) = db_get_row('~form_name~', '~db_fields~', 'id = %"', $edit_id);
		~tem_sets.tab~
	} else {
		# form not submitted, you can set default values like so
		#tem_set('~always_field~', 'Yes');
	}<!--~upload_max start~-->

	tem_set('upload_max_filesize', upload_max_filesize());<!--~end~-->

	tem_sub('form');
}

# emulate run.php if it's not being used
if(!function_exists('run_php')) {
	tem_load('~form_name~.html');
	~form_name~();
	tem_output();
}

?>
