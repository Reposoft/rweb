<?php
// need allow_url_fopen (which is default)
require 'http://jaddajada/file_with_no_query.php';
/*
Need to rewrite repository urls in Apache, here's a thought:

RewriteEngine  on
RewriteRule    ^\.phprun\.php\?(.+)  http://www.repos.se/phprun/.phprun.php?file=$1
# all PHPs under dir
RewriteRule    ^(.+).php\??(.+)  http://www.repos.se/phprun/.phprun.php?file=$1.php&$2

Check http://httpd.apache.org/docs-2.0/misc/rewriteguide.html
*/
?>