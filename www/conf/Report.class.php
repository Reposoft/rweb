<?php
/**
 * Repos administrative reports.
 *
 * A common interface for scripts that do tests, configuration or sysadmin tasks.
 *
 * Does not do output buffering, because for slow operations we want to report progress.
 * 
 * Passing an array as message means print as block (with <pre> tag in html)
 */
// TODO count X output lines (info+), X warnings, X fails, X exception
// TODO count fatal() as exception
// TODO CSS class for pre tags

require_once(dirname(__FILE__).'/repos.properties.php');

class Report {

	var $hasErrors = false; // error events are recorded
	var $offline;

	function Report($title='Repos system report', $category='') {
		$this->offline = isOffline();
		if ($this->offline) {
			$this->_linestart();
			$this->_output("---- $title ----");
			$this->_lineend();
		} else {
			$this->_pageStart($title);
		}
	}
	
	/**
	 * Completes the report and saves it as a file at the default reports location.
	 */
	function publish() {
		trigger_error("publish() not implemented");
		$this->display();
	}
	
	/**
	 * Ends output and writes it to output stream.
	 */
	function display() {
		$this->_pageEnd();
	}
	
	/**
	 * Call when a test or validation has completed successfuly
	 * (opposite to error)
	 */
	function ok($message) {
		$this->_linestart('ok');
		$this->_output($message);
		$this->_lineend();
	}
	
	/**
	 * Debug lines are hidden by default
	 */
	function debug($message) {
		$this->_linestart('debug');
		$this->_output($message);
		$this->_lineend();
	}
	
	/**
	 * Prints a normal paragraph
	 * @param String $message line contents, String array to make a block
	 */
	function info($message) {
		$this->_linestart();
		$this->_output($message);
		$this->_lineend();
	}
	
	/**
	 * Prints a warning, calls for administrator's attention but is not considered an error
	 * @param String $message line contents, String array to make a block
	 */
	function warn($message) {
		$this->_linestart('warning');
		$this->_output($message);
		$this->_lineend();
	}
	
	/**
	 * Prints an error message. 
	 * @param String $message line contents, String array to make a block
	 */
	function error($message) {
		$this->hasErrors = true;
		$this->_linestart('error');
		$this->_output($message);
		$this->_lineend();
	}
	
	/**
	 * Fatal error causes output to end and script to exit.
	 * It is assumed that fatal errors are handled manually by the administrator.
	 * @deprecated use error($message) instead, and the reporter might chose to quit the operation
	 */
	function fatal($message, $code = 1) {
		$this->error( $message );
		// TODO this method shouldn't be herer, right?
	}
	
	// prepare for line contents
	function _linestart($class='normal') {
		if ($this->offline) {
			if ($class=='ok') $this->_print("== ");
			if ($class=='warning') $this->_print("?? ");
			if ($class=='error') $this->_print("!! ");	
		} else {
			$this->_print("<p class=\"$class\">");
		}
	}
	// line complete
	function _lineend() {
		if (!$this->offline) $this->_print("</p>");
		$this->_print("\n");
	}
	// text block start (printed inside a line)
	function _blockstart() {
		if (!$this->offline) $this->_print("<pre>");
		$this->_print("\n");
	}
	// text block end (before line end)
	function _blockend() {
		if (!$this->offline) $this->_print("</pre>");
		$this->_print("\n");		
	}
	// writes a message to output no HTML here because it is used both online and offline
	function _output($message) {
		if (is_array($message)) {
			$this->_blockstart();
			$this->_print($this->_formatArray($message));
			$this->_blockend();
		} else {
			$this->_print($message);
		}
	}
	// replacement for echo, to customize output buffering and such things
	function _print($string) {
		echo($string);
	}
	
	function _formatArray($message) {
		$msg = '';
		$linebreak = "\n";
		if (!$this->offline) $linebreak = "<br />".$linebreak;
		foreach ( $message as $key=>$val ) {
			if ( $val===false )
				$val = 0;
			if ( is_string($key) )
				$msg .= "$key: ";
			$msg .= "$val$linebreak";
		}
		// remove last linebreak
		$last = strlen($msg)-strlen($linebreak);
		if ( $last>=0 )
			$msg = substr( $msg, 0, $last);
		return $msg;
	}
	
	function _pageStart($title) {
		$this->_print('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"');
		$this->_print(' "http://www.w3.org/TR/html4/loose.dtd">');
		$this->_print("\n<html>");
		$this->_print('<head>');
		$this->_print('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">');
		$this->_print('<title>Repos administration: ' . $title . '</title>');
		$this->_print('<link href="/repos/style/global.css" rel="stylesheet" type="text/css">');
		?>
		<script>
		function hide(level) {
			var p = document.getElementsByTagName('p');
			for (i = 0; i < p.length; i++) {
				if (p[i].getAttribute('class') == level) p[i].style.display = 'none';
			}
		}
		function showAll() {
			var p = document.getElementsByTagName('p');
			for (i = 0; i < p.length; i++) {
				p[i].style.display = '';
			}	
		}
		</script>
		<?php
		$this->_print("</head>\n");
		$this->_print("<body onLoad=\"hide('debug')\">\n");
		$this->_print("<p><a href=\"javascript:showAll()\">Show also debug level</a></p>");
	}
	
	function _pageEnd($code = 0) {
		if (!$this->offline) $this->_print("</body></html>\n\n");
		exit( $code );
	}

}
?>
