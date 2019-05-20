<?php
// Recursive delete function
function delRec($path){ 
	if(!is_dir($path))
		return unlink($path);
	$files = array_diff(scandir($path), array('.','..'));
	foreach($files as $file)
		delRec("$path/$file");
	return rmdir($path); 
}
// Get human filesizes
function humanFilesize($bytes) {
	if($bytes < 1024)
		return "$bytes B";
	$sz = ' KMGTP';
	$factor = floor(log($bytes, 1024));
	$value = $bytes / pow(1024, $factor);
	$precision = max(2-floor(log($value, 10)), 0);
	return sprintf("%.{$precision}f ", $value).$sz[(int)$factor]."B";
}

// Get filename from path info
$backupBase = "/var/www/backup";
$filename = $backupBase.$_SERVER['PATH_INFO'];

switch($_SERVER['REQUEST_METHOD']){
case 'PUT':
	// Put data is read from stdin
	$putData = fopen("php://input", "r");

	// Open file pointer to output file
	if(!file_exists(dirname($filename)))
		mkdir(dirname($filename), 0755, true);
	$outFile = fopen($filename, "w");

	// Copy file
	while($data = fread($putData, 1024))
		fwrite($outFile, $data);

	// Close the streams
	fclose($outFile);
	fclose($putData);
	break;
case 'DELETE':
	// Delete file
	delRec($filename);
	break;
case 'GET':
	// Get data
	$data = ["files" => []];
	$files = array_diff(scandir($filename), array('.','..')); 
	foreach($files as $file){
		if(is_dir("$filename/$file")){
			$io = popen("/usr/bin/du -sk $filename/$file", "r");
			$bytes = fgets($io, 4096);
			$bytes = 1024*(int)substr($bytes, 0, strpos($bytes, "\t"));
			pclose($io);
		}
		else{
			$bytes = filesize("$filename/$file");
		}
		$size = humanFilesize($bytes);
		$time = date("n/j/Y G:i:s", filemtime("$filename/$file"));
		$data["files"][] = ["name"=>$file, "lastModified"=>$time, "size"=>$size];
	}

	// Write data as JSON
	header('Content-type: application/json');
	echo json_encode($data);
	break;
}
?>
