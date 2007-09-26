# show the list as plain json
curl -s -u test:test "http://localhost/repos/open/json/?target=/demoproject/trunk/public/" | grep kind
# when tere is a selector a script function should be returned
curl -s -u test:test "http://localhost/repos/open/json/?target=/demoproject/trunk/public/&selector=%23list" | grep function
# selector without # or . should be converted ID selector
curl -s -u test:test "http://localhost/repos/open/json/?target=/demoproject/trunk/public/&selector=list" | grep #list
# list single file to get metadata
curl -s -u test:test "http://localhost/repos/open/json/?target=/demoproject/trunk/Policy%20document.html" | grep size
# empty folder should return empty list
curl -s -u test:test "http://localhost/repos/open/json/?target=/demoproject/trunk/empty/" | grep list

# also make sure that startpage can be shown as json
curl -s -u test:test http://localhost/repos/open/start/?serv=json | grep entrypoints
