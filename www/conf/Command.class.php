<?php
/**
 * Controls access to the command line, as wrapper for executable operations with arguments.
 * 
 * Repos PHP relies heavily on command line execution.
 * The PHP code is actually only a thin wrapper for running subversion commands
 * and system administration commands on the server.
 * 
 * All command line operations should be controlled by this class.
 * Pages normally use one of the classes that delegate to this class.
 * 
 * @see SvnOpen
 * @see SvnOpenFile
 * @see SvnEdit
 * @package conf
 */

if (!class_exists('System')) require(dirname(__FILE__).'/System.class.php');
if (!function_exists('_getConfigFolder')) require(dirname(__FILE__).'/repos.properties.php');

// the actual command execution, can be mocked
if (!function_exists('_command_run')) {
	/**
	 * @returns stdout and stderr output from the command, one array element per row. 
	 *   Last element is the return code (use array_pop to remove).
	 */
	function _command_run($cmd) {
		exec($cmd, $output, $returnvalue);
		$output[] = $returnvalue;
		return $output;
	}
}
if (!function_exists('_command_passthru')) {
	/**
	 * @return the exit code of the execution. Any messages have been passed through.
	 */
	function _command_passthru($cmd) {
		passthru($cmd, $returnvalue);
		return $returnvalue;
	}
}

/**
 * Might be nessecary to run all commands through a script that sets up a proper execution environment
 * for example locale for subversion.
 * @return wrapper script name if needed, or empty string if not needed
 */
function _command_getScriptWrapper() {
	if (System::isWindows()) {
		return '';
	}
	return _getConfigFolder().'reposrun.sh';
}

/**
 * Safely executes command line operations.
 * @package conf
 */
class Command {

	// initialize
	var $operation;
	var $args = Array(); // command line arguments, properly escaped and surrounded with quotes if needed	
	
	// after exec
	var $output;
	var $exitcode = null;
	
	/**
	 * @param String $commandName the command without arguments, for example "grep" or "ls"
	 * @return Command
	 */
	function Command($commandName, $validate=true) {
		if (!$validate) {
			if (!preg_match('/\/test\/|\/conf\/index.php/', $_SERVER['SCRIPT_FILENAME'])) {
				trigger_error("Only test scripts can run commands that are not validated", E_USER_ERROR);
			}
		} else if (System::getCommand($commandName) === false) { 
			trigger_error("Security error: command '$commandName' is not valid on this patform.", E_USER_ERROR);
		}
		// currently only the name is stored, and the command is retreived again at exec
		$this->operation = $commandName;
	}
	
	/**
	 * Adds a command line element that is an option to the command.
	 * Only ASCII strings that do _not_ come from user input may be used as options.
	 * Options can not contain whitespace.
	 *
	 * @param String $option command line element that does not need quoting or encoding.
	 * @param String $value to add a value right after the option (with a whitespace between)
	 * @param boolean $valueNeedsEscape false if it is safe to append the value without escaping
	 *  (appropriate where the value can not be altered by a user)
	 */
	function addArgOption($option, $value=null, $valueNeedsEscape=true) {
		$this->_addArgument($option);
		if (!is_null($value)) {
			if ($valueNeedsEscape) {
				$this->addArg($value);
			} else {
				$this->addArgOption($value);	
			}
		}
	}
	
	/**
	 * Command line arguments are converted to the system encoding and surrounded with quotes.
	 *
	 * @param String $argument command line element that can be surrounded in quotes
	 */
	function addArg($argument) {
		$this->_addArgument($this->_escapeArgument($argument));
	}
	
	/**
	 * Redirect STDOUT to a file instead of output to this instance.
	 * Using an output file, STDERR will not be accessible because
	 * no other output redirection will be used with the command.
	 *
	 * @param String $absolutePath full path to the writable file (or nonexisting file in writable folder)
	 * @param boolean $append set to true to use >> instead of >
	 */
	function setOutputToFile($absolutePath, $append=false) {
		// the quotes are expected by run method to detect output redirection
		$this->addArgOption($append ? '>>' : '>', $absolutePath, true);
	}	
	
	/**
	 * Append an command line argument last in the current arguments list
	 * @param The argument, should be appropriately encoded
	 *  (for example urlencoding for a new filename from input box)
	 */
	function _addArgument($nextArgument) {
		$this->args[] = $nextArgument;
	}
	
	// --- run when arguments are set ---
	
	/**
	 * Executes the command and places stdout and stderr output for access with getOutput,
	 * and the return code for getExitcode.
	 * @return int the exit code, generally 0 if successful
	 */
	function exec() {
		$cmd = Command::_getFullCommand($this->operation, $this->_getArgumentsString());
		$this->output = _command_run($cmd);
		$this->exitcode = array_pop($this->output);
		return $this->exitcode;
	}
	
	/**
	 * Passes the command output directly to browser without buffering,
	 * and also without error handling.
	 * This method should only be used for administration tasks. Useful when output is large.
	 * @return int the exit code, generally 0 if successful
	 */
	function passthru() {
		$cmd = Command::_getFullCommand($this->operation, $this->_getArgumentsString());
		$this->exitcode = _command_passthru($cmd);
		return $this->exitcode;
	}
	
