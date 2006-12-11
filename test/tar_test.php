<?php

require_once('code/wfpl/tar.php');

# Unfortunately, I don't know how to set the suggested filename for downloads,
# so you'll have to deal with that yourself.
make_tar('foo', array(
	'foo.txt' => 'foo two three four',
	'README' => 'this is a test...',
	'Makefile' => 'all: or_nothing'));

?>
