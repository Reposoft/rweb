<?php
/**
 *
 *
 * @package
 */
//addPlugin('edit');
//addPlugin('syntax');

function acl_getHeadTags($webapp) {
	if (strEnds(getSelfUrl(), '/upload/')) return array(); // upload is used for saving the file
	if (!strEnds(getTarget(), '.accs')) return array();
	return array('<script language="javascript" type="text/javascript" src="'.$webapp.'plugins/acl/acl.js"></script>');
}

function editWriteNewVersion_acl(&$postedText, $destinationFile) {
	// TODO validation using AclRule
	editWriteNewVersion_txt($postedText, $destinationFile);	
}

class AclRule extends Rule {
	
	function AclRule($fieldname='usertext') {
		$this->Rule($fieldname);
	}
	
	
	
}

?>
