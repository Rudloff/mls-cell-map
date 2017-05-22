<?php
/**
 * Script to create the config file.
 */
$env = getenv('DBNAME');
if (isset($env)) {
    $config = "<?php
    define('DBNAME', '".getenv('DBNAME')."');
    define('DBUSER', '".getenv('DBUSER')."');
    define('DBPASS', '".getenv('DBPASS')."');
    define('DBHOST', '".getenv('DBHOST')."');
    define('DBPORT', '".getenv('DBPORT')."');";
    file_put_contents('config.php', $config.PHP_EOL);
}
