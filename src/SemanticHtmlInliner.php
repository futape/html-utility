<?php


namespace Futape\Utility\Html;


class SemanticHtmlInliner extends HtmlInliner
{
    /**
     * @param string $html
     */
    public function __construct(string $html)
    {
        parent::__construct($html);

        $this->setTagsToRemove(
            array_merge(
                $this->getTagsToRemove(),
                [
                    'h1',
                    'h2',
                    'h3',
                    'h4',
                    'h5',
                    'h6',
                    'header',
                    'footer',
                    'hgroup',
                    'aside',

                    // Figures
                    'figure',
                    'figcaption',

                    'nav',
                    'menu',
                    'legend'
                ]
            )
        );
    }
}
