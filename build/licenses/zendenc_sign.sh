#!/bin/sh

if [ $# -ne 1 ]
then
  echo "Usage: zendenc_sign [license name]";
  exit 0;
fi

NAME=$1
if [ ! -e "$NAME.txt" ]
then
  echo "There is no license specification $NAME.txt"
  exit 1;
fi

if [ ! -e "./generated" ]
then
  mkdir .generated
fi

/usr/local/Zend/Guard4/bin/zendenc_sign "$NAME.txt" "./generated/$NAME.zl"

cat "./generated/$NAME.zl" | grep Verification
