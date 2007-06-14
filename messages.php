<?php

#  Copyright (C) 2007 Jason Woofenden
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



# This file is useful for putting message boxe(s) on the screen.
#
# Just call message("message here") whenever you have something to report.
#
# Once a template is loaded, call display_messages(). Your template should have
# a <!--~message_box start~--> section with ~message_text.html~ tag in it.
#
# If you want a divider (any text between message boxes when there are multiple
# boxes) provide a sub-template section named "message_divider" INSIDE
# "message_box" at the begining of it.
#
# If you'd like something around the group of all message boxes, you can put
# the whole thing in a sub-template section called "message_container"

# Simple example:
#
#    <!--~message_box start~-->
#        <p>~message_text.html~</p>
#    <!--~end~-->

# Full-featured example:
#
#    <!--~message_container start~-->
#         <div style="border: 2px solid red; background: #f88; padding: 5px">
#         <!--~message_box start~-->
#             <!--~message_divider start~-->
#                 <hr />
#             <!--~end~-->
#             <p style="font-size: 120%">~message_text.html~</p>
#         <!--~end~-->
#         </div>
#    <!--~end~-->

require_once('code/wfpl/template.php');

function message($msg) {
	if(!isset($GLOBALS['wfpl_messages'])) {
		$GLOBALS['wfpl_messages'] = array();
	}

	$GLOBALS['wfpl_messages'][] = $msg;
}

# if you want the messages in a template other than the default one, pass it like so:
#
# display_messages(ref($my_template));
function display_messages($template = 0) {
	$first = true;
	if($template === 0) {
		$template = &$GLOBALS['wfpl_template'];
	} else {
		$template = &$template->ref;
	}

	if($GLOBALS['wfpl_messages']) {
		foreach($GLOBALS['wfpl_messages'] as $msg) {
			if($first) {
				$first = false;
			} else {
				$template->sub('message_divider');
			}
			$template->set('message_text', $msg);
			$template->sub('message_box');
		}
		$template->sub('message_container');
	}
}

?>
