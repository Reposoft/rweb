<?php
include('./authentication.inc.php');

echo("<pre>\n");
echo("==== Test retrieval of credentials ===\n");
echo("Username = " + getReposUser());
echo("Password = " + getReposPass());
echo("BASIC string = " + getReposAuthentication());

// Display info about current repos configuration
include('./repos.properties.php');
echo("\n==== Configuration files are apparently accessible ===\n");
print_r($repos_config);
echo("\n==== Debug info: server variables ===\n");
print_r($_SERVER);
echo("</pre>\n");

?>