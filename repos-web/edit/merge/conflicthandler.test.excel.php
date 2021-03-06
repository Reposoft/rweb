<?php
require("conflicthandler.inc.php");
require("../../conf/System.class.php");
require("../../lib/simpletest/setup.php");

function tempfile_create($contents) {
	$file = System::getTempFile('testconflict').'_excel.xml';
	$f=@fopen($file,"w");
	if (!$f) {
	 trigger_error("Could not write contents to temp file ".$file);
	} else {
	 fwrite($f,$contents);
	 fclose($f);
	 return $file;
	}
}


class ConflicthandlerTest extends UnitTestCase {
	
	// ------- excel ----------
	
	function testExcel2003Header() {
		$contents =
'<?xml version="1.0"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
  <Author>Svensson</Author>
<<<<<<< .working
  <LastAuthor>A B</LastAuthor>
=======
  <LastAuthor>test</LastAuthor>
>>>>>>> .merge-right.r17
  <Created>2006-11-24T14:13:43Z</Created>
  <Company></Company>
  <Version>11.6568</Version>
 </DocumentProperties>
 <ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">
  <WindowHeight>15945</WindowHeight>
  <WindowWidth>19980</WindowWidth>
  <WindowTopX>480</WindowTopX>
  <WindowTopY>15</WindowTopY>
  <ActiveSheet>1</ActiveSheet>
  <ProtectStructure>False</ProtectStructure>
  <ProtectWindows>False</ProtectWindows>
 </ExcelWorkbook>
 <Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Alignment ss:Vertical="Bottom"/>
   <Borders/>
   <Font/>
   <Interior/>
   <NumberFormat/>
   <Protection/>
  </Style>
  <Style ss:ID="s22">
   <Interior ss:Color="#CCFFCC" ss:Pattern="Solid"/>
  </Style>
 </Styles>
 <Worksheet ss:Name="Blad1">
  <Table ss:ExpandedColumnCount="2" ss:ExpandedRowCount="4" x:FullColumns="1"
   x:FullRows="1">
   <Row>
    <Cell><Data ss:Type="Number">1</Data></Cell>
    <Cell ss:StyleID="s22"/>
   </Row>
   <Row>
    <Cell><Data ss:Type="Number">2</Data></Cell>
   </Row>
   <Row>
    <Cell><Data ss:Type="Number">4</Data></Cell>
    <Cell ss:Formula="=R[-2]C[-1]:RC[-1]"><Data ss:Type="Number">4</Data></Cell>
   </Row>
   <Row>
    <Cell ss:Index="2" ss:Formula="=R[-2]C[-1]/R[-2]C[-1]"><Data ss:Type="Number">1</Data></Cell>
   </Row>
  </Table>
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <PageMargins x:Bottom="0.984251969" x:Left="0.78740157499999996"
     x:Right="0.78740157499999996" x:Top="0.984251969"/>
   </PageSetup>
   <Panes>
    <Pane>
     <Number>3</Number>
     <ActiveRow>3</ActiveRow>
     <ActiveCol>1</ActiveCol>
    </Pane>
   </Panes>
   <Panes>
    <Pane>
     <Number>3</Number>
     <ActiveCol>2</ActiveCol>
    </Pane>
   </Panes>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>
 <Worksheet ss:Name="Blad2">
  <Table ss:ExpandedColumnCount="1" ss:ExpandedRowCount="1" x:FullColumns="1"
   x:FullRows="1">
   <Row>
    <Cell><Data ss:Type="Number">1</Data></Cell>
   </Row>
  </Table>
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <PageMargins x:Bottom="0.984251969" x:Left="0.78740157499999996"
     x:Right="0.78740157499999996" x:Top="0.984251969"/>
   </PageSetup>
   <Selected/>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>
 <Worksheet ss:Name="hej">
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <PageMargins x:Bottom="0.984251969" x:Left="0.78740157499999996"
     x:Right="0.78740157499999996" x:Top="0.984251969"/>
   </PageSetup>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>
</Workbook>
';		
		$file = tempfile_create($contents);
		$this->assertTrue($this->containsLine($file, '/^<<<.*/'), "Just make sure that the file was written to disk, with a conflict");
		$this->assertTrue($this->containsConflictMarker($file), "Testing the test function");
		$log = array();
		$actual = handleConflict_excel2003xml($file, $log);
		$this->dump(null, $log);
		$this->assertTrue($actual, "Should have automatically merged LatestAuthor conflict");
		$this->assertFalse($this->containsConflictMarker($file), "All conflict markers should be gone");
		$this->assertTrue($this->containsLine($file, '/.*<LastAuthor>test<\/LastAuthor>.*/'),
			"Should pick the branch author (who's doing the merge) so we get all the authors in the history of the file");
		$this->assertFalse($this->containsLine($file, '/.*<LastAuthor>A B<\/LastAuthor>.*/'),"Should have removed conflicting author");
	}
	
