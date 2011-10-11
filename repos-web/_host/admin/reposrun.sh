#!/bin/bash
# Repos command line runner: set up the environment, then run the single argument as a command

if [ $# -ne 1 ]
then
	echo "Invalid command. Should be one argument."
	exit 1
fi

export LANG="en_US.UTF-8"
export LC_ALL="en_US.UTF-8"

eval "$1"
exit $?
