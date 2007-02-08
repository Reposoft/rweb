<?php
/**
 *
 *
 * @package lib
 */

function convertGetCommand() {
	$installed = dirname(__FILE__).'/installed/';
	if (file_exists($installed)) {
		return '"'.$installed.'convert"';
	}
	// expect imagemagick in path
	return 'converta';
}

?>
