<?php
if (!defined('MEDIAWIKI')) die();

require_once( "$IP/includes/SpecialPage.php" );

// Interface to mediawiki
$wgExternBibCredits =
  array(
	'name' => 'ExternBib',
	'version' => '2.0',
	'author' => 'Olaf Lenz, Christopher Wagner',
	'url' => 'https://github.com/olenz/externbib',
	'description' => 'Cite an external bibtex file.',
	'descriptionmsg' => 'externbib-desc',
	);
$wgExtensionCredits['parserhook'][] = $wgExternBibCredits;
$wgExtensionCredits['specialpage'][] = $wgExternBibCredits;

$dir = dirname(__FILE__) . '/';

// directly load ExternBib.class.php, as an instance will be created
// anyway
require_once($dir . 'ExternBib.class.php');

$wgExtensionMessagesFiles['ExternBib'] = $dir . 'ExternBib.i18n.php';
$wgExtensionFunctions[] = 'efExternBibSetup';

$wgAutoloadClasses['SpecialExternBibSearch'] = $dir . 'SpecialExternBibSearch.php';
$wgSpecialPages['ExternBibSearch'] = 'SpecialExternBibSearch';
$wgSpecialPageGroups['ExternBibSearch'] = 'other';

$wgAutoloadClasses['SpecialExternBibShowEntry'] = $dir . 'SpecialExternBibShowEntry.php';
$wgSpecialPages['ExternBibShowEntry'] = 'SpecialExternBibShowEntry';
$wgSpecialPageGroups['ExternBibShowEntry'] = 'other';

// defaults
if (!isset($wgExternBibDBFile)) 
  $wgExternBibDBFile = "$dir/test/externbib.db";
if (!isset($wgExternBibFileDirs)) 
  $wgExternBibFileDirs ="$dir/test/pdf" ;
if (!isset($wgExternBibFileBaseURLs)) 
  $wgExternBibFileBaseURLs = "extensions/ExternBib/test/pdf";
if (!isset($wgExternBibDOIBaseURL)) 
  $wgExternBibDOIBaseURL = "http://dx.doi.org";
if (!isset($wgExternBibEPrintBaseURL)) 
  $wgExternBibEPrintBaseURL = "http://arxiv.org/abs";
if (!isset($wgExternBibDefaultFormat)) 
  $wgExternBibDefaultFormat = array();

// setup the module
function efExternBibSetup() {
  global $wgParser, 
    $wgExternBib,
    $wgExternBibDBFile, 
    $wgExternBibFileDirs, 
    $wgExternBibFileBaseURLs, 
    $wgExternBibDOIBaseURL,
    $wgExternBibEPrintBaseURL,
    $wgExternBibDefaultFormat;

  $wgExternBib = new ExternBib($wgExternBibDBFile,
			       $wgExternBibFileDirs, 
			       $wgExternBibFileBaseURLs, 
			       $wgExternBibDOIBaseURL,
			       $wgExternBibEPrintBaseURL,
			       $wgExternBibDefaultFormat
			       );

  // register the tags
  $wgParser->setHook("bibentry", array($wgExternBib, 'bibentry'));
  $wgParser->setHook("bibsearch", array($wgExternBib, 'bibsearch'));
  
  return true;
}

?>
