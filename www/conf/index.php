<?php
include('./authentication.inc.php');

echo("<pre>\n");
echo("==== Test retrieval of credentials ===\n");
echo("Username = " + REPOS_USER);
echo("Password = " + REPOS_PASS);
echo("BASIC string = " + REPOS_AUTH);

// Display info about current repos configuration
include('./repos.properties.php');
echo("\n==== Configuration files are apparently accessible ===\n");
print_r($repos_config);
echo("\n==== Debug info: server variables ===\n");
print_r($_SERVER);
echo("</pre>\n");

?>