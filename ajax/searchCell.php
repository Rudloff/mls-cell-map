<?php
/**
 * Returns a cell position.
 */
header('Content-Type: application/json; charset=UTF-8');
if (isset($_GET['cell_id'])) {
    include_once '../config.php';
    $pdo = new PDO('mysql:dbname='.DBNAME.';host='.DBHOST.';port='.DBPORT, DBUSER, DBPASS);

    $query = $pdo->prepare(
        'SELECT lon, lat
        FROM cells
        WHERE mcc = :mcc AND net = :mnc
        AND cell = :cell_id AND area = :lac
        LIMIT 1;'
    );
    $query->execute(
        [
            ':mcc'     => $_GET['mcc'],
            ':mnc'     => $_GET['mnc'],
            ':cell_id' => $_GET['cell_id'],
            ':lac'     => $_GET['lac'],
        ]
    );

    $cell = $query->fetch(PDO::FETCH_ASSOC);

    echo json_encode($cell);
}
