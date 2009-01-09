<?php

require_once('code/wfpl/db.php');

function fields($table = NULL)
{
	if(!$table) $table = $GLOBALS['table'];
	return implode(',', array_keys($GLOBALS["fields_in_$table"]));
}

function values($table = NULL)
{
	if(!$table) $table = $GLOBALS['table'];
	$values = array();
	foreach($GLOBALS["fields_in_$table"] as $name => $format) {
		$func = "format_$format";
		if(function_exists($func)) {
			$values[] = $func($_REQUEST[$name]);
		} else $values[] = $_REQUEST[$name];
	}
	return $values;
}

function insert($table = NULL)
{
	if(!$table) $table = $GLOBALS['table'];
	db_insert($table, fields($table), values($table));
	$_REQUEST['id'] = db_get_value($table, 'LAST_INSERT_ID()');
}

function update($table = NULL)
{
	if(!$table) $table = $GLOBALS['table'];
	$id = format_int($_REQUEST['id']);
	db_update($table, fields($table), values($table), "where id=$id");
}

?>
