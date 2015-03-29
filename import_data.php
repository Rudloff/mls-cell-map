#!/usr/bin/php
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

$csvfile = __DIR__.'/data/MLS-full-cell-export.csv';

//Download data
echo 'Downloading data…'.PHP_EOL;
$csv = file_get_contents(
    'https://d17pt8qph6ncyq.cloudfront.net/export/'.
    'MLS-full-cell-export-'.date('Y-m-d').'T000000.csv.gz'
);
file_put_contents($csvfile.'.gz', $csv);

//Uncompress data
echo 'Uncompressing data…'.PHP_EOL;
$gzip = gzopen($csvfile.'.gz', 'r');
$csv = '';
while (!gzeof($gzip)) {
    $csv .= gzread($gzip, 4096);
}
file_put_contents($csvfile, $csv);
gzclose($gzip);

//PDO
$pdo = new PDO('mysql:dbname='.DBNAME.';host=localhost', DBUSER, DBPASS);
$pdo->exec("SET NAMES 'utf8';");

//Delete tables
echo 'Deleting tables…'.PHP_EOL;
$query = $pdo->prepare(
    "DROP TABLE `cells`;
    DROP TABLE `cells_mnc`;
    DROP TABLE `cells_country`;"
);
$query->execute();

//Create tables
echo 'Creating tables…'.PHP_EOL;
$query = $pdo->prepare(
    file_get_contents('create_tables.sql')
);
$query->execute();

//Load CSV files
echo 'Importing data…'.PHP_EOL;
$query = $pdo->prepare(
    "LOAD DATA INFILE '".$csvfile."'
    INTO TABLE `cells`
    FIELDS TERMINATED BY ','
    IGNORE 1 LINES;"
);
$query->execute();

//Import MNC
$query = $pdo->prepare(
    'INSERT INTO cells_mnc (MNC, MCC, Network) VALUES (:mnc, :mcc, :net)'
);
$mnclist = json_decode(
    file_get_contents(
        'https://raw.githubusercontent.com/andymckay/mobile-codes/master/'.
        'mobile_codes/json/mnc_operators.json'
    )
);
foreach ($mnclist as $mnc) {
    $query->execute(
        array(
            ':mcc'=>$mnc[0],
            ':mnc'=>$mnc[1],
            ':net'=>$mnc[2]
        )
    );
}

//Import MCC
$query = $pdo->prepare(
    'INSERT INTO cells_country (MCC, Country) VALUES (:mcc, :country)'
);
$mcclist = json_decode(
    file_get_contents(
        'https://raw.githubusercontent.com/andymckay/mobile-codes/master/'.
        'mobile_codes/json/countries.json'
    )
);
foreach ($mcclist as $mcc) {
    if (is_array($mcc[4])) {
        foreach ($mcc[4] as $submcc) {
            $query->execute(
                array(
                    ':mcc'=>$submcc,
                    ':country'=>$mcc[0]
                )
            );
        }
    } else {
        $query->execute(
            array(
                ':mcc'=>$mcc[4],
                ':country'=>$mcc[0]
            )
        );
    }
}

//Timestamp
echo 'Writing timestamp…'.PHP_EOL;
file_put_contents(
    __DIR__.'/data/timestamp.json', json_encode(new DateTime()).PHP_EOL
);

//Done
echo 'Done!'.PHP_EOL;
?>
