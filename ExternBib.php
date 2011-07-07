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
$wgAutoloadClasses['SpecialExternBibFullEntry'] = $dir . 'SpecialExternBibFullEntry.php';
$wgSpecialPages['ExternBibSearch'] = 'SpecialExternBibSearch';
$wgSpecialPageGroups['ExternBibSearch'] = 'other';
$wgSpecialPages['ExternBibFullEntry'] = 'SpecialExternBibFullEntry';
$wgSpecialPageGroups['ExternBibFulEntry'] = 'other';

if (!isset($wgExternBibDBFile)) $wgExternBibDBFile = NULL;
if (!isset($wgExternBibPDFDirs)) $wgExternBibPDFDirs = NULL;
if (!isset($wgExternBibDOIBaseURL)) $wgExternBibDOIBaseURL = NULL;
if (!isset($wgExternBibEPrintBaseURL)) $wgExternBibEPrintBaseURL = NULL;

// setup the module
function efExternBibSetup() {
  global $wgParser, 
    $wgExternBib,
    $wgExternBibDBFile, 
    $wgExternBibPDFDirs, 
    $wgExternBibDOIBaseURL,
    $wgExternBibEPrintBaseURL;

  $wgExternBib = new ExternBib($wgExternBibDBFile,
			       $wgExternBibPDFDirs, 
			       $wgExternBibDOIBaseURL,
			       $wgExternBibEPrintBaseURL
			       );

  // register the tags
  $wgParser->setHook("bibentry", array($wgExternBib, 'bibentry'));
  $wgParser->setHook("bibsearch", array($wgExternBib, 'bibsearch'));
  
  return true;
}

?>
