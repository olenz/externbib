<?php
/**
 * Utility functions that process escape sequences, LaTeX macros and math.
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
    const StripCurlyBraces = 0b00000100; ///< Strip all remaining unescaped curly braces (see @ref strip_curly_braces)
    const LaTeXMacros      = 0b00001000; ///< Replace LaTeX macros (see @ref latex2html)
    const MathSimple       = 0b00010000; ///< Replace simple math environments by HTML (see @ref math2html)
    const MathJax          = 0b00100000; ///< Replace all math environments by MathJax (see @ref math2mathjax)
}

/** Mapping of LaTeX diacritics escape sequences to UTF-8 symbols. */
$bibtex2utf8_array = array(
  '\\"a' => 'ä',
  '\\"A' => 'Ä',
  '\\"e' => 'ë',
  '\\"E' => 'Ë',
  '\\"i' => 'ï',
  '\\"I' => 'Ï',
  '\\"o' => 'ö',
  '\\"O' => 'Ö',
  '\\"r' => 'r̈',
  '\\"R' => 'R̈',
  '\\"u' => 'ü',
  '\\"U' => 'Ü',
  '\\"y' => 'ÿ',
  '\\"Y' => 'Ÿ',
  '\\\'a' => 'á',
  '\\\'A' => 'Á',
  '\\\'c' => 'ć',
  '\\\'C' => 'Ć',
  '\\\'e' => 'é',
  '\\\'E' => 'É',
  '\\\'i' => 'í',
  '\\\'I' => 'Í',
  '\\\'l' => 'ĺ',
  '\\\'L' => 'Ĺ',
  '\\\'n' => 'ń',
  '\\\'N' => 'Ń',
  '\\\'o' => 'ó',
  '\\\'O' => 'Ó',
  '\\\'r' => 'ŕ',
  '\\\'R' => 'Ŕ',
  '\\\'s' => 'ś',
  '\\\'S' => 'Ś',
  '\\\'u' => 'ú',
  '\\\'U' => 'Ú',
  '\\\'y' => 'ý',
  '\\\'Y' => 'Ý',
  '\\\'z' => 'ź',
  '\\\'Z' => 'Ź',
  '\\\'\\ae' => 'ǽ',
  '\\\'\\AE' => 'Ǽ',
  '\\\'\\o' => 'ǿ',
  '\\\'\\O' => 'Ǿ',
  '\\^a' => 'â',
  '\\^A' => 'Â',
  '\\^c' => 'ĉ',
  '\\^C' => 'Ĉ',
  '\\^e' => 'ê',
  '\\^E' => 'Ê',
  '\\^g' => 'ĝ',
  '\\^G' => 'Ĝ',
  '\\^h' => 'ĥ',
  '\\^H' => 'Ĥ',
  '\\^i' => 'î',
  '\\^I' => 'Î',
  '\\^j' => 'ĵ',
  '\\^J' => 'Ĵ',
  '\\^o' => 'ô',
  '\\^O' => 'Ô',
  '\\^r' => 'r̂',
  '\\^R' => 'R̂',
  '\\^s' => 'ŝ',
  '\\^S' => 'Ŝ',
  '\\^u' => 'û',
  '\\^U' => 'Û',
  '\\^w' => 'ŵ',
  '\\^W' => 'Ŵ',
  '\\^x' => 'x̂',
  '\\^X' => 'X̂',
  '\\^y' => 'ŷ',
  '\\^Y' => 'Ŷ',
  '\\`a' => 'à',
  '\\`A' => 'À',
  '\\`e' => 'è',
  '\\`E' => 'È',
  '\\`i' => 'ì',
  '\\`I' => 'Ì',
  '\\`o' => 'ò',
  '\\`O' => 'Ò',
  '\\`u' => 'ù',
  '\\`U' => 'Ù',
  '\\`y' => 'ỳ',
  '\\`Y' => 'Ỳ',
  '\\~a' => 'ã',
  '\\~A' => 'Ã',
  '\\~e' => 'ẽ',
  '\\~E' => 'Ẽ',
  '\\~i' => 'ĩ',
  '\\~I' => 'Ĩ',
  '\\~n' => 'ñ',
  '\\~N' => 'Ñ',
  '\\~o' => 'õ',
  '\\~O' => 'Õ',
  '\\~u' => 'ũ',
  '\\~U' => 'Ũ',
  '\\~v' => 'ṽ',
  '\\~V' => 'Ṽ',
  '\\~y' => 'ỹ',
  '\\~Y' => 'Ỹ',
  '\\=a' => 'ā',
  '\\=A' => 'Ā',
  '\\=e' => 'ē',
  '\\=E' => 'Ē',
  '\\=I' => 'Ī',
  '\\=i' => 'ī',
  '\\=o' => 'ō',
  '\\=O' => 'Ō',
  '\\=u' => 'ū',
  '\\=U' => 'Ū',
  '\\=v' => 'v̄',
  '\\=V' => 'V̄',
  '\\=x' => 'x̄',
  '\\=X' => 'X̄',
  '\\=y' => 'ȳ',
  '\\=Y' => 'Ȳ',
  '\\=\\ae' => 'ǣ',
  '\\=\\AE' => 'Ǣ',
  '\\.a' => 'ȧ',
  '\\.A' => 'Ȧ',
  '\\.b' => 'ḃ',
  '\\.B' => 'Ḃ',
  '\\.c' => 'ċ',
  '\\.C' => 'Ċ',
  '\\.d' => 'ḋ',
  '\\.D' => 'Ḋ',
  '\\.e' => 'ė',
  '\\.E' => 'Ė',
  '\\.f' => 'ḟ',
  '\\.F' => 'Ḟ',
  '\\.g' => 'ġ',
  '\\.G' => 'Ġ',
  '\\.h' => 'ḣ',
  '\\.H' => 'Ḣ',
  '\\.I' => 'İ',
  '\\.k' => 'k̇',
  '\\.K' => 'K̇',
  '\\.l' => 'l̇',
  '\\.L' => 'L̇',
  '\\.m' => 'ṁ',
  '\\.M' => 'Ṁ',
  '\\.n' => 'ṅ',
  '\\.N' => 'Ṅ',
  '\\.o' => 'ȯ',
  '\\.O' => 'Ȯ',
  '\\.p' => 'ṗ',
  '\\.P' => 'Ṗ',
  '\\.q' => 'q̇',
  '\\.Q' => 'Q̇',
  '\\.r' => 'ṙ',
  '\\.R' => 'Ṙ',
  '\\.s' => 'ṡ',
  '\\.S' => 'Ṡ',
  '\\.t' => 'ṫ',
  '\\.T' => 'Ṫ',
  '\\.u' => 'u̇',
  '\\.U' => 'U̇',
  '\\.v' => 'v̇',
  '\\.V' => 'V̇',
  '\\.w' => 'ẇ',
  '\\.W' => 'Ẇ',
  '\\.x' => 'ẋ',
  '\\.X' => 'Ẋ',
  '\\.y' => 'ẏ',
  '\\.Y' => 'Ẏ',
  '\\.z' => 'ż',
  '\\.Z' => 'Ż',
  '\\c c' => 'ç',
  '\\c C' => 'Ç',
  '\\c d' => 'ḑ',
  '\\c D' => 'Ḑ',
  '\\c e' => 'ȩ',
  '\\c E' => 'Ȩ',
  '\\c h' => 'ḩ',
  '\\c H' => 'Ḩ',
  '\\c g' => 'ģ',
  '\\c G' => 'Ģ',
  '\\c k' => 'ķ',
  '\\c K' => 'Ķ',
  '\\c l' => 'ļ',
  '\\c L' => 'Ļ',
  '\\c n' => 'ņ',
  '\\c N' => 'Ņ',
  '\\c r' => 'ŗ',
  '\\c R' => 'Ŗ',
  '\\c s' => 'ş',
  '\\c S' => 'Ş',
  '\\c t' => 'ţ',
  '\\c T' => 'Ţ',
  '\\d a' => 'ạ',
  '\\d A' => 'Ạ',
  '\\d b' => 'ḅ',
  '\\d B' => 'Ḅ',
  '\\d c' => 'c̣',
  '\\d C' => 'C̣',
  '\\d d' => 'ḍ',
  '\\d D' => 'Ḍ',
  '\\d e' => 'ẹ',
  '\\d E' => 'Ẹ',
  '\\d f' => 'f̣',
  '\\d F' => 'F̣',
  '\\d g' => 'g̣',
  '\\d G' => 'G̣',
  '\\d h' => 'ḥ',
  '\\d H' => 'Ḥ',
  '\\d i' => 'ị',
  '\\d I' => 'Ị',
  '\\d j' => 'j̣',
  '\\d J' => 'J̣',
  '\\d k' => 'ḳ',
  '\\d K' => 'Ḳ',
  '\\d l' => 'ḷ',
  '\\d L' => 'Ḷ',
  '\\d m' => 'ṃ',
  '\\d M' => 'Ṃ',
  '\\d n' => 'ṇ',
  '\\d N' => 'Ṇ',
  '\\d o' => 'ọ',
  '\\d O' => 'Ọ',
  '\\d p' => 'p̣',
  '\\d P' => 'P̣',
  '\\d q' => 'q̣',
  '\\d Q' => 'Q̣',
  '\\d r' => 'ṛ',
  '\\d R' => 'Ṛ',
  '\\d s' => 'ṣ',
  '\\d S' => 'Ṣ',
  '\\d t' => 'ṭ',
  '\\d T' => 'Ṭ',
  '\\d u' => 'ụ',
  '\\d U' => 'Ụ',
  '\\d v' => 'ṿ',
  '\\d V' => 'Ṿ',
  '\\d w' => 'ẉ',
  '\\d W' => 'Ẉ',
  '\\d x' => 'x̣',
  '\\d X' => 'X̣',
  '\\d y' => 'ỵ',
  '\\d Y' => 'Ỵ',
  '\\d z' => 'ẓ',
  '\\d Z' => 'Ẓ',
  '\\k a' => 'ą',
  '\\k A' => 'Ą',
  '\\k e' => 'ę',
  '\\k E' => 'Ę',
  '\\k i' => 'į',
  '\\k I' => 'Į',
  '\\k n' => 'n̨',
  '\\k N' => 'N̨',
  '\\k o' => 'ǫ',
  '\\k O' => 'Ǫ',
  '\\k u' => 'ų',
  '\\k U' => 'Ų',
  '\\k s' => 's̨',
  '\\k S' => 'S̨',
  '\\k z' => 'z̨',
  '\\k Z' => 'Z̨',
  '\\r a' => 'å',
  '\\r A' => 'Å',
  '\\r u' => 'ů',
  '\\r U' => 'Ů',
  '\\r w' => 'ẘ',
  '\\r y' => 'ẙ',
  '\\u a' => 'ă',
  '\\u A' => 'Ă',
  '\\u e' => 'ĕ',
  '\\u E' => 'Ĕ',
  '\\u g' => 'ğ',
  '\\u G' => 'Ğ',
  '\\u i' => 'ĭ',
  '\\u I' => 'Ĭ',
  '\\u o' => 'ŏ',
  '\\u O' => 'Ŏ',
  '\\u s' => 's̆',
  '\\u S' => 'S̆',
  '\\u u' => 'ŭ',
  '\\u U' => 'Ŭ',
  '\\u y' => 'y̆',
  '\\u Y' => 'Y̆',
  '\\v a' => 'ǎ',
  '\\v A' => 'Ǎ',
  '\\v c' => 'č',
  '\\v C' => 'Č',
  '\\v d' => 'ď',
  '\\v D' => 'Ď',
  '\\v e' => 'ě',
  '\\v E' => 'Ě',
  '\\v g' => 'ǧ',
  '\\v G' => 'Ǧ',
  '\\v h' => 'ȟ',
  '\\v H' => 'Ȟ',
  '\\v i' => 'ǐ',
  '\\v I' => 'Ǐ',
  '\\v j' => 'ǰ',
  '\\v k' => 'ǩ',
  '\\v K' => 'Ǩ',
  '\\v l' => 'ľ',
  '\\v L' => 'Ľ',
  '\\v n' => 'ň',
  '\\v N' => 'Ň',
  '\\v o' => 'ǒ',
  '\\v O' => 'Ǒ',
  '\\v r' => 'ř',
  '\\v R' => 'Ř',
  '\\v s' => 'š',
  '\\v S' => 'Š',
  '\\v t' => 'ť',
  '\\v T' => 'Ť',
  '\\v u' => 'ǔ',
  '\\v U' => 'Ǔ',
  '\\v z' => 'ž',
  '\\v Z' => 'Ž',
  '\\H a' => 'a̋',
  '\\H A' => 'A̋',
  '\\H e' => 'e̋',
  '\\H E' => 'E̋',
  '\\H i' => 'i̋',
  '\\H I' => 'I̋',
  '\\H m' => 'm̋',
  '\\H M' => 'M̋',
  '\\H o' => 'ő',
  '\\H O' => 'Ő',
  '\\H u' => 'ű',
  '\\H U' => 'Ű',
  '\\H y' => 'ӳ',
  '\\H Y' => 'Y̋',
  '{\\aa}' => 'å',
  '{\\AA}' => 'Å',
  '{\\ae}' => 'æ',
  '{\\AE}' => 'Æ',
  '{\\oe}' => 'œ',
  '{\\OE}' => 'Œ',
  '{\\dh}' => 'ð',
  '{\\DH}' => 'Ð',
  '{\\dj}' => 'đ',
  '{\\DJ}' => 'Đ',
  '{\\ij}' => '&ijlig;',
  '{\\IJ}' => '&IJlig;',
  '{\\ss}' => 'ß',
  '{\\SS}' => 'ẞ',
  '{\\th}' => 'Þ',
  '{\\TH}' => 'þ',
  '{\\ng}' => 'ŋ',
  '{\\NG}' => 'Ŋ',
  '{\\i}' => 'ı',
  '{\\l}' => 'ł',
  '{\\L}' => 'Ł',
  '{\\o}' => 'ø',
  '{\\O}' => 'Ø',
  '{\\S}' => '§',
  '{\\P}' => '¶',
  '---' => '&mdash;',
  '--' => '&ndash;',
  '\\&' => '&amp;',
  '\\-' => '',
  '\\,' => ' ',
  '\\%' => '%',
  '\\$' => '&#36;',
  '\\_' => '_',
  '\\#' => '#',
  '~' => ' ',
);

