<?php
// PHP authentication compatible with webapp strategy.
// Defines functions to retrieve credentials, abstracting the autentication method.
// Returns a 401 header if credentials are not found.
if (!isset($_SERVER['PHP_AUTH_USER'])) {
   header('WWW-Authenticate: Basic realm="repos"');
   header('HTTP/1.0 401 Unauthorized');
   echo 'Please provide your Repos login';
   exit;
} elseif (false) {
   // There seems to be a number of reasons why you don't get these server variables. Alternative methods include
   // - $_SERVER['REMOTE_USER'] = Basic QWxhZGRpbjpvcGVuIHNlc2FtZQ==
   // - list($user, $pw) = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
   // For HTTP Authentication to work with IIS, the PHP directive cgi.rfc2616_headers must be set to 0 (the default value).
}
// "PHP_AUTH variables will not be set if external authentication is enabled for that particular page and safe mode is enabled"
// set credentials as constants, so that dependencies are not tied to the server variables
$repos_authentication;
$repos_authentication['user'] = $_SERVER['PHP_AUTH_USER'];
$repos_authentication['pass'] = $_SERVER['PHP_AUTH_PW'];
$repos_authentication['auth'] = substr($_SERVER['HTTP_AUTHORIZATION'], 6);

function getReposUser() {
	global $repos_authentication;
	return($repos_authentication['user']);
}
function getReposPass() {
	global $repos_authentication;
	return($repos_authentication['pass']);
}
function getReposAuth() {
	global $repos_authentication;
	return($repos_authentication['auth']);
}
?>