#!/bin/bash

total=0
failures=0

# you need the source to do this, which isn't very build-contract friendly

TESTS=$(find . -name *.test.php | sed 's|^\.|http://svn|')
for TEST in $TESTS; do
  (( total++ ))
  # some tests fail with text reporter, so use html for now
  #curl -u test:t "$TEST?serv=tap" | grep '0 failed, 0 exceptions'
  RESULT=$(curl -u test:t "$TEST" -s | grep 'class="testsummary')
  [ -z "$RESULT" ] && {
    (( failures++ ))
    echo "$TEST did not finish"
  } || {
    ASSERTED=$(echo "$RESULT" | grep -v 'passed, <strong>0</strong> failed, <strong>0</strong> exceptions')
    [ -z "$ASSERTED" ] && {
      echo "$TEST passed"
    } || {
      (( failures++ ))
      echo "$TEST failed: $RESULT"
    }
  }
done

echo "$failures of $total test files failed"
exit $failures
