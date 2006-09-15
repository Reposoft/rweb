<?

require("../../lib/simpletest/setup.php");

class TestNisse extends UnitTestCase {
	function TestOfLogging() {
		$this->UnitTestCase();
	}
	function testCreatingNewFile() {
		$this->assertEqual('osse', 'bosse');
	}
}
    
$test = &new TestNisse();
$test->run(new HtmlReporter());
