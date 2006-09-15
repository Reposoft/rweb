<?

require("../../lib/simpletest/setup.php");

class TestNisse extends UnitTestCase {
	function TestOfLogging() {
		$this->UnitTestCase();
	}
	function testCreatingNewFile() {
		$this->assertEquals('bosse', 'bosse');
	}
}
    
$test = &new TestNisse();
$test->run(new HtmlReporter());
