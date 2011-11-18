<?php
require "xmlConflictHandler.php";
/**
 * Tries to automatically resolve conflicts according to repos logic.
 *
 * This is done at the server, so the file with the conflict markers is the common file.
 * A user produced a diff, probably in a branch, that was merged into the common file.
 * Which means that the conflict markers are as follows:
<<<<<<< .working
The value that was in the common file, but did not match the "-" line in the user's file
=======
The value from the "+" line in the user's file.
>>>>>>> .merge-right.r2
 * 
 * @param String $file the local plaintext file that contains the conflict markers
 *  
 * @param unknown_type $mine
 * @param unknown_type $older
 * @param unknown_type $yours
 * @param array[String] $logChoices an array to append output messages to, telling the user what has been done
 * 
 * @return true if the conflict could be resolved, false if it must be handled by the user.
 *  Note that this function does not mark the file as resolved, so the caller should run the svn command.
 */
function handleConflict($file, &$logChoices) { //, $mine, $older, $yours, &$logChoices) {
	// validate
	
	// call all available handlers
	if (handleConflict_excel2003xml($file, $logChoices)) return true;	
}

/**
 * Enter description here...
 *
 * @param String $file the conflicting file
 * @return true if conflict could be resolved
 */
function handleConflict_excel2003xml($file, &$log) {
	// verify that it is an excel file, return false otherwise
	
	// resolve conflict, return
	$contents = file($file);
	$result = resolveConflicts($contents, $log);
	$fh = fopen($file, 'w');
	fwrite($fh, implode("\n", $contents));
	fclose($fh);
	//print_r(file($file));
	return $result;
}





?>