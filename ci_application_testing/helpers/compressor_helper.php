<?php
/**
 * Class Minify_CSS_Compressor
 *
 * @package Minify
 */

/**
 * Compress CSS
 *
 * This is a heavy regex-based removal of whitespace, unnecessary
 * comments and tokens, and some CSS value minimization, where practical.
 * Many steps have been taken to avoid breaking comment-based hacks,
 * including the ie5/mac filter (and its inversion), but expect tricky
 * hacks involving comment tokens in 'content' value strings to break
 * minimization badly. A test suite is available.
 *
 * @package Minify
 * @author  Stephen Clay <steve@mrclay.org>
 * @author  http://code.google.com/u/1stvamp/ (Issue 64 patch)
 */
class Minify_CSS_Compressor
{

    /**
     * Minify a CSS string
     *
     * @param string $css
     *
     * @param array  $options (currently ignored)
     *
     * @return string
     */
    public static function process($css, $options = array())
    {
        $obj = new Minify_CSS_Compressor($options);
        return $obj->_process($css);
    }

    /**
     * @var array options
     */
    protected $_options = NULL;

    /**
     * @var bool Are we "in" a hack?
     *
     * I.e. are some browsers targetted until the next comment?
     */
    protected $_inHack = FALSE;


    /**
     * Constructor
     *
     * @param array $options (currently ignored)
     *
     * @return null
     */
    private function __construct($options)
    {
        $this->_options = $options;
    }

