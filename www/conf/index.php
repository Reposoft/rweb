<?php
echo("<pre>\n");
// Display info about current repos configuration
include('./repos.properties.php');
echo("==== Configuration files are apparently accessible ===\n");
print_r($repos_config);
echo("\n==== Thest retrieval of credentials ===\n");
include('./authentication.inc.php');
echo("Username = " + REPOS_USER);
echo("Password = " + REPOS_PASS);
echo("BASIC string = " + REPOS_AUTH);
echo("</pre>\n");
?>