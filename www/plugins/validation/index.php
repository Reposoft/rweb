<?php
/** form validation logic for repos
 * see test page for documentation
 */

require(dirname(dirname(dirname(__FILE__))).'/conf/Presentation.class.php');

// define the validation rules
rule('filename', RULE_FILENAME);

// dispatch to form processing if 'submit' was pressed (button must have name="submit"
if (isset($_GET['submit'])) processForm();

$p = new Presentation();
$p->display();

function processForm() {
	validate('name');
}

?>