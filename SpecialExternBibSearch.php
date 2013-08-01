<?php
class SpecialExternBibSearch extends SpecialPage {
  function __construct() {
    parent::__construct( 'ExternBibSearch' );
  }
  
  function execute( $par ) {
    global $wgRequest, $wgOut, $wgUser, $wgExternBib, $wgExternBibDBFiles;
    
    $this->setHeaders();
    
    // Get request data from, e.g.
    // $param = $wgRequest->getText('param');
    
    ob_start();
    $query = $wgRequest->getVal('query');
    //$databases = $wgRequest->getArray('databases');
    $databases = $wgRequest->getVal('databases');

    echo "<p>";
    echo wfMsg('externbib-enterquery'); 
    ?>:<br/>
    <form name="searchform2" action="" method="get">
       <input style="width:400px" type="text" name="query" value="<?php print htmlspecialchars($query) ?>"/><br>
       <?php
          echo '<input type="radio" name="databases" value="icp"';
          if ($databases && strpos($databases, "icp") === false)
             echo '> ICP.bib<br>';
          else
             echo 'checked="checked"> ICP.bib<br>';
             
          echo '<input type="radio" name="databases" value="library"';
          if ($databases)
          {
             if (strpos($databases, "library") !== false)
                echo 'checked="checked"';
          }
          echo '> ICP-Library.bib<br>';
          
          /*  problem with identical keys in both files
          echo '<input type="checkbox" name="databases[icp]" value="icp"';
          if ($databases && !array_key_exists("icp", $databases))
             echo '> ICP.bib<br>';
          else
             echo 'checked="checked"> ICP.bib<br>';
             
          echo '<input type="checkbox" name="databases[library]" value="library"';
          if ($databases && !array_key_exists("library", $databases))
             echo '> ICP-Library.bib<br>';
          else
             echo 'checked="checked"> ICP-Library.bib<br>';
             */
       ?>
       <input type="submit" value="Search"/>
       </form>
       </p>
       <?php
       if ($query) {
	 echo "<h2>" . wfMsg('externbib-results') . "</h2>\n";
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
	   echo '<p class="error">' . wfMsg('externbib-noresults') . "</p>\n";
	 } else {
	   if ($wgUser->isLoggedIn()) {
	     $format_options = array( "meta" => 1, "pdflink" => 1, );
	   } else {
	     $format_options = array();
	   }
	   echo '<p>' . wfMsg('externbib-gotentries', count($found_entries)) . "</p>\n";
	   
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
	   echo '<p class="error">' . wfMsg('externbib-noresults') . "</p>\n";
	 } else {
	   echo '<p>' . wfMsg('externbib-gotentries', $nbr_entries) . "</p>\n";
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

