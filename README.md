
## Changelog

### 1.7.11

 * Release archives now have the version number printed in the footer of the index stylesheet
 * The tree sidebar plugin was removed from repo root toolbar because recent browsers don't display the sidebar

### 1.7.10

 * The `/repos-web/open/json/` endpoint returns json with `Content-Type`: `application/json`
 * Requests with a `selector` query param to that same endpoint get `application/javascript`
 * Both were formerly `text/plain`
 * The `tree` plugin is installed by default

### 1.7.9

 * Adds id attributes to deails page actions
 * Fontawesome upgrade to 4.7.0

### 1.7.8

 * Uses Ghostscript instead of pdf tk for PDF page extraction.

### 1.7.7

 * Fixes issue with text "upload" (POST new version) in PHP 7.2

### 1.7.3

 * Supports "accept" at upload new version (https://github.com/Reposoft/rweb/pull/25)
 * Fixes issue with broken form for upload new version (https://github.com/Reposoft/rweb/pull/24)

### 1.6.5

 * Fixes details page mime-type for historical revision with svn 1.9 (https://github.com/Reposoft/rweb/issues/22)
 * Backports fix for details page result with new PHP (https://github.com/Reposoft/rweb/issues/21)

### 1.7.2

 * Supports "accept" at upload new file and ?rweb=t.x (https://github.com/Reposoft/rweb/pull/20)

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
