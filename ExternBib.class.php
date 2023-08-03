<?php
/**
 * Definition of the main class that opens cursors to SQLite databases,
 * handles user queries and formats search results as HTML.
 *
 * @package    ExternBib
 * @author     Olaf Lenz
 * @author     David Schwoerer
 * @author     Michael Kuron
 * @author     Jean-Noël Grad
 * @copyright  2011-2013,2016,2021-2022 The Authors
 * @license    https://opensource.org/licenses/BSD-3-Clause New BSD License
 * @link       https://github.com/olenz/externbib
 */
if (!defined('MEDIAWIKI')) die();
require_once("bibdb.php");
require_once("BibFormatter.class.php");


class ExternBib {
  // the database
  var $dbs = array();

  // the BibTeX forammter
  var $bibformatter = null;

  // parameters
  var $filedirs;
  var $filebaseurls;
  var $default_format;

  function __construct($dbfiles, 
                       $filedirs,
                       $filebaseurls,
                       $doibaseurl,
                       $eprintbaseurl,
                       $default_format) {
    if (!is_file(reset($dbfiles)))
      error_log("ERROR: $dbfiles[0] does not exist!");
    
    foreach ($dbfiles as $name => $dbfile){
      // bibdb_open returns a sqlite db object on success, a false else
      $db_local = bibdb_open($dbfile, 'rd');
      if (is_bool($db_local)) {
        error_log("ERROR: Could not open $dbfiles[$name]!");
      } else {
        $this->dbs[$name] = $db_local;
      }
    }
     
    if (is_array($filedirs))
      $this->filedirs = $filedirs;
    else
      $this->filedirs = array($filedirs);

    if (is_array($filebaseurls))
      $this->filebaseurls = $filebaseurls;
    else
      $this->filebaseurls = array($filebaseurls);

    $this->filebaseurls = array_map(
        function ($x) { return rtrim($x, '/'); },
        $this->filebaseurls);

    if (count($this->filedirs) != count($this->filebaseurls))
      error_log('ERROR: Number of elements in $wgExternBibFileDirs does not match number of elements in $wgExternBibFileURLs!');

    $this->default_format = $default_format;
    $this->bibformatter = new BibFormatter($doibaseurl, $eprintbaseurl);
  }

  //////////////////////////////////////////////////
  // Handle <bibentry>
  //////////////////////////////////////////////////
  // bibentry creates an unsorted list of all bib entries provided in
  // the tag
  // Example: <bibentry>lenz07b,holm98a</bibentry>
  function bibentry( $input, $argv, $parser, $frame ) {
    global $wgOut, $wgParser;

    // TODO: check whether this can be avoided
    // disable the cache
    $parser->mOutput->updateCacheExpiry( 0 );

    // parse $input and split it into entries
    $input = trim(trim($input), ',');
    $entries = preg_split("/[\s,]+/", $input);

    // start writing into the output buffer
    ob_start();
    
    $this->format_entries($entries, $argv);
  
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
    $parser->mOutput->updateCacheExpiry( 0 );
    
    // start writing into the output buffer
    ob_start();

    // search for entries, using the input as query
    $found_entries = $this->search_entries($input);

    if ($found_entries) {
      // Output the results
      $this->format_entries($found_entries, $argv);
    }
  
    // get everything from the output buffer
    $output = ob_get_contents();
    ob_end_clean();
    return $output;
  }


  //////////////////////////////////////////////////
  // helper functions
  //////////////////////////////////////////////////
  // returns whether $key exists in the array and whether it is true
  private function array_isset($key, $array, $defaults) {
    $value = false;
    if (array_key_exists($key, $array)) {
      $value = $array[$key];
    } elseif (array_key_exists($key, $defaults)) {
      $value = $defaults[$key];
    }
    return $value === true or $value === "true" or $value === "yes" or $value === 1;
  }

  function fullEntryLink($entry, $text) {
    $title = SpecialPage::getTitleFor("ExternBibShowEntry", "$entry");
    $linker = new Linker();
    $link = $linker->link($title, $text);
    return $link;
  }

