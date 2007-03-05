
# run in repos root

for F in conf account admin edit open plugins test view
do
	echo "$F"
	find $F -name "*.php" \
		| grep -v ".test" \
		| xargs cat \
		| grep "[;{]" \
		| wc -l
	find $F -name "*.php" \
                | grep -v ".test" \
                | xargs cat \
                | grep "\(//\|\\*\\*\| \\* \)" \
                | wc -l
	find $F -name "*.php" \
                | grep ".test" \
                | xargs cat \
                | grep "[;{]" \
                | wc -l
done

#echo "html:"
#find . -name "*.html" \
#        | xargs cat \
#        | wc -l

