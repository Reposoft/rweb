curl -s -I -u test:test http://localhost/data/demoproject/trunk/ | grep "200 OK"
curl -s -I -u test:test http://localhost/data/demoproject/ | grep "200 OK"
# when user is not authorized to view parent, redirect to startpage
curl -s -I -u test:test http://localhost/data/ | grep "403 Forbidden"
curl -s -I -u test:test http://localhost/data/ | grep "Location" | grep "start/"
# not authorized to view folder inside project - show message
curl -s -I -u test:test http://localhost/data/demoproject/trunk/noaccess/ | grep "403 Forbidden"
curl -s -I -u test:test http://localhost/data/demoproject/trunk/noaccess/ | grep "location" | assert []
