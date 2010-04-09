<?php
// thumbnail resize types
define('FILE_SET_WIDTH', 0);
define('FILE_SET_HEIGHT', 1);
define('FILE_FORCE_DIM', 2);

// thumbnail prefix type
define('FILE_PAD_LEFT', 0);
define('FILE_PAD_RIGHT', 1);

class ImageResize {
	var $_filename;
	var $_prefix;
	var $_prefixPos;
	var $_fixDimension;
	
	function ImageResize($filename) {
		$this->_filename = $filename;
 		$this->_prefix = "_thumb"; 
		$this->_resizeType=FILE_SET_WIDTH; 
		$this->_prefixPos=FILE_PAD_RIGHT;
	}
	
	//
	// public method - resize function
	//
	function Resize($desW, $desH, $desFilename='', $resize_type=0) {
		// set destination filename
		if ($desFilename == '') $desFilename = $this->_filename;
		// get file attributes
		list($width, $height, $type, $attr) = getimagesize($this->_filename);
		// determine whether image requires resizing
		$resize = 0;
		$crop = 0;
		$this->_fixDimension=$resize_type;
		// determine image proportions
		switch ($this->_fixDimension) {
			case FILE_SET_WIDTH :
				// do we need to resize this image?
				if ($width > $desW) $resize = 1;
				// what is the ratio between the current width and the destination width?
				$multiplier = $width/$desW;
				// modify detination height
				$desH = $height/$multiplier;
				break;
				
			case FILE_SET_HEIGHT :
				// do we need to resize this image?
				if ($height > $desH) $resize = 1;
				// what is the ratio between the current width and the destination width?
				$multiplier = $height/$desH;
				// modify detination width
				$desW = $width/$multiplier;
				break;
				
			case FILE_FORCE_DIM :
				$resize = 1;
				$crop = 1;
				break;
		}
		# retreive source image
		$src_file = $this->_imagecreatefrom($type, $this->_filename);
		# create image holder
		$des_file = imagecreatetruecolor($desW, $desH);
		if ($resize==1) {
			# resize and resample
			if (function_exists('imagecopyresampled')) {
				imagecopyresampled( $des_file, $src_file, 0, 0, 0, 0, $desW, $desH, $width, $height);
				# write to file
				$this->_image($type, $des_file, $desFilename, 95);
			} else {
				imagecopyresized( $des_file, $src_file, 0, 0, 0, 0, $desW, $desH, $width, $height);
				# write to file
				$this->_image($type, $des_file, $desFilename, 95);
			}
		}
		
		# close resource
		imagedestroy($src_file);
		imagedestroy($des_file);
		return $desFilename;
	}
	
	//
	// public method - CreateThumbnail creates a thumbnail for the uploaded file.
	// it is an extension of the resize function.
	//
	function CreateThumb($desW, $desH, $resize_type = 0) {
		// build thumbnail filename
		$desFilename = '';
		$parts = pathinfo($this->_filename);
		if ($this->_prefixPos == FILE_PAD_LEFT) {
			$desFilename = $parts['dirname'].'/'.$this->_prefix.$parts['basename']; 
		} elseif ($this->_prefixPos == FILE_PAD_RIGHT) {
			$desFilename = $parts['dirname'].'/'.substr($parts['basename'],0,-(1+strpos(strrev($parts['basename']),'.'))).$this->_prefix.'.'.$parts['extension']; 
		}
		return $this->Resize($desW, $desH, $desFilename, $resize_type);
	}
	
	function _imagecreatefrom($type, $filename) {
		switch ($type) {
			case IMAGETYPE_GIF : // image/gif
				return imagecreatefromgif($filename);

			case IMAGETYPE_JPEG : // image/jpeg
				return imagecreatefromjpeg($filename);
				
			case IMAGETYPE_PNG : // image/png
				return imagecreatefrompng($filename);
		}
	}
	
	function _image($type, $image, $filename, $quality=100) {
		switch ($type) {
			case IMAGETYPE_GIF : // image/gif
				imagegif($image, $filename);
				break;
				
			case IMAGETYPE_JPEG : // image/jpeg
				imagejpeg($image, $filename, $quality);
				break;
				
			case IMAGETYPE_PNG : // image/png
				imagepng($image, $filename);
				break;
		}
	}
}
?>