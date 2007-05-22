<?php
/**
 * Copyright 2007 Staffan Olsson www.repos.se
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.

=== Print svn log --xml to response ===

Recommended apache configuration, using mod_rewrite:
RewriteEngine on
RewriteCond %{QUERY_STRING} ^svn=log$
RewriteRule ^(/repository-root/.*)$ /repos/open/log/?url=http://%{HTTP_HOST}$1 [PT,L]

 */

if (!isset($_REQUEST['url'])) die("Parameter 'url' is required");
$url = $_REQUEST['url'];

$cmd = "svn log --xml --verbose --incremental --non-interactive \"$url\" ";
 
// proxy BASIC authentication
if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
	$cmd .= ' --no-auth-cache --username "'.$_SERVER['PHP_AUTH_USER'].'" --password "'.$_SERVER['PHP_AUTH_PW'].'"';
}

header('Content-Type: text/xml');
echo('<?xml version="1.0"?>
<?xml-stylesheet type="text/xsl" href="/repos/view/log.xsl"?>
<log>
');
passthru($cmd);
echo('</log>
');
?>