/** Mapping of math escape sequences to UTF-8 symbols. */
$mathcal2utf8_array = array(
  'A' => '𝓐', 'B' => '𝓑', 'C' => '𝓒', 'D' => '𝓓', 'E' => '𝓔', 'F' => '𝓕',
  'G' => '𝓖', 'H' => '𝓗', 'I' => '𝓘', 'J' => '𝓙', 'K' => '𝓚', 'L' => '𝓛',
  'M' => '𝓜', 'N' => '𝓝', 'O' => '𝓞', 'P' => '𝓟', 'Q' => '𝓠', 'R' => '𝓡',
  'S' => '𝓢', 'T' => '𝓣', 'U' => '𝓤', 'V' => '𝓥', 'W' => '𝓦', 'X' => '𝓧',
  'Y' => '𝓨', 'Z' => '𝓩',
  'a' => '𝓪', 'b' => '𝓫', 'c' => '𝓬', 'd' => '𝓭', 'e' => '𝓮', 'f' => '𝓯',
  'g' => '𝓰', 'h' => '𝓱', 'i' => '𝓲', 'j' => '𝓳', 'k' => '𝓴', 'l' => '𝓵',
  'm' => '𝓶', 'n' => '𝓷', 'o' => '𝓸', 'p' => '𝓹', 'q' => '𝓺', 'r' => '𝓻',
  's' => '𝓼', 't' => '𝓽', 'u' => '𝓾', 'v' => '𝓿', 'w' => '𝔀', 'x' => '𝔁',
  'y' => '𝔂', 'z' => '𝔃',
);

