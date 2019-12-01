<?php


use Futape\Utility\Html\HtmlInliner;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Futape\Utility\Html\HtmlInliner
 */
class HtmlInlinerTest extends TestCase
{
    /**
     * @dataProvider renderDataProvider
     *
     * @param array $input
     * @param string $expected
     */
    public function testRender(array $input, string $expected)
    {
        $htmlInliner = new HtmlInliner(...$input);

        $this->assertEquals($expected, $htmlInliner->render());
    }

    public function renderDataProvider(): array
    {
        $data = [
            'Plaintext parsed' => [
                ['Foo <plaintext>Bar <b>Baz</b>'],
                'Foo Bar <b>Baz</b>'
            ],
            'Unordered lists styled' => [
                ['<ul type="square"><li>One</li><li>Two</li></ul>'],
                'One, Two,'
            ],
            'Ordered lists styled' => [
                ['<ol><li>One</li><li>Two</li></ol>'],
                'One, Two,'
            ],
            'Description lists styled' => [
                ['<dl><dt>futape/html-utility</dt><dd>A package for working with HTML content in PHP</dd></dl>'],
                'futape/html-utility: A package for working with HTML content in PHP,'
            ],
            'Blockquotes styled' => [
                ['<blockquote>Bacon ipsum dolor amet burgdoggen ground round andouille</blockquote>'],
                '"Bacon ipsum dolor amet burgdoggen ground round andouille"'
            ],
            'Inline quotes styled' => [
                ['<q>Bacon ipsum dolor amet burgdoggen ground round andouille</q>'],
                '"Bacon ipsum dolor amet burgdoggen ground round andouille"'
            ],
            'Captions styled' => [
                ['<figure><p>Bacon ipsum dolor amet burgdoggen ground round andouille</p><figcaption>baconipsum.com</figcaption></figure>'],
                'Bacon ipsum dolor amet burgdoggen ground round andouille (baconipsum.com)'
            ],
            'Void tags removed' => [
                ['Foo <hr /> Bar'],
                'Foo Bar'
            ],
            'HTML entities decoded' => [
                ['&auml;'],
                utf8_encode('ä')
            ],
            'Content inlined' => [
                ["Foo\nBar\tBaz"],
                'Foo Bar Baz'
            ],
            'Spaces collapsed' => [
                ['Foo   Bar Baz  Bam'],
                'Foo Bar Baz Bam'
            ],
            'Render fixture' => [
                [file_get_contents(dirname(__DIR__) . '/fixtures/markup.html')],
                'A markup file This is just a file containing HTML markup. A list of very important stuff: One, Two, ' .
                    'And a list of definitions: WWW: World Wide Web, HTTP: Hypertext Transfer Protocol,'
            ]
        ];

        foreach ((new HtmlInliner(''))->getTagsToRemove() as $tag) {
            $data[mb_strtoupper($tag) . ' tags removed entirely'] = [
                ['<' . $tag . '>Foo</' . $tag . '>'],
                ''
            ];
        }

        return $data;
    }
}
