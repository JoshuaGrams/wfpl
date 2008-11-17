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


require_once('code/wfpl/template.php');
require_once('code/wfpl/encode.php');
require_once('code/wfpl/session.php');
require_once('code/wfpl/upload.php'); # FIXME for path_to() which should be somewhere else

# This function is for making an uploader with a progress bar.
#
# Parameter: (optional)
#     progress_url: URL javascript should use to get progress updates (defaults to this_url() with the query string replaced with ?wfpl_upload_progress=FILENAME (where FILENAME is the first parameter.))
#
# You must also set $GLOBALS['wfpl_uploader_port'] to an available port for the upload receiver to run on.
#
# Returns: (an array containing)
#     html
#     css
#     javascript
#     filename

function uploader($progress_url = '') {
	if(!$filename) {
		$filename = strtolower(session_generate_key());
	}
	if(!$progress_url) {
		$progress_url = this_url();
		$q = strpos($progress_url, '?');
		if($q) {
			$progress_url = substr($progress_url, 0, $q);
		}
		$progress_url .= '?wfpl_upload_progress=' . enc_url_val($filename);
	}
	if(!$GLOBALS['wfpl_uploader_host']) {
		$GLOBALS['wfpl_uploader_host'] = this_host();
	}

	$html = new tem();
	$html->load('code/wfpl/uploader/uploader.html');
	$html->set('filename', $filename);
	$html->set('host', $GLOBALS['wfpl_uploader_host']);
	$html->set('port', $GLOBALS['wfpl_uploader_port']);
	$html->show('main');
	$html = $html->get('main');

	$css = read_whole_file('code/wfpl/uploader/uploader.css');

	$javascript = new tem();
	$javascript->load('code/wfpl/uploader/progress.js');
	$javascript->set('url', $progress_url);
	$javascript = $javascript->run();

	uploader_daemon_start($GLOBALS['wfpl_uploader_port']);

	return array($html, $css, $javascript, $filename);
}

function uploader_move($tmp_filename, $filename) {
	$tmp_path = $GLOBALS['wfpl_uploader_path'] . '/partial/' . $tmp_filename;
	$out_path = $GLOBALS['wfpl_uploader_path'] . '/' . $filename;
	unlink($GLOBALS['wfpl_uploader_path'] . '/progress/' . $tmp_filename);
	rename($tmp_path, $out_path);
}

# start a daemon to accept file uploads and give progress indicators
# if the port is used (eg if the daemon is already running) this will do nothing.
function uploader_daemon_start($port) {
	exec(path_to('tcpserver') . " -q -R -H -llocalhost 0 $port " . path_to('perl') . ' code/wfpl/uploader/daemon.pl ' . $GLOBALS['wfpl_uploader_path'] . ' >/dev/null 2>/dev/null < /dev/null &');
}

/* call this to respond to the javascript async request for progress on the upload */
function wfpl_uploader_progress() {
	if(!isset($_REQUEST['wfpl_upload_progress'])) {
		return;
	}

	# allow this script to run for 8 hours
	set_time_limit(28800);

	$file = $_REQUEST['wfpl_upload_progress'];
	$file = strtolower($file);
	$file = ereg_replace('[^a-z0-9.-]', '_', $file);
	$file = ereg_replace('^[.-]', '_', $file);
	$file = $GLOBALS['wfpl_uploader_path'] . "/progress/$file";
	
	$waited = 0;
	while(!file_exists($file)) {
		usleep(500000);
		++$waited;
		if($waited > 100) {
			return;
		}
	}

	$progress_sent = 0;
	while(true) {
		clearstatcache();
		$stats = stat($file);
		if($stats !== false) {
			$progress = $stats['size'];
			if($progress > $progress_sent) {
				print(substr('............................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................', 0, $progress - $progress_sent));
				flush();
				$progress_sent = $progress;
				if($progress == 1000) {
					return;
				}
			}
		}
		usleep(500000); # wait half a second
	}
}