	function testExcel2003Value() {
		$contents =
'<?xml version="1.0"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
  <Author>Svensson</Author>
  <LastAuthor>test</LastAuthor>
  <Created>2006-11-24T14:13:43Z</Created>
  <Company></Company>
  <Version>11.6568</Version>
 </DocumentProperties>
 <ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">
  <WindowHeight>15945</WindowHeight>
  <WindowWidth>19980</WindowWidth>
  <WindowTopX>480</WindowTopX>
  <WindowTopY>15</WindowTopY>
  <ActiveSheet>1</ActiveSheet>
  <ProtectStructure>False</ProtectStructure>
  <ProtectWindows>False</ProtectWindows>
 </ExcelWorkbook>
 <Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Alignment ss:Vertical="Bottom"/>
   <Borders/>
   <Font/>
   <Interior/>
   <NumberFormat/>
   <Protection/>
  </Style>
  <Style ss:ID="s22">
   <Interior ss:Color="#CCFFCC" ss:Pattern="Solid"/>
  </Style>
 </Styles>
 <Worksheet ss:Name="Blad1">
  <Table ss:ExpandedColumnCount="2" ss:ExpandedRowCount="4" x:FullColumns="1"
   x:FullRows="1">
   <Row>
    <Cell><Data ss:Type="Number">1</Data></Cell>
    <Cell ss:StyleID="s22"/>
   </Row>
   <Row>
    <Cell><Data ss:Type="Number">2</Data></Cell>
   </Row>
   <Row>
<<<<<<< .working
    <Cell><Data ss:Type="Number">4</Data></Cell>
=======
    <Cell><Data ss:Type="Number">5</Data></Cell>
>>>>>>> .merge-right.r17
    <Cell ss:Formula="=R[-2]C[-1]:RC[-1]"><Data ss:Type="Number">4</Data></Cell>
   </Row>
   <Row>
    <Cell ss:Index="2" ss:Formula="=R[-2]C[-1]/R[-2]C[-1]"><Data ss:Type="Number">1</Data></Cell>
   </Row>
  </Table>
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <PageMargins x:Bottom="0.984251969" x:Left="0.78740157499999996"
     x:Right="0.78740157499999996" x:Top="0.984251969"/>
   </PageSetup>
   <Panes>
    <Pane>
     <Number>3</Number>
     <ActiveRow>3</ActiveRow>
     <ActiveCol>1</ActiveCol>
    </Pane>
   </Panes>
   <Panes>
    <Pane>
     <Number>3</Number>
     <ActiveCol>2</ActiveCol>
    </Pane>
   </Panes>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>
 <Worksheet ss:Name="Blad2">
  <Table ss:ExpandedColumnCount="1" ss:ExpandedRowCount="1" x:FullColumns="1"
   x:FullRows="1">
   <Row>
    <Cell><Data ss:Type="Number">1</Data></Cell>
   </Row>
  </Table>
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <PageMargins x:Bottom="0.984251969" x:Left="0.78740157499999996"
     x:Right="0.78740157499999996" x:Top="0.984251969"/>
   </PageSetup>
   <Selected/>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>
 <Worksheet ss:Name="hej">
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <PageMargins x:Bottom="0.984251969" x:Left="0.78740157499999996"
     x:Right="0.78740157499999996" x:Top="0.984251969"/>
   </PageSetup>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>
</Workbook>
';
		$file = tempfile_create($contents);
		$log = array();
		$actual = handleConflict_excel2003xml($file, $log);
		$this->dump(null, $log);
		$this->assertFalse($actual, "Cannot automatically merge value conflicts");		
		$this->assertTrue($this->containsConflictMarker($file), "Should still contain the conflict markers");	
	}

