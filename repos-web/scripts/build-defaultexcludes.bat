echo "Excluding plugins that are deprecated or in development"
ant compress.closure -Dplugins.excludes="refresh/**,jquery.jqUploader/**,arbortext*/**,searchintegration/**,defaultaction/**,listrecursive/**"
