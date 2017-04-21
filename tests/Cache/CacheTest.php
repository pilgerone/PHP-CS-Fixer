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

namespace PhpCsFixer\Tests\Cache;

use PhpCsFixer\Cache\Cache;
use PhpCsFixer\Cache\Signature;
use PhpCsFixer\Cache\SignatureInterface;
use PhpCsFixer\ToolInfo;

/**
 * @author Andreas Möller <am@localheinz.com>
 *
 * @internal
 *
 * @covers \PhpCsFixer\Cache\Cache
 */
final class CacheTest extends \PHPUnit_Framework_TestCase
{
    public function testIsFinal()
    {
        $reflection = new \ReflectionClass(\PhpCsFixer\Cache\Cache::class);

        $this->assertTrue($reflection->isFinal());
    }

    public function testImplementsCacheInterface()
    {
        $reflection = new \ReflectionClass(\PhpCsFixer\Cache\Cache::class);

        $this->assertTrue($reflection->implementsInterface(\PhpCsFixer\Cache\CacheInterface::class));
    }

    public function testConstructorSetsValues()
    {
        $signature = $this->getSignatureDouble();

        $cache = new Cache($signature);

        $this->assertSame($signature, $cache->getSignature());
    }

    public function testDefaults()
    {
        $signature = $this->getSignatureDouble();

        $cache = new Cache($signature);

        $file = 'test.php';

        $this->assertFalse($cache->has($file));
        $this->assertNull($cache->get($file));
    }

    public function testSetThrowsInvalidArgumentExceptionIfValueIsNotAnInteger()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $signature = $this->getSignatureDouble();

        $cache = new Cache($signature);

        $file = 'test.php';

        $cache->set($file, null);
    }

    public function testCanSetAndGetValue()
    {
        $signature = $this->getSignatureDouble();

        $cache = new Cache($signature);

        $file = 'test.php';
        $hash = crc32('hello');

        $cache->set($file, $hash);

        $this->assertTrue($cache->has($file));
        $this->assertSame($hash, $cache->get($file));
    }

    public function testCanClearValue()
    {
        $signature = $this->getSignatureDouble();

        $cache = new Cache($signature);

        $file = 'test.php';
        $hash = crc32('hello');

        $cache->set($file, $hash);
        $cache->clear($file);

        $this->assertNull($cache->get($file));
    }

    public function testFromJsonThrowsInvalidArgumentExceptionIfJsonIsInvalid()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $json = '{"foo';

        Cache::fromJson($json);
    }

    /**
     * @dataProvider providerMissingData
     *
     * @param array $data
     */
    public function testFromJsonThrowsInvalidArgumentExceptionIfJsonIsMissingKey(array $data)
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $json = json_encode($data);

        Cache::fromJson($json);
    }

    /**
     * @return array
     */
    public function providerMissingData()
    {
        $data = [
            'php' => '7.1.2',
            'version' => '2.0',
            'rules' => [
                'foo' => true,
                'bar' => false,
            ],
            'hashes' => [],
        ];

        return array_map(function ($missingKey) use ($data) {
            unset($data[$missingKey]);

            return [
                $data,
            ];
        }, array_keys($data));
    }

    /**
     * @dataProvider provideCanConvertToAndFromJsonCases
     */
    public function testCanConvertToAndFromJson(SignatureInterface $signature)
    {
        $cache = new Cache($signature);

        $file = 'test.php';
        $hash = crc32('hello');

        $cache->set($file, $hash);
        $cached = Cache::fromJson($cache->toJson());

        $this->assertTrue($cached->getSignature()->equals($signature));
        $this->assertTrue($cached->has($file));
        $this->assertSame($hash, $cached->get($file));
    }

    public function provideCanConvertToAndFromJsonCases()
    {
        return [
            [new Signature(
                PHP_VERSION,
                '2.0',
                [
                    'foo' => true,
                    'bar' => true,
                ]
            )],
            [new Signature(
                PHP_VERSION,
                ToolInfo::getVersion(),
                [
                    // value encoded in ANSI, not UTF
                    'header_comment' => ['header' => 'Dariusz '.base64_decode('UnVtafFza2k=', true)],
                ]
            )],
        ];
    }

    /**
     * @return SignatureInterface
     */
    private function getSignatureDouble()
    {
        return $this->prophesize(\PhpCsFixer\Cache\SignatureInterface::class)->reveal();
    }
}
