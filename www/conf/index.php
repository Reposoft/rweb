<?php
// Display info about current repos configuration
include('derived/repos.properties.ph');
echo('==== Configuration files are apparently accessible ===\n')
print_r($repos_config);
?>