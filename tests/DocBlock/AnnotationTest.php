<?php

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Tests\DocBlock;

use PhpCsFixer\DocBlock\Annotation;
use PhpCsFixer\DocBlock\DocBlock;
use PhpCsFixer\DocBlock\Line;

/**
 * @author Graham Campbell <graham@alt-three.com>
 *
 * @internal
 *
 * @covers \PhpCsFixer\DocBlock\Annotation
 */
final class AnnotationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * This represents the content an entire docblock.
     *
     * @var string
     */
    private static $sample = '/**
     * Test docblock.
     *
     * @param string $hello
     * @param bool $test Description
     *        extends over many lines
     *
     * @param adkjbadjasbdand $asdnjkasd
     *
     * @throws \Exception asdnjkasd
     *
     * asdasdasdasdasdasdasdasd
     * kasdkasdkbasdasdasdjhbasdhbasjdbjasbdjhb
     *
     * @return void
     */';

    /**
     * This represents the content of each annotation.
     *
     * @var string[]
     */
    private static $content = [
        "     * @param string \$hello\n",
        "     * @param bool \$test Description\n     *        extends over many lines\n",
        "     * @param adkjbadjasbdand \$asdnjkasd\n",
        "     * @throws \Exception asdnjkasd\n     *\n     * asdasdasdasdasdasdasdasd\n     * kasdkasdkbasdasdasdjhbasdhbasjdbjasbdjhb\n",
        "     * @return void\n",
    ];

    /**
     * This represents the start indexes of each annotation.
     *
     * @var int[]
     */
    private static $start = [3, 4, 7, 9, 14];

    /**
     * This represents the start indexes of each annotation.
     *
     * @var int[]
     */
    private static $end = [3, 5, 7, 12, 14];

    /**
     * This represents the tag type of each annotation.
     *
     * @var string[]
     */
    private static $tags = ['param', 'param', 'param', 'throws', 'return'];

    /**
     * @param int    $index
     * @param string $content
     *
     * @dataProvider provideContent
     */
    public function testGetContent($index, $content)
    {
        $doc = new DocBlock(self::$sample);
        $annotation = $doc->getAnnotation($index);

        $this->assertSame($content, $annotation->getContent());
        $this->assertSame($content, (string) $annotation);
    }

    public function provideContent()
    {
        $cases = [];

        foreach (self::$content as $index => $content) {
            $cases[] = [$index, $content];
        }

        return $cases;
    }

    /**
     * @param int $index
     * @param int $start
     *
     * @dataProvider provideStartCases
     */
    public function testStart($index, $start)
    {
        $doc = new DocBlock(self::$sample);
        $annotation = $doc->getAnnotation($index);

        $this->assertSame($start, $annotation->getStart());
    }

    public function provideStartCases()
    {
        $cases = [];

        foreach (self::$start as $index => $start) {
            $cases[] = [$index, $start];
        }

        return $cases;
    }

    /**
     * @param int $index
     * @param int $end
     *
     * @dataProvider provideEndCases
     */
    public function testEnd($index, $end)
    {
        $doc = new DocBlock(self::$sample);
        $annotation = $doc->getAnnotation($index);

        $this->assertSame($end, $annotation->getEnd());
    }

    public function provideEndCases()
    {
        $cases = [];

        foreach (self::$end as $index => $end) {
            $cases[] = [$index, $end];
        }

        return $cases;
    }

    /**
     * @param int    $index
     * @param string $tag
     *
     * @dataProvider provideTags
     */
    public function testGetTag($index, $tag)
    {
        $doc = new DocBlock(self::$sample);
        $annotation = $doc->getAnnotation($index);

        $this->assertSame($tag, $annotation->getTag()->getName());
    }

    public function provideTags()
    {
        $cases = [];

        foreach (self::$tags as $index => $tag) {
            $cases[] = [$index, $tag];
        }

        return $cases;
    }

    /**
     * @param int $index
     * @param int $start
     * @param int $end
     *
     * @dataProvider provideRemoveCases
     */
    public function testRemove($index, $start, $end)
    {
        $doc = new DocBlock(self::$sample);
        $annotation = $doc->getAnnotation($index);

        $annotation->remove();
        $this->assertSame('', $annotation->getContent());
        $this->assertSame('', $doc->getLine($start)->getContent());
        $this->assertSame('', $doc->getLine($end)->getContent());
    }

    public function provideRemoveCases()
    {
        $cases = [];

        foreach (self::$start as $index => $start) {
            $cases[] = [$index, $start, self::$end[$index]];
        }

        return $cases;
    }

    /**
     * @param string[] $expected
     * @param string[] $new
     * @param string   $input
     * @param string   $output
     *
     * @dataProvider provideTypesCases
     */
    public function testTypes($expected, $new, $input, $output)
    {
        $line = new Line($input);
        $tag = new Annotation([$line]);

        $this->assertSame($expected, $tag->getTypes());

        $tag->setTypes($new);

        $this->assertSame($new, $tag->getTypes());

        $this->assertSame($output, $line->getContent());
    }

    public function provideTypesCases()
    {
        return [
            [['Foo', 'null'], ['Bar[]'], '     * @param Foo|null $foo', '     * @param Bar[] $foo'],
            [['false'], ['bool'], '*   @return            false', '*   @return            bool'],
            [['RUNTIMEEEEeXCEPTION'], [\Throwable::class], "\t@throws\t  \t RUNTIMEEEEeXCEPTION\t\t\t\t\t\t\t\n\n\n", "\t@throws\t  \t Throwable\t\t\t\t\t\t\t\n\n\n"],
            [['string'], ['string', 'null'], ' * @method string getString()', ' * @method string|null getString()'],
        ];
    }

    public function testGetTypesOnBadTag()
    {
        $this->setExpectedException(
            \RuntimeException::class,
            'This tag does not support types'
        );

        $tag = new Annotation([new Line(' * @deprecated since 1.2')]);

        $tag->getTypes();
    }

    public function testSetTypesOnBadTag()
    {
        $this->setExpectedException(
            \RuntimeException::class,
            'This tag does not support types'
        );

        $tag = new Annotation([new Line(' * @author Chuck Norris')]);

        $tag->setTypes(['string']);
    }

    public function testGetTagsWithTypes()
    {
        $tags = Annotation::getTagsWithTypes();
        $this->assertInternalType('array', $tags);
        foreach ($tags as $tag) {
            $this->assertInternalType('string', $tag);
            $this->assertNotEmpty($tag);
        }
    }
}
