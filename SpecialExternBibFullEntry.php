<?php
class SpecialExternBibFullEntry extends SpecialPage {
  function __construct() {
    parent::__construct( 'ExternBibFullEntry' );
    wfLoadExtensionMessages('ExternBib');
  }
  
  function execute( $par ) {
    global $wgRequest, $wgOut, $wgUser, $wgExternBib;
    
    $this->setHeaders();
    
    ob_start();
    $format_options = array( "meta" => 1, 
			     "pdflink" => 1, 
			     "abstract" => 1,
			     "bibtex" => 1);

    $wgExternBib->format_entry($par, $format_options);
    
    $output = ob_get_contents();
    ob_end_clean();
    $wgOut->addHTML($output);
  }
}

