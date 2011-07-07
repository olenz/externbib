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
$wgAutoloadClasses['SpecialExternBibSearch'] = $dir . 'SpecialExternBibSearch.php';
$wgExtensionFunctions[] = 'efExternBibSetup';
$wgSpecialPages['ExternBib'] = 'SpecialExternBibSearch';
$wgSpecialPageGroups['ExternBib'] = 'other';

// setup the module
function efExternBibSetup() {
  global $wgMessageCache, $wgParser, $wgExternBib;

  $wgExternBib = new ExternBib($wgExternBibDBFile, 
			       $wgExternBibPDFDirs, 
			       $wgExternBibPDFURLBases,
			       $wgExternBibDOIBase,
			       $wgExternBibEPrintBase
			       );

  // register the tags
  $wgParser->setHook("bibentry", array($wgExternBib, 'bibentry'));
  $wgParser->setHook("bibsearch", array($wgExternBib, 'bibsearch'));
  
  // setup the special page
  $wgMessageCache->addMessage('bibsearch', 'Search in the bibtex database');
  SpecialPage::addPage(new SpecialPage('BibSearch', '', true, 'ExternBibSpecialBibSearch', '', true)); 

  return true;
}

?>
