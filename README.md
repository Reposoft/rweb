
## Changelog

### 1.6.5

 * Fixes details page mime-type for historical revision with svn 1.9 (https://github.com/Reposoft/rweb/issues/22)
 * Backports fix for details page result with new PHP (https://github.com/Reposoft/rweb/issues/21)

### 1.6.4

 * Adds opt-in folder download support (https://github.com/Reposoft/rweb/pull/18)
 * Download and json sometimes produces more useful HTTP response codes

### 1.6.3

 * Fixes a regression in file download (https://github.com/Reposoft/rweb/issues/16)

### 1.6.0

 * Recommends PHP 7
 * Recomments PHP-FPM
 * Tested on docker image `solsson/rweb`
 * REMOTE_USER
 * empty password
 * New server name lookup
 * Default script bundle comes with the release

TODO
 * syntaxhighlight evaluate server side options
 * errorpages generate in docker httpd:rweb from source's template
   - using sed etc, probably only hostname?
 * A docker concept with indexing would be interesting
   - needs to share the svn volume for svnlook but that's no problem in compose
   - and can be done in the same pod in k8s
