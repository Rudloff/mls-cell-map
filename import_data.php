<?php
/**
 * Script to import the CSV data.
 */

use League\CLImate\CLImate;
use MlsCellMap\Importer;

require_once __DIR__.'/config.php';
require_once __DIR__.'/vendor/autoload.php';

if (defined('STDOUT')) {
    $importer = new Importer();
    $importer->run();
}
