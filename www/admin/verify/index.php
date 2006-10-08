<?php

require('../repos-backup.inc.php' );

$backupFolder = getConfig('backup_folder');

verifyMD5($backupFolder);

$report->info('<p><a id="back" href="../" class="action">return to admin page</a></p>');

$report->display();

?>