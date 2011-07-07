<?php
class SpecialExternBibSearch extends SpecialPage {
  function __construct() {
    parent::__construct( 'ExternBibSearch' );
    wfLoadExtensionMessages('ExternBib');
  }
  
  function execute( $par ) {
    global $wgRequest, $wgOut, $wgUser, $wgExternBib;
    
    $this->setHeaders();
    
    // Get request data from, e.g.
    // $param = $wgRequest->getText('param');
    
    ob_start();
    $query = $wgRequest->getVal('query');

    echo "<p>";
    echo wfMsg('externbib-enterquery'); 
    ?>:<br/>
    <form name="searchform2" action="" method="get">
       <input style="width:400px" type="text" name="query" value="<?php print htmlspecialchars($query) ?>"/>
       <input type="submit" value="Search"/>
       </form>
       </p>
       <?php
       if ($query) {
	 echo "<h2>" . wfMsg('externbib-results') . "</h2>\n";
	 
	 // print results
	 $found_entries = $wgExternBib->search_entries($query);
	 if (!is_array($found_entries)) {
	   echo '<p class="error">' . $found_entries . "</p>\n";
	 } elseif (count($found_entries) == 0) {
	   echo '<p class="error">' . wfMsg('externbib-noresults') . "</p>\n";
	 } else {
	   if ($wgUser->isLoggedIn()) {
	     $format_options = array( "meta" => 1, "pdflink" => 1, );
	   } else {
	     $format_options = array();
	   }
	   echo '<p>' . wfMsg('externbib-gotentries', count($found_entries)) . "</p>\n";

	   echo "<ul class=\"plainlinks\">\n";
	   foreach ($found_entries as $entry) {
	     $wgExternBib->format_entry($entry, $format_options);
	   }
	   echo "</ul>\n";
	 }
       }
    
    $output = ob_get_contents();
    ob_end_clean();
    $wgOut->addHTML($output);
  }
}

