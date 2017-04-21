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

namespace PhpCsFixer\Tests\Fixer\NamespaceNotation;

use PhpCsFixer\Test\AbstractFixerTestCase;

/**
 * @author Graham Campbell <graham@alt-three.com>
 *
 * @internal
 *
 * @covers \PhpCsFixer\AbstractLinesBeforeNamespaceFixer
 * @covers \PhpCsFixer\Fixer\NamespaceNotation\NoBlankLinesBeforeNamespaceFixer
 */
final class NoBlankLinesBeforeNamespaceFixerTest extends AbstractFixerTestCase
{
    /**
     * @dataProvider provideExamples
     *
     * @param string      $expected
     * @param null|string $input
     */
    public function testFix($expected, $input = null)
    {
        $this->doTest($expected, $input);
    }

    /**
     * @return array
     */
    public function provideExamples()
    {
        return [
            ["<?php\nnamespace X;"],
            ["<?php\nnamespace X;", "<?php\n\n\n\nnamespace X;"],
            ["<?php\r\nnamespace X;"],
            ["<?php\r\nnamespace X;", "<?php\r\n\r\n\r\n\r\nnamespace X;"],
            ["<?php\n\nnamespace\\Sub\\Foo::bar();"],
        ];
    }

    public function testFixExampleWithComment()
    {
        $expected = <<<'EOF'
<?php

/*
 * This file is part of the PHP CS utility.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace PhpCsFixer\Fixer\Contrib;
EOF;

        $input = <<<'EOF'
<?php

/*
 * This file is part of the PHP CS utility.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Fixer\Contrib;
EOF;

        $this->doTest($expected, $input);
    }
}
