<?php
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
	// delete file
	unlink($filename);
	break;
}
?>
