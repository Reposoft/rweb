<?php
// Display info about current repos configuration
include('./repos.properties.php');
echo("==== Configuration files are apparently accessible ===\n");
print_r($repos_config);
?>