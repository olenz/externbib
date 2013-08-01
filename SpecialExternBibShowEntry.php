<?php
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