    /**
     * Minify a CSS string
     *
     * @param string $css
     *
     * @return string
     */
    protected function _process($css)
    {
        $css = str_replace("\r\n", "\n", $css);

        // preserve empty comment after '>'
        // http://www.webdevout.net/css-hacks#in_css-selectors
        $css = preg_replace('@>/\\*\\s*\\*/@', '>/*keep*/', $css);

        // preserve empty comment between property and value
        // http://css-discuss.incutio.com/?page=BoxModelHack
        $css = preg_replace('@/\\*\\s*\\*/\\s*:@', '/*keep*/:', $css);
        $css = preg_replace('@:\\s*/\\*\\s*\\*/@', ':/*keep*/', $css);

        // apply callback to all valid comments (and strip out surrounding ws
        $css = preg_replace_callback('@\\s*/\\*([\\s\\S]*?)\\*/\\s*@', array(
                                                                            $this,
                                                                            '_commentCB'
                                                                       ), $css);

        // remove ws around { } and last semicolon in declaration block
        $css = preg_replace('/\\s*{\\s*/', '{', $css);
        $css = preg_replace('/;?\\s*}\\s*/', '}', $css);

        // remove ws surrounding semicolons
        $css = preg_replace('/\\s*;\\s*/', ';', $css);

        // remove ws around urls
        $css = preg_replace('/
                url\\(      # url(
                \\s*
                ([^\\)]+?)  # 1 = the URL (really just a bunch of non right parenthesis)
                \\s*
                \\)         # )
            /x', 'url($1)', $css);

        // remove ws between rules and colons
        $css = preg_replace('/
                \\s*
                ([{;])              # 1 = beginning of block or rule separator
                \\s*
                ([\\*_]?[\\w\\-]+)  # 2 = property (and maybe IE filter)
                \\s*
                :
                \\s*
                (\\b|[#\'"-])        # 3 = first character of a value
            /x', '$1$2:$3', $css);

        // remove ws in selectors
        $css = preg_replace_callback('/
                (?:              # non-capture
                    \\s*
                    [^~>+,\\s]+  # selector part
                    \\s*
                    [,>+~]       # combinators
                )+
                \\s*
                [^~>+,\\s]+      # selector part
                {                # open declaration block
            /x', array(
                      $this,
                      '_selectorsCB'
                 ), $css);

        // minimize hex colors
        $css = preg_replace('/([^=])#([a-f\\d])\\2([a-f\\d])\\3([a-f\\d])\\4([\\s;\\}])/i', '$1#$2$3$4$5', $css);

        // remove spaces between font families
        $css = preg_replace_callback('/font-family:([^;}]+)([;}])/', array(
                                                                          $this,
                                                                          '_fontFamilyCB'
                                                                     ), $css);

        $css = preg_replace('/@import\\s+url/', '@import url', $css);

        // replace any ws involving newlines with a single newline
        $css = preg_replace('/[ \\t]*\\n+\\s*/', "\n", $css);

        // separate common descendent selectors w/ newlines (to limit line lengths)
        $css = preg_replace('/([\\w#\\.\\*]+)\\s+([\\w#\\.\\*]+){/', "$1\n$2{", $css);

        // Use newline after 1st numeric value (to limit line lengths).
        $css = preg_replace('/
            ((?:padding|margin|border|outline):\\d+(?:px|em)?) # 1 = prop : 1st numeric value
            \\s+
            /x', "$1\n", $css);

        // prevent triggering IE6 bug: http://www.crankygeek.com/ie6pebug/
        $css = preg_replace('/:first-l(etter|ine)\\{/', ':first-l$1 {', $css);

        return trim($css);
    }

    /**
     * Replace what looks like a set of selectors
     *
     * @param array $m regex matches
     *
     * @return string
     */
    protected function _selectorsCB($m)
    {
        // remove ws around the combinators
        return preg_replace('/\\s*([,>+~])\\s*/', '$1', $m[0]);
    }

    /**
     * Process a comment and return a replacement
     *
     * @param array $m regex matches
     *
     * @return string
     */
    protected function _commentCB($m)
    {
        $hasSurroundingWs = (trim($m[0]) !== $m[1]);
        $m                = $m[1];
        // $m is the comment content w/o the surrounding tokens,
        // but the return value will replace the entire comment.
        if ($m === 'keep') {
            return '/**/';
        }
        if ($m === '" "') {
            // component of http://tantek.com/CSS/Examples/midpass.html
            return '/*" "*/';
        }
        if (preg_match('@";\\}\\s*\\}/\\*\\s+@', $m)) {
            // component of http://tantek.com/CSS/Examples/midpass.html
            return '/*";}}/* */';
        }
        if ($this->_inHack) {
            // inversion: feeding only to one browser
            if (preg_match('@
                    ^/               # comment started like /*/
                    \\s*
                    (\\S[\\s\\S]+?)  # has at least some non-ws content
                    \\s*
                    /\\*             # ends like /*/ or /**/
                @x', $m, $n)
            ) {
                // end hack mode after this comment, but preserve the hack and comment content
                $this->_inHack = FALSE;
                return "/*/{$n[1]}/**/";
            }
        }
        if (substr($m, -1) === '\\') { // comment ends like \*/
            // begin hack mode and preserve hack
            $this->_inHack = TRUE;
            return '/*\\*/';
        }
        if ($m !== '' && $m[0] === '/') { // comment looks like /*/ foo */
            // begin hack mode and preserve hack
            $this->_inHack = TRUE;
            return '/*/*/';
        }
        if ($this->_inHack) {
            // a regular comment ends hack mode but should be preserved
            $this->_inHack = FALSE;
            return '/**/';
        }
        // Issue 107: if there's any surrounding whitespace, it may be important, so 
        // replace the comment with a single space
        return $hasSurroundingWs // remove all other comments
            ? ' ' : '';
    }

    /**
     * Process a font-family listing and return a replacement
     *
     * @param array $m regex matches
     *
     * @return string
     */
    protected function _fontFamilyCB($m)
    {
        $m[1] = preg_replace('/
                \\s*
                (
                    "[^"]+"      # 1 = family in double qutoes
                    |\'[^\']+\'  # or 1 = family in single quotes
                    |[\\w\\-]+   # or 1 = unquoted family
                )
                \\s*
            /x', '$1', $m[1]);
        return 'font-family:' . $m[1] . $m[2];
    }
}

/**
 * Class Minify_HTML
 *
 * @package Minify
 */

/**
 * Compress HTML
 *
 * This is a heavy regex-based removal of whitespace, unnecessary comments and
 * tokens. IE conditional comments are preserved. There are also options to have
 * STYLE and SCRIPT blocks compressed by callback functions.
 *
 * A test suite is available.
 *
 * @package Minify
 * @author  Stephen Clay <steve@mrclay.org>
 */
class Minify_HTML
{

    /**
     * "Minify" an HTML page
     *
     * @param string $html
     *
     * @param array  $options
     *
     * 'cssMinifier' : (optional) callback function to process content of STYLE
     * elements.
     *
     * 'jsMinifier' : (optional) callback function to process content of SCRIPT
     * elements. Note: the type attribute is ignored.
     *
     * 'xhtml' : (optional boolean) should content be treated as XHTML1.0? If
     * unset, minify will sniff for an XHTML doctype.
     *
     * @return string
     */
    public static function minify($html, $options = array())
    {
        $min = new Minify_HTML($html, $options);
        return $min->process();
    }


    /**
     * Create a minifier object
     *
     * @param string $html
     *
     * @param array  $options
     *
     * 'cssMinifier' : (optional) callback function to process content of STYLE
     * elements.
     *
     * 'jsMinifier' : (optional) callback function to process content of SCRIPT
     * elements. Note: the type attribute is ignored.
     *
     * 'xhtml' : (optional boolean) should content be treated as XHTML1.0? If
     * unset, minify will sniff for an XHTML doctype.
     *
     * @return null
     */
    public function __construct($html, $options = array())
    {
        $this->_html = str_replace("\r\n", "\n", trim($html));
        if (isset($options['xhtml'])) {
            $this->_isXhtml = (bool)$options['xhtml'];
        }
        if (isset($options['cssMinifier'])) {
            $this->_cssMinifier = $options['cssMinifier'];
        }
        if (isset($options['jsMinifier'])) {
            $this->_jsMinifier = $options['jsMinifier'];
        }
    }


    /**
     * Minify the markeup given in the constructor
     *
     * @return string
     */
    public function process()
    {
        if ($this->_isXhtml === NULL) {
            $this->_isXhtml = (FALSE !== strpos($this->_html, '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML'));
        }

        $this->_replacementHash = 'MINIFYHTML' . md5($_SERVER['REQUEST_TIME']);
        $this->_placeholders    = array();

        // replace SCRIPTs (and minify) with placeholders
        $this->_html = preg_replace_callback('/(\\s*)(<script\\b[^>]*?>)([\\s\\S]*?)<\\/script>(\\s*)/i', array(
                                                                                                               $this,
                                                                                                               '_removeScriptCB'
                                                                                                          ), $this->_html);

        // replace STYLEs (and minify) with placeholders
        $this->_html = preg_replace_callback('/\\s*(<style\\b[^>]*?>)([\\s\\S]*?)<\\/style>\\s*/i', array(
                                                                                                         $this,
                                                                                                         '_removeStyleCB'
                                                                                                    ), $this->_html);

        // remove HTML comments (not containing IE conditional comments).
        $this->_html = preg_replace_callback('/<!--([\\s\\S]*?)-->/', array(
                                                                           $this,
                                                                           '_commentCB'
                                                                      ), $this->_html);

        // replace PREs with placeholders
        $this->_html = preg_replace_callback('/\\s*(<pre\\b[^>]*?>[\\s\\S]*?<\\/pre>)\\s*/i', array(
                                                                                                   $this,
                                                                                                   '_removePreCB'
                                                                                              ), $this->_html);

        // replace TEXTAREAs with placeholders
        $this->_html = preg_replace_callback('/\\s*(<textarea\\b[^>]*?>[\\s\\S]*?<\\/textarea>)\\s*/i', array(
                                                                                                             $this,
                                                                                                             '_removeTextareaCB'
                                                                                                        ), $this->_html);

        // trim each line.
        // @todo take into account attribute values that span multiple lines.
        $this->_html = preg_replace('/^\\s+|\\s+$/m', '', $this->_html);

        // remove ws around block/undisplayed elements
        $this->_html = preg_replace('/\\s+(<\\/?(?:area|base(?:font)?|blockquote|body' . '|caption|center|cite|col(?:group)?|dd|dir|div|dl|dt|fieldset|form' . '|frame(?:set)?|h[1-6]|head|hr|html|legend|li|link|map|menu|meta' . '|ol|opt(?:group|ion)|p|param|t(?:able|body|head|d|h||r|foot|itle)' . '|ul)\\b[^>]*>)/i', '$1', $this->_html);

        // remove ws outside of all elements
        $this->_html = preg_replace_callback('/>([^<]+)</', array(
                                                                 $this,
                                                                 '_outsideTagCB'
                                                            ), $this->_html);

        // use newlines before 1st attribute in open tags (to limit line lengths)
        $this->_html = preg_replace('/(<[a-z\\-]+)\\s+([^>]+>)/i', "$1\n$2", $this->_html);

        // fill placeholders
        $this->_html = str_replace(array_keys($this->_placeholders), array_values($this->_placeholders), $this->_html);
        return $this->_html;
    }

    protected function _commentCB($m)
    {
        return (0 === strpos($m[1], '[') || FALSE !== strpos($m[1], '<![')) ? $m[0] : '';
    }

    protected function _reservePlace($content)
    {
        $placeholder                       = '%' . $this->_replacementHash . count($this->_placeholders) . '%';
        $this->_placeholders[$placeholder] = $content;
        return $placeholder;
    }

    protected $_isXhtml = NULL;
    protected $_replacementHash = NULL;
    protected $_placeholders = array();
    protected $_cssMinifier = NULL;
    protected $_jsMinifier = NULL;

    protected function _outsideTagCB($m)
    {
        return '>' . preg_replace('/^\\s+|\\s+$/', ' ', $m[1]) . '<';
    }

    protected function _removePreCB($m)
    {
        return $this->_reservePlace($m[1]);
    }

    protected function _removeTextareaCB($m)
    {
        return $this->_reservePlace($m[1]);
    }

    protected function _removeStyleCB($m)
    {
        $openStyle = $m[1];
        $css       = $m[2];
        // remove HTML comments
        $css = preg_replace('/(?:^\\s*<!--|-->\\s*$)/', '', $css);

        // remove CDATA section markers
        $css = $this->_removeCdata($css);

        // minify
        $minifier = $this->_cssMinifier ? $this->_cssMinifier : 'trim';
        $css      = call_user_func($minifier, $css);

        return $this->_reservePlace($this->_needsCdata($css) ? "{$openStyle}/*<![CDATA[*/{$css}/*]]>*/</style>" : "{$openStyle}{$css}</style>");
    }

    protected function _removeScriptCB($m)
    {
        $openScript = $m[2];
        $js         = $m[3];

        // whitespace surrounding? preserve at least one space
        $ws1 = ($m[1] === '') ? '' : ' ';
        $ws2 = ($m[4] === '') ? '' : ' ';

        // remove HTML comments (and ending "//" if present)
        $js = preg_replace('/(?:^\\s*<!--\\s*|\\s*(?:\\/\\/)?\\s*-->\\s*$)/', '', $js);

        // remove CDATA section markers
        $js = $this->_removeCdata($js);

        // minify
        $minifier = $this->_jsMinifier ? $this->_jsMinifier : 'trim';
        $js       = call_user_func($minifier, $js);

        return $this->_reservePlace($this->_needsCdata($js) ? "{$ws1}{$openScript}/*<![CDATA[*/{$js}/*]]>*/</script>{$ws2}" : "{$ws1}{$openScript}{$js}</script>{$ws2}");
    }

    protected function _removeCdata($str)
    {
        return (FALSE !== strpos($str, '<![CDATA[')) ? str_replace(array(
                                                                        '<![CDATA[',
                                                                        ']]>'
                                                                   ), '', $str) : $str;
    }

    protected function _needsCdata($str)
    {
        return ($this->_isXhtml && preg_match('/(?:[<&]|\\-\\-|\\]\\]>)/', $str));
    }
}

