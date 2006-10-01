<?php
/**
 * Repos PHP unittest report class for the Simpletest library.
 */

/**#@+
 * This class delegates to Report
 */
require_once(dirname(dirname(dirname(__FILE__))).'/conf/Report.class.php');
/**#@-*/

/**#@+
 * This class extends SimpleReporter
 */
require_once(dirname(__FILE__) . '/simpletest/scorer.php');
/**#@-*/

// see http://www.lastcraft.com/reporter_documentation.php

/**
   *    Sample minimal test displayer. Generates only
   *    failure messages and a pass count.
*	  @package SimpleTest
*	  @subpackage UnitTester
   */
class HtmlReporter extends SimpleReporter {
    var $_character_set;
    
    var $report;

    /**
     *    Does nothing yet. The first output will
     *    be sent on the first test start. For use
     *    by a web browser.
     *    @access public
     */
    function HtmlReporter($character_set = 'ISO-8859-1') {
        $this->SimpleReporter();
        $this->_character_set = $character_set;
    }

    /**
     *    Paints the top of the web page setting the
     *    title to the name of the starting test.
     *    @param string $test_name      Name class of test.
     *    @access public
     */
    function paintHeader($test_name) {
        $this->sendNoCacheHeaders();
        $this->report = new Report($test_name);
    }

    /**
     *    Send the headers necessary to ensure the page is
     *    reloaded on every request. Otherwise you could be
     *    scratching your head over out of date test data.
     *    @access public
     *    @static
     */
    function sendNoCacheHeaders() {
        if (! headers_sent()) {
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: no-store, no-cache, must-revalidate");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
        }
    }

    /**
     *    Paints the end of the test with a summary of
     *    the passes and failures.
     *    @param string $test_name        Name class of test.
     *    @access public
     */
    function paintFooter($test_name) {
    	// don't trust Repost class counters yet
		print "<!-- ";
        print $this->getTestCaseProgress() . "/" . $this->getTestCaseCount();
        print " test cases complete: ";
        print $this->getPassCount() . " passes, ";
        print $this->getFailCount() . " fails, ";
        print $this->getExceptionCount() . " exceptions. ";
        print "-->\n";
        $this->report->display();
    }

    function paintMethodStart($test_name) {
    	parent::paintMethodStart($message);
    	$this->report->info("Test: $test_name");
    }
    
    function paintPass($message) {
    	parent::paintPass($message);
    	if (strContains($message, '[')) $message = null; // filter out default messages
    	$this->report->ok($message);
    }
    
    /**
     *    Paints the test failure with a breadcrumbs
     *    trail of the nesting test suites below the
     *    top level test.
     *    @param string $message    Failure message displayed in
     *                              the context of the other tests.
     *    @access public
     */
    function paintFail($message) {
        parent::paintFail($message);
        $breadcrumb = $this->getTestList();
        array_shift($breadcrumb);
        $p = implode(" -&gt; ", $breadcrumb);
        $p .= " -&gt; " . $this->_htmlEntities($message) . "<br />\n";
        $this->report->fail($p);
    }

    /**
     *    Paints a PHP error or exception.
     *    @param string $message        Message is ignored.
     *    @access public
     *    @abstract
     */
    function paintError($message) {
        parent::paintError($message);
        $breadcrumb = $this->getTestList();
        array_shift($breadcrumb);
        $p = implode(" -&gt; ", $breadcrumb);
        $p .= " -&gt; <strong>" . $this->_htmlEntities($message) . "</strong><br />\n";
        $this->report->error($p);
    }
    
    function paintMessage($message) {
    	parent::paintMessage($message);
    	$this->report->info($message);
    }

    /**
     *    Paints formatted text such as dumped variables.
     *    @param string $message        Text to show.
     *    @access public
     */
    function paintFormattedMessage($message) {
    	parent::paintFormattedMessage($message);
        $this->report->info( array($this->_htmlEntities($message)) );
    }

    /**
     *    Character set adjusted entity conversion.
     *    @param string $message    Plain text or Unicode message.
     *    @return string            Browser readable message.
     *    @access protected
     */
    function _htmlEntities($message) {
        return htmlentities($message, ENT_COMPAT, $this->_character_set);
    }
}
?>
