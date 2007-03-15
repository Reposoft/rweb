<?php
/**
 * Loads the proplist plugin
 *
 * @param String $webapp
 * @return array
 */
function proplist_getHeadTags($webapp) {
	return array('<script type="text/javascript" src="'.$webapp.'plugins/dateformat/dateformat.js"></script>');
}

?>