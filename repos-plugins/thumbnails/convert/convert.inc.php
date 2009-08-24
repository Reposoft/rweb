<?php
/**
 * @return the command for image transformation using GraphicsMagick arguments
 */
function convertGetCommand() {
	// ImageMagick in path
	//return 'convert';
	// GraphicsMagick in path: 
	return 'gm convert';
}
?>
