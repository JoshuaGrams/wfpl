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
		return false;
	}

	return $filename;
}

# this function exists to deal with cases where binaries are installed in very
# standard places (like /usr/bin or /usr/local bin) and PHP's PATH environment
# variable is not set appropriately.
function path_to($prog, $or_die = true) {
	$prog = ereg_replace('[^a-zA-Z0-9_.-]', '', $prog);
	$prog = ereg_replace('^[-.]*', '', $prog);
	if($prog == '') {
		die('Invalid argument to path_to()');
	}

	if(!isset($GLOBALS["path_to_$prog"])) {
		$ret = _path_to($prog, $or_die);
		if($ret == false) {
			return false;
		}
		$GLOBALS["path_to_$prog"] = $ret;
	}

	return $GLOBALS["path_to_$prog"];
}
	
function _path_to($prog, $or_die) {
	# relies on PHP's short-circuit mechanism
	if(file_exists($path = "/usr/local/bin/$prog") ||
	   file_exists($path = "/usr/bin/$prog") ||
	   ($path = `which $prog` != '' && file_exists($path))) {
		return $path;
	} else {
		if($or_die) {
			die("Failed to locate '$prog' executable.");
		}
		return false;
	}
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

	$convert = path_to('convert');

	$command = "$convert " . escapeshellarg($filename) . ' ' . escapeshellarg($new_filename);

	exec($command, $dummy, $ret);
	if($ret != 0) {
		die("image conversion failed. convert did exit($ret)");
	}
	unlink($filename);
	return $new_filename;
}

# make a thumbnail image.
#
# Thumbnail will have the same filename, except "_thumb" will be added right
# before the dot preceding the extension. so foo.png yields foo_thumb.png
#
# Thumbnail will retain aspect ratio, and be either $max_width wide or
# $max_height tall (or, if the aspect is just right, both)
function make_thumbnail($filename, $max_width = '70', $max_height = '70') {
	$last_dot = strrpos($filename, '.');
	if($last_dot === false) {
		die("couldn't make thumbnail because filename has no extension.");
	}

	$thumb = substr($filename, 0, $last_dot);
	$thumb .= '_thumb';
	$thumb .= substr($filename, $last_dot);

	$convert = path_to('convert');

	# can't be too careful
	$max_width = ereg_replace('[^0-9]', '', $max_width);
	if($max_width == '') {
		$max_width = '70';
	}
	$max_height = ereg_replace('[^0-9]', '', $max_height);
	if($max_height == '') {
		$max_height = '70';
	}
	
	$command = "$convert -geometry ${max_width}x$max_height " . escapeshellarg($filename) . ' ' . escapeshellarg($thumb);

	exec($command, $dummy, $ret);
	if($ret != 0) {
		die("Thumbnail creation failed. Convert called exit($ret)");
	}

	return $thumb;
}

# Argument: path to image file
#
# Return: string in the format WIDTHxHEIGHT, or boolean false
#
# Example: image_dimensions('uploads/foo.png'); ==> "124x58"
function image_dimensions($image) {
	$identify = path_to('identify');
	$command = "$identify -format '%wx%h' " . escapeshellarg($image);
	$dimensions = rtrim(`$command`);
	if($dimensions == '') {
		return false;
	} else {
		return $dimensions;
	}
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
