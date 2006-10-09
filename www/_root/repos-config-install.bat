@echo "Installing repos-config folder next to repos, without .svn metadata."
svn --config-dir repos-config\svn-config-dir export repos-config ..\..\repos-config
cd ..\..\repos-config
@dir
@echo installed
@pause

