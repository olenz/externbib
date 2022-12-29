<?php
/**
 * Dump a SQLite database to the ASCII format.
 *
 * @package    ExternBib
 * @author     Olaf Lenz
 * @author     Michael Kuron
 * @copyright  2011,2016 The Authors
 * @license    https://opensource.org/licenses/BSD-3-Clause New BSD License
 * @link       https://github.com/olenz/externbib
 */
require_once("bibdb.php");
if ($argc < 3) {
  echo "Usage: $argv[0] DBFILE KEY...\n";
  exit(2);
}

array_shift($argv);
$dbfile = array_shift($argv);
$keys = $argv;

$bib = bibdb_open($dbfile, "rd");

foreach ($keys as $key) {
  $value = bibdb_fetch($key, $bib);
  $entry = unserialize($value);
  echo "--------------------------------------------------\n";
  print_r($entry);
}

bibdb_close($bib);

?>
