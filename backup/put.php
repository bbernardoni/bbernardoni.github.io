<?php
// Put data is read from stdin
$putData = fopen("php://input", "r");

// Open file pointer to output file
$backupBase = "/var/www/backup";
$filename = $backupBase.$_SERVER['REQUEST_URI'];

if(!file_exists(dirname($filename)))
	mkdir(dirname($filename), 0755, true);
$outFile = fopen($filename, "w");

// Copy file
while($data = fread($putData, 1024))
	fwrite($outFile, $data);

// Close the streams
fclose($outFile);
fclose($putData);
?>
