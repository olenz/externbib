<?php
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
