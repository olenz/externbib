<?php
/**
 * Unit tests for utility functions that process escape sequences,
 * LaTeX macros and mathematical formula.
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

echo "Testing latex2html()\n";

check_conversion(
  "\\textsuperscript{1\\}} \\textsuperscript{1 \\^a 2} \\textsubscript{1\\}} \\textsubscript{1 \\^a 2}",
  "\\textsuperscript{1\\}} \\textsuperscript{1 \\^a 2} \\textsubscript{1\\}} \\textsubscript{1 \\^a 2}",
  "latex2html"
);
check_conversion(
  "\\textsuperscript{1 $2$ 3} \\textsubscript{1 $2$ 3}",
  "<sup>1 $2$ 3</sup> <sub>1 $2$ 3</sub>",
  "latex2html"
);
check_conversion(
  "\\textdegree \\textperiodcentered \\textminus \\textgreater \\textless",
  "&deg; &dot; &minus; &gt; &lt;",
  "latex2html"
);
check_conversion(
  "\\textbackslash \\textdollar \\textbullet \\textendash \\textemdash \\textbraceleft \\textbraceright \\textparagraph \\textquestiondown \\textexclamdown \\textasciicircum \\textasciitilde \\textregistered \\texttrademark \\copyright \\pounds \\textbar \\dag \\ddag \\textdagger \\textdaggerdbl \\textalpha \\textBeta",
  "&#92; &#36; &#149; &ndash; &mdash; &#123; &#125; &para; &iquest; &iexcl; &#94; &#126; &reg; &trade; &copy; &pound; &vert; &dagger; &Dagger; &dagger; &Dagger; &alpha; &Beta;",
  "latex2html"
);
check_conversion(
  "\\textquotesingle \\textquotedbl \\textquoteleft \\textquoteright \\textquotedblleft \\textquotedblright \\guillemetleft \\guillemetright \\guilsinglleft \\guilsinglright \\quotesinglbase \\quotedblbase",
  "&#39; &quot; &lsquo; &rsquo; &ldquo; &rdquo; &laquo; &raquo; &lsaquo; &rsaquo; &sbquo; &bdquo;",
  "latex2html"
);
check_conversion(
  "\\textsuperscript{B2\\textsuperscript{3}} \\textsubscript{B2\\textsubscript{3}} \\textsubscript{B2\\textsuperscript{3}}",
  "<sup>B2<sup>3</sup></sup> <sub>B2<sub>3</sub></sub> <sub>B2<sup>3</sup></sub>",
  "latex2html"
);
check_conversion(
  "\\underline{A} \\textbf{B} \\textit{C} \\emph{D} {\\bf B} {\\bfseries B} {\\it C} {\\itshape C}",
  "<u>A</u> <b>B</b> <i>C</i> <em>D</em> <b>B</b> <b>B</b> <i>C</i> <i>C</i>",
  "latex2html"
);
check_conversion(
  "\\underline{A\\textbf{B\\textit{C\\underline{A\\textbf{B\\textit{}}}}}}",
  "<u>A<b>B<i>C<u>A<b>B<i></i></b></u></i></b></u>",
  "latex2html"
);

echo "Testing math2html()\n";

function test_math2html($string) {
  return convert_latex_string($string, ConversionModes::Newlines | ConversionModes::MathSimple);
}

check_conversion(
  "$\\varphi$ $\\varepsilon$ $\\phi$ $\\epsilon$ $\\upAlpha$ $\\beta$",
  "<i>&phi;</i> <i>&epsilon;</i> <i>&varphi;</i> <i>&varepsilon;</i> <span>&Alpha;</span> <i>&beta;</i>",
  "test_math2html"
);
check_conversion(
  "\\ensuremath{\\varphi} \\ensuremath{\\varepsilon} \\ensuremath{\\phi} \\ensuremath{\\epsilon} \\ensuremath{\\upAlpha} \\ensuremath{\\beta}",
  "<i>&phi;</i> <i>&epsilon;</i> <i>&varphi;</i> <i>&varepsilon;</i> <span>&Alpha;</span> <i>&beta;</i>",
  "test_math2html"
);
check_conversion(
  "\\(\\varphi\\) \\(\\varepsilon\\) \\(\\phi\\) \\(\\epsilon\\) \\(\\upAlpha\\) \\(\\beta\\)",
  "<i>&phi;</i> <i>&epsilon;</i> <i>&varphi;</i> <i>&varepsilon;</i> <span>&Alpha;</span> <i>&beta;</i>",
  "test_math2html"
);
check_conversion(
  "$<$ $\\less$ $>$ $\\greater$ $\\leq$ $\\geq$ $\\infty$ $\\equiv$ $\\mathplus$ $\\pm$ $\\star$ $^\\star$",
  "<span>&lt;</span> <span>&lt;</span> <span>&gt;</span> <span>&gt;</span> <span>&leq;</span> <span>&geq;</span> <span>&infin;</span> <span>&equiv;</span> <span>+</span> <span>&plusmn;</span> <span>&#x22C6;</span> <span><sup>&#x22C6;</sup></span>",
  "test_math2html"
);
check_conversion(
  "\\(+1.2 - ax, 1+2;: b\\)",
  "<span>+1.2 &minus; <i>ax</i>, 1+2;: <i>b</i></span>",
  "test_math2html"
);
check_conversion(
  "\$100^\\circ\$C $+1.0^{\\circ}\$C \$-1{}^{\\circ}\$C \${}^{\\circ}\$ \$^{\\circ}\$",
  "<span>100&deg;</span>C <span>+1.0&deg;</span>C <span>&minus;1&deg;</span>C <span>&deg;</span> <span>&deg;</span>",
  "test_math2html"
);
check_conversion(
  "Li\$^{+}\$ Mg\${}^{2+}\$ CO\$_{2}\$ CO\$_2\$ [Cu(H\$_2\$O)\\({}_{6}\\)]\\ensuremath{^{2+}}",
  "Li<span><sup>+</sup></span> Mg<span><sup>2+</sup></span> CO<span><sub>2</sub></span> CO<span><sub>2</sub></span> [Cu(H<span><sub>2</sub></span>O)<span><sub>6</sub></span>]<span><sup>2+</sup></span>",
  "test_math2html"
);
check_conversion(
  "\$H^{+}\$ \$A^{-}\$ \$H^+\$ \$A^-\$",
  "<span><i>H</i><sup>+</sup></span> <span><i>A</i><sup>&minus;</sup></span> <span><i>H</i><sup>+</sup></span> <span><i>A</i><sup>&minus;</sup></span>",
  "test_math2html"
);
check_conversion(
  "\${h}^{+}\$ \${a}^{-}\$ \${h}^+\$ \${a}^-\$",
  "<span><i>h</i><sup>+</sup></span> <span><i>a</i><sup>&minus;</sup></span> <span><i>h</i><sup>+</sup></span> <span><i>a</i><sup>&minus;</sup></span>",
  "test_math2html"
);
check_conversion(
  "L\${}_0\$\$C^{-0.4}\$\\({C}_{1-x}\\)O\$_{3-h+2.5 - 0.1X}\$",
  "L<span><sub>0</sub></span><span><i>C</i><sup>&minus;0.4</sup></span><span><i>C</i><sub>1&minus;<i>x</i></sub></span>O<span><sub>3&minus;<i>h</i>+2.5 &minus; 0.1<i>X</i></sub></span>",
  "test_math2html"
);
check_conversion(
  "\$\\mathcal A\$ \$\\mathcal{a}\$ \$\\mathfrak B\$ \$\\mathfrak{b}\$ \$\\mathbb C\$ \$\\mathbb{c}\$ \$\\mathscr D\$ \$\\mathscr{d}\$",
  "<span>𝓐</span> <span>𝓪</span> <span>&Bfr;</span> <span>&bfr;</span> <span>&Copf;</span> <span>&copf;</span> <span>&Dscr;</span> <span>&dscr;</span>",
  "test_math2html"
);
check_conversion(
  "\$O(N^2)\$ \$O(n)\$ \$O(1)\$",
  "<span><i>O</i>(<i>N</i><sup>2</sup>)</span> <span><i>O</i>(<i>n</i>)</span> <span><i>O</i>(1)</span>",
  "test_math2html"
);
check_conversion(
  "\$\\mathcal O (N^12)\$ \$\\mathcal O (n)\$ \$\\mathcal O (1)\$",
  "<span>𝓞(<i>N</i><sup>12</sup>)</span> <span>𝓞(<i>n</i>)</span> <span>𝓞(1)</span>",
  "test_math2html"
);
check_conversion(
  "4-\$n\$-alkyl-(\$N\$,\\(N\\)]-\$2\$,\$n\$-iodo",
  "4-<span><i>n</i></span>-alkyl-(<span><i>N</i></span>,<span><i>N</i></span>]-<span>2</span>,<span><i>n</i></span>-iodo",
  "test_math2html"
);
check_conversion(
  '$1\'$ $2\'\'$ $3\'\'\'$ ${1}^\\prime$ ${b}^{\\prime\\prime}$',
  '<span>1&prime;</span> <span>2&Prime;</span> $3\'\'\'$ <span>1&prime;</span> <span><i>b</i>&Prime;</span>',
  "test_math2html"
);
check_conversion(
  '1$\'$ 2${}\'\'$ 1$^\\prime$ 2${}^{\\prime\\prime}$',
  '1<span>&prime;</span> 2<span>&Prime;</span> 1<span>&prime;</span> 2<span>&Prime;</span>',
  "test_math2html"
);
check_conversion(
  '\\emph{1} $n$$v$ \\$$1$ \\(1$ - 1\\) \\[5$\\] $$5$\(6\)$$',
  '\\emph{1} <span><i>n</i></span><span><i>v</i></span> \\$<span>1</span> \\(1$ - 1\\) \\[5$\\] $$5$\\(6\\)$$',
  "test_math2html"
);

echo "Testing math2mathjax()\n";

function test_math2mathjax($string) {
  return convert_latex_string($string, ConversionModes::Newlines | ConversionModes::MathJax);
}

check_conversion(
  '$n$ $$v$$',
  '<span class="math">\\(n\\)</span> <div class="math">\\[v\\]</div>',
  "test_math2mathjax"
);
check_conversion(
  '\\(n\\) \\[v\\]',
  '<span class="math">\\(n\\)</span> <div class="math">\\[v\\]</div>',
  "test_math2mathjax"
);
check_conversion(
  '\\ensuremath{\\{{A\\}}}',
  '<span class="math">\\(\\{{A\\}}\\)</span>',
  "test_math2mathjax"
);
check_conversion(
  '\\emph{1} $n$$v$ \\$$1$ \\(1$ - 1\\) \\[5$\\] $$5$\(6\)$$',
  '\\emph{1} <span class="math">\\(n\\)</span><span class="math">\\(v\\)</span> \\$<span class="math">\\(1\\)</span> <span class="math">\\(1$ - 1\\)</span> <div class="math">\\[5$\\]</div> <div class="math">\\[5$\\(6\\)\\]</div>',
  "test_math2mathjax"
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
