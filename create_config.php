<?php
/**
 * Script to create the config file
 * 
 * PHP version 5.3
 * 
 * @category Script
 * @package  MLS_Cell_Map
 * @author   Pierre Rudloff <contact@rudloff.pro>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     https://carto.rudloff.pro/gsm/
 * */
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
