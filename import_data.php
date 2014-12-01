<?php
/**
 * Script to import the CSV data
 * 
 * PHP version 5.3
 * 
 * @category Script
 * @package  MLS_Cell_Map
 * @author   Pierre Rudloff <contact@rudloff.pro>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     https://carto.rudloff.pro/gsm/
 * */
require_once 'config.php';
header('Content-Type: text/plain; charset=utf-8');

$csvfile = 'data/MLS-full-cell-export.csv';

//Download data
echo 'Downloading data…'.PHP_EOL;
$csv = file_get_contents(
    'https://d17pt8qph6ncyq.cloudfront.net/export/'.
    'MLS-full-cell-export-'.date('Y-m-d').'T000000.csv.gz'
);
file_put_contents($csvfile.'.gz', $csv);

//Uncompress data
echo 'Uncompressiing data…'.PHP_EOL;
$gzip = gzopen($csvfile.'.gz', 'r');
file_put_contents($csvfile, gzread($gzip, filesize($csvfile.'.gz')));
gzclose($gzip);

//PDO
$pdo = new PDO('mysql:dbname='.DBNAME.';host=localhost', DBUSER, DBPASS);
$pdo->exec("SET NAMES 'utf8';");

//Create tables
echo 'Creating tables…'.PHP_EOL;
$query = $pdo->prepare(
    file_get_contents('create_tables.sql')
);
$query->execute();

//Empty tables
echo 'Emptying tables…'.PHP_EOL;
$query = $pdo->prepare(
    "DELETE FROM `cells`;"
);
$query->execute();

//Load CSV files
echo 'Importing data…'.PHP_EOL;
$query = $pdo->prepare(
    "LOAD DATA INFILE '".__DIR__."/data/MLS-full-cell-export.csv'
    INTO TABLE `cells`
    FIELDS TERMINATED BY ','
    IGNORE 1 LINES;"
);
$query->execute();

//Done
echo 'Done!'.PHP_EOL;
?>
