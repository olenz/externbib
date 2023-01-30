<?php

/**
 * Convert BibTeX record to HTML using the BibLaTeX specification.
 *
 * @package    ExternBib
 * @author     Olaf Lenz
 * @author     Jean-Noël Grad
 * @copyright  2011-2013,2022 The Authors
 * @license    https://opensource.org/licenses/BSD-3-Clause New BSD License
 * @link       https://github.com/olenz/externbib
 */
class BibFormatter {
  // the currently handled entry
  var $current_entry;

  // parameters
  var $doibaseurl;
  var $eprintbaseurl;

  function __construct($doibaseurl, $eprintbaseurl) {
    $this->doibaseurl = $doibaseurl;
    $this->eprintbaseurl = $eprintbaseurl;
  }

  // returns whether the field $key for the current entry is set
  private function issetb($key) {
    return array_key_exists($key, $this->current_entry);
  }

  // if $key is set in the current entry,
  // return the entry (formatted with $format)
  // otherwise return the default
  private function getb($key, $default = "", $format="%s") {
    if ($this->issetb($key))
    return sprintf($format, $this->current_entry[$key]);
    else return $default;
  }

  private function format_current_entry_title($style, $prefix="") {
    $string = "<${style}>";
    $string .= $this->getb("${prefix}title", "Unknown ${prefix}title");
    if (preg_match('/[,;:\\.\\!\\?]$/', $string) === 1) {
      $string .= $this->getb("${prefix}subtitle", "", " %s");
    } else {
      $string .= $this->getb("${prefix}subtitle", "", ": %s");
    }
    if ($this->issetb("${prefix}titleaddon") or $prefix === "") {
      if (preg_match('/[\\.\\!\\?]$/', $string) === 0) {
        $string .= ".";
      }
    }
    $string .= "</${style}>";
    if ($this->issetb("${prefix}titleaddon")) {
      $string .= $this->getb("${prefix}titleaddon", "", " %s");
      if ($prefix === "") {
        if (preg_match('/[\\.\\!\\?]$/', $string) === 0) {
          $string .= ".";
        }
      }
    }
    return $string;
  }

  private function format_current_entry_book_volume() {
    $array = [];
    $string = "";
    $string .= $this->getb("chapter", "", ", chapter %s");
    $string .= $this->getb("pages", "", ", pages %s");
    if ($this->issetb("venue")) {
      $string .= " (";
      $string .= $this->format_current_entry_venue();
      $string .= ")";
    }
    if (strlen($string) != 0 or ($this->getb("entryType") !== "book" and $this->getb("entryType") !== "proceedings")) {
      array_push($array, $string);
    }

    if ($this->issetb("editor")) {
      $string = "Edited by " . $this->format_current_entry_authors("editor");
      array_push($array, $string);
    }

    $string = "";
    $string .= $this->getb("series", "", "part of <i>%s</i>, ");
    $string .= $this->getb("volume", "", "volume %s, ");
    if (strlen($string) >= 2) {
      $string = substr($string, 0, -2);
      $string = ucfirst($string);
      array_push($array, $string);
    }

    $string = $this->getb("edition", "", "Edition %s");
    if (strlen($string) != 0) {
      array_push($array, $string);
    }

    if (count($array) == 1 and $array[0] == "") {
      return "";
    }
    return implode(". ", $array);
  }

  private function format_current_entry_authors($key="author") {
    if (!$this->issetb($key)) {
      return "Unknown author";
    }
    $authors = preg_split('/\\s+and\\s+/', $this->getb($key));
    foreach ($authors as &$author) {
      if (preg_match('/,/', $author) === 1) {
        $components = preg_split('/,/', $author, 3);
        if (count($components) == 2) {
          $author = $components[1] . " " . $components[0];
        } elseif (count($components) == 3) {
          $author = $components[2] . " " . $components[0] . " " . $components[1];
        }
      }
    }
    unset($author);
    return implode(", ", $authors);
  }

  private function format_current_entry_venue() {
    $array = [];
    if ($this->issetb("eventtitle")) {
      array_push($array, $this->getb("eventtitle"));
    }
    if ($this->issetb("venue")) {
      array_push($array, $this->getb("venue"));
    }
    if ($this->issetb("eventdate")) {
      if (version_compare(phpversion(), '5.2.0', '>=')) {
        $eventdates = preg_split('/\\//', $this->getb("eventdate"), 2);
        $eventdatevalid = true;
        foreach ($eventdates as $eventdate) {
          if (date_create($eventdate) === false) {
            $eventdatevalid = false;
            break;
          }
        }
        if ($eventdatevalid) {
          foreach ($eventdates as &$eventdate) {
            $eventdate = date_create($eventdate);
          }
          unset($eventdate);
          if (count($eventdates) == 1) {
            $eventdates[0] = date_format($eventdates[0], 'M d Y');
          } else {
            if (strcmp(date_format($eventdates[0], 'Y'), date_format($eventdates[1], 'Y')) === 0) {
              if (strcmp(date_format($eventdates[0], 'm'), date_format($eventdates[1], 'm')) === 0) {
                $eventdates[0] = date_format($eventdates[0], 'M d');
                $eventdates[1] = date_format($eventdates[1], 'd Y');
              } else {
                $eventdates[0] = date_format($eventdates[0], 'M d');
                $eventdates[1] = date_format($eventdates[1], 'M d Y');
              }
            } else {
              $eventdates[0] = date_format($eventdates[0], 'M d Y');
              $eventdates[1] = date_format($eventdates[1], 'M d Y');
            }
          }
        }
        array_push($array, implode("&ndash;", $eventdates));
      } else {
        array_push($array, $this->getb("eventdate"));
      }
    }
    return implode(", ", $array);
  }

