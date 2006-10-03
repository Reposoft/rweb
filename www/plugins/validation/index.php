<?php
/** form validation logic for repos
 * see test page for documentation
 */

// the validation API is imported by the Presentation class
require(dirname(dirname(dirname(__FILE__))).'/conf/Presentation.class.php');

// define custom validation rules
class UsernameRule extends RuleEreg {
	function UsernameRule($fieldname) {
		$this->RuleEreg($fieldname,
			'Username is max 20 characters and can not contain special characters',
			'[a-zA-Z0-9]+'); // not required
	}
}

// create validation instances
new Rule('name'); // = 'name' is required
new FilenameRule('filename', false);
new UsernameRule('username');

// dispatch to form processing if 'submit' was pressed (button must have name="submit")
if (isset($_GET['submit'])) {
	processForm();
} else {
	presentForm();
}

function presentForm() {
	$p = new Presentation();
	$p->display();
}

function processForm() {
	Validation::expect('name');
	presentForm();
}

?>