<?PHP

// TODO create a generic install helper script, with these functions and also help for download

require (dirname(dirname(__FILE__)).'/conf/System.class.php');

/**
 * Downloads a URL to the local hard drive
 *
 * @param String $url the file url
 * @param String $localTargetFile the absolute path to download to, in an existing folder
 * @return int the exit code for the transfer
 */
function download($url, $localTargetFile) {
	// simple solution to introduce install check
	if (!array_key_exists('go', $_GET)) {
		echo("\nNot found. <a href=\"?go\">Install</a>.\n"); exit;	
	}
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_VERBOSE, 1);
	$fh = fopen($localTargetFile, 'w');
	curl_setopt($ch, CURLOPT_FILE, $fh);
	$result = curl_exec($ch);
	$info = curl_getinfo($ch);
	curl_close($ch);
	fclose($fh);
	// verify result
	echo ("Got $info[url] with status $info[http_code] after $info[redirect_count] redirects.\n");
	echo ("Downloaded $info[size_download] bytes of type '$info[content_type]' in $info[total_time] seconds.\n");
	return $result;
}

/**
 * Extracts ZIP to folder
 *
 * @param String $sourceFile absolute path for the compressed file
 * @param String $destination folder to extract to. Will attempt to create it if it does not exist.
 */
function decompressZip( $sourceFile, $destination) {
	if (!strEnds($destination, '/')) $destination .= '/';
	$zip = zip_open($sourceFile);

	if (!$zip) trigger_error('Could not open zip file '.$sourceFile, E_USER_ERROR);
	$dir = $destination;
   while($zip_entry = zip_read($zip)) {
       $entry = zip_entry_open($zip,$zip_entry);
       $filename = zip_entry_name($zip_entry);
       $target_dir = $dir.substr($filename,0,strrpos($filename,'/'));
       $filesize = zip_entry_filesize($zip_entry);
       if (is_dir($target_dir) || mkdir($target_dir)) {
           if ($filesize > 0) {
               $contents = zip_entry_read($zip_entry, $filesize);
               System::createFileWithContents($dir.$filename,$contents);
           }
       }
   }
}

/*
	extract GZ archive
	arg 1 is an absolute path to a gz archive
	arg 2 is the extracted file's name
	arg 3 is optional. default value is 10 000 000. it has to be larger than the extracted file 
*/
function uncompressGZ( $srcFileName, $dstFileName, $fileSize ){

	if (!is_writable(dirname($dstFileName))) {
		return false;
	}
	
	if (!$fileSize){
		$fileSize = 10000000;
	}
		
	// read in the GZ file
	$gp = gzopen( $srcFileName, "r" );
	$data = fread ( $gp, $fileSize );
	gzclose( $gp );

	// write uncompressed file
	$fp = fopen( $dstFileName, "w" );
	fwrite( $fp, $data );
	fclose( $fp );
	return true;
}

/*
	extract TAR archive
	arg 1 is an absolute path to a gz archive
	arg 2 is the extracted file's name. it is optional. default value is the same path as the tar file
	arg 3 is optional. it should be used only if a special directory from the tar file is needed.  
*/
function uncompressTAR( $srcFileName, $dstDirectory = null, $unpackDir = null ){

	if (!$dstDirectory){
		$dstDirectory = dirname(realpath($srcFileName))."/";
		$dstDirectory = str_replace('\\', '/', $dstDirectory);
	}
	// read in the TAR file
	$fp = fopen($srcFileName, "rb");
	$tar_file = fread($fp, filesize($srcFileName));
	fclose($fp);

	$tar_length = strlen($tar_file);
	$offset = 0;

	while($offset < $tar_length) {
	
		//  end of the archive consists of 512 nulls
		if(substr($tar_file,$offset,512) == str_repeat(chr(0),512)){
			return true;
		}
		
		/*TARs consist of 512-byte blocks. Each constituent file is preceded by a 512-byte 
		header with the file's name and some other info, all in ASCII. Padding fills out any 
		block that doesn't need all 512 bytes--header blocks, the empty part of a constituent 
		file's last block, and sometimes one or more totally empty blocks.*/
		
		// first 100 characters of the file are reserved for the filename followed by nulls
		$file_name		= rtrim(substr($tar_file,$offset,100),chr(0));
		// filesize information is 12 characters long and it starts at 124th character
		$file_size		= octdec(substr($tar_file,$offset + 124,12));
		// the actual file starts at 512th character
		$file_contents	= substr($tar_file,$offset + 512,$file_size);

		// Create directories
		
		if ($file_name{strlen($file_name)-1} == "/"){
			$delTrailSlash = rtrim($file_name, "/");
			$directory_array = explode("/", $delTrailSlash);	// trailing slash causes explode to make an empty cell in the array
			$_file = null;
		} else {
			$directory_array = explode("/", dirname($file_name));
			$_file = basename($file_name);
		}

		$j = $dstDirectory;
 		if ($unpackDir){
			if (in_array($unpackDir, $directory_array)){
				$k = array_search($unpackDir, $directory_array);			//find the directory which you want to unpack in the TAR file
				$newDirArray = array_slice($directory_array, $k);			//remove all of it's parentdirectories
				$unpdir = implode("/", $newDirArray)."/";					//and construct a new path
				foreach($newDirArray as $l){
					$j = $j.$l."/";
					if (!is_dir($j)){
						//echo "dir: $j\n";
						mkdir($j);
					}
				}
				if ($_file){
					//echo "Extracted: ".$dstDirectory.$unpdir.$_file."\n";
					$fileFromTar = fopen($dstDirectory.$unpdir.$_file ,"wb");
					fwrite($fileFromTar,$file_contents);
					fclose($fileFromTar);
				}
			}
		} else {
			foreach($directory_array as $i){
				$j = $j.$i."/";
				if (!is_dir($j)){
					//echo $j."\n";
					mkdir($j);
				}
			} 
			// Create files
			if ($_file){
			 	//echo $dstDirectory.$file_name."\n";
				$fileFromTar = fopen($dstDirectory.$file_name,"wb");
				fwrite($fileFromTar,$file_contents);
				fclose($fileFromTar);
			}
		}

		// move offset to the beginning of the next file
		$offset += 512 + (ceil($file_size / 512) * 512);
	}
}
?>