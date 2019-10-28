<?php


namespace Futape\Utility\Html;

use Futape\Utility\String\Strings;

class HtmlInliner
{
    /** @var string */
    protected $html;

    /** @var int */
    protected $options = ENT_COMPAT | ENT_HTML401;

    /** @var string[] */
    protected $tagsToRemove = [
        'table',
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
        'frameset'
        //'meter'=>true, //don't do this because <meter> requires a content and its appearance depends on the render engine (some browsers simply display the text content). moreover it doesn't allow userinput.
        //'progress'=>true //same as <meter>, but it doesn't require (but allows) a content. <progress> doesn't allow userinput, too.
    ] + Html::HIDDEN_BLOCK_TAGS;

    /**
     * @param string $html
     */
    public function __construct(string $html)
    {
        $this->setHtml($html);
    }

    /**
     * @return string
     */
    public function getHtml(): string
    {
        return $this->html;
    }

    /**
     * @param string $html
     * @return self
     */
    public function setHtml(string $html): self
    {
        $this->html = $html;

        return $this;
    }

    /**
     * @return int
     */
    public function getOptions(): int
    {
        return $this->options;
    }

    /**
     * @param int $options
     * @return self
     */
    public function setOptions(int $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getTagsToRemove(): array
    {
        return $this->tagsToRemove;
    }

    /**
     * Define the *block* tags which should be removed entirely together with their contents
     *
     * @param string[] $tagsToRemove
     * @return self
     */
    public function setTagsToRemove(array $tagsToRemove): self
    {
        $this->tagsToRemove = array_unique(array_map('mb_strtolower', $tagsToRemove));

        return $this;
    }

    /**
     * @return string
     */
    public function render(): string
    {
        $inline = $this->getHtml();
        $plain = Html::parsePlaintext($inline);

        $inline = Html::removeAttributes($inline);
        $inline = $this->styleLists($inline);
        $inline = $this->styleQuotes($inline);
        $inline = $this->styleCaptions($inline);
        $inline = $this->wrapTags($inline, $this->getBlockTags());
        $inline = strip_tags(
            $inline,
            $this->getBlockTagsToRemove() > 0 ? '<' . implode('><', $this->getBlockTagsToRemove()) . '>' : null
        );
        $inline = Html::removeTags($inline, $this->getBlockTagsToRemove());
        $inline = html_entity_decode($inline, $this->getOptions());
        if ($plain !== null) {
            $inline .= $plain;
        }
        $inline = Strings::inline($inline);
        $inline = $this->collapseSpaces($inline);

        return $inline;
    }

    /**
     * @return array
     */
    protected function getBlockTags(): array
    {
        $blockTags = [];
        $removeBlockTags = array_flip($this->getBlockTagsToRemove());

        foreach (Html::BLOCK_TAGS as $key => $val) {
            if (is_array($val)) {
                $blockTags[] = $key;
                if (!isset($removeBlockTags[$key])) {
                    foreach ($val as $subVal) {
                        if (!isset($removeBlockTags[$subVal])) {
                            $blockTags[] = $subVal;
                        }
                    }
                }
            } else {
                $blockTags[] = $val;
            }
        }

        return $blockTags;
    }

    protected function getBlockTagsToRemove(): array
    {
        $removeBlockTags = [];

        foreach ($this->getTagsToRemove() as $tag) {
            $removeBlockTags[] = $tag;
            if (isset(Html::BLOCK_TAGS[$tag]) && is_array(Html::BLOCK_TAGS[$tag])) {
                array_splice($removeBlockTags, count($removeBlockTags), 0, Html::BLOCK_TAGS[$tag]);
            }
        }

        return array_unique($removeBlockTags);
    }

    /**
     * Styles <ul>, <ol> and <dl> lists
     *
     * Appends a comma to the content of <li> and <dd> tags and appends a colon to the content of a <dt> tag.
     * Beware that this work only on tags without any attributes.
     *
     * @param string $html
     * @return string
     */
    protected function styleLists(string $html): string
    {
        $html = preg_replace('/\s*' . $this->getTagPattern(['li', 'dd'], true) . '/i', ',$0', $html);
        $html = preg_replace('/\s*' . $this->getTagPattern('dt', true) . '/i', ':$0', $html);

        return $html;
    }

    /**
     * Styles <blockquote> and <q> quotes
     *
     * Wraps the contents of <blockquote> and <q> tags into quotes.
     * Beware that this work only on tags without any attributes.
     *
     * @param string $html
     * @return string
     */
    protected function styleQuotes(string $html): string
    {
        // use &quot;, not ", because of the possibility that the tag exists inside of an attribute-value an could end
        // the attribute-value by " - HTML elements' attrbutes are removed above, this is no longer an argument.
        // thus simply use `"` instead of `&quot;`
        $html = preg_replace('/' . $this->getTagPattern(['blockquote', 'q'], false) . '\s*/i', '$0"', $html);

        // again &quot;, not ". see line above - no longer relevant
        $html = preg_replace('/\s*' . $this->getTagPattern(['blockquote', 'q'], true) . '/i', '"$0', $html);

        return $html;
    }

    /**
     * Styles <figcaption> and <caption> captions
     *
     * Wraps the contents of <figcaption> and <caption> tags into brackets.
     * Beware that this work only on tags without any attributes.
     *
     * @param string $html
     * @return string
     */
    protected function styleCaptions(string $html): string
    {
        $html = preg_replace('/' . $this->getTagPattern(['figcaption', 'caption'], false) . '\s*/i', '$0(', $html);
        $html = preg_replace('/\s*' . $this->getTagPattern(['figcaption', 'caption'], true) . '/i', ')$0', $html);

        return $html;
    }

    /**
     * Prepends a space to each opening and closing tag with one of the specified tag names
     *
     * @param string $html
     * @param string|string[] $tagName
     * @return string
     */
    protected function wrapTags(string $html, $tagName): string
    {
        return preg_replace('/' . $this->getTagPattern($tagName) . '/i', ' $0', $html);
    }

    /**
     * @param string|string[] $tagName
     * @param bool|null $closing `false` if opening tag, `true` if closing tag and `null` if both should be matched
     * @param string $regexDelimiter
     * @return string
     */
    protected function getTagPattern($tagName, ?bool $closing = null, $regexDelimiter = '/'): string
    {
        $pattern = '<';

        if ($closing === true || $closing === null) {
            $pattern .= '\/';

            if ($closing === null) {
                $pattern .= '?';
            }
        }

        $tagName = (array)$tagName;
        array_walk(
            $tagName,
            function (&$val) use ($regexDelimiter) {
                $val = preg_quote($val, $regexDelimiter);
            }
        );

        if (count($tagName) > 1) {
            $pattern .= '(?:' . implode('|', $tagName) . ')';
        } else {
            $pattern .= $tagName[0] ?? '';
        }

        $pattern .= '>';

        return $pattern;
    }

    /**
     * Removes whitespaces from the beginning and end of a string and replace subsequent spaces with one space
     *
     * @param string $value
     * @return string
     */
    protected function collapseSpaces(string $value): string
    {
        return trim(preg_replace('/ +/', ' ', $value));
    }
}
