<?php

function read_whole_file($name) {
	$fd = fopen($name, 'r');
	if($fd === false) {
		die("Failed to read file: '$name'");
	}
	$temp = fread($fd, filesize($name));
	fclose($fd);
	return $temp;
}

?>
