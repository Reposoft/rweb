echo "Excluding plugins that are deprecated or in development, and integration plugins"
ant -Dplugins.excludes="refresh/**,jquery.jqUploader/**,arbortext*/**,searchintegration/**,defaultaction/**,listrecursive/**,*-integration/**"
