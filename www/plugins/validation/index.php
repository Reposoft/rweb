<?php
/** form validation logic for repos
 * see test page for documentation
 */

// the validation API is imported by the Presentation class
require(dirname(dirname(dirname(__FILE__))).'/conf/Presentation.class.php');

// define custom validation rules
class TestUsernameRule extends RuleEreg {
	function TestUsernameRule($fieldname) {
		$this->RuleEreg($fieldname,
			'Username is 4-20 characters and can not contain special characters',
			'^$|^[a-zA-Z0-9]{4,20}$'); // not required
	}
}

// create validation instances
new Rule('name');
new FilenameRule('filename', false);
// description has no rule
new TestUsernameRule('testuser');

// dispatch to form processing if 'submit' was pressed (button must have name="submit")
if (isset($_GET['submit'])) {
	processForm();
} else {
	presentForm();
}

function presentForm($message='enter some values and press submit') {
	$p = new Presentation();
	$p->assign('message', $message);
	$p->display();
}

function processForm() {
	Validation::expect('name', 'filename', 'description', 'testuser');
	presentForm("we're happy campers");
}

?>