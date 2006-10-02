SET REPOS_WEB=C:\srv\www\htdocs\repos
phpdoc -t "%REPOS_WEB%-docs" -o HTML:frames:default -d "%REPOS_WEB%" -i "%REPOS_WEB%\lib\*.*" > docs-report.txt