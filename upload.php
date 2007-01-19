<?php

#  Copyright (C) 2007 Jason Woofenden
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

$GLOBALS['mime_to_ext'] = array(
	'text/plain' => 'txt',
	'text/html'  => 'html',
	'image/jpeg' => 'jpg',
	'image/jpg'  => 'jpg',
	'image/gif'  => 'gif',
	'image/png'  => 'png',
	'application/pdf' => 'pdf'
);

$GLOBALS['ext_to_ext'] = array(
	'text' => 'txt',
	'jpe'  => 'jpg',
	'jpeg' => 'jpg',
	'htm'  => 'html'
);


# pass in the client's path that came from an html <input type="file"/> tag
#
# mime time used to generate extension ONLY IF it doesn't have one already.
function generate_filename($path, $mime = 'text/plain') {
	# lower case
	$filename = strtolower($path)

	# remove directories (unix, windows and mac paths)
	$last = strrpos($filename, '/');
	if($last === false) {
		$last = strrpos($filename, '\\');
	}
	if($last === false) {
		$last = strrpos($filename, ':');
	}
	if($last) {
		$filename = substr($filename, $last + 1);
	}

	# remove dots from the begning (no invisible files)
	$filename = ereg_replace('^\.*', '', $filename);

	# fix extension
	$last_dot = strrpos($filename, '.');
	if($last_dot === false) {
		#no extension
		if(isset($GLOBALS['mime_to_ext'][$mime])) {
			$filename .= '.' . $GLOBALS['mime_to_ext'][$mime];
		}
	} else {
		$basename = substr($filename, 0, $last_dot);
		$ext = substr($filename, $last_dot + 1);
		if(isset($GLOBALS['ext_to_ext'][$ext])) {
			$ext .= $GLOBALS['ext_to_ext'][$ext];
		}
		$filename = $basename . '.' . $ext;
	}
}



# Move to save folder, and return new filename.
#
# Pass in the index into the $_FILES array (the name of the html input tag) and
# the path to the folder you'd like it saved to. If path ends with a slash this
# function will generate a filename based on the client's name, otherwise it'll
# name the file that.
#
# <input type="image" name="photo">
# example: save_uploaded_image('photo', '/www/example.com/images/');
# example: save_uploaded_image('photo', '/www/example.com/images/example.jpg');

function save_uploaded_file($key, $path) {
	if(substr($path, -1) == '/') {
		$filename = $path . generate_filename($_FILES[$key]['name'], $_FILES[$key]['type']);
	} else {
		$filename = $path;
	}

	if(!move_uploaded_file($_FILES['userfile']['tmp_name'], $filename)) {
		die('file upload failed');
	}
}


# returns new filename with .png extension
function gif_to_png($filename, $new_filename = 'just change extension') {
	if($new_filename == 'just change extension') {
		$last_dot = strrpos($filename, '.');
		if($last_dot !== false) {
			$new_filename = substr($filename, 0, $last_dot);
		}
		$new_filename = $filename . '.png';

		$newfilename = $filename;
		$filename = substr($filename, 0 -4) . '.png';
	}

	$convert = '/usr/local/bin/convert';
	if(!file_exists($convert)) {
		$convert = '/usr/bin/convert';
	}
	if(!file_exists($convert)) {
		$convert = `which convert`;
	}
	if(!file_exists($convert)) {
		die("can't find imagemagick's 'convert' program");
	}
		
	$command = "$convert " . escapeshellarg($filename) . ' ' . escapeshellarg($new_filename);

	exec($command, null, $ret);
	if($ret != 0) {
		die("image conversion failed. convert did exit($ret)");
	}
	unlink($filename);
	return $new_filename;
}

# like save_uploaded_file except it converts gifs to pngs.
#
# FIXME: if destination has an extension, it should convert to that type.
function save_uploaded_image($key, $path) {
	if(substr($path, -1) != '/') {
		$filename = save_uploaded_file($key, $path);
		if(substr($filename, -4) == '.gif') {
			$filename = gif_to_png($filename);
		}
		return $filename;
	} else {
		return save_file_upload($key, $path);
	}
}

?>
