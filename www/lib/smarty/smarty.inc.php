<?php
// this file is not needed anymore. simply instantiate Presentation.

require( dirname(dirname(__FILE__)).'/conf/Presentation.class.php' );

// smarty factory
function getTemplateEngine() {
	return new Presentation();
}

?>