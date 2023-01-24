<?php
/**
 * Command-line tool to convert BibTeX files to a SQLite table.
 *
 * @package    ExternBib
 * @author     Olaf Lenz
 * @author     Michael Kuron
 * @author     Jean-Noël Grad
 * @copyright  2011-2014,2016,2022 The Authors
 * @license    https://opensource.org/licenses/BSD-3-Clause New BSD License
 * @link       https://github.com/olenz/externbib
 */
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'BibTex.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'tex2html.php');
require_once("bibdb.php");
require_once("makedb.php");

$script = array_shift($argv);

function usage() {
  global $script;
  error_log("Usage: $script [-h] [-?]");
  error_log("Usage: $script [-n] [-o OUTPUTDB] BIBFILE...");
  exit(2);
} 

// where to find the pdf files (in subdirs)
// default is subdir pdf of the cwd
$dryrun = false;
$pdfbase = 'pdf';
$outputdb = "externbib.db";

// command line parsing

$parameters = array(
  'h' => 'help',
  'n' => 'dryrun',
  'o:' => 'output:',
);

$options = getopt(implode('', array_keys($parameters)), $parameters);

if (array_key_exists('h', $options) 
    || array_key_exists('?', $options)
    || $argc < 2)
  usage();

if (array_key_exists('n', $options)) {
  $dryrun = true;
  echo "Dry run, won't write any files!\n";
}

if (array_key_exists('o', $options))
  $outputdb = $options['o'];

// remove getopt args from argv
$pruneargv = array();
foreach ($options as $option => $value) {
  foreach ($argv as $key => $chunk) {
    $regex = '/^'. (isset($option[1]) ? '--' : '-') . $option . '/';
    if ($chunk == $value && $argv[$key-1][0] == '-' || preg_match($regex, $chunk)) {
      array_push($pruneargv, $key);
    }
  }
}
while (($key = array_pop($pruneargv)) !== NULL) unset($argv[$key]);

$bibfiles = $argv;

$bibtex = new Structures_BibTex();
$bibtex->setOption("extractAuthors", false);
$bibtex->setOption("removeCurlyBraces", false);
$bibtex->setOption("unwrap", true);
$bibtex->setOption("storeFullEntries", true);

$outputdb_new = tempnam("/tmp", "$outputdb");
echo "Opening $outputdb_new...\n";

echo "Setting permissions on file...\n";
chmod($outputdb_new, 0644);

echo "Writing data into file...\n";
$db = bibdb_open($outputdb_new, 'n');
if (is_bool($db)) {
  error_log("ERROR: Could not open $outputdb_new for writing!");
  exit(1);
}

$modes = ConversionModes::Newlines | ConversionModes::Diacritics | ConversionModes::LaTeXMacros | ConversionModes::MathSimple | ConversionModes::StripCurlyBraces;
foreach ($bibfiles as $bibfile) {
  populate_db($db, $bibfile, $bibtex, $modes, true);
}

bibdb_close($db);
echo "Wrote all data.\n";

if ($dryrun) {
  echo "Dry run. Exiting.\n";
  exit(0);
}

// remove old file
echo "Removing $outputdb...\n";
if (file_exists($outputdb))
  unlink($outputdb);

// rename new file
echo "Renaming $outputdb_new to $outputdb...\n";
rename($outputdb_new, $outputdb);

echo "Finished.\n";
exit(0);
?>
