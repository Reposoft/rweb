<?php

$reposGraphicsTransforms = array(
	'default' => 'ReposGraphicsTransformThumb',
	'tiny' => 'ReposGraphicsTransformTiny',
	'thumb' => 'ReposGraphicsTransformThumb',
	'preview' => 'ReposGraphicsTransformPreview',
	'screen' => 'ReposGraphicsTransformScreen',
	'extract' => 'ReposGraphicsTransformExtract',
	'png' => 'ReposGraphicsTransformPng'
);

/**
 * Interface defining the parameters available when creating transforms.
 */
class ReposGraphicsTransformSource {
	
	/**
	 * @return string the filename extension, could be both upper and lowe case
	 */
	function getExtension() {}
	
}

/**
 * Interface defining a graphics transform.
 */
class ReposGraphicsTransform {

	/**
	 * @param ReposGraphicsTransformSource $file
	 */
	function ReposGraphicsTransform($source) {}
	
	/**
	 * @return output width in pixels
	 */
	function getWidth() {}
	
	/**
	 * @return output height in pixels
	 */
	function getHeight() {}
	
	/**
	 * @return string Output file format, typically png or jpeg
	 */
	function getOutputFormat() {}
	
}

/**
 * Standard behavior for pre-defined transforms.
 */
class ReposGraphicsTransformBase extends ReposGraphicsTransform {
	
	function ReposGraphicsTransformBase($source) {
		$this->source = $source;
	}
	
	function getOutputFormat() {
		// jpeg is generally smaller than png but graphicsmagick produced some invalid images for line art in jpg
		if (preg_match('/^jpe?g|raw/i', $this->source->getExtension())) {
			return 'jpeg';
		}
		return 'png';
	}
	
}

class ReposGraphicsTransformTiny extends ReposGraphicsTransformBase {

	function getWidth() {
		return 75;
	}

	function getHeight() {
		return 75;
	}

}

class ReposGraphicsTransformThumb extends ReposGraphicsTransformBase {
	
	function getWidth() {
		return 150;
	}
	
	function getHeight() {
		return 150;
	}	
	
}

class ReposGraphicsTransformPreview extends ReposGraphicsTransformBase {

	function getWidth() {
		return 400;
	}

	function getHeight() {
		return 400;
	}

}

class ReposGraphicsTransformScreen extends ReposGraphicsTransformBase {

	function getWidth() {
		return 960;
	}

	function getHeight() {
		return 720;
	}

}

class ReposGraphicsTransformExtract extends ReposGraphicsTransformBase {

	function getOutputFormat() {
		// jpeg is generally smaller than png but graphicsmagick produced some invalid images for line art in jpg
		if (preg_match('/^pdf$/i', $this->source->getExtension())) {
			return 'pdf';
		}
		handleError(0, 'Operation "extract" only supported for format pdf');
	}
	
	function getCustomCommand($extension, $tempfile) {
		if (!isset($_REQUEST['page'])) {
			handleError(0, 'Missing page parameter for extract operation');
		}
		$page = $_REQUEST['page'];
		unset($_REQUEST['page']); // disable default page handling
		return 'pdftk - cat '.$page.' output "'.$tempfile.'"';
	}

}

class ReposGraphicsTransformNoresize {

	function getWidth() {
		return false;
	}

	function getHeight() {
		return false;
	}

}

/**
 * Interface defining the parameters available when creating transforms.
 */
class ReposGraphicsTransformPng extends ReposGraphicsTransformNoresize {

        function getOutputFormat() {
                return 'png';
        }

}

?>
