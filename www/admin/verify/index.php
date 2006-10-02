<?php

require('../repos-backup.inc.php' );

$backupFolder = getConfig('backup_folder');

verifyMD5($backupFolder);

?>