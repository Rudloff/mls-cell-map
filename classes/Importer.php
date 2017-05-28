<?php
/**
 * Importer class.
 */

namespace MlsCellMap;

use League\CLImate\CLImate;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Import cells data into our database.
 */
class Importer
{
    /**
     * CLImate instance.
     *
     * @var CLImate
     */
    private $climate;

    /**
     * Guzzle client instance.
     *
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * Path to downloaded CSV file.
     *
     * @var string
     */
    private $csvfile;

    /**
     * PDO instance.
     *
     * @var \PDO
     */
    private $pdo;

    /**
     * Importer class constructor.
     */
    public function __construct()
    {
        $this->climate = new CLImate();
        $this->client = new \GuzzleHttp\Client();
        $this->csvfile = __DIR__.'/../data/MLS-full-cell-export.csv';
        $this->pdo = new \PDO(
            'mysql:dbname='.DBNAME.';host='.DBHOST.';port='.DBPORT, DBUSER, DBPASS,
            [\PDO::MYSQL_ATTR_LOCAL_INFILE => true]
        );
        $this->pdo->exec("SET NAMES 'utf8';");
    }

    /**
     * Calculate the size of a gzip file.
     *
     * @param string $filename Filename
     *
     * @return int Size (in bytes)
     */
    private function getGzipFullsize($filename)
    {
        $builder = new ProcessBuilder(['gzip', '-l', $filename]);
        $process = $builder->getProcess();
        $process->run();
        preg_match('/^\s+\d+\s+(\d+)/m', $process->getOutput(), $matches);

        return (int) $matches[1];
    }

    /**
     * Download CSV data.
     *
     * @return void
     */
    private function download()
    {
        $csv = fopen($this->csvfile.'.gz', 'w+');
        $date = new \DateTime();
        $date->sub(new \DateInterval('P1D'));
        $csvurl = 'https://d17pt8qph6ncyq.cloudfront.net/export/'.
            'MLS-full-cell-export-'.$date->format('Y-m-d').'T000000.csv.gz';
        $this->climate->info('Downloading data from '.$csvurl.'…');
        $response = $this->client->request('GET', $csvurl, [
            'stream' => true,
        ]);
        $length = $response->getHeader('Content-Length');
        $progress = $this->climate->progress()->total($length[0]);
        $body = $response->getBody();
        while (!$body->eof()) {
            $progress->advance(fwrite($csv, $body->read(8192)));
        }
        fclose($csv);
    }

    /**
     * Uncompress CSV data.
     *
     * @return void
     */
    private function uncompress()
    {
        $this->climate->info('Uncompressing data…');
        $gzip = gzopen($this->csvfile.'.gz', 'r');

        if (!is_resource($gzip)) {
            $this->climate->error("Couldn't read gzip data…");
            die;
        }
        file_put_contents($this->csvfile, '');
        $progress = $this->climate->progress()->total(self::getGzipFullsize($this->csvfile.'.gz'));
        while (!gzeof($gzip)) {
            $data = gzread($gzip, 4096);
            file_put_contents($this->csvfile, $data, FILE_APPEND);
            $progress->advance(strlen($data));
        }
        gzclose($gzip);
    }

    /**
     * Delete existing SQL tables.
     *
     * @return void
     */
    private function clearTables()
    {
        $this->climate->info('Deleting tables…');
        $query = $this->pdo->prepare(
            'DROP TABLE `cells`;
            DROP TABLE `cells_mnc`;
            DROP TABLE `cells_country`;'
        );
        $query->execute();
    }

    /**
     * Create SQL tables.
     *
     * @return void
     */
    private function createTables()
    {
        $this->climate->info('Creating tables…');
        $query = $this->pdo->prepare(
            file_get_contents(__DIR__.'/../create_tables.sql')
        );
        $query->execute();
    }

    /**
     * Import cells data.
     *
     * @return void
     */
    private function importCells()
    {
        $this->climate->info('Importing data…');
        $query = $this->pdo->prepare(
            "LOAD DATA LOCAL INFILE '".$this->csvfile."'
            INTO TABLE `cells`
            FIELDS TERMINATED BY ','
            IGNORE 1 LINES;"
        );
        $query->execute();
    }

    /**
     * Import MNC data.
     *
     * @return void
     */
    private function importMnc()
    {
        $query = $this->pdo->prepare(
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
    }

    /**
     * Import MCC data.
     *
     * @return void
     */
    private function importMcc()
    {
        $query = $this->pdo->prepare(
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
    }

    /**
     * Write timestamp file.
     *
     * @return void
     */
    private function writeTimestamp()
    {
        $this->climate->info('Writing timestamp…');
        file_put_contents(
            __DIR__.'/../data/timestamp.json', json_encode(new \DateTime()).PHP_EOL
        );
    }

    /**
     * Run importer.
     *
     * @return void
     */
    public function run()
    {
        $this->download();
        $this->uncompress();
        $this->clearTables();
        $this->createTables();
        $this->importCells();
        $this->importMnc();
        $this->importMcc();
        $this->writeTimestamp();
        $this->climate->shout('Done!');
    }
}
