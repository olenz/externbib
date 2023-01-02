<?php
/**
 * Utility functions that process escape sequences.
 *
 * @package    ExternBib
 * @author     Olaf Lenz
 * @author     Jean-Noël Grad
 * @copyright  2011-2014,2022 The Authors
 * @license    https://opensource.org/licenses/BSD-3-Clause New BSD License
 * @link       https://github.com/olenz/externbib
 */

/** Conversion modes. */
class ConversionModes {
    const Newlines         = 0b00000001; ///< Replace double backslashes by a newline (strongly recommended)
    const Diacritics       = 0b00000010; ///< Remove diacritic escape sequences (see @ref diacritics2utf8)
}

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

/**
 * Substitute LaTeX escape sequences and macros by equivalent HTML.
 *
 * @param  string  $value  The LaTeX string.
 * @param  int     $modes  LaTeX-to-HTML conversions to apply.
 * @return string  The converted string.
 */
function convert_latex_string($value, $modes) {
  if ($modes and (($modes & ConversionModes::Newlines) == 0)) {
    throw "Cannot process LaTeX without first substituting newlines";
  }
  if ($modes == 0) {
    return $value;
  }
  if ($modes & ConversionModes::Newlines) {
    $value = str_replace("\\\\", "\n", $value);
  }
  if ($modes & ConversionModes::Diacritics) {
    $value = diacritics2utf8($value);
  }
  return $value;
}

?>
