<?php
/**
 * Returns GeoJSON data.
 *
 * PHP version 5.4
 *
 * @category AJAX
 *
 * @author   Pierre Rudloff <contact@rudloff.pro>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 *
 * @link     https://carto.rudloff.pro/gsm/
 * */
header('Content-Type: application/json; charset=UTF-8');
require_once '../config.php';
$pdo = new PDO('mysql:dbname='.DBNAME.';host='.DBHOST.';port='.DBPORT, DBUSER, DBPASS);
$bbox = explode(',', $_GET['bbox']);

$query = $pdo->prepare(
    "SELECT lon, lat, radio, mcc, cell, net, area, samples, `range`, created, updated
    FROM cells
    WHERE lon > :bb1 AND lon < :bb3
    AND lat > :bb2 AND lat < :bb4
    AND !(radio = 'UMTS' AND cell <=65535)
    AND !(radio = 'GSM' AND cell = 65535)
    AND !(radio = 'UMTS' AND cell = 2147483647)
    AND `range` > 0
    AND samples > 1
    GROUP BY cell"
);
$query->execute(
    [
        ':bb1' => $bbox[0],
        ':bb2' => $bbox[1],
        ':bb3' => $bbox[2],
        ':bb4' => $bbox[3],
    ]
);
$cells = $query->fetchAll(PDO::FETCH_ASSOC);
$output = ['type' => 'FeatureCollection'];
$features = [];
$mncquery = $pdo->prepare(
    'SELECT Network
    FROM cells_mnc
    WHERE `MCC` = :mcc AND `MNC` = :mnc'
);
$mccquery = $pdo->prepare(
    'SELECT Country
    FROM cells_country
    WHERE `MCC` = :mcc;'
);
foreach ($cells as $cell) {
    if ($cell['net'] < 10) {
        $mnc = '0'.$cell['net'];
    } else {
        $mnc = $cell['net'];
    }
    $mncquery->execute(
        [
            ':mcc' => $cell['mcc'],
            ':mnc' => $mnc,
        ]
    );
    $network = $mncquery->fetch(PDO::FETCH_ASSOC);
    if (isset($network['Network'])) {
        $mccquery->execute(
            [
                ':mcc' => $cell['mcc'],
            ]
        );
        $country = $mccquery->fetch(PDO::FETCH_ASSOC);
        $features[] = [
            'type'     => 'Feature',
            'geometry' => [
                'type'        => 'Point',
                'coordinates' => [floatval($cell['lon']), floatval($cell['lat'])],
            ],
            'properties' => [
                'radio'    => $cell['radio'],
                'mcc'      => $cell['mcc'],
                'net'      => $cell['net'],
                'cell'     => $cell['cell'],
                'area'     => $cell['area'],
                'samples'  => $cell['samples'],
                'range'    => $cell['range'],
                'created'  => $cell['created'],
                'updated'  => $cell['updated'],
                'country'  => $country['Country'],
                'operator' => $network['Network'],
            ],
        ];
    }
}
$output['features'] = $features;
echo json_encode($output);
