<?php
###################
# plugin modified by Christopher Wagner
# all modifications marked with a MOD statement
# extension: possible to write a file with author name with array of bibtex entries as content, sort by year
###################
require_once( "$IP/includes/SpecialPage.php" );

# Interface to mediawiki
$wgExtensionFunctions[]        = 'bib_setup';

# setup the module
function bib_setup() {
  global $wgParser, $wgMessageCache;
  
# register the bibentry tag (former "bibcite")
  $wgParser->setHook( "bibentry", "bibentry" );
  
# setup the special page
  $wgMessageCache->addMessage('bibsearch', 'Search in the bibtex database');
  SpecialPage::addPage( new BibSearch );
}

# Constants
$bib['dir'] = "/home/simbio/public_html/bib";
$bib['bibfile'] = $bib['dir'] . "/simbiodb.dat";
$bib['pdfdir'] = $bib['dir'] . "/pdf";
$bib['entrylinkbase'] = "/~simbio/bib/entries";
$bib['pdflinkbase'] = "/~simbio/bib/pdf";
$bib['doibase'] = "http://dx.doi.org";
$bib['eprintbase'] = "http://arxiv.org/abs";
$bib_authors=array("0"=>array("0"=>"0",1));
$bib_authors["0"]["0"]="1";

##################################################
# Handle <bibentry>
##################################################
function bibentry( $input, $argv, &$parser ) {
  global $bib;

# TODO: check whether this can be avoided
# disable the cache
  $parser->disableCache();

# read the data
  bib_readdata();

# parse $input and split it into entries
  $input=ltrim($input);
  $input=rtrim($input);
  $entries = preg_split("/[\s,]+/", $input);

# start writing into the output buffer
  ob_start();

  echo "<ul class=\"plainlinks\">\n";
  foreach ($entries as $entry)
    bib_format($entry, $argv);
  echo "</ul>\n";
  
# MOD: write author arrays in files 
  bib_write_authors($argv["write"]);
# end MOD

# get everything from the output buffer
  $output = ob_get_contents();
  ob_end_clean();
  return $output;
}


##################################################
# BibSearch special page
##################################################
class BibSearch extends SpecialPage {
  function BibSearch() {
    SpecialPage::SpecialPage( 'BibSearch' );
  }
    
  function execute( $par = null ) {
    global $wgOut, $wgRequest, $wgUser, $bib;

    # setup
    $this->setHeaders();
    $wgOut->setPagetitle( 'Search the bibtex database' );
    
# if the page is called via ~simbio/Special:BibSearch/PAR?bla=blub
#   $par contains "PAR"
# In this function, the following can be used:
# $wgUser->isLoggedIn() 
#   - test whether the user is logged in
# $wgRequest->getVal( 'searchbib' )
#   - get the value of the parameter searchbib
# $wgRequest->getText( 'searchbib' )
#   - get a text from the parameter searchbib
# $wgOut->addHTML($out);
#   - output $out in the page

    ob_start();

    $query = $wgRequest->getVal('query');

?>
<p>
   Enter your query (e.g. author=holm and author=deserno and title="mesh up" and year>1998):<br/>

  <form name="searchform2" action="" method="get">
    <input style="width:400px" type="text" name="query" value="<?php print htmlspecialchars($query) ?>"/>
    <input type="submit" value="Search"/>
  </form>
</p>
<?php

    if ($query) {
      echo "<h2>Results</h2>\n";

      // print results
      $found_entries=bib_search($query);
      if (!is_array($found_entries)) {
	echo '<p class="error">Error in query: ' . $found_entries . "</p>\n";
      } elseif (count($found_entries) == 0) {
	echo "<p class=\"error\">Query returned no results!</p>\n";
      } else {
	if ($wgUser->isLoggedIn()) {
	  $format_options = array( "meta" => 1, "pdflink" => 1, );
	} else {
	  $format_options = array();
	}

	echo "<p>Your query returned " . count($found_entries) . " entries.</p>\n";

	echo "<ul class=\"plainlinks\">\n";
	foreach ($found_entries as $entry)
	  bib_format($entry, $format_options);
	echo "</ul>\n";
      }
    }

    $output = ob_get_contents();
    ob_end_clean();
    $wgOut->addHTML($output);
  }
}


##################################################
# helper functions
##################################################
# Read the data from the datafile
# requires the global variable $bibdatafile to be set
function bib_readdata() {
  global $bib;

# only read the data if it was not already read
  if (!isset($bib['data'])) {
    $fh = fopen($bib['bibfile'], "r");
    $bib['data'] = 
      unserialize(fread($fh,filesize($bib['bibfile'])));
    fclose($fh);
  }
}

