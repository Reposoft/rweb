<?php
define('DIR',dirname(__FILE__).DIRECTORY_SEPARATOR);
define('PARENT_DIR',substr(DIR, 0, strrpos(rtrim(strtr(DIR,'\\','/'),'/'),'/')));
require( PARENT_DIR.'/conf/repos.properties.php' );
require( PARENT_DIR."/smarty/smarty.inc.php" );
require( PARENT_DIR."/language.inc.php" );

function getDemoUrl() {
	if(isset($_GET['url'])) {
		return $_GET['url'];
	}
	$repo = getConfig('repo_url');
	if(isset($_SERVER['HTTP_REFERER'])) {
		$ref = $_SERVER['HTTP_REFERER'];
		if( strstr($ref,$repo)== $ref ) return $ref;
	}
	return $repo.'/svensson/trunk';
}

$smarty = getTemplateEngine();
$smarty->assign('url', getDemoUrl());
$smarty->display(DIR.getLocaleFile());
?>