  /** Format a file size in human-readable form. */
  function hfilesize($file){
    $size = filesize($file);
    $i = 0;
    $units = array("B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB");
    while ($size >= 1024) {
      $size /= 1024;
      $i++;
    }
    if ($size < 10.) {
      $size = sprintf("%.1f", round($size, 1));
    } else {
      $size = ceil($size);
    }
    return "$size ${units[$i]}";
  }

  // Format $entries
  // parameters: 
  //  - abstract: show the abstract
  //  - bibtex: show the bibtexentry 
  //  - filelink: show links to corresponding files (if available)
  //  - meta: show the timestamp, owner and the key
  //  - compact: insert line breaks or not
  function format_entries($entries, $argv=array()) {
    global $wgUser;
    
    $dbname = reset($this->dbs);
    if (count($entries) == 0)
      return;

    // for backwards compatibility
    if (array_key_exists("pdflink", $argv))
      $argv["filelink"] = $argv["pdflink"];

    // set defaults
    $make_compact = $this->array_isset("compact", $argv, $this->default_format);
    $show_abstract = $this->array_isset("abstract", $argv, $this->default_format);
    $show_filelink = $this->array_isset("filelink", $argv, $this->default_format);
    $show_metadata = $this->array_isset("meta", $argv, $this->default_format);
    $show_bibtex = $this->array_isset("bibtex", $argv, $this->default_format);
    $show_fullentrylink = $this->array_isset("fullentrylink", $argv, $this->default_format);

    $formatted_entries = [];

    foreach ($entries as $entry) {
      // fetch the entry

      // use db name if given
      if (is_array($entry) && array_key_exists("db", $entry)) {
        $data = bibdb_fetch(reset($entry), $this->dbs[$entry["db"]]);
        $dbname = $entry["db"];
        if (!$data) {
          $msg = wfMessage('externbib-entry-notfound', reset($entry));
          array_push($formatted_entries, '<li class="error">' . $msg . '</li>');
          continue;
        }
        $entry = reset($entry);
      } else {
        // else check in each database if entry exists
        for (reset($this->dbs); (current($this->dbs) !== false) && !isset($data); next($this->dbs)){
          $data = bibdb_fetch($entry, current($this->dbs));
        }
        $dbname = key($this->dbs);
        if (!$data) {
          $msg = wfMessage('externbib-entry-notfound', $entry);
          array_push($formatted_entries, '<li class="error">' . $msg . '</li>');
          continue;
        }
      }

      reset($this->dbs);

      // current entry is used by getb and issetb
      $current_entry = unserialize($data);
      unset($data);
     
      // check whether the entry is superseded by another one
      if (array_key_exists("superseded", $current_entry)) {
        if ($show_metadata) {
          $superseded = $current_entry["superseded"];
          $supersededLink = $this->fullEntryLink($superseded, wfMessage('externbib-fullentry'));
          $msg = wfMessage('externbib-entry-superseded',
                           $entry, $superseded, $supersededLink);
          array_push($formatted_entries, '<li class="warning">' . $msg . '</li>');
        }
        continue;
      }

      // link to files
      $filelinks = "";
      if ($show_filelink) {
        for ($i=0; $i < count($this->filedirs); $i++) {
          $dir = $this->filedirs[$i];
          $urlbase = $this->filebaseurls[$i];
          foreach (array("pdf", "ps", "ps.gz") as $ext) {
            $filepath = "$dir/$entry.$ext";
            if (file_exists($filepath)) {
              $filelinks .= "<a href=\"$urlbase/$entry.$ext\">[" . strtoupper($ext) . "]</a>";
              $filelinks .= " (" . $this->hfilesize($filepath) . ")\n";
            }
          }
        }
      }

      // link to full entry
      $fullentrylink = "";
      if ($show_fullentrylink) {
        $fullentrylink = $this->fullEntryLink(
            $entry, '[' . wfMessage('externbib-fullentry') . ']') . "\n";
      }

      // Timestamp
      $timestamp = "";
      $entry_name = "";
      if ($show_metadata) {
        $entry_name = "[$entry]<br/>\n";
        if (array_key_exists('timestamp', $current_entry) || array_key_exists('owner', $current_entry)) {
          $timestamp = "<div style=\"margin-left:1em;font-size:90%;\">";
          if (array_key_exists('timestamp', $current_entry) && array_key_exists('owner', $current_entry)) {
            $timestamp .= wfMessage(
                'externbib-enteredon', $current_entry["owner"], $current_entry["timestamp"], $dbname);
          } elseif (array_key_exists('timestamp', $current_entry)) {
            $timestamp .= wfMessage(
                'externbib-enteredon-noowner', $current_entry["timestamp"], $dbname);
          } else {
            $timestamp .= wfMessage(
                'externbib-enteredon-notimestamp', $current_entry["owner"], $dbname);
          }
          $timestamp .= "</div>\n";
        }
      }

      // main formatting
      $formatted_entry = $this->bibformatter->format(
          $current_entry, $make_compact, $show_abstract, $show_bibtex,
          $entry_name, $timestamp, $filelinks, $fullentrylink);

      array_push($formatted_entries, "<li>\n" . $formatted_entry . "\n</li>");
    } //end foreach

    echo "<ul class=\"plainlinks\">\n" . implode("\n", $formatted_entries) . "</ul>\n";
  } // end format_entries

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