# returns whether the field $key for the current entry is set
function issetb($key) {
  global $bib;
  return isset($bib['current_entry'][$key]);
}

# if $key is set in the current entry, 
# return the entry (formatted with $format)
# otherwise return the default
function getb($key, $default = "", $format="%s") {
  global $bib;
#  $bib['used'][$key] = 1;
  if (isset($bib['current_entry'][$key])) 
    return sprintf($format, $bib['current_entry'][$key]);
  else return $default;
}


# returns the human readable filesize
function hfilesize($file){
  $size = filesize($file);
  $i=0;
  $iec = array("B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB");
  while (($size/1024)>1) {
   $size=$size/1024;
   $i++;
  }
  $size = ceil($size);
  
  return "$size $iec[$i]";
}

# Format $entry
# parameters: 
#  - abstract: show the abstract inline
#  - pdflink: show links to the pdf files (if available)
#  - meta: show the timestamp, owner and the key
#  - compact: insert line breaks or not
function bib_format($entry, $argv=array()) {

  # $bib contains all the data
  global $bib;

  # current entry is required by getv
  $bib['current_entry'] = $bib['data'][$entry];

  # set defaults
  $compact="no";
  $abstract="no";
  $pdflink="no";
  $meta="no";

  # parse options
  foreach ($argv as $option => $value) {
    switch ($option) {
    case "abstract":
      $abstract = $value;
      break;
    case "pdflink":
      $pdflink = $value;
      break;
    case "meta":
      $meta = $value;
      break;
    case "compact":
      $compact = $value;
      break;
    }
  }
  
  $abstract = ($abstract == "yes" || $abstract == "1");
  $pdflink = ($pdflink == "yes" || $pdflink == "1");
  $meta = ($meta == "yes" || $meta == "1");
  $compact = ($compact == "yes" || $compact == "1");
    
#####
# check whether the entry is superseded by another one
  if (issetb("superseded")) {
    if ($meta) {
      $superseded = getb("superseded");
      echo "<li>Entry $entry\n";
      echo "<a href=\"" . $bib['entrylinkbase'] . "/$entry.bib\">[BibTeX]</a>\n";
      echo " is superseded by entry $superseded ";
      echo "<a href=\"" . $bib['entrylinkbase'] . "/$superseded.bib\">[BibTeX]</a>\n";
      echo ".</li>\n";
    }
    return;
  } 

  echo "<li>\n";
  
#####
# main formatting

  if ($meta) echo "[$entry]<br/>\n";

  echo "<b>". getb("author", "Unknown author") . ". </b>";
  
# MOD: cache the Bibtex-Entry with author and year
  bib_cache_author(getb("author", "Unknown author"),getb("year", "unknown"),$entry);
# end MOD

  if (!$compact) echo "<br/>";
  echo "\n";
  
  echo getb("title", "Unknown title.", "\"%s\".");
  if (!$compact) echo "<br/>";
  echo "\n";

  switch (getb("entryType")) {
  case "article":
    echo "<i>" . getb("journal", "Unknown journal") . "</i>\n";
    echo getb("volume");
    echo getb("number", "", "(%s)");
    echo getb("pages", "", "(%s)");
    echo getb("year", "", ", <b>%s</b>");
    echo ".\n";
    break;

  case "book":
    echo getb("series", "", "<i>%s</i>, ");
    echo getb("publisher", "Unknown publisher", "%s");
    echo getb("address", "", ", %s");
    echo getb("year", "", ", <b>%s</b>"); 
    echo ". \n";
    break;

  case "inbook":
  case "incollection":
    echo "In ";
    echo "<i>" . getb("booktitle", "unknown booktitle") . "</i>";
    if (issetb("series") || issetb("volume")) {
      echo ", volume " . getb("volume") . " of ";
      echo "<i>" . getb("series", "") . "</i>";
    }
    echo getb("chapter", "", ", chapter %s");
    echo getb("pages", "", ", pages %s");
    echo ".";
    echo getb("editor", "", " Editors: %s,\n");
    if (!$compact) echo "<br/>";
    echo "\n";
    echo getb("publisher", "Unknown publisher");
    echo getb("address", "", ", %s");
    echo getb("year", "", ", <b>%s</b>");
    echo ". \n";
    break;

  case "conference":
  case "inproceedings":
    echo "<i>" . getb("booktitle", "unknown booktitle") . "</i>\n";
    if (issetb("series") || issetb("volume")) {
      echo ", volume " . getb("volume") . " of ";
      echo "<i>" . getb("series", "") . "</i>";
    }
    echo getb("chapter", "", ", chapter %s");
    echo getb("pages", "", ", pages %s, ");
    echo getb("editor", "", "Editors: %s,\n");
    echo getb("address", "", ", %s");
    echo getb("year", "", ", <b>%s</b>");
    echo ".";
    if (!$compact) echo "<br/>";
    echo "\n";
    echo getb("publisher", "Unknown publisher");
    echo getb("pubaddress", "", ", %s");
    echo ". \n";
    break;

  case "mastersthesis":
    echo getb("type", "<i>Master's thesis</i>");
    echo getb("school", "", ", %s");
    echo getb("address", "", ", %s");
    echo getb("month", "", ", %s");
    echo getb("year", "", ", <b>%s</b>"); 
    echo ". \n";
    break;

  case "bookreview":
    echo "Book review: ";
    echo getb("title", "", ", %s");
    echo "<i>" . getb("journal", "Unknown journal") . "</i>, \n";
    echo getb("volume");
    echo getb("number", "", "(%s)");
    echo getb("pages", "", ", (%s)");
    echo getb("year", "", ", <b>%s</b>"); 
    echo ". \n";
    break;

  case "phdthesis":
    echo getb("type", "<i>PhD thesis</i>");
    echo getb("school", "", ", %s");
    echo getb("address", "", ", %s");
    echo getb("month", "", ", %s");
    echo getb("year", "", ", <b>%s</b>"); 
    echo ". \n";
    break;

  case "habilitation":
    echo "<i>Habilitationsschrift</i> :";
    echo getb("month", "", ", %s");
    echo getb("year", "", ", <b>%s</b>");
    echo ". \n";
    break;

  case "techreport":
    echo getb("type", "Technical report", "%s");
    echo getb("institution", "", ", %s");
    echo getb("pages", "", ", pages %s");
    echo getb("editor", "", ", %s, editors");
    echo getb("publisher", "", ", %s");
    echo getb("address", "", ", %s");
    echo getb("year", "", ", <b>%s</b>"); 
    echo ". \n";
    break;

  case "manual":
    echo getb("publisher", "unknown publisher", "%s");
    echo getb("address", "", ", %s");
    echo getb("year", "", ", <b>%s</b>");
    echo ". \n";
    break;

  case "unpublished":
  case "misc":
  default:
    echo getb("year", "", "<b>%s</b>.\n");
    break;
  }

#####
# Notes
  if (issetb("note")) {
    if (! $compact) echo "<br/>\n";
    echo getb("note");
  }

#####
# Links
  if (!$compact) echo "<br/>\n";

  echo "<a href=\"" . $bib['entrylinkbase'] . "/$entry.bib\">[BibTeX]</a>\n";

  if (!$abstract && issetb("abstract"))
    echo "<a href=\"" . $bib['entrylinkbase'] . "/$entry-abs.html\">[Abstract]</a>\n";   

  if ($pdflink) {
    $filedir = $bib['pdfdir'] . "/" . getb("pdfdir");
    $filelinkbase = $bib['pdflinkbase'] . "/" . getb("pdfdir");

    $pdffile = "$filedir/$entry.pdf";
    if (file_exists($pdffile)) {
      echo "<a href=\"$filelinkbase/$entry.pdf\">[PDF]</a>";
      echo " (" . hfilesize($pdffile) . ")\n";
    }
    
    $psfile = "$filedir/$entry.ps";
    if (file_exists($psfile)) {
      echo "<a href=\"$filelinkbase/$entry.ps\">[PS]</a>";
      echo " (" . hfilesize($psfile) .")\n";
    }

    $psgzfile = "$filedir/$entry.ps.gz";
    if (file_exists($psgzfile)) {
      echo "<a href=\"$filelinkbase/$entry.ps.gz\">[PS.GZ]</a>";
      echo " (" . hfilesize($psgzfile) .")\n";
    }
  }
  
  echo getb("e-print", "", 
	    "<a href=\"" . $bib['eprintbase'] . "/%s\">[Preprint]</a>\n");
  echo getb("doi", "",
	    "<a href=\"" . $bib['doibase'] . "/%s\">[DOI]</a>\n");
  echo getb("url", "", "<a href=\"%s\">[URL]</a>\n");
	
    
#####
# Abstract
  if ($abstract && issetb("abstract")) {
    echo "<div style=\"margin:0pt 1em 1em 1em;font-size:75%\">\n";
    echo getb("abstract");
    echo "</div>\n";
  }

#####
# Timestamp
  if ($meta && 
      (issetb('timestamp') || issetb('owner'))) {
    echo "<div style=\"margin-left:1em;font-size:90%;\">";
    echo "(entered";
    echo getb("timestamp", "", " on %s");
    echo getb("owner", "", " by %s");
    echo ")</div>\n";
  }

  echo "</li>\n";
}  

