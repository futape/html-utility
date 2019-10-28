<?php


namespace Futape\Utility\Html;


abstract class Html
{
    const BLOCK_TAGS = [
        'aside',
        'article',
        'section',
        'figure' => [
            'figcaption'
        ],
        'footer',
        'header',
        'hgroup',
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'address',
        'p',
        'blockquote',
        'div',
        'dialog',
        'dir',
        'ul' => [
            'li'
        ],
        'ol' => [
            'li'
        ],
        'dl' => [
            'dt',
            'dd'
        ],
        'body',
        'center',
        'details',
        'summary',
        'form',
        'fieldset',
        'html',
        'pre',
        'plaintext',
        'main',
        'legend',
        //'meter'=>false, //? - it's an inline element and it must not be empty. thus the author should have ensured that it is separated (by spaces) from its surrounding content (regardless of whether it's rendered as a kind of progressbar or whether its content is printed literally) (moreover it's not an input elem) -> remove from this array
        //'progress'=>false, //same as <meter>,
        //'label'=>false,
        'nav',
        'menu',
        'table' => [
            'td',
            'th',
            'tr',
            'thead',
            'tbody',
            'tfoot',
            'caption',
        ],
        'applet',
        'audio',
        'video',
        'canvas',
        'button',
        'command',
        'textarea',
        'select',
        'object',
        'iframe',
        'frameset',
        //'meter'=>true, //don't do this because <meter> requires a content and its appearance depends on the render engine (some browsers simply display the text content). moreover it doesn't allow userinput.
        //'progress'=>true //same as <meter>, but it doesn't require (but allows) a content. <progress> doesn't allow userinput, too.
    ];

//    const VOID_TAGS = [
//        'embed',
//        'frame',
//        'input',
//        'img',
//        'keygen',
//        'hr',
//        'br'
//    ];

    const HIDDEN_BLOCK_TAGS = [
        'noframes',
        'noscript',
        'style',
        'script',
        'head',
        'map',
        'datalist',
        'title',
        'option',
        'colgroup',
        'optgroup',
    ];

//    const HIDDEN_VOID_TAGS = [
//        'area',
//        'col',
//        'param',
//        'font',
//        'base',
//        'basefont',
//        'link',
//        'meta',
//        'source',
//        'track'
//    ];

    const TAG_NAME_PATTERN = '[^>\s]*';

    const ATTRIBUTES_PATTERN = '(?:\s(?:[^"\'>]*(["\'])(?:(?!\%d).)*\%1$d)*[^"\'>]*(?:[^>].*)?)?';

    /**
     * Get HTML for a preformatted string
     *
     * Replaces 2 subsequent spaces with a combination of a non-breaking and a simple space,
     * tabs with 4 subsequent non-breaking spaces,
     * spaces that follow on a newline with a non-breaking space,
     * newline characters with `<br />` and
     * converts special HTML characters to HTML entities.
     * In the rendered HTML, any whitespace is shown and doesn't collapse.
     * However, a line may still wrap if too long.
     *
     * @see https://php.net/manual/en/function.htmlspecialchars.php
     *
     * @param string $plain
     * @param int $tabSize
     * @param int $options Passed to htmlspecialchars()
     * @return string
     */
    public static function preformatted(
        string $plain,
        int $tabSize = 4,
        int $options = ENT_COMPAT | ENT_HTML401
    ): string {
        $html = htmlspecialchars($plain, $options);
        $html = preg_replace('/^ | $/m', '&nbsp;', $html);
        $html = str_replace('  ', ' &nbsp;', $html);
        $html = str_replace("\t", str_repeat('&nbsp;', $tabSize), $html);
        $html = nl2br($html);

        return $html;
    }

    /**
     * Parses a <plaintext> tag from passed HTML
     *
     * If found, the tag and any content following it is stripped off the passed variable
     * and the content following the tag is returned.
     *
     * @param string $html
     * @return string|null
     */
    public static function parsePlaintext(string &$html): ?string
    {
        $matches = [];

        if (preg_match(
            '/^((?:<' . self::TAG_NAME_PATTERN . sprintf(self::ATTRIBUTES_PATTERN, 2) . '>|[^<])*)' .
                '<plaintext' . sprintf(self::ATTRIBUTES_PATTERN, 3) . '>/is',
            $html,
            $matches
        ) === 1) {
            $plain = mb_substr($html, mb_strlen($matches[0]));
            $html = mb_substr($html, 0, mb_strlen($matches[1]));

            return $plain;
        }

        return null;
    }

    /**
     * Removes all HTML tags' attributes
     *
     * Also makes sure that every tag has a closing `>` and replaces `/>` through a simple `>`.
     *
     * @param string $html
     * @return string
     */
    public static function removeAttributes(string $html): string
    {
        return preg_replace(
            '/(<' . self::TAG_NAME_PATTERN . ')'. sprintf(self::ATTRIBUTES_PATTERN, 2) . '>?/s',
            '$1>',
            $html
        );
    }

    /**
     * @param string $html
     * @param array $tagNames
     * @return string
     */
    public static function removeTags(string $html, array $tagNames): string
    {
        $matches = [];

        preg_match_all(
            '/<(\/?)(' . implode(
                '|',
                array_map(
                    function ($val) {
                        return preg_quote($val, '/');
                    },
                    $tagNames
                )
            ) . ')' . sprintf(self::ATTRIBUTES_PATTERN, 3) . '>?/is',
            $html,
            $matches,
            PREG_SET_ORDER | PREG_OFFSET_CAPTURE
        );

        $depths = [];
        $pointer = 0;
        $alteredHtml = '';

        // Loop through all occurrences of the tags (opening and closing)
        foreach ($matches as $tag) {
            // Lowercase the tag name.
            $tagName = mb_strtolower($tag[2][0]);

            // If the current tag name doesn't exist in $tree, add it and set it to 0
            if (!isset($depths[$tagName])) {
                $depths[$tagName] = 0;
            }

            $depth = array_sum($depths);

            // Check if currently no tag of another tag name is entered.
            // This means that the current tag is either of the same type
            // (tag name) as the one already entered, or no tag has been entered yet
            // (i.e. currently on root level)
            if ($depths[$tagName] == $depth) {
                // If currently no tag is entered (currently on root level)
                // append the part of the content starting after the
                // last discovered closing tag that entered the root level
                // again ($pointer) and ending before the current tag
                if ($depth == 0) {
                    $alteredHtml .= substr($html, $pointer, $tag[0][1] - $pointer);
                }

                // If the current tag is a closing one,
                // decrease the (depth) level of the tag type (tag name) by 1,
                // otherwise increase it by 1.
                // Manage the level never to be less than 0
                $depths[$tagName] = max($depths[$tagName] + ($tag[1][0] != '' ? -1 : 1), 0);

                // Save the position of the current tag's ending to $pointer.
                // Actually only necessary for the closing tag that is entering the
                // root level again
                $pointer = $tag[0][1] + strlen($tag[0][0]);
            }
        }

        // If the last entered level is the root level,
        // append the text following the last discovered
        // closing tag to the produced content.
        if (array_sum($depths) == 0) {
            $alteredHtml .= substr($html, $pointer);
        }

        return $alteredHtml;
    }
}
