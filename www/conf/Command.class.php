<?php
/**
 * Controls access to the command line, as wrapper for executable operations with arguments.
 * 
 * Repos PHP relies heavily on command line execution.
 * The PHP code is actually only a thin wrapper for running subversion commands
 * and system administration commands on the server.
 * 
 * All command line operations should be controlled by this class or one of its subclasses.
 * 
 * @package conf
 */

// include repos.properties.php only if repos_runCommand is not defined (to allow mocks for testing)
if (function_exists('repos_runCommand')) require(dirname(__FILE__).'/repos.properties.php');

class ReposCommand {
	
	function ReposCommand($commandName) {
		
	}
	
	// --- run anytime ----
	
	function getId() {
		
	}
	
	// --- run when arguments are set ---
	
	function exec() {
		
	}
	
	// --- run when exec has completed ---
	
	function show() {
		
	}
	
	function getExitCode() {
		
	}
	
	function getOutput() {
		
	}
	
	function getContentLength() {
		
	}
	
	
	
}

?>