# parse the query string
function parse_query($querystring) {
  $s = trim($querystring);
  $error = 0;
  do {
    // match the key
    if (!preg_match('/^\s*(\w+)/', $s, $match)) 
      return "Cannot parse key in \"$s\"!";
    $key = $match[1];
    
    // advance the string
    $offset=strlen($match[0]);
    $s = substr($s, $offset);
    
    // match the op
    if (preg_match('/^\s+contains\s+/', $s, $match)
	|| preg_match('/^\s*=/', $s, $match))
      $op = "contains";
    elseif (preg_match('/^\s+greater\s+/', $s, $match) ||
	    preg_match('/^\s*\>/', $s, $match))
      $op = "greater";
    elseif (preg_match('/^\s+less\s+/', $s, $match) ||
	    preg_match('/^\s*\</', $s, $match))
      $op = "less";
    else return "Cannot parse operator in \"$s\"!";
    
    // advance the string
    $offset=strlen($match[0]);
    $s = substr($s, $offset);
    
    if (!preg_match('/^\s*\"([^\"]*)\"/', $s, $match) &&
	!preg_match('/^\s*(\S+)/', $s, $match))
      return "Cannot parse searchvalue in \"$s\"";

    $searchvalue = $match[1];
    
    // advance the string
    $offset=strlen($match[0]);
    $s = substr($s, $offset);
    
    $query[] = array($key, $op, $searchvalue);
    
    $and = preg_match('/^\s*and\s*/', $s, $match);
    if ($and) {
      // advance the string
      $offset=strlen($match[0]);
      $s = substr($s, $offset);
    }
  } while ($and);
  
  if (!preg_match('/^\s*$/', $s)) {
    return "Cannot parse continuing string \"$s\"!";
  }

  return $query;
}

