<?php

#  Copyright (C) 2005 Jason Woofenden
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


# This function makes it easier to put everything in functions like you should.
#
# Instead of putting everything in the global namespace and having it run when
# you include the file, put everything in functions. This function makes it so
# you can both include (require_once) the file, and call its main function in
# one easy step.

# EXAMPLE
#
# list($user, $pass) = file_run('db_password.php');
#
# the file db_password.php would be like so:
#
#     function db_password_main() {
#           return array('me', 'secret');
#     }

function file_run($filename) {
	require_once($filename);
	$func = basename($filename, '.php') . '_main';

	if(function_exists($func)) {
		return $func();
	}
}

?>
