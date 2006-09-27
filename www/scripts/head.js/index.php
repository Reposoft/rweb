<?php

// under constructtion
exit;


/**
 * Server side generated javascript settings.
 * Declares predefined settings variables.
 * Organizes the plugin imports.
 */
$revision = '$Rev$';

// use nocache headers, or does repos.properties.php do that?

require(dirname(dirname(dirname(__FILE__))).'/conf/repos.properties.php');

// load same plugins in all pages
// could also use a properties file in all plugins 
$plugins = array( 
'dateformat',
'tmt-validator'
);

// generate the .js

?>