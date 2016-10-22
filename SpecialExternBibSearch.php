<?php
class SpecialExternBibSearch extends SpecialPage {
  function __construct() {
    parent::__construct( 'ExternBibSearch' );
  }
  
  function execute( $par ) {
    global $wgRequest, $wgOut, $wgUser, $wgExternBib, $wgExternBibDBFiles, $wgExternBibDBNames;
    
    $this->setHeaders();
    
    // Get request data from, e.g.
    // $param = $wgRequest->getText('param');
    
    ob_start();
    $query = $wgRequest->getVal('query');
    //$databases = $wgRequest->getArray('databases');
    $databases = $wgRequest->getVal('databases');

    echo "<p>";
    echo wfMessage('externbib-enterquery')->text(); 
    ?>:<br/>
    <form name="searchform2" action="" method="get">
       <input style="width:400px" type="text" name="query" value="<?php print htmlspecialchars($query) ?>"/><br>
       <?php
       if (count($wgExternBibDBFiles)>1){
	 foreach ($wgExternBibDBFiles as $key => $url){
	   if ($databases==""){ // use first one as default;
	     $databases=$key;
	   }
	   echo '<input type="radio" name="databases" value="'.$key.'"';
	   if ($databases == $key)
	     echo 'checked="checked"';
	   if (isset($wgExternBibDBNames[$key])){
	     echo "> ".$wgExternBibDBNames[$key]."<br>";
	   }
	   else{
	     echo "> $key<br>";
	   }
	 }
       }
       ?>
       <input type="submit" value="Search"/>
       </form>
       </p>
       <?php
       if ($query) {
	 echo "<h2>" . wfMessage('externbib-results')->text() . "</h2>\n";
	 //$error = 0;
	 //$no_entries = 0;
	 //$nbr_entries = 0;
	 
	 // print results
	 $found_entries = $wgExternBib->search_entries($query, $databases);
	 
	 /*
	 foreach ($databases as $database)
	 {
	    $found_entries[$database] = $wgExternBib->search_entries($query, array($database => $database));
	    if (!is_array($found_entries[$database]))
	       $error++;
	    elseif (count($found_entries[$database]) == 0)
	       $no_entries++;
	    else
	    {
	       $database_ok[] = $database;
	       $nbr_entries = count($found_entries[$database]);
	    }
	 }
	 */

	 if (!is_array($found_entries)) {
	   echo '<p class="error">' . $found_entries . "</p>\n";
	 } elseif (count($found_entries) == 0) {
	   echo '<p class="error">' . wfMessage('externbib-noresults')->text() . "</p>\n";
	 } else {
	   if ($wgUser->isLoggedIn()) {
	     $format_options = array( "meta" => 1, "pdflink" => 1, );
	   } else {
	     $format_options = array();
	   }
	   echo '<p>' . wfMessage('externbib-gotentries', count($found_entries))->text() . "</p>\n";
	   
           $wgExternBib->format_entries($found_entries, $format_options);
	 }
	 
	 /*
	 if ($error == count($wgExternBibDBFiles)) {
	   echo '<p class="error">';
	   foreach ($databases as $database)
	   {
	      echo $found_entries[$database]." ";
	   }
	   echo "</p>\n";
	 } elseif ($no_entries == count($wgExternBibDBFiles) || ($no_entries + $error) == count($wgExternBibDBFiles)) {
	   echo '<p class="error">' . wfMessage('externbib-noresults')->text() . "</p>\n";
	 } else {
	   echo '<p>' . wfMessage('externbib-gotentries', $nbr_entries)->text() . "</p>\n";
	   foreach ($database_ok as $database)
	   {
	   echo $database;
	      if ($wgUser->isLoggedIn()) {
	        $format_options = array( "meta" => 1, "pdflink" => 1, );
	      } else {
	        $format_options = array();
	      }
	      
              $wgExternBib->format_entries($found_entries[$database], $format_options);
           }
	 }
	 */
       }
    
    $output = ob_get_contents();
    ob_end_clean();
    $wgOut->addHTML($output);
  }
}

