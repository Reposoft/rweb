
$host="http://localhost:8530";

# login to public url should redirect there without login
curl $host/?login&go=/data/demoproject/trunk/public/
# only 'go' parameter means implicit login
curl $host/?go=/data/demoproject/trunk/public/

curl "http://localhost:8530/?login=&go=/data/demoproject/trunk/public/" -sI | grep Location
# expect "Location: http://localhost:8530/data/demoproject/trunk/public/"

# normally using meta refresh so that html page is displayed during redirect to login
http://localhost:8530/?login | grep refresh

# login to auth url should redirect to login page
curl $host/?login&go=/data/demoproject/trunk/

curl "http://localhost:8530/?go=/data/demoproject/trunk/" -sI | grep 401
# expect "HTTP/1.1 401 Authorization Required"

# ... then send credentials like a browser would do
curl "http://test:test@localhost:8530/?go=/data/demoproject/trunk/" -sI | grep Location
# expect "Location: http://localhost:8530/data/demoproject/trunk/"

