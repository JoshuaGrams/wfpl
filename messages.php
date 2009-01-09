<?php

#  Copyright (C) 2007 Jason Woofenden
#
#  This program is free software: you can redistribute it and/or modify
#  it under the terms of the GNU General Public License as published by
#  the Free Software Foundation, either version 3 of the License, or
#  (at your option) any later version.
#  
#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU General Public License for more details.
#  
#  You should have received a copy of the GNU General Public License
#  along with this program.  If not, see <http://www.gnu.org/licenses/>.


# FIXME: need to rewrite session_restore_messages and call it in run.php or 
# something.


# This file is useful for putting message box(es) on the screen.
#
# Just call message("message here") whenever you have something to report.
#
# Once a template is loaded, call display_messages(). Your template should have
# a <!--~message?~--> section with a ~text:html~ tag in it.
#
# If you want a divider (any text between message boxes when there are
# multiple boxes) provide a sub-template section named "separator"
# INSIDE "message" at the begining of it.
#
# If you'd like something around the group of all message boxes, you can put
# the whole thing in a sub-template section called "messages"

# Simple example:
#
#    <!--~message?~-->
#        <p>~text.html~</p>
#    <!--~.~-->

# Full-featured example:
#
#    <!--~messages?~-->
#         <div style="border: 2px solid red; background: #f88; padding: 5px">
#         <!--~message?~-->
#             <!--~separator?~-->
#                 <hr />
#             <!--~.~-->
#             <p style="font-size: 120%">~text.html~</p>
#         <!--~.~-->
#         </div>
#    <!--~.~-->

require_once('code/wfpl/template.php');

function message($msg, &$tem = NULL) {
	if(!$tem) $tem = &$GLOBALS['wfpl_template'];

	$sub['separator'] = $tem['message_separator'];
	$sub['text'] = $message;

	$tem['messages'] = TRUE;
	$tem['message'][] = $sub;
	$tem['message_separator'] = TRUE;
}
