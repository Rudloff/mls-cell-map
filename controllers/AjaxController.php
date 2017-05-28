<?php
/**
 * AjaxController class.
 */

namespace MlsCellMap\Controller;

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Manage AJAX requests.
 */
class AjaxController
{
    /**
     * PDO instance.
     *
     * @var \PDO
     */
    private $pdo;

    /**
     * AjaxController class constructor.
     */
    public function __construct()
    {
        $this->pdo = new \PDO('mysql:dbname='.DBNAME.';host='.DBHOST.';port='.DBPORT, DBUSER, DBPASS);
        $this->pdo->exec("SET NAMES 'utf8';");
    }

    /**
     * Return cells in specified bounding box.
     *
     * @param Request  $request  PSR request
     * @param Response $response PSR response
     * @param array    $data     Request parameters
     *
     * @return Response
     */
    public function get(Request $request, Response $response, array $data)
    {
        $bbox = explode(',', $data['bbox']);
        if (count($bbox) != 4) {
            throw new \Exception('The bounding box must contain four values.');
        }

        $query = $this->pdo->prepare(
            "SELECT lon, lat, radio, mcc, cell, net, area, samples, `range`, created, updated
            FROM cells
            WHERE lon > :bb1 AND lon < :bb3
            AND lat > :bb2 AND lat < :bb4
            AND !(radio = 'UMTS' AND cell <=65535)
            AND !(radio = 'GSM' AND cell = 65535)
            AND !(radio = 'UMTS' AND cell = 2147483647)
            AND `range` > 0
            AND samples > 1;"
        );
        $query->execute(
            [
                ':bb1' => $bbox[0],
                ':bb2' => $bbox[1],
                ':bb3' => $bbox[2],
                ':bb4' => $bbox[3],
            ]
        );
        $cells = $query->fetchAll(\PDO::FETCH_ASSOC);
        $output = ['type' => 'FeatureCollection'];
        $features = [];
        $mncquery = $this->pdo->prepare(
            'SELECT Network
            FROM cells_mnc
            WHERE `MCC` = :mcc AND `MNC` = :mnc'
        );
        $mccquery = $this->pdo->prepare(
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
            $network = $mncquery->fetch(\PDO::FETCH_ASSOC);
            if (isset($network['Network'])) {
                $mccquery->execute(
                    [
                        ':mcc' => $cell['mcc'],
                    ]
                );
                $country = $mccquery->fetch(\PDO::FETCH_ASSOC);
                $features[] = [
                    'type'     => 'Feature',
                    'geometry' => [
                        'type'        => 'Point',
                        'coordinates' => [(float) $cell['lon'], (float) $cell['lat']],
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

        return $response->withJson($output);
    }

    /**
     * Get a cell by its ID.
     *
     * @param Request  $request  PSR request
     * @param Response $response PSR response
     * @param array    $data     Request parameters
     *
     * @return Response
     */
    public function search(Request $request, Response $response, array $data)
    {
        $query = $this->pdo->prepare(
            'SELECT lon, lat
            FROM cells
            WHERE mcc = :mcc AND net = :mnc
            AND cell = :cell_id AND area = :lac
            LIMIT 1;'
        );
        $query->execute(
            [
                ':mcc'     => $data['mcc'],
                ':mnc'     => $data['mnc'],
                ':cell_id' => $data['cell_id'],
                ':lac'     => $data['lac'],
            ]
        );

        $cell = $query->fetch(\PDO::FETCH_ASSOC);

        return $response->withJson($cell);
    }
}
