#!/usr/bin/php
<?php
/**
 * Script to import the CSV data.
 */
require_once __DIR__.'/config.php';
header('Content-Type: text/plain; charset=utf-8');

$csvfile = __DIR__.'/data/MLS-full-cell-export.csv';

//Download data

$csv = fopen($csvfile.'.gz', 'w+');
$date = new DateTime();
$date->sub(new DateInterval('P1D'));
$csvurl = 'https://d17pt8qph6ncyq.cloudfront.net/export/'.
    'MLS-full-cell-export-'.$date->format('Y-m-d').'T000000.csv.gz';
echo 'Downloading data from '.$csvurl.'…'.PHP_EOL;
$distcsv = fopen($csvurl, 'r');
if (!is_resource($distcsv) || !is_resource($csv)) {
    die("Couldn't download data…".PHP_EOL);
}
while (!feof($distcsv)) {
    fwrite($csv, fread($distcsv, 8192));
}

//Uncompress data
echo 'Uncompressing data…'.PHP_EOL;
$gzip = gzopen($csvfile.'.gz', 'r');
$csv = '';
if (!is_resource($gzip)) {
    die("Couldn't read gzip data…".PHP_EOL);
}
file_put_contents($csvfile, '');
while (!gzeof($gzip)) {
    file_put_contents($csvfile, gzread($gzip, 4096), FILE_APPEND);
}
gzclose($gzip);

//PDO
$pdo = new PDO(
    'mysql:dbname='.DBNAME.';host='.DBHOST.';port='.DBPORT, DBUSER, DBPASS
);
$pdo->exec("SET NAMES 'utf8';");

//Delete tables
echo 'Deleting tables…'.PHP_EOL;
$query = $pdo->prepare(
    'DROP TABLE `cells`;
    DROP TABLE `cells_mnc`;
    DROP TABLE `cells_country`;'
);
$query->execute();

//Create tables
echo 'Creating tables…'.PHP_EOL;
$query = $pdo->prepare(
    file_get_contents(__DIR__.'/create_tables.sql')
);
$query->execute();

//Load CSV files
echo 'Importing data…'.PHP_EOL;
$query = $pdo->prepare(
    "LOAD DATA LOCAL INFILE '".$csvfile."'
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
        [
            ':mcc' => $mnc[0],
            ':mnc' => $mnc[1],
            ':net' => $mnc[2],
        ]
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
                [
                    ':mcc'     => $submcc,
                    ':country' => $mcc[0],
                ]
            );
        }
    } else {
        $query->execute(
            [
                ':mcc'     => $mcc[4],
                ':country' => $mcc[0],
            ]
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
