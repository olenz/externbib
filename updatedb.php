<?php
// include include/BibTex.php
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR 
	     . 'include' .  DIRECTORY_SEPARATOR . 'BibTex.php');
require_once("bibdb.php");
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

// translation of bibtex chars to utf8 chars
$bibtex2utf8_array=
array(
      '\\"a' => "ä", 
      '\\"A' => "Ä",
      '\\"e' => "ë",
      '\\"E' => "Ë",
      '\\"i' => "ï",
      '\\"I' => "Ï",
      '\\"o' => "ö", 
      '\\"O' => "Ö",
      '\\"u' => "ü", 
      '\\"U' => "Ü",
      '\\\'a' => "á", 
      '\\\'A' => "Á", 
      '\\\'e' => "é", 
      '\\\'E' => "É",
      '\\\'i' => "í", 
      '\\\'I' => "Í",
      '\\\'o' => "ó", 
      '\\\'O' => "Ó",
      '\\\'u' => "ú", 
      '\\\'U' => "Ú",
      '\\^a' => "â", 
      '\\^A' => "Â",
      '\\^e' => "ê", 
      '\\^E' => "Ê",
      '\\^i' => "î", 
      '\\^I' => "Î",
      '\\^o' => "ô", 
      '\\^O' => "Ô",
      '\\^u' => "û", 
      '\\^U' => "Û",
      '\\`a' => "à", 
      '\\`A' => "À",
      '\\`e' => "è", 
      '\\`E' => "È",
      '\\`i' => "ì", 
      '\\`I' => "Ì",
      '\\`o' => "ò", 
      '\\`O' => "Ò",
      '\\`u' => "ù", 
      '\\`U' => "Ù",
      '\\aa' => "å", 
      '\\AA' => "Å",
      '\\ae' => "æ",
      '\\AE' => "Æ",
      '\\o' => "ø", 
      '\\O' => "Ø",
      '\\c c' => "ç", 
      '\\c C' => "Ç",
      '---' => '&mdash;',
      '--' => '–', 
      '\\-' => '',
      '~' => ' ',
      '\\vs' => "&scaron;",
      '\\&' => "&",
      );
			 
$bibtexenc=array_keys($bibtex2utf8_array);
$utf8enc=array_values($bibtex2utf8_array);

// Remove bibtex chars from the string
function bibtex2utf8($string) {
  global $bibtexenc;
  global $utf8enc;

  $string = preg_replace('/([^\\\]|^)\}/', '$1' ,$string);
  $string = preg_replace('/([^\\\]|^)\{/', '$1' ,$string);
  $string = str_replace($bibtexenc, $utf8enc, $string);
  return $string;
}

$bibtex = new Structures_BibTex();
$bibtex->setOption("extractAuthors", false);
$bibtex->setOption("removeCurlyBraces", true);
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

foreach ($bibfiles as $bibfile) {
  echo "Loading $bibfile...\n";
  $ret    = $bibtex->loadFile($bibfile);
  if (PEAR::isError($ret)) {
    die($ret->getMessage());
  }
  
  echo "Parsing $bibfile...\n";
  $bibtex->parse();
  if ($bibtex->hasWarning()) {
    foreach ($bibtex->warnings as $warning) {
      error_log("WARNING: " . $warning['warning'] 
		. " (line \"" . $warning['entry'] . "\")");
    }
  }
  
  echo "Found " . $bibtex->amount() . " entries.\n";

  foreach ($bibtex->data as $entry) {
    // create associative array
    $key  = $entry['cite'];

    // UTF8-ify and trim entry
    $cleanentry = array();
    foreach ($entry as $k=>$v) {
      $v = trim($v);
      if ($v != "" && $k != "fullEntry")
	$v = bibtex2utf8($v); 
      $cleanentry[$k] = $v;
    }
    $value = serialize($cleanentry);
    bibdb_insert($key, $value, $db);
  }
}


bibdb_close($db);
echo "Wrote all data.\n";


if ($dryrun) {
  echo "Dry run. Exiting.\n";
  exit(0);
 } else {

  // remove old file
  echo "Removing $outputdb...\n";
  if (file_exists($outputdb))
    unlink($outputdb);
  
  // rename new file 
  echo "Renaming $outputdb_new to $outputdb...\n";
  rename($outputdb_new, $outputdb);
  
  echo "Finished.\n";
  exit(0);
 }
?>
