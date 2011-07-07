<?php
require_once( "$IP/includes/SpecialPage.php" );

// Interface to mediawiki
$wgExtensionFunctions[] = 'wfSetupExternBib';
$wgExtensionCredits['parserhook'][] =  array(
	'name' => 'ExternBib',
	'version' => '2.0',
	'author' => 'Olaf Lenz, Christopher Wagner',
	'description' => 'Cite an external bibtex file.',
);

// setup the module
function wfSetupExternBib() {
  global $wgMessageCache, $wgParser, $wgExtExternBib;

  $wgExtExternBib = new ExtExternBib;

  // register the tags
  $wgParser->setHook("bibentry", "efBibEntry");
  $wgParser->setHook("bibsearch", "efBibSearch");
  
  // setup the special page
  $wgMessageCache->addMessage('bibsearch', 'Search in the bibtex database');
  SpecialPage::addPage(new SpecialPage('BibSearch', '', true, 'ExternBibSpecialBibSearch', '', true)); 

  return true;
}

function efBibEntry($input, $argv, $parser, $frame) {
  global $wgExtExternBib;
  return $wgExtExternBib->bibentry($input, $argv, $parser, $frame);
}

function efBibSearch($input, $argv, $parser, $frame) {
  global $wgExtExternBib;
  return $wgExtExternBib->bibsearch($input, $argv, $parser, $frame);
}

function ExternBibSpecialBibSearch($par) {
  global $wgExtExternBib;
  $wgExtExternBib->specialBibSearch($par);
}

class ExtExternBib {
  // The following are parameters
  var $dir = "/home/icp/public_html/bib";
  var $bibfile = "/home/icp/public_html/bib/icpdb.dat";
  var $pdfdir = "/home/icp/public_html/bib/pdf";
  var $entrylinkbase = "/~icp/bib/entries";
  var $pdflinkbase = "/~icp/bib/pdf";
  var $doibase = "http://dx.doi.org";
  var $eprintbase = "http://arxiv.org/abs";

  // the actual data
  var $data;
  // the currently handled entry
  var $current_entry;

  function ExtExternBib() {
    $this->dir = "/home/icp/public_html/bib";
    $this->bibfile = $this->dir . "/icpdb.dat";
    $this->pdfdir = $this->dir . "/pdf";
    $this->entrylinkbase = "/~icp/bib/entries";
    $this->pdflinkbase = "/~icp/bib/pdf";
    $this->doibase = "http://dx.doi.org";
    $this->eprintbase = "http://arxiv.org/abs";
  }

  //////////////////////////////////////////////////
  // Handle <bibentry>
  //////////////////////////////////////////////////
  // bibentry creates an unsorted list of all bib entries provided in the tags
  // Example: <bibentry>lenz07b,holm98a</bibentry>
  function bibentry( $input, $argv, $parser, $frame ) {

    // TODO: check whether this can be avoided
    // disable the cache
    $parser->disableCache();

    // read the data
    $this->readdata();
    // parse $input and split it into entries
    $input=trim($input);
    $entries = preg_split("/[\s,]+/", $input);

    // start writing into the output buffer
    ob_start();

    echo "<ul class=\"plainlinks\">\n";
    foreach ($entries as $entry) {
      $this->format_entry($entry, $argv);
    }
    echo "</ul>\n";
  
    // get everything from the output buffer
    $output = ob_get_contents();
    ob_end_clean();
    return $output;
  }

  //////////////////////////////////////////////////
  // Handle <bibsearch>
  //////////////////////////////////////////////////
  // bibsearch creates an unsorted list of all bib entries provided by the query between the tags
  // Example: <bibsearch>author=holm</bibsearch>
  function bibsearch( $input, $argv, $parser, $frame ) {
    // TODO: check whether this can be avoided
    // disable the cache
    $parser->disableCache();
    
    // read the data
    $this->readdata();
    
    // start writing into the output buffer
    ob_start();

    // search for entries, using the input as query
    $found_entries = $this->search_entries($input);

    if ($found_entries) {
      // Output the results
      echo "<ul class=\"plainlinks\">\n";
      foreach ($found_entries as $entry) {
	$this->format_entry($entry, $argv);
      }
      echo "</ul>\n";
    }
  
    // get everything from the output buffer
    $output = ob_get_contents();
    ob_end_clean();
    return $output;
  }

