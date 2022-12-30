<?php
/**
 * Utility functions that process escape sequences.
 *
 * @package    ExternBib
 * @author     Olaf Lenz
 * @copyright  2011-2014 The Authors
 * @license    https://opensource.org/licenses/BSD-3-Clause New BSD License
 * @link       https://github.com/olenz/externbib
 */

// translation of bibtex chars to utf8 chars
$bibtex2utf8_array=
array(
      '\\"a' => "ä", 
      '\\"A' => "Ä",
      '\\"e' => "ë",
      '\\"E' => "Ë",
      '\\"i' => "ï",
      '\\"I' => "Ï",
      '\\"o' => "ö", 
      '\\"O' => "Ö",
      '\\"u' => "ü", 
      '\\"U' => "Ü",
      '\\\'a' => "á", 
      '\\\'A' => "Á", 
      '\\\'e' => "é", 
      '\\\'E' => "É",
      '\\\'i' => "í", 
      '\\\'I' => "Í",
      '\\\'o' => "ó", 
      '\\\'O' => "Ó",
      '\\\'u' => "ú", 
      '\\\'U' => "Ú",
      '\\^a' => "â", 
      '\\^A' => "Â",
      '\\^e' => "ê", 
      '\\^E' => "Ê",
      '\\^i' => "î", 
      '\\^I' => "Î",
      '\\^o' => "ô", 
      '\\^O' => "Ô",
      '\\^u' => "û", 
      '\\^U' => "Û",
      '\\`a' => "à", 
      '\\`A' => "À",
      '\\`e' => "è", 
      '\\`E' => "È",
      '\\`i' => "ì", 
      '\\`I' => "Ì",
      '\\`o' => "ò", 
      '\\`O' => "Ò",
      '\\`u' => "ù", 
      '\\`U' => "Ù",
      '\\aa' => "å", 
      '\\AA' => "Å",
      '\\ae' => "æ",
      '\\AE' => "Æ",
      '\\o' => "ø", 
      '\\O' => "Ø",
      '\\c c' => "ç", 
      '\\c C' => "Ç",
      '---' => '&mdash;',
      '--' => '–', 
      '\\-' => '',
      '~' => ' ',
      '\\vs' => "&scaron;",
      '\\&' => "&",
      );
			 
$bibtexenc=array_keys($bibtex2utf8_array);
$utf8enc=array_values($bibtex2utf8_array);

// Remove bibtex chars from the string
function bibtex2utf8($string) {
  global $bibtexenc;
  global $utf8enc;

  $string = preg_replace('/([^\\\]|^)\}/', '$1' ,$string);
  $string = preg_replace('/([^\\\]|^)\{/', '$1' ,$string);
  $string = str_replace($bibtexenc, $utf8enc, $string);
  return $string;
}

?>
