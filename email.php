<?php

#  Copyright (C) 2006 Jason Woofenden
#
#  This file is part of wfpl.
#
#  wfpl is free software; you can redistribute it and/or modify it
#  under the terms of the GNU General Public License as published by
#  the Free Software Foundation; either version 2, or (at your option)
#  any later version.
#
#  wfpl is distributed in the hope that it will be useful, but
#  WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
#  General Public License for more details.
#
#  You should have received a copy of the GNU General Public License
#  along with wfpl; see the file COPYING.  If not, write to the
#  Free Software Foundation, Inc., 59 Temple Place - Suite 330, Boston,
#  MA 02111-1307, USA.



# This function will SAFELY send e-mail (ie you can pass parameters to it
# that you got from a form and not worry about header injection.) Weird
# characters are stripped from the $subject and from the real names, but e-mail
# addresses are not modified at all. If an e-mail address is invalid this
# function will return non-zero.

# You cannot pass more than one address to any parameter
# address fields (from, to, cc, bcc) can be in either of these formats:
# 1) me@foo.com  2) Me Who <me@foo.com>
# returns 0 on success
function email($from, $to, $subject, $message, $cc = '', $bcc = '') {
	if(($from = email_header($from)) === false) { return 1; }
	if(($to   = email_header($to))   === false) { return 2; }
	if(($cc   = email_header($cc))   === false) { return 3; }
	if(($bcc  = email_header($bcc))  === false) { return 4; }
	if($from == '') { return 1; }
	if($to   == '') { return 2; }

	#FIXME should allow many more characters here
	$subject = ereg_replace("[^a-zA-Z _'-]", '_', $subject);

	$headers = "From: $from";
	if($cc) {
		$headers .= "\r\nCC: $cc";
	}
	if($bcc) {
		$headers .= "\r\nBCC: $bcc";
	}
	if(mail($to, $subject, $message, $headers)) {
		return 0;
	} else {
		return 5;
	}
}
	


# This function probably isn't useful appart from writing functions like email() above.

# addr can be in these formats:
# 1) me@foo.com  2) Me Who <me@foo.com>  3)
# returns false, or a valid format 2 above, except if input is an empty string, it'll return an empty string
function email_header($addr) {
	if($addr == '') {
		return '';
	}

	if(ereg('<.*>$', $addr) !== false) {
		# format 2
		list($name, $email) = split('<', $addr);
		$name = rtrim($name);
		$email = substr($email, 0, -1); # get rid of the '>' at the end
	} else {
		$email = $addr;
		$name = ereg_replace('@.*', '', $addr);
	}

	if(!valid_email($email)) {
		return false;
	}

	#FIXME should allow many more characters here
	$name = ereg_replace("[^a-zA-Z _'-]", '_', $name);

	return $name . ' <' . $email . '>';
}
	


# return true if e-mail is formatted like a valid email address
function valid_email($email) {
	return ereg('^[0-9a-zA-Z_~.-]+@[0-9a-zA-Z.-]+\.[a-z]+$', $email) !== false;
}
