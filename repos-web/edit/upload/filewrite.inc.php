<?php
/**
 * Handles file I/O for server side edit operations.
 *
 * @package edit
 */
require_once(dirname(dirname(dirname(__FILE__))).'/conf/System.class.php');

/**
 * This constant declares that we use \r\n as default newline for new documents,
 * because that is what the HTML spec (form encoding section) recommends browsers to post.
 */
define('EDIT_DEFAULT_NEWLINE', "\r\n");

/**
 * Writes textarea contents to version controlled text file.
 * Based on the 'type' parameter value, a custom write function "editWriteNewVersion_$type"
 * is called if it exists. If no custom function is found, the posted string is written with
 * a single fwrite to the file.
 *
 * @param String $postedText the contents from the text area
 * @param String $destinationFile the local working copy file,
 * 	containing the "based-on" revision, or for new files empty
 * @param String $type a type as validated by the 'edit' plugin.
 * @return int the number of bytes in the new version of the file
 * @package edit
 */
function editWriteNewVersion(&$postedText, $destinationFile, $type) {
	$custom = 'editWriteNewVersion_'.$type;
	if ($type && function_exists($custom)) {
		// note that content must be passed by value here
		return call_user_func($custom, $postedText, $destinationFile);
	}
	return _defaultWriteNewVersion($postedText, $destinationFile);
}

/**
 * Writes $postedText as it is to $destinationFile, returns length of $postedText.
 * @see editWriteNewVersion
 */
function _defaultWriteNewVersion(&$postedText, $destinationFile) {
	$fp = fopen($destinationFile, 'w+');
	if ($fp) {
		fwrite($fp, $postedText);
		fclose($fp);
	} else {
		trigger_error("Couldn't write file contents from the submitted text.", E_USER_ERROR);
	}
	return strlen($postedText);
}

/**
 * Plaintext documents are _always_ written in UTF-8 with newline at end of file.
 * We assume that all users have text editors capable of open and resave in UTF-8.
 *
 * @param String $postedText the contents from the text area
 * @param String $destinationFile the local working copy file, currently containing the "based-on" revision
 * @param String $type a type as validated by the 'edit' plugin.
 * @return int the number of bytes in the new version of the file
 * @package edit
 */
function editWriteNewVersion_txt($postedText, $destinationFile) {
	// Already made UTF-8 by browser encoding
	// Suggest newline type for new files.
	$nl = EDIT_DEFAULT_NEWLINE;
	// Check current newline type of posted text
	if (preg_match('/(\r?\n)/', $postedText, $matches)) {
		$nl = $matches[1];
	}
	// Existing newline type has precedence
	if (file_exists($destinationFile)) {
		$existing = getNewlineType($destinationFile);
		if ($existing && $existing != $nl) {
			$nl = $existing;
			$postedText = preg_replace('/\r?\n/m', $nl, $postedText);
		}
	}
	// Always newline at end of file
	if (!strEnds($postedText, $nl)) $postedText.=$nl;
	// Write to file
	return _defaultWriteNewVersion($postedText, $destinationFile);
}

/**
 * Reads the first line of a file and returns the newline character(s).
 * @param String $existingFile absolute path
 * @return String newline if found, false if no newline in file
 */
function getNewlineType($existingFile) {
		$f = fopen($existingFile, 'r');
		$line = fgets($f);
		fclose($f);
		if (strEnds($line, "\r\n")) return "\r\n";
		if (strEnds($line, "\n")) return "\n";
		if (strEnds($line, "\r")) return "\r";
		return false;
}

?>