/** Mapping of LaTeX special symbols to HTML symbols. */
$special2utf8_array = array(
  '\\textdegree' => '&deg;',
  '\\textendash' => '&ndash;',
  '\\textemdash' => '&mdash;',
  '\\textgreater' => '&gt;',
  '\\textless' => '&lt;',
  '\\textbackslash' => '&#92;',
  '\\textdollar' => '&#36;',
  '\\textbullet' => '&#149;',
  '\\textquestiondown' => '&iquest;',
  '\\textexclamdown' => '&iexcl;',
  '\\textasciitilde' => '&#126;',
  '\\textasciicircum' => '&#94;',
  '\\textregistered' => '&reg;',
  '\\texttrademark' => '&trade;',
  '\\textbraceleft' => '&#123;',
  '\\textbraceright' => '&#125;',
  '\\textperiodcentered' => '&dot;',
  '\\textparagraph' => '&para;',
  '\\textminus' => '&minus;',
  '\\copyright' => '&copy;',
  '\\pounds' => '&pound;',
  '\\textbar' => '&vert;',
  '\\dag' => '&dagger;',
  '\\ddag' => '&Dagger;',
  '\\textdagger' => '&dagger;',
  '\\textdaggerdbl' => '&Dagger;',
  '\\textquotesingle' => '&#39;',
  '\\textquotedbl' => '&quot;',
  '\\textquoteleft' => '&lsquo;',
  '\\textquoteright' => '&rsquo;',
  '\\textquotedblleft' => '&ldquo;',
  '\\textquotedblright' => '&rdquo;',
  '\\guillemetleft' => '&laquo;',
  '\\guillemetright' => '&raquo;',
  '\\guilsinglleft' => '&lsaquo;',
  '\\guilsinglright' => '&rsaquo;',
  '\\quotesinglbase' => '&sbquo;',
  '\\quotedblbase' => '&bdquo;',
);

