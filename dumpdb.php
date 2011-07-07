<?php

if ($argc < 3) {
  echo "Usage: $argv[0] DBFILE KEY...\n";
  exit(2);
}

array_shift($argv);
$dbfile = array_shift($argv);
$keys = $argv;

$bib = dba_open($dbfile, "rd");

foreach ($keys as $key) {
  $value = dba_fetch($key, $bib);
  $entry = unserialize($value);
  echo "--------------------------------------------------\n";
  print_r($entry);
}

dba_close($bib);

?>