function bib_search($querystring) {
  global $bib;

  $query = parse_query($querystring);
  if (!is_array($query)) return $query;

  bib_readdata();

  $selection = array_keys($bib['data']);
  foreach ($query as $phrase) {
    $newselection = array();
    $key=$phrase[0];
    $op=$phrase[1];
    $searchvalue=$phrase[2];
    switch ($op) {
    case "contains":
      foreach ($selection as $entry) {
	  $value = $bib['data'][$entry][$key];
	  if (mb_strpos(mb_strtolower($value), mb_strtolower($searchvalue)) !== FALSE)
	    $newselection[] = $entry;
      }
      break;
    case "greater":
      foreach ($selection as $entry) {
	  $value = $bib['data'][$entry][$key];
	  if ($value > $searchvalue)
	    $newselection[] = $entry;
      }
      break;
    case "less":
      foreach ($selection as $entry) {
	  $value = $bib['data'][$entry][$key];
	  if ($value < $searchvalue)
	    $newselection[] = $entry;
      }
      break;
    }
    $selection = $newselection;
  }

  return $selection;
}

# MOD: cache all authors with pubs in array
function bib_cache_author($author,$year,$entry) {
  global $bib_authors;
  
  $author=str_replace(",","",$author);
  $author=str_replace(".","",$author);
  $author=str_replace("and","",$author);
  $author=strtolower($author);
  $exxx=explode(" ",$author);
  for($i=0;$i<count($exxx);$i++){
  $bib_authors[$exxx[$i]]["y".$year][count($bib_authors[$exxx[$i]]["y".$year])+1]=$entry;
  }
}

# MOD: write author arrays in author-files
function bib_write_authors($argv){
  global $bib_authors;

  if($argv=="yes") {
  reset($bib_authors);
  for($i=0;$i<count($bib_authors);$i++){
  $auth=key($bib_authors);
  $fh = fopen("./extensions/local/bib_cache/".$auth.".dat", 'w+');
  fwrite($fh, implodeMDA($bib_authors[$auth],";"));
  fclose($fh);  
  next($bib_authors);
  }  
 }  

}
?>
