<?php
// set default timezone
$timezone = "America/Chicago";
date_default_timezone_set($timezone);

// Check signin
require_once '../vendor/autoload.php';
$CLIENT_ID = "473922392138-v288ikuqsq22maakmldm8entgnu5cuu7.apps.googleusercontent.com";
$client = new Google_Client(["client_id" => $CLIENT_ID]);
$id_token = $_SERVER['HTTP_TOKEN'];
$payload = $client->verifyIdToken($id_token);
if ($payload) {
	$userid = $payload['sub'];
	if($userid != "116801955654281384888"){
		// exit if not me
		exit();
	}
}
else{
	// exit if invalid token
	exit();
}

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
if(isset($_SERVER['PATH_INFO']))
	$filename = $backupBase.$_SERVER['PATH_INFO'];
else
	$filename = $backupBase.'/';

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
			// Get filesize
			$io = popen("/usr/bin/du -sk $filename/$file", "r");
			$bytes = fgets($io, 4096);
			$bytes = 1024*(int)substr($bytes, 0, strpos($bytes, "\t"));
			$size = humanFilesize($bytes);
			pclose($io);
		
			// Get last modified
			$dt = new DateTime("@".filemtime("$filename/$file"));
			$dt->setTimeZone(new DateTimeZone($timezone));
			$time = $dt->format("n/j/Y G:i:s");
		
			// Get type
			$type = "folder";
			
			$data["files"][] = ["name"=>$file, "lastModified"=>$time, "size"=>$size, "type"=>$type];
		}
	}
	foreach($files as $file){
		if(!is_dir("$filename/$file")){
			// Get filesize
			$size = humanFilesize(filesize("$filename/$file"));
		
			// Get last modified
			$dt = new DateTime("@".filemtime("$filename/$file"));
			$dt->setTimeZone(new DateTimeZone($timezone));
			$time = $dt->format("n/j/Y G:i:s");
			
			// Get type
			$type = pathinfo($file, PATHINFO_EXTENSION);
			
			$data["files"][] = ["name"=>$file, "lastModified"=>$time, "size"=>$size, "type"=>$type];
		}
	}

	// Write data as JSON
	header('Content-type: application/json');
	echo json_encode($data);
	break;
}
?>
