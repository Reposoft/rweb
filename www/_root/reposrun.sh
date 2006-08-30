# arguments
#1: the command
#2: all arguments
if [ $# != 2 ]
then
	echo "Invalid command, $# arguments"
	exit 1
fi

export LANG="en_US.UTF-8"
export LC_ALL="en_US.UTF-8"
RESULT=$("$1 $2 2>&1")
exit RESULT;
