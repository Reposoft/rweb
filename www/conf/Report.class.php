<?php
/**
 * Repos administrative reports.
 *
 * A common interface for scripts that do tests, configuration or sysadmin tasks.
 * Does not do output buffering, because for slow operations we want to report progress.
 * 
 * PHP scripts should require this class or the Report class, depending on the type of output they produce.
 * 
 * @package conf
 * @see Presentation, for user contents.
 */
// TODO count fatal() as exception
// TODO convert to HTML-entities where needed (without ending up in some kind of wiki syntax). see test reporter.php

// do not force the use of shared functions //require_once(dirname(__FILE__).'/repos.properties.php');

// same function as in Presentation
if (!function_exists('setupResponse')) {
	function setupResponse() {
		// no headers needed, might be in offline mode
	}
}

// reports may be long running
set_time_limit(60*5);

$reportDate = date("Y-m-d\TH:i:sO");

$reportStartTime = time();

/**
 * @return true if this is PHP running from a command line instead of a web server
 */
function isOffline() {
	// maybe there is some clever CLI detection, but this works too
	return !isset($_SERVER['REQUEST_URI']);
}

/**
 * @return newline character for this OS, or always \n if output is web
 */
function getNewline() {
	if (isOffline() && isWindows()) return "\n\r";
	else return "\n";
}

function getReportTime() {
	global $reportDate;
    return '<span class="datetime">'.$reportDate.'</span>';
}

/**
 * Represents the output of the operation, either for web or as text.
 * All output should go through this class.
 * Passing array as message means print as block (with <pre> tag in html).
 * Output should end with a call to the display() method.
 */
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
	
	/**
	 * Creates a new report, which is a new page.
	 * @param boolean $plaintext Overrides the default detection of offline/online output: true to get plaintext output, false to get html.
	 */
	function Report($title='Repos system report', $category='', $plaintext=null) {
		if (is_null($plaintext)) {
			$this->offline = isOffline();
		} else {
			$this->offline = $plaintext;
		}
		setupResponse();
		$this->_pageStart($title);
	}
	
	/**
	 * Completes the report and saves it as a file at the default reports location.
	 */
	function publish() {
		trigger_error("publish() not implemented", E_USER_ERROR);
		$this->display();
	}
	
	/**
	 * Ends output and writes it to output stream.
	 */
	function display() {
		$this->_summary();
		if ($this->nd > 0) $this->_toggleDebug();
		if ($this->hasErrors()) $this->_toggleError();
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
		} else if ($this->test) {
			$message = str_replace('"','&quot;', $message);
			$this->_output("<acronym class=\"$class\" title=\"");
			$this->_output($message);
			$this->_output("\">$s</acronym>");
		} else {
			$this->_outputline($class, " $s $message");
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
	 */
	function fatal($message, $code = 1) {
		$this->error( $message );
		$this->display();
		exit($code);
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
		$this->_print('<link href="/repos/style/global.css" rel="stylesheet" type="text/css"/>');
		$this->_print('<link href="/repos/style/docs.css" rel="stylesheet" type="text/css"/>');
		// TODO use head.js
		$this->_print('<script src="/repos/scripts/lib/jquery/jquery.js" type="text/javascript"></script>');
		$this->_print('<script src="/repos/plugins/tooltip/tooltip.js" type="text/javascript"></script>');
		$this->_print("</head>\n");
		$this->_print("<body>\n");
		// don't use containers, because then the prowser can not show process as the operation proceeds
		//$this->_print("<div id=\"workspace\">\n");
		//$this->_print("<div id=\"contents\">\n");
		
		$this->_print('<div id="commandbar">');
		$this->_print('<a class="command" href="/repos/conf/">config</a>');
		$this->_print('<a class="command" href="/repos/admin/">admin</a>');
		$this->_print('<a  class="command" href="/repos/test/">test</a>');
		$this->_print('<a  class="command" href="/repos/admin/size/">size</a>');
		$this->_print('</div>');
		
		$this->_print("<h1>$title</h1>\n");
		$this->_print('<p><span class="datetime">'.getReportTime().'</span></p>');
		} else {
			$this->_linestart();
			$this->_output("---- $title ----");
			$this->_lineend();
		}
	}
	
	function _pageEnd($code = 0) {
		if (!$this->offline) $this->_print("</body></html>\n\n");
		exit( $code );
	}
	
	function _summary() {
		global $reportStartTime;
		$time = time() - $reportStartTime;
		$class = $this->hasErrors() ? "failed" : "passed";
        $this->_output("<div class=\"testsummary $class\">");
        $this->_output("<strong>" . $this->no . "</strong> passes, ");
        $this->_output("<strong>" . $this->nf . "</strong> fails and ");
        $this->_output("<strong>" . $this->ne . "</strong> exceptions");
        $this->_output(" in $time seconds.");
        $this->_output("</div>\n");
	}
	
	function _toggleDebug() {
		// thanks to jQuery
		?>
		<script type="text/javascript">$('.debug').hide();</script>
		<p><a href="#" onclick="$('.debug').show()" accesskey="d">show <?php echo($this->nd); ?> <u>d</u>ebug messages</a></p>
		<?php	
	}
	
	function _toggleError() {
		?>
		<p><a href="#" onclick="showErrors()" accesskey="e">show <?php echo($this->ne + $this->nf); ?> <u>e</u>rror messages</a></p>
		<script type="text/javascript">
		function showErrors() {
			var i = 0;
			$('acronym.failed').each( function() {
				i++;
				this.innerHTML=''+i;
				var error = this.getAttribute('title');
				var span = document.createElement('span');
				span.innerHTML = '<small>['+i+'] '+error+'</small>';
				span.style.display = 'block';
				this.parentNode.parentNode.appendChild(span);
			});
		}
		</script>
		<?php
	}

}
?>
