<?php
require("mimetype.inc.php");

require("../../conf/System.class.php");
require("../../lib/simpletest/setup.php");

// this is our custom filetype handler for testing
function getSpecificMimetype_ir2($file, $suggestedType) {
	return 'text/test';
}

function getSpecificMimetype_ir3($file, $suggestedType) {
	return $suggestedType;
}

function getSpecificMimetype_ir4($file, $suggestedType) {
	return false;
}

class MimetypeTest extends UnitTestCase {
		
	function testGetSpecificMimetypeForExtensionXml() {
		$actual = getSpecificMimetypeForExtension('xml', 'text/xml');
		$this->assertEqual(false, $actual, "text/xml is the default mimetype for xml. %s");
		$actual = getSpecificMimetypeForExtension('xml', 'application/excel');
		$this->assertEqual('application/excel', $actual, "suggested a non-standard type for a relevant extension. %s");
	}
	
	function testGetSpecificMimetypeForExtensionTst() {
		$actual = getSpecificMimetypeForExtension('tst', 'text/repos-testfile');
		$this->assertEqual(false, $actual, "should be default. %s");
		$actual = getSpecificMimetypeForExtension('tst', 'text/plain');
		$this->assertEqual('text/plain', $actual, "suggested a non-standard type for a relevant extension. %s");
	}

	function testGetSpecificMimetypeForExtensionIrrelevant() {
		$actual = getSpecificMimetypeForExtension('irr', 'text/repos-testfile');
		$this->assertEqual(false, $actual, "We don't know anything about the filetype 'irr' so we don't want to set a mimetype. %s");
	}	
	
	function testGetSpecificMimetype_nonExistingFile() {
		// should do the same as ForExtension
		$actual = getSpecificMimetype('/tmp/test.xml', 'text/xml');
		$this->assertEqual(false, $actual, "text/xml is the default mimetype for xml. %s");
		$actual = getSpecificMimetype('/tmp/test.xml', 'text/plain');
		$this->assertEqual('text/plain', $actual, "suggested a non-standard type for a relevant extension. %s");
	}
	
	function testGetSpecificMimetype_pluggableHandler() {
		// user our own mimtype resolvers, that only exist in this test
		// (extension should not have to be known, the existense of the function name should call for the call)
		$f2 = System::getTempFile('mimetest').'a.ir2';
		touch($f2);
		$this->assertEqual('text/test', getSpecificMimetype($f2,'text/p'), "Should call the ir2 resolver. %s");
		System::deleteFile($f2);
		$f3 = System::getTempFile('mimetest').'a.ir3';
		touch($f3);
		$this->assertEqual('text/p', getSpecificMimetype($f3,'text/p'), "Should call the ir3 resolver. %s");
		System::deleteFile($f3);
		$f4 = System::getTempFile('mimetest').'a.ir4';
		touch($f4);
		$this->assertEqual(false, getSpecificMimetype($f4,'t/p'), "Should call the ir4 resolver. %s");
		System::deleteFile($f4);
		// there is currently no pluggable solution to making decisions based on filetype if the file does not exist
		// because we don't have a need for it
	}
	
	// test the xml type handler, which is currently in the mimetype file
	function testGetSpecificMimetype_xml() {
		$file = System::getTempFile('mimetest').'tmp';
		$fh = fopen($file,'w');
		fwrite($fh,
'<?xml version="1.0"?>
<empty/>
');
		fclose($fh);
		$actual = getSpecificMimetype_xml($file, 'doesnt/matter');
		$this->assertFalse($actual, "The file contains standard xml, should not get a mime type");
		System::deleteFile($file);
	}
	
	function testGetSpecificMimetype_xml_excel() {
		$this->sendMessage('Mimetypes based on contents is not supported in 1.1.');
		return; // this functionality is not in 1.1
		$file = System::getTempFile('mimetest').'tmp';
		$fh = fopen($file,'w');
		fwrite($fh,
'<?xml version="1.0"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office"/>
');
		fclose($fh);
		$actual = getSpecificMimetype_xml($file, 'doesnt/matter');
		$this->assertEqual('application/excel', $actual, "The file contains Excel xml headers, should get excel non-binary mime type");
		System::deleteFile($file);
	}
	
	function testBrowserMimetype() {
		// IE6 jpeg => image/pjpeg
		// FF jpeg => image/jpeg
		// IE6/FF htm => text/html
		// pdf => application/pdf
		// IE6 xml => application/octet-stream
		// FF xml => text/xml
		//upload->getType method returns this
	}
	
}

testrun(new MimetypeTest());

?>