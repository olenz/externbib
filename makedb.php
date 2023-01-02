<?php
/**
 * Parse BibTeX objects and write them to a SQLite database.
 *
 * @package    ExternBib
 * @author     Olaf Lenz
 * @author     Michael Kuron
 * @author     Jean-Noël Grad
 * @copyright  2011-2014,2016,2022 The Authors
 * @license    https://opensource.org/licenses/BSD-3-Clause New BSD License
 * @link       https://github.com/olenz/externbib
 */

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'PEAR.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'tex2html.php');
require_once("bibdb.php");

/**
 * Parse a BibTeX file and populate its content in the default table of a
 * SQLite database. LaTeX math, macros and escape sequences are converted
 * to HTML on a "best effort" basis.
 *
 * @param SQLite3            $db         The SQLite database.
 * @param mixed              $bibfile    The BibTeX file path.
 * @param Structures_BibTex  $bibparser  The BibTeX parser.
 * @param int                $modes      LaTeX-to-HTML conversions to apply.
 * @return void
 */
function populate_db($db, $bibfile, $bibparser, $modes) {
  echo "Loading $bibfile...\n";
  $ret = $bibparser->loadFile($bibfile);
  if (PEAR::isError($ret)) {
    die($ret->getMessage());
  }

  echo "Parsing $bibfile...\n";
  $bibparser->parse();
  if ($bibparser->hasWarning()) {
    foreach ($bibparser->warnings as $warning) {
      error_log("WARNING: " . $warning['warning'] . " (line \"" . $warning['entry'] . "\")");
    }
  }

  echo "Found " . $bibparser->amount() . " entries.\n";

  foreach ($bibparser->data as $entry) {
    // create associative array
    $key  = $entry['cite'];

    // UTF8-ify and trim entry
    $cleanentry = array();
    foreach ($entry as $k=>$v) {
      $v = trim($v);
      if ($modes != 0 && $v != "" && $k != "fullEntry") {
        $v = convert_latex_string($v, $modes);
      }
      $cleanentry[$k] = $v;
    }
    $value = serialize($cleanentry);
    bibdb_insert($key, $value, $db);
  }
}

?>
