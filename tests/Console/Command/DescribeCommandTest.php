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

namespace PhpCsFixer\Tests\Console\Command;

use PhpCsFixer\Console\Application;
use PhpCsFixer\Console\Command\DescribeCommand;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerOption;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerFactory;
use Prophecy\Argument;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @internal
 *
 * @covers \PhpCsFixer\Console\Command\DescribeCommand
 */
final class DescribeCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testExecuteOutput()
    {
        $expected = <<<'EOT'
Description of Foo/bar rule.
Fixes stuff.
Replaces bad stuff with good stuff.

Fixer is configurable using following options:
* things (bool): enables fixing things as well; defaults to false

Fixing examples:
 * Example #1.
   ---------- begin diff ----------
   --- Original
   +++ New
   @@ @@
   -<?php echo 'bad stuff and bad thing';
   +<?php echo 'good stuff and bad thing';
%spaces%
   ----------- end diff -----------

 * Example #2. Fixing with configuration: ['things' => true].
   ---------- begin diff ----------
   --- Original
   +++ New
   @@ @@
   -<?php echo 'bad stuff and bad thing';
   +<?php echo 'good stuff and good thing';
%spaces%
   ----------- end diff -----------


EOT;

        $this->assertSame(
            str_replace(['%spaces%', "\n"], ['   ', PHP_EOL], $expected),
            $this->execute()->getDisplay()
        );
    }

    public function testExecuteStatusCode()
    {
        $this->assertSame(0, $this->execute()->getStatusCode());
    }

    public function testExecuteWithUnknownName()
    {
        $application = new Application();
        $application->add(new DescribeCommand(new FixerFactory()));

        $command = $application->find('describe');

        $commandTester = new CommandTester($command);

        $this->setExpectedException(\InvalidArgumentException::class, 'Rule Foo/bar not found.');
        $commandTester->execute([
            'command' => $command->getName(),
            'name' => 'Foo/bar',
        ]);
    }

    public function testExecuteWithoutName()
    {
        $application = new Application();
        $application->add(new DescribeCommand(new FixerFactory()));

        $command = $application->find('describe');

        $commandTester = new CommandTester($command);

        $this->setExpectedExceptionRegExp(\RuntimeException::class, '/^Not enough arguments( \(missing: "name"\))?\.$/');
        $commandTester->execute([
            'command' => $command->getName(),
        ]);
    }

    /**
     * @return CommandTester
     */
    private function execute()
    {
        $fixer = $this->prophesize();
        $fixer->willImplement(\PhpCsFixer\Fixer\DefinedFixerInterface::class);
        $fixer->willImplement(\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface::class);

        $fixer->getName()->willReturn('Foo/bar');
        $fixer->getPriority()->willReturn(0);
        $fixer->isRisky()->willReturn(false);
        $fixer->getConfigurationDefinition()->willReturn(new FixerConfigurationResolver([
            new FixerOption('things', 'Enables fixing things as well.', false, false, ['bool']),
        ]));
        $fixer->getDefinition()->willReturn(new FixerDefinition(
            'Fixes stuff.',
            [
                new CodeSample(
                    '<?php echo \'bad stuff and bad thing\';'
                ),
                new CodeSample(
                    '<?php echo \'bad stuff and bad thing\';',
                    ['things' => true]
                ),
            ],
            'Replaces bad stuff with good stuff.',
            'Can break stuff.'
        ));

        $things = false;
        $fixer->configure(null)->will(function () use (&$things) {
            $things = false;
        });
        $fixer->configure(['things' => true])->will(function () use (&$things) {
            $things = true;
        });
        $fixer->fix(
            Argument::type(\SplFileInfo::class),
            Argument::type(\PhpCsFixer\Tokenizer\Tokens::class)
        )->will(function (array $arguments) use (&$things) {
            $arguments[1][3]->setContent($things ? '\'good stuff and good thing\'' : '\'good stuff and bad thing\'');
        });

        $fixerFactory = new FixerFactory();
        $fixerFactory->registerFixer($fixer->reveal(), true);

        $application = new Application();
        $application->add(new DescribeCommand($fixerFactory));

        $command = $application->find('describe');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                'name' => 'Foo/bar',
            ],
            [
                'decorated' => false,
            ]
        );

        return $commandTester;
    }
}