$bibtexenc = array_keys($bibtex2utf8_array);
$utf8enc = array_values($bibtex2utf8_array);

/**
 * Replace diacritic escape sequences with their corresponding UTF-8 symbols.
 *
 * @param  string  $string  The BibTeX string.
 * @return string  The UTF-8 version.
 */
function diacritics2utf8($string) {
  global $bibtexenc;
  global $utf8enc;
  $graph_pat = '[ilLoOSP]|aa|AA|ae|AE|oe|OE|dh|DH|dj|DJ|ij|IJ|ng|NG|ss|SS|th|TH';
  // replace TeX escape sequences by UTF-8 characters
  $string = preg_replace('/\\\\('.$graph_pat.')(?: |(?=[^a-zA-Z]|$))/', '{\\\\$1}', $string);
  $string = preg_replace('/\\{\\{\\\\('.$graph_pat.')\\}\\}/', '{\\\\$1}', $string);
  $string = preg_replace('/(\\\\[\"\'\\^\\.`~=])\\{(\\\\?[a-zA-Z]+)\\}/', '$1$2', $string);
  $string = preg_replace('/(\\\\[bcdkruvH])\\{(\\\\?[a-zA-Z]+)\\}/', '$1 $2', $string);
  $string = str_replace($bibtexenc, $utf8enc, $string);
  // replace quotation marks
  if (strpos($string, '`') !== false) {
    $string = preg_replace('/(?<=^|[\\s\\[\\{\\(;]|--)``/', '&ldquo;', $string);
    $string = preg_replace('/(?<=^|[\\s\\[\\{\\(;]|--)`/',  '&lsquo;', $string);
    $string = preg_replace('/\'\'\'(?=[\\s\\.:;,\\!\\?\\]\\}\\)]|--|$)/', '&rsquo;&rdquo;', $string);
    $string = preg_replace('/\'\'(?=[\\s\\.:;,\\!\\?\\]\\}\\)]|--|$)/', '&rdquo;', $string);
    $string = preg_replace('/\'(?=[\\s\\.:;,\\!\\?\\]\\}\\)]|--|$)/', '&rsquo;', $string);
  }
  return $string;
}

