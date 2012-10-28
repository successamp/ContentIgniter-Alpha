<?php
function phone_text_to_phone_number($text)
{
    if (!empty($text)) {
        $replace = array(
            '+',
            '(',
            ')',
            '.',
            '-',
            ' ',
            '_',
            '<',
            '>'
        );
        $text    = str_replace($replace, "", $text);
        $first   = (int)$text[0];
        if ($first === 1) {
            $text = substr($text, 1);
        }
        $area  = substr($text, 0, 3);
        $part1 = substr($text, 3, 3);
        $part2 = substr($text, 6, 4);
        if ((is_numeric($text) && strlen($text) == 10) || (is_numeric($area) && strlen($area) == 3 && is_numeric($part1) && strlen($part1) == 3 && is_numeric($part2) && strlen($part2) == 4)
        ) {
            return "$area-$part1-$part2";
        }
    }
    return $text;
}

function build_css_link($css, $static_url)
{
    $url_base        = $css['static'] == 1 ? $static_url : '';
    $url_type_folder = $css['js_folder'] == 1 ? 'js' : 'css';
    $url_library     = $css['library_name'];
    $url_major       = $css['version_major'];
    $url_minor       = $css['version_major'];
    $url_build       = $css['version_build'];
    $url_file        = $css['file_name'];
    return $url_base . '/' . $url_type_folder . '/' . $url_library . '/' . $url_major . '/' . $url_minor . '/' . $url_build . '/' . $url_file;
}