	function testExcel2003Function() {
		$contents =
'<?xml version="1.0"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
  <Author>Svensson</Author>
  <LastAuthor>test</LastAuthor>
  <Created>2006-11-24T14:13:43Z</Created>
  <Company></Company>
  <Version>11.6568</Version>
 </DocumentProperties>
 <ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">
  <WindowHeight>15945</WindowHeight>
  <WindowWidth>19980</WindowWidth>
  <WindowTopX>480</WindowTopX>
  <WindowTopY>15</WindowTopY>
  <ActiveSheet>1</ActiveSheet>
  <ProtectStructure>False</ProtectStructure>
  <ProtectWindows>False</ProtectWindows>
 </ExcelWorkbook>
 <Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Alignment ss:Vertical="Bottom"/>
   <Borders/>
   <Font/>
   <Interior/>
   <NumberFormat/>
   <Protection/>
  </Style>
  <Style ss:ID="s22">
   <Interior ss:Color="#CCFFCC" ss:Pattern="Solid"/>
  </Style>
 </Styles>
 <Worksheet ss:Name="Blad1">
  <Table ss:ExpandedColumnCount="2" ss:ExpandedRowCount="4" x:FullColumns="1"
   x:FullRows="1">
   <Row>
    <Cell><Data ss:Type="Number">1</Data></Cell>
    <Cell ss:StyleID="s22"/>
   </Row>
   <Row>
    <Cell><Data ss:Type="Number">2</Data></Cell>
   </Row>
   <Row>
    <Cell><Data ss:Type="Number">4</Data></Cell>
    <Cell ss:Formula="=R[-2]C[-1]:RC[-1]"><Data ss:Type="Number">4</Data></Cell>
   </Row>
   <Row>
<<<<<<< .working
    <Cell ss:Index="2" ss:Formula="=R[-2]C[-1]/R[-2]C[-1]"><Data ss:Type="Number">1</Data></Cell>
=======
    <Cell ss:Index="2" ss:Formula="=R[-1]C[-1]/R[-1]C[-1]"><Data ss:Type="Number">1</Data></Cell>
>>>>>>> .merge-right.r17
   </Row>
  </Table>
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <PageMargins x:Bottom="0.984251969" x:Left="0.78740157499999996"
     x:Right="0.78740157499999996" x:Top="0.984251969"/>
   </PageSetup>
   <Panes>
    <Pane>
     <Number>3</Number>
     <ActiveRow>3</ActiveRow>
     <ActiveCol>1</ActiveCol>
    </Pane>
   </Panes>
   <Panes>
    <Pane>
     <Number>3</Number>
     <ActiveCol>2</ActiveCol>
    </Pane>
   </Panes>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>
 <Worksheet ss:Name="Blad2">
  <Table ss:ExpandedColumnCount="1" ss:ExpandedRowCount="1" x:FullColumns="1"
   x:FullRows="1">
   <Row>
    <Cell><Data ss:Type="Number">1</Data></Cell>
   </Row>
  </Table>
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <PageMargins x:Bottom="0.984251969" x:Left="0.78740157499999996"
     x:Right="0.78740157499999996" x:Top="0.984251969"/>
   </PageSetup>
   <Selected/>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>
 <Worksheet ss:Name="hej">
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <PageMargins x:Bottom="0.984251969" x:Left="0.78740157499999996"
     x:Right="0.78740157499999996" x:Top="0.984251969"/>
   </PageSetup>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>
</Workbook>
';			
		$file = tempfile_create($contents);
		$log = array();
		$actual = handleConflict_excel2003xml($file, $log);
		$this->dump(null, $log);
		$this->assertFalse($actual, "Cannot automatically merge modified formulas");		
		$this->assertTrue($this->containsConflictMarker($file), "Should still contain the conflict markers");	
}	

