<?php

function parseList($svnList) {
	return array_map('parseListRow',$svnList);
}

function parseListRow($row) {
	return rtrim($row);
	// row ends with slask --> directory --> no size info
	// names may contain space characters
}

$result = array();
$last = exec("svn list -v ..",$result);
print_r(parseList($result));

?>