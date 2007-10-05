
curl -s http://localhost/repos/edit/file/ -I | grep 412

curl -s http://localhost/repos/edit/file/ | grep parameter | grep target

curl -s http://localhost/repos/edit/file/?target=/demoproject/trunk/public/ -I | grep 401