	function testExcel2003FunctionResult() {
		$contents =
'<?xml version="1.0"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
  <Author>Svensson</Author>
  <LastAuthor>test</LastAuthor>
  <Created>2006-11-24T14:13:43Z</Created>
  <Company></Company>
  <Version>11.6568</Version>
 </DocumentProperties>
 <ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">
  <WindowHeight>15945</WindowHeight>
  <WindowWidth>19980</WindowWidth>
  <WindowTopX>480</WindowTopX>
  <WindowTopY>15</WindowTopY>
  <ActiveSheet>1</ActiveSheet>
  <ProtectStructure>False</ProtectStructure>
  <ProtectWindows>False</ProtectWindows>
 </ExcelWorkbook>
 <Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Alignment ss:Vertical="Bottom"/>
   <Borders/>
   <Font/>
   <Interior/>
   <NumberFormat/>
   <Protection/>
  </Style>
  <Style ss:ID="s22">
   <Interior ss:Color="#CCFFCC" ss:Pattern="Solid"/>
  </Style>
 </Styles>
 <Worksheet ss:Name="Blad1">
  <Table ss:ExpandedColumnCount="2" ss:ExpandedRowCount="4" x:FullColumns="1"
   x:FullRows="1">
   <Row>
    <Cell><Data ss:Type="Number">1</Data></Cell>
    <Cell ss:StyleID="s22"/>
   </Row>
   <Row>
    <Cell><Data ss:Type="Number">2</Data></Cell>
   </Row>
   <Row>
	<Cell><Data ss:Type="Number">4</Data></Cell>
<<<<<<< .working
    <Cell ss:Formula="=R[-2]C[-1]:RC[-1]"><Data ss:Type="Number">4</Data></Cell>
=======
    <Cell ss:Formula="=R[-2]C[-1]:RC[-1]"><Data ss:Type="Number">5</Data></Cell>
>>>>>>> .merge-right.r17
   </Row>
   <Row>
    <Cell ss:Index="2" ss:Formula="=R[-2]C[-1]/R[-2]C[-1]"><Data ss:Type="Number">1</Data></Cell>
   </Row>
  </Table>
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <PageMargins x:Bottom="0.984251969" x:Left="0.78740157499999996"
     x:Right="0.78740157499999996" x:Top="0.984251969"/>
   </PageSetup>
   <Panes>
    <Pane>
     <Number>3</Number>
     <ActiveRow>3</ActiveRow>
     <ActiveCol>1</ActiveCol>
    </Pane>
   </Panes>
   <Panes>
    <Pane>
     <Number>3</Number>
     <ActiveCol>2</ActiveCol>
    </Pane>
   </Panes>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>
 <Worksheet ss:Name="Blad2">
  <Table ss:ExpandedColumnCount="1" ss:ExpandedRowCount="1" x:FullColumns="1"
   x:FullRows="1">
   <Row>
    <Cell><Data ss:Type="Number">1</Data></Cell>
   </Row>
  </Table>
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <PageMargins x:Bottom="0.984251969" x:Left="0.78740157499999996"
     x:Right="0.78740157499999996" x:Top="0.984251969"/>
   </PageSetup>
   <Selected/>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>
 <Worksheet ss:Name="hej">
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <PageMargins x:Bottom="0.984251969" x:Left="0.78740157499999996"
     x:Right="0.78740157499999996" x:Top="0.984251969"/>
   </PageSetup>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>
</Workbook>
';			
		$file = tempfile_create($contents);
		$this->assertTrue($this->containsLine($file, '/^<<<.*/'), "Just make sure that the file was written to disk, with a conflict");
		$this->assertTrue($this->containsConflictMarker($file), "Testing the test function");
		$log = array();
		$actual = handleConflict_excel2003xml($file, $log);
		$this->dump(null, $log);
		$this->assertTrue($actual, "Should have automatically merged calculated results of identical functions");
		$this->assertFalse($this->containsConflictMarker($file), "All conflict markers should be gone");
		$this->assertTrue($this->containsLine($file, '/.*<Data ss:Type="Number">4<.*/'),
			"Should pick the trunk value of function");
		$this->assertFalse($this->containsLine($file, '/.*<Data ss:Type="Number">5<.*/'),"Should have removed the branch value");
	}
		
