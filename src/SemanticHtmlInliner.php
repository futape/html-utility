<?php


namespace Futape\Utility\Html;


class SemanticHtmlInliner extends HtmlInliner
{
    /** @var string[] */
    protected $tagsToRemove = [
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
        'figure',
        'nav',
        'menu',
        'legend'
    ];

    /**
     * @param string $html
     */
    public function __construct(string $html)
    {
        parent::__construct($html);


        var_dump(parent::getTagsToRemove());
        var_dump($this->getTagsToRemove());

        $this->setTagsToRemove(array_merge(parent::getTagsToRemove(), $this->getTagsToRemove()));
        var_dump($this->getTagsToRemove());

    }
}
