<?php

# Copyright (C) 2005 Jason Woofenden  PUBLIC DOMAIN


# This file demonstrates, documents and tests the template system (along with
# its html template.)

# First we'll need the functions in wfpl/template.php:
require_once('code/wfpl/template.php');
# Always use the exact require_once statement above to get access to these
# functions. You should have in each directory of your projects a copy of wfpl
# or a symbolic link to it. This allows there to be different versions of wfpl
# without changing the php for the site.

# Now grab the template (you might want to take a look at this file)
tem_load('tem_test.php.html');
# This creates a template object to store all sorts of things, and then reads
# in the template file and scans through it for sub-templates. Sub templates
# are parts of the template that may appear any number of times in the output
# (even zero times) such as a table row meant to hold a database record.

# This is probably not the best example, but this template contains tables for
# login, and for displaying some fake database content. For this silly example
# I decide which I'm going to display by checking if the user has submitted a
# username.
if(!isset($_REQUEST['user'])) {
	# tem_set() gives a key/value pair to template.php. When the template is
	# output (or sub-templates are run with tem_sub) any occurences of ~user~
	# will be replaced with 'bert'.
	tem_set('user', 'bert');

	# The template file contains a sub-template called 'login'. By default,
	# sub-templates do not display at all. They display once for each time you
	# call tem_sub()
	tem_sub('login');

	# This runs the template and prints the output. Running the template is
	# simply replacing all ~key~ tags with the associated value. The values are
	# set with tem_set() and tem_sub().
	tem_output();

	exit(0);
}

# Below is an example of using a sub-sub-template many times

# first set some values to be displayed in the row:
tem_set('foo', '*&^@$<>"');
tem_set('bar', 'one*&^@$<>"');

# Now run the row. This runs the sub-template for the row, and appends the data
# for the 'foobar_row' entry in the main key/value list. 
tem_sub('foobar_row');

# and a couple more times:
tem_set('foo', '"""""****"""""');
tem_set('bar', 'two*&^"');
tem_sub('foobar_row');
tem_set('foo', '<<<<<<&&&&&&&&amp;>>>>>');
tem_set('bar', 'threeeeeeee*&^@$<>"eeeeeeeeeeee');
tem_sub('foobar_row');

# Now we have a 'foobar_row' in the main keyval array with three rows of html in it.

# in the template foobar_row is within a bigger sub-template called
# 'foobar_table'. The only reason for this is so that we can have that table
# not display at all when we're displaying the login. This is a silly use of
# the templates, but I wanted to demonstrate and test a simple use of a
# sub-template within a sub-template.
tem_sub('foobar_table');


# Now run the main template (the body of the template file)
tem_output();


?>
