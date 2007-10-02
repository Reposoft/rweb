
# verify creation and redirect-after post
curl -u test:test "http://localhost/repos/edit/mkdir/?target=/test/trunk/&submit=T&name=new&message=test" -I | grep "Refresh: 1" | assert

# now the folder already exists
curl -u test:test "http://localhost/repos/edit/mkdir/?target=/test/trunk/&submit=T&name=new&message=test" -I | grep "412 Preconditions" | assert
