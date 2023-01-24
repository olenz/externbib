<?php
/**
 * Integration tests for the database functionality.
 *
 * @package    ExternBib
 * @author     Jean-Noël Grad
 * @copyright  2022 The Authors
 * @license    https://opensource.org/licenses/BSD-3-Clause New BSD License
 * @link       https://github.com/olenz/externbib
 */
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'makedb.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'bibdb.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'BibTex.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'tex2html.php');

function pretty_print_from_backtrace($level) {
  $bt = debug_backtrace();
  $filename = $bt[$level]['file'];
  $fileno = $bt[$level]['line'];
  return "$filename:$fileno";
}

function check_db_field($array, $key, $expected) {
  $output = $array[$key];
  if (strcmp($output, $expected) !== 0) {
    $line = pretty_print_from_backtrace(1);
    echo "Database value for key '$key' failed checks at $line:\n";
    echo "  output:   \"$output\"\n";
    echo "  expected: \"$expected\"\n";
    exit(0);
  }
}

$bibparser = new Structures_BibTex();
$bibparser->setOption("extractAuthors", false);
$bibparser->setOption("removeCurlyBraces", false);
$bibparser->setOption("unwrap", true);
$bibparser->setOption("storeFullEntries", true);

$bibentry = "@Article{example01a,
  author    = {\\o \\v s\\v{c}{\\r A}{\\v{e}}{\\AA}\\'e\\\"u\\~n---\\&{}\\\\\\^a},
  title     = {{\$X\$\\textsubscript{\\textsuperscript{9}\(_4\)BeF\\textsubscript{2}}}\\,=\\,1.5 at $+1^{\\circ}\$C},
  subtitle  = {\\ensuremath{\\alpha} \$\\mathbb{C}\$ \\(\\mathscr N\\) {\\it in silico \\textbf{m}} 4-\$n\$-alkyl-(\$N\$,\\(N\\)},
  booktitle = {{{{Comment}}} {\\emph{on}:} ```a' and `b'''},
  journal   = {Journal Name},
  year      = {2001},
  volume    = {6},
  number    = {11},
  pages     = {7--8},
}";

$bibfile = fopen("php://memory", "rw+");
fputs($bibfile, "\n% comment line\n$bibentry\n");
rewind($bibfile);

echo "Testing populate_db()\n";
$db = bibdb_open(":memory:", "n");
$modes = ConversionModes::Newlines | ConversionModes::Diacritics | ConversionModes::LaTeXMacros | ConversionModes::MathSimple | ConversionModes::StripCurlyBraces;
populate_db($db, $bibfile, $bibparser, $modes, false);
$sqliteobject = bibdb_fetch("example01a", $db);
$array = unserialize($sqliteobject);
unset($sqliteobject);
check_db_field($array, "fullEntry", $bibentry);
check_db_field($array, "cite", "example01a");
check_db_field($array, "author", "øščÅěÅéüñ&mdash;&amp;\nâ");
check_db_field($array, "year", "2001");
check_db_field($array, "journal", "Journal Name");
check_db_field($array, "pages", "7&ndash;8");
check_db_field($array, "title", "<span><i>X</i></span><sub><sup>9</sup><span><sub>4</sub></span>BeF<sub>2</sub></sub> = 1.5 at <span>+1&deg;</span>C");
check_db_field($array, "subtitle", "<i>&alpha;</i> <span>&Copf;</span> <span>&Nscr;</span> <i>in silico <b>m</b></i> 4-<span><i>n</i></span>-alkyl-(<span><i>N</i></span>,<span><i>N</i></span>");
check_db_field($array, "booktitle", "Comment <em>on</em>: &ldquo;&lsquo;a&rsquo; and &lsquo;b&rsquo;&rdquo;");
bibdb_close($db);

echo "The testsuite was successful.\n";
exit(0);
?>
