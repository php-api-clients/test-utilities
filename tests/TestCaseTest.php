<?php

declare(strict_types=1);

namespace ApiClients\Tests\Tools\TestUtilities;

use ApiClients\Tools\TestUtilities\TestCase;
use React\Promise\Deferred;
use React\Promise\Timer\TimeoutException;

use function random_int;
use function React\Promise\resolve;
use function Safe\file_get_contents;
use function Safe\file_put_contents;
use function Safe\mkdir;
use function time;

use const DIRECTORY_SEPARATOR;
use const PHP_INT_MAX;

final class TestCaseTest extends TestCase
{
    private const PENTIUM = 66;

    private string $previousTemporaryDirectory = '';

    /**
     * @return iterable<array<string>>
     */
    public function provideTemporaryDirectory(): iterable
    {
        for ($i = 0; $i <= self::PENTIUM; $i++) {
            yield [
                (string) random_int($i * $i, PHP_INT_MAX),
            ];
        }
    }

    public function testRecursiveDirectoryCreation(): void
    {
        static::assertFileExists($this->getTmpDir());
    }

    /**
     * @dataProvider provideTemporaryDirectory
     */
    public function testTemporaryDirectoryAndGetFilesInDirectory(string $int): void
    {
        static::assertNotSame($this->getTmpDir(), $this->previousTemporaryDirectory);

        $dir = $this->getTmpDir() . $this->getRandomNameSpace() . DIRECTORY_SEPARATOR;
        mkdir($dir);

        for ($i = 0; $i < self::PENTIUM; $i++) {
            static::assertCount($i, $this->getFilesInDirectory($this->getTmpDir()), (string) $i);
            file_put_contents($dir . $i, $int);
        }

        static::assertCount(self::PENTIUM, $this->getFilesInDirectory($this->getTmpDir()));

        foreach ($this->getFilesInDirectory($this->getTmpDir()) as $file) {
            static::assertSame($int, file_get_contents($file));
        }
    }

    public function testAwait(): void
    {
        $value = time();
        static::assertSame($value, $this->await(resolve($value)));
    }

    public function testAwaitAll(): void
    {
        $value = time();
        static::assertSame([$value, $value], $this->awaitAll([resolve($value), resolve($value)]));
    }

    public function testAwaitAny(): void
    {
        $value = time();
        static::assertSame($value, $this->awaitAny([resolve($value), resolve($value)]));
    }

    /**
     * @param mixed $bool
     *
     * @dataProvider provideTrueFalse
     */
    public function testTrueFalse($bool): void
    {
        static::assertIsBool($bool);
    }

    public function testAwaitTimeout(): void
    {
        self::expectException(TimeoutException::class);

        $this->await((new Deferred())->promise(), 0.1);
    }

    public function testGetSysTempDir(): void
    {
        self::assertFileExists($this->getSysTempDir());
    }
}
