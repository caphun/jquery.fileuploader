<?php
// status
// - total: total size of file
// - current: amount received so far
// - rate: the upload speed in bytes per second
// - filename: name of file
// - name: name of the variable
// - temp_filename: where PHP is saving that temporary copy
// - cancel_upload: whether the upload has been cancelled[1|0]
// - done: whether the upload is complete [1|0]

session_start();

$progressKey = isset($_REQUEST['progress_key']) ? $_REQUEST['progress_key'] : '';

if (!empty($progressKey)) {
	$status = fetchProgress('upload_' . $progressKey);
	echo $status['current']/$status['total']*100;
}


function fetchProgress($key) {
	return $_SESSION[$key];
}