<?php


use Futape\Utility\Html\SemanticHtmlInliner;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Futape\Utility\Html\SemanticHtmlInliner
 */
class SemanticHtmlInlinerTest extends TestCase
{
    /**
     * @dataProvider renderDataProvider
     *
     * @param array $input
     * @param string $expected
     */
    public function testRender(array $input, string $expected)
    {
        $htmlInliner = new SemanticHtmlInliner(...$input);

        $this->assertEquals($expected, $htmlInliner->render());
    }

    public function renderDataProvider(): array
    {
        $data = [];

        foreach ((new SemanticHtmlInliner(''))->getTagsToRemove() as $tag) {
            $data[mb_strtoupper($tag) . ' tags removed entirely'] = [
                ['<' . $tag . '>Foo</' . $tag . '>'],
                ''
            ];
        }

        return $data;
    }
}
