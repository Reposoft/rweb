<?php
// Display info about current repos configuration
include('./derived/repos.properties.php');
echo('==== Configuration files are apparently accessible ===\n');
print_r($repos_config);
?>