<?php

require( dirname(__FILE__) . '/repos-backup.inc.php' );

backupFolder = getConfig('backup_folder');

verifyMD5(backupFolder);

?>