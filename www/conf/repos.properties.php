<?php
// Repos properties as php variables
$repos_config = parse_ini_file( dirname(__FILE__) . '/repos.properties', false );
// Extra runtime information
$isWindows = (substr(PHP_OS, 0, 3) == 'WIN');
?>