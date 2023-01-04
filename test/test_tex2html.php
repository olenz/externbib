<?php
/**
 * Unit tests for utility functions that process escape sequences.
 *
 * @package    ExternBib
 * @author     Jean-Noël Grad
 * @copyright  2022 The Authors
 * @license    https://opensource.org/licenses/BSD-3-Clause New BSD License
 * @link       https://github.com/olenz/externbib
 */

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'tex2html.php');

function pretty_print_from_backtrace($level) {
  $bt = debug_backtrace();
  $filename = $bt[$level]['file'];
  $fileno = $bt[$level]['line'];
  return "$filename:$fileno";
}

function check_conversion($input, $expected, $callback) {
  $output = call_user_func($callback, $input);
  if (strcmp($output, $expected) !== 0) {
    $line = pretty_print_from_backtrace(1);
    echo "Failed conversion by $callback() at $line:\n";
    echo "  input:    \"$input\"\n";
    echo "  output:   \"$output\"\n";
    echo "  expected: \"$expected\"\n";
    exit(0);
  }
}

echo "Testing diacritics2utf8()\n";

check_conversion(
  "Str\\o m \\v s\\v{s} \\'{\\ae} ```a' and `b'''",
  "Strøm šš ǽ &ldquo;&lsquo;a&rsquo; and &lsquo;b&rsquo;&rdquo;",
  "diacritics2utf8"
);
check_conversion(
  '\\"a \\"A \\"e \\"E \\"i \\"I \\"o \\"O \\"r \\"R \\"u \\"U \\"y \\"Y',
  "ä Ä ë Ë ï Ï ö Ö r̈ R̈ ü Ü ÿ Ÿ",
  "diacritics2utf8"
);
check_conversion(
  "\\'a \\'A \\'c \\'C \\'e \\'E \\'i \\'I \\'l \\'L \\'n \\'N \\'o \\'O \\'r \\'R \\'s \\'S \\'u \\'U \\'y \\'Y \\'z \\'Z \\'\\ae \\'\\AE \\'\\o \\'\\O",
  "á Á ć Ć é É í Í ĺ Ĺ ń Ń ó Ó ŕ Ŕ ś Ś ú Ú ý Ý ź Ź ǽǼǿǾ",
  "diacritics2utf8"
);
check_conversion(
  "\\^a \\^A \\^c \\^C \\^e \\^E \\^g \\^G \\^h \\^H \\^i \\^I \\^j \\^J \\^o \\^O \\^r \\^R \\^s \\^S \\^u \\^U \\^w \\^W \\^x \\^X \\^y \\^Y",
  "â Â ĉ Ĉ ê Ê ĝ Ĝ ĥ Ĥ î Î ĵ Ĵ ô Ô r̂ R̂ ŝ Ŝ û Û ŵ Ŵ x̂ X̂ ŷ Ŷ",
  "diacritics2utf8"
);
check_conversion(
  "\\`a \\`A \\`e \\`E \\`i \\`I \\`o \\`O \\`u \\`U \\`y \\`Y",
  "à À è È ì Ì ò Ò ù Ù ỳ Ỳ",
  "diacritics2utf8"
);
check_conversion(
  "\\~a \\~A \\~e \\~E \\~i \\~I \\~n \\~N \\~o \\~O \\~u \\~U \\~v \\~V \\~y \\~Y",
  "ã Ã ẽ Ẽ ĩ Ĩ ñ Ñ õ Õ ũ Ũ ṽ Ṽ ỹ Ỹ",
  "diacritics2utf8"
);
check_conversion(
  "\\=a \\=A \\=e \\=E \\=I \\=i \\=o \\=O \\=u \\=U \\=v \\=V \\=x \\=X \\=y \\=Y \\=\\ae \\=\\AE",
  "ā Ā ē Ē Ī ī ō Ō ū Ū v̄ V̄ x̄ X̄ ȳ Ȳ ǣǢ",
  "diacritics2utf8"
);
check_conversion(
  "\\.a \\.A \\.b \\.B \\.c \\.C \\.d \\.D \\.e \\.E \\.f \\.F \\.g \\.G \\.h \\.H \\.I \\.k \\.K \\.l \\.L \\.m \\.M \\.n \\.N \\.o \\.O \\.p \\.P \\.q \\.Q \\.r \\.R \\.s \\.S \\.t \\.T \\.u \\.U \\.v \\.V \\.w \\.W \\.x \\.X \\.y \\.Y \\.z \\.Z",
  "ȧ Ȧ ḃ Ḃ ċ Ċ ḋ Ḋ ė Ė ḟ Ḟ ġ Ġ ḣ Ḣ İ k̇ K̇ l̇ L̇ ṁ Ṁ ṅ Ṅ ȯ Ȯ ṗ Ṗ q̇ Q̇ ṙ Ṙ ṡ Ṡ ṫ Ṫ u̇ U̇ v̇ V̇ ẇ Ẇ ẋ Ẋ ẏ Ẏ ż Ż",
  "diacritics2utf8"
);
check_conversion(
  "\\c c \\c C \\c d \\c D \\c e \\c E \\c h \\c H \\c g \\c G \\c k \\c K \\c l \\c L \\c n \\c N \\c r \\c R \\c s \\c S \\c t \\c T",
  "ç Ç ḑ Ḑ ȩ Ȩ ḩ Ḩ ģ Ģ ķ Ķ ļ Ļ ņ Ņ ŗ Ŗ ş Ş ţ Ţ",
  "diacritics2utf8"
);
check_conversion(
  "\\d a \\d A \\d b \\d B \\d c \\d C \\d d \\d D \\d e \\d E \\d f \\d F \\d g \\d G \\d h \\d H \\d i \\d I \\d j \\d J \\d k \\d K \\d l \\d L \\d m \\d M \\d n \\d N \\d o \\d O \\d p \\d P \\d q \\d Q \\d r \\d R \\d s \\d S \\d t \\d T \\d u \\d U \\d v \\d V \\d w \\d W \\d x \\d X \\d y \\d Y \\d z \\d Z",
  "ạ Ạ ḅ Ḅ c̣ C̣ ḍ Ḍ ẹ Ẹ f̣ F̣ g̣ G̣ ḥ Ḥ ị Ị j̣ J̣ ḳ Ḳ ḷ Ḷ ṃ Ṃ ṇ Ṇ ọ Ọ p̣ P̣ q̣ Q̣ ṛ Ṛ ṣ Ṣ ṭ Ṭ ụ Ụ ṿ Ṿ ẉ Ẉ x̣ X̣ ỵ Ỵ ẓ Ẓ",
  "diacritics2utf8"
);
check_conversion(
  "\\k a \\k A \\k e \\k E \\k i \\k I \\k n \\k N \\k o \\k O \\k u \\k U \\k s \\k S \\k z \\k Z",
  "ą Ą ę Ę į Į n̨ N̨ ǫ Ǫ ų Ų s̨ S̨ z̨ Z̨",
  "diacritics2utf8"
);
check_conversion(
  "\\r a \\r A \\r u \\r U \\r w \\r y",
  "å Å ů Ů ẘ ẙ",
  "diacritics2utf8"
);
check_conversion(
  "\\u a \\u A \\u e \\u E \\u g \\u G \\u i \\u I \\u o \\u O \\u s \\u S \\u u \\u U \\u y \\u Y",
  "ă Ă ĕ Ĕ ğ Ğ ĭ Ĭ ŏ Ŏ s̆ S̆ ŭ Ŭ y̆ Y̆",
  "diacritics2utf8"
);
check_conversion(
  "\\v a \\v A \\v c \\v C \\v d \\v D \\v e \\v E \\v g \\v G \\v h \\v H \\v i \\v I \\v j \\v k \\v K \\v l \\v L \\v n \\v N \\v o \\v O \\v r \\v R \\v s \\v S \\v t \\v T \\v u \\v U \\v z \\v Z",
  "ǎ Ǎ č Č ď Ď ě Ě ǧ Ǧ ȟ Ȟ ǐ Ǐ ǰ ǩ Ǩ ľ Ľ ň Ň ǒ Ǒ ř Ř š Š ť Ť ǔ Ǔ ž Ž",
  "diacritics2utf8"
);
check_conversion(
  "\\H a \\H A \\H e \\H E \\H i \\H I \\H m \\H M \\H o \\H O \\H u \\H U \\H y \\H Y",
  "a̋ A̋ e̋ E̋ i̋ I̋ m̋ M̋ ő Ő ű Ű ӳ Y̋",
  "diacritics2utf8"
);
check_conversion(
  "{\\aa} {\\AA} {\\ae} {\\AE} {\\oe} {\\OE} {\\dh} {\\DH} {\\dj} {\\DJ} {\\ij} {\\IJ} {\\ng} {\\NG} {\\ss} {\\SS} {\\th} {\\TH} {\\i} {\\l} {\\L} {\\o} {\\O} {\\S} {\\P} --- -- \\- \\, \\% \\$ \\_ \\# ~ \\&",
  "å Å æ Æ œ Œ ð Ð đ Đ &ijlig; &IJlig; ŋ Ŋ ß ẞ Þ þ ı ł Ł ø Ø § ¶ &mdash; &ndash;    % &#36; _ #   &amp;",
  "diacritics2utf8"
);

echo "Testing strip_curly_braces()\n";

function test_strip_curly_braces($string) {
  return convert_latex_string($string, ConversionModes::Newlines | ConversionModes::StripCurlyBraces);
}

check_conversion(
  '\\emph{abc} {\\bfseries \\{text}',
  '\\emph abc \\bfseries \\{text',
  'strip_curly_braces'
);

check_conversion(
  '\\emph{abc} {\\bfseries \\{text}',
  '\\emph abc \\bfseries \\{text',
  'test_strip_curly_braces'
);

check_conversion(
  '\\emph{abc} $\\mathcal{A}$ \\({}\\)',
  '\\emph abc $\\mathcal A$ \\(\\)',
  'strip_curly_braces'
);

check_conversion(
  '\\emph{abc} $\\mathcal{A}$ \\({}\\)',
  '\\emph abc $\\mathcal{A}$ \\({}\\)',
  'test_strip_curly_braces'
);

echo "The testsuite was successful.\n";
exit(0);
?>
