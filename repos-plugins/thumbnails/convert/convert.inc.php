<?php
/**
 * @return the command for image transformation using GraphicsMagick arguments
 */
function convertGetCommand() {
	// ImageMagick in path
	//return 'convert';
	//return 'convert -auto-orient';
	// GraphicsMagick in path: 
	return 'gm convert';
}
?>
