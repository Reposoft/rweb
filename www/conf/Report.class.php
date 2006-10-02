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
// TODO convert to HTML-entities where needed (without ending up in some kind of wiki syntax). see test reporter.php
require_once(dirname(__FILE__).'/repos.properties.php');

function getReportTime() {
    return '<span class="datetime">'.date("Y-m-d\TH:i:sO").'</span>';
}

class Report {

	var $offline;
	// counters
	var $nd = 0; //debug
	var $ni = 0; //info
	var $nw = 0; //warn
	var $ne = 0; //error
	var $no = 0; //ok
	var $nf = 0; //fail
	var $nt = 0; //test cases
	var $test = false; // true inside a test case
	
	function Report($title='Repos system report', $category='') {
		$this->offline = isOffline();
		$this->_pageStart($title);
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
		$this->_summary();
		if ($this->nd > 0) $this->_toggleDebug();
		$this->_pageEnd();
	}
	
	function teststart($name) {
		$this->_linestart('row test n'.$this->nt%4);
		$this->test = 1 + $this->ne + $this->nf;
		$this->nt++;
		
		$this->_linestart('testname');
		$this->_output($name);	
		$this->_lineend();
		$this->_linestart('testoutput');
	}
	
	function testend() {
		$this->_lineend();
		if ($this->ne + $this->nf >= $this->test) {
			$this->_linestart('testresult failed');
			$this->_output("failed");
		} else {
			$this->_linestart('testresult passed');
			$this->_output("passed");
		}
		$this->_lineend();
		$this->test = false;
		$this->_lineend();
	}
	
	function _testoutput($class, $message) {
		$s='i';
		if ($class=='passed') $s='=';
		if ($class=='failed') $s='X';
		if ($class=='debug') $s='.';
		if ($class=='warning') $s='?';
		if ($class=='error') $s='!';
		if ($this->offline) {
			$this->_output(" $s $message");
		} else {
			$message = str_replace('"','&quot;', $message);
			$this->_output("<div class=\"$class\" title=\"$message\">$s</div>");
		}
	}
	
	/**
	 * Call when a test or validation has completed successfuly
	 * (opposite to fail)
	 */
	function ok($message='') {
		$this->no++;
		$this->_testoutput('passed', $message);
	}
	
	/**
	 * Call when a check has failed.
	 * Not same as error($message), which is called for unexpected conditions.
	 */
	function fail($message) {
		$this->nf++;
		$this->_testoutput('failed', $message);
	}
	
	/**
	 * Debug lines are hidden by default
	 */
	function debug($message) {
		$this->nd++;
		$this->_outputline('debug', $message);
	}
	
	/**
	 * Prints a normal paragraph
	 * @param String $message line contents, String array to make a block
	 */
	function info($message) {
		$this->ni++;
		$this->_outputline(null, $message);
	}
	
	/**
	 * Prints a warning, calls for administrator's attention but is not considered an error
	 * @param String $message line contents, String array to make a block
	 */
	function warn($message) {
		$this->nw++;
		$this->_outputline('warning', $message);
	}
	
	/**
	 * Prints an error message. 
	 * @param String $message line contents, String array to make a block
	 */
	function error($message) {
		$this->ne++;
		if ($this->test) { $this->_testoutput('error', $message); return; }
		$this->_outputline('error', $message);
	}
	
	/**
	 * Fatal error causes output to end and script to exit.
	 * It is assumed that fatal errors are handled manually by the administrator.
	 * @deprecated use error($message) instead, and the reporter might chose to quit the operation
	 * TODO remove when backup scripts don't need it
	 */
	function fatal($message, $code = 1) {
		$this->error( $message );
	}
	
	function hasErrors() {
		return $this->ne + $this->nf > 0;
	}
	
	// prepare for line contents
	function _linestart($class='normal') {
		if ($this->offline) {
			if ($class=='ok') $this->_print("== ");
			if ($class=='warning') $this->_print("?? ");
			if ($class=='error') $this->_print("!! ");	
		} else {
			$this->_print("<div class=\"$class\">");
		}
	}
	// line complete, does flush()
	function _lineend() {
		if ($this->offline) {
			$this->_print(getNewline());
		} else {
			$this->_print("</div>".getNewline());
		}
		flush();
	}
	// text block start (printed inside a line)
	function _blockstart() {
		if (!$this->offline) $this->_print("<pre>");
		$this->_print(getNewline());
	}
	// text block end (before line end)
	function _blockend() {
		if (!$this->offline) $this->_print("</pre>");
		$this->_print(getNewline());		
	}
	// writes a line of output
	function _outputline($class, $message) {
		if ($this->test) { $this->_testoutput($class, $message); return; }
		$this->_linestart($class);
		$this->_output($message);
		$this->_lineend();
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
		$linebreak = getNewline();
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
		if (!$this->offline) {
		$this->_print('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"');
		$this->_print(' "http://www.w3.org/TR/html4/loose.dtd">');
		$this->_print("\n<html>");
		$this->_print('<head>');
		$this->_print('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">');
		$this->_print('<title>Repos administration: ' . $title . '</title>');
		$this->_print('<link href="/repos/style/global.css" rel="stylesheet" type="text/css">');
		$this->_print('<link href="/repos/style/docs.css" rel="stylesheet" type="text/css">');
		$this->_print("</head>\n");
		$this->_print("<body>\n");
		$this->_print("<div id=\"workspace\">\n");
		$this->_print("<div id=\"contents\">\n");
		$this->_print("<h1>$title</h1>\n");
		$this->_print(getReportTime());
		} else {
			$this->_linestart();
			$this->_output("---- $title ----");
			$this->_lineend();
		}
	}
	
	function _pageEnd($code = 0) {
		if (!$this->offline) $this->_print("</div></div></body></html>\n\n");
		exit( $code );
	}
	
	function _summary() {
		$class = $this->hasErrors() ? "failed" : "passed";
        $this->_output("<div class=\"testsummary $class\">");
        $this->_output("<strong>" . $this->no . "</strong> passes, ");
        $this->_output("<strong>" . $this->nf . "</strong> fails and ");
        $this->_output("<strong>" . $this->ne . "</strong> exceptions.");
        $this->_output("</div>\n");
	}
	
	function _toggleDebug() {
		?>
		<script type="text/javascript">
		function hideDebug() {
			var p = document.getElementsByTagName('div');
			for (i = 0; i < p.length; i++) { // does not work in ie
				if (/debug.*/.test(p[i].getAttribute('class'))) p[i].style.display = 'none';
			}
		}
		function showAll() {
			var p = document.getElementsByTagName('div');
			for (i = 0; i < p.length; i++) {
				p[i].style.display = '';
			}	
		}
		hideDebug();
		</script>
		<?php
		$this->_print("<p><a href=\"javascript:showAll()\">show $this->nd debug messages</a></p>\n");		
	}

}
?>
