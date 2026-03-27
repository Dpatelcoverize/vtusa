<?php
@ini_set('implicit_flush', 1);
@ini_set('output_buffering', 'off');
@ini_set('zlib.output_compression', 0);

ob_implicit_flush(1);
ob_end_flush();

// Secure with a custom key
// if (!isset($_GET['key']) || $_GET['key'] !== 'MY_SECRET_KEY_123') {
//     http_response_code(403);
//     exit("Forbidden");
// }

$cmd = '/usr/bin/ea-php82 /home/portalvitaltrend/Backup_Manager_Wasabi/backup_script.php';

// run the script and show output in real-time
$proc = popen($cmd, 'r');
while (!feof($proc)) {
    echo fgets($proc);
    flush();
}
pclose($proc);
