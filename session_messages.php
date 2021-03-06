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


# require_once() this file instead of messages.php if you'd like to store
# save/restore messages in the session accross redirect()s. That's all you have
# to do. These functions are called when needed (from redirect() and
# display_messages()) if they are defined.

# see messages.php for documentation on how to use it.

require_once('code/wfpl/session.php');
require_once('code/wfpl/string_array.php');
require_once('code/wfpl/messages.php');

function session_save_messages() {
	if(!isset($GLOBALS['wfpl_messages'])) {
		return;
	}
	if(!is_array($GLOBALS['wfpl_messages'])) {
		return;
	}

	init_session();
	session_set('wfpl_messages', array_to_string($GLOBALS['wfpl_messages']));
}

function session_restore_messages() {
	if(!session_exists()) {
		return false;
	}
	$messages = session_get('wfpl_messages');
	if($messages !== false) {
		$messages = string_to_array($messages);
		if(!(isset($GLOBALS['wfpl_messages']) && is_array($GLOBALS['wfpl_messages']))) {
			$GLOBALS['wfpl_messages'] = array();
		}
		# messages from the previous run happened first
		$GLOBALS['wfpl_messages'] = array_merge($messages, $GLOBALS['wfpl_messages']);

	}
	session_clear('wfpl_messages');
}
