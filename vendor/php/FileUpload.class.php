<?php
// max upload size 500K
ini_set('upload_max_filesize', 512000);

// upload constants
define('FILE_NO_ERROR', 0);
define('FILE_EXCEED_INI_MAX_SIZE', 1);
define('FILE_EXCEED_HTML_MAX_SIZE', 2);
define('FILE_PARTIAL_UPLOAD', 3);
define('FILE_NOT_UPLOADED', 4);
define('FILE_UNSPECIFIED_ERROR', 5);
define('FILE_WRONG_FORMAT', 6);
define('FILE_ATTACK', 7);
define('FILE_WRONG_EXT', 8);

class FileUpload
{ // begin FileUpload

	var $maxSize; // string
	var $errorList = array();
	var $fileList = array();
	var $genList = array();
		
	function FileUpload() {
		$this->initGenericErrorMsg();
	}

	// public method - upload the file.
	// $file - posted file data
	// $filePath - destination of uploaded file
	function Put($file, $filePath) {
		$errno = $this->checkFile($file);

		if ($errno == FILE_NO_ERROR) {
			$uploadDir = $filePath . (substr($filePath, -1) !== '/' ? '/':'');
			chmod($uploadDir, 0777);
			$uploadName = $file['name'];
			if (move_uploaded_file($file['tmp_name'], ($uploadDir.$uploadName))) {
				$this->fileList[] = $uploadDir . $uploadName;
				return true;
			} else
				$this->AddError($this->genList[FILE_ATTACK], FILE_ATTACK);
		}
		else
			$this->AddError($this->genList[$errno], $errno);	
		return false;
	}

	function initGenericErrorMsg() {
		$list = array();
		$list[FILE_EXCEED_INI_MAX_SIZE] 	= 'Failed! UPLOAD EXCEEDS "UPLOAD_MAX_FILESIZE" DIRECTIVE';
		$list[FILE_EXCEED_HTML_MAX_SIZE] 	= 'Failed! UPLOAD EXCEEDS "MAX_FILE_SIZE" DIRECTIVE';
		$list[FILE_PARTIAL_UPLOAD] 			= 'Failed! PARTIAL UPLOAD';
		$list[FILE_NOT_UPLOADED] 			= 'Failed! NO FILE UPLOADED';
		$list[FILE_UNSPECIFIED_ERROR] 		= 'Failed! UNSPECIFIED ERROR';
		$list[FILE_WRONG_FORMAT] 			= $list[FILE_UNSPECIFIED_ERROR];
		$list[FILE_ATTACK] 					= 'Failed! Possible file upload attack!';
		$list[FILE_WRONG_EXT]				= 'Incorrect file format. JPEG required';
		$this->genList = $list; 
	}
	
	function checkFile($f) {
		$err = FILE_NO_ERROR;
		// check for file extension is jpg
		/*if (FILE_NO_ERROR==$err) {
			$parts = pathinfo($f['name']);
			$err = ( ('jpg'==strtolower($parts['extension'])) || ('jpeg'==strtolower($parts['extension'])) )?FILE_NO_ERROR:FILE_WRONG_EXT;
		}*/
		// check for potential upload errors
		if (FILE_NO_ERROR==$err) {
			$err = (array_key_exists('error', $f)) ? $f['error'] : FILE_UNSPECIFIED_ERROR;
		}
		return $err;
	}

	function ShowErrors() {
		$output = '';
		if (0 != count($this->errorList)) {
			foreach($this->errorList as $err) {
				$output .= $err;
			}
			return $output;
		}
		return false;
	}	
	
	function AddError($msg, $key=null) {
		$this->errorList[$key] = $msg;
	}
	
	//
	// Accessor Methods
	//
	function GetFile($key) {
		return $this->fileList[$key];
	}
	function SetFile($key, $a) {
		$this->fileList[$key] = $a;
	}
	
} // end FileUpload