function math_detect_opening($string, $pos, &$result) {
  $delimiters = array('\\(' => '\\)', '\\[' => '\\]', '$$' => '$$', '$' => '$', '\\ensuremath{' => '}');
  $delim_start = '';
  if ($string[$pos] == '}') {
    return $pos + 1;
  } elseif ($string[$pos] == '\\') {
    if ($string[$pos + 1] == '(') {
      $delim_start = '\\(';
    } elseif ($string[$pos + 1] == '[') {
      $delim_start = '\\[';
    } elseif ($string[$pos + 1] == 'e' and strcmp("\\ensuremath{", substr($string, $pos, 12)) === 0) {
      $delim_start = '\\ensuremath{';
    } else {
      return $pos + 2;
    }
  } elseif ($string[$pos] == '$') {
    if ($string[$pos + 1] == '$') {
      $delim_start = '$$';
    } else {
      $delim_start = '$';
    }
  }
  $pos_start = $pos;
  $pos_end = null;
  $delim_end = $delimiters[$delim_start];
  $result = [$pos_start, $delim_start, $pos_end, $delim_end, null];
  return $pos + mb_strlen($delim_start);
}

function math_detect_closing($string, $pos, &$result) {
  $needle = $result[3];
  $found = false;
  if ($string[$pos] == '\\') {
    if ($string[$pos + 1] == ')' and $needle == '\\)' or
        $string[$pos + 1] == ']' and $needle == '\\]') {
      $found = true;
    } else {
      return $pos + 2;
    }
  } elseif ($string[$pos] == '$' and
            ($needle == '$' or $string[$pos + 1] == '$' and $needle == '$$')) {
    $found = true;
  } elseif ($string[$pos] == '}' and $needle == '}') {
    $pos_start = $result[0];
    $substring = substr($string, $pos_start, $pos - $pos_start + 1);
    $substring = str_replace('\\\\', '\n', $substring);
    $substring = str_replace('\\{', '', $substring);
    $substring = str_replace('\\}', '', $substring);
    if (substr_count($substring, '{') === substr_count($substring, '}')) {
      $found = true;
    } else {
      return $pos + 1;
    }
  }
  if ($found) {
    $pos_start = $result[0];
    $pos_end = $pos + mb_strlen($needle);
    $result[2] = $pos_end;
    $result[4] = substr($string, $pos_start + mb_strlen($result[1]), $pos_end - $pos_start - mb_strlen($result[1]) - mb_strlen($result[3]));
    return $pos_end;
  }
  return $pos + 1;
}

/**
 * Detect and delimit all math environments.
 *
 * @param  string  $string  The BibTeX string.
 * @return array  The list of environments.
 */
function math_extractor($string) {
  $positions = [];
  preg_match_all('/\\$|\\\\|\\}/', $string, $matches, PREG_OFFSET_CAPTURE);
  $result = null;
  $pos_last = 0;
  foreach ($matches[0] as $match) {
    $pos = $match[1];
    if ($pos >= $pos_last) {
      if (is_null($result)) {
        $pos_last = math_detect_opening($string, $pos, $result);
      } else {
        $pos_last = math_detect_closing($string, $pos, $result);
        if (!is_null($result[4])) {
          array_push($positions, $result);
          $result = null;
        }
      }
    }
  }
  unset($match);
  if (!is_null($result)) {
    $numbers = "0";
    for ($i = 2; $i < strlen($string); $i++) {
      if ($i % 10 == 0) {
        $numbers = $numbers . $i++;
      } else {
        $numbers = $numbers . " ";
      }
    }
    $string_caret = '';
    foreach ($positions as $x) {
      $pos_start = $x[0];
      $pos_end = $x[2];
      $delim_start = $x[1];
      $delim_end = $x[3];
      $delim_len = mb_strlen($delim_start);
      $string_caret = $string_caret . str_repeat(' ', $pos_start - mb_strlen($string_caret)) . '^' . str_repeat('.', $pos_end - $pos_start - 2 * $delim_len) . '^';
    }
    unset($x);
    $pos_start = $result[0];
    $delim_start = $result[1];
    $delim_end = $result[3];
    $string_caret = $string_caret . str_repeat(' ', $pos_start - mb_strlen($string_caret)) . '^---> ?';
    throw new Exception("Cannot find closing delimiters '$delim_end' for opening delimiters '$delim_start' at position $pos_start:\n$numbers\n$string\n$string_caret\n");
  }
  return $positions;
}

