
## Changelog

### 1.7.1

 * Makes unit tests runnable in docker-compose setup with manual simpletest install
   - ./build-contracts/unittest/run-curl.sh says 8/15 .test.php files fail
 * Switches to PHP5 constructors (__construct instead of ClassName) to avoid deprecation warning
 * Includes the stuff from 1.6.3 and 1.6.4

### 1.7.0 (released from f56ced90 excluding 1.6.3 and 1.6.4 changes)

 * Adds normalization of file upload name (https://github.com/Reposoft/rweb/pull/14)
 * Drops support for svn <1.5
 * Adds support for a REPOS_TEMP variable to specify tmp dir
   - Performance of the backing volume matters in edit operations due to temporary WCs

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
