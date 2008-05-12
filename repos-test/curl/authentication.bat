
# test request without user
curl -s -I "http://localhost/data/test/" | grep "401 Authorization Required"
curl -s -I "http://localhost/data/test/" | grep "WWW-Authenticate: Basic realm"

# the page shown with Authorization required header is shown if user presses Cancel at login popup
curl -s "http://localhost/data/test/" | grep "requires login"

# authenticate to repository
curl -s -u test:test "http://localhost/data/test/" | grep "trunk/"
curl -s -u test:test "http://localhost/data/test/" -I | grep "200 OK"
curl -s -u test:test -I "http://localhost/data/demoproject/trunk/noaccess/" | grep "403 Forbidden"

# history and list with authentication (runs svn command)
curl -s -u test:test -I "http://localhost/repos/open/log/?target=/demoproject/trunk/" | grep "200 OK"
curl -s -u test:test -I "http://localhost/repos/open/list/?target=/demoproject/trunk/" | grep "200 OK"

# history without authentication
curl -s "http://localhost/repos/open/log/?target=/demoproject/trunk/" -I | grep "WWW-Authenticate"
# log is an xml page so error message formatting is tricky
curl -s "http://localhost/repos/open/log/?target=/demoproject/trunk/" | grep "requires authentication"

# file without authentication (html page, should get html error)
curl -s "http://localhost/repos/open/file/?target=/test/trunk/fa/a.txt" -I | grep "WWW-Authenticate"
curl -s "http://localhost/repos/open/file/?target=/test/trunk/fa/a.txt" | grep "requires authentication"

# view and edit with authentication (runs service call)
curl -s -u test:test -I "http://localhost/repos/open/?target=/test/trunk/fa/a.txt" | grep "200 OK"
curl -s -u test:test -I "http://localhost/repos/edit/?target=/test/trunk/fa/a.txt" | grep "200 OK"

# view without authentication, should show login box
curl -s "http://localhost/repos/open/?target=/test/trunk/fa/a.txt" -I | grep "401 Authorization Required"
curl -s "http://localhost/repos/open/?target=/test/trunk/fa/a.txt" -I | grep "WWW-Authenticate: Basic realm"
# if user hits cancel, show explanation and back button
curl -s "http://localhost/repos/open/?target=/test/trunk/fa/a.txt" | grep "requires authentication"
curl -s "http://localhost/repos/open/?target=/test/trunk/fa/a.txt" | grep "back"

# In Repos 1.1, upload page on public file without login says "Service authentication required, but user is not logged in."
curl -s "http://localhost/repos/edit/upload/?target=/demoproject/trunk/public/xmlfile.xml" | grep "requires authentication"
# should be login box
curl -s "http://localhost/repos/edit/upload/?target=/demoproject/trunk/public/xmlfile.xml" -I | grep "WWW-Authenticate:"

# view public file without login
curl -s "http://localhost/repos/open/?target=/demoproject/trunk/public/xmlfile.xml" -I | grep "200 OK"

# show log for public folder without login
curl -s "http://localhost/repos/open/log/?target=/demoproject/trunk/public/" -I | grep "200 OK"