  function specialBibsearch($par) {
    global $wgOut, $wgRequest, $wgUser;

    $wgOut->setPageTitle( 'Search the bibtex database' );
    
    // # if the page is called via ~simbio/Special:BibSearch/PAR?bla=blub
    //     #   $par contains "PAR"
    // # In this function, the following can be used:
    //     # $wgUser->isLoggedIn() 
    //     #   - test whether the user is logged in
    //     # $wgRequest->getVal( 'searchbib' )
    //     #   - get the value of the parameter searchbib
    //     # $wgRequest->getText( 'searchbib' )
    //     #   - get a text from the parameter searchbib
    //     # $wgOut->addHTML($out);
    //     #   - output $out in the page
    
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
	   $found_entries = $this->search_entries($query);
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
	     foreach ($found_entries as $entry) {
	       $this->format_entry($entry, $format_options);
	     }
	     echo "</ul>\n";
	   }
	 }

    $output = ob_get_contents();
    ob_end_clean();
    $wgOut->addHTML($output);
  }

  //////////////////////////////////////////////////
  // helper functions
  //////////////////////////////////////////////////
  // Read the data from the datafile
  // requires the global variable $bibdatafile to be set
  function readdata() {
    // only read the data if it was not already read
    if (!isset($this->data)) {
      $fh = fopen($this->bibfile, "r");
      $this->data = 
	unserialize(fread($fh,filesize($this->bibfile)));
       
      fclose($fh);
    }
  }
   
  // returns whether the field $key for the current entry is set
  function issetb($key) {
    return isset($this->current_entry[$key]);
  }
   
  // if $key is set in the current entry, 
  // return the entry (formatted with $format)
  // otherwise return the default
  function getb($key, $default = "", $format="%s") {
    if ($this->issetb($key)) 
      return sprintf($format, $this->current_entry[$key]);
    else return $default;
  }

  // returns the human readable filesize
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

  // Format $entry
  // parameters: 
  //  - abstract: show the abstract inline
  //  - pdflink: show links to the pdf files (if available)
  //  - meta: show the timestamp, owner and the key
  //  - compact: insert line breaks or not
  function format_entry($entry, $argv=array()) {
    // current entry is required by getb and issetb
    $this->current_entry = $this->data[$entry];
     
    // set defaults
    $compact="no";
    $abstract="no";
    $pdflink="no";
    $meta="no";
     
    // parse options
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
    
    ////#
    // check whether the entry is superseded by another one
    if ($this->issetb("superseded")) {
      if ($meta) {
	$superseded = $this->getb("superseded");
	echo "<li>Entry $entry\n";
	echo "<a href=\"" . $this->entrylinkbase . "/$entry.bib\">[BibTeX]</a>\n";
	echo " is superseded by entry $superseded ";
	echo "<a href=\"" . $this->entrylinkbase . "/$superseded.bib\">[BibTeX]</a>\n";
	echo ".</li>\n";
      }
      return;
    } 

    echo "<li>\n";
  
    ////#
    // main formatting

    if ($meta) echo "[$entry]<br/>\n";
    
    echo "<b>". $this->getb("author", "Unknown author") . ". </b>";
    
    if (!$compact) echo "<br/>";
    echo "\n";
  
    echo $this->getb("title", "Unknown title.", "\"%s\".");
    if (!$compact) echo "<br/>";
    echo "\n";

    switch ($this->getb("entryType")) {
    case "article":
      echo "<i>" . $this->getb("journal", "Unknown journal") . "</i>\n";
      echo $this->getb("volume");
      echo $this->getb("number", "", "(%s)");
      echo $this->getb("pages", "", "(%s)");
      echo $this->getb("year", "", ", <b>%s</b>");
      echo ".\n";
      break;

    case "book":
      echo $this->getb("series", "", "<i>%s</i>, ");
      echo $this->getb("publisher", "Unknown publisher", "%s");
      echo $this->getb("editor", "Unknown editor", ", %s");
      echo $this->getb("address", "", ", %s");
      echo $this->getb("year", "", ", <b>%s</b>"); 
      echo ". \n";
      break;

    case "inbook":
    case "incollection":
      echo "In ";
      echo "<i>" . $this->getb("booktitle", "unknown booktitle") . "</i>";
      if ($this->issetb("series") || $this->issetb("volume")) {
	echo ", volume " . $this->getb("volume") . " of ";
	echo "<i>" . $this->getb("series", "") . "</i>";
      }
      echo $this->getb("chapter", "", ", chapter %s");
      echo $this->getb("pages", "", ", pages %s");
      echo ".";
      echo $this->getb("editor", "", " Editors: %s,\n");
      if (!$compact) echo "<br/>";
      echo "\n";
      echo $this->getb("publisher", "Unknown publisher");
      echo $this->getb("address", "", ", %s");
      echo $this->getb("year", "", ", <b>%s</b>");
      echo ". \n";
      break;

    case "conference":
    case "inproceedings":
      echo "<i>" . $this->getb("booktitle", "unknown booktitle") . "</i>\n";
      if ($this->issetb("series") || $this->issetb("volume")) {
	echo ", volume " . $this->getb("volume") . " of ";
	echo "<i>" . $this->getb("series", "") . "</i>";
      }
      echo $this->getb("chapter", "", ", chapter %s");
      echo $this->getb("pages", "", ", pages %s, ");
      echo $this->getb("editor", "", "Editors: %s,\n");
      echo $this->getb("address", "", ", %s");
      echo $this->getb("year", "", ", <b>%s</b>");
      echo ".";
      if (!$compact) echo "<br/>";
      echo "\n";
      echo $this->getb("publisher", "Unknown publisher");
      echo $this->getb("pubaddress", "", ", %s");
      echo ". \n";
      break;

    case "mastersthesis":
      echo $this->getb("type", "<i>Master's thesis</i>");
      echo $this->getb("school", "", ", %s");
      echo $this->getb("address", "", ", %s");
      echo $this->getb("month", "", ", %s");
      echo $this->getb("year", "", ", <b>%s</b>"); 
      echo ". \n";
      break;

    case "bookreview":
      echo "Book review: ";
      echo $this->getb("title", "", ", %s");
      echo "<i>" . $this->getb("journal", "Unknown journal") . "</i>, \n";
      echo $this->getb("volume");
      echo $this->getb("number", "", "(%s)");
      echo $this->getb("pages", "", ", (%s)");
      echo $this->getb("year", "", ", <b>%s</b>"); 
      echo ". \n";
      break;

    case "phdthesis":
      echo $this->getb("type", "<i>PhD thesis</i>");
      echo $this->getb("school", "", ", %s");
      echo $this->getb("address", "", ", %s");
      echo $this->getb("month", "", ", %s");
      echo $this->getb("year", "", ", <b>%s</b>"); 
      echo ". \n";
      break;
      
    case "habilitation":
      echo "<i>Habilitationsschrift</i> :";
      echo $this->getb("month", "", ", %s");
      echo $this->getb("year", "", ", <b>%s</b>");
      echo ". \n";
      break;

    case "techreport":
      echo $this->getb("type", "Technical report", "%s");
      echo $this->getb("institution", "", ", %s");
      echo $this->getb("pages", "", ", pages %s");
      echo $this->getb("editor", "", ", %s, editors");
      echo $this->getb("publisher", "", ", %s");
      echo $this->getb("address", "", ", %s");
      echo $this->getb("year", "", ", <b>%s</b>"); 
      echo ". \n";
      break;

    case "manual":
      echo $this->getb("publisher", "unknown publisher", "%s");
      echo $this->getb("address", "", ", %s");
      echo $this->getb("year", "", ", <b>%s</b>");
      echo ". \n";
      break;

    case "unpublished":
    case "misc":
    default:
      echo $this->getb("year", "", "<b>%s</b>.\n");
      break;
    }

    ////#
    // Notes
    if ($this->issetb("note")) {
      if (! $compact) echo "<br/>\n";
      echo $this->getb("note");
    }

    ////#
    // Links
    if (!$compact) echo "<br/>\n";

    echo "<a href=\"" . $this->entrylinkbase . "/$entry.bib\">[BibTeX]</a>\n";

    if (!$abstract && $this->issetb("abstract"))
      echo "<a href=\"" . $this->entrylinkbase . "/$entry-abs.html\">[Abstract]</a>\n";   

    if ($pdflink) {
      $filedir = $this->pdfdir . "/" . $this->getb("pdfdir");
      $filelinkbase = $this->pdflinkbase . "/" . $this->getb("pdfdir");

      $pdffile = "$filedir/$entry.pdf";
      if (file_exists($pdffile)) {
	echo "<a href=\"$filelinkbase/$entry.pdf\">[PDF]</a>";
	echo " (" . $this->hfilesize($pdffile) . ")\n";
      }
    
      $psfile = "$filedir/$entry.ps";
      if (file_exists($psfile)) {
	echo "<a href=\"$filelinkbase/$entry.ps\">[PS]</a>";
	echo " (" . $this->hfilesize($psfile) .")\n";
      }

      $psgzfile = "$filedir/$entry.ps.gz";
      if (file_exists($psgzfile)) {
	echo "<a href=\"$filelinkbase/$entry.ps.gz\">[PS.GZ]</a>";
	echo " (" . $this->hfilesize($psgzfile) .")\n";
      }
    }
  
    echo $this->getb("e-print", "", 
		     "<a href=\"" . $this->eprintbase . "/%s\">[Preprint]</a>\n");
    echo $this->getb("doi", "",
		     "<a href=\"" . $this->doibase . "/%s\">[DOI]</a>\n");
    echo $this->getb("url", "", "<a href=\"%s\">[URL]</a>\n");
	
    
    ////#
    // Abstract
    if ($abstract && $this->issetb("abstract")) {
      echo "<div style=\"margin:0pt 1em 1em 1em;font-size:75%\">\n";
      echo $this->getb("abstract");
      echo "</div>\n";
    }
    
    ////#
    // Timestamp
    if ($meta && 
	($this->issetb('timestamp') || $this->issetb('owner'))) {
      echo "<div style=\"margin-left:1em;font-size:90%;\">";
      echo "(entered";
      echo $this->getb("timestamp", "", " on %s");
      echo $this->getb("owner", "", " by %s");
      echo ")</div>\n";
    }

    echo "</li>\n";
  } // end format_entry  

  // parse the query string
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

  function search_entries($querystring) {
    
    $query = $this->parse_query($querystring);
    if (!is_array($query)) return $query;

    $this->readdata();

    $selection = array_keys($this->data);
    foreach ($query as $phrase) {
      $newselection = array();
      $key=$phrase[0];
      $op=$phrase[1];
      $searchvalue=$phrase[2];
      switch ($op) {
      case "contains":
	foreach ($selection as $entry) {
	  $value = $this->data[$entry][$key];
	  if (mb_strpos(mb_strtolower($value), mb_strtolower($searchvalue)) !== FALSE)
	    $newselection[] = $entry;
	}
	break;
      case "greater":
	foreach ($selection as $entry) {
	  $value = $this->data[$entry][$key];
	  if ($value > $searchvalue)
	    $newselection[] = $entry;
	}
	break;
      case "less":
	foreach ($selection as $entry) {
	  $value = $this->data[$entry][$key];
	  if ($value < $searchvalue)
	    $newselection[] = $entry;
	}
	break;
      }
      $selection = $newselection;
    }

    return $selection;
  }
}

?>