/** Replace math environments by placeholders.
 *
 * @param[in]  string  $string     The BibTeX string.
 * @param[out] string  $token      Placeholder text.
 * @param[in]  array   $positions  Location of math environments.
 * @return string  The protected string.
 */
function protect_math($string, &$token, $positions) {
  $token = "abcdefghijk";
  while (strpos($string, $token) !== false) {
    $token = substr(md5(strval(mt_rand())), 0, 16);
  }
  for ($i = count($positions) - 1; $i >= 0; $i--) {
    $pos_start = $positions[$i][0];
    $pos_end = $positions[$i][2];
    $string = substr($string, 0, $pos_start) . "MATH" . $token . "INDEX" . $i . "END" . substr($string, $pos_end);
  }
  return $string;
}

/** Replace placeholders by the original math environments.
 *
 * @param[in]  string  $string     The BibTeX string.
 * @param[in]  string  $token      Placeholder text.
 * @param[in]  array   $positions  Location of math environments.
 * @return string  The deprotected string.
 */
function deprotect_math($string, $token, $positions) {
  for ($i = 0; $i < count($positions); $i++) {
    $string = str_replace(
      "MATH" . $token . "INDEX" . $i . "END",
      $positions[$i][1] . $positions[$i][4] . $positions[$i][3],
      $string);
  }
  return $string;
}

/**
 * Convert simple LaTeX math environments to HTML.
 *
 * Note: conversion is done on a "best effort" basis and is limited
 * to math environment with at most one token.
 *
 * @param[in]      string  $string     The LaTeX string containing math formula.
 * @param[in,out]  array   $positions  Location of math environments.
 * @return void
 */
