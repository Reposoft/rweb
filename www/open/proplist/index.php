<?php
// get the properties of a file or folder as JSON 
/* subversion resturns something like
Properties on 'http://localhost/testrepo/demoproject/trunk/public/xmlfile.xml':
  svn:mime-type : text/xml
  svn:ignore : .project
.projectOptions
.cache
*/

require("../SvnOpen.class.php" );
require_once("../../lib/json/json.php" );

$url = getTargetUrl();
Validation::expect('target');

header('Content-type: text/plain');

$cmd = new SvnOpen('proplist');
$cmd->addArgOption('-v');
$cmd->addArgUrl($url);
if ($cmd->exec()) {
	trigger_error(implode("\n",$cmd->getOutput()), E_USER_ERROR);	
}

$output = $cmd->getOutput();
$start = array_shift($output);
if (preg_match('/\'(\w+:\/\/\S+)\'/', $start, $matches)) {
	$result['target'] = rawurldecode($matches[1]);
} else {
	$result['target'] = getTargetUrl(); // return an empty proplist if no properties set
}

$proplist = array();
$last = null;
foreach($output as $line) {
	preg_match('/(\s+([\w-:]+)\s:\s)?(.*)/', $line, $matches);
	if (preg_match('/\s+([\w-:]+)\s:\s(.*)/', $line, $matches)) {
		$last = $matches[1];
		$proplist[$last] = $matches[2];		
	} else {
		if (!$last) trigger_error("Invalid property line '$line'", E_USER_ERROR);
		$proplist[$last] .= "\n".$line;
	}
}

$result['proplist'] = $proplist;

$json = new Services_JSON();
echo $json->encode($result);

// to get all the properties of a specific type for a tree,
// use propget -R [propertyname] path
// but that's probably a different service

?>