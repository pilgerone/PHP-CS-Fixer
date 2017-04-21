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

namespace PhpCsFixer\Tests\Fixer\ReturnNotation;

use PhpCsFixer\Test\AbstractFixerTestCase;

/**
 * @author Graham Campbell <graham@alt-three.com>
 *
 * @internal
 *
 * @covers \PhpCsFixer\Fixer\ReturnNotation\SimplifiedNullReturnFixer
 */
final class SimplifiedNullReturnFixerTest extends AbstractFixerTestCase
{
    /**
     * @param string      $expected
     * @param null|string $input
     *
     * @dataProvider provideExamples
     */
    public function testFix($expected, $input = null)
    {
        $this->doTest($expected, $input);
    }

    public function provideExamples()
    {
        return [
            // check correct statements aren't changed
            ['<?php return  ;'],
            ['<?php return \'null\';'],
            ['<?php return false;'],
            ['<?php return (false );'],
            ['<?php return null === foo();'],
            ['<?php return array() == null ;'],

            // check we modified those that can be changed
            ['<?php return;', '<?php return null;'],
            ['<?php return;', '<?php return (null);'],
            ['<?php return;', '<?php return ( null    );'],
            ['<?php return;', '<?php return ( (( null)));'],
            ['<?php return /* hello */;', '<?php return /* hello */ null  ;'],
            ['<?php return;', '<?php return NULL;'],
            ['<?php return;', "<?php return\n(\nnull\n)\n;"],
        ];
    }
}