	function _getArgumentsString() {
		$cmd = '';
		foreach ($this->args as $arg) {
			$cmd .= ' '.$arg;
		}
		return $cmd;
	}
	
	// --- run when exec has completed ---
	
	function getExitcode() {
		if (is_null($this->exitcode)) trigger_error('Command not executed yet', E_USER_ERROR);
		return $this->exitcode;
	}
	
	function getOutput() {
		if (is_null($this->exitcode)) trigger_error('Command not executed yet', E_USER_ERROR);
		return $this->output;
	}
	
	/**
	 * Sums up the size of the output.
	 * @return int number of bytes in the output array (note that this does not include any newlines)
	 */
	function getContentLength() {
		$size = 0;
		for ($i=0; $i<count($this->output); $i++) {
			$size += strlen($this->output[$i]);
		}
		return $size;
	}
	
	/**
	 * Makes an argument safe for command line execution.
	 *
	 * @param String $argument the plaintext argument, possibly with whitespaces
	 * @return String the escaped argument, encoded with the current system encoding
	 * @static 
	 */
	function _escapeArgument($argument) {
		if (System::isWindows()) {
			return Command::_escapeArgumentWindows($argument);
		} else {
			return Command::_escapeArgumentNix($argument);
		}
	}
	
	/**
	 * @static
	 */
	function _escapeArgumentNix($arg) {
		// Shell metacharacters are: & ; ` ' \ " | * ? ~ < > ^ ( ) [ ] { } $ \n \r (WWW Security FAQ [Stein 1999, Q37])
		// Use escapeshellcmd to make argument safe for command line
		// (double qoutes around the string escapes: *, ?, ~, ', &, <, >, |, (, )
		$arg = preg_replace('/(\s+)/',' ',$arg);
		$arg = str_replace("\\","\\\\", $arg);
		$arg = str_replace("\x0A", " ", $arg);
		$arg = str_replace("\xFF", " ", $arg);
		$arg = str_replace('"','\"', $arg);
		$arg = str_replace('$','\$', $arg);
		$arg = str_replace('`','\`', $arg);
		// ! is a metacharacter in strings, but only in interactive mode
		//$arg = str_replace('!','\!', $arg);
		return '"'.$arg.'"'; // The quotes are very important because they escape many characters that are not escaped here
		// #&;`|*?~<>^()[]{}$\, \x0A  and \xFF. ' and "
	}
	
	/**
	 * @param String $arg
	 * @return String safe for windows command line
	 * @static
	 */
	function _escapeArgumentWindows($arg) {
		$arg = preg_replace('/(\s+)/',' ',$arg);
		$arg = str_replace('"','""', $arg);
		$arg = str_replace("\\","\\\\", $arg); // double backslashes needed when inside quotes, for example in --config-dir
		// windows uses % to get variable names, which can be used to read system properties.
		if(strContains($arg, '%')) {
			$arg = Command::_escapeWindowsVariables($arg);
		}
		return '"'.$arg.'"';
	}

	/**
	 * if windows sees %abc%, it checks if abc is an environment variables. \% prevents this but adds the backslash to the string.
	 */
	function _escapeWindowsVariables($arg) {
		//$arg = str_replace('%','#', $arg);
		$i = strpos($arg, '%');
		if ($i === false) return $arg;
		$j = strpos($arg, '%', $i+1);
		if ($j === false) return $arg;
		if ($j > $i+1 && getenv(substr($arg, $i+1, $j-$i-1))) {
			return substr($arg, 0, $j).'#'.Command::_escapeWindowsVariables(substr($arg,$j+1));
		} else {
			return substr($arg, 0, $j).Command::_escapeWindowsVariables(substr($arg,$j));
		}
	}
	
	/**
	 * Compiles the exact string to run on the command line
	 */
	function _getFullCommand($commandName, $argumentsString) {
		if (escapeshellcmd($commandName) != $commandName) trigger_error("Invalid command '$commandName'.", E_USER_ERROR);
		$run = System::getCommand($commandName);
		if ($run === false) $run = $commandName; // expect that it was validated in Command class.
		// detect output redirection to file (path must be surrounded with quotes)
		$tofile = preg_match('/^.*>>?\s+".*"\s*$/', $argumentsString);
		$redirect = ''; // extra output redirection
		if (!$tofile) $redirect = ' 2>&1';
		// take care of encoding and wrapping, arguments have already been escaped
		$argumentsString = System::toShellEncoding($argumentsString);
		$wrapper = _command_getScriptWrapper();
		if (strlen($wrapper)>0) {
			// make one argument (to the wrapper) of the entire command
			// the arguments in the argumentsString are already escaped and surrounded with quoutes where needed
			// existing single quotes must be adapted for shell
			$run = " '".$run.' '.str_replace("'","'\\''",$argumentsString).$redirect."'";
		} else {
			$run .= ' '.$argumentsString;
		}
		// don't redirect output if > or >>, because that is expected to be stdout
		return "$wrapper$run$redirect";
	}
	
}

?>