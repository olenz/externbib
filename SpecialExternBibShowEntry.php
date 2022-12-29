<?php
/**
 * Mediawiki special page to display a formatted BibTeX entry.
 *
 * @package    ExternBib
 * @author     Olaf Lenz
 * @copyright  2011,2013 The Authors
 * @license    https://opensource.org/licenses/BSD-3-Clause New BSD License
 * @link       https://github.com/olenz/externbib
 */
class SpecialExternBibShowEntry extends SpecialPage {
  function __construct() {
    parent::__construct( 'ExternBibShowEntry' );
  }
  
  function execute( $par ) {
    global $wgRequest, $wgOut, $wgUser, $wgExternBib;
    
    $this->setHeaders();
    
    if (!isset($par)) {
    } else {
      ob_start();
      $format_options = array("meta" => 1, 
			      "pdflink" => 1, 
			      "abstract" => 1,
			      "bibtex" => 1);
      
      $wgExternBib->format_entries(array($par), $format_options);
      
      $output = ob_get_contents();
      ob_end_clean();
      $wgOut->addHTML($output);
    }
  }
}