function math2html($string, &$positions) {
  $greek_pat = '[Aa]lpha|[Bb]eta|[Gg]amma|[Dd]elta|[Ee]psilon|[Zz]eta|[Ee]ta|[Tt]heta|[Ii]ota|[Kk]appa|[Ll]ambda|[Mm]u|[Nn]u|[Xx]i|[Oo]micron|[Pp]i|[Rr]ho|[Ss]igma|[Tt]au|[Uu]psilon|[Pp]hi|[Cc]hi|[Pp]si|[Oo]mega';
  $beg = '/^\\s*';
  $end = '\\s*$/';
  $slant_symbols = function($symbols) {
    return preg_replace('/([a-zA-Z]+)/', '<i>$1</i>', $symbols);
  };
  foreach ($positions as &$result) {
    $string = $result[4];
    // Roman symbols, mathematical symbols and integers
    $string = preg_replace($beg.'<'.$end, '<span>&lt;</span>', $string);
    $string = preg_replace($beg.'>'.$end, '<span>&gt;</span>', $string);
    $string = preg_replace($beg.'\\\\equiv'.$end, '<span>&equiv;</span>', $string);
    $string = preg_replace($beg.'\\\\mathplus'.$end, '<span>+</span>', $string);
    $string = preg_replace($beg.'\\\\pm'.$end, '<span>&plusmn;</span>', $string);
    $string = preg_replace_callback(
      $beg.'\\{?([0-9a-zA-Z\\+\\-\\.,;: ]+)\\}?'.$end,
      function ($matches) use ($slant_symbols) {
        return '<span>' . $slant_symbols($matches[1]) . '</span>';
      },
      $string
    );
    // Greek symbols with alternative versions (reversed in HTML)
    $string = preg_replace($beg.'\\\\var(phi|epsilon)'.$end, '<i>&$1;</i>', $string);
    $string = preg_replace($beg.'\\\\(phi|epsilon)'.$end, '<i>&var$1;</i>', $string);
    // Greek symbols with alternative and upright versions
    $string = preg_replace($beg.'\\\\((?:var)?)('.$greek_pat.')'.$end, '<i>&$1$2;</i>', $string);
    $string = preg_replace($beg.'\\\\up('.$greek_pat.')'.$end, '<span>&$1;</span>', $string);
    // miscellaneous symbols
    $string = preg_replace($beg.'\\\\less'.$end, '<span>&lt;</span>', $string);
    $string = preg_replace($beg.'\\\\greater'.$end, '<span>&gt;</span>', $string);
    $string = preg_replace($beg.'\\\\leq'.$end, '<span>&leq;</span>', $string);
    $string = preg_replace($beg.'\\\\geq'.$end, '<span>&geq;</span>', $string);
    $string = preg_replace($beg.'\\\\infty'.$end, '<span>&infin;</span>', $string);
    $string = preg_replace($beg.'\\\\star'.$end, '<span>&#x22C6;</span>', $string);
    $string = preg_replace($beg.'(?:\\{\\})?\\^\\\\star'.$end, '<span><sup>&#x22C6;</sup></span>', $string);
    $string = preg_replace($beg.'([\\+\\-][0-9\\.]+|[0-9]*)(?:\\{\\})?\\^\\{?\\\\circ\\}?'.$end, '<span>$1&deg;</span>', $string);
    // prime and double prime symbols
    $string = preg_replace_callback(
      $beg.'\\{?([0-9a-zA-Z]*)\\}?(?:(\'{1,2})|\\^\\{?((?:\\\\prime){1,2})\\}?)'.$end,
      function ($matches) use ($slant_symbols) {
        return '<span>'
               . $slant_symbols($matches[1])
               . (($matches[2] === "'" or count($matches) >= 4 and $matches[3] === "\\prime") ? '&prime;' : '&Prime;')
               . '</span>';
      },
      $string
    );
    // subscripts and superscripts
    $string = preg_replace_callback(
      $beg.'\\{?([a-zA-Z]?)\\}?([_^])\\{?([0-9a-zA-Z\\+\\-\\.,;: ]+)\\}?'.$end,
      function ($matches) use ($slant_symbols) {
        return '<span>'
               . $slant_symbols($matches[1])
               . ($matches[2] == '_' ? '<sub>' : '<sup>')
               . $slant_symbols($matches[3])
               . ($matches[2] == '_' ? '</sub>' : '</sup>')
               . '</span>';
      },
      $string
    );
    // calligraphy
    $string = preg_replace($beg.'\\\\mathfrak(?: +|\\{)([a-zA-Z])\\}?'.$end, '<span>&$1fr;</span>', $string);
    $string = preg_replace($beg.'\\\\mathbb(?: +|\\{)([a-zA-Z])\\}?'.$end, '<span>&$1opf;</span>', $string);
    $string = preg_replace($beg.'\\\\mathscr(?: +|\\{)([a-zA-Z])\\}?'.$end, '<span>&$1scr;</span>', $string);
    $string = preg_replace_callback(
      $beg.'\\\\mathcal(?: +|\\{)([a-zA-Z])\\}?'.$end,
      function ($matches) {
        global $mathcal2utf8_array;
        return '<span>' . $mathcal2utf8_array[$matches[1]] . '</span>';
      },
      $string
    );
    // computer science
    $string = preg_replace($beg.'\\\\mathcal(?: +O| *\\{O\\}) *\\(([Nn])\\^\\{?([0-9]+)\\}?\\)'.$end, '<span>𝓞(<i>$1</i><sup>$2</sup>)</span>', $string);
    $string = preg_replace($beg.'\\\\mathcal(?: +O| *\\{O\\}) *\\(([Nn])\\)'.$end, '<span>𝓞(<i>$1</i>)</span>', $string);
    $string = preg_replace($beg.'\\\\mathcal(?: +O| *\\{O\\}) *\\(([0-9])\\)'.$end, '<span>𝓞($1)</span>', $string);
    $string = preg_replace($beg.'O *\\(([Nn])\\^\\{?([0-9]+)\\}?\\)'.$end, '<span><i>O</i>(<i>$1</i><sup>$2</sup>)</span>', $string);
    $string = preg_replace($beg.'O *\\(([Nn])\\)'.$end, '<span><i>O</i>(<i>$1</i>)</span>', $string);
    $string = preg_replace($beg.'O *\\(([0-9])\\)'.$end, '<span><i>O</i>($1)</span>', $string);
    // commit new string if substitution was successful
    if (strcmp($string, $result[4]) !== 0) {
      // replace hyphens by minus signs
      $string = str_replace("-", "&minus;", $string);
      // HTML nodes
      if ($result[1] == '\\(' or $result[1] == '$' or $result[1] == '\\ensuremath{') {
        $result[1] = '';
        $result[3] = '';
      } elseif ($result[1] == '\\[' or $result[1] == '$$') {
        $result[1] = '<div>';
        $result[3] = '</div>';
      }
      $result[4] = $string;
    }
  }
  unset($result);
}

/** Convert math environments to MathJax HTML nodes.
 *
 * @param[in]      string  $string     The BibTeX string.
 * @param[in,out]  array   $positions  Location of math environments.
 * @return void
 */
function math2mathjax($string, &$positions) {
  foreach ($positions as &$result) {
    if ($result[1] == '\\(' or $result[1] == '$' or $result[1] == '\\ensuremath{') {
      $result[1] = '<span class="math">\\(';
      $result[3] = '\\)</span>';
    } elseif ($result[1] == '\\[' or $result[1] == '$$') {
      $result[1] = '<div class="math">\\[';
      $result[3] = '\\]</div>';
    }
  }
  unset($result);
}