  /**
   * Main formatter.
   *
   * @param  array   $current_entry   BibTeX data structure.
   * @param  bool    $make_compact    Don't insert line breaks if true.
   * @param  bool    $show_abstract   Insert abstract if true.
   * @param  bool    $show_bibtex     Insert the raw BibTeX if true.
   * @param  string  $preamble        Preamble to the bibliographic note (HTML).
   * @param  string  $timestamp       BibTeX timestamp and owner (HTML).
   * @param  string  $filelinks       Links to files (HTML).
   * @param  string  $fullentrylink   Link to full entry (HTML).
   * @return string  HTML representation of the record.
   */
  function format($current_entry, $make_compact, $show_abstract, $show_bibtex,
                  $preamble, $timestamp, $filelinks, $fullentrylink) {

    $this->current_entry = $current_entry;
    $formatted_entry = $preamble;

    if ($this->getb("author") or (
        $this->getb("entryType") != "book" and
        $this->getb("entryType") != "manual" and
        $this->getb("entryType") != "proceedings") ) {
      $formatted_entry .= $this->format_current_entry_authors();
      $formatted_entry .= ".";
      if (!$make_compact)
        $formatted_entry .= "<br/>";
      $formatted_entry .= "\n";
    }

    $formatted_entry .= $this->format_current_entry_title("b");
    if (!$make_compact)
      $formatted_entry .= "<br/>";
    $formatted_entry .= "\n";

    switch ($this->getb("entryType")) {
    case "article":
      $formatted_entry .= $this->getb("journal", "Unknown journal", "<i>%s</i>");
      $formatted_entry .= " ";
      $formatted_entry .= $this->getb("volume");
      if ($this->issetb("number")) {
        // journal issue is stored in field "number" if it's an integer (or a
        // string, but this implementation detail wasn't documented until 2018)
        $formatted_entry .= $this->getb("number", "", "(%s)");
      } elseif ($this->issetb("issue")) {
        // journal issue is ofter stored in field "issue" by accident
        // https://github.com/retorquere/zotero-better-bibtex/issues/925
        $number_pat = '/^ *(?:S|Suppl\\. +|Suppl[a-zé]+ +)?([IVXLCDM]+|[0-9]+)(?=[a-z\\/\\.\\-]?| *$)/';
        if (preg_match($number_pat, $this->getb("issue")) === 1) {
          $formatted_entry .= $this->getb("issue", "", "(%s)");
        }
      }
      $formatted_entry .= $this->getb("pages", "", ":%s");
      $formatted_entry .= $this->getb("year", "", ", <b>%s</b>");
      $formatted_entry .= ".";
      break;

    case "inbook":
    case "incollection":
    case "inproceedings":
    case "conference":
      $formatted_entry .= "In ";
      $formatted_entry .= $this->format_current_entry_title("i", "book");
      // fallthrough

    case "book":
    case "proceedings":
      $formatted_vol_info = $this->format_current_entry_book_volume();
      if ($formatted_vol_info != "" or !($this->getb("entryType") === "book" or $this->getb("entryType") === "proceedings")) {
        $formatted_entry .= $formatted_vol_info;
        $formatted_entry .= ".";
        if (!$make_compact)
          $formatted_entry .= "<br/>";
        $formatted_entry .= "\n";
      }
      if ($this->issetb("publisher")) {
        $formatted_entry .= $this->getb("publisher");
        $formatted_entry .= $this->getb("address", "", ", %s");
        $formatted_entry .= ", ";
      } elseif ($this->getb("entryType") !== "inproceedings" and $this->getb("entryType") !== "conference") {
        $formatted_entry .= "Unknown publisher, ";
      }
      $formatted_entry .= $this->getb("year", "unknown year", "<b>%s</b>");
      $formatted_entry .= $this->getb("isbn", "", ". ISBN: %s");
      $formatted_entry .= ".";
      break;

    case "mastersthesis":
      // can be a Bachelor's thesis
      $formatted_entry .= $this->getb("type", "<i>Master's thesis</i>");
      $formatted_entry .= $this->getb("school", "", ", %s");
      $formatted_entry .= $this->getb("address", "", ", %s");
      $formatted_entry .= $this->getb("month", "", ", %s");
      $formatted_entry .= $this->getb("year", "<b>unknown year</b>", ", <b>%s</b>");
      $formatted_entry .= ".";
      break;

    case "phdthesis":
      // can be a degree equivalent to a PhD thesis
      $formatted_entry .= $this->getb("type", "<i>PhD thesis</i>");
      $formatted_entry .= $this->getb("school", "", ", %s");
      $formatted_entry .= $this->getb("address", "", ", %s");
      $formatted_entry .= $this->getb("month", "", ", %s");
      $formatted_entry .= $this->getb("year", "<b>unknown year</b>", ", <b>%s</b>");
      $formatted_entry .= ".";
      break;

    case "techreport":
      $formatted_entry .= $this->getb("type", "Technical report", "%s");
      $formatted_entry .= $this->getb("institution", "", ", %s");
      $formatted_entry .= $this->getb("pages", "", ", pages %s");
      $formatted_entry .= $this->getb("editor", "", ", ed. by %s");
      $formatted_entry .= $this->getb("publisher", "", ", %s");
      $formatted_entry .= $this->getb("address", "", ", %s");
      $formatted_entry .= $this->getb("year", "<b>unknown year</b>", ", <b>%s</b>");
      $formatted_entry .= ".";
      break;

    case "manual":
      if ($this->issetb("organization")) {
        $formatted_entry .= $this->getb("organization");
      } else {
        $formatted_entry .= $this->getb("publisher", "unknown publisher");
      }
      $formatted_entry .= $this->getb("address", "", ", %s");
      $formatted_entry .= $this->getb("year", "<b>unknown year</b>", ", <b>%s</b>");
      $formatted_entry .= ".";
      break;

    case "misc":
      $formatted_entry .= $this->getb("howpublished", "", "<i>%s</i>, ");
      $formatted_entry .= $this->getb("year", "<b>unknown year</b>", "<b>%s</b>");
      $formatted_entry .= ".";
      break;

    case "unpublished":
    default:
      $formatted_entry .= $this->getb("year", "<b>unknown year</b>", "<b>%s</b>");
      $formatted_entry .= ".";
      break;
    } //end switch
    $formatted_entry .= "\n";

    // Notes
    if ($this->issetb("note")) {
      if (!$make_compact) $formatted_entry .= "<br/>\n";
      $formatted_entry .= $this->getb("note");
    }

    // Links
    if (!$make_compact)
      $formatted_entry .= "<br/>\n";

    // link to full entry
    $formatted_entry .= $fullentrylink;

    // link to files
    $formatted_entry .= $filelinks;

    $eprinturl = null;
    if ($this->issetb("e-print")) {
      // The ExternBib-specific e-print field can contain either
      // an arXiv identifier or an url to another preprint server
      $eprintval = $this->current_entry["e-print"];
      if (preg_match('/^https?:\\/\\//', $eprintval)) {
        $eprinturl = $eprintval;
      } else {
        $eprinturl = $this->eprintbaseurl . "/" . $eprintval;
      }
    } elseif ($this->issetb("eprint")) {
      // Regular e-print field
      $eprintval = $this->current_entry["eprint"];
      $arxivid = null;
      if (mb_strtolower($this->getb("archiveprefix")) === "arxiv") {
        if ($this->issetb("primaryclass")) {
          $arxivid = $this->current_entry["primaryclass"] . '/' . $eprintval;
        } else {
          $arxivid = $eprintval;
        }
      } elseif (preg_match('/^([0-9]+\\.[0-9]+|[a-zA-Z\\.\\-]+\\/[0-9]+)$/', $eprintval) === 1) {
        // Most likely an arXiv identifier
        $arxivid = $eprintval;
      }
      if (!is_null($arxivid)) {
        $eprinturl = sprintf("https://arxiv.org/abs/%s", $arxivid);
      }
    }
    if (!is_null($eprinturl)) {
      $formatted_entry .= "<a href=\"" . $eprinturl . "\">[Preprint]</a>\n";
    }
    $formatted_entry .= $this->getb("doi", "", "<a href=\"" . $this->doibaseurl . "/%s\">[DOI]</a>\n");
    $formatted_entry .= $this->getb("url", "", "<a href=\"%s\">[URL]</a>\n");

    // Abstract
    if ($show_abstract and $this->issetb("abstract")) {
      $formatted_entry .= "<div style=\"margin:0pt 1em 1em 1em;font-size:75%\">\n";
      $formatted_entry .= $this->getb("abstract") . "\n";
      $formatted_entry .= "</div>\n";
    }

    // Timestamp
    $formatted_entry .= $timestamp;

    // BibTeX record
    if ($show_bibtex and $this->issetb("fullEntry")) {
      $formatted_entry .= "<pre>\n";
      $formatted_entry .= $this->getb("fullEntry") . "\n";
      $formatted_entry .= "</pre>\n";
    }

    $this->current_entry = null;
    return $formatted_entry;
  }
}