	function testExcel2003ValueFunction() {
		$contents =
'<?xml version="1.0"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
  <Author>Svensson</Author>
  <LastAuthor>test</LastAuthor>
  <Created>2006-11-24T14:13:43Z</Created>
  <Company></Company>
  <Version>11.6568</Version>
 </DocumentProperties>
 <ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">
  <WindowHeight>15945</WindowHeight>
  <WindowWidth>19980</WindowWidth>
  <WindowTopX>480</WindowTopX>
  <WindowTopY>15</WindowTopY>
  <ActiveSheet>1</ActiveSheet>
  <ProtectStructure>False</ProtectStructure>
  <ProtectWindows>False</ProtectWindows>
 </ExcelWorkbook>
 <Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Alignment ss:Vertical="Bottom"/>
   <Borders/>
   <Font/>
   <Interior/>
   <NumberFormat/>
   <Protection/>
  </Style>
  <Style ss:ID="s22">
   <Interior ss:Color="#CCFFCC" ss:Pattern="Solid"/>
  </Style>
 </Styles>
 <Worksheet ss:Name="Blad1">
  <Table ss:ExpandedColumnCount="2" ss:ExpandedRowCount="4" x:FullColumns="1"
   x:FullRows="1">
   <Row>
    <Cell><Data ss:Type="Number">1</Data></Cell>
    <Cell ss:StyleID="s22"/>
   </Row>
   <Row>
    <Cell><Data ss:Type="Number">2</Data></Cell>
   </Row>
   <Row>
<<<<<<< .working
    <Cell><Data ss:Type="Number">4</Data></Cell>
    <Cell ss:Formula="=R[-2]C[-1]:RC[-1]"><Data ss:Type="Number">4</Data></Cell>
=======
    <Cell><Data ss:Type="Number">5</Data></Cell>
    <Cell ss:Formula="=R[-2]C[-1]:RC[-1]"><Data ss:Type="Number">5</Data></Cell>
>>>>>>> .merge-right.r17
   </Row>
   <Row>
<<<<<<< .working
    <Cell ss:Index="2" ss:Formula="=R[-2]C[-1]/R[-2]C[-1]"><Data ss:Type="Number">1</Data></Cell>
=======
    <Cell ss:Index="2" ss:Formula="=R[-1]C[-1]/R[-1]C[-1]"><Data ss:Type="Number">1</Data></Cell>
>>>>>>> .merge-right.r17
   </Row>
  </Table>
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <PageMargins x:Bottom="0.984251969" x:Left="0.78740157499999996"
     x:Right="0.78740157499999996" x:Top="0.984251969"/>
   </PageSetup>
   <Panes>
    <Pane>
     <Number>3</Number>
     <ActiveRow>3</ActiveRow>
     <ActiveCol>1</ActiveCol>
    </Pane>
   </Panes>
   <Panes>
    <Pane>
     <Number>3</Number>
     <ActiveCol>2</ActiveCol>
    </Pane>
   </Panes>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>
 <Worksheet ss:Name="Blad2">
  <Table ss:ExpandedColumnCount="1" ss:ExpandedRowCount="1" x:FullColumns="1"
   x:FullRows="1">
   <Row>
    <Cell><Data ss:Type="Number">1</Data></Cell>
   </Row>
  </Table>
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <PageMargins x:Bottom="0.984251969" x:Left="0.78740157499999996"
     x:Right="0.78740157499999996" x:Top="0.984251969"/>
   </PageSetup>
   <Selected/>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>
 <Worksheet ss:Name="hej">
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <PageMargins x:Bottom="0.984251969" x:Left="0.78740157499999996"
     x:Right="0.78740157499999996" x:Top="0.984251969"/>
   </PageSetup>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>
</Workbook>
';			
	}	