/**
 * Convert common LaTeX macros and escape sequences to HTML.
 *
 * Note: conversion is done on a "best effort" basis.
 * It is for example limited to macros that take at most one argument,
 * and whose argument doesn't contain a macro. Hence, this function should
 * typically be run last, after diacritics escape sequences and math formula
 * have already been processed.
 *
 * @param  string  $string  The LaTeX string containing macros.
 * @return string  The HTML version.
 */
function latex2html($string) {
  $greek_pat = '[Aa]lpha|[Bb]eta|[Gg]amma|[Dd]elta|[Ee]psilon|[Zz]eta|[Ee]ta|[Tt]heta|[Ii]ota|[Kk]appa|[Ll]ambda|[Mm]u|[Nn]u|[Xx]i|[Oo]micron|[Pp]i|[Rr]ho|[Ss]igma|[Tt]au|[Uu]psilon|[Pp]hi|[Cc]hi|[Pp]si|[Oo]mega';
  global $special2utf8_array;
  // special symbols
  foreach ($special2utf8_array as $macro => $substitute) {
    $string = preg_replace('/'.preg_quote($macro).'(?![a-zA-Z])/', $substitute, $string);
  }
  // Greek symbols
  $string = preg_replace('/\\\\text('.$greek_pat.')(?![a-zA-Z])/', '&$1;', $string);
  // superscripts and subscripts
  if (preg_match('/\\\\(?:textsuperscript|textsubscript)[^a-zA-Z]/', $string) === 1) {
    for ($iteration = 0; $iteration < 3; $iteration++) {
      $string = preg_replace('/\\\\textsuperscript\\{([^\\{\\}\\\\]+)\\}/', '<sup>$1</sup>', $string);
      $string = preg_replace('/\\\\textsubscript\\{([^\\{\\}\\\\]+)\\}/',   '<sub>$1</sub>', $string);
    }
  }
  // text formatting
  if (preg_match('/\\\\(?:emph|textit|textbf|it(?:shape)?|bf(?:series)?|underline)[^a-zA-Z]/', $string) === 1) {
    for ($iteration = 0; $iteration < 3; $iteration++) {
      $string = preg_replace('/\\\\emph\\{([^\\{\}\\\\]*)\\}/', '<em>$1</em>', $string);
      $string = preg_replace('/\\\\textit\\{([^\\{\}\\\\]*)\\}/', '<i>$1</i>', $string);
      $string = preg_replace('/\\\\textbf\\{([^\\{\}\\\\]*)\\}/', '<b>$1</b>', $string);
      $string = preg_replace('/\\\\underline\\{([^\\{\}\\\\]*)\\}/', '<u>$1</u>', $string);
      $string = preg_replace('/\\{\\\\it(?:shape)? ([^\\{\}\\\\]*)\\}/', '<i>$1</i>', $string);
      $string = preg_replace('/\\{\\\\bf(?:series)? ([^\\{\}\\\\]*)\\}/', '<b>$1</b>', $string);
    }
  }
  return $string;
}

/**
 * Strip unescaped curly braces.
 *
 * @param  string  $string  The LaTeX string.
 * @return string  The stripped string.
 */
function strip_curly_braces($string) {
  $string = preg_replace('/(\\\\[a-zA-Z]+)\\{/', '$1 ', $string);
  $string = preg_replace('/(?<=[^\\\\]|^)\\{/', '', $string);
  $string = preg_replace('/(?<=[^\\\\]|^)\\}/', '', $string);
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
  if ((($modes & ConversionModes::MathJax) != 0) and (($modes & ConversionModes::MathSimple) != 0)) {
    throw "Cannot convert LaTeX math environments to both HTML and MathJax";
  }
  if ($modes and (($modes & ConversionModes::Newlines) == 0)) {
    throw "Cannot process LaTeX without first substituting newlines";
  }
  if ($modes == 0) {
    return $value;
  }
  if ($modes & ConversionModes::Newlines) {
    $value = str_replace("\\\\", "\n", $value);
  }
  $token = null;
  $math_environments = [];
  if (preg_match('/\\$|\\\\[\\[\\(]|\\\\ensuremath\\{/', $value) === 1) {
    $math_environments = math_extractor($value);
    $value = protect_math($value, $token, $math_environments);
  }
  if ($modes & ConversionModes::Diacritics) {
    $value = diacritics2utf8($value);
  }
  if ($modes & ConversionModes::MathSimple) {
    math2html($value, $math_environments);
  }
  if ($modes & ConversionModes::MathJax) {
    math2mathjax($value, $math_environments);
  }
  if ($modes & ConversionModes::LaTeXMacros) {
    $value = latex2html($value);
  }
  if ($modes & ConversionModes::StripCurlyBraces) {
    $value = strip_curly_braces($value);
  }
  if (!is_null($token)) {
    $value = deprotect_math($value, $token, $math_environments);
  }
  return $value;
}

?>
