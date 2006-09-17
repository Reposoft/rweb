#!/bin/bash
# Repos command line runner
# First set up the environment, then run all the arguments as a command

# The argument should be the complete command escaped with single quotes

if [ $# -ne 1 ]
then
	echo "Invalid command. Should be one argument."
	exit 1
fi

export LANG="en_US.UTF-8"
export LC_ALL="en_US.UTF-8"

echo "$1" >> "/tmp/repos-php/run-log.txt"

eval "$1"
exit $?
