<?php
require_once ("FileUpload.class.php");
require_once ("ImageResize.class.php");

// setup response
$data = array('response' => null, 'status' => null);

// UPLOAD FILE
$folder = '/uploads/';
$w = $h = 0;
$tw = $th = 0;

$options = isset($_POST['options']) ? $_POST['options'] : null;
if (count($options) > 0) {
	$folder = isset($options['upload_dir']) ? $options['upload_dir'] : $folder;
	$w = isset($options['w']) ? $options['w'] : $w;
	$h = isset($options['h']) ? $options['h'] : $h;
	$tw = isset($options['tw']) ? $options['tw'] : $tw;
	$th = isset($options['th']) ? $options['th'] : $th;
}

if (isset($_FILES)) {
	foreach ($_FILES as $file) {
		$upd = new FileUpload();
		$result = $upd->Put($file, realpath(dirname(__FILE__) . '/../../../') . $folder );
		if ($result) {
			$data['response'] = array('filename' => basename($upd->GetFile(0)), 'url' => $folder . basename($upd->GetFile(0)), 'filepath' => $upd->GetFile(0));
			
			// create thumbnail
			if ($tw > 0) {
				$ir = new ImageResize($data['response']['filepath']);
				$thumb = $ir->CreateThumb($tw, $th);
				$data['response']['thumbnail'] = array('filename' => basename($thumb), 'url' => $folder . basename($thumb), 'filepath' => $thumb);
			}
			
			// resize original file
			if ($w > 0) {
				$large = $ir->Resize($w, $h);
			}
			
		} else {
			$data['response'] = $upd->ShowErrors();
		}
		$data['status'] = ($result) ? 'OK' : 'ERROR';
	}
}

// DELETE FILE
$file = isset($_REQUEST['filepath']) ? $_REQUEST['filepath'] : '';
$thumb = isset($_REQUEST['thumbnail']) ? $_REQUEST['thumbnail'] : '';
if (file_exists($file)) { unlink($file); }
if (file_exists($thumb)) { unlink($thumb); }

if (file_exists($file)) {
	
	unlink($file);

}

echo "[".json_encode($data)."]";