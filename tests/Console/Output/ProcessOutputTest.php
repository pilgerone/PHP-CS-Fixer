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

namespace PhpCsFixer\Tests\Console\Output;

use PhpCsFixer\Console\Output\ProcessOutput;
use PhpCsFixer\FixerFileProcessedEvent;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @internal
 *
 * @covers \PhpCsFixer\Console\Output\ProcessOutput
 */
final class ProcessOutputTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array  $statuses
     * @param string $expectedOutput
     *
     * @dataProvider getProcessProgressOutputCases
     */
    public function testProcessProgressOutput(array $statuses, $expectedOutput)
    {
        $processOutput = new ProcessOutput(
            $output = new BufferedOutput(),
            $this->prophesize(\Symfony\Component\EventDispatcher\EventDispatcher::class)->reveal(),
            null
        );

        $this->foreachStatus($statuses, function ($status) use ($processOutput) {
            $processOutput->onFixerFileProcessed(new FixerFileProcessedEvent($status));
        });

        $this->assertSame($expectedOutput, $output->fetch());
    }

    public function getProcessProgressOutputCases()
    {
        return [
            [
                [
                    [FixerFileProcessedEvent::STATUS_NO_CHANGES, 4],
                ],
                '....',
            ],
            [
                [
                    [FixerFileProcessedEvent::STATUS_NO_CHANGES],
                    [FixerFileProcessedEvent::STATUS_FIXED],
                    [FixerFileProcessedEvent::STATUS_NO_CHANGES, 4],
                ],
                '.F....',
            ],
            [
                [
                    [FixerFileProcessedEvent::STATUS_NO_CHANGES, 65],
                ],
                '.................................................................',
            ],
            [
                [
                    [FixerFileProcessedEvent::STATUS_NO_CHANGES, 81],
                ],
                '.................................................................................',
            ],
            [
                [
                    [FixerFileProcessedEvent::STATUS_NO_CHANGES, 19],
                    [FixerFileProcessedEvent::STATUS_EXCEPTION],
                    [FixerFileProcessedEvent::STATUS_NO_CHANGES, 6],
                    [FixerFileProcessedEvent::STATUS_LINT],
                    [FixerFileProcessedEvent::STATUS_FIXED, 3],
                    [FixerFileProcessedEvent::STATUS_NO_CHANGES, 67],
                    [FixerFileProcessedEvent::STATUS_SKIPPED],
                    [FixerFileProcessedEvent::STATUS_NO_CHANGES, 66],
                    [FixerFileProcessedEvent::STATUS_INVALID],
                    [FixerFileProcessedEvent::STATUS_NO_CHANGES],
                    [FixerFileProcessedEvent::STATUS_INVALID],
                    [FixerFileProcessedEvent::STATUS_NO_CHANGES, 40],
                    [FixerFileProcessedEvent::STATUS_UNKNOWN],
                    [FixerFileProcessedEvent::STATUS_NO_CHANGES, 32],
                ],
                '...................E......EFFF...................................................................S..................................................................I.I........................................?................................',
            ],
        ];
    }

    /**
     * @param array  $statuses
     * @param string $expectedOutput
     *
     * @dataProvider getProcessProgressOutputWithNumbersCases
     */
    public function testProcessProgressOutputWithNumbers(array $statuses, $expectedOutput)
    {
        $nbFiles = 0;
        $this->foreachStatus($statuses, function ($status) use (&$nbFiles) {
            ++$nbFiles;
        });

        $processOutput = new ProcessOutput(
            $output = new BufferedOutput(),
            $this->prophesize(\Symfony\Component\EventDispatcher\EventDispatcher::class)->reveal(),
            $nbFiles
        );

        $this->foreachStatus($statuses, function ($status) use ($processOutput) {
            $processOutput->onFixerFileProcessed(new FixerFileProcessedEvent($status));
        });

        $this->assertSame($expectedOutput, $output->fetch());
    }

    public function getProcessProgressOutputWithNumbersCases()
    {
        return [
            [
                [
                    [FixerFileProcessedEvent::STATUS_NO_CHANGES, 4],
                ],
                '....                                                                4 / 4 (100%)',
            ],
            [
                [
                    [FixerFileProcessedEvent::STATUS_NO_CHANGES],
                    [FixerFileProcessedEvent::STATUS_FIXED],
                    [FixerFileProcessedEvent::STATUS_NO_CHANGES, 4],
                ],
                '.F....                                                              6 / 6 (100%)',
            ],
            [
                [
                    [FixerFileProcessedEvent::STATUS_NO_CHANGES, 65],
                ],
                '................................................................. 65 / 65 (100%)',
            ],
            [
                [
                    [FixerFileProcessedEvent::STATUS_NO_CHANGES, 66],
                ],
                '................................................................. 65 / 66 ( 98%)'.PHP_EOL.
                '.                                                                 66 / 66 (100%)',
            ],
            [
                [
                    [FixerFileProcessedEvent::STATUS_NO_CHANGES, 19],
                    [FixerFileProcessedEvent::STATUS_EXCEPTION],
                    [FixerFileProcessedEvent::STATUS_NO_CHANGES, 6],
                    [FixerFileProcessedEvent::STATUS_LINT],
                    [FixerFileProcessedEvent::STATUS_FIXED, 3],
                    [FixerFileProcessedEvent::STATUS_NO_CHANGES, 50],
                    [FixerFileProcessedEvent::STATUS_SKIPPED],
                    [FixerFileProcessedEvent::STATUS_NO_CHANGES, 49],
                    [FixerFileProcessedEvent::STATUS_INVALID],
                    [FixerFileProcessedEvent::STATUS_NO_CHANGES],
                    [FixerFileProcessedEvent::STATUS_INVALID],
                    [FixerFileProcessedEvent::STATUS_NO_CHANGES, 40],
                    [FixerFileProcessedEvent::STATUS_UNKNOWN],
                    [FixerFileProcessedEvent::STATUS_NO_CHANGES, 15],
                ],
                '...................E......EFFF.................................  63 / 189 ( 33%)'.PHP_EOL.
                '.................S............................................. 126 / 189 ( 67%)'.PHP_EOL.
                '....I.I........................................?............... 189 / 189 (100%)',
            ],
        ];
    }

    private function foreachStatus(array $statuses, \Closure $action)
    {
        foreach ($statuses as $status) {
            $multiplier = isset($status[1]) ? $status[1] : 1;
            $status = $status[0];

            for ($i = 0; $i < $multiplier; ++$i) {
                $action($status);
            }
        }
    }
}
