:: There has been some problems with apache on windows and locks

:: The problem seems to occur only with the Apache Lounge binaries, not the official binaries

:: Might say svn: Lock request failed: 400 Bad Request (http://localhost)
svn lock http://localhost/data/demoproject/trunk/Policy%20document.html

:: Might make the Apache HTTP Server process crash
svn list --xml "http://localhost/data/demoproject/trunk/public/locked-file.txt@HEAD"
