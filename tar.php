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
function make_tar($dirname, $files) {
	$tmpdir = '/tmp/make_tar';
	$dirname = ereg_replace('[^a-z0-9_-]', '', $dirname);
	if($dirname == '') $dirname = 'foo';
	if(!file_exists($tmpdir)) {
		mkdir($tmpdir);
	}
	mkdir("$tmpdir/$dirname");
	foreach($files as $filename => $file_data) {
		$filename_fixed = ereg_replace('[^a-zA-Z0-9_.-]', '', $filename);
		if($filename != $filename_fixed) {
			die("Invalid filename for tar archive");
		}
		write_file("$tmpdir/$dirname/$filename", $file_data);
	}
	header("Content-type: application/x-gzip");
	passthru("tar -C $tmpdir -czf - $dirname/");
	foreach($files as $filename => $file_data) {
		unlink("$tmpdir/$dirname/$filename");
	}
	rmdir("$tmpdir/$dirname");
}
