
## Changelog

### 1.6.0

 * Recommends PHP 7
 * Recomments PHP-FPM
 * Tested on docker image `solsson/rweb`
 * REMOTE_USER
 * empty password
 * New server name lookup
 * Default script bundle comes with the release

TODO
 * http://svn/svn/test1/?rweb=history&serv=json doesn't give you json
 * syntaxhighlight evaluate server side options
 * errorpages generate in docker httpd:rweb from source's template
   - using sed etc, probably only hostname?
 * A docker concept with indexing would be interesting
   - needs to share the svn volume for svnlook but that's no problem in compose
   - and can be done in the same pod in k8s
