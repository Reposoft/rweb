echo "Excluding plugins that are deprecated or in development. Also excluding repos-admin plugins."
ant -Dplugins.excludes="refresh/**,jquery.jqUploader/**,arbortext*/**,searchintegration/**,defaultaction/**,listrecursive/**,admin*/**"
