#!/usr/bin/perl -w

use strict;
use warnings;
use Apache::Test;

my $host="localhost:8530";

# login to public url should redirect there without login
print `curl "http://$host/?login&go=/data/demoproject/trunk/public/"`;
# only 'go' parameter means implicit login
print `curl "http://$host/?go=/data/demoproject/trunk/public/"`;

print `curl "http://$host/?login=&go=/data/demoproject/trunk/public/" -sI | grep Location`;
# expect "Location: http://localhost:8530/data/demoproject/trunk/public/"

# normally using meta refresh so that html page is displayed during redirect to login
print `curl "http://$host/?login" | grep refresh`;

# login to auth url should redirect to login page
print `curl "http://$host/?login&go=/data/demoproject/trunk/"`;

print `curl "http://$host/?go=/data/demoproject/trunk/" -sI | grep 401`;
# expect "HTTP/1.1 401 Authorization Required"

# ... then send credentials like a browser would do
print `curl "http://test:test\@$host/?go=/data/demoproject/trunk/" -sI | grep Location`;
# expect "Location: http://localhost:8530/data/demoproject/trunk/"

#'This user does not havve acces to the project, but to a subfolder
print `curl "http://Test User:test\@$host/data/demoproject/trunk/public/"`;
print `curl "http://Test User:test\@$host/data/demoproject/trunk/public/" -sI | grep "200 OK"`;

