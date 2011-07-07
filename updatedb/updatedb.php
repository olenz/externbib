<?php
require('php/BibTex.php');

// constants

// this script should only be called from this directory
$basedir = "/web/icp/public_html/bib";

// where to find the bibtex files
$bibtexbase = "$basedir/bibtex";
// where to find the pdf files (in subdirs)
$pdfbase = "$basedir/pdf";

// which bibtexfiles are to be used
// - the .bib is found in $bibtexbase/$file.bib
// - the pdf-files are found in $pdfbase/$file/$entry.pdf
$bibtexfiles = array("icp");

// where to save the bibtex entries
$bibentrydir = "$basedir/entries";
// where to save the PHP database
$dbfile = "$basedir/icpdb.dat";

// option parsing
$dryrun = false;
foreach ($argv as $arg) {
  if ($arg == "-n") {
    echo "Dry run, won't actually modify anything!\n";
    $dryrun = true;
  }
}

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
      '--' => '-', 
      '\\-' => '',
      '~' => ' ',
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

foreach ($bibtexfiles as $file) {
  $bibtexfile="$bibtexbase/$file.bib";
  $pdfdir="$file";
  
  print "Loading $bibtexfile...\n";
  $ret    = $bibtex->loadFile($bibtexfile);
  if (PEAR::isError($ret)) {
    die($ret->getMessage());
  }
  
  print "Parsing data...\n";
  $bibtex->parse();
  if ($bibtex->hasWarning()) {
    foreach ($bibtex->warnings as $warning) {
      echo "WARNING: " . $warning['warning'] . " (line \"" . $warning['entry'] . "\")\n";
    }
  }
  
  print "Found " . $bibtex->amount() . " entries.\n";

  print "Sorting data into array ...\n";
  foreach ($bibtex->data as $entry) {
// create associative array
    $bibtexkey  = $entry['cite'];
    $bibarray[$bibtexkey]['pdfdir'] = $pdfdir;
    $fullentries[$bibtexkey] = $entry["fullEntry"];
    if (isset($entry["abstract"])) 
      $abstracts[$bibtexkey] = $entry["abstract"];
    foreach ($entry as $key=>$value) {
      $value=ltrim($value);
      $value=rtrim($value);
      if ($value != "" && $key != "fullEntry")
	$bibarray[$bibtexkey][$key] = bibtex2utf8($value); 
    }
  }
}

print "Read all data.\n";

if ($dryrun) {
  echo "Dry run. Exiting.\n";
  exit(0);
 }

// Now start output
print "Cleaning up $bibentrydir...\n";
$n=0;
foreach (glob("$bibentrydir/*") as $filename) {
  $n++;
  unlink($filename);
}
print "Deleted $n files.\n";

print "Creating .bib-files ...\n";
$n=0;
foreach ($fullentries as $bibtexkey => $fullEntry) {
  $n++;
  $filename = "$bibentrydir/$bibtexkey.bib";
  $fh = fopen($filename, "w+");
  fwrite($fh, $fullEntry);
  fclose($fh);
}
print "Created $n files.\n";

print "Creating abstract-files ...\n";
$n=0;
foreach ($abstracts as $bibtexkey => $abstract) {
  $n++;
  $filename = "$bibentrydir/$bibtexkey-abs.html";
  $fh = fopen($filename, "w+");
  fwrite($fh, "<html><body>$abstract</body></html>");
  fclose($fh);
}
print "Created $n files.\n";
 
// serialize and save the array
print "Serializing and saving data to $dbfile...\n";
$bibsave = serialize($bibarray);
if (file_exists($dbfile)) unlink($dbfile);
$fh = fopen($dbfile, "w+");
fwrite($fh, $bibsave);
fclose($fh);

print "Finished.\n";

?>
