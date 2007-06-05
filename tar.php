<?php

#  Copyright (C) 2006 Jason Woofenden
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


# This file is for making a tar archive out of some strings you pass. See
# make_tar()


# create a file (including contents)
function write_file($name, $data) {
	$fd = fopen($name, 'w');
	if($fd === false) {
		die("Failed to open file: '$name' for writing");
	}
	$temp = fwrite($fd, $data);
	fclose($fd);
	return $temp;
}


# create (and output) a tar archive. Don't put in any symbols in filenames.
#
# parameters:
#    $dirname: the name of the tar file (sans "tgz"). Also the name of the directory within.
#    $files: a hash. The keys are the filenames, the values the file data
#    $extra: (optional) a function to be called right before tar-ing.
function make_tar($dirname, $files, $extra = '') {
	$tmpdir = '/tmp/make_tar';
	$dirname = ereg_replace('[^a-z0-9_-]', '', $dirname);
	if($dirname == '') $dirname = 'foo';
	if(!file_exists($tmpdir)) {
		mkdir($tmpdir);
	}
	mkdir("$tmpdir/$dirname");
	foreach($files as $filename => $file_data) {
		if(substr($filename, -3) == ' ->') {
			$filename = substr($filename, 0, -3);
			$link = true;
		} else {
			$link = false;
		}
		$filename_fixed = ereg_replace('[^a-zA-Z0-9_.-]', '', $filename);
		if($filename != $filename_fixed) {
			die("Invalid filename for tar archive");
		}
		if($link) {
			$target = ereg_replace('[^a-zA-Z0-9_./-]', '', $file_data);
			system("/bin/ln -s $file_data \"$tmpdir/$dirname/$filename\"");
		} else {
			write_file("$tmpdir/$dirname/$filename", $file_data);
		}
	}

	if(function_exists($extra)) {
		$extra("$tmpdir/$dirname");
	}

	header("Content-type: application/x-gzip");
	passthru("tar -C $tmpdir -czf - $dirname/");
	system("/bin/rm -rf '$tmpdir/$dirname'");
}

# like make_tar above, except it includes a copy of code/wfpl
function make_wfpl_tar($dirname, $files) {
	make_tar($dirname, $files, 'add_wfpl_dir');
}

function add_wfpl_dir($dir) {
	mkdir("$dir/code");
	system("rsync -plr --exclude=\".git\" --exclude=\"*.swp\" 'code/wfpl/' '$dir/code/wfpl/'", $return_code);
	if($return_code != 0) {
		die("ERROR: while trying to copy wfpl into archive: rsync returned $return_code");
	}
}
