
curl -s -u test:test "http://localhost/repos/open/json/?target=/demoproject/trunk/public/"

curl -s -u test:test "http://localhost/repos/open/json/?target=/demoproject/trunk/public/&selector=%23list"

curl -s -u test:test "http://localhost/repos/open/json/?target=/demoproject/trunk/public/Policy document.html"

curl -s -u test:test "http://localhost/repos/open/json/?target=/demoproject/trunk/public/empty/" | grep {}
