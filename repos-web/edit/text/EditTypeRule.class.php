<?php
/**
 * Shared between edit/file/ and edit/upload/
 * @package edit
 */
class EditTypeRule extends Rule {
	function valid($value) {
		return ($value = 'upload' 
			|| $value == 'txt' 
			|| $value == 'html');
	}
}
?>