	function testExcel2003ActiveCell() {
		// TODO contents not edited for this test yet
		$contents = 
'<?xml version="1.0"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
  <Author>Svensson</Author>
  <LastAuthor>A B</LastAuthor>
  <Created>2006-11-24T14:13:43Z</Created>
  <Company></Company>
  <Version>11.6568</Version>
 </DocumentProperties>
 <ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">
  <WindowHeight>15945</WindowHeight>
  <WindowWidth>19980</WindowWidth>
  <WindowTopX>480</WindowTopX>
  <WindowTopY>15</WindowTopY>
  <ActiveSheet>1</ActiveSheet>
  <ProtectStructure>False</ProtectStructure>
  <ProtectWindows>False</ProtectWindows>
 </ExcelWorkbook>
 <Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Alignment ss:Vertical="Bottom"/>
   <Borders/>
   <Font/>
   <Interior/>
   <NumberFormat/>
   <Protection/>
  </Style>
<<<<<<< .working
  <Style ss:ID="s22">
   <Interior ss:Color="#CCFFCC" ss:Pattern="Solid"/>
=======
  <Style ss:ID="s22">
   <Interior ss:Color="#FF00FF" ss:Pattern="Solid"/>
>>>>>>> .merge-right.r17
  </Style>
 </Styles>
 <Worksheet ss:Name="Blad1">
  <Table ss:ExpandedColumnCount="2" ss:ExpandedRowCount="4" x:FullColumns="1"
   x:FullRows="1">
   <Row>
    <Cell><Data ss:Type="Number">1</Data></Cell>
    <Cell ss:StyleID="s22"/>
   </Row>
   <Row>
    <Cell><Data ss:Type="Number">2</Data></Cell>
   </Row>
   <Row>
<<<<<<< .working
    <Cell><Data ss:Type="Number">4</Data></Cell>
    <Cell ss:Formula="=R[-2]C[-1]:RC[-1]"><Data ss:Type="Number">4</Data></Cell>
=======
    <Cell><Data ss:Type="Number">5</Data></Cell>
    <Cell ss:Formula="=R[-2]C[-1]:RC[-1]"><Data ss:Type="Number">5</Data></Cell>
>>>>>>> .merge-right.r17
   </Row>
   <Row>
<<<<<<< .working
    <Cell ss:Index="2" ss:Formula="=R[-2]C[-1]/R[-2]C[-1]"><Data ss:Type="Number">1</Data></Cell>
=======
    <Cell ss:Index="2" ss:Formula="=R[-1]C[-1]/R[-1]C[-1]"><Data ss:Type="Number">1</Data></Cell>
>>>>>>> .merge-right.r17
   </Row>
  </Table>
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <PageMargins x:Bottom="0.984251969" x:Left="0.78740157499999996"
     x:Right="0.78740157499999996" x:Top="0.984251969"/>
   </PageSetup>
   <Panes>
    <Pane>
     <Number>3</Number>
     <ActiveRow>3</ActiveRow>
     <ActiveCol>1</ActiveCol>
    </Pane>
   </Panes>
   <Panes>
    <Pane>
     <Number>3</Number>
     <ActiveCol>2</ActiveCol>
    </Pane>
   </Panes>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>
 <Worksheet ss:Name="Blad2">
  <Table ss:ExpandedColumnCount="1" ss:ExpandedRowCount="1" x:FullColumns="1"
   x:FullRows="1">
   <Row>
    <Cell><Data ss:Type="Number">1</Data></Cell>
   </Row>
  </Table>
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <PageMargins x:Bottom="0.984251969" x:Left="0.78740157499999996"
     x:Right="0.78740157499999996" x:Top="0.984251969"/>
   </PageSetup>
<<<<<<< .working
   <Selected/>
=======
   <Panes>
    <Pane>
     <Number>3</Number>
     <ActiveCol>1</ActiveCol>
    </Pane>
   </Panes>
>>>>>>> .merge-right.r17
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>
<<<<<<< .working
 <Worksheet ss:Name="hej">
=======
 <Worksheet ss:Name="jox">
>>>>>>> .merge-right.r17
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <PageMargins x:Bottom="0.984251969" x:Left="0.78740157499999996"
     x:Right="0.78740157499999996" x:Top="0.984251969"/>
   </PageSetup>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>
</Workbook>
';			
	}	
	
