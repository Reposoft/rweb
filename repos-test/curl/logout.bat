
# log out when not logged in (same as logout when login wasn't done through /?login)
curl -s http://localhost/?logout
curl -s http://localhost/?logout -I | grep no-cache
# ...has to be tested in browsers. log in to a repository url, log out, go back to repo

# log out when logged in
curl -s http://localhost/?logout -u test:test | grep Refresh | grep logout=verify
# HTTP spec is quite clear about requiring a WWW-Authenticate for every 401, but we must be pragmatic
curl -s http://localhost/?logout -u test:test -I | grep "401 Unauthorized"

#
curl -s -A "Mozilla/5.0 (Windows; MSIE 6.0; Win 9x 4.90)" -u test:test http://localhost/?logout | grep Refresh 

curl -s -A "Mozilla/5.0 (Windows; MSIE 6.0; Win 9x 4.90)" -u test:test http://localhost/?logout=verify | grep ClearAuthenticationCache

# check username cookie handling?