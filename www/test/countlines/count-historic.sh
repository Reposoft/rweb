
cd ..
cd ..

DATE=$(svn info --xml . | grep "<date>" | awk '{print substr($0,7,27)}')
LAST=""

while [ "$DATE" != "$LAST" ]
do
	D=$(echo "$DATE" | awk '{print substr($0,0,10)}')
	L=$(echo "$LAST" | awk '{print substr($0,0,10)}')
	if [ "$D" != "$L" ]
	then
	 echo "$DATE"
	 test/countlines/countlines.sh
	fi

	svn update -r PREV . | grep "Updated to revision"
	LAST="$DATE"
	DATE=$(svn info --xml . | grep "<date>" | awk '{print substr($0,7,27)}')
done
