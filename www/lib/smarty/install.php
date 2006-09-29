<HTML>
<HEAD>
</HEAD>
<BODY>
<PRE>
<?PHP

$home = "http://smarty.php.net";
$version = "2.6.14";
$archive = "$home/distributions/Smarty-$version";
$file = "Smarty-$version";

$basedir = dirname(__FILE__);
$dir_backslash = rtrim($basedir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
$dir = str_replace('\\', '/', $dir_backslash);


uncompress($archive.".tar.gz", $dir.$file.".tar", 10000000 );
function uncompress( $srcFileName, $dstFileName, $fileSize ){
	// getting content of the compressed file
	$zp = gzopen( $srcFileName, "r" );
	$data = fread ( $zp, $fileSize );
	gzclose( $zp );

	// writing uncompressed file
	$fp = fopen( $dstFileName, "w" );
	fwrite( $fp, $data );
	fclose( $fp );
}

$filename = $dir.$file.".tar";

// Read in the TAR file
$fp = fopen($filename, "rb");
$tar_file = fread($fp, filesize($filename));
fclose($fp);

$tar_length = strlen($tar_file);
$offset = 0;
while($offset < $tar_length) {
	// If we read a block of 512 nulls, we are at the end of the archive
	if(substr($tar_file,$offset,512) == str_repeat(chr(0),512)){
		echo "done";
		break;
	}

	$file_name		= rtrim(substr($tar_file,$offset,100),chr(0));
	$file_size		= octdec(substr($tar_file,$offset + 124,12));
	$file_contents	= substr($tar_file,$offset + 512,$file_size);
	
	if (strpos($file_name, "libs/")) {
		$newpath = substr($file_name, strpos($file_name, "libs/"));
		echo $dir.$newpath."\n";
		if ($file_size == 0 && !is_dir($dir.$newpath) && $newpath != "."){
			mkdir($dir.$newpath);
		} elseif ($file_size > 0) {
			$fileFromTar = fopen($dir.$newpath,"wb");
			fwrite($fileFromTar,$file_contents);
			fclose($fileFromTar);
		}
	}
	
	$offset += 512 + (ceil($file_size / 512) * 512);
}

unlink($dir.$file.".tar");


?> 
</PRE>
</BODY>
</HTML>