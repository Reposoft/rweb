REM install PEAR and do: pear upgrade PhpDocumentor
REM you might need to upgrad pear first: pear upgrade PEAR

echo Assuming that this script's working folder is the folder where the script is.
cd ..
cd ..
SET REPOS_WEB=%CD%
cd lib
cd phpdoc
call phpdoc -t "%REPOS_WEB%-docs" -o HTML:frames:default -d "%REPOS_WEB%" -i "%REPOS_WEB%\lib\*, %REPOS_WEB%\test\*, *.test.*" -ti "Repos PHP documentation" > docs-report.txt

start %REPOS_WEB%-docs\index.html

call phpdoc -t "%REPOS_WEB%-docs\repos-test-docs" -o HTML:frames:default -f "%REPOS_WEB%\test\*, %REPOS_WEB%\*.test.*" -i "%REPOS_WEB%\lib\*" -ti "Repos PHP test documentation" > docs-test-report.txt
