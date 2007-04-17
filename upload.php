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


# This file contains functions to accept files being uplodad with the <input
# type="file" name="foo"> control.
#
# ########
# # HTML #
# ########
# 
# First, your <form> tag must contain this attribute:
# enctype="multipart/form-data"
# 
# Second, you should indicate to the browser the maximum file size (in bytes)
# allowed for uploads with a hidden input field named MAX_FILE_SIZE. You can
# use the function upload_max_filesize() to get the maximum allowed size that
# PHP will accept.
# 
# Example:
# 
# <form action="foo.php" enctype="multipart/form-data" method="post">
# <input type="hidden" name="MAX_FILE_SIZE" value="2097152" />
# <input type="file" name="photo" />
# <input type="submit" name="save" value="Save" />
# </form>
# 
# #######
# # PHP #
# #######
#
# In the php code you can use either save_uploaded_file('photo',
# 'upload/dir/'); or save_uploaded_image('photo', 'upload/dir/'); The only
# difference being that save_uploaded_image() will convert gifs to PNGs.
# 
# Both functions will generate a reasonable filename based on the filename
# passed from the browser (and on the mime-type if there's no extension) unless
# you specify a filename. See the comments above the function definitions below
# for more details.
# 
# In a future version of save_uploaded_image(), when you specify a filename, it
# will check the image type of the uploaded image, and if it's different than
# the type you specified, it will convert the image for you.



$GLOBALS['mime_to_ext'] = array(
	'text/plain' => 'txt',
	'text/html'  => 'html',
	'image/jpeg' => 'jpg',
	'image/jpe' => 'jpg',
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

# return the upload_max_filesize in bytes
function upload_max_filesize() {
	$max = ini_get('upload_max_filesize');
	$postfix = strtolower(substr($max, -1));
	if($postfix == 'g') {
		return substr($max, 0, -1) * 1073741824;
	} elseif($postfix == 'm') {
		return substr($max, 0, -1) * 1048576;
	} elseif ($postfix == 'k') {
		return substr($max, 0, -1) * 1024;
	} else {
		return $max;
	}
}


# pass in the client's path that came from an html <input type="file"/> tag
#
# mime time used to generate extension ONLY IF it doesn't have one already.
function generate_filename($path, $mime = 'text/plain') {
	# lower case
	$filename = strtolower($path);

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

	# replace symbols with underscores
	$filename = ereg_replace('[^a-z0-9_.]', '_', $filename);

	# remove dots from the beginning (no invisible files)
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
	return $filename;
}



# Move uploaded file, and return the new filename.
#
# Pass in the index into the $_FILES array (the name of the html input tag) and
# the path to the folder you'd like it saved to. If path ends with a slash this
# function will generate a filename based on the client's name, otherwise it'll
# name the file that.
#
# example: save_uploaded_file('pdf', 'uploaded_pdfs/');
# example: save_uploaded_file('resume', "/www/example.com/remumes/$user_id.txt");
function save_uploaded_file($key, $path) {
	if(substr($path, -1) == '/') {
		$filename = $path . generate_filename($_FILES[$key]['name'], $_FILES[$key]['type']);
	} else {
		$filename = $path;
	}

	if(!move_uploaded_file($_FILES[$key]['tmp_name'], $filename)) {
		die('file upload failed');
	}

	return $filename;
}


# returns new filename with .png extension
function gif_to_png($filename, $new_filename = 'just change extension') {
	if($new_filename == 'just change extension') {
		$new_filename = $filename;
		$last_dot = strrpos($new_filename, '.');
		if($last_dot !== false) {
			$new_filename = substr($new_filename, 0, $last_dot);
		}
		$new_filename .= '.png';
	}

	$convert = '/usr/local/bin/convert';
	if(!file_exists($convert)) {
		$convert = '/usr/bin/convert';
	}
	if(!file_exists($convert)) {
		$convert = `which convert`;
	}
	if($convert == '' || !file_exists($convert)) {
		die("can't find imagemagick's 'convert' program");
	}
		
	$command = "$convert " . escapeshellarg($filename) . ' ' . escapeshellarg($new_filename);

	exec($command, $dummy, $ret);
	if($ret != 0) {
		die("image conversion failed. convert did exit($ret)");
	}
	unlink($filename);
	return $new_filename;
}

# like save_uploaded_file() (above) except it converts gifs to pngs.
#
# FIXME: if a filename is passed in the end of path, we should check if the file type matches, and if not run convert.
function save_uploaded_image($key, $path) {
	if(substr($path, -1) == '/') {
		$filename = save_uploaded_file($key, $path);
		if(substr($filename, -4) == '.gif') {
			$filename = gif_to_png($filename);
		}
		return $filename;
	} else {
		return save_uploaded_file($key, $path);
	}
}

?>
