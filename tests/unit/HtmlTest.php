<?php


use Futape\Utility\Html\Html;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Futape\Utility\Html\Html
 */
class HtmlTest extends TestCase
{
    public function testPreformattedLeadingSpaces()
    {
        $this->assertEquals("&nbsp;Foo<br />\n&nbsp;Bar", Html::preformatted(" Foo\n Bar"));
    }

    public function testPreformattedTrailingSpaces()
    {
        $this->assertEquals("Foo&nbsp;<br />\nBar&nbsp;", Html::preformatted("Foo \nBar "));
    }

    public function testPreformattedSubsequentSpaces()
    {
        $this->assertEquals('Foo &nbsp; Bar', Html::preformatted('Foo   Bar'));
    }

    public function testPreformattedNewlines()
    {
        $this->assertEquals("Foo<br />\nBar", Html::preformatted("Foo\nBar"));
    }

    public function testPreformattedTabs()
    {
        $this->assertEquals('Foo&nbsp;&nbsp;&nbsp;&nbsp;Bar', Html::preformatted("Foo\tBar"));
        $this->assertEquals('Foo&nbsp;&nbsp;Bar', Html::preformatted("Foo\tBar", 2));
    }

    public function testPreformattedHtmlEscape()
    {
        $this->assertEquals('&lt;', Html::preformatted('<'));
    }

    public function testParsePlaintext()
    {
        $html = 'Foo <b>Bar</b> <i class="class">Baz</i> <plaintext>Bam';

        $this->assertEquals('Bam', Html::parsePlaintext($html));
        $this->assertEquals('Foo <b>Bar</b> <i class="class">Baz</i> ', $html);
    }

    /**
     * @dataProvider removeAttributesDataProvider
     *
     * @param string $input
     * @param string $expected
     */
    public function testRemoveAttributes(string $input, string $expected)
    {
        $this->assertEquals($expected, Html::removeAttributes($input));
    }

    public function removeAttributesDataProvider(): array
    {
        return [
            ['<input type="text" name="name" />', '<input>'],
            ['<input type="text" name=" /> <strong>A strong text.</strong>', '<input>'],
            ['<input type="text" id="name-input" name=" /> <label for="name-input">A strong text.</label>', '<input>'],
            [
                '<input type="text" id="name-input" name=" /> <label for="name-input>A strong text.</label>',
                '<input>A strong text.</label>'
            ],
            ['</label data-name="Carl">', '</label>'],
            ['<value="foo">', '<value="foo">'],
            ['< >', '<>'],
            [
                '<span <div style="color:red;">A red <strong>bold</strong> text.</div>',
                '<span>A red <strong>bold</strong> text.</div>',
            ],
            ['<em class="emphasized"', '<em>'],
            ['<input type="text" name=name>', '<input>'],
            ['<input type=text>', '<input>'],
            ['<input type="text" name=name value="foo">', '<input>']
        ];
    }

    /**
     * @dataProvider removeTagsDataProvider
     *
     * @param array $input
     * @param string $expected
     */
    public function testRemoveTags(array $input, string $expected)
    {
        $this->assertEquals($expected, Html::removeTags(...$input));
    }

    public function removeTagsDataProvider(): array
    {
        return [
            'Without HTML attributes' => [
                ['Foo <div>Bar</div> Baz', ['div']],
                'Foo  Baz'
            ],
            'With HTML attributes' => [
                ['Foo <div style="color: red;">Bar</div> Baz', ['div']],
                'Foo  Baz'
            ],
            'Multiple tags' => [
                ['Foo <div>Bar</div> Baz <span>Bam</span>', ['div', 'span']],
                'Foo  Baz '
            ],
            'Multiple tags, but remove only some' => [
                ['Foo <div>Bar</div> Baz <span>Bam</span>', ['div']],
                'Foo  Baz <span>Bam</span>'
            ],
            'Nested tags' => [
                ['Foo <div>Bar <span>Baz</span></div> Bam', ['div', 'span']],
                'Foo  Bam'
            ],
            'Nested tags, but remove only some' => [
                ['Foo <div>Bar <span>Baz</span></div> Bam', ['span']],
                'Foo <div>Bar </div> Bam'
            ]
        ];
    }
}
