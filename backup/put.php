<?php
// Put data is read from stdin
$putData = fopen("php://input", "r");

// Open file pointer to output file
if(substr($_SERVER['REQUEST_URI'],0,16) != "/backup/put.php/")
	exit();
$backupBase = "/var/www/backup";
$filename = $backupBase.substr($_SERVER['REQUEST_URI'],15);

if(!file_exists(dirname($filename)))
	mkdir(dirname($filename), 0755, true);
$outFile = fopen($filename, "w");

// Copy file
while($data = fread($putData, 1024))
	fwrite($outFile, $data);

// Close the streams
fclose($outFile);
fclose($putData);

$indicesServer = array('PHP_SELF', 
'argv', 
'argc', 
'GATEWAY_INTERFACE', 
'SERVER_ADDR', 
'SERVER_NAME', 
'SERVER_SOFTWARE', 
'SERVER_PROTOCOL', 
'REQUEST_METHOD', 
'REQUEST_TIME', 
'REQUEST_TIME_FLOAT', 
'QUERY_STRING', 
'DOCUMENT_ROOT', 
'HTTP_ACCEPT', 
'HTTP_ACCEPT_CHARSET', 
'HTTP_ACCEPT_ENCODING', 
'HTTP_ACCEPT_LANGUAGE', 
'HTTP_CONNECTION', 
'HTTP_HOST', 
'HTTP_REFERER', 
'HTTP_USER_AGENT', 
'HTTPS', 
'REMOTE_ADDR', 
'REMOTE_HOST', 
'REMOTE_PORT', 
'REMOTE_USER', 
'REDIRECT_REMOTE_USER', 
'SCRIPT_FILENAME', 
'SERVER_ADMIN', 
'SERVER_PORT', 
'SERVER_SIGNATURE', 
'PATH_TRANSLATED', 
'SCRIPT_NAME', 
'REQUEST_URI', 
'PHP_AUTH_DIGEST', 
'PHP_AUTH_USER', 
'PHP_AUTH_PW', 
'AUTH_TYPE', 
'PATH_INFO', 
'ORIG_PATH_INFO') ; 

foreach ($indicesServer as $arg) { 
    if (isset($_SERVER[$arg])) { 
        echo $arg.' = '.$_SERVER[$arg]; 
    } 
    else { 
        echo $arg.' = NOPE'; 
    } 
}
?>
