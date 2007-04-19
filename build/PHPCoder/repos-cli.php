<?php
/**
 * Run phpcoder for repos from command line
 */

define("EXIT_OK", 0);
define("EXIT_ABNORMAL", 1);
define("EXIT_NOENCODER", EXIT_OK);

// skip encoding if the encoder is not available, leaving destination folder empty
if (!function_exists('eaccelerator_encode')) {
	echo "eAccelerator not available. Won't encode.\n";
	exit(EXIT_NOENCODER);
}

// check that commandline has a chance of being valid
if ( count( $argv ) != 3 ) {
	echo "Usage: php repos-cli.php /source/folder /destination/folder\n";
    exit(EXIT_ABNORMAL);
}

// put user input in variables
$source = $argv[1];
$dest = $argv[2];

// --- code below is ported from index.php in PHPCoder ---

   // get coder class
   require_once('./coder-class.php'); 

   $coder = new coder;
   
   // set directory recursion, default to true
   $coder->recursive = true;

   // determine if we should copy files that don't get encoded, on by default, turning this off will break applications
   $coder->copy_skipped_files = true;

   // set source and destination directories
   $coder->src_dir = $source ? $source : getcwd() . '/files';
   $coder->dest_dir = $dest ? $dest : getcwd() . '/encoded';

   // set file extensions
   $possible_extensions = explode(',', 'php');
   $coder->extensions = $possible_extensions;

   // set file extensions to skip
   //$coder->ignore_extensions = array();

   // set php pre and post content variables
   $coder->php_pre_content = '';
   $coder->php_post_content = '';

   // set restrictions up
   $coder->restrictions = 
       array("visitor_ip"     => "",
             "server_ip"      => "",
             "server_name"    => "",
             "expire_value"   => "",
             "expire_unit"    => "",
             "expire_english" => "");
   
// enable output buffering if we need to
ob_start();

// run
$coder->Encode();

// capture output because it is not readable in command prompt
$result = og_get_clean();
// TODO analyde

echo("Encoding completed.\n");

exit(EXIT_OK);

?>

