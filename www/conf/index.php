<?php
include './authentication.inc.php';

echo '<small><a href="logout.php">Log out</a></small>';
echo "<pre>\n";
echo "==== Test retrieval of credentials ===";
echo "\nUsername = ";
//echo $repos_authentication['user'];
echo getReposUser();
echo "\nPassword = ";
echo getReposPass();
//echo $repos_authentication['pass'];
echo "\nBASIC string = ";
echo getReposAuth();
//echo $repos_authentication['auth'];
echo "\n";

// Display info about current repos configuration
include './repos.properties.php';
echo "\n==== Configuration files are apparently accessible ===\n";
print_r($repos_config);
echo "\n==== Debug info: server variables ===\n";
print_r($_SERVER);
echo "</pre>\n";

?>