  function search_entries($querystring, $databases = array()) {
    //search in all databases if none specified
    
    if (!is_array($databases))
       $databases = array($databases => $databases);
    elseif (count($databases) == 0)
       $databases = array_keys($this->dbs);
       
    $query = $this->parse_query($querystring);
    if (!is_array($query)) return $query;

    // fetch all entries of the given databases into data array
    if (!isset($this->data)) {
       foreach($databases as $database)
       {
          //$entry = dba_firstkey($this->dbs[$database]);
          $query_local = bibdb_query($this->dbs[$database]);
          while ($row = $query_local->fetchArray(SQLITE3_NUM)) {
              $entry = $row[0];
              $record = unserialize($row[1]);
              $this->data[$entry] = array_merge($record, array("db" => $database));
          }
       }
    }

    // rule to strip diacritic marks
    $to_ascii = Transliterator::createFromRules(
        ':: Any-Latin; :: Latin-ASCII; :: NFD; :: [:Nonspacing Mark:] Remove; :: NFC;',
        Transliterator::FORWARD);

    // now query the data
    $selection = array_keys($this->data);
    if (is_array($selection) && count($selection) > 0)
    {
       foreach ($query as $phrase) {
         $newselection = array();
         $key=$phrase[0];
         $op=$phrase[1];
         $searchvalue=$phrase[2];
      
         switch ($op) {
         case "contains":
	   foreach ($selection as $entry) {
	     if (array_key_exists($key, $this->data[$entry])) {
	       $value = $this->data[$entry][$key];
	       if (mb_strpos(mb_strtolower($value), mb_strtolower($searchvalue)) !== FALSE)
	         $newselection[] = $entry;
	       elseif (mb_strpos(mb_strtolower($to_ascii->transliterate($value)),
	                         mb_strtolower($to_ascii->transliterate($searchvalue))) !== FALSE)
	         $newselection[] = $entry;
	     } 
	   }
	   break;
         case "greater":
	   foreach ($selection as $entry) {
	     if (array_key_exists($key, $this->data[$entry])) {
	       $value = $this->data[$entry][$key];
	       if ($value > $searchvalue)
	         $newselection[] = $entry;
	     }
	   }
	   break;
         case "less":
	   foreach ($selection as $entry) {
	     if (array_key_exists($key, $this->data[$entry])) {
	       $value = $this->data[$entry][$key];
	       if ($value < $searchvalue)
	         $newselection[] = $entry;
	     }
	   }
	   break;
         }
         $selection = $newselection;
       }
    }
    foreach ($selection as $entry)
    {
       $ret_selection[] = array($entry, "db" => $this->data[$entry]["db"]);
    }
    return $selection;
  }
}