	function testExcel2003Combination() {
		// all the conflicts from the tests above
		$contents =
'<?xml version="1.0"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
  <Author>Svensson</Author>
<<<<<<< .working
  <LastAuthor>A B</LastAuthor>
=======
  <LastAuthor>test</LastAuthor>
>>>>>>> .merge-right.r17
  <Created>2006-11-24T14:13:43Z</Created>
  <Company></Company>
  <Version>11.6568</Version>
 </DocumentProperties>
 <ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">
  <WindowHeight>15945</WindowHeight>
  <WindowWidth>19980</WindowWidth>
  <WindowTopX>480</WindowTopX>
  <WindowTopY>15</WindowTopY>
  <ActiveSheet>1</ActiveSheet>
  <ProtectStructure>False</ProtectStructure>
  <ProtectWindows>False</ProtectWindows>
 </ExcelWorkbook>
 <Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Alignment ss:Vertical="Bottom"/>
   <Borders/>
   <Font/>
   <Interior/>
   <NumberFormat/>
   <Protection/>
  </Style>
<<<<<<< .working
  <Style ss:ID="s22">
   <Interior ss:Color="#CCFFCC" ss:Pattern="Solid"/>
=======
  <Style ss:ID="s22">
   <Interior ss:Color="#FF00FF" ss:Pattern="Solid"/>
>>>>>>> .merge-right.r17
  </Style>
 </Styles>
 <Worksheet ss:Name="Blad1">
  <Table ss:ExpandedColumnCount="2" ss:ExpandedRowCount="4" x:FullColumns="1"
   x:FullRows="1">
   <Row>
    <Cell><Data ss:Type="Number">1</Data></Cell>
    <Cell ss:StyleID="s22"/>
   </Row>
   <Row>
    <Cell><Data ss:Type="Number">2</Data></Cell>
   </Row>
   <Row>
<<<<<<< .working
    <Cell><Data ss:Type="Number">4</Data></Cell>
    <Cell ss:Formula="=R[-2]C[-1]:RC[-1]"><Data ss:Type="Number">4</Data></Cell>
=======
    <Cell><Data ss:Type="Number">5</Data></Cell>
    <Cell ss:Formula="=R[-2]C[-1]:RC[-1]"><Data ss:Type="Number">5</Data></Cell>
>>>>>>> .merge-right.r17
   </Row>
   <Row>
<<<<<<< .working
    <Cell ss:Index="2" ss:Formula="=R[-2]C[-1]/R[-2]C[-1]"><Data ss:Type="Number">1</Data></Cell>
=======
    <Cell ss:Index="2" ss:Formula="=R[-1]C[-1]/R[-1]C[-1]"><Data ss:Type="Number">1</Data></Cell>
>>>>>>> .merge-right.r17
   </Row>
  </Table>
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <PageMargins x:Bottom="0.984251969" x:Left="0.78740157499999996"
     x:Right="0.78740157499999996" x:Top="0.984251969"/>
   </PageSetup>
   <Panes>
    <Pane>
     <Number>3</Number>
     <ActiveRow>3</ActiveRow>
     <ActiveCol>1</ActiveCol>
    </Pane>
   </Panes>
   <Panes>
    <Pane>
     <Number>3</Number>
     <ActiveCol>2</ActiveCol>
    </Pane>
   </Panes>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>
 <Worksheet ss:Name="Blad2">
  <Table ss:ExpandedColumnCount="1" ss:ExpandedRowCount="1" x:FullColumns="1"
   x:FullRows="1">
   <Row>
    <Cell><Data ss:Type="Number">1</Data></Cell>
   </Row>
  </Table>
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <PageMargins x:Bottom="0.984251969" x:Left="0.78740157499999996"
     x:Right="0.78740157499999996" x:Top="0.984251969"/>
   </PageSetup>
<<<<<<< .working
   <Selected/>
=======
   <Panes>
    <Pane>
     <Number>3</Number>
     <ActiveCol>1</ActiveCol>
    </Pane>
   </Panes>
>>>>>>> .merge-right.r17
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>
<<<<<<< .working
 <Worksheet ss:Name="hej">
=======
 <Worksheet ss:Name="jox">
>>>>>>> .merge-right.r17
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <PageMargins x:Bottom="0.984251969" x:Left="0.78740157499999996"
     x:Right="0.78740157499999996" x:Top="0.984251969"/>
   </PageSetup>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>
</Workbook>
';			
	}
	
	
	// ---- helpers for assertions ----
	
	function containsConflictMarker($file) {
		if ($this->containsLine($file, '/^<<<<<<<.*/')) return true;
		if ($this->containsLine($file, '/=======^.*/')) return true;
		if ($this->containsLine($file, '/^>>>>>>>.*/')) return true;
	}
	
	function containsLine($file, $preg) {
		$fh = fopen($file, 'r');
		$m = false;
		while (!feof($fh) && !$m) {
			$buffer = fgets($fh, 4096);
			$m = preg_match($preg, $buffer);
		}
		fclose($fh);
		return $m;
	}
	
}

$testcase = new ConflicthandlerTest();
testrun($testcase);

?>