<?php
   header('WWW-Authenticate: Basic realm=""');
   header('HTTP/1.0 401 Unauthorized');
   echo 'You have been logged out';
?>