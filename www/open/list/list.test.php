<?php

// TODO move this to SvnOpen's parsing of svn info instead

// TODO support other formats than xml, and write tests from the samples below

// list a file
$test1 = '
<?xml version="1.0"?>
<lists>
<list
   path="https://localhost/testrepo/demoproject/trunk/public/xmlfile.xml">
<entry
   kind="file">
<name>xmlfile.xml</name>
<size>18</size>
<commit
   revision="1">
<author>This Guy</author>
<date>2006-10-17T06:33:53.714614Z</date>
</commit>
</entry>
</list>
</lists>
';
$test1Array = explode("\n", $test1);

// list a folder containing file and folder
$test2 = '
<?xml version="1.0"?>
<lists>
<list
   path="https://localhost/testrepo/test/trunk/fa/fb">
<entry
   kind="file">
<name>b.txt</name>
<size>1</size>
<commit
   revision="4">
<author>SYSTEM</author>
<date>2006-10-17T06:33:55.627364Z</date>
</commit>
</entry>
<entry
   kind="dir">
<name>fc</name>
<commit
   revision="4">
<author>SYSTEM</author>
<date>2006-10-17T06:33:55.627364Z</date>
</commit>
</entry>
<entry
   kind="dir">
<name>io</name>
<commit
   revision="18">
<author>test</author>
<date>2006-10-18T09:30:58.451603Z</date>
</commit>
</entry>
</list>
</lists>
';
$test2Array = explode("\n", $test2);

require('../../lib/simpletest/setup.php');

    
?>