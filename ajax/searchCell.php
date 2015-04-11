<?php
/**
 * Returns a cell position
 * 
 * PHP version 5.4
 * 
 * @category AJAX
 * @package  MLS_Cell_Map
 * @author   Pierre Rudloff <contact@rudloff.pro>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     https://carto.rudloff.pro/gsm/
 * */
header('Content-Type: application/json; charset=UTF-8');
if (isset($_GET['cell_id'])) {
    include_once '../config.php';
    $pdo = new PDO('mysql:dbname='.DBNAME.';host='.DBHOST, DBUSER, DBPASS);


    $query = $pdo->prepare(
        "SELECT lon, lat
        FROM cells
        WHERE mcc = :mcc AND net = :mnc
        AND cell = :cell_id AND area = :lac
        LIMIT 1;"
    );
    $query->execute(
        array(
            ':mcc'=>$_GET['mcc'],
            ':mnc'=>$_GET['mnc'],
            ':cell_id'=>$_GET['cell_id'],
            ':lac'=>$_GET['lac']
        )
    );

    $cell = $query->fetch(PDO::FETCH_ASSOC);

    echo json_encode($cell);
}
?>
