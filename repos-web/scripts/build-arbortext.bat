echo "Excluding plugins that are not in the standard package"
ant compress.closure -Dplugins.excludes="refresh/**,jquery.jqUploader/**,admin*/**,linehistory/**,templates/**,tinymce/**,searchintegration/**,loginconventional/**,sla/**,listrecursive/**,thumblist/**"
